<x-layouts.public :title="__('site.faq.heading')">
    <x-page-hero :title="__('site.faq.heading')" :subtitle="__('site.faq.subheading')"
                 :breadcrumbs="[__('site.nav.faq') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($groups->isEmpty())
                <x-empty-state icon="info" :title="__('site.faq.empty')"/>
            @else
                <div class="mx-auto max-w-3xl space-y-12">
                    @foreach ($groups as $group => $faqs)
                        <div x-data x-reveal>
                            <h2 class="mb-5 text-xl font-bold tracking-tight">
                                {{ __('site.faq.groups.'.$group) === 'site.faq.groups.'.$group ? Str::headline($group) : __('site.faq.groups.'.$group) }}
                            </h2>
                            <x-faq-accordion :faqs="$faqs->values()"/>
                        </div>
                    @endforeach
                </div>

                <div class="mx-auto mt-12 max-w-3xl">
                    <div class="card relative flex flex-col items-center gap-5 overflow-hidden bg-primary-900 p-9 text-center text-white sm:flex-row sm:text-start">
                        <div aria-hidden="true" class="pointer-events-none absolute -end-16 -top-16 h-48 w-48 rounded-full bg-primary-600/40 blur-3xl"></div>
                        <div class="relative flex-1">
                            <h2 class="text-xl font-semibold text-white">{{ __('site.contact.form_heading') }}</h2>
                            <p class="mt-1.5 text-sm text-primary-100">{{ __('site.contact.subheading') }}</p>
                        </div>
                        @feature('contact')
                            <a href="{{ route('contact.create') }}" class="btn relative shrink-0 btn-invert">
                                {{ __('site.nav.contact') }}
                            </a>
                        @endfeature
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
