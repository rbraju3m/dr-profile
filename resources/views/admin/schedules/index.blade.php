<x-layouts.admin :title="__('admin.schedules.title')">
    <x-admin.page-header :title="$chamber->name_en.' · '.__('admin.schedules.title')"
                         :subtitle="__('admin.schedules.intro')"
                         :back="route('admin.chambers.index')"/>

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
                                                    <span class="tabular-nums text-slate-700">
                                                        {{ \Illuminate\Support\Carbon::parse($sitting->start_time)->format('g:i A') }}
                                                        –
                                                        {{ \Illuminate\Support\Carbon::parse($sitting->end_time)->format('g:i A') }}
                                                    </span>
                                                    <span class="text-xs text-slate-500">
                                                        {{ $sitting->slot_minutes }} min
                                                        @if ($sitting->max_patients) · max {{ $sitting->max_patients }} @endif
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
