@php
    $listRoute = match ($post->type) {
        'event' => ['events.index', __('site.nav.events')],
        'blog' => ['blog.index', __('site.nav.blog')],
        default => ['news.index', __('site.nav.news')],
    };
    $date = $post->type === 'event' ? $post->event_start_at : $post->published_at;
@endphp

<x-layouts.public :title="$post->tr('title')" :description="$post->tr('excerpt')">
    <x-page-hero :title="$post->tr('title')"
                 :breadcrumbs="[$listRoute[1] => route($listRoute[0]), Str::limit($post->tr('title'), 40) => null]"/>

    {{-- Reading progress: a thin line under the header on long reads. --}}
    <div x-data="readingProgress()" @scroll.window.passive="track()"
         class="no-print sticky top-0 z-40 h-0.5 bg-transparent" aria-hidden="true">
        <div class="h-full bg-primary-500 transition-[width] duration-150 ease-out"
             :style="`width: ${progress}%`"></div>
    </div>

    <section class="section bg-white">
        <div class="container-page grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                <div class="mb-6 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    @if ($post->category)
                        <span class="chip">{{ $post->category->tr('name') }}</span>
                    @endif
                    @if ($date)
                        <span class="flex items-center gap-1.5">
                            <x-icon name="calendar" class="h-4 w-4"/>
                            <time datetime="{{ $date->toDateString() }}">
                                {{ bn_digits($date->format('j')) }} {{ __('site.months.'.$date->month) }} {{ bn_digits($date->format('Y')) }}
                            </time>
                        </span>
                    @endif
                    @if ($post->reading_minutes)
                        <span class="flex items-center gap-1.5">
                            <x-icon name="clock" class="h-4 w-4"/>
                            {{ __('site.posts.reading_time', ['count' => bn_digits($post->reading_minutes)]) }}
                        </span>
                    @endif
                </div>

                @if ($post->imageUrl())
                    <div class="mb-8 overflow-hidden rounded-2xl">
                        <x-media-frame :src="$post->imageUrl()" :alt="$post->tr('title')" fit="natural" ratio="aspect-[16/9]"
                                       :icon="$post->type === 'event' ? 'calendar' : 'file-text'" :seed="$post->slug"/>
                    </div>
                @endif

                @if ($post->type === 'event')
                    <div class="mb-8 grid gap-4 rounded-2xl bg-primary-50 p-5 sm:grid-cols-2">
                        <div class="flex gap-3">
                            <x-icon name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-primary-600"/>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-primary-700">{{ __('site.posts.event_when') }}</p>
                                @php
                                    // An end time on its own reads as the same day. When the event
                                    // runs past midnight the closing date has to be said, or a
                                    // three-day conference is advertised as one afternoon.
                                    $day = fn ($at) => $at ? bn_digits($at->format('j')).' '.__('site.months.'.$at->month) : null;
                                    $spansDays = $post->event_end_at
                                        && $post->event_start_at
                                        && ! $post->event_end_at->isSameDay($post->event_start_at);
                                @endphp
                                <p class="mt-1 text-sm font-medium text-primary-900">
                                    {{ $day($post->event_start_at) }}, {{ App\Support\Week::time($post->event_start_at) }}
                                    @if ($spansDays)
                                        – {{ $day($post->event_end_at) }}, {{ App\Support\Week::time($post->event_end_at) }}
                                    @elseif ($post->event_end_at)
                                        – {{ App\Support\Week::time($post->event_end_at) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <x-icon name="map-pin" class="mt-0.5 h-5 w-5 shrink-0 text-primary-600"/>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-primary-700">{{ __('site.posts.event_where') }}</p>
                                <p class="mt-1 text-sm font-medium text-primary-900">
                                    {{ $post->event_is_online ? __('site.posts.event_online') : $post->tr('event_venue') }}
                                </p>
                            </div>
                        </div>

                        @if ($post->event_registration_url && $post->isUpcoming())
                            <div class="sm:col-span-2">
                                <a href="{{ $post->event_registration_url }}" target="_blank" rel="noopener noreferrer" class="btn-primary w-full">
                                    {{ __('site.posts.register') }}<x-icon name="external-link" class="h-4 w-4"/>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <div id="article-body" class="prose-content">{!! $post->tr('content') !!}</div>

                @if ($post->tags)
                    <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-6">
                        <span class="text-sm text-slate-500">{{ __('site.posts.tags') }}:</span>
                        @foreach ($post->tags as $tag)
                            <span class="chip bg-slate-100 text-slate-600">#{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-5 lg:sticky lg:top-28">
                    @if ($related->isNotEmpty())
                        <div class="card p-6">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('site.posts.related') }}</h2>
                            <ul class="mt-4 space-y-4">
                                @foreach ($related as $item)
                                    <li>
                                        <a href="{{ route($listRoute[0] === 'events.index' ? 'events.show' : ($listRoute[0] === 'blog.index' ? 'blog.show' : 'news.show'), $item) }}"
                                           class="group flex gap-3">
                                            <span class="h-14 w-14 shrink-0 overflow-hidden rounded-lg">
                                                <x-media-frame :src="$item->imageUrl()" :alt="$item->tr('title')" ratio="aspect-square" :seed="$item->slug"/>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="line-clamp-2 text-sm font-medium leading-snug text-slate-800 group-hover:text-primary-700">
                                                    {{ $item->tr('title') }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @feature('appointment')
                        <div class="card bg-primary-900 p-6 text-white">
                            <h2 class="text-base font-semibold text-white">{{ __('site.home.cta_heading') }}</h2>
                            <p class="mt-2 text-sm text-primary-100">{{ __('site.home.cta_text') }}</p>
                            <a href="{{ route('appointment.create') }}" class="btn mt-4 w-full btn-invert">
                                {{ __('site.actions.book_now') }}
                            </a>
                        </div>
                    @endfeature
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
