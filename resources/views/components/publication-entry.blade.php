{{--
    One line of a bibliography.

    `abstract` is off by default: the About page shows five of these in a narrow
    sidebar, where a paragraph each would bury the list. The publications page
    turns it on, which is the only place the abstract the admin typed has ever
    had to land.
--}}
@props(['publication', 'abstract' => false])

<div class="flex gap-4">
    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500">
        <x-icon name="file-text" class="h-5 w-5"/>
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="chip bg-slate-100 text-slate-600">{{ __('site.publications.types.'.$publication->type) }}</span>
            @if ($publication->year)
                <span class="text-xs tabular-nums text-slate-400">{{ bn_digits($publication->year) }}</span>
            @endif
        </div>

        <h3 class="mt-1.5 text-[15px] font-semibold leading-snug text-slate-900">{{ $publication->tr('title') }}</h3>

        @if ($publication->authors)
            <p class="mt-1 text-sm text-slate-500">{{ $publication->authors }}</p>
        @endif

        @if ($publication->tr('venue'))
            <p class="mt-0.5 text-sm italic text-slate-500">
                {{ $publication->tr('venue') }}@if ($publication->volume), {{ $publication->volume }}@endif @if ($publication->pages), {{ bn_digits($publication->pages) }}@endif
            </p>
        @endif

        @if ($abstract && $publication->tr('abstract'))
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $publication->tr('abstract') }}</p>
        @endif

        @if ($publication->url || $publication->doi || $publication->file)
            <a href="{{ $publication->url ?: ($publication->doi ? 'https://doi.org/'.$publication->doi : $publication->mediaUrl('file')) }}"
               target="_blank" rel="noopener noreferrer"
               class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-800">
                {{ __('site.publications.view_paper') }}
                <x-icon name="external-link" class="h-3.5 w-3.5"/>
            </a>
        @endif
    </div>
</div>
