<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\SuccessStory;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class SuccessStoryController extends ResourceController
{
    protected string $model = SuccessStory::class;

    protected string $viewPath = 'stories';

    protected string $routeName = 'admin.stories';

    protected string $labelKey = 'admin.nav.stories';

    protected bool $reorderable = true;

    protected array $searchable = ['title_en', 'title_bn', 'patient_name'];

    protected array $mediaFields = ['image' => 'stories'];

    protected ?string $slugSource = 'title_en';

    protected function indexQuery(): Builder
    {
        return SuccessStory::query()->with('service')->orderByDesc('published_at')->orderByDesc('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.image'), 'type' => 'image', 'value' => fn (SuccessStory $s) => $s->imageUrl(), 'class' => 'w-20'],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            ['label' => __('site.stories.patient'), 'type' => 'muted', 'key' => 'patient_name'],
            ['label' => __('admin.nav.services'), 'value' => fn (SuccessStory $s) => $s->service?->name ?? '—'],
            ['label' => __('admin.common.featured'), 'type' => 'bool', 'key' => 'is_featured'],
            ['label' => __('admin.common.published'), 'type' => 'bool', 'key' => 'is_published'],
        ];
    }

    protected function formData(?Model $record): array
    {
        return parent::formData($record) + [
            'services' => Service::active()->ordered()->get()->mapWithKeys(fn (Service $s) => [$s->id => $s->name]),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title_en' => ['required', 'string', 'max:200'],
            'title_bn' => ['nullable', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('success_stories', 'slug')->ignore($record?->id)],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'patient_name' => ['nullable', 'string', 'max:120'],
            'patient_age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'patient_location_en' => ['nullable', 'string', 'max:120'],
            'patient_location_bn' => ['nullable', 'string', 'max:120'],
            'condition_en' => ['nullable', 'string', 'max:1000'],
            'condition_bn' => ['nullable', 'string', 'max:1000'],
            'summary_en' => ['nullable', 'string', 'max:600'],
            'summary_bn' => ['nullable', 'string', 'max:600'],
            'content_en' => self::LONG_TEXT,
            'content_bn' => self::LONG_TEXT,
            'image' => Uploads::imageRules(),
            'video_url' => ['nullable', 'url', 'max:500'],
            'treatment_date' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'meta_title_en' => ['nullable', 'string', 'max:180'],
            'meta_title_bn' => ['nullable', 'string', 'max:180'],
            'meta_description_en' => ['nullable', 'string', 'max:300'],
            'meta_description_bn' => ['nullable', 'string', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
