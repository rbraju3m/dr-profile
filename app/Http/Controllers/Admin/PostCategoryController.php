<?php

namespace App\Http\Controllers\Admin;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PostCategoryController extends ResourceController
{
    protected string $model = PostCategory::class;

    protected string $viewPath = 'post-categories';

    protected string $routeName = 'admin.post-categories';

    protected string $labelKey = 'admin.nav.post_categories';

    protected array $searchable = ['name_en', 'name_bn'];

    protected ?string $slugSource = 'name_en';

    protected function indexQuery(): Builder
    {
        return PostCategory::query()->withCount('posts')->orderBy('sort_order')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'name_en'],
            ['label' => __('admin.common.bangla'), 'type' => 'muted', 'key' => 'name_bn'],
            ['label' => __('admin.nav.posts'), 'type' => 'number', 'key' => 'posts_count'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name_en' => ['required', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('post_categories', 'slug')->ignore($record?->id)],
            'description_en' => ['nullable', 'string', 'max:500'],
            'description_bn' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
