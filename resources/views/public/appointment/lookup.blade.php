<x-layouts.public :title="__('site.booking.lookup_heading')">
    <x-page-hero :title="__('site.booking.lookup_heading')" :subtitle="__('site.booking.lookup_hint')"
                 :breadcrumbs="[__('site.nav.appointment') => route('appointment.create'), __('site.booking.lookup_heading') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            <div class="mx-auto max-w-md">
                <form method="GET" action="{{ route('appointment.lookup') }}" class="card p-6 sm:p-8">
                    <label for="serial" class="field-label">{{ __('site.booking.serial') }}</label>
                    <input id="serial" name="serial" value="{{ $serial }}" required
                           placeholder="APT-20260820-A7K3" autocomplete="off"
                           class="field-input uppercase tracking-wider">

                    @if ($error)
                        <p class="field-error">{{ $error }}</p>
                    @endif

                    <button type="submit" class="btn-primary mt-5 w-full">
                        <x-icon name="search" class="h-4 w-4"/>{{ __('site.actions.search') }}
                    </button>
                </form>

                <p class="mt-4 text-center text-sm text-slate-500">
                    {{ __('site.booking.lookup_hint') }}
                </p>
            </div>
        </div>
    </section>
</x-layouts.public>
