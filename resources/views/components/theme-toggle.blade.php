{{--
    Flips the page between light and dark.

    The class on <html> is what the stylesheet reads, so the switch changes it
    directly — no reload — and writes the cookie so the next request is served
    the same way round. Hidden entirely when the admin has taken the choice
    back.
--}}
@props(['class' => ''])

@if (App\Support\Theme::switchable())
    <button type="button"
            x-data="themeToggle()"
            @click="flip()"
            :aria-pressed="dark"
            :title="dark ? @js(__('site.theme.to_light')) : @js(__('site.theme.to_dark'))"
            :aria-label="dark ? @js(__('site.theme.to_light')) : @js(__('site.theme.to_dark'))"
            {{ $attributes->merge(['class' => 'grid h-8 w-8 place-items-center rounded-lg transition hover:bg-black/10 dark:hover:bg-white/10 '.$class]) }}>
        <span x-show="!dark" x-cloak><x-icon name="moon" class="h-4 w-4"/></span>
        <span x-show="dark" x-cloak><x-icon name="sun" class="h-4 w-4"/></span>
    </button>
@endif
