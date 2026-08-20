<?php

namespace App\Http\Controllers\Admin;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SliderController extends ResourceController
{
    protected string $model = Slider::class;

    protected string $viewPath = 'sliders';

    protected string $routeName = 'admin.sliders';

    protected string $labelKey = 'admin.nav.sliders';

    protected array $searchable = ['title_en', 'title_bn'];

    protected array $mediaFields = ['image' => 'sliders', 'mobile_image' => 'sliders'];

    protected function indexQuery(): Builder
    {
        return Slider::query()->orderBy('sort_order')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.image'), 'type' => 'image', 'value' => fn (Slider $s) => $s->imageUrl(), 'class' => 'w-20'],
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            ['label' => __('admin.common.bangla'), 'type' => 'muted', 'key' => 'title_bn'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title_en' => ['nullable', 'string', 'max:200'],
            'title_bn' => ['nullable', 'string', 'max:200'],
            'subtitle_en' => ['nullable', 'string', 'max:400'],
            'subtitle_bn' => ['nullable', 'string', 'max:400'],
            'image' => [$record ? 'nullable' : 'nullable', 'image', 'max:6144'],
            'mobile_image' => ['nullable', 'image', 'max:6144'],
            'cta_label_en' => ['nullable', 'string', 'max:60'],
            'cta_label_bn' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
