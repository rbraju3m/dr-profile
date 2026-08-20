<?php

namespace App\Models;

use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasTranslations, Sortable;

    protected $guarded = [];

    public const GROUPS = ['general', 'appointment', 'fees', 'treatment'];

    protected array $translatable = ['question', 'answer'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }
}
