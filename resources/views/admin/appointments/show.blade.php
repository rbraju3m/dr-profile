<x-layouts.admin :title="$appointment->appointment_no">
    <x-admin.page-header :title="$appointment->appointment_no"
                         :subtitle="$appointment->patient_name"
                         :back="route('admin.appointments.index')">
        <x-slot:actions>
            <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn-secondary">
                <x-icon name="pencil" class="h-4 w-4"/>{{ __('admin.actions.edit') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card :title="__('admin.appointments.when')" flush>
                <dl class="divide-y divide-slate-100">
                    @foreach ([
                        [__('site.booking.step_date'), $appointment->appointment_date->format('l, d F Y')],
                        [__('site.booking.step_slot'), \Illuminate\Support\Carbon::parse($appointment->slot_time)->format('g:i A')],
                        [__('admin.appointments.chamber'), $appointment->chamber?->name_en],
                        [__('site.chamber.address'), $appointment->chamber?->address_en],
                        [__('admin.appointments.visit_type'), $appointment->visit_type === 'followup' ? __('site.booking.visit_followup') : __('site.booking.visit_new')],
                        [__('site.booking.service'), $appointment->service?->name_en],
                        [__('admin.appointments.notes'), $appointment->notes],
                        [__('admin.appointments.admin_note'), $appointment->admin_note],
                        [__('admin.common.created'), $appointment->created_at?->format('d M Y, g:i A')],
                    ] as [$dtLabel, $value])
                        @if ($value)
                            <div class="flex gap-4 px-5 py-3">
                                <dt class="w-40 shrink-0 text-sm text-slate-500">{{ $dtLabel }}</dt>
                                <dd class="text-sm text-slate-800">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-admin.card>

            <x-admin.card :title="__('admin.appointments.patient')" flush>
                <dl class="divide-y divide-slate-100">
                    @foreach ([
                        [__('site.booking.patient_name'), $appointment->patient_name],
                        [__('site.booking.patient_phone'), $appointment->patient_phone],
                        [__('site.booking.patient_email'), $appointment->patient_email],
                        [__('site.booking.patient_gender'), $appointment->patient_gender ? __('admin.gender.'.$appointment->patient_gender) : null],
                        [__('site.booking.patient_age'), $appointment->patient_age],
                        [__('site.booking.patient_address'), $appointment->patient_address],
                    ] as [$dtLabel, $value])
                        @if ($value)
                            <div class="flex gap-4 px-5 py-3">
                                <dt class="w-40 shrink-0 text-sm text-slate-500">{{ $dtLabel }}</dt>
                                <dd class="text-sm text-slate-800">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card :title="__('admin.appointments.change_status')">
                <div class="mb-4"><x-admin.status-badge :status="$appointment->status"/></div>

                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}"
                      x-data="{ status: '{{ $appointment->status }}' }" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-2">
                        @foreach (App\Models\Appointment::STATUSES as $status)
                            <label class="cursor-pointer rounded-xl border px-3 py-2.5 text-center text-sm transition"
                                   :class="status === '{{ $status }}' ? 'border-primary-500 bg-primary-50 font-medium text-primary-700' : 'border-slate-200 text-slate-600 hover:border-primary-300'">
                                <input type="radio" name="status" value="{{ $status }}" x-model="status" class="sr-only">
                                {{ __('site.status.'.$status) }}
                            </label>
                        @endforeach
                    </div>

                    <div x-show="status === 'cancelled'" x-cloak>
                        <x-admin.input name="cancelled_reason" :label="__('admin.appointments.cancel_reason')"
                                       :value="$appointment->cancelled_reason"/>
                    </div>

                    <button type="submit" class="btn-primary w-full">{{ __('admin.actions.save') }}</button>
                </form>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-2">
                    <a href="tel:{{ $appointment->patient_phone }}" class="btn-secondary w-full">
                        <x-icon name="phone" class="h-4 w-4"/>{{ $appointment->patient_phone }}
                    </a>
                    <a href="{{ route('appointment.show', $appointment) }}" target="_blank" class="btn-ghost w-full">
                        <x-icon name="external-link" class="h-4 w-4"/>{{ __('admin.actions.view') }}
                    </a>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
