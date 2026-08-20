{{--
    The band every inner page opens with. It borrows the homepage hero's
    language — the hairline grid, the display type, the accent rule — so a
    reader arriving on a chamber page recognises where they are.
--}}
@props(['title', 'subtitle' => null, 'breadcrumbs' => [], 'actions' => null])

<section class="relative overflow-hidden bg-primary-900 text-white">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        <div class="absolute -end-24 -top-32 h-80 w-80 rounded-full bg-primary-500/30 blur-3xl"></div>
        <div class="absolute -bottom-40 start-1/4 h-80 w-80 rounded-full bg-accent-500/20 blur-3xl"></div>

        <div class="absolute inset-0 opacity-[0.07]"
             style="background-image:linear-gradient(to right,#fff 1px,transparent 1px),linear-gradient(to bottom,#fff 1px,transparent 1px);background-size:64px 64px;mask-image:linear-gradient(to bottom,#000,transparent 80%)"></div>
    </div>

    <div class="container-page relative py-14 sm:py-20">
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-primary-200">
                <li><a href="{{ route('home') }}" class="transition hover:text-white">{{ __('site.nav.home') }}</a></li>
                @foreach ($breadcrumbs as $label => $url)
                    <li aria-hidden="true"><x-icon name="chevron-right" class="h-3.5 w-3.5 opacity-60 rtl:rotate-180"/></li>
                    <li>
                        @if ($url)
                            <a href="{{ $url }}" class="transition hover:text-white">{{ $label }}</a>
                        @else
                            <span class="font-medium text-white">{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-3xl">
                <h1 class="display text-3xl text-white sm:text-4xl lg:text-[2.75rem]">{{ $title }}</h1>

                <span aria-hidden="true" class="rule-in mt-5 block h-[3px] w-14 origin-left rounded-full bg-accent-500"></span>

                @if ($subtitle)
                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-primary-100">{{ $subtitle }}</p>
                @endif
            </div>

            @if ($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endif
        </div>
    </div>
</section>
