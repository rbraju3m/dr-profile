<x-layouts.public :title="$album->tr('title')">
    <x-page-hero :title="$album->tr('title')" :subtitle="$album->tr('description')"
                 :breadcrumbs="[__('site.nav.gallery') => route('gallery.index'), $album->tr('title') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page"
             x-data="{ open: false, index: 0, items: @js($items->map(fn ($i) => ['type' => $i->type, 'src' => $i->imageUrl(), 'embed' => $i->embedUrl(), 'title' => $i->tr('title')])->values()) }">

            @if ($items->isEmpty())
                <x-empty-state icon="image" :title="__('site.gallery.empty')"/>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($items as $i => $item)
                        <button type="button" @click="index = {{ $i }}; open = true"
                                class="group relative overflow-hidden rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                            <x-media-frame :src="$item->thumbnailUrl()" :alt="$item->tr('title')"
                                           :icon="$item->type === 'video' ? 'play' : 'image'"
                                           ratio="aspect-square" :seed="$item->id"/>

                            @if ($item->type === 'video')
                                <span class="absolute inset-0 grid place-items-center bg-black/50">
                                    <span class="grid h-12 w-12 place-items-center rounded-full bg-white/95 text-primary-700">
                                        <x-icon name="play" class="h-5 w-5 fill-current"/>
                                    </span>
                                </span>
                            @endif

                            @if ($item->tr('title'))
                                <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/80 to-transparent p-3 text-start text-xs font-medium text-white opacity-0 transition group-hover:opacity-100">
                                    {{ $item->tr('title') }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{ $items->links() }}

                {{-- Lightbox --}}
                <div x-show="open" x-cloak @click.self="open = false"
                     @keydown.escape.window="open = false"
                     @keydown.arrow-right.window="open && (index = (index + 1) % items.length)"
                     @keydown.arrow-left.window="open && (index = (index - 1 + items.length) % items.length)"
                     x-transition.opacity
                     class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4"
                     role="dialog" aria-modal="true">

                    <button type="button" @click="open = false"
                            class="absolute end-4 top-4 grid h-11 w-11 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            aria-label="{{ __('site.nav.close_menu') }}">
                        <x-icon name="x" class="h-5 w-5"/>
                    </button>

                    <button type="button" x-show="items.length > 1"
                            @click="index = (index - 1 + items.length) % items.length"
                            class="absolute start-4 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            aria-label="{{ __('site.actions.previous') }}">
                        <x-icon name="chevron-left" class="h-5 w-5 rtl:rotate-180"/>
                    </button>

                    <button type="button" x-show="items.length > 1"
                            @click="index = (index + 1) % items.length"
                            class="absolute end-4 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            aria-label="{{ __('site.actions.next') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rtl:rotate-180"/>
                    </button>

                    <div class="max-h-[85vh] w-full max-w-4xl" @click.self="open = false">
                        <template x-if="items[index]?.type === 'video' && items[index]?.embed">
                            <div class="aspect-video overflow-hidden rounded-xl bg-black">
                                <iframe :src="items[index].embed" class="h-full w-full" allowfullscreen loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin" :title="items[index].title"></iframe>
                            </div>
                        </template>

                        <template x-if="items[index]?.type !== 'video'">
                            <img :src="items[index]?.src" :alt="items[index]?.title"
                                 class="mx-auto max-h-[80vh] rounded-xl object-contain">
                        </template>

                        <p class="mt-3 text-center text-sm text-white/80" x-text="items[index]?.title"></p>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
