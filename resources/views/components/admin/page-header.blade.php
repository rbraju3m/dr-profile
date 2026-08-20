@props(['title', 'subtitle' => null, 'back' => null])

<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-2 inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800">
                <x-icon name="arrow-left" class="h-4 w-4 rtl:rotate-180"/>{{ __('admin.actions.back') }}
            </a>
        @endif
        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>
