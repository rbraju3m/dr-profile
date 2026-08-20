<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Support\Number;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ServiceController extends ResourceController
{
    protected string $model = Service::class;

    protected string $viewPath = 'services';

    protected string $routeName = 'admin.services';

    protected string $labelKey = 'admin.nav.services';

    protected bool $reorderable = true;

    protected array $searchable = ['name_en', 'name_bn'];

    protected array $mediaFields = ['image' => 'services'];

    protected ?string $slugSource = 'name_en';

    protected function indexQuery(): Builder
    {
        return Service::query()->orderBy('sort_order')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'name_en'],
            ['label' => __('admin.common.bangla'), 'type' => 'muted', 'key' => 'name_bn'],
            ['label' => __('admin.common.fee'), 'value' => fn (Service $s) => $s->fee ? Number::money($s->fee, 'en') : '—'],
            ['label' => __('admin.common.featured'), 'type' => 'bool', 'key' => 'is_featured'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name_en' => ['required', 'string', 'max:150'],
            'name_bn' => ['nullable', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('services', 'slug')->ignore($record?->id)],
            'short_description_en' => ['nullable', 'string', 'max:500'],
            'short_description_bn' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string'],
            'description_bn' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:60'],
            'image' => Uploads::imageRules(),
            'fee' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'duration_en' => ['nullable', 'string', 'max:80'],
            'duration_bn' => ['nullable', 'string', 'max:80'],
            'meta_title_en' => ['nullable', 'string', 'max:180'],
            'meta_title_bn' => ['nullable', 'string', 'max:180'],
            'meta_description_en' => ['nullable', 'string', 'max:300'],
            'meta_description_bn' => ['nullable', 'string', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
