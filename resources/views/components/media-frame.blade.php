{{-- Image with a designed fallback, so a missing upload never leaves a broken box. --}}
@props(['src' => null, 'alt' => '', 'icon' => 'image', 'ratio' => 'aspect-[4/3]'])

<div {{ $attributes->merge(['class' => "$ratio relative w-full overflow-hidden bg-slate-100"]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
    @else
        <div class="grid h-full w-full place-items-center bg-gradient-to-br from-primary-50 via-slate-50 to-accent-50">
            <x-icon :name="$icon" class="h-10 w-10 text-primary-300"/>
        </div>
    @endif
    {{ $slot ?? '' }}
</div>
