<?php

namespace App\Http\Controllers\Admin;

use App\Models\Stat;
use App\Support\Icons;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class StatController extends ResourceController
{
    protected string $model = Stat::class;

    protected string $viewPath = 'stats';

    protected string $routeName = 'admin.stats';

    protected string $labelKey = 'admin.nav.stats';

    protected bool $reorderable = true;

    protected array $searchable = ['label_en', 'label_bn'];

    protected function indexQuery(): Builder
    {
        return Stat::query()->orderBy('sort_order')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'label_en'],
            ['label' => __('admin.common.bangla'), 'type' => 'muted', 'key' => 'label_bn'],
            ['label' => __('admin.common.value'), 'type' => 'number', 'value' => fn (Stat $s) => number_format($s->value).$s->suffix],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'label_en' => ['required', 'string', 'max:120'],
            'label_bn' => ['nullable', 'string', 'max:120'],
            'value' => ['required', 'integer', 'min:0'],
            'suffix' => ['nullable', 'string', 'max:10'],
            // Only a glyph the site can draw; anything else renders as a bare circle.
            'icon' => ['nullable', 'string', Rule::in(Icons::names())],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
