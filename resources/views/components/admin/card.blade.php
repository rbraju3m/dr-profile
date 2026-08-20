@props(['title' => null, 'subtitle' => null, 'flush' => false])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title)
        <header class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
            @if ($subtitle)
                <p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    <div @class(['p-5' => ! $flush])>{{ $slot }}</div>
</section>
