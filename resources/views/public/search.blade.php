<x-layouts.public :title="$query ? __('site.search.results_for', ['query' => $query]) : __('site.actions.search')">
    <x-page-hero :title="__('site.actions.search')"
                 :breadcrumbs="[__('site.actions.search') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            <form method="GET" action="{{ route('search') }}" class="mx-auto mb-10 max-w-2xl" role="search">
                <label for="q" class="sr-only">{{ __('site.search.placeholder') }}</label>
                <div class="flex gap-2">
                    <input id="q" name="q" value="{{ $query }}" autofocus
                           placeholder="{{ __('site.search.placeholder') }}"
                           class="field-input py-3 text-base">
                    <button type="submit" class="btn-primary btn-lg shrink-0">
                        <x-icon name="search" class="h-5 w-5"/>
                        <span class="hidden sm:inline">{{ __('site.actions.search') }}</span>
                    </button>
                </div>
            </form>

            @if ($query === '')
                <p class="text-center text-sm text-slate-500">{{ __('site.search.placeholder') }}</p>
            @elseif ($groups->isEmpty())
                <x-empty-state icon="search"
                               :title="__('site.search.no_results')"
                               :text="__('site.search.results_for', ['query' => $query])"/>
            @else
                <div class="mx-auto max-w-3xl space-y-10">
                    <p class="text-sm text-slate-500">{{ __('site.search.results_for', ['query' => $query]) }}</p>

                    @foreach ($groups as $label => $hits)
                        <div>
                            <h2 class="mb-5 flex items-center gap-4 text-lg font-bold tracking-tight">
                                {{ $label }}
                                <span class="h-px flex-1 bg-slate-200"></span>
                                <span class="chip tabular-nums">{{ bn_digits($hits->count()) }}</span>
                            </h2>

                            <ul class="space-y-3">
                                @foreach ($hits as $hit)
                                    <li>
                                        <a href="{{ $hit['url'] }}" class="card-hover group flex gap-4 p-5">
                                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600">
                                                <x-icon :name="$hit['icon']" class="h-5 w-5"/>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block font-medium text-slate-900 group-hover:text-primary-700">{{ $hit['title'] }}</span>
                                                @if ($hit['excerpt'])
                                                    <span class="mt-1 block text-sm leading-relaxed text-slate-500">{{ $hit['excerpt'] }}</span>
                                                @endif
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
