<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PageController extends ResourceController
{
    protected string $model = Page::class;

    protected string $viewPath = 'pages';

    protected string $routeName = 'admin.pages';

    protected string $labelKey = 'admin.nav.pages';

    protected bool $reorderable = true;

    protected array $searchable = ['title_en', 'title_bn', 'slug'];

    protected array $mediaFields = ['banner_image' => 'pages'];

    protected ?string $slugSource = 'title_en';

    protected function indexQuery(): Builder
    {
        return Page::query()->orderBy('title_en');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            ['label' => __('admin.common.slug'), 'type' => 'muted', 'key' => 'slug'],
            ['label' => __('admin.common.in_footer'), 'type' => 'bool', 'key' => 'show_in_footer'],
            ['label' => __('admin.common.published'), 'type' => 'bool', 'key' => 'is_published'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title_en' => ['required', 'string', 'max:180'],
            'title_bn' => ['nullable', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('pages', 'slug')->ignore($record?->id)],
            'content_en' => ['nullable', 'string'],
            'content_bn' => ['nullable', 'string'],
            'banner_image' => Uploads::imageRules(),
            'meta_title_en' => ['nullable', 'string', 'max:180'],
            'meta_title_bn' => ['nullable', 'string', 'max:180'],
            'meta_description_en' => ['nullable', 'string', 'max:300'],
            'meta_description_bn' => ['nullable', 'string', 'max:300'],
            'show_in_footer' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
