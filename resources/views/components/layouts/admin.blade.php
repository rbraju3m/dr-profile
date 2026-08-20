@php
    $user = auth()->user();
    $groups = [
        __('admin.nav.practice') => [
            ['route' => 'admin.dashboard', 'label' => __('admin.nav.dashboard'), 'icon' => 'layout-dashboard'],
            ['route' => 'admin.appointments.index', 'label' => __('admin.nav.appointments'), 'icon' => 'calendar-check', 'badge' => $pendingAppointments ?? null],
            ['route' => 'admin.chambers.index', 'label' => __('admin.nav.chambers'), 'icon' => 'building'],
            ['route' => 'admin.exceptions.index', 'label' => __('admin.nav.exceptions'), 'icon' => 'calendar-x'],
            ['route' => 'admin.services.index', 'label' => __('admin.nav.services'), 'icon' => 'stethoscope'],
        ],
        __('admin.nav.content') => [
            ['route' => 'admin.profile.edit', 'label' => __('admin.nav.profile'), 'icon' => 'user'],
            ['route' => 'admin.credentials.index', 'label' => __('admin.nav.credentials'), 'icon' => 'graduation-cap'],
            ['route' => 'admin.stories.index', 'label' => __('admin.nav.stories'), 'icon' => 'heart'],
            ['route' => 'admin.posts.index', 'label' => __('admin.nav.posts'), 'icon' => 'file-text'],
            ['route' => 'admin.post-categories.index', 'label' => __('admin.nav.post_categories'), 'icon' => 'filter'],
            ['route' => 'admin.testimonials.index', 'label' => __('admin.nav.testimonials'), 'icon' => 'quote'],
            ['route' => 'admin.publications.index', 'label' => __('admin.nav.publications'), 'icon' => 'book-open'],
            ['route' => 'admin.albums.index', 'label' => __('admin.nav.albums'), 'icon' => 'image'],
            ['route' => 'admin.faqs.index', 'label' => __('admin.nav.faqs'), 'icon' => 'info'],
        ],
        __('admin.nav.system') => [
            ['route' => 'admin.messages.index', 'label' => __('admin.nav.messages'), 'icon' => 'inbox', 'badge' => $unreadMessages ?? null],
            ['route' => 'admin.pages.index', 'label' => __('admin.nav.pages'), 'icon' => 'file-text'],
            ['route' => 'admin.sliders.index', 'label' => __('admin.nav.sliders'), 'icon' => 'image'],
            ['route' => 'admin.stats.index', 'label' => __('admin.nav.stats'), 'icon' => 'activity'],
            ['route' => 'admin.settings.edit', 'label' => __('admin.nav.settings'), 'icon' => 'settings', 'admin' => true],
            ['route' => 'admin.users.index', 'label' => __('admin.nav.users'), 'icon' => 'users', 'admin' => true],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? __('admin.panel')).' — '.__('admin.panel') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100" x-data="{ sidebar: false }">

    {{-- Sidebar --}}
    <div x-show="sidebar" x-cloak class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebar = false"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 start-0 z-50 flex w-72 flex-col bg-primary-950 transition-transform lg:translate-x-0">

        <div class="flex h-16 shrink-0 items-center justify-between gap-2 px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-white">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-primary-600">
                    <x-icon name="heart-pulse" class="h-5 w-5"/>
                </span>
                <span class="text-sm font-semibold">{{ __('admin.panel') }}</span>
            </a>
            <button type="button" @click="sidebar = false" class="grid h-9 w-9 place-items-center rounded-lg text-primary-200 hover:bg-white/10 lg:hidden">
                <x-icon name="x" class="h-5 w-5"/>
            </button>
        </div>

        {{--
            The menu is taller than a laptop viewport, and it used to simply stop
            at whichever item ran out of room — with the divider above "View
            site" making that cut look like the end of the list. The fades show
            there is more in either direction, and the active item is scrolled
            into view so a deep page is never hidden off-screen.
        --}}
        <div class="relative min-h-0 flex-1"
             x-data="{
                atTop: true,
                atBottom: true,
                measure() {
                    const el = $refs.nav
                    this.atTop = el.scrollTop <= 4
                    this.atBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 4
                },
                init() {
                    this.$nextTick(() => {
                        $refs.nav.querySelector('[data-active]')
                            ?.scrollIntoView({ block: 'nearest' })
                        this.measure()
                    })
                },
             }">

            <div x-show="!atTop" x-cloak x-transition.opacity aria-hidden="true"
                 class="pointer-events-none absolute inset-x-0 top-0 z-10 h-8 bg-gradient-to-b from-primary-950 to-transparent"></div>
            <div x-show="!atBottom" x-cloak x-transition.opacity aria-hidden="true"
                 class="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-10 bg-gradient-to-t from-primary-950 to-transparent"></div>

            <nav x-ref="nav" @scroll="measure()" @resize.window="measure()"
                 class="sidebar-nav h-full space-y-6 overflow-y-auto px-3 pb-6">
            @foreach ($groups as $groupLabel => $items)
                @php
                    $visible = collect($items)->reject(fn ($i) => ($i['admin'] ?? false) && ! $user?->isAdmin());
                @endphp
                @continue ($visible->isEmpty())

                <div>
                    <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-primary-400">{{ $groupLabel }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($visible as $item)
                            @php $active = request()->routeIs(Str::replaceLast('index', '*', $item['route'])) || request()->routeIs($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @if ($active) data-active @endif
                                   @class([
                                       'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition',
                                       'bg-primary-600 font-medium text-white' => $active,
                                       'text-primary-100/80 hover:bg-white/10 hover:text-white' => ! $active,
                                   ])>
                                    <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0"/>
                                    <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold tabular-nums text-amber-950">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </nav>
        </div>

        <div class="shrink-0 border-t border-white/10 p-3">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-primary-100/80 transition hover:bg-white/10 hover:text-white">
                <x-icon name="external-link" class="h-4 w-4"/>{{ __('admin.nav.view_site') }}
            </a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="lg:ps-72">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6">
            <button type="button" @click="sidebar = true" class="grid h-10 w-10 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden">
                <x-icon name="menu" class="h-5 w-5"/>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-semibold text-slate-900">{{ $title ?? __('admin.panel') }}</h1>
            </div>

            {{-- Admin UI language --}}
            <form method="POST" action="{{ route('admin.language') }}" class="hidden sm:block">
                @csrf
                <div class="flex rounded-lg bg-slate-100 p-0.5">
                    @foreach (config('site.locales') as $code => $meta)
                        <button type="submit" name="locale" value="{{ $code }}"
                                @class([
                                    'rounded-md px-2.5 py-1 text-xs font-medium transition',
                                    'bg-white text-slate-900 shadow-sm' => app()->getLocale() === $code,
                                    'text-slate-500 hover:text-slate-800' => app()->getLocale() !== $code,
                                ])>{{ $meta['native'] }}</button>
                    @endforeach
                </div>
            </form>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg p-1 hover:bg-slate-100" :aria-expanded="open">
                    <x-avatar :src="$user?->avatarUrl()" :name="$user?->name ?? ''" class="h-8 w-8"/>
                    <x-icon name="chevron-down" class="h-4 w-4 text-slate-400"/>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
                     class="absolute end-0 top-full mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                    <div class="border-b border-slate-100 px-3 py-2.5">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $user?->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $user?->email }}</p>
                        <span class="mt-1.5 inline-flex rounded-full bg-primary-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-primary-700">
                            {{ $user?->role }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50">
                            <x-icon name="log-out" class="h-4 w-4"/>{{ __('admin.auth.sign_out') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Result of a drag, shown briefly so reordering is not silent. --}}
        <div id="reorder-note" class="hidden"
             data-saved="{{ __('admin.common.order_saved') }}"
             data-failed="{{ __('admin.common.order_failed') }}"></div>

        <main class="p-4 sm:p-6 lg:p-8">
            <x-admin.flash/>
            {{ $slot }}
        </main>
    </div>
</body>
</html>
