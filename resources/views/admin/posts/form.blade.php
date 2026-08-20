<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div x-data="{ type: '{{ old('type', $record?->type ?? 'news') }}' }" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <div>
                        <span class="field-label">Type <span class="text-rose-500">*</span></span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['news' => __('site.nav.news'), 'event' => __('site.nav.events'), 'blog' => __('site.nav.blog')] as $value => $optionLabel)
                                <label class="cursor-pointer rounded-xl border px-3 py-2.5 text-center text-sm transition"
                                       :class="type === '{{ $value }}' ? 'border-primary-500 bg-primary-50 font-medium text-primary-700' : 'border-slate-200 text-slate-600 hover:border-primary-300'">
                                    <input type="radio" name="type" value="{{ $value }}" x-model="type" class="sr-only">
                                    {{ $optionLabel }}
                                </label>
                            @endforeach
                        </div>
                        @error('type') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <x-admin.bilingual name="title" label="Title" :record="$record" required/>
                    <x-admin.bilingual name="excerpt" label="Excerpt" :record="$record" type="textarea" rows="3"/>
                    <x-admin.bilingual name="content" label="Content" :record="$record" type="textarea" rows="14"/>
                </div>
            </x-admin.card>

            {{-- Event-only block --}}
            <div x-show="type === 'event'" x-cloak>
                <x-admin.card :title="__('site.nav.events')">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="event_start_at" type="datetime-local" :label="__('site.posts.event_when')"
                                       :value="$record?->event_start_at?->format('Y-m-d\TH:i')"/>
                        <x-admin.input name="event_end_at" type="datetime-local" label="Ends"
                                       :value="$record?->event_end_at?->format('Y-m-d\TH:i')"/>
                        <div class="sm:col-span-2">
                            <x-admin.bilingual name="event_venue" :label="__('site.posts.event_where')" :record="$record"/>
                        </div>
                        <div class="sm:col-span-2">
                            <x-admin.input name="event_registration_url" label="Registration link" :value="$record?->event_registration_url"/>
                        </div>
                        <div class="sm:col-span-2">
                            <x-admin.toggle name="event_is_online" :label="__('site.posts.event_online')" :value="$record?->event_is_online ?? false"/>
                        </div>
                    </div>
                </x-admin.card>
            </div>

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
                    <x-admin.select name="post_category_id" :label="__('site.posts.category')" :options="$categories"
                                    :value="$record?->post_category_id" :placeholder="__('admin.common.none')"/>
                    <x-admin.input name="published_at" type="date" :label="__('site.posts.published_on')"
                                   :value="$record?->published_at?->toDateString() ?? now()->toDateString()"/>
                    <x-admin.input name="tags" label="Tags" :value="$record?->tags ? implode(', ', $record->tags) : null"
                                   hint="Comma separated"/>
                    <div x-show="type === 'blog'" x-cloak>
                        <x-admin.input name="reading_minutes" type="number" label="Reading minutes" :value="$record?->reading_minutes"/>
                    </div>
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
