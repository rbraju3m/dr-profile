<x-layouts.admin :title="$album->title_en">
    <x-admin.page-header :title="$album->title_en" :back="route('admin.albums.index')"/>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($items->isEmpty())
                <x-empty-state icon="image" :title="__('site.gallery.empty')"/>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($items as $item)
                        <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <x-media-frame :src="$item->thumbnailUrl()" :alt="$item->title_en"
                                           :icon="$item->type === 'video' ? 'play' : 'image'" ratio="aspect-square"/>
                            <div class="p-2.5">
                                <p class="truncate text-xs text-slate-600">{{ $item->title_en ?: '—' }}</p>
                            </div>
                            <div class="absolute end-2 top-2 rounded-lg bg-white/90 opacity-0 shadow-sm transition group-hover:opacity-100">
                                <x-admin.delete-button :action="route('admin.items.destroy', $item)"/>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <x-admin.card :title="__('admin.actions.create')">
            <form method="POST" action="{{ route('admin.albums.items.store', $album) }}" enctype="multipart/form-data"
                  class="space-y-4" x-data="{ type: 'image' }">
                @csrf

                <div>
                    <span class="field-label">Type</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['image' => __('site.gallery.photos'), 'video' => __('site.gallery.videos')] as $value => $typeLabel)
                            <label class="cursor-pointer rounded-xl border px-3 py-2.5 text-center text-sm transition"
                                   :class="type === '{{ $value }}' ? 'border-primary-500 bg-primary-50 font-medium text-primary-700' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="type" value="{{ $value }}" x-model="type" class="sr-only">
                                {{ $typeLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="type === 'image'">
                    <label for="images" class="field-label">{{ __('site.gallery.photos') }}</label>
                    <input id="images" type="file" name="images[]" multiple accept="image/*"
                           class="block w-full text-sm text-slate-500 file:me-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-700">
                    <p class="mt-1 text-xs text-slate-400">Up to 20 images at a time.</p>
                    @error('images.*') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div x-show="type === 'video'" x-cloak>
                    <x-admin.input name="video_url" label="Video URL" placeholder="https://youtube.com/watch?v=…"/>
                </div>

                <x-admin.input name="title_en" :label="__('admin.common.english')"/>
                <x-admin.input name="title_bn" :label="__('admin.common.bangla')"/>
                <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" value="0"/>

                <button type="submit" class="btn-primary w-full">
                    <x-icon name="plus" class="h-4 w-4"/>{{ __('admin.actions.create') }}
                </button>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
