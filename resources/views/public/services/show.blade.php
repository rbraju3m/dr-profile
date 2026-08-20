<x-layouts.public :title="$service->tr('name')" :description="$service->tr('short_description')">
    <x-page-hero :title="$service->tr('name')" :subtitle="$service->tr('short_description')"
                 :breadcrumbs="[__('site.nav.services') => route('services.index'), $service->tr('name') => null]"/>

    <section class="section bg-white">
        <div class="container-page grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if ($service->imageUrl())
                    <div class="mb-8 overflow-hidden rounded-2xl">
                        <x-media-frame :src="$service->imageUrl()" :alt="$service->tr('name')" ratio="aspect-[16/9]"/>
                    </div>
                @endif

                <div class="prose-content">{!! $service->tr('description') !!}</div>

                @if ($stories->isNotEmpty())
                    <section class="mt-12">
                        <h2 class="text-xl font-bold">{{ __('site.stories.heading') }}</h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            @foreach ($stories as $story)
                                <x-story-card :story="$story"/>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($testimonials->isNotEmpty())
                    <section class="mt-12">
                        <h2 class="text-xl font-bold">{{ __('site.home.testimonials_heading') }}</h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            @foreach ($testimonials as $testimonial)
                                <x-testimonial-card :testimonial="$testimonial"/>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="lg:col-span-4">
                <div class="space-y-5 lg:sticky lg:top-28">
                    <div class="card p-6">
                        <span class="grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-600">
                            <x-icon :name="$service->icon ?: 'stethoscope'" class="h-6 w-6"/>
                        </span>

                        <h2 class="mt-4 text-base font-semibold">{{ $service->tr('name') }}</h2>

                        <dl class="mt-4 space-y-3 border-t border-slate-100 pt-4 text-sm">
                            @if ($service->fee)
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500">{{ __('site.chamber.consultation_fee') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ App\Support\Number::money($service->fee) }}</dd>
                                </div>
                            @endif
                            @if ($service->tr('duration'))
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500">{{ __('site.duration') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ $service->tr('duration') }}</dd>
                                </div>
                            @endif
                        </dl>

                        <a href="{{ route('appointment.create', ['service' => $service->id]) }}" class="btn-primary mt-5 w-full">
                            <x-icon name="calendar-check" class="h-4 w-4"/>{{ __('site.actions.book_appointment') }}
                        </a>
                    </div>

                    @if ($related->isNotEmpty())
                        <div class="card p-6">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('site.nav.services') }}</h2>
                            <ul class="mt-4 space-y-1">
                                @foreach ($related as $item)
                                    <li>
                                        <a href="{{ route('services.show', $item) }}"
                                           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-primary-700">
                                            <x-icon :name="$item->icon ?: 'stethoscope'" class="h-4 w-4 shrink-0 text-slate-400"/>
                                            <span class="min-w-0 flex-1 truncate">{{ $item->tr('name') }}</span>
                                            <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-slate-300 rtl:rotate-180"/>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
