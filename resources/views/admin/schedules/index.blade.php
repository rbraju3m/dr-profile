<x-layouts.admin :title="__('admin.schedules.title')">
    <x-admin.page-header :title="$chamber->name.' · '.__('admin.schedules.title')"
                         :subtitle="__('admin.schedules.intro')"
                         :back="route('admin.chambers.index')"/>

    {{-- Rows written before the guard covered every chamber are still in the table,
         and the person who can fix them is standing on this page. --}}
    @if ($clashes->isNotEmpty())
        <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-900">
            <p class="flex items-center gap-2 font-semibold">
                <x-icon name="alert-triangle" class="h-4 w-4 shrink-0"/>{{ __('admin.schedules.clash_heading') }}
            </p>
            <p class="mt-1 text-sm leading-relaxed text-amber-800">{{ __('admin.schedules.clash_intro') }}</p>
            <ul class="mt-3 space-y-1 text-sm">
                @foreach ($clashes as $clash)
                    <li class="tabular-nums">
                        {{ __('admin.schedules.clash_row', [
                            'day' => \App\Support\Week::name($clash['day']),
                            'time' => \App\Support\Week::time($clash['from']).' – '.\App\Support\Week::time($clash['to']),
                            'chamber' => $clash['other']->name,
                        ]) }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card :title="__('admin.schedules.title')" flush>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.schedules.day') }}</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.schedules.start') }} – {{ __('admin.schedules.end') }}</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.schedules.slot_minutes') }}</th>
                            <th scope="col" class="px-5 py-3 text-end font-semibold">{{ __('admin.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($days as $day => $dayName)
                            @php $sittings = $schedules[$day] ?? collect(); @endphp
                            <tr class="{{ $sittings->isEmpty() ? 'bg-slate-50/50' : '' }}">
                                <th scope="row" class="px-5 py-3 text-start font-medium text-slate-700">{{ $dayName }}</th>
                                <td class="px-5 py-3" colspan="3">
                                    @if ($sittings->isEmpty())
                                        <span class="text-sm text-slate-400">{{ __('site.chamber.closed') }}</span>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach ($sittings as $sitting)
                                                <li class="flex items-center justify-between gap-4">
                                                    {{-- The model already knows how to write its own hours, localised. --}}
                                                    <span class="tabular-nums text-slate-700">{{ $sitting->timeRange() }}</span>
                                                    <span class="text-xs text-slate-500">
                                                        {{ __('admin.schedules.minutes_each', ['count' => bn_digits($sitting->slot_minutes)]) }}
                                                        @if ($sitting->max_patients)
                                                            · {{ __('admin.schedules.capacity', ['count' => bn_digits($sitting->max_patients)]) }}
                                                        @endif
                                                    </span>
                                                    <x-admin.delete-button :action="route('admin.schedules.destroy', $sitting)"/>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.card>
        </div>

        <x-admin.card :title="__('admin.actions.add_row')">
            <form method="POST" action="{{ route('admin.chambers.schedules.store', $chamber) }}" class="space-y-4">
                @csrf
                <x-admin.select name="day_of_week" :label="__('admin.schedules.day')" required :options="$days"/>
                <div class="grid grid-cols-2 gap-3">
                    <x-admin.input name="start_time" type="time" :label="__('admin.schedules.start')" required value="17:00"/>
                    <x-admin.input name="end_time" type="time" :label="__('admin.schedules.end')" required value="20:00"/>
                </div>
                <x-admin.input name="slot_minutes" type="number" :label="__('admin.schedules.slot_minutes')" required value="20"/>
                <x-admin.input name="max_patients" type="number" :label="__('admin.schedules.max_patients')"
                               :hint="__('admin.hints.no_cap')"/>
                <button type="submit" class="btn-primary w-full">
                    <x-icon name="plus" class="h-4 w-4"/>{{ __('admin.actions.add_row') }}
                </button>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
