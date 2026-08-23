<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.bilingual name="title" :label="__('admin.fields.title')" :record="$record" required/>
                    <x-admin.input name="authors" :label="__('admin.fields.authors')" :value="$record?->authors" placeholder="Rahman A, Islam S"/>
                    <x-admin.bilingual name="venue" :label="__('admin.fields.venue')" :record="$record"/>
                    <x-admin.bilingual name="abstract" :label="__('admin.fields.abstract')" :record="$record" type="textarea" rows="6"/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.select name="type" :label="__('admin.fields.type')" required :value="$record?->type"
                                    :options="collect(App\Models\Publication::TYPES)->mapWithKeys(fn ($t) => [$t => __('site.publications.types.'.$t)])"/>
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.input name="year" type="number" :label="__('admin.fields.year')" :value="$record?->year"/>
                        <x-admin.input name="volume" :label="__('admin.fields.volume')" :value="$record?->volume"/>
                    </div>
                    <x-admin.input name="pages" :label="__('admin.fields.pages')" :value="$record?->pages"/>
                    <x-admin.input name="doi" :label="__('admin.fields.doi')" :value="$record?->doi"/>
                    <x-admin.input name="url" :label="__('admin.fields.link')" :value="$record?->url"/>
                    <x-admin.image-upload name="file" :label="__('admin.fields.pdf')" accept="application/pdf"
                                          :current="$record?->mediaUrl('file')" :hint="__('admin.hints.pdf_limit', ['max' => App\Support\Uploads::maxLabel()])"/>
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
