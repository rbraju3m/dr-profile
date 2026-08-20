<x-layouts.public :title="$story->tr('title')" :description="$story->tr('summary')">
    <x-page-hero :title="$story->tr('title')" :subtitle="$story->tr('summary')"
                 :breadcrumbs="[__('site.nav.success_stories') => route('stories.index'), Str::limit($story->tr('title'), 40) => null]"/>

    {{-- Reading progress: a thin line under the header on long reads. --}}
    <div x-data="readingProgress()" @scroll.window.passive="track()"
         class="no-print sticky top-0 z-40 h-0.5 bg-transparent" aria-hidden="true">
        <div class="h-full bg-primary-500 transition-[width] duration-150 ease-out"
             :style="`width: ${progress}%`"></div>
    </div>

    <section class="section bg-white">
        <div class="container-page grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                @if ($story->imageUrl())
                    <div class="mb-8 overflow-hidden rounded-2xl">
                        <x-media-frame :src="$story->imageUrl()" :alt="$story->tr('title')" fit="natural" ratio="aspect-[16/9]"
                                       icon="heart" :seed="$story->slug"/>
                    </div>
                @endif

                @if ($story->tr('condition'))
                    <div class="mb-8 rounded-2xl border-s-4 border-primary-500 bg-primary-50/60 p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-primary-700">{{ __('site.stories.condition') }}</h2>
                        <p class="mt-2 leading-relaxed text-primary-900/80">{{ $story->tr('condition') }}</p>
                    </div>
                @endif

                <div id="article-body" class="prose-content">{!! $story->tr('content') !!}</div>

                @if ($story->embedUrl())
                    @php
                        $portrait = App\Support\VideoEmbed::isPortrait($story->video_url);
                        $fromFacebook = App\Support\VideoEmbed::isFacebookVideo($story->video_url);
                    @endphp

                    {{-- Reels are shot vertically; framing one at 16:9 leaves a
                         sliver between two black bands. --}}
                    <div @class([
                        'mt-8 overflow-hidden rounded-2xl bg-slate-900',
                        'aspect-video' => ! $portrait,
                        'mx-auto aspect-[9/16] max-w-sm' => $portrait,
                    ])>
                        <iframe src="{{ $story->embedUrl() }}" title="{{ $story->tr('title') }}" loading="lazy"
                                allowfullscreen scrolling="no" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                class="h-full w-full"></iframe>
                    </div>

                    @if ($fromFacebook)
                        {{-- A Facebook embed shows an empty frame when the video
                             is not public, and says nothing. There is no way to
                             detect that from here, so always leave a way out. --}}
                        <a href="{{ $story->video_url }}" target="_blank" rel="noopener noreferrer"
                           class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-800">
                            <x-icon name="facebook" class="h-4 w-4"/>{{ __('site.stories.watch_on_facebook') }}
                            <x-icon name="external-link" class="h-3.5 w-3.5"/>
                        </a>
                    @endif
                @elseif ($story->video_url)
                    {{-- Not a platform we can frame; a link beats an empty black box. --}}
                    <a href="{{ $story->video_url }}" target="_blank" rel="noopener noreferrer"
                       class="btn-secondary mt-8">
                        <x-icon name="play" class="h-4 w-4"/>{{ __('site.stories.watch') }}
                        <x-icon name="external-link" class="h-3.5 w-3.5"/>
                    </a>
                @endif
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-5 lg:sticky lg:top-28">
                    <div class="card p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('site.stories.patient') }}</h2>

                        <dl class="mt-4 space-y-3 text-sm">
                            @foreach ([
                                ['user', __('site.booking.patient_name'), $story->patient_name],
                                ['calendar', __('site.booking.patient_age'), $story->patient_age ? bn_digits($story->patient_age) : null],
                                ['map-pin', __('site.chamber.address'), $story->tr('patient_location')],
                                ['stethoscope', __('site.stories.treatment'), $story->service?->tr('name')],
                                ['calendar-check', __('site.stories.treated_on'), $story->treatment_date ? bn_digits($story->treatment_date->format('j')).' '.__('site.months.'.$story->treatment_date->month).' '.bn_digits($story->treatment_date->format('Y')) : null],
                            ] as [$icon, $label, $value])
                                @if ($value)
                                    <div class="flex gap-3">
                                        <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                        <div class="min-w-0">
                                            <dt class="text-xs text-slate-500">{{ $label }}</dt>
                                            <dd class="font-medium text-slate-800">{{ $value }}</dd>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </dl>

                        <a href="{{ route('appointment.create') }}" class="btn-primary mt-5 w-full">
                            <x-icon name="calendar-check" class="h-4 w-4"/>{{ __('site.actions.book_appointment') }}
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section bg-slate-50">
            <div class="container-page">
                <x-section-heading :title="__('site.stories.related')"/>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-story-card :story="$item"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
