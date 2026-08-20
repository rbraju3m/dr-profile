@props(['post'])

@php
    $route = match ($post->type) {
        'event' => 'events.show',
        'blog' => 'blog.show',
        default => 'news.show',
    };
    $date = $post->type === 'event' ? $post->event_start_at : $post->published_at;
@endphp

<a href="{{ route($route, $post) }}" class="card-hover group flex flex-col overflow-hidden">
    <x-media-frame :src="$post->imageUrl()" :alt="$post->tr('title')"
                   :icon="$post->type === 'event' ? 'calendar' : 'file-text'"
                   ratio="aspect-[16/10]" fit="contain" :seed="$post->slug">
        @if ($post->type === 'event')
            <span @class([
                'absolute start-3 top-3 rounded-full px-3 py-1 text-xs font-semibold shadow-sm',
                'bg-accent-600 text-white' => $post->isUpcoming(),
                'bg-white/95 text-slate-600' => ! $post->isUpcoming(),
            ])>
                {{ $post->isUpcoming() ? __('site.posts.upcoming_events') : __('site.posts.past_events') }}
            </span>
        @elseif ($post->category)
            <span class="absolute start-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-medium text-primary-700 shadow-sm">
                {{ $post->category->tr('name') }}
            </span>
        @endif
    </x-media-frame>

    <div class="flex flex-1 flex-col p-5">
        @if ($date)
            <p class="mb-2 flex items-center gap-1.5 text-xs text-slate-400">
                <x-icon name="calendar" class="h-3.5 w-3.5"/>
                <time datetime="{{ $date->toDateString() }}">
                    {{ bn_digits($date->format('j')) }} {{ __('site.months.'.$date->month) }} {{ bn_digits($date->format('Y')) }}
                </time>
                @if ($post->reading_minutes)
                    <span aria-hidden="true">·</span>
                    <span>{{ __('site.posts.reading_time', ['count' => bn_digits($post->reading_minutes)]) }}</span>
                @endif
            </p>
        @endif

        <h3 class="text-base font-semibold leading-snug text-slate-900 group-hover:text-primary-700">
            {{ $post->tr('title') }}
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-500">
            {{ Str::limit($post->tr('excerpt'), 110) }}
        </p>

        @if ($post->type === 'event' && $post->tr('event_venue'))
            <p class="mt-3 flex items-start gap-1.5 text-xs text-slate-500">
                <x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0"/>
                {{ $post->event_is_online ? __('site.posts.event_online') : $post->tr('event_venue') }}
            </p>
        @endif
    </div>
</a>
