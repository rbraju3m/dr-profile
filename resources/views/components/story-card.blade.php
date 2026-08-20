@props(['story'])

<a href="{{ route('stories.show', $story) }}" class="card-hover group flex flex-col overflow-hidden">
    <x-media-frame :src="$story->imageUrl()" :alt="$story->tr('title')" icon="heart" ratio="aspect-[16/10]">
        @if ($story->service)
            <span class="absolute start-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-medium text-primary-700 shadow-sm">
                {{ $story->service->tr('name') }}
            </span>
        @endif
    </x-media-frame>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-base font-semibold leading-snug text-slate-900 group-hover:text-primary-700">
            {{ $story->tr('title') }}
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-500">
            {{ Str::limit($story->tr('summary'), 120) }}
        </p>

        <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4 text-xs text-slate-500">
            <x-icon name="user" class="h-3.5 w-3.5"/>
            <span>{{ $story->patient_name }}</span>
            @if ($story->patient_age)
                <span aria-hidden="true">·</span>
                <span class="tabular-nums">{{ bn_digits($story->patient_age) }}</span>
            @endif
            @if ($story->tr('patient_location'))
                <span aria-hidden="true">·</span>
                <span class="truncate">{{ $story->tr('patient_location') }}</span>
            @endif
        </div>
    </div>
</a>
