{{--
    Editorial — the magazine homepage.

    No coloured bands and almost no cards: the page is built from hairline
    rules, wide display type and numbered sections, the way a printed feature
    is. The photographs are given room rather than cropped into tiles, and the
    expertise list reads as a contents page. It suits a practice that wants the
    writing — the bio, the stories, the tips — to carry the page.

    Same bands, same data, same switches as every other layout — only the shape
    of the page differs.
--}}
<x-layouts.public>
    @php
        $locale = app()->getLocale();

        $slides = $sliders->values();
        $hasSlides = $slides->isNotEmpty();

        // Sections are numbered as a contents page is, so the count has to
        // follow whatever is switched on rather than a fixed list.
        $index = 0;
        $number = function () use (&$index) {
            return bn_digits(str_pad((string) ++$index, 2, '0', STR_PAD_LEFT));
        };
    @endphp

    {{-- ============================ Hero ============================ --}}
    @feature('home_hero')
        <section
            x-data="heroCarousel({{ $hasSlides ? $slides->count() : 1 }})"
            x-init="start()"
            @mouseenter="pause()" @mouseleave="resume()"
            @focusin="pause()" @focusout="resume()"
            class="relative overflow-hidden bg-white"
            role="region" aria-roledescription="carousel"
            aria-label="{{ __('site.home.hero_greeting') }}">

            {{-- The column rules the page is set on. Real hairlines rather
                 than a painted grid, so they follow the theme. --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 hidden lg:grid lg:grid-cols-12">
                @for ($i = 0; $i < 12; $i++)
                    <div class="border-s border-slate-100"></div>
                @endfor
            </div>

            <div class="container-page relative grid gap-12 py-16 lg:grid-cols-12 lg:gap-16 lg:py-24">
                <div class="lg:col-span-7">
                    @if ($doctor->tr('degrees'))
                        <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            <span class="h-px w-10 bg-accent-500"></span>{{ $doctor->tr('degrees') }}
                        </p>
                    @endif

                    @if ($hasSlides)
                        <div aria-live="polite">
                            @foreach ($slides as $i => $slide)
                                <div x-show="current === {{ $i }}"
                                     x-transition:enter="transition ease-out duration-500"
                                     x-transition:enter-start="opacity-0 translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     role="group" aria-roledescription="slide">
                                    <h1 class="display mt-7 text-[2.75rem] leading-[1.05] sm:text-6xl lg:text-[4.25rem]">
                                        {{ $slide->tr('title') ?: $doctor->fullName() }}
                                    </h1>
                                    @if ($slide->tr('subtitle'))
                                        <p class="mt-8 max-w-xl border-s-2 border-slate-200 ps-5 text-lg leading-relaxed text-slate-600">
                                            {{ $slide->tr('subtitle') }}
                                        </p>
                                    @endif
                                    @if ($slide->cta_url && $slide->tr('cta_label'))
                                        <a href="{{ $slide->cta_url }}" class="group mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary-700 underline-offset-4 hover:underline">
                                            {{ $slide->tr('cta_label') }}
                                            <x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <h1 class="display mt-7 text-[2.75rem] leading-[1.05] sm:text-6xl lg:text-[4.25rem]">
                            {{ $doctor->tr('tagline')
                                ?: ($doctor->fullName() ?: setting('site_name_'.$locale) ?: __('site.home.hero_greeting')) }}
                        </h1>

                        @if ($doctor->tr('short_bio'))
                            <p class="mt-8 max-w-xl border-s-2 border-slate-200 ps-5 text-lg leading-relaxed text-slate-600">
                                {{ Str::limit(strip_tags((string) $doctor->tr('short_bio')), 240) }}
                            </p>
                        @endif
                    @endif

                    <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3">
                        @feature('appointment')
                            <a href="{{ route('appointment.create') }}" class="group btn-primary btn-lg">
                                <x-icon name="calendar-check" class="h-5 w-5"/>
                                {{ __('site.actions.book_appointment') }}
                            </a>
                        @endfeature
                        @feature('about')
                            <a href="{{ route('about') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-slate-700 underline-offset-4 hover:text-primary-700 hover:underline">
                                {{ __('site.home.hero_cta_secondary') }}
                                <x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        @endfeature
                    </div>

                    {{-- The facts a patient weighs before booking, set as a
                         masthead: figures large, labels small beneath. --}}
                    @if ($trust)
                        <dl class="mt-12 grid gap-px overflow-hidden border-y border-slate-200 py-8 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($trust as $fact)
                                <div class="px-1 sm:px-5 sm:first:ps-0">
                                    <dd class="text-xl font-bold tabular-nums leading-tight text-slate-900">
                                        @isset($fact['href'])
                                            <a href="{{ $fact['href'] }}" class="underline-offset-4 hover:text-primary-700 hover:underline">{{ $fact['value'] }}</a>
                                        @else
                                            {{ $fact['value'] }}
                                        @endisset
                                    </dd>
                                    <dt class="mt-1.5 text-[11px] uppercase tracking-[0.14em] text-slate-500">{{ $fact['label'] }}</dt>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>

                {{-- The portrait, given the proportions of a printed plate. --}}
                <div class="lg:col-span-5">
                    <figure class="relative">
                        <div class="overflow-hidden rounded-sm border border-slate-200">
                            @if ($hasSlides && $slides->contains(fn ($slide) => (bool) $slide->imageUrl()))
                                <div class="relative aspect-[3/4]">
                                    @foreach ($slides as $i => $slide)
                                        @php $image = $slide->imageUrl() ?: ($doctor->heroImageUrl() ?? $doctor->photoUrl()); @endphp
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
                                </div>
                            @else
                                <x-media-frame :src="$doctor->photoUrl() ?? $doctor->heroImageUrl()" :alt="$doctor->fullName()"
                                               icon="stethoscope" ratio="aspect-[3/4]" fit="contain"
                                               :label="$doctor->tr('name')" seed="doctor-portrait"/>
                            @endif
                        </div>

                        <figcaption class="mt-4 flex items-baseline justify-between gap-4 border-t border-slate-900/10 pt-4">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $doctor->fullName() }}</span>
                                @if ($doctor->tr('designation'))
                                    <span class="block truncate text-xs text-slate-500">{{ $doctor->tr('designation') }}</span>
                                @endif
                            </span>

                            {{-- The slide index, read as a page number. --}}
                            @if ($slides->count() > 1)
                                <span class="flex shrink-0 items-center gap-3">
                                    <button type="button" @click="prev()"
                                            class="grid h-8 w-8 place-items-center rounded-full text-slate-500 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-100 hover:text-slate-900"
                                            aria-label="{{ __('site.actions.previous') }}">
                                        <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180"/>
                                    </button>
                                    {{-- Rendered rather than counted in JS:
                                         Bangla sets its own digits, and
                                         padStart would print Latin ones. --}}
                                    <span class="text-xs font-semibold tabular-nums text-slate-500">
                                        @foreach ($slides as $i => $slide)
                                            <span x-show="current === {{ $i }}">{{ bn_digits(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) }}</span>
                                        @endforeach
                                        <span aria-hidden="true">/ {{ bn_digits(str_pad((string) $slides->count(), 2, '0', STR_PAD_LEFT)) }}</span>
                                    </span>
                                    <button type="button" @click="next()"
                                            class="grid h-8 w-8 place-items-center rounded-full text-slate-500 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-100 hover:text-slate-900"
                                            aria-label="{{ __('site.actions.next') }}">
                                        <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180"/>
                                    </button>
                                </span>
                            @endif
                        </figcaption>
                    </figure>
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
            <section class="border-y border-slate-200 bg-white">
                <div x-data x-reveal.stagger class="container-page grid grid-cols-2 gap-y-10 py-14 lg:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="px-2 sm:px-6">
                            <p class="display text-4xl tabular-nums text-slate-900 sm:text-5xl" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                            <span aria-hidden="true" class="rule-draw mt-4 block h-[2px] w-10 rounded-full bg-accent-500"></span>
                            <p class="mt-3 text-xs uppercase tracking-[0.14em] text-slate-500">{{ $stat->tr('label') }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ About ============================ --}}
    @if (feature('home_about') && ($doctor->tr('bio') || $doctor->tr('short_bio')))
        <section class="section bg-white">
            <div class="container-page grid gap-12 lg:grid-cols-12 lg:gap-16">
                {{-- A standing rail, as a feature's deck sits beside its body. --}}
                <div x-data x-reveal class="lg:col-span-4">
                    <div class="lg:sticky lg:top-28">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ $number() }} — {{ __('site.nav.about') }}
                        </p>
                        <h2 class="display mt-5 text-3xl sm:text-4xl">{{ __('site.home.about_heading') }}</h2>
                        <span aria-hidden="true" class="rule-draw mt-6 block h-[3px] w-14 rounded-full bg-accent-500"></span>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @feature('about')
                                <a href="{{ route('about') }}" class="group btn-secondary">
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

                <div class="lg:col-span-8">
                    <div class="prose-content max-w-2xl text-[17px] leading-[1.8]">
                        {!! Str::limit(strip_tags((string) $doctor->tr('bio'), '<p><strong><em>'), 900, '…</p>') !!}
                    </div>

                    <figure x-data x-reveal class="mt-12">
                        <div class="overflow-hidden rounded-sm border border-slate-200">
                            {{-- Letterboxed rather than cropped: a practice
                                 plate carries its own lettering, and a centre
                                 crop cuts straight through it. --}}
                            <x-media-frame :src="$doctor->heroImageUrl() ?? $doctor->photoUrl()" :alt="$doctor->fullName()"
                                           icon="stethoscope" ratio="aspect-[16/10]" fit="contain"
                                           :label="$doctor->tr('name')" seed="doctor-hero"/>
                        </div>
                    </figure>

                    <dl x-data x-reveal.stagger class="mt-10 divide-y divide-slate-200 border-y border-slate-200">
                        @foreach ([__('site.about.education') => $doctor->tr('degrees'), __('site.about.languages') => $doctor->tr('languages'), __('site.about.registration') => $doctor->bmdc_reg_no] as $label => $value)
                            @if ($value)
                                <div class="flex flex-wrap items-baseline gap-x-8 gap-y-1 py-4">
                                    <dt class="w-40 shrink-0 text-xs uppercase tracking-[0.14em] text-slate-500">{{ $label }}</dt>
                                    <dd class="min-w-0 flex-1 text-sm text-slate-700">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ Expertise ============================ --}}
    {{-- A contents page rather than a wall of tiles: the reader is scanning
         for one condition, and a list is faster to scan than a grid. --}}
    @feature('home_services')
        @if ($services->isNotEmpty())
            <section class="section bg-slate-50">
                <div class="container-page">
                    <div x-data x-reveal class="flex flex-wrap items-end justify-between gap-6">
                        <div class="max-w-2xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $number() }} — {{ __('site.nav.services') }}
                            </p>
                            <h2 class="display mt-5 text-3xl sm:text-4xl">{{ __('site.home.expertise_heading') }}</h2>
                            <p class="mt-5 text-[15px] leading-relaxed text-slate-500 sm:text-base">{{ __('site.home.expertise_subheading') }}</p>
                        </div>

                        <a href="{{ route('services.index') }}" class="group btn-secondary shrink-0">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>

                    <div x-data x-reveal.stagger class="mt-12 border-t border-slate-200">
                        @foreach ($services as $i => $service)
                            <a href="{{ route('services.show', $service) }}"
                               class="group flex items-center gap-6 border-b border-slate-200 py-6 transition hover:bg-white">
                                <span class="w-10 shrink-0 text-sm font-semibold tabular-nums text-slate-400">
                                    {{ bn_digits(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) }}
                                </span>

                                {{-- The admin has always accepted a picture for
                                     a service; a list still has to show it. --}}
                                @if ($service->imageUrl())
                                    <span class="hidden h-16 w-16 shrink-0 overflow-hidden rounded-sm sm:block">
                                        <x-media-frame :src="$service->imageUrl()" :alt="$service->tr('name')"
                                                       :icon="$service->icon ?: 'stethoscope'" ratio="aspect-square" :seed="$service->slug"/>
                                    </span>
                                @else
                                    <span class="hidden h-16 w-16 shrink-0 place-items-center rounded-sm bg-primary-50 text-primary-600 sm:grid">
                                        <x-icon :name="$service->icon ?: 'stethoscope'" class="h-6 w-6"/>
                                    </span>
                                @endif

                                <span class="min-w-0 flex-1">
                                    <span class="block text-lg font-semibold leading-snug text-slate-900 transition group-hover:text-primary-700">
                                        {{ $service->tr('name') }}
                                    </span>
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-500">
                                        {{ Str::limit($service->tr('short_description'), 140) }}
                                    </span>
                                </span>

                                <x-icon name="arrow-right" class="lean h-5 w-5 shrink-0 text-slate-400 rtl:rotate-180"/>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Chambers ============================ --}}
    @feature('home_chambers')
        @if ($chambers->isNotEmpty())
            <section class="section bg-white">
                <div class="container-page">
                    <div x-data x-reveal class="flex flex-wrap items-end justify-between gap-6">
                        <div class="max-w-2xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $number() }} — {{ __('site.nav.chambers') }}
                            </p>
                            <h2 class="display mt-5 text-3xl sm:text-4xl">{{ __('site.home.chambers_heading') }}</h2>
                            <p class="mt-5 text-[15px] leading-relaxed text-slate-500 sm:text-base">{{ __('site.home.chambers_subheading') }}</p>
                        </div>

                        <a href="{{ route('chambers.index') }}" class="group btn-secondary shrink-0">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>

                    <div class="mt-12">
                        <x-chamber-grid :chambers="$chambers" :next-dates="$nextDates ?? []"/>
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Booking steps ============================ --}}
    @feature('home_steps')
        <section class="section bg-slate-50">
            <div class="container-page">
                <div x-data x-reveal>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                        {{ $number() }} — {{ __('site.nav.appointment') }}
                    </p>
                    <h2 class="display mt-5 text-3xl sm:text-4xl">{{ __('site.home.steps_heading') }}</h2>
                </div>

                <ol x-data x-reveal.stagger class="mt-12 grid gap-10 md:grid-cols-3">
                    @foreach ([1, 2, 3] as $step)
                        <li class="border-t-2 border-slate-900/10 pt-6">
                            <span class="display block text-5xl tabular-nums text-slate-200">
                                {{ bn_digits(str_pad((string) $step, 2, '0', STR_PAD_LEFT)) }}
                            </span>
                            <h3 class="mt-4 text-lg font-semibold">{{ __("site.home.step_{$step}_title") }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ __("site.home.step_{$step}_text") }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endfeature

    {{-- ============================ Success stories ============================ --}}
    {{-- One lead piece and the rest beneath it, the way a feature spread
         opens. --}}
    @feature('home_stories')
        @if ($stories->isNotEmpty())
            @php $lead = $stories->first(); $rest = $stories->skip(1); @endphp

            <section class="section bg-white">
                <div class="container-page">
                    <div x-data x-reveal class="flex flex-wrap items-end justify-between gap-6">
                        <div class="max-w-2xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $number() }} — {{ __('site.nav.success_stories') }}
                            </p>
                            <h2 class="display mt-5 text-3xl sm:text-4xl">{{ __('site.home.stories_heading') }}</h2>
                            <p class="mt-5 text-[15px] leading-relaxed text-slate-500 sm:text-base">{{ __('site.home.stories_subheading') }}</p>
                        </div>

                        <a href="{{ route('stories.index') }}" class="group btn-secondary shrink-0">
                            {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                        </a>
                    </div>

                    <a href="{{ route('stories.show', $lead) }}" x-data x-reveal
                       class="group mt-12 grid gap-8 border-t border-slate-200 pt-10 lg:grid-cols-12">
                        <div class="overflow-hidden rounded-sm border border-slate-200 lg:col-span-7">
                            <x-media-frame :src="$lead->imageUrl()" :alt="$lead->tr('title')" icon="heart"
                                           ratio="aspect-[16/10]" fit="contain" :seed="$lead->slug"/>
                        </div>

                        <div class="lg:col-span-5">
                            @if ($lead->service)
                                <span class="chip">{{ $lead->service->tr('name') }}</span>
                            @endif

                            <h3 class="display mt-4 text-2xl leading-snug transition group-hover:text-primary-700 sm:text-3xl">
                                {{ $lead->tr('title') }}
                            </h3>

                            <p class="mt-4 text-[15px] leading-relaxed text-slate-500">
                                {{ Str::limit($lead->tr('summary'), 260) }}
                            </p>

                            <p class="mt-6 flex flex-wrap items-center gap-2 border-t border-slate-200 pt-4 text-xs text-slate-500">
                                <x-icon name="user" class="h-3.5 w-3.5"/>
                                <span>{{ $lead->patient_name }}</span>
                                @if ($lead->patient_age)
                                    <span aria-hidden="true">·</span>
                                    <span class="tabular-nums">{{ bn_digits($lead->patient_age) }}</span>
                                @endif
                                @if ($lead->tr('patient_location'))
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $lead->tr('patient_location') }}</span>
                                @endif
                            </p>

                            <span class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600">
                                {{ __('site.actions.read_more') }}
                                <x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </span>
                        </div>
                    </a>

                    @if ($rest->isNotEmpty())
                        <div x-data x-reveal.stagger class="mt-10 grid gap-x-10 border-t border-slate-200 sm:grid-cols-2">
                            @foreach ($rest as $story)
                                <a href="{{ route('stories.show', $story) }}" class="group flex flex-col py-8">
                                    <h3 class="text-lg font-semibold leading-snug text-slate-900 transition group-hover:text-primary-700">
                                        {{ $story->tr('title') }}
                                    </h3>
                                    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-500">
                                        {{ Str::limit($story->tr('summary'), 150) }}
                                    </p>
                                    <span class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                                        <x-icon name="user" class="h-3.5 w-3.5"/>{{ $story->patient_name }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ Testimonials ============================ --}}
    {{-- Set as pull quotes rather than cards: on this page a boxed quote would
         be the only box on the screen. --}}
    @feature('home_testimonials')
        @if ($testimonials->isNotEmpty())
            <section class="section bg-slate-50">
                <div class="container-page">
                    <div x-data x-reveal>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ $number() }} — {{ __('site.home.testimonials_heading') }}
                        </p>
                    </div>

                    <div x-data x-reveal.stagger class="mt-10 grid gap-x-12 sm:grid-cols-2">
                        @foreach ($testimonials->take(4) as $testimonial)
                            <figure class="border-t border-slate-300 py-8">
                                <x-icon name="quote" class="h-6 w-6 text-slate-300"/>

                                <blockquote class="display mt-4 text-xl leading-snug text-slate-800 sm:text-2xl">
                                    {{ $testimonial->tr('content') }}
                                </blockquote>

                                <div class="mt-5 flex items-center gap-1 text-amber-400" aria-label="{{ $testimonial->rating }}/5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <x-icon name="star" class="h-3.5 w-3.5 {{ $i <= $testimonial->rating ? 'fill-current' : 'text-slate-300' }}"/>
                                    @endfor
                                </div>

                                <figcaption class="mt-4 flex items-center gap-3">
                                    <x-avatar :src="$testimonial->photoUrl()" :name="$testimonial->patient_name" class="h-9 w-9"/>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-slate-900">{{ $testimonial->patient_name }}</span>
                                        @if ($testimonial->tr('patient_title'))
                                            <span class="block truncate text-xs text-slate-500">{{ $testimonial->tr('patient_title') }}</span>
                                        @endif
                                    </span>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endfeature

    {{-- ============================ News, events, articles ============================ --}}
    @if (($news->isNotEmpty() && feature('home_news')) || ($articles->isNotEmpty() && feature('home_blog')))
        <section class="section bg-white">
            <div class="container-page grid gap-12 lg:grid-cols-2 lg:gap-16">
                @if ($news->isNotEmpty() && feature('home_news'))
                    <div>
                        <div x-data x-reveal class="border-b-2 border-slate-900/10 pb-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $number() }} — {{ __('site.nav.news_events') }}
                            </p>
                            <h2 class="display mt-4 text-2xl sm:text-3xl">{{ __('site.home.news_heading') }}</h2>
                        </div>

                        <div x-data x-reveal.stagger class="mt-8 space-y-6">
                            @foreach ($news as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </div>

                        @php $listing = feature('news') ? 'news.index' : (feature('events') ? 'events.index' : null); @endphp
                        @if ($listing)
                            <a href="{{ route($listing) }}" class="group mt-8 inline-flex items-center gap-2 text-sm font-semibold text-primary-700 underline-offset-4 hover:underline">
                                {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($articles->isNotEmpty() && feature('home_blog'))
                    <div>
                        <div x-data x-reveal class="border-b-2 border-slate-900/10 pb-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $number() }} — {{ __('site.nav.blog') }}
                            </p>
                            <h2 class="display mt-4 text-2xl sm:text-3xl">{{ __('site.home.blog_heading') }}</h2>
                        </div>

                        <div x-data x-reveal.stagger class="mt-8 space-y-6">
                            @foreach ($articles as $post)
                                <x-post-card :post="$post"/>
                            @endforeach
                        </div>

                        <a href="{{ route('blog.index') }}" class="group mt-8 inline-flex items-center gap-2 text-sm font-semibold text-primary-700 underline-offset-4 hover:underline">
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
            <section class="section bg-slate-50">
                <div class="container-page grid gap-12 lg:grid-cols-12 lg:gap-16">
                    <div x-data x-reveal class="lg:col-span-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ $number() }} — {{ __('site.nav.faq') }}
                        </p>
                        <h2 class="display mt-5 text-3xl sm:text-4xl">{{ __('site.home.faq_heading') }}</h2>
                        <p class="mt-5 text-[15px] leading-relaxed text-slate-500">{{ __('site.faq.subheading') }}</p>

                        <a href="{{ route('faq.index') }}" class="group btn-secondary mt-8">
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
    {{-- The colophon: a rule, a line of type, and the two ways to reach him. --}}
    @feature('home_cta')
        <section class="bg-white">
            <div x-data x-reveal class="container-page border-t-2 border-slate-900/10 py-20">
                <div class="flex flex-col items-start justify-between gap-10 lg:flex-row lg:items-end">
                    <div class="max-w-2xl">
                        <h2 class="display text-3xl sm:text-5xl">{{ __('site.home.cta_heading') }}</h2>
                        <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">{{ __('site.home.cta_text') }}</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-3">
                        @feature('appointment')
                            <a href="{{ route('appointment.create') }}" class="btn-lg btn-primary">
                                <x-icon name="calendar-check" class="h-5 w-5"/>{{ __('site.actions.book_appointment') }}
                            </a>
                        @endfeature
                        @if ($doctor->phone)
                            <a href="tel:{{ preg_replace('/\s/', '', $doctor->phone) }}" class="btn-lg btn-secondary">
                                <x-icon name="phone" class="h-5 w-5"/>{{ __('site.actions.call_now') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endfeature
</x-layouts.public>
