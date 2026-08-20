@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'action' => null])

<div x-data x-reveal @class([
    'mb-12 max-w-2xl',
    'mx-auto text-center' => $align === 'center',
    'flex flex-wrap items-end justify-between gap-6 max-w-none' => $align === 'between',
])>
    <div @class(['max-w-2xl' => $align === 'between'])>
        @if ($eyebrow)
            <span class="eyebrow">
                <span class="h-px w-6 bg-primary-400"></span>{{ $eyebrow }}
            </span>
        @endif

        <h2 class="display mt-4 text-[1.75rem] sm:text-4xl">{{ $title }}</h2>

        {{-- Draws itself in as the heading arrives, which is the whole of the
             emphasis: no underline, no box, one hairline. --}}
        <span aria-hidden="true" @class([
            'rule-draw mt-5 block h-[3px] w-14 rounded-full bg-primary-500',
            'mx-auto origin-center' => $align === 'center',
        ])></span>

        @if ($subtitle)
            <p class="mt-5 text-[15px] leading-relaxed text-slate-500 sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($action)
        <div class="shrink-0">{{ $action }}</div>
    @endif
</div>
