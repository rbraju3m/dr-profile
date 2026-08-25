<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    /** @var array<int, string> */
    protected array $mediaColumns = ['image'];

    protected array $translatable = [
        'name', 'short_description', 'description', 'duration',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function successStories(): HasMany
    {
        return $this->hasMany(SuccessStory::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }
}
