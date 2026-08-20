{{--
    Renders whichever social accounts have been filled in on the profile.

    Both the header and the footer used to hold their own hardcoded list, so a
    network added in the admin might appear in one, the other, or neither.
    DoctorProfile::socialLinks() is now the only list.
--}}
@props(['doctor', 'variant' => 'bare'])

@php $links = $doctor->socialLinks(); @endphp

@if ($links)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        @foreach ($links as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
               aria-label="{{ $link['label'] }}"
               @class([
                   'transition',
                   'rounded p-1 hover:bg-white/10 hover:text-white' => $variant === 'bare',
                   'grid h-9 w-9 place-items-center rounded-lg bg-white/10 hover:bg-primary-600 hover:text-white' => $variant === 'boxed',
               ])>
                <x-icon :name="$link['network']" @class(['h-3.5 w-3.5' => $variant === 'bare', 'h-4 w-4' => $variant === 'boxed'])/>
            </a>
        @endforeach
    </div>
@endif
