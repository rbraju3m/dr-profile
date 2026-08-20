<x-layouts.public :title="__('site.errors.500_title')">
    <section class="section bg-slate-50">
        <div class="container-page">
            <div class="mx-auto max-w-md text-center">
                <p class="text-7xl font-bold tabular-nums text-primary-200">{{ bn_digits('503') }}</p>
                <h1 class="mt-4 text-2xl font-bold sm:text-3xl">{{ __('site.errors.500_title') }}</h1>
                <p class="mt-3 text-slate-500">{{ __('site.errors.500_text') }}</p>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('home') }}" class="btn-primary">{{ __('site.errors.go_home') }}</a>
                    <a href="{{ route('appointment.create') }}" class="btn-secondary">{{ __('site.actions.book_appointment') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
