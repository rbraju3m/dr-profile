<?php

namespace App\Http\Controllers\Admin;

use App\Models\Chamber;
use App\Models\ScheduleException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ScheduleExceptionController extends ResourceController
{
    protected string $model = ScheduleException::class;

    protected string $viewPath = 'exceptions';

    protected string $routeName = 'admin.exceptions';

    protected string $labelKey = 'admin.nav.exceptions';

    protected function indexQuery(): Builder
    {
        return ScheduleException::query()->with('chamber')->orderByDesc('date');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('site.booking.step_date'), 'type' => 'strong', 'value' => fn (ScheduleException $e) => $e->date->format('D, d M Y')],
            ['label' => __('admin.nav.chambers'), 'value' => fn (ScheduleException $e) => $e->chamber?->name_en ?? __('admin.exceptions.all_chambers')],
            ['label' => 'Kind', 'value' => fn (ScheduleException $e) => $e->is_available ? __('admin.exceptions.extra') : __('admin.exceptions.closed')],
            ['label' => __('admin.exceptions.reason'), 'type' => 'muted', 'key' => 'reason_en'],
        ];
    }

    protected function formData(?Model $record): array
    {
        return parent::formData($record) + [
            'chambers' => Chamber::ordered()->pluck('name_en', 'id'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'chamber_id' => [
                'nullable',
                Rule::exists('chambers', 'id'),
                Rule::unique('schedule_exceptions')->where(
                    fn ($q) => $q->where('date', request('date'))
                )->ignore($record?->id),
            ],
            'date' => ['required', 'date'],
            'is_available' => ['boolean'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_if:is_available,1'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time', 'required_if:is_available,1'],
            'slot_minutes' => ['nullable', 'integer', 'min:5', 'max:180'],
            'reason_en' => ['nullable', 'string', 'max:200'],
            'reason_bn' => ['nullable', 'string', 'max:200'],
        ];
    }
}
