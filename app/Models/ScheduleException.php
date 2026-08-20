<?php

namespace App\Models;

use App\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleException extends Model
{
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['reason'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
            'slot_minutes' => 'integer',
        ];
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }
}
