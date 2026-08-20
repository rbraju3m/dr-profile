@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="mt-10 flex items-center justify-center gap-1.5">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="grid h-10 w-10 place-items-center rounded-xl text-slate-300" aria-disabled="true">
                <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180"/>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('site.actions.previous') }}"
               class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:text-primary-700">
                <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180"/>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-slate-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="grid h-10 min-w-10 place-items-center rounded-xl bg-primary-600 px-3 text-sm font-semibold tabular-nums text-white">
                            {{ bn_digits($page) }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="grid h-10 min-w-10 place-items-center rounded-xl px-3 text-sm font-medium tabular-nums text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:text-primary-700">
                            {{ bn_digits($page) }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('site.actions.next') }}"
               class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:text-primary-700">
                <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180"/>
            </a>
        @else
            <span class="grid h-10 w-10 place-items-center rounded-xl text-slate-300" aria-disabled="true">
                <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180"/>
            </span>
        @endif
    </nav>
@endif
