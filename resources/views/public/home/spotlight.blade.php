{{--
    Spotlight — the compact, card-led homepage.

    Where Classic stacks full-width bands, this design keeps one continuous
    surface and floats the content on it: the hero photograph bleeds off the
    end edge, the statistics ride up over it on a single card, and the closing
    call to action sits inset rather than full bleed. It suits a practice whose
    photographs are strong and whose visitor is on a phone.

    Same bands, same data, same switches as every other layout — only the shape
    of the page differs.
--}}
<x-layouts.public>
    @php
        $locale = app()->getLocale();

        $slides = $sliders->values();
        $hasSlides = $slides->isNotEmpty();

        // The photograph behind the hero: a slide if one carries an image,
        // otherwise the profile's own picture.
        $heroFallback = $doctor->heroImageUrl() ?? $doctor->photoUrl();
    @endphp

    {{-- ============================ Hero ============================ --}}
    @feature('home_hero')
        <section
            x-data="heroCarousel({{ $hasSlides ? $slides->count() : 1 }})"
            x-init="start()"
            @mouseenter="pause()" @mouseleave="resume()"
            @focusin="pause()" @focusout="resume()"
            class="relative overflow-hidden bg-primary-950"
            role="region" aria-roledescription="carousel"
            aria-label="{{ __('site.home.hero_greeting') }}">

            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -start-40 -top-40 h-[32rem] w-[32rem] rounded-full bg-primary-600/25 blur-3xl"></div>
                <div class="absolute -bottom-48 start-1/3 h-[26rem] w-[26rem] rounded-full bg-accent-500/20 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.07]"
                     style="background-image:linear-gradient(to right,#fff 1px,transparent 1px),linear-gradient(to bottom,#fff 1px,transparent 1px);background-size:64px 64px;mask-image:linear-gradient(to bottom,#000,transparent 75%)"></div>
            </div>

            {{-- The photograph. A band of its own on a phone; on a wide screen
                 it leaves the grid and holds the end edge, with the headline
                 running across it. --}}
            <div class="relative lg:absolute lg:inset-y-0 lg:end-0 lg:w-[44%]">
                <div class="relative h-64 sm:h-80 lg:h-full">
                    @if ($hasSlides)
                        @foreach ($slides as $i => $slide)
                            @php $image = $slide->imageUrl() ?: $heroFallback; @endphp
                            @if ($image)
                                <div x-show="current === {{ $i }}" x-transition.opacity.duration.700ms class="absolute inset-0">
                                    <picture>
                                        @if ($slide->mobileImageUrl() && $slide->mobileImageUrl() !== $slide->imageUrl())
                                            <source media="(max-width: 640px)" srcset="{{ $slide->mobileImageUrl() }}">
                                        @endif
                                        <img src="{{ $image }}" alt="{{ $slide->tr('title') ?: $doctor->fullName() }}"
                                             class="ken-burns h-full w-full object-cover"
                                             @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                                    </picture>
                                </div>
                            @endif
                        @endforeach
                    @elseif ($heroFallback)
                        <img src="{{ $heroFallback }}" alt="{{ $doctor->fullName() }}" fetchpriority="high"
                             class="ken-burns absolute inset-0 h-full w-full object-cover">
                    @else
                        <x-media-frame :src="null" icon="stethoscope" :label="$doctor->tr('name')"
                                       :seed="$doctor->tr('name')" fit="natural"
                                       class="absolute inset-0 h-full"/>
                    @endif

                    {{-- Two scrims: down the start edge so the headline keeps
                         its ground on a wide screen, and up from the foot so
                         the caption sits on something. --}}
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-primary-950 via-primary-950/30 to-transparent lg:bg-gradient-to-r lg:from-primary-950 lg:via-primary-950/40 lg:to-primary-950/10"></div>
                </div>

                {{-- Kept clear of the statistics card, which rides up over
                     the foot of this band whenever it is switched on. --}}
                <div @class([
                    'absolute inset-x-0 bottom-0 p-6 lg:p-8',
                    'pb-24 lg:pb-28' => feature('home_stats'),
                ])>
                    <div class="inline-flex max-w-full items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-inset ring-white/20 backdrop-blur">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/15 text-primary-100">
                            <x-icon name="stethoscope" class="h-4 w-4"/>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-white">{{ $doctor->fullName() }}</span>
                            @if ($doctor->tr('designation'))
                                <span class="block truncate text-xs text-primary-200">{{ $doctor->tr('designation') }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="container-page relative py-16 lg:grid lg:grid-cols-12 lg:py-28">
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
                                    <h1 class="display mt-5 text-4xl text-white sm:text-5xl lg:text-[3.4rem]">
                                        {{ $slide->tr('title') ?: $doctor->fullName() }}
                                    </h1>
                                    @if ($slide->tr('subtitle'))
                                        <p class="mt-6 max-w-lg text-lg leading-relaxed text-primary-100/90">
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
                        <h1 class="display mt-5 text-4xl text-white sm:text-5xl lg:text-[3.4rem]">
                            {{ $doctor->tr('tagline')
                                ?: ($doctor->fullName() ?: setting('site_name_'.$locale) ?: __('site.home.hero_greeting')) }}
                        </h1>

                        @if ($doctor->tr('short_bio'))
                            <p class="mt-6 max-w-lg text-lg leading-relaxed text-primary-100/90">
                                {{ Str::limit(strip_tags((string) $doctor->tr('short_bio')), 200) }}
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

                    {{-- The facts a patient weighs before booking. Classic sets
                         them on a rule; here they are pills, to match a page
                         made of cards. --}}
                    @if ($trust)
                        <dl class="mt-10 flex flex-wrap gap-3">
                            @foreach ($trust as $fact)
                                <div class="flex items-center gap-2.5 rounded-full bg-white/10 py-2 pe-4 ps-2.5 ring-1 ring-inset ring-white/15">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/10 text-primary-200">
                                        <x-icon :name="$fact['icon']" class="h-4 w-4"/>
                                    </span>
                                    <div class="leading-tight">
                                        <dt class="text-[11px] uppercase tracking-wider text-primary-300">{{ $fact['label'] }}</dt>
                                        <dd class="text-sm font-semibold tabular-nums text-white">
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
            </div>
        </section>

        @push('scripts')
            @include('public.home.partials.carousel-script')
        @endpush
    @endfeature

    {{-- ============================ Stats ============================ --}}
    {{-- One card rather than a full-width strip, riding up over the hero when
         there is a hero to ride on. With it switched off the card simply opens
         the page. --}}
    @feature('home_stats')
        @if ($stats->isNotEmpty())
            <section @class(['relative z-10 bg-slate-50 pb-12', 'pt-12' => ! feature('home_hero')])>
                <div class="container-page">
                    <div x-data x-reveal.stagger
                         @class([
                             'card grid grid-cols-2 gap-y-8 rounded-3xl p-8 shadow-[var(--shadow-lift)] sm:divide-x sm:divide-slate-200 lg:grid-cols-4 rtl:divide-x-reverse',
                             '-mt-16' => feature('home_hero'),
                         ])>
                        @foreach ($stats as $stat)
                            <div class="group flex flex-col items-center gap-2 px-4 text-center">
                                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-primary-50 text-primary-600 transition duration-300 group-hover:bg-primary-600 group-hover:text-white">
                                    <x-icon :name="$stat->icon ?: 'activity'" class="h-5 w-5"/>
                                </span>
                                <p class="text-3xl font-bold tabular-nums tracking-tight text-slate-900" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                                <p class="text-sm leading-tight text-slate-500">{{ $stat->tr('label') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ About ============================ --}}
    @if (feature('home_about') && ($doctor->tr('bio') || $doctor->tr('short_bio')))
        <section class="section bg-slate-50">
            <div class="container-page grid items-center gap-14 lg:grid-cols-12">
                {{-- Text leads here; Classic leads with the portrait. --}}
                <div class="lg:col-span-7">
                    <x-section-heading align="start" :eyebrow="__('site.nav.about')" :title="__('site.home.about_heading')"/>

                    <div class="prose-content -mt-7 text-base">
                        {!! Str::limit(strip_tags((string) $doctor->tr('bio'), '<p><strong><em>'), 700, '…</p>') !!}
                    </div>

                    <ul x-data x-reveal.stagger class="mt-8 grid gap-3 sm:grid-cols-2">
                        @foreach (['badge-check' => $doctor->tr('degrees'), 'globe' => $doctor->tr('languages'), 'file-text' => $doctor->bmdc_reg_no ? __('site.about.registration').': '.$doctor->bmdc_reg_no : null] as $icon => $value)
                            @if ($value)
                                <li class="card flex items-start gap-3 p-3.5 text-sm text-slate-600">
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

                <div x-data x-reveal class="relative lg:col-span-5">
                    <div class="relative overflow-hidden rounded-[2rem] shadow-[var(--shadow-lift)]">
                        <x-media-frame :src="$doctor->photoUrl() ?? $doctor->heroImageUrl()" :alt="$doctor->fullName()"
                                       icon="stethoscope" ratio="aspect-[4/5]" fit="contain"
                                       :label="$doctor->tr('name')" seed="doctor-portrait"/>
                    </div>

                    @php $badge = $stats->first(); @endphp
                    @if ($badge)
                        <div class="card absolute -bottom-6 -start-4 hidden rounded-2xl px-5 py-4 text-center shadow-[var(--shadow-lift)] sm:block">
                            <p class="text-2xl font-bold tabular-nums text-primary-700">{{ $badge->displayValue() }}</p>
                            <p class="mt-0.5 text-[11px] leading-tight text-slate-500">{{ $badge->tr('label') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ Expertise ============================ --}}
    @feature('home_services')
        @if ($services->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page">
                    <x-section-heading align="between" :eyebrow="__('site.nav.services')"
                                       :title="__('site.home.expertise_heading')" :subtitle="__('site.home.expertise_subheading')">
                        <x-slot:action>
                            <a href="{{ route('services.index') }}" class="group btn-secondary">
                                {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        </x-slot:action>
                    </x-section-heading>

                    <x-card-grid x-data x-reveal.stagger :count="$services->count()">
                        @foreach ($services as $service)
                            <x-service-card :service="$service"/>
                        @endforeach
                    </x-card-grid>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Chambers and how booking works ============================ --}}
    {{-- Classic gives the three steps a band of their own. Here they are a
         ribbon above the chambers, because the step a reader is on is
         "choose a chamber" and it is directly beneath. Each half still
         answers to its own switch. --}}
    @if ((feature('home_chambers') && $chambers->isNotEmpty()) || feature('home_steps'))
        <section class="section bg-slate-50">
            <div class="container-page">
                @feature('home_steps')
                    <div x-data x-reveal class="card mb-14 rounded-3xl p-8">
                        <p class="eyebrow mb-6">
                            <span class="h-px w-6 bg-primary-400"></span>{{ __('site.home.steps_heading') }}
                        </p>

                        <ol class="grid gap-6 md:grid-cols-3">
                            @foreach ([1, 2, 3] as $step)
                                <li class="group flex items-start gap-4">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-primary-50 text-base font-bold tabular-nums text-primary-700 transition duration-300 group-hover:bg-primary-600 group-hover:text-white">
                                        {{ bn_digits($step) }}
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold">{{ __("site.home.step_{$step}_title") }}</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ __("site.home.step_{$step}_text") }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endfeature

                @feature('home_chambers')
                    @if ($chambers->isNotEmpty())
                        <x-section-heading align="between" :eyebrow="__('site.nav.chambers')"
                                           :title="__('site.home.chambers_heading')" :subtitle="__('site.home.chambers_subheading')">
                            <x-slot:action>
                                <a href="{{ route('chambers.index') }}" class="group btn-secondary">
                                    {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                                </a>
                            </x-slot:action>
                        </x-section-heading>

                        <x-chamber-grid :chambers="$chambers" :next-dates="$nextDates ?? []"/>
                    @endif
                @endfeature
            </div>
        </section>
    @endif

    {{-- ============================ Success stories ============================ --}}
    {{-- The one dark band below the fold: it breaks a long white page in two
         and marks the point where the site stops describing itself and starts
         showing evidence. --}}
    @feature('home_stories')
        @if ($stories->isNotEmpty())
            <section class="relative overflow-hidden bg-primary-950">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                    <div class="absolute -end-32 -top-32 h-[24rem] w-[24rem] rounded-full bg-primary-600/25 blur-3xl"></div>
                    <div class="absolute -bottom-40 start-1/4 h-[20rem] w-[20rem] rounded-full bg-accent-500/20 blur-3xl"></div>
                </div>

                <div class="container-page relative py-20 sm:py-24">
                    <div x-data x-reveal class="mb-12 flex flex-wrap items-end justify-between gap-6">
                        <div class="max-w-2xl">
                            <span class="eyebrow text-primary-300">
                                <span class="h-px w-6 bg-primary-400"></span>{{ __('site.nav.success_stories') }}
                            </span>
                            <h2 class="display mt-4 text-[1.75rem] text-white sm:text-4xl">{{ __('site.home.stories_heading') }}</h2>
                            <span aria-hidden="true" class="rule-draw mt-5 block h-[3px] w-14 rounded-full bg-accent-500"></span>
                            <p class="mt-5 text-[15px] leading-relaxed text-primary-100 sm:text-base">{{ __('site.home.stories_subheading') }}</p>
                        </div>

                        <a href="{{ route('stories.index') }}" class="group btn btn-invert shrink-0">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>

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
    {{-- A rail the reader pushes, so all six fit without a wall of cards.
         Nothing scrolls on its own. --}}
    @feature('home_testimonials')
        @if ($testimonials->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page">
                    <x-section-heading :title="__('site.home.testimonials_heading')"/>
                </div>

                <div class="container-page">
                    {{-- The rail runs out to the edge of the screen, so a card
                         cut off at the right tells the reader there is more. --}}
                    <div x-data x-reveal.stagger
                         class="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-6 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                        @foreach ($testimonials as $testimonial)
                            <div class="w-[85%] shrink-0 snap-start sm:w-[24rem]">
                                <x-testimonial-card :testimonial="$testimonial"/>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ News, events, articles ============================ --}}
    {{-- Side by side rather than stacked: on this page they are two short
         columns, not two more full bands. --}}
    @if (($news->isNotEmpty() && feature('home_news')) || ($articles->isNotEmpty() && feature('home_blog')))
        <section class="section bg-slate-50">
            <div class="container-page grid gap-14 lg:grid-cols-2">
                @if ($news->isNotEmpty() && feature('home_news'))
                    <div>
                        <div x-data x-reveal class="mb-8">
                            <span class="eyebrow"><span class="h-px w-6 bg-primary-400"></span>{{ __('site.nav.news_events') }}</span>
                            <h2 class="display mt-4 text-2xl sm:text-3xl">{{ __('site.home.news_heading') }}</h2>
                            <span aria-hidden="true" class="rule-draw mt-4 block h-[3px] w-14 rounded-full bg-primary-500"></span>
                        </div>

                        <div x-data x-reveal.stagger class="space-y-5">
                            @foreach ($news as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </div>

                        @php $listing = feature('news') ? 'news.index' : (feature('events') ? 'events.index' : null); @endphp
                        @if ($listing)
                            <a href="{{ route($listing) }}" class="group btn-secondary mt-6">
                                {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($articles->isNotEmpty() && feature('home_blog'))
                    <div>
                        <div x-data x-reveal class="mb-8">
                            <span class="eyebrow"><span class="h-px w-6 bg-primary-400"></span>{{ __('site.nav.blog') }}</span>
                            <h2 class="display mt-4 text-2xl sm:text-3xl">{{ __('site.home.blog_heading') }}</h2>
                            <span aria-hidden="true" class="rule-draw mt-4 block h-[3px] w-14 rounded-full bg-primary-500"></span>
                        </div>

                        <div x-data x-reveal.stagger class="space-y-5">
                            @foreach ($articles as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </div>

                        <a href="{{ route('blog.index') }}" class="group btn-secondary mt-6">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ============================ FAQ ============================ --}}
    @feature('home_faq')
        @if ($faqs->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page">
                    <x-section-heading :eyebrow="__('site.nav.faq')" :title="__('site.home.faq_heading')"
                                       :subtitle="__('site.faq.subheading')"/>

                    <div x-data x-reveal class="mx-auto max-w-3xl">
                        <x-faq-accordion :faqs="$faqs"/>

                        <div class="mt-8 text-center">
                            <a href="{{ route('faq.index') }}" class="group btn-secondary">
                                {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Final CTA ============================ --}}
    {{-- Inset, not full bleed: the page ends on a card like the one it opened
         with, rather than on another band. --}}
    @feature('home_cta')
        <section class="bg-slate-50 pb-20 pt-4">
            <div class="container-page">
                <div x-data x-reveal class="relative overflow-hidden rounded-[2rem] bg-primary-900 px-8 py-14 sm:px-14">
                    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                        <div class="absolute -end-24 -top-24 h-[20rem] w-[20rem] rounded-full bg-primary-600/40 blur-3xl"></div>
                        <div class="absolute -bottom-28 start-1/4 h-[16rem] w-[16rem] rounded-full bg-accent-500/20 blur-3xl"></div>
                    </div>

                    <div class="relative flex flex-col items-center justify-between gap-8 text-center lg:flex-row lg:text-start">
                        <div class="max-w-xl">
                            <h2 class="display text-3xl text-white sm:text-4xl">{{ __('site.home.cta_heading') }}</h2>
                            <span aria-hidden="true" class="rule-draw mx-auto mt-5 block h-[3px] w-14 rounded-full bg-accent-500 lg:mx-0"></span>
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
            </div>
        </section>
    @endfeature
</x-layouts.public>
