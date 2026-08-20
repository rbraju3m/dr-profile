@php
    $locale = app()->getLocale();
    $siteName = setting('site_name_'.$locale) ?: $doctor->fullName();
    $pageTitle = trim($title ?? '');
    $metaDescription = $description ?? $doctor->tr('meta_description') ?? $doctor->tr('short_bio');

    // Structured data so the profile can surface as a physician in search results.
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Physician',
        'name' => $doctor->fullName(),
        'medicalSpecialty' => $doctor->tr('designation'),
        'description' => Str::limit(strip_tags((string) $doctor->tr('short_bio')), 300),
        'url' => route('home'),
        'telephone' => $doctor->phone,
        'email' => $doctor->email,
        'image' => $doctor->photoUrl(),
        'address' => $navChambers->map(fn ($chamber) => [
            '@type' => 'PostalAddress',
            'name' => $chamber->tr('name'),
            'streetAddress' => $chamber->tr('address'),
            'addressLocality' => $chamber->tr('city'),
            'addressCountry' => 'BD',
        ])->values()->all(),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ config('site.locales.'.$locale.'.dir', 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle ? $pageTitle.' — '.$siteName : ($doctor->tr('meta_title') ?: $siteName) }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags((string) $metaDescription), 160) }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Tell search engines about the other language of this exact page. --}}
    @foreach (config('site.locales') as $code => $meta)
        <link rel="alternate" hreflang="{{ $code }}"
              href="{{ url()->current() === url('/'.$locale) ? url('/'.$code) : preg_replace('#/'.$locale.'(/|$)#', '/'.$code.'$1', url()->current(), 1) }}">
    @endforeach

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle ?: $siteName }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags((string) $metaDescription), 200) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($doctor->mediaUrl('og_image') ?? $doctor->photoUrl())
        <meta property="og:image" content="{{ $doctor->mediaUrl('og_image') ?? $doctor->photoUrl() }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🩺</text></svg>">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-slate-50">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-primary-700 focus:px-4 focus:py-2 focus:text-white">
        {{ __('site.skip_to_content') }}
    </a>

    @include('partials.header')

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    @include('partials.footer')

    {{-- Back to top: appears once the visitor is well down a long page. --}}
    <button type="button"
            x-data="{ shown: false }"
            x-init="$watch('shown', () => {})"
            @scroll.window.passive="shown = window.scrollY > 700"
            x-show="shown"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-end="opacity-0 translate-y-3"
            @click="window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
            class="to-top no-print hover:bg-primary-800"
            aria-label="{{ __('site.errors.go_home') }}">
        <x-icon name="chevron-up" class="h-5 w-5"/>
    </button>

    {{-- Mobile sticky booking bar: the single most important action on the site. --}}
    <div class="no-print fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-3 backdrop-blur lg:hidden">
        <div class="flex items-center gap-2">
            @if ($doctor->phone)
                <a href="tel:{{ preg_replace('/\s/', '', $doctor->phone) }}"
                   class="btn-secondary flex-1"
                   aria-label="{{ __('site.actions.call_now') }}">
                    <x-icon name="phone" class="h-4 w-4"/>
                    {{ __('site.actions.call_now') }}
                </a>
            @endif
            <a href="{{ route('appointment.create') }}" class="btn-primary flex-1">
                <x-icon name="calendar-check" class="h-4 w-4"/>
                {{ __('site.actions.book_now') }}
            </a>
        </div>
    </div>
    <div class="h-20 lg:hidden" aria-hidden="true"></div>

    @stack('scripts')
</body>
</html>
