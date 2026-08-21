<x-layouts.public>
    @php $locale = app()->getLocale(); @endphp

    {{-- ============================ Hero ============================ --}}
    @php
        // Slides drive the headline, the lead and the backdrop together. With
        // none configured the hero falls back to the profile's own wording.
        $slides = $sliders->values();
        $hasSlides = $slides->isNotEmpty();
    @endphp

    @feature('home_hero')
        <section
            x-data="heroCarousel({{ $hasSlides ? $slides->count() : 1 }})"
            x-init="start()"
            @mouseenter="pause()" @mouseleave="resume()"
            @focusin="pause()" @focusout="resume()"
            class="relative overflow-hidden bg-primary-950"
            role="region" aria-roledescription="carousel"
            aria-label="{{ __('site.home.hero_greeting') }}">

            {{-- Backdrop: one layer per slide, cross-fading, each photograph
                 drifting slowly the first time it is shown. --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -end-40 -top-40 h-[34rem] w-[34rem] rounded-full bg-primary-600/30 blur-3xl"></div>
                <div class="absolute -bottom-52 -start-32 h-[28rem] w-[28rem] rounded-full bg-accent-500/20 blur-3xl"></div>

                @foreach ($slides as $i => $slide)
                    @if ($slide->imageUrl())
                        <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms class="absolute inset-0">
                            <picture>
                                @if ($slide->mobileImageUrl() && $slide->mobileImageUrl() !== $slide->imageUrl())
                                    <source media="(max-width: 640px)" srcset="{{ $slide->mobileImageUrl() }}">
                                @endif
                                <img src="{{ $slide->imageUrl() }}" alt=""
                                     class="ken-burns h-full w-full object-cover"
                                     @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                            </picture>
                            {{-- Keeps the headline readable over any photograph --}}
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-950/95 via-primary-950/80 to-primary-950/40"></div>
                        </div>
                    @endif
                @endforeach

                {{-- A hairline grid, fading out downward: texture, so the band
                     reads as a designed surface rather than a flat fill. --}}
                <div class="absolute inset-0 opacity-[0.07]"
                     style="background-image:linear-gradient(to right,#fff 1px,transparent 1px),linear-gradient(to bottom,#fff 1px,transparent 1px);background-size:64px 64px;mask-image:linear-gradient(to bottom,#000,transparent 75%)"></div>
            </div>

            <div class="container-page relative grid items-center gap-14 py-20 lg:grid-cols-12 lg:py-28">
                <div class="lg:col-span-7">
                    @if ($doctor->tr('degrees'))
                        <span class="eyebrow text-primary-300">
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
                                    <h1 class="display mt-5 text-4xl text-white sm:text-5xl lg:text-6xl">
                                        {{ $slide->tr('title') ?: $doctor->fullName() }}
                                    </h1>
                                    @if ($slide->tr('subtitle'))
                                        <p class="mt-6 max-w-xl text-lg leading-relaxed text-primary-100/90">
                                            {{ $slide->tr('subtitle') }}
                                        </p>
                                    @endif
                                    @if ($slide->cta_url && $slide->tr('cta_label'))
                                        <a href="{{ $slide->cta_url }}" class="group btn-secondary mt-6 bg-white/10 text-white ring-white/25 hover:bg-white/20">
                                            {{ $slide->tr('cta_label') }}
                                            <x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <h1 class="display mt-5 text-4xl text-white sm:text-5xl lg:text-6xl">
                            {{ $doctor->tr('tagline')
                                ?: ($doctor->fullName() ?: setting('site_name_'.$locale) ?: __('site.home.hero_greeting')) }}
                        </h1>

                        @if ($doctor->tr('short_bio'))
                            <p class="mt-6 max-w-xl text-lg leading-relaxed text-primary-100/90">
                                {{ Str::limit(strip_tags((string) $doctor->tr('short_bio')), 220) }}
                            </p>
                        @endif
                    @endif

                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        @feature('appointment')
                            <a href="{{ route('appointment.create') }}" class="group btn-primary btn-lg">
                                <x-icon name="calendar-check" class="h-5 w-5"/>
                                {{ __('site.actions.book_appointment') }}
                            </a>
                        @endfeature
                        @feature('about')
                            <a href="{{ route('about') }}" class="group btn btn-lg bg-white/10 text-white ring-1 ring-inset ring-white/20 hover:bg-white/20">
                                {{ __('site.home.hero_cta_secondary') }}
                                <x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        @endfeature
                    </div>

                    {{-- The three things a patient weighs before booking, on one line. --}}
                    @if ($trust)
                        <dl class="mt-10 flex flex-wrap gap-x-10 gap-y-6 border-t border-white/10 pt-8">
                            @foreach ($trust as $fact)
                                <div class="flex items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 text-primary-200">
                                        <x-icon :name="$fact['icon']" class="h-5 w-5"/>
                                    </span>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-primary-300">{{ $fact['label'] }}</dt>
                                        <dd class="text-base font-semibold tabular-nums text-white">
                                            @isset($fact['href'])
                                                <a href="{{ $fact['href'] }}" class="underline-offset-4 hover:underline">{{ $fact['value'] }}</a>
                                            @else
                                                {{ $fact['value'] }}
                                            @endisset
                                        </dd>
                                    </div>
                                </div>
                            @endforeach
                        </dl>
                    @endif

                    {{-- Slide controls, only worth showing when there is more than one --}}
                    @if ($slides->count() > 1)
                        <div class="mt-10 flex items-center gap-4">
                            <button type="button" @click="prev()"
                                    class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-inset ring-white/15 transition hover:bg-white/25"
                                    aria-label="{{ __('site.actions.previous') }}">
                                <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180"/>
                            </button>

                            <div class="flex items-center gap-2" role="tablist">
                                @foreach ($slides as $i => $slide)
                                    <button type="button" @click="go({{ $i }})" role="tab"
                                            :aria-selected="current === {{ $i }}"
                                            :class="current === {{ $i }} ? 'w-10 bg-white' : 'w-4 bg-white/40 hover:bg-white/70'"
                                            class="h-1 rounded-full transition-all duration-500"
                                            aria-label="{{ $i + 1 }}"></button>
                                @endforeach
                            </div>

                            <button type="button" @click="next()"
                                    class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-inset ring-white/15 transition hover:bg-white/25"
                                    aria-label="{{ __('site.actions.next') }}">
                                <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180"/>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Doctor card --}}
                <div class="lg:col-span-5">
                    <div class="relative mx-auto max-w-sm">
                        {{-- A second frame, offset behind: depth without a shadow. --}}
                        <div aria-hidden="true" class="absolute -end-4 -top-4 hidden h-full w-full rounded-3xl border border-white/15 sm:block"></div>

                        <div class="relative overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-white/10">
                            <x-media-frame :src="$doctor->photoUrl()" :alt="$doctor->fullName()" icon="stethoscope"
                                           ratio="aspect-[4/5]" fit="contain"
                                           :label="$doctor->tr('name')" :seed="$doctor->tr('name')"/>

                            <div class="p-6">
                                <p class="text-lg font-semibold text-slate-900">{{ $doctor->fullName() }}</p>
                                <p class="mt-1 text-sm font-medium text-primary-700">{{ $doctor->tr('designation') }}</p>

                                @if ($doctor->tr('languages'))
                                    <p class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4 text-xs text-slate-500">
                                        <x-icon name="globe" class="h-3.5 w-3.5 text-slate-400"/>
                                        {{ $doctor->tr('languages') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @php $badgeValue = $stats->first()?->value ?: $doctor->experience_years; @endphp
                        @if ($badgeValue)
                            <div class="absolute -bottom-5 -end-5 hidden rounded-2xl bg-accent-600 px-5 py-4 text-center text-white shadow-xl sm:block">
                                <p class="text-2xl font-bold tabular-nums">{{ bn_digits($badgeValue) }}+</p>
                                <p class="text-xs opacity-90">{{ $stats->first()?->tr('label') ?? __('site.about.experience_years') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @push('scripts')
            @include('public.home.partials.carousel-script')
        @endpush
    @endfeature


    {{-- ============================ Stats ============================ --}}
    @feature('home_stats')
        @if ($stats->isNotEmpty())
            <section class="border-b border-slate-200 bg-white">
                <div x-data x-reveal.stagger
                     class="container-page grid grid-cols-2 divide-slate-200 py-12 sm:divide-x lg:grid-cols-4 rtl:divide-x-reverse">
                    @foreach ($stats as $stat)
                        <div class="group flex items-center gap-4 px-2 py-3 sm:px-6">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-primary-50 text-primary-600 transition duration-300 group-hover:bg-primary-600 group-hover:text-white">
                                <x-icon :name="$stat->icon ?: 'activity'" class="h-6 w-6"/>
                            </span>
                            <div class="min-w-0">
                                <p class="text-3xl font-bold tabular-nums tracking-tight text-slate-900" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                                <p class="mt-0.5 truncate text-sm text-slate-500">{{ $stat->tr('label') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ About ============================ --}}
    @if (feature('home_about') && ($doctor->tr('bio') || $doctor->tr('short_bio')))
        <section class="section bg-slate-50">
            <div class="container-page grid items-center gap-16 lg:grid-cols-2">
                <div x-data x-reveal class="relative">
                    {{-- The portrait, with the frame stepped out behind it. --}}
                    <div aria-hidden="true" class="absolute -bottom-5 -start-5 hidden h-full w-full rounded-3xl border-2 border-primary-200 lg:block"></div>

                    <div class="relative overflow-hidden rounded-3xl shadow-[var(--shadow-lift)]">
                        <x-media-frame :src="$doctor->heroImageUrl() ?? $doctor->photoUrl()" :alt="$doctor->fullName()"
                                       icon="stethoscope" ratio="aspect-[4/3]"
                                       :label="$doctor->tr('name')" seed="doctor-hero"/>
                    </div>

                    @if ($stats->skip(1)->take(3)->isNotEmpty())
                        <div class="relative mt-5 grid grid-cols-3 gap-3">
                            @foreach ($stats->skip(1)->take(3) as $stat)
                                <div class="card card-hover p-4 text-center">
                                    <p class="text-xl font-bold tabular-nums text-primary-700" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                                    <p class="mt-1 text-[11px] leading-tight text-slate-500">{{ $stat->tr('label') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <x-section-heading align="start" :eyebrow="__('site.nav.about')" :title="__('site.home.about_heading')"/>

                    <div class="prose-content -mt-7 text-base">
                        {!! Str::limit(strip_tags((string) $doctor->tr('bio'), '<p><strong><em>'), 700, '…</p>') !!}
                    </div>

                    <ul x-data x-reveal.stagger class="mt-8 grid gap-3 sm:grid-cols-2">
                        @foreach (['badge-check' => $doctor->tr('degrees'), 'globe' => $doctor->tr('languages'), 'file-text' => $doctor->bmdc_reg_no ? __('site.about.registration').': '.$doctor->bmdc_reg_no : null] as $icon => $value)
                            @if ($value)
                                <li class="flex items-start gap-3 rounded-xl bg-white p-3.5 text-sm text-slate-600 ring-1 ring-slate-200/70">
                                    <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0 text-accent-600"/>
                                    <span>{{ $value }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @feature('about')
                            <a href="{{ route('about') }}" class="group btn-primary">
                                {{ __('site.actions.read_more') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        @endfeature
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
    @feature('home_services')
        @if ($services->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page">
                    <x-section-heading :eyebrow="__('site.nav.services')" :title="__('site.home.expertise_heading')"
                                       :subtitle="__('site.home.expertise_subheading')"/>

                    <x-card-grid x-data x-reveal.stagger :count="$services->count()">
                        @foreach ($services as $service)
                            <x-service-card :service="$service"/>
                        @endforeach
                    </x-card-grid>

                    <div x-data x-reveal class="mt-12 text-center">
                        <a href="{{ route('services.index') }}" class="group btn-secondary">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Chambers ============================ --}}
    @feature('home_chambers')
        @if ($chambers->isNotEmpty())
            <section class="section bg-slate-50">
                <div class="container-page">
                    <x-section-heading :eyebrow="__('site.nav.chambers')" :title="__('site.home.chambers_heading')"
                                       :subtitle="__('site.home.chambers_subheading')"/>

                    <x-chamber-grid :chambers="$chambers" :next-dates="$nextDates ?? []"/>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Booking steps ============================ --}}
    @feature('home_steps')
        <section class="section bg-white">
            <div class="container-page">
                <x-section-heading :title="__('site.home.steps_heading')"/>

                <div x-data x-reveal class="relative">
                    {{-- The line the three steps sit on, drawn in behind them. --}}
                    <div aria-hidden="true"
                         class="line-draw absolute inset-x-[16%] top-9 hidden h-0.5 rounded-full bg-gradient-to-r from-primary-100 via-primary-300 to-primary-100 md:block"></div>

                    <div class="relative grid gap-8 md:grid-cols-3">
                        @foreach ([1, 2, 3] as $step)
                            <div class="group text-center">
                                <span class="mx-auto grid h-[4.5rem] w-[4.5rem] place-items-center rounded-2xl bg-white text-2xl font-bold tabular-nums text-primary-700 shadow-[var(--shadow-soft)] ring-1 ring-primary-100 transition duration-300 group-hover:bg-primary-600 group-hover:text-white group-hover:ring-primary-600">
                                    {{ bn_digits($step) }}
                                </span>
                                <h3 class="mt-6 text-lg font-semibold">{{ __("site.home.step_{$step}_title") }}</h3>
                                <p class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-slate-500">{{ __("site.home.step_{$step}_text") }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endfeature

    {{-- ============================ Success stories ============================ --}}
    @feature('home_stories')
        @if ($stories->isNotEmpty())
            <section class="section bg-slate-50">
                <div class="container-page">
                    <x-section-heading align="between" :eyebrow="__('site.nav.success_stories')"
                                       :title="__('site.home.stories_heading')" :subtitle="__('site.home.stories_subheading')">
                        <x-slot:action>
                            <a href="{{ route('stories.index') }}" class="group btn-secondary">
                                {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        </x-slot:action>
                    </x-section-heading>

                    <x-card-grid x-data x-reveal.stagger two-up="md" :count="$stories->count()">
                        @foreach ($stories as $story)
                            <x-story-card :story="$story"/>
                        @endforeach
                    </x-card-grid>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Testimonials ============================ --}}
    @feature('home_testimonials')
        @if ($testimonials->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page">
                    <x-section-heading :title="__('site.home.testimonials_heading')"/>

                    @php $featured = $testimonials->take(3); @endphp
                    <x-card-grid x-data x-reveal.stagger two-up="md" :count="$featured->count()">
                        @foreach ($featured as $testimonial)
                            <x-testimonial-card :testimonial="$testimonial"/>
                        @endforeach
                    </x-card-grid>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ News, events, articles ============================ --}}
    @if (($news->isNotEmpty() && feature('home_news')) || ($articles->isNotEmpty() && feature('home_blog')))
        <section class="section bg-slate-50">
            <div class="container-page space-y-20">
                @if ($news->isNotEmpty() && feature('home_news'))
                    <div>
                        <x-section-heading align="between" :eyebrow="__('site.nav.news_events')"
                                           :title="__('site.home.news_heading')" :subtitle="__('site.home.news_subheading')">
                            <x-slot:action>
                                {{-- The band can outlive the news page: with news off and
                                     events on it still carries the event cards. --}}
                                @php $listing = feature('news') ? 'news.index' : (feature('events') ? 'events.index' : null); @endphp
                                @if ($listing)
                                    <a href="{{ route($listing) }}" class="group btn-secondary">
                                        {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                                    </a>
                                @endif
                            </x-slot:action>
                        </x-section-heading>

                        <x-card-grid x-data x-reveal.stagger two-up="md" :count="$news->count()">
                            @foreach ($news as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </x-card-grid>
                    </div>
                @endif

                @if ($articles->isNotEmpty() && feature('home_blog'))
                    <div>
                        <x-section-heading align="between" :eyebrow="__('site.nav.blog')"
                                           :title="__('site.home.blog_heading')" :subtitle="__('site.home.blog_subheading')">
                            <x-slot:action>
                                <a href="{{ route('blog.index') }}" class="group btn-secondary">
                                    {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                                </a>
                            </x-slot:action>
                        </x-section-heading>

                        <x-card-grid x-data x-reveal.stagger two-up="md" :count="$articles->count()">
                            @foreach ($articles as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </x-card-grid>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ============================ FAQ ============================ --}}
    @feature('home_faq')
        @if ($faqs->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <x-section-heading align="start" :eyebrow="__('site.nav.faq')" :title="__('site.home.faq_heading')"
                                           :subtitle="__('site.faq.subheading')"/>
                        <a href="{{ route('faq.index') }}" class="group btn-secondary -mt-5">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>

                    <div x-data x-reveal class="lg:col-span-8">
                        <x-faq-accordion :faqs="$faqs"/>
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Final CTA ============================ --}}
    @feature('home_cta')
        <section class="relative overflow-hidden bg-primary-900">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -end-24 -top-24 h-[22rem] w-[22rem] rounded-full bg-primary-600/40 blur-3xl"></div>
                <div class="absolute -bottom-32 start-1/4 h-[18rem] w-[18rem] rounded-full bg-accent-500/20 blur-3xl"></div>
            </div>

            <div x-data x-reveal class="container-page relative py-20">
                <div class="flex flex-col items-center justify-between gap-8 text-center lg:flex-row lg:text-start">
                    <div class="max-w-xl">
                        <h2 class="display text-3xl text-white sm:text-4xl">{{ __('site.home.cta_heading') }}</h2>
                        <span aria-hidden="true" class="rule-draw mt-5 mx-auto block h-[3px] w-14 rounded-full bg-accent-500 lg:mx-0"></span>
                        <p class="mt-5 text-lg leading-relaxed text-primary-100">{{ __('site.home.cta_text') }}</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap justify-center gap-3">
                        @feature('appointment')
                            <a href="{{ route('appointment.create') }}" class="btn-lg btn btn-invert">
                                <x-icon name="calendar-check" class="h-5 w-5"/>{{ __('site.actions.book_appointment') }}
                            </a>
                        @endfeature
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
    @endfeature
</x-layouts.public>
