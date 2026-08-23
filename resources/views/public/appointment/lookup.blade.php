<x-layouts.public :title="__('site.booking.lookup_heading')">
    <x-page-hero :title="__('site.booking.lookup_heading')" :subtitle="__('site.booking.lookup_hint')"
                 :breadcrumbs="[__('site.nav.appointment') => route('appointment.create'), __('site.booking.lookup_heading') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            <div class="mx-auto max-w-md">
                {{--
                    Serial *and* number. The serial is printed on a slip and sent by
                    email, so on its own it is not proof that the record is yours —
                    and the record carries everything the patient typed.
                --}}
                <form method="POST" action="{{ route('appointment.lookup.find') }}" class="card p-6 sm:p-8">
                    @csrf

                    <label for="serial" class="field-label">{{ __('site.booking.serial') }}</label>
                    <input id="serial" name="serial" value="{{ old('serial', $serial) }}" required
                           placeholder="APT-20260820-A7K3M2" autocomplete="off"
                           class="field-input uppercase tracking-wider">

                    <label for="phone" class="field-label mt-4">{{ __('site.booking.patient_phone') }}</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                           placeholder="01712345678" autocomplete="tel" inputmode="numeric"
                           class="field-input">

                    @error('serial')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-primary mt-5 w-full">
                        <x-icon name="search" class="h-4 w-4"/>{{ __('site.actions.search') }}
                    </button>
                </form>

                <p class="mt-4 text-center text-sm text-slate-500">
                    {{ __('site.booking.lookup_protected') }}
                </p>
            </div>
        </div>
    </section>
</x-layouts.public>
