<?php

namespace App\Models;

use App\Support\Week;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Appointment extends Model
{
    protected $guarded = [];

    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

    /** Statuses that still occupy a slot. */
    public const BLOCKING_STATUSES = ['pending', 'confirmed', 'completed'];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'patient_age' => 'integer',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'appointment_no';
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ChamberSchedule::class, 'chamber_schedule_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', self::BLOCKING_STATUSES);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '>=', Carbon::today());
    }

    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('appointment_date', $date);
    }

    /**
     * APT-20260820-A7K3M2 — short enough to read down the phone, long enough
     * that the day's serials cannot be walked.
     *
     * Four characters folded to upper case is about 20 bits, or a million
     * guesses for a known date: reachable. Six is about 31. The mobile number
     * on the lookup form is what actually guards the record; this only means
     * that guessing at the door is not worth starting.
     */
    public static function generateNumber(Carbon|string|null $date = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        do {
            $number = 'APT-'.$date->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::where('appointment_no', $number)->exists());

        return $number;
    }

    public function slotLabel(): string
    {
        return Week::time($this->slot_time);
    }

    /**
     * The moment the appointment actually begins.
     *
     * `appointment_date` is date-cast, so on its own it is midnight — which is
     * already past for every one of today's appointments. The time of day is in
     * `slot_time`, and the two only mean anything together.
     */
    public function startsAt(): Carbon
    {
        return Carbon::parse($this->appointment_date->toDateString().' '.($this->slot_time ?: '23:59:59'));
    }

    /**
     * A patient may call it off until it starts.
     *
     * Comparing the bare date meant a six o'clock slot stopped being cancellable
     * at midnight the night before: the patient could not release it and the
     * chamber held a seat nobody was coming to. Nothing is risked by allowing it
     * late — a released slot inside the booking lead time is not offered again
     * today anyway.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && $this->startsAt()->isFuture();
    }
}
