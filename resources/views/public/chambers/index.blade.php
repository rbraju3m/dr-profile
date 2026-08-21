<x-layouts.public :title="__('site.chamber.heading')">
    <x-page-hero :title="__('site.home.chambers_heading')" :subtitle="__('site.home.chambers_subheading')"
                 :breadcrumbs="[__('site.nav.chambers') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($chambers->isEmpty())
                <x-empty-state icon="building" :title="__('site.chamber.no_chambers')"/>
            @else
                <x-chamber-grid :chambers="$chambers" :next-dates="$nextDates ?? []"/>
            @endif
        </div>
    </section>
</x-layouts.public>
