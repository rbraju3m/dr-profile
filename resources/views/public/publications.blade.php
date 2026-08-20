<x-layouts.public :title="__('site.publications.heading')">
    <x-page-hero :title="__('site.publications.heading')" :subtitle="__('site.publications.subheading')"
                 :breadcrumbs="[__('site.nav.publications') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page">
            @if ($publications->isEmpty())
                <x-empty-state icon="book-open" :title="__('site.publications.empty')"/>
            @else
                <div class="mx-auto max-w-4xl space-y-12">
                    @foreach ($publications as $year => $items)
                        <div x-data x-reveal>
                            <h2 class="mb-5 flex items-center gap-4">
                                <span class="text-2xl font-bold tabular-nums tracking-tight text-primary-700">{{ $year ? bn_digits($year) : '—' }}</span>
                                <span class="h-px flex-1 bg-slate-200"></span>
                                <span class="chip">{{ bn_digits($items->count()) }}</span>
                            </h2>

                            <ul class="space-y-3">
                                @foreach ($items as $publication)
                                    <li class="card card-hover p-5">
                                        <x-publication-entry :publication="$publication"/>
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
