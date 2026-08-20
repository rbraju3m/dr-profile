@props(['variant' => 'inline'])

@php
    $current = app()->getLocale();
    $locales = config('site.locales');

    // Swap the locale segment of the current URL so the visitor stays on this page.
    $urlFor = function (string $code) use ($current) {
        $url = url()->current();
        $swapped = preg_replace('#/'.$current.'(/|$)#', '/'.$code.'$1', $url, 1);

        return $swapped.(request()->getQueryString() ? '?'.request()->getQueryString() : '');
    };
@endphp

@if ($variant === 'buttons')
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-2']) }}>
        @foreach ($locales as $code => $meta)
            <a href="{{ $urlFor($code) }}" hreflang="{{ $code }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-center text-sm font-medium ring-1 ring-inset transition',
                   'bg-primary-600 text-white ring-primary-600' => $code === $current,
                   'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50' => $code !== $current,
               ])>{{ $meta['native'] }}</a>
        @endforeach
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-1 text-xs']) }}>
        <x-icon name="languages" class="me-1 h-3.5 w-3.5 opacity-70"/>
        @foreach ($locales as $code => $meta)
            <a href="{{ $urlFor($code) }}" hreflang="{{ $code }}"
               @class([
                   'rounded px-2 py-0.5 font-medium transition',
                   'bg-white/15 text-white' => $code === $current,
                   'hover:bg-white/10 hover:text-white' => $code !== $current,
               ])>{{ $meta['native'] }}</a>
        @endforeach
    </div>
@endif
