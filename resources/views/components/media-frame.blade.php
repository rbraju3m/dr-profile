{{--
    Image with a designed fallback, so a missing upload never leaves a broken box.

    Pass `seed` (a slug or title) and neighbouring cards pick different tints from
    the brand palette instead of all rendering the same grey square. Pass `label`
    for a monogram — used where a person's face would go.
--}}
@props([
    'src' => null,
    'alt' => '',
    'icon' => 'image',
    'ratio' => 'aspect-[4/3]',
    'seed' => null,
    'label' => null,
])

@php
    // Five tints that stay inside the medical palette. Chosen deterministically
    // so a card keeps the same colour between page loads.
    $palettes = [
        ['bg-gradient-to-br from-primary-100 via-primary-50 to-white', 'text-primary-400/70', 'text-primary-500/80'],
        ['bg-gradient-to-br from-accent-100 via-accent-50 to-white', 'text-accent-500/60', 'text-accent-600/70'],
        ['bg-gradient-to-br from-slate-200 via-slate-100 to-white', 'text-slate-400/70', 'text-slate-500/80'],
        ['bg-gradient-to-br from-primary-200 via-primary-100 to-primary-50', 'text-primary-500/60', 'text-primary-600/70'],
        ['bg-gradient-to-br from-accent-100 via-primary-50 to-white', 'text-primary-400/70', 'text-primary-500/80'],
    ];

    [$surface, $markTone, $textTone] = $palettes[$seed ? crc32((string) $seed) % count($palettes) : 0];

    $initials = $label
        ? collect(preg_split('/\s+/', trim($label)))
            ->reject(fn ($part) => str_contains($part, '.'))   // drop "Prof." / "Dr."
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('')
        : null;
@endphp

{{-- @container so the monogram can scale with the frame, not the viewport --}}
<div {{ $attributes->merge(['class' => "@container $ratio relative w-full overflow-hidden bg-slate-100"]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
    @else
        <div class="relative grid h-full w-full place-items-center {{ $surface }}">
            {{-- Faint dot field: gives the panel texture so it reads as a designed
                 surface rather than a failed image load. --}}
            <svg class="pointer-events-none absolute inset-0 h-full w-full {{ $markTone }} opacity-40"
                 aria-hidden="true">
                <defs>
                    <pattern id="mf-dots-{{ $seed ? crc32((string) $seed) : 'base' }}"
                             width="16" height="16" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#mf-dots-{{ $seed ? crc32((string) $seed) : 'base' }})"/>
            </svg>

            @if ($initials)
                <span class="relative flex flex-col items-center gap-2">
                    <span class="text-[clamp(2rem,22cqw,4.5rem)] font-bold leading-none tracking-tight {{ $textTone }}"
                          aria-hidden="true">{{ $initials }}</span>
                    <x-icon :name="$icon" class="h-5 w-5 {{ $markTone }}"/>
                </span>
            @else
                <x-icon :name="$icon" class="relative h-10 w-10 {{ $markTone }}"/>
            @endif
        </div>
    @endif

    {{ $slot ?? '' }}
</div>
