{{--
    The line under a success story: who, how old, where.

    Every part is optional and the story survives without any of them, so the
    separators are drawn between what is there rather than beside each field.
    Written once because it is drawn on the story card and on the editorial
    homepage, and the two had already drifted: both printed the name unguarded,
    so a story with none left a lone user icon and a leading "·" behind.
--}}
@props(['story'])

@php
    $parts = array_values(array_filter([
        $story->patient_name,
        $story->patient_age ? bn_digits($story->patient_age) : null,
        $story->tr('patient_location'),
    ]));
@endphp

@if ($parts)
    <div {{ $attributes->merge(['class' => 'story-meta flex items-center gap-2 text-xs text-slate-500']) }}>
        <x-icon name="user" class="h-3.5 w-3.5 shrink-0"/>

        @foreach ($parts as $i => $part)
            @if ($i)
                <span aria-hidden="true">·</span>
            @endif
            <span class="truncate">{{ $part }}</span>
        @endforeach
    </div>
@endif
