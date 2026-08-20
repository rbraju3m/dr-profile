<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="title" :label="__('admin.fields.headline')" :record="$record"/>
                    <x-admin.bilingual name="subtitle" :label="__('admin.fields.subtitle')" :record="$record" type="textarea" rows="3"/>
                    <x-admin.bilingual name="cta_label" :label="__('admin.fields.button_label')" :record="$record"/>
                    <x-admin.input name="cta_url" :label="__('admin.fields.button_link')" :value="$record?->cta_url" placeholder="/en/appointment"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="image" :label="__('admin.fields.desktop_image')" :current="$record?->imageUrl()"/>
                    <x-admin.image-upload name="mobile_image" :label="__('admin.fields.mobile_image')" :current="$record?->mediaUrl('mobile_image')"/>
                    <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                    <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.form-shell>
