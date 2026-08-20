<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="title" label="Title" :record="$record" required/>
                    <x-admin.bilingual name="condition" :label="__('site.stories.condition')" :record="$record" type="textarea" rows="3"/>
                    <x-admin.bilingual name="summary" label="Summary" :record="$record" type="textarea" rows="3"/>
                    <x-admin.bilingual name="content" label="Story" :record="$record" type="textarea" rows="12"/>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('site.stories.patient')">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="patient_name" :label="__('site.booking.patient_name')" :value="$record?->patient_name"/>
                    <x-admin.input name="patient_age" type="number" :label="__('site.booking.patient_age')" :value="$record?->patient_age"/>
                    <div class="sm:col-span-2">
                        <x-admin.bilingual name="patient_location" label="Location" :record="$record"/>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card :title="__('admin.profile.seo')">
                <div class="space-y-4">
                    <x-admin.bilingual name="meta_title" label="Meta title" :record="$record"/>
                    <x-admin.bilingual name="meta_description" label="Meta description" :record="$record" type="textarea" rows="2"/>
                    <x-admin.input name="slug" label="Slug" :value="$record?->slug" :hint="__('admin.common.slug_hint')"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="image" :label="__('admin.common.image')" :current="$record?->imageUrl()"/>
                    <x-admin.input name="video_url" label="Video URL" :value="$record?->video_url" placeholder="https://youtube.com/embed/…"/>
                    <x-admin.select name="service_id" :label="__('admin.nav.services')" :options="$services"
                                    :value="$record?->service_id" :placeholder="__('admin.common.none')"/>
                    <x-admin.input name="treatment_date" type="date" :label="__('site.stories.treated_on')"
                                   :value="$record?->treatment_date?->toDateString()"/>
                    <x-admin.input name="published_at" type="date" :label="__('site.posts.published_on')"
                                   :value="$record?->published_at?->toDateString() ?? now()->toDateString()"/>
                    <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                </div>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-3">
                    <x-admin.toggle name="is_featured" :label="__('admin.common.featured')" :value="$record?->is_featured ?? false"/>
                    <x-admin.toggle name="is_published" :label="__('admin.common.published')" :value="$record?->is_published ?? true"/>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.form-shell>
