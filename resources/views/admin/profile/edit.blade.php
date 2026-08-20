<x-layouts.admin :title="__('admin.profile.title')">
    <x-admin.page-header :title="__('admin.profile.title')" :subtitle="__('admin.profile.intro')">
        <x-slot:actions>
            <a href="{{ route('about') }}" target="_blank" class="btn-secondary">
                <x-icon name="external-link" class="h-4 w-4"/>{{ __('admin.actions.view') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-admin.card :title="__('admin.profile.identity')">
                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-1">
                                <x-admin.bilingual name="title" :label="__('admin.fields.title')" :record="$profile" :hint="__('admin.hints.title_example')"/>
                            </div>
                            <div class="sm:col-span-2">
                                <x-admin.bilingual name="name" :label="__('admin.fields.name')" :record="$profile" required/>
                            </div>
                        </div>
                        <x-admin.bilingual name="designation" :label="__('admin.fields.designation')" :record="$profile"/>
                        <x-admin.bilingual name="tagline" :label="__('admin.fields.tagline')" :record="$profile"/>
                        <x-admin.bilingual name="degrees" :label="__('admin.fields.degrees')" :record="$profile" type="textarea" rows="2"/>
                        <x-admin.bilingual name="languages" :label="__('site.about.languages')" :record="$profile"/>
                    </div>
                </x-admin.card>

                <x-admin.card :title="__('admin.profile.biography')">
                    <div class="space-y-4">
                        <x-admin.bilingual name="short_bio" :label="__('admin.fields.short_bio')" :record="$profile" type="textarea" rows="4"/>
                        <x-admin.bilingual name="bio" :label="__('admin.fields.full_biography')" :record="$profile" type="rich"/>
                        <x-admin.bilingual name="philosophy" :label="__('site.about.philosophy')" :record="$profile" type="rich"/>
                    </div>
                </x-admin.card>

                <x-admin.card :title="__('admin.profile.contact')">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="email" type="email" :label="__('admin.fields.email')" :value="$profile->email"/>
                        <x-admin.input name="phone" :label="__('admin.fields.phone')" :value="$profile->phone"/>
                        <x-admin.input name="hotline" :label="__('site.contact.hotline')" :value="$profile->hotline"/>
                        <x-admin.input name="whatsapp" :label="__('admin.fields.whatsapp')" :value="$profile->whatsapp"/>
                    </div>
                </x-admin.card>

                <x-admin.card :title="__('admin.profile.social')">
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok', 'linkedin_url' => 'LinkedIn', 'x_url' => 'X'] as $field => $networkLabel)
                            <x-admin.input :name="$field" :label="$networkLabel" :value="$profile->{$field}"/>
                        @endforeach
                    </div>
                </x-admin.card>

                <x-admin.card :title="__('admin.profile.seo')">
                    <div class="space-y-4">
                        <x-admin.bilingual name="meta_title" :label="__('admin.fields.meta_title')" :record="$profile"/>
                        <x-admin.bilingual name="meta_description" :label="__('admin.fields.meta_description')" :record="$profile" type="textarea" rows="2"/>
                    </div>
                </x-admin.card>
            </div>

            <div class="space-y-6">
                <x-admin.card :title="__('admin.profile.media')">
                    <div class="space-y-5">
                        <x-admin.image-upload name="photo" :label="__('admin.fields.portrait')" :current="$profile->photoUrl()"/>
                        <x-admin.image-upload name="hero_image" :label="__('admin.fields.hero_image')" :current="$profile->heroImageUrl()"/>
                        <x-admin.image-upload name="og_image" :label="__('admin.fields.share_image')" :current="$profile->mediaUrl('og_image')"/>
                        <x-admin.image-upload name="cv_file" :label="__('admin.fields.cv')" accept="application/pdf" :current="null"/>
                        @if ($profile->cv_file)
                            <a href="{{ $profile->mediaUrl('cv_file') }}" target="_blank"
                               class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800">
                                <x-icon name="file-text" class="h-4 w-4"/>{{ __('site.actions.download_cv') }}
                            </a>
                        @endif
                    </div>
                </x-admin.card>

                <x-admin.card>
                    <div class="space-y-4">
                        <x-admin.select name="gender" :label="__('admin.fields.gender')" :value="$profile->gender" :placeholder="__('admin.common.none')"
                                        :options="['male' => __('admin.gender.male'), 'female' => __('admin.gender.female'), 'other' => __('admin.gender.other')]"/>
                        <x-admin.input name="experience_years" type="number" :label="__('site.about.experience_years')" :value="$profile->experience_years"/>
                        <x-admin.input name="bmdc_reg_no" :label="__('site.about.registration')" :value="$profile->bmdc_reg_no"/>
                    </div>
                </x-admin.card>
            </div>
        </div>

        <x-admin.form-actions/>
    </form>
</x-layouts.admin>
