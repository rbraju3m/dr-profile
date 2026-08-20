@php
    $locale = app()->getLocale();
    $primaryNav = [
        ['route' => 'home', 'label' => __('site.nav.home')],
        ['route' => 'about', 'label' => __('site.nav.about')],
        ['route' => 'services.index', 'label' => __('site.nav.services')],
        ['route' => 'chambers.index', 'label' => __('site.nav.chambers')],
        ['route' => 'stories.index', 'label' => __('site.nav.success_stories')],
    ];
    $moreNav = [
        ['route' => 'news.index', 'label' => __('site.nav.news'), 'icon' => 'file-text'],
        ['route' => 'events.index', 'label' => __('site.nav.events'), 'icon' => 'calendar'],
        ['route' => 'blog.index', 'label' => __('site.nav.blog'), 'icon' => 'book-open'],
        ['route' => 'gallery.index', 'label' => __('site.nav.gallery'), 'icon' => 'image'],
        ['route' => 'publications.index', 'label' => __('site.nav.publications'), 'icon' => 'graduation-cap'],
        ['route' => 'faq.index', 'label' => __('site.nav.faq'), 'icon' => 'info'],
    ];
@endphp

<header x-data="{ open: false, more: false, scrolled: false }"
        @scroll.window="scrolled = window.scrollY > 8"
        class="no-print sticky top-0 z-50">

    {{-- Utility strip: contact details and language, hidden on small screens --}}
    <div class="hidden bg-primary-900 text-primary-100 lg:block">
        <div class="container-page flex h-10 items-center justify-between text-xs">
            <div class="flex items-center gap-6">
                @if ($doctor->hotline)
                    <a href="tel:{{ $doctor->hotline }}" class="flex items-center gap-1.5 hover:text-white">
                        <x-icon name="phone" class="h-3.5 w-3.5"/>
                        {{ __('site.contact.hotline') }}: <span class="font-semibold tabular-nums">{{ bn_digits($doctor->hotline) }}</span>
                    </a>
                @endif
                @if ($doctor->email)
                    <a href="mailto:{{ $doctor->email }}" class="flex items-center gap-1.5 hover:text-white">
                        <x-icon name="mail" class="h-3.5 w-3.5"/>{{ $doctor->email }}
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    @foreach (['facebook' => $doctor->facebook_url, 'youtube' => $doctor->youtube_url, 'linkedin' => $doctor->linkedin_url] as $network => $url)
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="rounded p-1 hover:bg-white/10 hover:text-white" aria-label="{{ ucfirst($network) }}">
                                <x-icon :name="$network" class="h-3.5 w-3.5"/>
                            </a>
                        @endif
                    @endforeach
                </div>
                <x-language-switcher class="text-primary-100"/>
            </div>
        </div>
    </div>

    {{-- Main bar --}}
    <div class="border-b border-slate-200 bg-white/95 backdrop-blur transition-shadow"
         :class="scrolled && 'shadow-sm'">
        <div class="container-page flex h-16 items-center justify-between gap-4 lg:h-20">

            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-600 text-white">
                    <x-icon name="heart-pulse" class="h-6 w-6"/>
                </span>
                <span class="leading-tight">
                    <span class="block text-[15px] font-semibold text-slate-900">{{ $doctor->fullName() }}</span>
                    <span class="block text-xs text-slate-500">{{ Str::limit($doctor->tr('designation'), 42) }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="{{ __('site.nav.menu') }}">
                @foreach ($primaryNav as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'rounded-full px-3.5 py-2 text-sm font-medium transition',
                           'bg-primary-50 text-primary-700' => request()->routeIs($item['route']),
                           'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($item['route']),
                       ])>{{ $item['label'] }}</a>
                @endforeach

                <div class="relative" @mouseenter="more = true" @mouseleave="more = false">
                    <button type="button" @click="more = !more" :aria-expanded="more"
                            class="flex items-center gap-1 rounded-full px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                        {{ __('site.nav.menu') }}
                        <x-icon name="chevron-down" class="h-4 w-4 transition" ::class="more && 'rotate-180'"/>
                    </button>

                    <div x-show="more" x-cloak x-transition.opacity.duration.150ms
                         class="absolute end-0 top-full w-60 pt-2">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-[var(--shadow-lift)]">
                            @foreach ($moreNav as $item)
                                <a href="{{ route($item['route']) }}"
                                   @class([
                                       'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition',
                                       'bg-primary-50 text-primary-700' => request()->routeIs($item['route']),
                                       'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs($item['route']),
                                   ])>
                                    <x-icon :name="$item['icon']" class="h-4 w-4 text-slate-400"/>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('appointment.create') }}" class="btn-primary hidden sm:inline-flex">
                    <x-icon name="calendar-check" class="h-4 w-4"/>
                    {{ __('site.nav.appointment') }}
                </a>

                <button type="button" @click="open = true"
                        class="grid h-11 w-11 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden"
                        :aria-expanded="open" aria-controls="mobile-menu" aria-label="{{ __('site.nav.menu') }}">
                    <x-icon name="menu" class="h-6 w-6"/>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="open" x-cloak class="lg:hidden" role="dialog" aria-modal="true" id="mobile-menu">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50" @click="open = false"></div>

        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             @keydown.escape.window="open = false"
             class="fixed inset-y-0 end-0 z-50 flex w-[85%] max-w-sm flex-col bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <span class="font-semibold text-slate-900">{{ __('site.nav.menu') }}</span>
                <button type="button" @click="open = false"
                        class="grid h-10 w-10 place-items-center rounded-lg text-slate-500 hover:bg-slate-100"
                        aria-label="{{ __('site.nav.close_menu') }}">
                    <x-icon name="x" class="h-5 w-5"/>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4">
                <div class="space-y-1">
                    @foreach (array_merge($primaryNav, $moreNav) as $item)
                        <a href="{{ route($item['route']) }}"
                           @class([
                               'flex items-center gap-3 rounded-xl px-4 py-3 text-[15px] font-medium transition',
                               'bg-primary-50 text-primary-700' => request()->routeIs($item['route']),
                               'text-slate-700 hover:bg-slate-50' => ! request()->routeIs($item['route']),
                           ])>
                            @isset($item['icon'])
                                <x-icon :name="$item['icon']" class="h-4 w-4 text-slate-400"/>
                            @endisset
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <a href="{{ route('contact.create') }}"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 text-[15px] font-medium text-slate-700 hover:bg-slate-50">
                        <x-icon name="mail" class="h-4 w-4 text-slate-400"/>
                        {{ __('site.nav.contact') }}
                    </a>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('site.nav.language') }}</p>
                    <x-language-switcher variant="buttons"/>
                </div>
            </nav>

            <div class="border-t border-slate-200 p-4">
                <a href="{{ route('appointment.create') }}" class="btn-primary btn-lg w-full">
                    <x-icon name="calendar-check" class="h-5 w-5"/>
                    {{ __('site.nav.appointment') }}
                </a>
            </div>
        </div>
    </div>
</header>
