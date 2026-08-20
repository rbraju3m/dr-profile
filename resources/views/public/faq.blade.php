<x-layouts.public :title="__('site.faq.heading')">
    <x-page-hero :title="__('site.faq.heading')" :subtitle="__('site.faq.subheading')"
                 :breadcrumbs="[__('site.nav.faq') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($groups->isEmpty())
                <x-empty-state icon="info" :title="__('site.faq.empty')"/>
            @else
                <div class="mx-auto max-w-3xl space-y-10">
                    @foreach ($groups as $group => $faqs)
                        <div>
                            <h2 class="mb-4 text-lg font-bold">
                                {{ __('site.faq.groups.'.$group) === 'site.faq.groups.'.$group ? Str::headline($group) : __('site.faq.groups.'.$group) }}
                            </h2>
                            <x-faq-accordion :faqs="$faqs->values()"/>
                        </div>
                    @endforeach
                </div>

                <div class="mx-auto mt-12 max-w-3xl">
                    <div class="card flex flex-col items-center gap-4 bg-primary-900 p-8 text-center text-white sm:flex-row sm:text-start">
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold text-white">{{ __('site.contact.form_heading') }}</h2>
                            <p class="mt-1 text-sm text-primary-100">{{ __('site.contact.subheading') }}</p>
                        </div>
                        @feature('contact')
                            <a href="{{ route('contact.create') }}" class="btn shrink-0 btn-invert">
                                {{ __('site.nav.contact') }}
                            </a>
                        @endfeature
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
