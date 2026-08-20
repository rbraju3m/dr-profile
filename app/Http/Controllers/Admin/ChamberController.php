<?php

namespace App\Http\Controllers\Admin;

use App\Models\Chamber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ChamberController extends ResourceController
{
    protected string $model = Chamber::class;

    protected string $viewPath = 'chambers';

    protected string $routeName = 'admin.chambers';

    protected string $labelKey = 'admin.nav.chambers';

    protected array $searchable = ['name_en', 'name_bn', 'city_en'];

    protected array $mediaFields = ['image' => 'chambers'];

    protected ?string $slugSource = 'name_en';

    protected function indexQuery(): Builder
    {
        return Chamber::query()->withCount('schedules')->orderBy('sort_order')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.nav.chambers'), 'type' => 'strong', 'key' => 'name_en'],
            ['label' => __('admin.common.bangla'), 'type' => 'muted', 'key' => 'name_bn'],
            [
                'label' => __('admin.nav.schedules'),
                'type' => 'html',
                'value' => fn (Chamber $c) => '<a href="'.route('admin.chambers.schedules.index', $c).'" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 hover:bg-primary-100">'
                    .$c->schedules_count.' · '.e(__('admin.actions.edit')).'</a>',
            ],
            ['label' => 'Online booking', 'type' => 'bool', 'key' => 'accepts_online_booking'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name_en' => ['required', 'string', 'max:150'],
            'name_bn' => ['nullable', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('chambers', 'slug')->ignore($record?->id)],
            'address_en' => ['nullable', 'string', 'max:500'],
            'address_bn' => ['nullable', 'string', 'max:500'],
            'city_en' => ['nullable', 'string', 'max:80'],
            'city_bn' => ['nullable', 'string', 'max:80'],
            'room_no' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:40'],
            'appointment_phone' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'image', 'max:4096'],
            'map_embed' => ['nullable', 'string', 'max:2000'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'followup_fee' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'note_en' => ['nullable', 'string', 'max:500'],
            'note_bn' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'accepts_online_booking' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
