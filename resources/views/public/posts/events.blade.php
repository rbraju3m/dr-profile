<x-layouts.public :title="__('site.posts.events_heading')">
    <x-page-hero :title="__('site.posts.events_heading')" :breadcrumbs="[__('site.nav.events') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page space-y-14">
            <div>
                <x-section-heading align="start" :title="__('site.posts.upcoming_events')"/>

                @if ($upcoming->isEmpty())
                    <x-empty-state icon="calendar" :title="__('site.posts.empty')"/>
                @else
                    <x-card-grid x-data x-reveal.stagger two-up="md" :count="$upcoming->count()">
                        @foreach ($upcoming as $post)
                            <x-post-card :post="$post"/>
                        @endforeach
                    </x-card-grid>
                @endif
            </div>

            @if ($past->isNotEmpty())
                <div>
                    <x-section-heading align="start" :title="__('site.posts.past_events')"/>

                    <x-card-grid two-up="md" :count="$past->count()">
                        @foreach ($past as $post)
                            <x-post-card :post="$post"/>
                        @endforeach
                    </x-card-grid>

                    {{ $past->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
