<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="label" :label="__('admin.fields.label')" :record="$record" required/>
                </div>
            </x-admin.card>
        </div>

        <x-admin.card>
            <div class="space-y-4">
                <x-admin.input name="value" type="number" :label="__('admin.fields.value')" :value="$record?->value ?? 0" required/>
                <x-admin.input name="suffix" :label="__('admin.fields.suffix')" :value="$record?->suffix" placeholder="+"/>
                <x-admin.icon-picker name="icon" :value="$record?->icon"/>
                <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
            </div>
        </x-admin.card>
    </div>
</x-admin.form-shell>
