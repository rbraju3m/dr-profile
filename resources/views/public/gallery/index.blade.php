<x-layouts.public :title="__('site.gallery.heading')">
    <x-page-hero :title="__('site.gallery.heading')" :breadcrumbs="[__('site.nav.gallery') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($albums->isEmpty())
                <x-empty-state icon="image" :title="__('site.gallery.empty')"/>
            @else
                <div x-data x-reveal.stagger class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($albums as $album)
                        <a href="{{ route('gallery.show', $album) }}" class="card-hover group flex flex-col overflow-hidden">
                            <x-media-frame :src="$album->coverUrl()" :alt="$album->tr('title')" ratio="aspect-[4/3]" :seed="$album->slug">
                                <span class="absolute end-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-medium text-slate-700 shadow-sm dark:bg-slate-900/80">
                                    {{ __('site.gallery.items', ['count' => bn_digits($album->items_count)]) }}
                                </span>
                            </x-media-frame>

                            <div class="p-5">
                                <h2 class="text-[17px] font-semibold leading-snug text-slate-900 transition group-hover:text-primary-700">{{ $album->tr('title') }}</h2>
                                @if ($album->event_date)
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ bn_digits($album->event_date->format('j')) }} {{ __('site.months.'.$album->event_date->month) }} {{ bn_digits($album->event_date->format('Y')) }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
