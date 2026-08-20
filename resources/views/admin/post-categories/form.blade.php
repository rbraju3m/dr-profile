<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="name" :label="__('admin.fields.name')" :record="$record" required/>
                    <x-admin.bilingual name="description" :label="__('admin.fields.description')" :record="$record" type="textarea" rows="3"/>
                    <x-admin.input name="slug" :label="__('admin.fields.slug')" :value="$record?->slug" :hint="__('admin.common.slug_hint')"/>
                </div>
            </x-admin.card>
        </div>

        <x-admin.card>
            <div class="space-y-4">
                <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
            </div>
        </x-admin.card>
    </div>
</x-admin.form-shell>
