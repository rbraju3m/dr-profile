<x-layouts.admin :title="__('admin.appointments.title')">
    <x-admin.page-header :title="__('admin.appointments.title')">
        <x-slot:actions>
            <a href="{{ route('admin.appointments.export', request()->query()) }}" class="btn-secondary">
                <x-icon name="download" class="h-4 w-4"/>{{ __('admin.actions.export') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filters --}}
    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label for="q" class="field-label">{{ __('admin.actions.search') }}</label>
                <input id="q" name="q" value="{{ $filters['q'] }}"
                       placeholder="{{ __('admin.appointments.search_placeholder') }}" class="field-input">
            </div>

            <x-admin.select name="status" :label="__('admin.appointments.filter_status')" :value="$filters['status']"
                            :placeholder="__('admin.common.all')"
                            :options="collect(App\Models\Appointment::STATUSES)->mapWithKeys(fn ($s) => [$s => __('site.status.'.$s)])"/>

            <x-admin.select name="chamber_id" :label="__('admin.appointments.filter_chamber')" :value="$filters['chamber_id']"
                            :placeholder="__('admin.common.all')" :options="$chambers"/>

            <x-admin.input name="from" type="date" :label="__('admin.appointments.filter_date_from')" :value="$filters['from']"/>
            <x-admin.input name="to" type="date" :label="__('admin.appointments.filter_date_to')" :value="$filters['to']"/>
        </div>

        <div class="mt-4 flex gap-2">
            <button type="submit" class="btn-primary">
                <x-icon name="filter" class="h-4 w-4"/>{{ __('admin.actions.filter') }}
            </button>
            <a href="{{ route('admin.appointments.index') }}" class="btn-ghost">{{ __('admin.actions.reset') }}</a>
        </div>
    </form>

    @if ($appointments->isEmpty())
        <x-empty-state icon="calendar-x" :title="__('admin.common.empty')"/>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.appointments.serial') }}</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.appointments.when') }}</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.appointments.patient') }}</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.appointments.chamber') }}</th>
                            <th scope="col" class="px-5 py-3 text-start font-semibold">{{ __('admin.appointments.status') }}</th>
                            <th scope="col" class="px-5 py-3 text-end font-semibold">{{ __('admin.common.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($appointments as $appointment)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <a href="{{ route('admin.appointments.show', $appointment) }}"
                                       class="font-mono text-xs font-semibold text-primary-700 hover:underline">
                                        {{ $appointment->appointment_no }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="block font-medium text-slate-800">{{ \App\Support\Week::date($appointment->appointment_date) }}</span>
                                    <span class="block text-xs tabular-nums text-slate-500">
                                        {{ $appointment->slotLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="block font-medium text-slate-800">{{ $appointment->patient_name }}</span>
                                    <span class="block text-xs tabular-nums text-slate-500">{{ $appointment->patient_phone }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $appointment->chamber?->name }}</td>
                                <td class="px-5 py-3.5"><x-admin.status-badge :status="$appointment->status"/></td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.appointments.show', $appointment) }}"
                                           class="grid h-9 w-9 place-items-center rounded-lg text-slate-400 hover:bg-primary-50 hover:text-primary-600"
                                           aria-label="{{ __('admin.actions.view') }}">
                                            <x-icon name="search" class="h-4 w-4"/>
                                        </a>
                                        <a href="{{ route('admin.appointments.edit', $appointment) }}"
                                           class="grid h-9 w-9 place-items-center rounded-lg text-slate-400 hover:bg-primary-50 hover:text-primary-600"
                                           aria-label="{{ __('admin.actions.edit') }}">
                                            <x-icon name="pencil" class="h-4 w-4"/>
                                        </a>
                                        <x-admin.delete-button :action="route('admin.appointments.destroy', $appointment)"/>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($appointments->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $appointments->links() }}</div>
            @endif
        </div>
    @endif
</x-layouts.admin>
