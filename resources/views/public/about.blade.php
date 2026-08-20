<x-layouts.public :title="__('site.about.heading')">
    <x-page-hero :title="$doctor->fullName()" :subtitle="$doctor->tr('designation')"
                 :breadcrumbs="[__('site.nav.about') => null]"/>

    {{-- Identity card + biography --}}
    <section class="section bg-white">
        <div class="container-page grid gap-12 lg:grid-cols-12">
            <aside class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <div class="card overflow-hidden">
                        <x-media-frame :src="$doctor->photoUrl()" :alt="$doctor->fullName()" icon="stethoscope"
                                       ratio="aspect-[4/5]" fit="contain"
                                       :label="$doctor->tr('name')" :seed="$doctor->tr('name')"/>

                        <div class="space-y-4 p-6">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $doctor->fullName() }}</p>
                                <p class="mt-0.5 text-sm text-primary-700">{{ $doctor->tr('designation') }}</p>
                            </div>

                            <dl class="space-y-3 border-t border-slate-100 pt-4 text-sm">
                                @foreach ([
                                    ['graduation-cap', __('site.about.education'), $doctor->tr('degrees')],
                                    ['badge-check', __('site.about.registration'), $doctor->bmdc_reg_no],
                                    ['globe', __('site.about.languages'), $doctor->tr('languages')],
                                    ['briefcase', __('site.about.experience_years'), $doctor->experience_years ? bn_digits($doctor->experience_years).'+' : null],
                                ] as [$icon, $label, $value])
                                    @if ($value)
                                        <div class="flex gap-3">
                                            <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                            <div class="min-w-0">
                                                <dt class="text-xs text-slate-500">{{ $label }}</dt>
                                                <dd class="font-medium leading-relaxed text-slate-800">{{ $value }}</dd>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>

                            <div class="space-y-2 border-t border-slate-100 pt-4">
                                @feature('appointment')
                                    <a href="{{ route('appointment.create') }}" class="btn-primary w-full">
                                        <x-icon name="calendar-check" class="h-4 w-4"/>{{ __('site.actions.book_appointment') }}
                                    </a>
                                @endfeature
                                @if ($doctor->cv_file)
                                    <a href="{{ $doctor->mediaUrl('cv_file') }}" download class="btn-secondary w-full">
                                        <x-icon name="download" class="h-4 w-4"/>{{ __('site.actions.download_cv') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-8">
                @if ($doctor->tr('bio'))
                    <div x-data x-reveal>
                        <h2 class="display text-2xl">{{ __('site.about.biography') }}</h2>
                        <span aria-hidden="true" class="rule-draw mt-4 block h-[3px] w-14 rounded-full bg-primary-500"></span>
                    </div>
                    <div class="prose-content mt-6 text-base">{!! $doctor->tr('bio') !!}</div>
                @endif

                @if ($doctor->tr('philosophy'))
                    <figure x-data x-reveal class="relative mt-12 overflow-hidden rounded-2xl border-s-4 border-primary-500 bg-primary-50 p-7">
                        <x-icon name="quote" class="absolute -end-2 -top-2 h-16 w-16 text-primary-200/60"/>
                        <h2 class="relative text-lg font-bold text-primary-900">{{ __('site.about.philosophy') }}</h2>
                        {{-- Plain text-primary-900, not /80: an opacity variant is its own
                             class and the dark override never reaches it. --}}
                        <div class="prose-content relative mt-3 text-[17px] leading-relaxed text-primary-900">{!! $doctor->tr('philosophy') !!}</div>
                    </figure>
                @endif

                @if ($stats->isNotEmpty())
                    <div x-data x-reveal.stagger class="mt-12 grid grid-cols-2 gap-4 lg:grid-cols-4">
                        @foreach ($stats as $stat)
                            <div class="card card-hover p-5 text-center">
                                <x-icon :name="$stat->icon ?: 'activity'" class="mx-auto h-6 w-6 text-primary-500"/>
                                <p class="mt-2 text-xl font-bold tabular-nums text-slate-900" x-data x-counter="{{ $stat->value }}">{{ $stat->displayValue() }}</p>
                                <p class="mt-0.5 text-xs leading-tight text-slate-500">{{ $stat->tr('label') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Credential timeline --}}
                @foreach ([
                    'education' => ['graduation-cap', __('site.about.education')],
                    'experience' => ['briefcase', __('site.about.experience')],
                    'training' => ['stethoscope', __('site.about.training')],
                    'award' => ['award', __('site.about.awards')],
                    'membership' => ['users', __('site.about.memberships')],
                    'certification' => ['badge-check', __('site.about.certifications')],
                ] as $type => [$icon, $label])
                    @php $items = $credentials[$type] ?? collect(); @endphp
                    @continue ($items->isEmpty())

                    <section class="mt-14">
                        <h2 x-data x-reveal class="flex items-center gap-3 text-xl font-bold tracking-tight">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary-50 text-primary-600">
                                <x-icon :name="$icon" class="h-5 w-5"/>
                            </span>
                            {{ $label }}
                        </h2>

                        <ol x-data x-reveal.stagger class="mt-5 space-y-0 border-s-2 border-slate-100 ps-6">
                            @foreach ($items as $item)
                                <li class="relative pb-7 last:pb-0">
                                    <span class="absolute -start-[1.9rem] top-1.5 grid h-3.5 w-3.5 place-items-center rounded-full bg-white ring-2 {{ $item->is_current ? 'ring-accent-500' : 'ring-slate-300' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $item->is_current ? 'bg-accent-500' : 'bg-slate-300' }}"></span>
                                    </span>

                                    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                        <h3 class="text-base font-semibold text-slate-900">{{ $item->tr('title') }}</h3>
                                        @if ($item->period())
                                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium tabular-nums text-slate-600">
                                                {{ bn_digits($item->period()) }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($item->tr('organization'))
                                        <p class="mt-0.5 text-sm text-primary-700">{{ $item->tr('organization') }}</p>
                                    @endif
                                    @if ($item->tr('description'))
                                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $item->tr('description') }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endforeach

                @if ($publications->isNotEmpty() && feature('publications'))
                    <section class="mt-14">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="flex items-center gap-3 text-xl font-bold tracking-tight">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary-50 text-primary-600">
                                    <x-icon name="book-open" class="h-5 w-5"/>
                                </span>
                                {{ __('site.publications.heading') }}
                            </h2>
                            <a href="{{ route('publications.index') }}" class="group btn-secondary">
                                {{ __('site.actions.view_all') }}<x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
                            </a>
                        </div>

                        <ul x-data x-reveal.stagger class="mt-6 space-y-3">
                            @foreach ($publications as $publication)
                                <li class="card card-hover p-5">
                                    <x-publication-entry :publication="$publication"/>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </section>

    @if ($services->isNotEmpty() && feature('services'))
        <section class="section bg-slate-50">
            <div class="container-page">
                <x-section-heading :title="__('site.home.expertise_heading')" :subtitle="__('site.home.expertise_subheading')"/>
                <div x-data x-reveal.stagger class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <x-service-card :service="$service"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
