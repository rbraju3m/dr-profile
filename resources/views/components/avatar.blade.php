@props(['src' => null, 'name' => '', 'class' => 'h-12 w-12'])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" loading="lazy"
         {{ $attributes->merge(['class' => "$class rounded-full object-cover ring-2 ring-white"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$class grid place-items-center rounded-full bg-primary-100 font-semibold text-primary-700 ring-2 ring-white"]) }}
          aria-hidden="true">{{ $initials ?: '·' }}</span>
@endif
