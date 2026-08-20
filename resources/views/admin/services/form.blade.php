<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card :title="__('admin.common.english').' / '.__('admin.common.bangla')">
                <div class="space-y-4">
                    <x-admin.bilingual name="name" :label="__('admin.nav.services')" :record="$record" required/>
                    <x-admin.bilingual name="short_description" :label="__('site.home.expertise_subheading')" :record="$record" type="textarea" rows="3"/>
                    <x-admin.bilingual name="description" :label="__('admin.profile.biography')" :record="$record" type="rich"/>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('admin.profile.seo')">
                <div class="space-y-4">
                    <x-admin.bilingual name="meta_title" :label="__('admin.fields.meta_title')" :record="$record"/>
                    <x-admin.bilingual name="meta_description" :label="__('admin.fields.meta_description')" :record="$record" type="textarea" rows="2"/>
                    <x-admin.input name="slug" :label="__('admin.fields.slug')" :value="$record?->slug" :hint="__('admin.common.slug_hint')"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="image" :label="__('admin.common.image')" :current="$record?->imageUrl()"/>
                    <x-admin.input name="icon" :label="__('admin.fields.icon')" :value="$record?->icon"
                                   hint="heart-pulse, activity, gauge, waves, shield-check, droplet, cpu, siren, stethoscope"/>
                    <x-admin.input name="fee" type="number" step="0.01" :label="__('admin.fields.fee')" :value="$record?->fee"/>
                    <x-admin.bilingual name="duration" :label="__('site.duration')" :record="$record"/>
                    <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                </div>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-3">
                    <x-admin.toggle name="is_featured" :label="__('admin.common.featured')" :value="$record?->is_featured ?? false"/>
                    <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.form-shell>
