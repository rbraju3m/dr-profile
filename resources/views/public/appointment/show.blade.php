<x-layouts.public :title="__('site.booking.success_heading')">
    <section class="section bg-slate-50">
        <div class="container-page">
            <div class="mx-auto max-w-2xl">
                @if ($justBooked)
                    <div class="mb-6 text-center">
                        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-accent-100 text-accent-600">
                            <x-icon name="check-circle" class="h-9 w-9"/>
                        </span>
                        <h1 class="mt-4 text-2xl font-bold sm:text-3xl">{{ __('site.booking.success_heading') }}</h1>
                        <p class="mt-2 text-slate-500">{{ __('site.booking.success_text') }}</p>
                    </div>
                @else
                    <h1 class="mb-6 text-center text-2xl font-bold sm:text-3xl">{{ __('site.booking.lookup_heading') }}</h1>
                @endif

                <div class="card overflow-hidden">
                    <div class="bg-primary-900 px-6 py-5 text-center text-white">
                        <p class="text-xs uppercase tracking-widest text-primary-200">{{ __('site.booking.serial') }}</p>
                        <p class="mt-1 text-2xl font-bold tracking-wider">{{ $appointment->appointment_no }}</p>
                        <span @class([
                            'mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-amber-400 text-amber-950' => $appointment->status === 'pending',
                            'bg-accent-400 text-accent-950' => $appointment->status === 'confirmed',
                            'bg-white/20 text-white' => $appointment->status === 'completed',
                            'bg-rose-400 text-rose-950' => $appointment->status === 'cancelled',
                        ])>{{ __('site.status.'.$appointment->status) }}</span>
                    </div>

                    <dl class="divide-y divide-slate-100">
                        @foreach ([
                            ['calendar', __('site.booking.step_date'), bn_digits($appointment->appointment_date->format('j')).' '.__('site.months.'.$appointment->appointment_date->month).' '.bn_digits($appointment->appointment_date->format('Y')).' — '.App\Support\Week::name($appointment->appointment_date->dayOfWeek)],
                            ['clock', __('site.booking.step_slot'), $appointment->slotLabel()],
                            ['building', __('site.booking.step_chamber'), $appointment->chamber?->tr('name')],
                            ['map-pin', __('site.chamber.address'), $appointment->chamber?->tr('address')],
                            ['user', __('site.booking.patient_name'), $appointment->patient_name],
                            ['phone', __('site.booking.patient_phone'), bn_digits($appointment->patient_phone)],
                            ['stethoscope', __('site.booking.service'), $appointment->service?->tr('name')],
                            ['file-text', __('site.booking.notes'), $appointment->notes],
                        ] as [$icon, $label, $value])
                            @if ($value)
                                <div class="flex gap-4 px-6 py-4">
                                    <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                    <div class="min-w-0 flex-1">
                                        <dt class="text-xs text-slate-500">{{ $label }}</dt>
                                        <dd class="mt-0.5 text-sm font-medium leading-relaxed text-slate-800">{{ $value }}</dd>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </dl>

                    @if ($appointment->chamber?->tr('note'))
                        <div class="border-t border-slate-100 bg-amber-50 px-6 py-4 text-sm leading-relaxed text-amber-800">
                            <x-icon name="info" class="me-1 inline h-4 w-4 align-[-3px]"/>
                            {{ $appointment->chamber->tr('note') }}
                        </div>
                    @endif
                </div>

                @if (session('cancelled') || $appointment->status === 'cancelled')
                    <x-alert type="warning" class="mt-6">{{ __('site.booking.cancelled_notice') }}</x-alert>
                @endif

                @if ($errors->any())
                    <x-alert type="error" class="mt-6">{{ $errors->first() }}</x-alert>
                @endif

                {{-- Cancelling needs the phone number from the booking: the serial
                     is printed on a slip and is not proof of who is asking. --}}
                @if ($appointment->isCancellable())
                    <div class="no-print mt-6" x-data="{ open: false }">
                        <button type="button" @click="open = !open" x-show="!open"
                                class="btn-ghost w-full !text-rose-600 hover:!bg-rose-50">
                            <x-icon name="calendar-x" class="h-4 w-4"/>{{ __('site.booking.cancel') }}
                        </button>

                        <div x-show="open" x-cloak x-collapse>
                            <form method="POST" action="{{ route('appointment.cancel', $appointment) }}"
                                  class="card space-y-4 p-6">
                                @csrf
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900">{{ __('site.booking.cancel_heading') }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('site.booking.cancel_hint') }}</p>
                                </div>

                                <div>
                                    <label for="phone" class="field-label">{{ __('site.booking.patient_phone') }} <span class="text-rose-500">*</span></label>
                                    <input id="phone" name="phone" required inputmode="tel" autocomplete="tel"
                                           placeholder="01712345678" class="field-input tabular-nums">
                                </div>

                                <div>
                                    <label for="reason" class="field-label">{{ __('site.booking.cancel_reason') }}</label>
                                    <input id="reason" name="reason" class="field-input">
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="btn bg-rose-600 text-white hover:bg-rose-700">
                                        {{ __('site.booking.cancel_confirm') }}
                                    </button>
                                    <button type="button" @click="open = false" class="btn-ghost">
                                        {{ __('site.actions.back') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="no-print mt-6 flex flex-wrap justify-center gap-3">
                    <button type="button" onclick="window.print()" class="btn-secondary">
                        <x-icon name="printer" class="h-4 w-4"/>{{ __('site.actions.print') }}
                    </button>
                    @if ($appointment->chamber?->appointment_phone)
                        <a href="tel:{{ $appointment->chamber->appointment_phone }}" class="btn-secondary">
                            <x-icon name="phone" class="h-4 w-4"/>{{ __('site.actions.call_now') }}
                        </a>
                    @endif
                    <a href="{{ route('home') }}" class="btn-primary">{{ __('site.errors.go_home') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
