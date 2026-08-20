<x-layouts.public :title="$chamber->tr('name')" :description="$chamber->tr('address')">
    <x-page-hero :title="$chamber->tr('name')" :subtitle="$chamber->tr('address')"
                 :breadcrumbs="[__('site.nav.chambers') => route('chambers.index'), $chamber->tr('name') => null]"/>

    <section class="section bg-white">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                @if ($chamber->imageUrl())
                    <div class="mb-8 overflow-hidden rounded-2xl">
                        <x-media-frame :src="$chamber->imageUrl()" :alt="$chamber->tr('name')" icon="building" fit="natural"
                                       ratio="aspect-[16/9]" :seed="$chamber->slug"/>
                    </div>
                @endif

                <h2 class="text-xl font-bold">{{ __('site.chamber.weekly_schedule') }}</h2>

                @php $byDay = $chamber->activeSchedules->groupBy('day_of_week'); @endphp

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <table class="w-full text-sm">
                        <caption class="sr-only">{{ __('site.chamber.weekly_schedule') }}</caption>
                        <tbody class="divide-y divide-slate-100">
                            @foreach (App\Support\Week::DAYS as $day)
                                <tr class="{{ $byDay->has($day) ? 'bg-white' : 'bg-slate-50/60' }}">
                                    <th scope="row" class="w-2/5 px-5 py-3.5 text-start font-medium text-slate-700">
                                        {{ App\Support\Week::name($day) }}
                                    </th>
                                    <td class="px-5 py-3.5 text-end tabular-nums">
                                        @if ($byDay->has($day))
                                            @foreach ($byDay[$day] as $sitting)
                                                <span class="block text-slate-700">{{ $sitting->timeRange() }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-slate-400">{{ __('site.chamber.closed') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($chamber->tr('note'))
                    <x-alert type="info" class="mt-5">{{ $chamber->tr('note') }}</x-alert>
                @endif

                {{-- Next two weeks at a glance --}}
                @feature('appointment')
                <h2 class="mt-10 text-xl font-bold">{{ __('site.booking.select_date') }}</h2>
                <div class="mt-4 grid grid-cols-3 gap-2.5 sm:grid-cols-5 lg:grid-cols-7">
                    @foreach ($calendar as $day)
                        @php $date = Illuminate\Support\Carbon::parse($day['date']); @endphp
                        <div @class([
                            'rounded-xl border p-2.5 text-center',
                            'border-accent-200 bg-accent-50' => $day['open'],
                            'border-slate-200 bg-slate-50' => ! $day['open'],
                        ])>
                            <p class="text-[11px] text-slate-500">{{ App\Support\Week::shortName($date->dayOfWeek) }}</p>
                            <p class="text-base font-semibold tabular-nums text-slate-900">{{ bn_digits($date->format('j')) }}</p>
                            <p @class([
                                'mt-0.5 text-[10px] font-medium',
                                'text-accent-700' => $day['open'],
                                'text-slate-400' => ! $day['open'],
                            ])>
                                {{ $day['open'] ? __('site.booking.slots_left', ['count' => bn_digits($day['count'])]) : __('site.chamber.closed') }}
                            </p>
                        </div>
                    @endforeach
                </div>
                @endfeature

                @if ($chamber->map_embed)
                    <div class="mt-10 overflow-hidden rounded-2xl border border-slate-200">
                        {!! $chamber->map_embed !!}
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-5">
                <div class="space-y-5 lg:sticky lg:top-28">
                    <div class="card p-6">
                        <h2 class="text-base font-semibold">{{ __('site.chamber.address') }}</h2>

                        <ul class="mt-4 space-y-3.5 text-sm">
                            <li class="flex gap-3">
                                <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                <span class="leading-relaxed text-slate-600">
                                    {{ $chamber->tr('address') }}
                                    @if ($chamber->room_no)
                                        <span class="mt-0.5 block text-xs text-slate-400">{{ __('site.chamber.room') }}: {{ $chamber->room_no }}</span>
                                    @endif
                                </span>
                            </li>
                            @if ($chamber->phone)
                                <li class="flex gap-3">
                                    <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                    <a href="tel:{{ $chamber->phone }}" class="tabular-nums text-slate-600 hover:text-primary-700">{{ bn_digits($chamber->phone) }}</a>
                                </li>
                            @endif
                            @if ($chamber->appointment_phone)
                                <li class="flex gap-3">
                                    <x-icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                    <span class="text-slate-600">
                                        {{ __('site.chamber.appointment_phone') }}:
                                        <a href="tel:{{ $chamber->appointment_phone }}" class="font-medium tabular-nums text-primary-700">{{ bn_digits($chamber->appointment_phone) }}</a>
                                    </span>
                                </li>
                            @endif
                        </ul>

                        @if ($chamber->consultation_fee !== null)
                            <dl class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4 text-sm">
                                <div>
                                    <dt class="text-xs text-slate-500">{{ __('site.chamber.consultation_fee') }}</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-900">{{ $chamber->consultation_fee > 0 ? App\Support\Number::money($chamber->consultation_fee) : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">{{ __('site.chamber.followup_fee') }}</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-900">{{ $chamber->followup_fee > 0 ? App\Support\Number::money($chamber->followup_fee) : '—' }}</dd>
                                </div>
                            </dl>
                        @endif

                        <div class="mt-5 space-y-2">
                            @if (! feature('appointment'))
                                {{-- Site-wide switch: not this chamber's doing, so no notice. --}}
                            @elseif ($chamber->accepts_online_booking)
                                <a href="{{ route('appointment.create', ['chamber' => $chamber->slug]) }}" class="btn-primary w-full">
                                    <x-icon name="calendar-check" class="h-4 w-4"/>{{ __('site.actions.book_appointment') }}
                                </a>
                                @if ($nextDate)
                                    <p class="text-center text-xs text-slate-500">
                                        {{ bn_digits($nextDate->format('j')) }} {{ __('site.months.'.$nextDate->month) }} — {{ App\Support\Week::name($nextDate->dayOfWeek) }}
                                    </p>
                                @endif
                            @else
                                <x-alert type="warning">{{ __('site.chamber.online_booking_off') }}</x-alert>
                            @endif

                            @if ($chamber->map_url)
                                <a href="{{ $chamber->map_url }}" target="_blank" rel="noopener noreferrer" class="btn-secondary w-full">
                                    <x-icon name="map-pin" class="h-4 w-4"/>{{ __('site.actions.get_directions') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($others->isNotEmpty())
                        <div class="card p-6">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('site.footer.chambers') }}</h2>
                            <ul class="mt-4 space-y-1">
                                @foreach ($others as $other)
                                    <li>
                                        <a href="{{ route('chambers.show', $other) }}"
                                           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-primary-700">
                                            <x-icon name="building" class="h-4 w-4 shrink-0 text-slate-400"/>
                                            <span class="min-w-0 flex-1 truncate">{{ $other->tr('name') }}</span>
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
