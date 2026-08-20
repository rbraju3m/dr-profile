{{--
    Flips a flag straight from the listing.

    Optimistic: the switch moves at once and rolls back if the write fails,
    because waiting on a round trip to see a switch move feels broken.
--}}
@props(['url', 'column', 'value', 'onLabel' => null, 'offLabel' => null])

<button type="button"
        x-data="toggleSwitch(@js($url), @js($column), {{ $value ? 'true' : 'false' }})"
        @click="flip()"
        :aria-pressed="on"
        :aria-label="on ? @js($onLabel ?? __('admin.common.active')) : @js($offLabel ?? __('admin.common.inactive'))"
        :title="on ? @js($onLabel ?? __('admin.common.active')) : @js($offLabel ?? __('admin.common.inactive'))"
        :disabled="busy"
        :class="on ? 'bg-accent-500' : 'bg-slate-300'"
        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition disabled:opacity-60
               focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
    <span :class="on ? 'translate-x-6' : 'translate-x-1'"
          class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"></span>
</button>
