{{--
    Choose a glyph instead of typing its name.

    The field used to be free text, so "hero" and "admin" were saved and the
    public page drew a bare circle. Every name here is one the site can
    actually draw; a value that is not gets flagged rather than silently
    rendering as nothing.
--}}
@props(['name' => 'icon', 'label' => null, 'value' => null, 'hint' => null])

@php
    $current = old($name, $value);
    $unknown = filled($current) && ! App\Support\Icons::has($current);
@endphp

<div x-data="iconPicker(@js($current), @js(App\Support\Icons::names()))">
    <label class="field-label" for="{{ $name }}-search">{{ $label ?? __('admin.fields.icon') }}</label>

    <input type="hidden" name="{{ $name }}" :value="chosen">

    <div class="flex items-center gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary-600">
            <template x-if="chosen">
                <span x-html="glyph(chosen)" class="contents"></span>
            </template>
            <template x-if="!chosen">
                <span class="text-xs font-medium text-slate-400">—</span>
            </template>
        </span>

        <input id="{{ $name }}-search" type="search" x-model="query" autocomplete="off"
               placeholder="{{ __('admin.icons.search') }}"
               class="field-input flex-1">

        <button type="button" x-show="chosen" @click="chosen = null"
                class="btn-ghost shrink-0 px-3 py-2 text-xs">{{ __('admin.icons.clear') }}</button>
    </div>

    @if ($unknown)
        <p class="mt-2 text-xs font-medium text-amber-700">
            {{ __('admin.icons.unknown', ['name' => $current]) }}
        </p>
    @endif

    @if ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    <div class="mt-3 grid max-h-56 grid-cols-6 gap-1.5 overflow-y-auto rounded-xl border border-slate-200 p-2 sm:grid-cols-8">
        <template x-for="name in matches()" :key="name">
            <button type="button" @click="chosen = name" :title="name"
                    :class="chosen === name ? 'bg-primary-600 text-white ring-primary-600' : 'text-slate-500 ring-transparent hover:bg-slate-100 hover:text-slate-800'"
                    class="grid aspect-square place-items-center rounded-lg ring-1 ring-inset transition">
                <span x-html="glyph(name)" class="contents"></span>
            </button>
        </template>

        <p x-show="!matches().length" class="col-span-full px-2 py-6 text-center text-xs text-slate-400">
            {{ __('admin.icons.none') }}
        </p>
    </div>

    <p class="mt-1.5 text-xs text-slate-400">
        <span x-text="chosen || '—'" class="font-medium tabular-nums"></span>
    </p>

    @error($name) <p class="field-error">{{ $message }}</p> @enderror
</div>

@once
    @push('scripts')
        {{-- One copy of the path data for the whole page, whatever the field is called. --}}
        <script type="application/json" id="icon-paths">@json(App\Support\Icons::paths())</script>
    @endpush
@endonce
