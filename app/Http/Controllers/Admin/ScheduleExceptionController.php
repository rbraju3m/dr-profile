<?php

namespace App\Http\Controllers\Admin;

use App\Models\Chamber;
use App\Models\ScheduleException;
use App\Support\Week;
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
            ['label' => __('site.booking.step_date'), 'type' => 'strong', 'value' => fn (ScheduleException $e) => Week::date($e->date, withWeekday: true)],
            ['label' => __('admin.nav.chambers'), 'value' => fn (ScheduleException $e) => $e->chamber?->name ?? __('admin.exceptions.all_chambers')],
            ['label' => __('admin.common.kind'), 'value' => fn (ScheduleException $e) => $e->is_available ? __('admin.exceptions.extra') : __('admin.exceptions.closed')],
            ['label' => __('admin.exceptions.reason'), 'type' => 'muted', 'key' => 'reason_en'],
        ];
    }

    protected function formData(?Model $record): array
    {
        return parent::formData($record) + [
            'chambers' => Chamber::ordered()->get()->mapWithKeys(fn (Chamber $c) => [$c->id => $c->name]),
        ];
    }

    /** "The date has already been taken" does not say what actually happened. */
    protected function messages(): array
    {
        return ['date.unique' => __('validation_custom.exception_exists')];
    }

    protected function rules(?Model $record): array
    {
        return [
            'chamber_id' => ['nullable', Rule::exists('chambers', 'id')],
            'date' => [
                'required',
                'date',
                /*
                 * One row per chamber per date, and one site-wide row per date.
                 *
                 * This hangs off `date` rather than off `chamber_id`, where it
                 * used to, because `nullable` stops the rest of a null field's
                 * rules from running: a second "away from everywhere" row for
                 * the same day was never checked. The unique index on the table
                 * cannot catch it either — MySQL counts two NULLs as different
                 * values — so nothing did.
                 */
                Rule::unique('schedule_exceptions', 'date')
                    ->where(fn ($q) => request()->filled('chamber_id')
                        ? $q->where('chamber_id', request()->integer('chamber_id'))
                        : $q->whereNull('chamber_id'))
                    ->ignore($record?->id),
            ],
            'is_available' => ['boolean'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_if:is_available,1'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time', 'required_if:is_available,1'],
            'slot_minutes' => ['nullable', 'integer', 'min:5', 'max:180'],
            'reason_en' => ['nullable', 'string', 'max:200'],
            'reason_bn' => ['nullable', 'string', 'max:200'],
        ];
    }
}
