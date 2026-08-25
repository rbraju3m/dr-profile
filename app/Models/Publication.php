<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    /** @var array<int, string> */
    protected array $mediaColumns = ['file'];

    public const TYPES = ['journal', 'conference', 'book', 'chapter', 'thesis', 'other'];

    protected array $translatable = ['title', 'venue', 'abstract'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('year');
    }
}
