@php
    $locale = app()->getLocale();
    $links = [
        ['route' => 'about', 'label' => __('site.nav.about')],
        ['route' => 'services.index', 'label' => __('site.nav.services')],
        ['route' => 'stories.index', 'label' => __('site.nav.success_stories')],
        ['route' => 'news.index', 'label' => __('site.nav.news_events')],
        ['route' => 'blog.index', 'label' => __('site.nav.blog')],
        ['route' => 'gallery.index', 'label' => __('site.nav.gallery')],
        ['route' => 'publications.index', 'label' => __('site.nav.publications')],
        ['route' => 'faq.index', 'label' => __('site.nav.faq')],
    ];
@endphp

<footer class="no-print mt-auto bg-primary-950 text-primary-100/80">
    <div class="container-page py-14">
        <div class="grid gap-10 lg:grid-cols-12">

            <div class="lg:col-span-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary-600 text-white">
                        <x-icon name="heart-pulse" class="h-6 w-6"/>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-semibold text-white">{{ $doctor->fullName() ?: setting('site_name_'.$locale) }}</span>
                        <span class="block text-xs">{{ $doctor->tr('designation') }}</span>
                    </span>
                </div>

                <p class="mt-4 max-w-sm text-sm leading-relaxed">
                    {{ setting('footer_note_'.$locale) ?: Str::limit(strip_tags((string) $doctor->tr('short_bio')), 150) }}
                </p>

                <div class="mt-5 flex items-center gap-2">
                    @foreach ([
                        'facebook' => $doctor->facebook_url,
                        'youtube' => $doctor->youtube_url,
                        'linkedin' => $doctor->linkedin_url,
                        'instagram' => $doctor->instagram_url,
                    ] as $network => $url)
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 transition hover:bg-primary-600 hover:text-white"
                               aria-label="{{ ucfirst($network) }}">
                                <x-icon :name="$network" class="h-4 w-4"/>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-3">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">{{ __('site.footer.quick_links') }}</h2>
                <ul class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm lg:grid-cols-1">
                    @foreach ($links as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" class="transition hover:text-white">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">{{ __('site.footer.chambers') }}</h2>
                <ul class="space-y-4 text-sm">
                    @foreach ($navChambers->take(3) as $chamber)
                        <li>
                            <a href="{{ route('chambers.show', $chamber) }}" class="font-medium text-white transition hover:text-primary-200">
                                {{ $chamber->tr('name') }}
                            </a>
                            <p class="mt-0.5 text-xs leading-relaxed">{{ Str::limit($chamber->tr('address'), 60) }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">{{ __('site.footer.contact') }}</h2>
                <ul class="space-y-3 text-sm">
                    @if ($doctor->hotline)
                        <li class="flex items-start gap-2.5">
                            <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 opacity-70"/>
                            <a href="tel:{{ $doctor->hotline }}" class="tabular-nums transition hover:text-white">{{ bn_digits($doctor->hotline) }}</a>
                        </li>
                    @endif
                    @if ($doctor->email)
                        <li class="flex items-start gap-2.5">
                            <x-icon name="mail" class="mt-0.5 h-4 w-4 shrink-0 opacity-70"/>
                            <a href="mailto:{{ $doctor->email }}" class="break-all transition hover:text-white">{{ $doctor->email }}</a>
                        </li>
                    @endif
                    @if (setting('contact_address_'.$locale))
                        <li class="flex items-start gap-2.5">
                            <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 opacity-70"/>
                            <span class="leading-relaxed">{{ setting('contact_address_'.$locale) }}</span>
                        </li>
                    @endif
                </ul>

                <a href="{{ route('contact.create') }}" class="btn-secondary mt-5 w-full !bg-white/10 !text-white !ring-white/20 hover:!bg-white/20">
                    {{ __('site.nav.contact') }}
                </a>
            </div>
        </div>

        <p class="mt-10 rounded-xl bg-white/5 px-4 py-3 text-xs leading-relaxed">
            <x-icon name="info" class="me-1 inline h-3.5 w-3.5 align-[-2px]"/>
            {{ __('site.footer.disclaimer') }}
        </p>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page flex flex-col items-center justify-between gap-3 py-5 text-xs sm:flex-row">
            @php $owner = $doctor->fullName() ?: setting('site_name_'.$locale); @endphp
            <p>© {{ bn_digits(date('Y')) }}@if ($owner) {{ $owner }}.@endif {{ __('site.footer.rights') }}</p>
            <div class="flex items-center gap-4">
                @foreach ($footerPages as $page)
                    <a href="{{ route('pages.show', $page) }}" class="transition hover:text-white">{{ $page->tr('title') }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
