<x-layouts.public :title="__('site.stories.heading')">
    <x-page-hero :title="__('site.stories.heading')" :subtitle="__('site.stories.subheading')"
                 :breadcrumbs="[__('site.nav.success_stories') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($services->isNotEmpty())
                <div class="mb-8 flex flex-wrap gap-2">
                    <a href="{{ route('stories.index') }}"
                       @class([
                           'rounded-full px-4 py-2 text-sm font-medium transition',
                           'bg-primary-600 text-white' => ! $activeService,
                           'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:ring-primary-300' => $activeService,
                       ])>{{ __('site.posts.all_categories') }}</a>

                    @foreach ($services as $service)
                        <a href="{{ route('stories.index', ['service' => $service->slug]) }}"
                           @class([
                               'rounded-full px-4 py-2 text-sm font-medium transition',
                               'bg-primary-600 text-white' => $activeService === $service->slug,
                               'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:ring-primary-300' => $activeService !== $service->slug,
                           ])>{{ $service->tr('name') }}</a>
                    @endforeach
                </div>
            @endif

            @if ($stories->isEmpty())
                <x-empty-state icon="heart" :title="__('site.stories.empty')"/>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stories as $story)
                        <x-story-card :story="$story"/>
                    @endforeach
                </div>

                {{ $stories->links() }}
            @endif
        </div>
    </section>
</x-layouts.public>
