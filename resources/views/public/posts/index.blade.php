@php
    $heading = __("site.posts.{$type}_heading");
    $routeName = $type === 'blog' ? 'blog.index' : 'news.index';
@endphp

<x-layouts.public :title="$heading">
    <x-page-hero :title="$heading" :breadcrumbs="[$heading => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if ($posts->isEmpty())
                    <x-empty-state icon="file-text" :title="__('site.posts.empty')"/>
                @else
                    <div x-data x-reveal.stagger class="grid gap-6 sm:grid-cols-2">
                        @foreach ($posts as $post)
                            <x-post-card :post="$post"/>
                        @endforeach
                    </div>

                    {{ $posts->links() }}
                @endif
            </div>

            <aside class="lg:col-span-4">
                <div class="space-y-5 lg:sticky lg:top-28">
                    <form method="GET" action="{{ route($routeName) }}" class="card p-5">
                        <label for="q" class="field-label">{{ __('site.actions.search') }}</label>
                        <div class="flex gap-2">
                            <input id="q" name="q" value="{{ $filters['q'] }}"
                                   placeholder="{{ __('site.posts.search_placeholder') }}" class="field-input">
                            <button type="submit" class="btn-primary shrink-0 !px-4" aria-label="{{ __('site.actions.search') }}">
                                <x-icon name="search" class="h-4 w-4"/>
                            </button>
                        </div>
                        @if ($filters['category'])
                            <input type="hidden" name="category" value="{{ $filters['category'] }}">
                        @endif
                    </form>

                    @if ($categories->isNotEmpty())
                        <div class="card p-5">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('site.posts.category') }}</h2>
                            <ul class="mt-3 space-y-1">
                                <li>
                                    <a href="{{ route($routeName, array_filter(['q' => $filters['q']])) }}"
                                       @class([
                                           'block rounded-lg px-3 py-2 text-sm transition',
                                           'bg-primary-50 font-medium text-primary-700' => ! $filters['category'],
                                           'text-slate-600 hover:bg-slate-50' => $filters['category'],
                                       ])>{{ __('site.posts.all_categories') }}</a>
                                </li>
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route($routeName, array_filter(['category' => $category->slug, 'q' => $filters['q']])) }}"
                                           @class([
                                               'block rounded-lg px-3 py-2 text-sm transition',
                                               'bg-primary-50 font-medium text-primary-700' => $filters['category'] === $category->slug,
                                               'text-slate-600 hover:bg-slate-50' => $filters['category'] !== $category->slug,
                                           ])>{{ $category->tr('name') }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @feature('appointment')
                        <div class="card bg-primary-900 p-6 text-white">
                            <h2 class="text-base font-semibold text-white">{{ __('site.home.cta_heading') }}</h2>
                            <p class="mt-2 text-sm text-primary-100">{{ __('site.home.cta_text') }}</p>
                            <a href="{{ route('appointment.create') }}" class="btn mt-4 w-full btn-invert">
                                {{ __('site.actions.book_now') }}
                            </a>
                        </div>
                    @endfeature
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
