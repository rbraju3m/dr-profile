<?php

namespace App\Http\Controllers\Admin;

use App\Models\Publication;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PublicationController extends ResourceController
{
    protected string $model = Publication::class;

    protected string $viewPath = 'publications';

    protected string $routeName = 'admin.publications';

    protected string $labelKey = 'admin.nav.publications';

    protected bool $reorderable = true;

    protected array $searchable = ['title_en', 'authors', 'venue_en'];

    protected array $mediaFields = ['file' => 'publications'];

    protected function indexQuery(): Builder
    {
        return Publication::query()->orderByDesc('year')->orderBy('sort_order');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.year'), 'type' => 'number', 'key' => 'year', 'class' => 'w-20'],
            ['label' => __('admin.common.type'), 'value' => fn (Publication $p) => __('site.publications.types.'.$p->type)],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            ['label' => __('admin.common.venue'), 'type' => 'muted', 'key' => 'venue_en'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'type' => ['required', Rule::in(Publication::TYPES)],
            'title_en' => ['required', 'string', 'max:300'],
            'title_bn' => ['nullable', 'string', 'max:300'],
            'authors' => ['nullable', 'string', 'max:250'],
            'venue_en' => ['nullable', 'string', 'max:200'],
            'venue_bn' => ['nullable', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 2)],
            'volume' => ['nullable', 'string', 'max:60'],
            'pages' => ['nullable', 'string', 'max:60'],
            'doi' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:500'],
            'file' => Uploads::pdfRules(),
            'abstract_en' => ['nullable', 'string'],
            'abstract_bn' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
