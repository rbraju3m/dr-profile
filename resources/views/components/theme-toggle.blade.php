{{--
    Cycles the page through light, dark and "follow my device".

    Three states rather than two because a plain flip is a one-way door: the
    moment a reader touches it their cookie outranks their phone's own setting
    for a year, with no way back.

    The class on <html> is what the stylesheet reads, so the switch changes it
    directly — no reload — and writes the cookie so the next request arrives
    the same way round. Hidden entirely when the admin has taken the choice
    back; `staff` keeps it for the back office whatever the public site offers.
--}}
@props(['class' => '', 'staff' => false])

@php
    // Each label names what the next click does, so they are keyed by where
    // the switch stands now.
    $labels = [
        'light' => __('site.theme.to_dark'),
        'dark' => __('site.theme.to_system'),
        'system' => __('site.theme.to_light'),
    ];
@endphp

@if ($staff || App\Support\Theme::switchable())
    <button type="button"
            x-data="themeToggle(@js(App\Support\Theme::preference($staff)), @js($labels))"
            @click="cycle()"
            :title="label"
            :aria-label="label"
            {{ $attributes->merge(['class' => 'grid h-8 w-8 place-items-center rounded-lg transition hover:bg-black/10 dark:hover:bg-white/10 '.$class]) }}>
        <span x-show="theme === 'light'" x-cloak><x-icon name="sun" class="h-4 w-4"/></span>
        <span x-show="theme === 'dark'" x-cloak><x-icon name="moon" class="h-4 w-4"/></span>
        <span x-show="theme === 'system'" x-cloak><x-icon name="monitor" class="h-4 w-4"/></span>
    </button>
@endif
