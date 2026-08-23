<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\Testimonial;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TestimonialController extends ResourceController
{
    protected string $model = Testimonial::class;

    protected string $viewPath = 'testimonials';

    protected string $routeName = 'admin.testimonials';

    protected string $labelKey = 'admin.nav.testimonials';

    protected bool $reorderable = true;

    protected array $searchable = ['patient_name', 'content_en'];

    protected array $mediaFields = ['photo' => 'testimonials'];

    protected function indexQuery(): Builder
    {
        return Testimonial::query()->orderBy('sort_order')->orderByDesc('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('site.stories.patient'), 'type' => 'strong', 'key' => 'patient_name'],
            ['label' => __('admin.common.quote'), 'value' => fn (Testimonial $t) => \Str::limit($t->content_en, 60)],
            ['label' => __('admin.common.rating'), 'type' => 'number', 'value' => fn (Testimonial $t) => str_repeat('★', $t->rating)],
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
            'patient_name' => ['required', 'string', 'max:120'],
            'patient_title_en' => ['nullable', 'string', 'max:120'],
            'patient_title_bn' => ['nullable', 'string', 'max:120'],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'content_en' => ['required', 'string', 'max:1000'],
            'content_bn' => ['nullable', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'photo' => Uploads::imageRules(),
            'visited_on' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
