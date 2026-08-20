<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="question" :label="__('admin.fields.question')" :record="$record" required/>
                    <x-admin.bilingual name="answer" :label="__('admin.fields.answer')" :record="$record" type="rich" required/>
                </div>
            </x-admin.card>
        </div>

        <x-admin.card>
            <div class="space-y-4">
                <x-admin.select name="group" :label="__('admin.fields.group')" required :value="$record?->group"
                                :options="collect(App\Models\Faq::GROUPS)->mapWithKeys(fn ($g) => [$g => __('site.faq.groups.'.$g)])"/>
                <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
            </div>
        </x-admin.card>
    </div>
</x-admin.form-shell>
