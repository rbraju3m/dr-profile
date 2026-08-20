<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="title" :label="__('admin.fields.title')" :record="$record" required/>
                    <x-admin.bilingual name="content" :label="__('admin.fields.content')" :record="$record" type="rich"/>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('admin.profile.seo')">
                <div class="space-y-4">
                    <x-admin.bilingual name="meta_title" :label="__('admin.fields.meta_title')" :record="$record"/>
                    <x-admin.bilingual name="meta_description" :label="__('admin.fields.meta_description')" :record="$record" type="textarea" rows="2"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="banner_image" :label="__('admin.fields.banner')" :current="$record?->mediaUrl('banner_image')"/>
                    <x-admin.input name="slug" :label="__('admin.fields.slug')" :value="$record?->slug" :hint="__('admin.common.slug_hint')"/>
                    @if ($record)
                        <a href="{{ route('pages.show', $record) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800">
                            <x-icon name="external-link" class="h-4 w-4"/>{{ __('admin.actions.view') }}
                        </a>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-3">
                    <x-admin.toggle name="show_in_footer" :label="__('admin.fields.show_in_footer')" :value="$record?->show_in_footer ?? false"/>
                    <x-admin.toggle name="is_published" :label="__('admin.common.published')" :value="$record?->is_published ?? true"/>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.form-shell>
