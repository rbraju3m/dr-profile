<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.select name="type" :label="__('admin.fields.type')" required :value="$record?->type"
                        :options="collect(App\Models\Credential::TYPES)->mapWithKeys(fn ($t) => [$t => __('site.about.'.match ($t) {
                            'education' => 'education', 'experience' => 'experience', 'training' => 'training',
                            'award' => 'awards', 'membership' => 'memberships', default => 'certifications',
                        })])"/>

                    <x-admin.bilingual name="title" :label="__('admin.fields.title')" :record="$record" required/>
                    <x-admin.bilingual name="organization" :label="__('admin.fields.organisation')" :record="$record"/>
                    <x-admin.bilingual name="location" :label="__('admin.fields.location')" :record="$record"/>
                    <x-admin.bilingual name="description" :label="__('admin.fields.description')" :record="$record" type="textarea" rows="3"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.input name="start_year" type="number" :label="__('admin.fields.from')" :value="$record?->start_year"/>
                        <x-admin.input name="end_year" type="number" :label="__('admin.fields.to')" :value="$record?->end_year"/>
                    </div>
                    <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                </div>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-3">
                    <x-admin.toggle name="is_current" :label="__('site.present')" :value="$record?->is_current ?? false"
                                    :hint="__('admin.hints.ongoing')"/>
                    <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.form-shell>
