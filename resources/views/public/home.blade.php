<x-layouts.public>
    @php $locale = app()->getLocale(); @endphp

    {{-- ============================ Hero ============================ --}}
    @php
        // Slides drive the headline, the lead and the backdrop together. With
        // none configured the hero falls back to the profile's own wording.
        $slides = $sliders->values();
        $hasSlides = $slides->isNotEmpty();
    @endphp

    <section
        x-data="heroCarousel({{ $hasSlides ? $slides->count() : 1 }})"
        x-init="start()"
        @mouseenter="pause()" @mouseleave="resume()"
        @focusin="pause()" @focusout="resume()"
        class="relative overflow-hidden bg-primary-950"
        role="region" aria-roledescription="carousel"
        aria-label="{{ __('site.home.hero_greeting') }}">

        {{-- Backdrop: one layer per slide, cross-fading --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -end-40 -top-40 h-[30rem] w-[30rem] rounded-full bg-primary-600/30 blur-3xl"></div>
            <div class="absolute -bottom-48 -start-24 h-[26rem] w-[26rem] rounded-full bg-accent-500/20 blur-3xl"></div>

            @foreach ($slides as $i => $slide)
                @if ($slide->imageUrl())
                    <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms class="absolute inset-0">
                        <picture>
                            @if ($slide->mobileImageUrl() && $slide->mobileImageUrl() !== $slide->imageUrl())
                                <source media="(max-width: 640px)" srcset="{{ $slide->mobileImageUrl() }}">
                            @endif
                            <img src="{{ $slide->imageUrl() }}" alt=""
                                 class="h-full w-full object-cover"
                                 @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                        </picture>
                        {{-- Keeps the headline readable over any photograph --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-primary-950/95 via-primary-950/80 to-primary-950/40"></div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="container-page relative grid items-center gap-12 py-16 lg:grid-cols-12 lg:py-24">
            <div class="lg:col-span-7">
                @if ($doctor->tr('degrees'))
                    <span class="eyebrow !text-primary-300">
                        <span class="h-px w-6 bg-primary-400"></span>{{ $doctor->tr('degrees') }}
                    </span>
                @endif

                @if ($hasSlides)
                    <div aria-live="polite">
                        @foreach ($slides as $i => $slide)
                            <div x-show="current === {{ $i }}"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 role="group" aria-roledescription="slide">
                                <h1 class="mt-4 text-3xl font-bold leading-tight text-balance text-white sm:text-4xl lg:text-5xl">
                                    {{ $slide->tr('title') ?: $doctor->fullName() }}
                                </h1>
                                @if ($slide->tr('subtitle'))
                                    <p class="mt-5 max-w-xl text-base leading-relaxed text-primary-100/90">
                                        {{ $slide->tr('subtitle') }}
                                    </p>
                                @endif
                                @if ($slide->cta_url && $slide->tr('cta_label'))
                                    <a href="{{ $slide->cta_url }}" class="btn-secondary mt-6 !bg-white/10 !text-white !ring-white/25 hover:!bg-white/20">
                                        {{ $slide->tr('cta_label') }}
                                        <x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <h1 class="mt-4 text-3xl font-bold leading-tight text-balance text-white sm:text-4xl lg:text-5xl">
                        {{ $doctor->tr('tagline')
                            ?: ($doctor->fullName() ?: setting('site_name_'.$locale) ?: __('site.home.hero_greeting')) }}
                    </h1>

                    @if ($doctor->tr('short_bio'))
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-primary-100/90">
                            {{ Str::limit(strip_tags((string) $doctor->tr('short_bio')), 220) }}
                        </p>
                    @endif
                @endif

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('appointment.create') }}" class="btn-primary btn-lg">
                        <x-icon name="calendar-check" class="h-5 w-5"/>
                        {{ __('site.actions.book_appointment') }}
                    </a>
                    <a href="{{ route('about') }}" class="btn btn-lg bg-white/10 text-white ring-1 ring-inset ring-white/20 hover:bg-white/20">
                        {{ __('site.home.hero_cta_secondary') }}
                        <x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                    </a>
                </div>

                @if ($doctor->hotline)
                    <p class="mt-6 text-sm text-primary-200">
                        <x-icon name="phone" class="me-1 inline h-4 w-4 align-[-3px]"/>
                        {{ __('site.contact.hotline') }}
                        <a href="tel:{{ $doctor->hotline }}" class="font-semibold tabular-nums text-white underline underline-offset-4">{{ bn_digits($doctor->hotline) }}</a>
                    </p>
                @endif

                {{-- Slide controls, only worth showing when there is more than one --}}
                @if ($slides->count() > 1)
                    <div class="mt-8 flex items-center gap-3">
                        <button type="button" @click="prev()"
                                class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/25"
                                aria-label="{{ __('site.actions.previous') }}">
                            <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180"/>
                        </button>

                        <div class="flex gap-1.5" role="tablist">
                            @foreach ($slides as $i => $slide)
                                <button type="button" @click="go({{ $i }})" role="tab"
                                        :aria-selected="current === {{ $i }}"
                                        :class="current === {{ $i }} ? 'w-6 bg-white' : 'w-2 bg-white/60 hover:bg-white/90'"
                                        class="h-2 rounded-full transition-all"
                                        aria-label="{{ $i + 1 }}"></button>
                            @endforeach
                        </div>

                        <button type="button" @click="next()"
                                class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/25"
                                aria-label="{{ __('site.actions.next') }}">
                            <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180"/>
                        </button>
                    </div>
                @endif
            </div>

            {{-- Doctor card --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-sm">
                    <div class="overflow-hidden rounded-3xl bg-white shadow-2xl">
                        <x-media-frame :src="$doctor->photoUrl()" :alt="$doctor->fullName()" icon="stethoscope"
                                       ratio="aspect-[4/5]" :label="$doctor->tr('name')" :seed="$doctor->tr('name')"/>

                        <div class="p-5">
                            <p class="text-lg font-semibold text-slate-900">{{ $doctor->fullName() }}</p>
                            <p class="mt-0.5 text-sm text-primary-700">{{ $doctor->tr('designation') }}</p>

                            <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                                @if ($doctor->experience_years)
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('site.about.experience_years') }}</dt>
                                        <dd class="font-semibold tabular-nums text-slate-900">{{ bn_digits($doctor->experience_years) }}+</dd>
                                    </div>
                                @endif
                                @if ($doctor->bmdc_reg_no)
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('site.about.registration') }}</dt>
                                        <dd class="font-semibold text-slate-900">{{ $doctor->bmdc_reg_no }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    @php $badgeValue = $stats->first()?->value ?: $doctor->experience_years; @endphp
                    @if ($badgeValue)
                        <div class="absolute -bottom-5 -start-5 hidden rounded-2xl bg-accent-600 px-5 py-4 text-white shadow-xl sm:block">
                            <p class="text-2xl font-bold tabular-nums">{{ bn_digits($badgeValue) }}+</p>
                            <p class="text-xs opacity-90">{{ $stats->first()?->tr('label') ?? __('site.about.experience_years') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('heroCarousel', (count) => ({
                    current: 0,
                    timer: null,
                    count,

                    start() {
                        // A hero that moves on its own is exactly what
                        // prefers-reduced-motion is asking us not to do.
                        const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches
                        if (this.count < 2 || still) return
                        this.timer = setInterval(() => this.next(), 6000)
                    },
                    pause() { clearInterval(this.timer); this.timer = null },
                    resume() { if (!this.timer) this.start() },
                    go(i) { this.current = i; this.pause(); this.resume() },
                    next() { this.current = (this.current + 1) % this.count },
                    prev() { this.current = (this.current - 1 + this.count) % this.count },
                }))
            })
        </script>
    @endpush

    {{-- ============================ Stats ============================ --}}
    @if ($stats->isNotEmpty())
        <section class="border-b border-slate-200 bg-white">
            <div x-data x-reveal.stagger class="container-page grid grid-cols-2 gap-6 py-10 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary-600">
                            <x-icon :name="$stat->icon ?: 'activity'" class="h-6 w-6"/>
                        </span>
                        <div class="min-w-0">
                            <p class="text-2xl font-bold tabular-nums text-slate-900" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $stat->tr('label') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================ About ============================ --}}
    @if ($doctor->tr('bio') || $doctor->tr('short_bio'))
    <section class="section bg-slate-50">
        <div class="container-page grid items-center gap-12 lg:grid-cols-2">
            <div class="relative">
                <div class="overflow-hidden rounded-3xl">
                    <x-media-frame :src="$doctor->heroImageUrl() ?? $doctor->photoUrl()" :alt="$doctor->fullName()"
                                   icon="stethoscope" ratio="aspect-[4/3]"
                                   :label="$doctor->tr('name')" seed="doctor-hero"/>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3">
                    @foreach ($stats->skip(1)->take(3) as $stat)
                        <div class="card p-4 text-center">
                            <p class="text-lg font-bold tabular-nums text-primary-700" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                            <p class="mt-0.5 text-[11px] leading-tight text-slate-500">{{ $stat->tr('label') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <x-section-heading align="start" :eyebrow="__('site.nav.about')" :title="__('site.home.about_heading')"/>

                <div class="prose-content -mt-6">
                    {!! Str::limit(strip_tags((string) $doctor->tr('bio'), '<p><strong><em>'), 700, '…</p>') !!}
                </div>

                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach (['badge-check' => $doctor->tr('degrees'), 'globe' => $doctor->tr('languages'), 'file-text' => $doctor->bmdc_reg_no ? __('site.about.registration').': '.$doctor->bmdc_reg_no : null] as $icon => $value)
                        @if ($value)
                            <li class="flex items-start gap-2.5 text-sm text-slate-600">
                                <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0 text-accent-600"/>
                                <span>{{ $value }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('about') }}" class="btn-primary">
                        {{ __('site.actions.read_more') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                    </a>
                    @if ($doctor->cv_file)
                        <a href="{{ $doctor->mediaUrl('cv_file') }}" class="btn-secondary" download>
                            <x-icon name="download" class="h-4 w-4"/>{{ __('site.actions.download_cv') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @endif

    {{-- ============================ Expertise ============================ --}}
    @if ($services->isNotEmpty())
        <section class="section bg-white">
            <div class="container-page">
                <x-section-heading :eyebrow="__('site.nav.services')" :title="__('site.home.expertise_heading')"
                                   :subtitle="__('site.home.expertise_subheading')"/>

                <div x-data x-reveal.stagger class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <x-service-card :service="$service"/>
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <a href="{{ route('services.index') }}" class="btn-secondary">
                        {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ Chambers ============================ --}}
    @if ($chambers->isNotEmpty())
        <section class="section bg-slate-50">
            <div class="container-page">
                <x-section-heading :eyebrow="__('site.nav.chambers')" :title="__('site.home.chambers_heading')"
                                   :subtitle="__('site.home.chambers_subheading')"/>

                <div x-data x-reveal.stagger class="grid gap-6 lg:grid-cols-3">
                    @foreach ($chambers as $chamber)
                        <x-chamber-card :chamber="$chamber" :next-date="$nextDates[$chamber->id] ?? null"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ Booking steps ============================ --}}
    <section class="section bg-white">
        <div class="container-page">
            <x-section-heading :title="__('site.home.steps_heading')"/>

            <div x-data x-reveal.stagger class="grid gap-6 md:grid-cols-3">
                @foreach ([1, 2, 3] as $step)
                    <div class="relative card p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary-600 text-lg font-bold text-white tabular-nums">
                            {{ bn_digits($step) }}
                        </span>
                        <h3 class="mt-4 text-base font-semibold">{{ __("site.home.step_{$step}_title") }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ __("site.home.step_{$step}_text") }}</p>

                        @if ($step < 3)
                            <x-icon name="arrow-right" class="absolute -end-3 top-1/2 hidden h-6 w-6 -translate-y-1/2 text-slate-300 md:block rtl:rotate-180"/>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ Success stories ============================ --}}
    @if ($stories->isNotEmpty())
        <section class="section bg-slate-50">
            <div class="container-page">
                <x-section-heading align="between" :eyebrow="__('site.nav.success_stories')"
                                   :title="__('site.home.stories_heading')" :subtitle="__('site.home.stories_subheading')">
                    <x-slot:action>
                        <a href="{{ route('stories.index') }}" class="btn-secondary">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </x-slot:action>
                </x-section-heading>

                <div x-data x-reveal.stagger class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stories as $story)
                        <x-story-card :story="$story"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ Testimonials ============================ --}}
    @if ($testimonials->isNotEmpty())
        <section class="section bg-white">
            <div class="container-page">
                <x-section-heading :title="__('site.home.testimonials_heading')"/>

                <div x-data x-reveal.stagger class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials->take(3) as $testimonial)
                        <x-testimonial-card :testimonial="$testimonial"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ News, events, articles ============================ --}}
    @if ($news->isNotEmpty() || $articles->isNotEmpty())
        <section class="section bg-slate-50">
            <div class="container-page space-y-16">
                @if ($news->isNotEmpty())
                    <div>
                        <x-section-heading align="between" :eyebrow="__('site.nav.news_events')"
                                           :title="__('site.home.news_heading')" :subtitle="__('site.home.news_subheading')">
                            <x-slot:action>
                                <a href="{{ route('news.index') }}" class="btn-secondary">
                                    {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                                </a>
                            </x-slot:action>
                        </x-section-heading>

                        <div x-data x-reveal.stagger class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ($news as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($articles->isNotEmpty())
                    <div>
                        <x-section-heading align="between" :eyebrow="__('site.nav.blog')"
                                           :title="__('site.home.blog_heading')" :subtitle="__('site.home.blog_subheading')">
                            <x-slot:action>
                                <a href="{{ route('blog.index') }}" class="btn-secondary">
                                    {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                                </a>
                            </x-slot:action>
                        </x-section-heading>

                        <div x-data x-reveal.stagger class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ($articles as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ============================ FAQ ============================ --}}
    @if ($faqs->isNotEmpty())
        <section class="section bg-white">
            <div class="container-page grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <x-section-heading align="start" :eyebrow="__('site.nav.faq')" :title="__('site.home.faq_heading')"
                                       :subtitle="__('site.faq.subheading')"/>
                    <a href="{{ route('faq.index') }}" class="btn-secondary -mt-4">
                        {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="h-4 w-4 rtl:rotate-180"/>
                    </a>
                </div>

                <div class="lg:col-span-8">
                    <x-faq-accordion :faqs="$faqs"/>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ Final CTA ============================ --}}
    <section class="bg-primary-900">
        <div class="container-page py-14">
            <div class="flex flex-col items-center justify-between gap-6 text-center lg:flex-row lg:text-start">
                <div>
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">{{ __('site.home.cta_heading') }}</h2>
                    <p class="mt-2 max-w-xl text-primary-100">{{ __('site.home.cta_text') }}</p>
                </div>

                <div class="flex shrink-0 flex-wrap justify-center gap-3">
                    <a href="{{ route('appointment.create') }}" class="btn-lg btn bg-white text-primary-800 hover:bg-primary-50">
                        <x-icon name="calendar-check" class="h-5 w-5"/>{{ __('site.actions.book_appointment') }}
                    </a>
                    @if ($doctor->phone)
                        <a href="tel:{{ preg_replace('/\s/', '', $doctor->phone) }}"
                           class="btn-lg btn bg-white/10 text-white ring-1 ring-inset ring-white/25 hover:bg-white/20">
                            <x-icon name="phone" class="h-5 w-5"/>{{ __('site.actions.call_now') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
