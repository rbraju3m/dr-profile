<x-layouts.public :title="__('site.booking.heading')">
    <x-page-hero :title="__('site.booking.heading')" :subtitle="__('site.booking.subheading')"
                 :breadcrumbs="[__('site.nav.appointment') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($chambers->isEmpty())
                <x-empty-state icon="calendar-x" :title="__('site.chamber.no_chambers')"/>
            @else
                <div
                    x-data="bookingWizard({
                        step: 1,
                        chamberId: {{ $selected?->id ?? 'null' }},
                        serviceId: {{ request()->integer('service') ?: 'null' }},
                        slotsUrl: @js(route('appointment.slots')),
                        calendars: @js($calendars),
                        maxDate: @js($maxDate),
                        chambers: @js($chambers->map(fn ($c) => [
                            'id' => $c->id,
                            'name' => $c->tr('name'),
                            'address' => $c->tr('address'),
                            'fee' => $c->consultation_fee,
                            'followup_fee' => $c->followup_fee,
                        ])->values()),
                        labels: @js([
                            'closed' => __('site.chamber.closed'),
                            'noSlots' => __('site.booking.no_slots'),
                            'loading' => __('site.actions.search'),
                        ]),
                        old: @js([
                            'chamber_id' => old('chamber_id'),
                            'appointment_date' => old('appointment_date'),
                            'slot_time' => old('slot_time'),
                        ]),
                    })"
                    x-cloak
                    class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-12">

                    {{-- ---------------- Steps ---------------- --}}
                    <div class="lg:col-span-8">
                        <ol class="mb-6 flex items-center gap-1 overflow-x-auto scrollbar-none">
                            @foreach ([
                                1 => __('site.booking.step_chamber'),
                                2 => __('site.booking.step_date'),
                                3 => __('site.booking.step_slot'),
                                4 => __('site.booking.step_details'),
                            ] as $number => $label)
                                <li class="flex shrink-0 items-center gap-1">
                                    <button type="button" @click="goToStep({{ $number }})"
                                            :disabled="!canReach({{ $number }})"
                                            :class="step === {{ $number }}
                                                ? 'bg-primary-600 text-white'
                                                : (canReach({{ $number }}) ? 'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:ring-primary-300' : 'bg-slate-100 text-slate-400 cursor-not-allowed')"
                                            class="flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium transition">
                                        <span class="grid h-5 w-5 place-items-center rounded-full text-[11px] font-bold tabular-nums"
                                              :class="step === {{ $number }} ? 'bg-white/25' : 'bg-slate-100'">
                                            <template x-if="step > {{ $number }}">
                                                <span>✓</span>
                                            </template>
                                            <template x-if="step <= {{ $number }}">
                                                <span>{{ bn_digits($number) }}</span>
                                            </template>
                                        </span>
                                        <span class="hidden sm:inline">{{ $label }}</span>
                                    </button>
                                    @if ($number < 4)
                                        <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-slate-300 rtl:rotate-180"/>
                                    @endif
                                </li>
                            @endforeach
                        </ol>

                        @if ($errors->any())
                            <x-alert type="error" class="mb-5" :title="__('site.errors.500_title')">
                                <ul class="list-disc space-y-0.5 ps-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-alert>
                        @endif

                        <form method="POST" action="{{ route('appointment.store') }}" class="card p-6 sm:p-8" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="chamber_id" :value="chamberId">
                            <input type="hidden" name="appointment_date" :value="date">
                            <input type="hidden" name="slot_time" :value="slot">

                            {{-- Step 1: chamber --}}
                            <div x-show="step === 1" x-transition.opacity>
                                <h2 class="text-lg font-semibold">{{ __('site.booking.select_chamber') }}</h2>

                                <div class="mt-5 space-y-3">
                                    @foreach ($chambers as $chamber)
                                        <label class="flex cursor-pointer items-start gap-4 rounded-2xl border p-4 transition"
                                               :class="chamberId === {{ $chamber->id }} ? 'border-primary-500 bg-primary-50/50 ring-1 ring-primary-500' : 'border-slate-200 hover:border-primary-300'">
                                            <input type="radio" name="chamber_choice" value="{{ $chamber->id }}"
                                                   x-model.number="chamberId" @change="onChamberChange()"
                                                   class="mt-1 h-4 w-4 shrink-0 text-primary-600 focus:ring-primary-500">
                                            <span class="min-w-0 flex-1">
                                                <span class="block font-semibold text-slate-900">{{ $chamber->tr('name') }}</span>
                                                <span class="mt-0.5 block text-sm leading-relaxed text-slate-500">{{ $chamber->tr('address') }}</span>
                                                @if ($chamber->consultation_fee)
                                                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                                        {{ __('site.chamber.consultation_fee') }}: {{ App\Support\Number::money($chamber->consultation_fee) }}
                                                    </span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @if ($closedChambers->isNotEmpty())
                                    <x-alert type="info" class="mt-5">
                                        {{ __('site.chamber.online_booking_off') }}
                                        <ul class="mt-1.5 space-y-0.5">
                                            @foreach ($closedChambers as $chamber)
                                                <li class="font-medium">
                                                    {{ $chamber->tr('name') }}
                                                    @if ($chamber->phone) — <a href="tel:{{ $chamber->phone }}" class="underline tabular-nums">{{ bn_digits($chamber->phone) }}</a>@endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </x-alert>
                                @endif
                            </div>

                            {{-- Step 2: date --}}
                            <div x-show="step === 2" x-transition.opacity>
                                <h2 class="text-lg font-semibold">{{ __('site.booking.select_date') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ setting('appointment_notice_'.app()->getLocale()) }}</p>

                                <div class="mt-5 grid grid-cols-3 gap-2.5 sm:grid-cols-4 lg:grid-cols-5">
                                    <template x-for="day in currentCalendar()" :key="day.date">
                                        <button type="button" @click="selectDate(day)" :disabled="!day.open"
                                                :class="day.date === date
                                                    ? 'border-primary-600 bg-primary-600 text-white'
                                                    : (day.open ? 'border-slate-200 bg-white hover:border-primary-400' : 'border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed')"
                                                class="rounded-xl border p-3 text-center transition">
                                            <span class="block text-[11px] opacity-80" x-text="day.dayName"></span>
                                            <span class="block text-lg font-semibold tabular-nums" x-text="day.dayNumber"></span>
                                            <span class="block text-[10px] opacity-80"
                                                  x-text="day.open ? day.count + ' ' + '{{ __('site.booking.step_slot') }}' : '{{ __('site.chamber.closed') }}'"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Step 3: slot --}}
                            <div x-show="step === 3" x-transition.opacity>
                                <h2 class="text-lg font-semibold">{{ __('site.booking.available_slots') }}</h2>
                                <p class="mt-1 text-sm text-slate-500" x-text="humanDate()"></p>

                                <div x-show="loading" class="mt-6 grid grid-cols-3 gap-2.5 sm:grid-cols-4">
                                    <template x-for="i in 8" :key="i">
                                        <div class="h-11 animate-pulse rounded-xl bg-slate-100"></div>
                                    </template>
                                </div>

                                <div x-show="!loading">
                                    <template x-if="slots.length === 0">
                                        <div class="mt-6 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800"
                                             x-text="closedReason || labels.noSlots"></div>
                                    </template>

                                    <div class="mt-6 grid grid-cols-3 gap-2.5 sm:grid-cols-4" x-show="slots.length > 0">
                                        <template x-for="s in slots" :key="s.time">
                                            <button type="button" @click="selectSlot(s)" :disabled="s.taken"
                                                    :class="s.taken ? 'slot-taken' : (slot === s.time ? 'slot-selected' : 'slot-free')"
                                                    x-text="s.label"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4: details --}}
                            <div x-show="step === 4" x-transition.opacity>
                                <h2 class="text-lg font-semibold">{{ __('site.booking.step_details') }}</h2>

                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="patient_name" class="field-label">{{ __('site.booking.patient_name') }} <span class="text-rose-500">*</span></label>
                                        <input id="patient_name" name="patient_name" value="{{ old('patient_name') }}" required
                                               class="field-input @error('patient_name') ring-rose-400 @enderror" autocomplete="name">
                                        @error('patient_name') <p class="field-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="patient_phone" class="field-label">{{ __('site.booking.patient_phone') }} <span class="text-rose-500">*</span></label>
                                        <input id="patient_phone" name="patient_phone" value="{{ old('patient_phone') }}" required
                                               inputmode="tel" placeholder="01712345678"
                                               class="field-input tabular-nums @error('patient_phone') ring-rose-400 @enderror" autocomplete="tel">
                                        @error('patient_phone') <p class="field-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="patient_email" class="field-label">{{ __('site.booking.patient_email') }}</label>
                                        <input id="patient_email" name="patient_email" type="email" value="{{ old('patient_email') }}"
                                               class="field-input @error('patient_email') ring-rose-400 @enderror" autocomplete="email">
                                        @error('patient_email') <p class="field-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="patient_age" class="field-label">{{ __('site.booking.patient_age') }}</label>
                                        <input id="patient_age" name="patient_age" type="number" min="0" max="130" value="{{ old('patient_age') }}"
                                               class="field-input tabular-nums">
                                        @error('patient_age') <p class="field-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="patient_gender" class="field-label">{{ __('site.booking.patient_gender') }}</label>
                                        <select id="patient_gender" name="patient_gender" class="field-input">
                                            <option value="">—</option>
                                            @foreach (['male', 'female', 'other'] as $gender)
                                                <option value="{{ $gender }}" @selected(old('patient_gender') === $gender)>
                                                    {{ __('admin.gender.'.$gender) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <span class="field-label">{{ __('site.booking.visit_type') }}</span>
                                        <div class="grid grid-cols-2 gap-3">
                                            @foreach (['new' => __('site.booking.visit_new'), 'followup' => __('site.booking.visit_followup')] as $value => $label)
                                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-4 py-2.5 text-sm transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                                                    <input type="radio" name="visit_type" value="{{ $value }}"
                                                           @checked(old('visit_type', 'new') === $value)
                                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500">
                                                    {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="service_id" class="field-label">{{ __('site.booking.service') }}</label>
                                        <select id="service_id" name="service_id" class="field-input">
                                            <option value="">—</option>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}" @selected(old('service_id', request()->integer('service')) == $service->id)>
                                                    {{ $service->tr('name') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="patient_address" class="field-label">{{ __('site.booking.patient_address') }}</label>
                                        <input id="patient_address" name="patient_address" value="{{ old('patient_address') }}" class="field-input">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="notes" class="field-label">{{ __('site.booking.notes') }}</label>
                                        <textarea id="notes" name="notes" rows="3" class="field-input">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Navigation --}}
                            <div class="mt-8 flex items-center justify-between gap-3 border-t border-slate-100 pt-6">
                                <button type="button" @click="back()" x-show="step > 1" class="btn-ghost">
                                    <x-icon name="arrow-left" class="h-4 w-4 rtl:rotate-180"/>{{ __('site.actions.back') }}
                                </button>
                                <span x-show="step === 1"></span>

                                <button type="button" @click="next()" x-show="step < 4" :disabled="!canAdvance()" class="btn-primary">
                                    {{ __('site.actions.next') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                                </button>

                                <button type="submit" x-show="step === 4" :disabled="submitting" class="btn-primary btn-lg">
                                    <x-icon name="check" class="h-5 w-5"/>
                                    <span x-text="submitting ? '…' : @js(__('site.booking.confirm_booking'))"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ---------------- Summary ---------------- --}}
                    <aside class="lg:col-span-4">
                        <div class="card p-6 lg:sticky lg:top-28">
                            <h2 class="text-base font-semibold">{{ __('site.booking.summary') }}</h2>

                            <dl class="mt-4 space-y-3.5 text-sm">
                                <div class="flex gap-3">
                                    <x-icon name="building" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                    <div class="min-w-0">
                                        <dt class="text-xs text-slate-500">{{ __('site.booking.step_chamber') }}</dt>
                                        <dd class="font-medium text-slate-800" x-text="selectedChamber()?.name || '—'"></dd>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <x-icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                    <div class="min-w-0">
                                        <dt class="text-xs text-slate-500">{{ __('site.booking.step_date') }}</dt>
                                        <dd class="font-medium text-slate-800" x-text="humanDate() || '—'"></dd>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                    <div class="min-w-0">
                                        <dt class="text-xs text-slate-500">{{ __('site.booking.step_slot') }}</dt>
                                        <dd class="font-medium tabular-nums text-slate-800" x-text="slotLabel || '—'"></dd>
                                    </div>
                                </div>
                            </dl>

                            <template x-if="selectedChamber()?.fee">
                                <div class="mt-5 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="text-slate-500">{{ __('site.chamber.consultation_fee') }}</span>
                                    <span class="font-semibold text-slate-900" x-text="'৳ ' + selectedChamber().fee.toLocaleString()"></span>
                                </div>
                            </template>

                            <p class="mt-5 rounded-xl bg-primary-50 px-4 py-3 text-xs leading-relaxed text-primary-800">
                                <x-icon name="info" class="me-1 inline h-3.5 w-3.5 align-[-2px]"/>
                                {{ __('site.booking.success_text') }}
                            </p>

                            <a href="{{ route('appointment.lookup') }}" class="mt-4 block text-center text-xs font-medium text-primary-600 hover:text-primary-800">
                                {{ __('site.booking.lookup_heading') }}
                            </a>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
        <script>
            const DAY_NAMES = @js(collect(App\Support\Week::DAYS)->map(fn ($d) => App\Support\Week::shortName($d))->all());
            const MONTH_NAMES = @js(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => __('site.months.'.$m)])->all());
            const LOCALE = @js(app()->getLocale());

            const toLocalDigits = (value) => LOCALE === 'bn'
                ? String(value).replace(/[0-9]/g, (d) => '০১২৩৪৫৬৭৮৯'[d])
                : String(value);

            document.addEventListener('alpine:init', () => {
                Alpine.data('bookingWizard', (config) => ({
                    ...config,
                    date: config.old.appointment_date || null,
                    slot: config.old.slot_time || null,
                    slotLabel: null,
                    slots: [],
                    closedReason: null,
                    loading: false,
                    submitting: false,

                    init() {
                        if (this.old.chamber_id) this.chamberId = Number(this.old.chamber_id)

                        // Returning from a validation error: jump back to the details step.
                        if (this.date && this.slot) {
                            this.fetchSlots().then(() => { this.step = 4 })
                        }
                    },

                    selectedChamber() {
                        return this.chambers.find((c) => c.id === this.chamberId) || null
                    },

                    currentCalendar() {
                        const days = this.calendars[this.chamberId] || []

                        return days.map((day) => {
                            const parsed = new Date(day.date + 'T00:00:00')
                            return {
                                ...day,
                                dayName: DAY_NAMES[parsed.getDay()],
                                dayNumber: toLocalDigits(parsed.getDate()),
                                count: toLocalDigits(day.count),
                            }
                        })
                    },

                    humanDate() {
                        if (!this.date) return ''
                        const d = new Date(this.date + 'T00:00:00')
                        return `${DAY_NAMES[d.getDay()]}, ${toLocalDigits(d.getDate())} ${MONTH_NAMES[d.getMonth() + 1]} ${toLocalDigits(d.getFullYear())}`
                    },

                    onChamberChange() {
                        this.date = null
                        this.slot = null
                        this.slotLabel = null
                        this.slots = []
                    },

                    selectDate(day) {
                        if (!day.open) return
                        this.date = day.date
                        this.slot = null
                        this.slotLabel = null
                        this.next()
                    },

                    selectSlot(slot) {
                        if (slot.taken) return
                        this.slot = slot.time
                        this.slotLabel = slot.label
                    },

                    async fetchSlots() {
                        if (!this.chamberId || !this.date) return

                        this.loading = true
                        this.closedReason = null

                        try {
                            const url = new URL(this.slotsUrl, window.location.origin)
                            url.searchParams.set('chamber_id', this.chamberId)
                            url.searchParams.set('date', this.date)

                            const response = await fetch(url, { headers: { Accept: 'application/json' } })
                            const data = await response.json()

                            this.slots = data.slots || []
                            this.closedReason = data.reason
                            this.slotLabel = this.slots.find((s) => s.time === this.slot)?.label || null
                        } catch (e) {
                            this.slots = []
                        } finally {
                            this.loading = false
                        }
                    },

                    canReach(step) {
                        if (step <= 1) return true
                        if (step === 2) return !!this.chamberId
                        if (step === 3) return !!this.chamberId && !!this.date
                        return !!this.chamberId && !!this.date && !!this.slot
                    },

                    canAdvance() {
                        return this.canReach(this.step + 1)
                    },

                    goToStep(step) {
                        if (!this.canReach(step)) return
                        this.step = step
                        if (step === 3) this.fetchSlots()
                    },

                    next() {
                        if (!this.canAdvance()) return
                        this.step += 1
                        if (this.step === 3) this.fetchSlots()
                    },

                    back() {
                        if (this.step > 1) this.step -= 1
                    },
                }))
            })
        </script>
    @endpush
</x-layouts.public>
