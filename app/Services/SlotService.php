<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\ScheduleException;
use App\Support\DayAvailability;
use App\Support\Week;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Turns weekly chamber sittings into concrete, bookable time slots for a date.
 *
 * Precedence, highest first:
 *   1. A schedule exception for this chamber on this date.
 *   2. A schedule exception with chamber_id = NULL (doctor unavailable everywhere).
 *   3. The weekly chamber_schedules rows for that day of week.
 *
 * Slots already held by a pending/confirmed/completed appointment are marked
 * taken; cancelled appointments release their slot.
 */
class SlotService
{
    public function leadMinutes(): int
    {
        return (int) config('site.booking.lead_time_minutes', 60);
    }

    public function windowDays(): int
    {
        return (int) config('site.booking.window_days', 30);
    }

    public function lastBookableDate(): Carbon
    {
        return Carbon::today()->addDays($this->windowDays());
    }

    public function isWithinWindow(Carbon $date): bool
    {
        return $date->gte(Carbon::today()) && $date->lte($this->lastBookableDate());
    }

    /**
     * Everything bookable at one chamber on one date.
     */
    public function availability(Chamber $chamber, Carbon $date): DayAvailability
    {
        $date = $date->copy()->startOfDay();

        if ($date->isBefore(Carbon::today())) {
            return DayAvailability::closed($date, __('site.booking.past_date'));
        }

        $exception = $this->exceptionFor($chamber, $date);

        if ($exception && ! $exception->is_available) {
            return DayAvailability::closed($date, $exception->tr('reason') ?: __('site.booking.closed_on_date'));
        }

        $sittings = $this->sittingsFor($chamber, $date, $exception);

        if ($sittings->isEmpty()) {
            return DayAvailability::closed($date, __('site.booking.closed_on_date'));
        }

        $taken = $this->takenTimes($chamber, $date);
        $cutoff = $date->isToday()
            ? Carbon::now()->addMinutes($this->leadMinutes())
            : null;

        $slots = [];

        foreach ($sittings as $sitting) {
            foreach ($this->expand($date, $sitting) as $slotTime) {
                if ($cutoff && $slotTime->lt($cutoff)) {
                    continue;
                }

                $key = $slotTime->format('H:i:s');

                $slots[$key] = [
                    'time' => $key,
                    'label' => Week::time($slotTime),
                    'schedule_id' => $sitting['schedule_id'],
                    'taken' => isset($taken[$key]),
                ];
            }
        }

        ksort($slots);

        return new DayAvailability($date, $slots !== [], array_values($slots));
    }

    /**
     * Day-by-day summary across the booking window — drives the date picker.
     *
     * @return array<int, array{date: string, day: int, open: bool, count: int}>
     */
    public function calendar(Chamber $chamber, ?int $days = null): array
    {
        $days = $days ?? $this->windowDays();
        $out = [];

        for ($i = 0; $i <= $days; $i++) {
            $date = Carbon::today()->addDays($i);
            $availability = $this->availability($chamber, $date);

            $out[] = [
                'date' => $date->toDateString(),
                'day' => $date->dayOfWeek,
                'open' => $availability->hasOpenSlots(),
                'count' => $availability->openCount(),
            ];
        }

        return $out;
    }

    /** The soonest date at this chamber that still has a free slot. */
    public function nextAvailableDate(Chamber $chamber): ?Carbon
    {
        for ($i = 0; $i <= $this->windowDays(); $i++) {
            $date = Carbon::today()->addDays($i);

            if ($this->availability($chamber, $date)->hasOpenSlots()) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Re-check a slot at submit time. Returns the schedule id when the slot is
     * genuinely free, or null when it is not — call inside the booking transaction.
     */
    public function resolveBookableSlot(Chamber $chamber, Carbon $date, string $time): ?int
    {
        $normalised = $this->normaliseTime($time);

        if ($normalised === null || ! $this->isWithinWindow($date)) {
            return null;
        }

        return $this->availability($chamber, $date)->scheduleIdFor($normalised);
    }

    public function normaliseTime(?string $time): ?string
    {
        if (blank($time) || ! preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i:s', str_pad($time, 8, ':00'))->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function exceptionFor(Chamber $chamber, Carbon $date): ?ScheduleException
    {
        return ScheduleException::query()
            ->whereDate('date', $date)
            ->where(fn ($q) => $q->where('chamber_id', $chamber->id)->orWhereNull('chamber_id'))
            // a chamber-specific override beats the site-wide one
            ->orderByRaw('chamber_id IS NULL')
            ->first();
    }

    /**
     * @return Collection<int, array{start: Carbon, end: Carbon, slot_minutes: int, max: int|null, schedule_id: int|null}>
     */
    private function sittingsFor(Chamber $chamber, Carbon $date, ?ScheduleException $exception): Collection
    {
        if ($exception && $exception->is_available && $exception->start_time && $exception->end_time) {
            return collect([[
                'start' => $this->at($date, $exception->start_time),
                'end' => $this->at($date, $exception->end_time),
                'slot_minutes' => $exception->slot_minutes ?: 20,
                'max' => null,
                'schedule_id' => null,
            ]]);
        }

        return $chamber->schedules
            ->where('is_active', true)
            ->where('day_of_week', $date->dayOfWeek)
            ->map(fn (ChamberSchedule $schedule) => [
                'start' => $this->at($date, $schedule->start_time),
                'end' => $this->at($date, $schedule->end_time),
                'slot_minutes' => max(5, $schedule->slot_minutes),
                'max' => $schedule->max_patients,
                'schedule_id' => $schedule->id,
            ])
            ->values();
    }

    /**
     * @param  array{start: Carbon, end: Carbon, slot_minutes: int, max: int|null, schedule_id: int|null}  $sitting
     * @return array<int, Carbon>
     */
    private function expand(Carbon $date, array $sitting): array
    {
        $slots = [];
        $cursor = $sitting['start']->copy();
        $end = $sitting['end'];

        // A sitting that runs past midnight (e.g. 20:00–00:30) ends the next day.
        if ($end->lte($cursor)) {
            $end = $end->copy()->addDay();
        }

        while ($cursor->lt($end)) {
            $slots[] = $cursor->copy();
            $cursor->addMinutes($sitting['slot_minutes']);

            if ($sitting['max'] !== null && count($slots) >= $sitting['max']) {
                break;
            }
        }

        return $slots;
    }

    /** @return array<string, true> */
    private function takenTimes(Chamber $chamber, Carbon $date): array
    {
        return Appointment::query()
            ->where('chamber_id', $chamber->id)
            ->forDate($date)
            ->blocking()
            ->pluck('slot_time')
            ->mapWithKeys(fn ($time) => [Carbon::parse($time)->format('H:i:s') => true])
            ->all();
    }

    private function at(Carbon $date, string $time): Carbon
    {
        return Carbon::parse($date->toDateString().' '.$time);
    }
}
