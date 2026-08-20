@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<section class="relative overflow-hidden bg-primary-900 text-white">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-25">
        <div class="absolute -end-24 -top-24 h-72 w-72 rounded-full bg-primary-500 blur-3xl"></div>
        <div class="absolute -bottom-32 start-1/4 h-72 w-72 rounded-full bg-accent-500 blur-3xl"></div>
    </div>

    <div class="container-page relative py-12 sm:py-16">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="flex flex-wrap items-center gap-1.5 text-xs text-primary-200">
                <li><a href="{{ route('home') }}" class="hover:text-white">{{ __('site.nav.home') }}</a></li>
                @foreach ($breadcrumbs as $label => $url)
                    <li aria-hidden="true"><x-icon name="chevron-right" class="h-3.5 w-3.5 rtl:rotate-180"/></li>
                    <li>
                        @if ($url)
                            <a href="{{ $url }}" class="hover:text-white">{{ $label }}</a>
                        @else
                            <span class="text-white">{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <h1 class="max-w-3xl text-3xl font-bold text-balance text-white sm:text-4xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-[15px] leading-relaxed text-primary-100">{{ $subtitle }}</p>
        @endif
    </div>
</section>
