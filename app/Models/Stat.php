<?php

namespace App\Models;

use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use App\Support\Number;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['label'];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function displayValue(): string
    {
        return Number::localizeDigits(number_format($this->value)).($this->suffix ?? '');
    }
}
