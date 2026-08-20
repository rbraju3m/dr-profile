<x-layouts.public :title="__('site.nav.services')">
    <x-page-hero :title="__('site.home.expertise_heading')" :subtitle="__('site.home.expertise_subheading')"
                 :breadcrumbs="[__('site.nav.services') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($services->isEmpty())
                <x-empty-state icon="stethoscope" :title="__('site.posts.empty')"/>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <x-service-card :service="$service"/>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
