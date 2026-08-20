<?php

namespace App\Models;

use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
