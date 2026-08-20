@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'success' => ['bg-accent-50 text-accent-700 ring-accent-200', 'check-circle'],
        'error' => ['bg-rose-50 text-rose-700 ring-rose-200', 'alert-circle'],
        'warning' => ['bg-amber-50 text-amber-800 ring-amber-200', 'alert-triangle'],
        'info' => ['bg-primary-50 text-primary-800 ring-primary-200', 'info'],
    ];
    [$classes, $icon] = $styles[$type] ?? $styles['info'];
@endphp

<div role="{{ $type === 'error' ? 'alert' : 'status' }}"
     {{ $attributes->merge(['class' => "flex gap-3 rounded-xl px-4 py-3 text-sm ring-1 ring-inset $classes"]) }}>
    <x-icon :name="$icon" class="mt-0.5 h-4.5 w-4.5 shrink-0"/>
    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-0.5' : '' }} leading-relaxed">{{ $slot }}</div>
    </div>
</div>
