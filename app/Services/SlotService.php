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
     *
     * This reads the database every time it is called, deliberately.
     * BookingService calls it inside its transaction while holding the chamber
     * lock, and a remembered answer there would be precisely the stale read the
     * lock exists to prevent. Anything that wants a run of days wants window().
     */
    public function availability(Chamber $chamber, Carbon $date): DayAvailability
    {
        $date = $date->copy()->startOfDay();

        if ($date->isBefore(Carbon::today())) {
            return DayAvailability::closed($date, __('site.booking.past_date'));
        }

        return $this->build(
            $chamber,
            $date,
            $this->exceptionFor($chamber, $date),
            $this->takenTimes($chamber, $date),
        );
    }

    /**
     * Every date in the booking window, in two queries rather than two a day.
     *
     * The date picker and the "next free date" line both walked the window
     * calling availability() one day at a time, so each of them cost a pair of
     * queries per day — sixty-two per chamber to draw the booking page, and the
     * homepage paid it again for every chamber it listed. The exceptions and the
     * taken slots for the whole span are read once here instead.
     *
     * @return array<string, DayAvailability> keyed by Y-m-d
     */
    public function window(Chamber $chamber, ?int $days = null): array
    {
        $days = $days ?? $this->windowDays();
        $start = Carbon::today();
        $end = $start->copy()->addDays($days);

        $exceptions = $this->exceptionsBetween($chamber, $start, $end);
        $taken = $this->takenBetween($chamber, $start, $end);

        $window = [];

        for ($offset = 0; $offset <= $days; $offset++) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            $window[$key] = $this->build($chamber, $date, $exceptions[$key] ?? null, $taken[$key] ?? []);
        }

        return $window;
    }

    /**
     * One date, once its exception and taken slots are already in hand.
     * The shared body of availability() and window().
     *
     * @param  array<string, true>  $taken
     */
    private function build(Chamber $chamber, Carbon $date, ?ScheduleException $exception, array $taken): DayAvailability
    {
        if ($exception && ! $exception->is_available) {
            return DayAvailability::closed($date, $exception->tr('reason') ?: __('site.booking.closed_on_date'));
        }

        $sittings = $this->sittingsFor($chamber, $date, $exception);

        if ($sittings->isEmpty()) {
            return DayAvailability::closed($date, __('site.booking.closed_on_date'));
        }

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
        $out = [];

        foreach ($this->window($chamber, $days) as $date => $availability) {
            $out[] = [
                'date' => $date,
                'day' => $availability->date->dayOfWeek,
                'open' => $availability->hasOpenSlots(),
                'count' => $availability->openCount(),
            ];
        }

        return $out;
    }

    /** The soonest date at this chamber that still has a free slot. */
    public function nextAvailableDate(Chamber $chamber): ?Carbon
    {
        foreach ($this->window($chamber) as $availability) {
            if ($availability->hasOpenSlots()) {
                return $availability->date;
            }
        }

        return null;
    }

    /**
     * "9:30", "09:30" and "09:30:00" all mean the same slot; anything else
     * means none.
     *
     * The guard used to accept a one-digit hour and then reject it anyway:
     * str_pad() to eight characters turned "9:30" into "9:30:00:", Carbon threw
     * on the trailing colon, and the caller was told the slot was taken. The
     * parts are read out of the pattern now rather than padded into shape.
     */
    public function normaliseTime(?string $time): ?string
    {
        if (blank($time) || ! preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $parts)) {
            return null;
        }

        [$hours, $minutes, $seconds] = [(int) $parts[1], (int) $parts[2], (int) ($parts[3] ?? 0)];

        if ($hours > 23 || $minutes > 59 || $seconds > 59) {
            return null;
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Every exception touching the window, keyed by date.
     *
     * Same precedence as exceptionFor(), arranged the other way round: the
     * site-wide rows are ordered first so that keyBy(), which keeps the last of
     * a repeated key, is left holding the chamber's own override.
     *
     * @return array<string, ScheduleException>
     */
    private function exceptionsBetween(Chamber $chamber, Carbon $from, Carbon $to): array
    {
        return ScheduleException::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where(fn ($q) => $q->where('chamber_id', $chamber->id)->orWhereNull('chamber_id'))
            ->orderByRaw('chamber_id IS NULL DESC')
            ->get()
            ->keyBy(fn (ScheduleException $exception) => $exception->date->toDateString())
            ->all();
    }

    /**
     * Slots already spoken for across the window, keyed by date and then time.
     *
     * @return array<string, array<string, true>>
     */
    private function takenBetween(Chamber $chamber, Carbon $from, Carbon $to): array
    {
        return Appointment::query()
            ->where('chamber_id', $chamber->id)
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->blocking()
            ->get(['appointment_date', 'slot_time'])
            ->groupBy(fn (Appointment $a) => $a->appointment_date->toDateString())
            ->map(fn ($rows) => $rows
                ->mapWithKeys(fn (Appointment $a) => [Carbon::parse($a->slot_time)->format('H:i:s') => true])
                ->all())
            ->all();
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

        // activeSchedules, not schedules: every caller eager-loads that one, and
        // reading the other lazy-loaded a second copy of the same rows per chamber.
        return $chamber->activeSchedules
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

        /*
         * A sitting ends the day it starts. Both the weekly rows and the
         * exceptions validate end_time as after:start_time, so this can only
         * fire on a row that reached the table another way.
         *
         * It used to wrap such a row into the next day and go on generating
         * slots, which read as support for an overnight chamber and was not:
         * the slots are keyed by clock time alone, so the ones past midnight
         * sorted to the head of the list and were counted against the day
         * before. Declining the row leaves the chamber showing as closed,
         * which is at least the truth about what can be booked.
         */
        if ($end->lte($cursor)) {
            return [];
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
