<x-layouts.public :title="$page->tr('title')" :description="$page->tr('meta_description')">
    <x-page-hero :title="$page->tr('title')" :breadcrumbs="[$page->tr('title') => null]"/>

    <section class="section bg-white">
        <div class="container-page">
            <div class="prose-content mx-auto max-w-3xl">{!! $page->tr('content') !!}</div>
        </div>
    </section>
</x-layouts.public>
