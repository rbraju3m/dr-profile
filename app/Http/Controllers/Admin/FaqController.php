<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class FaqController extends ResourceController
{
    protected string $model = Faq::class;

    protected string $viewPath = 'faqs';

    protected string $routeName = 'admin.faqs';

    protected string $labelKey = 'admin.nav.faqs';

    protected bool $reorderable = true;

    protected array $searchable = ['question_en', 'question_bn'];

    protected function indexQuery(): Builder
    {
        return Faq::query()->orderBy('group')->orderBy('sort_order');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.group'), 'value' => fn (Faq $f) => __('site.faq.groups.'.$f->group)],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'question_en'],
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'group' => ['required', Rule::in(Faq::GROUPS)],
            'question_en' => ['required', 'string', 'max:250'],
            'question_bn' => ['nullable', 'string', 'max:250'],
            'answer_en' => ['required', 'string'],
            'answer_bn' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
