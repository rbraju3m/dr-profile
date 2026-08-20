<?php

namespace App\Models;

use App\Support\Week;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamberSchedule extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_minutes' => 'integer',
            'max_patients' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    public function dayName(): string
    {
        return Week::name($this->day_of_week);
    }

    /** "10:00 AM – 1:00 PM", localised. */
    public function timeRange(): string
    {
        return Week::time($this->start_time).' – '.Week::time($this->end_time);
    }
}
