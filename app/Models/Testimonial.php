<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasMedia, HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['patient_title', 'content'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'visited_on' => 'date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function photoUrl(): ?string
    {
        return $this->mediaUrl('photo');
    }
}
