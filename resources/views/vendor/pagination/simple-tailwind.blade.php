@if ($paginator->hasPages())
    <nav class="mt-8 flex items-center justify-center gap-3">
        @if ($paginator->onFirstPage())
            <span class="btn-secondary pointer-events-none opacity-40">{{ __('site.actions.previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-secondary">{{ __('site.actions.previous') }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-secondary">{{ __('site.actions.next') }}</a>
        @else
            <span class="btn-secondary pointer-events-none opacity-40">{{ __('site.actions.next') }}</span>
        @endif
    </nav>
@endif
