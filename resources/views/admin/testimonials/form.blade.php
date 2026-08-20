<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.input name="patient_name" :label="__('site.booking.patient_name')" :value="$record?->patient_name" required/>
                    <x-admin.bilingual name="patient_title" :label="__('admin.fields.occupation')" :record="$record"/>
                    <x-admin.bilingual name="content" :label="__('admin.fields.testimonial')" :record="$record" type="textarea" rows="6" required/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="photo" :label="__('admin.fields.photo')" :current="$record?->photoUrl()"/>
                    <x-admin.select name="rating" :label="__('admin.fields.rating')" :value="$record?->rating ?? 5" required
                                    :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']"/>
                    <x-admin.select name="service_id" :label="__('admin.nav.services')" :options="$services"
                                    :value="$record?->service_id" :placeholder="__('admin.common.none')"/>
                    <x-admin.input name="visited_on" type="date" :label="__('admin.fields.visited_on')" :value="$record?->visited_on?->toDateString()"/>
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
