<?php

namespace App\Models;

use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['title', 'organization', 'location', 'description'];

    public const TYPES = ['education', 'experience', 'training', 'award', 'membership', 'certification'];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'is_active' => 'boolean',
            'start_year' => 'integer',
            'end_year' => 'integer',
        ];
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** "2010 – 2014", "2018 – Present" or a single year. */
    public function period(): ?string
    {
        if (! $this->start_year) {
            return $this->end_year ? (string) $this->end_year : null;
        }

        if ($this->is_current) {
            return $this->start_year.' – '.__('site.present');
        }

        return $this->end_year && $this->end_year !== $this->start_year
            ? $this->start_year.' – '.$this->end_year
            : (string) $this->start_year;
    }
}
