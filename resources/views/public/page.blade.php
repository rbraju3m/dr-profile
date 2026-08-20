<x-layouts.public :title="$page->tr('title')" :description="$page->tr('meta_description')">
    <x-page-hero :title="$page->tr('title')" :breadcrumbs="[$page->tr('title') => null]"/>

    @if ($page->mediaUrl('banner_image'))
        <div class="container-page -mt-8">
            <div class="overflow-hidden rounded-2xl shadow-[var(--shadow-lift)]">
                <x-media-frame :src="$page->mediaUrl('banner_image')" :alt="$page->tr('title')" ratio="aspect-[21/9]"/>
            </div>
        </div>
    @endif

    <section class="section bg-white">
        <div class="container-page">
            <div class="prose-content mx-auto max-w-3xl">{!! $page->tr('content') !!}</div>
        </div>
    </section>
</x-layouts.public>
