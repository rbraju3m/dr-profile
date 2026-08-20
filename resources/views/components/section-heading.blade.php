@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'action' => null])

<div @class([
    'mb-10 max-w-2xl',
    'mx-auto text-center' => $align === 'center',
    'flex flex-wrap items-end justify-between gap-4 max-w-none' => $align === 'between',
])>
    <div @class(['max-w-2xl' => $align === 'between'])>
        @if ($eyebrow)
            <span class="eyebrow">
                <span class="h-px w-6 bg-primary-400"></span>{{ $eyebrow }}
            </span>
        @endif

        <h2 class="mt-3 text-2xl font-bold text-balance sm:text-3xl lg:text-[2rem]">{{ $title }}</h2>

        @if ($subtitle)
            <p class="mt-3 text-[15px] leading-relaxed text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($action)
        <div class="shrink-0">{{ $action }}</div>
    @endif
</div>
