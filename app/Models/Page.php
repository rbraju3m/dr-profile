<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasMedia, HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['title', 'content', 'meta_title', 'meta_description'];

    protected function casts(): array
    {
        return [
            'show_in_footer' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
