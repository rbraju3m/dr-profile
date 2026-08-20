<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="title" label="Title" :record="$record" required/>
                    <x-admin.bilingual name="description" label="Description" :record="$record" type="textarea" rows="3"/>
                    <x-admin.input name="slug" label="Slug" :value="$record?->slug" :hint="__('admin.common.slug_hint')"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="cover_image" label="Cover" :current="$record?->mediaUrl('cover_image')"/>
                    <x-admin.input name="event_date" type="date" label="Date" :value="$record?->event_date?->toDateString()"/>
                    <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                    <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
                </div>
            </x-admin.card>

            @if ($record)
                <a href="{{ route('admin.albums.items.index', $record) }}" class="btn-secondary w-full">
                    <x-icon name="image" class="h-4 w-4"/>{{ __('site.gallery.photos') }} &amp; {{ __('site.gallery.videos') }}
                </a>
            @endif
        </div>
    </div>
</x-admin.form-shell>
