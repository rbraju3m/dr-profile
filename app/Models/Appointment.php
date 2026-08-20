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

    /** APT-20260820-A7K3 — readable on the phone, unguessable enough for a lookup URL. */
    public static function generateNumber(Carbon|string|null $date = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        do {
            $number = 'APT-'.$date->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (static::where('appointment_no', $number)->exists());

        return $number;
    }

    public function slotLabel(): string
    {
        return Week::time($this->slot_time);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && $this->appointment_date->isFuture();
    }
}
