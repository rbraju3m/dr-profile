<?php

namespace App\Http\Controllers\Admin;

use App\Models\Credential;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CredentialController extends ResourceController
{
    protected string $model = Credential::class;

    protected string $viewPath = 'credentials';

    protected string $routeName = 'admin.credentials';

    protected string $labelKey = 'admin.nav.credentials';

    protected bool $reorderable = true;

    protected array $searchable = ['title_en', 'title_bn', 'organization_en'];

    protected function indexQuery(): Builder
    {
        return Credential::query()->orderBy('type')->orderBy('sort_order');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.type'), 'value' => fn (Credential $c) => __('site.about.'.match ($c->type) {
                'education' => 'education',
                'experience' => 'experience',
                'training' => 'training',
                'award' => 'awards',
                'membership' => 'memberships',
                default => 'certifications',
            })],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            ['label' => __('admin.common.organisation'), 'type' => 'muted', 'key' => 'organization_en'],
            ['label' => __('admin.common.period'), 'value' => fn (Credential $c) => $c->period() ?? '—'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'type' => ['required', Rule::in(Credential::TYPES)],
            'title_en' => ['required', 'string', 'max:180'],
            'title_bn' => ['nullable', 'string', 'max:180'],
            'organization_en' => ['nullable', 'string', 'max:180'],
            'organization_bn' => ['nullable', 'string', 'max:180'],
            'location_en' => ['nullable', 'string', 'max:120'],
            'location_bn' => ['nullable', 'string', 'max:120'],
            'start_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'end_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 10), 'gte:start_year'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'description_bn' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_current' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
