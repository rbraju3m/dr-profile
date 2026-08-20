@props(['chamber', 'nextDate' => null])

@php
    $schedules = $chamber->relationLoaded('activeSchedules')
        ? $chamber->activeSchedules
        : $chamber->schedules->where('is_active', true);
    $byDay = $schedules->groupBy('day_of_week');
@endphp

<div class="card flex flex-col overflow-hidden">
    <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <x-icon name="building" class="h-4 w-4 shrink-0 text-primary-500"/>
                <h3 class="truncate text-base font-semibold text-slate-900">
                    <a href="{{ route('chambers.show', $chamber) }}" class="hover:text-primary-700">{{ $chamber->tr('name') }}</a>
                </h3>
            </div>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $chamber->tr('address') }}</p>
            @if ($chamber->room_no)
                <p class="mt-1 text-xs text-slate-400">{{ __('site.chamber.room') }}: {{ $chamber->room_no }}</p>
            @endif
        </div>

        @if ($chamber->accepts_online_booking)
            <span class="chip shrink-0 !bg-accent-50 !text-accent-700">
                <x-icon name="check" class="h-3 w-3"/>{{ __('site.actions.book_now') }}
            </span>
        @endif
    </div>

    <div class="flex-1 p-6">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('site.chamber.weekly_schedule') }}</p>

        @if ($byDay->isEmpty())
            <p class="text-sm text-slate-400">{{ __('site.chamber.closed') }}</p>
        @else
            <ul class="space-y-2">
                @foreach (App\Support\Week::DAYS as $day)
                    @continue (! $byDay->has($day))
                    <li class="flex items-baseline justify-between gap-3 text-sm">
                        <span class="font-medium text-slate-700">{{ App\Support\Week::name($day) }}</span>
                        <span class="text-end tabular-nums text-slate-500">
                            @foreach ($byDay[$day] as $sitting)
                                <span class="block">{{ $sitting->timeRange() }}</span>
                            @endforeach
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($chamber->consultation_fee !== null)
            <dl class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">{{ __('site.chamber.consultation_fee') }}</dt>
                    <dd class="mt-0.5 font-semibold text-slate-900">
                        {{ $chamber->consultation_fee > 0 ? App\Support\Number::money($chamber->consultation_fee) : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">{{ __('site.chamber.followup_fee') }}</dt>
                    <dd class="mt-0.5 font-semibold text-slate-900">
                        {{ $chamber->followup_fee > 0 ? App\Support\Number::money($chamber->followup_fee) : '—' }}
                    </dd>
                </div>
            </dl>
        @endif
    </div>

    <div class="border-t border-slate-100 p-4">
        @if ($chamber->accepts_online_booking)
            <div class="flex items-center gap-2">
                <a href="{{ route('appointment.create', ['chamber' => $chamber->slug]) }}" class="btn-primary flex-1">
                    <x-icon name="calendar-check" class="h-4 w-4"/>{{ __('site.actions.book_now') }}
                </a>
                <a href="{{ route('chambers.show', $chamber) }}" class="btn-secondary">{{ __('site.actions.view_details') }}</a>
            </div>
            @if ($nextDate)
                <p class="mt-2.5 text-center text-xs text-slate-500">
                    <x-icon name="clock" class="me-1 inline h-3.5 w-3.5 align-[-2px]"/>
                    {{ __('site.booking.select_date') }}:
                    <span class="font-medium text-slate-700">{{ bn_digits($nextDate->format('j')) }} {{ __('site.months.'.$nextDate->month) }}</span>
                </p>
            @endif
        @else
            <div class="rounded-xl bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-800">
                {{ __('site.chamber.online_booking_off') }}
                @if ($chamber->phone)
                    <a href="tel:{{ $chamber->phone }}" class="mt-1 block font-semibold underline">{{ bn_digits($chamber->phone) }}</a>
                @endif
            </div>
        @endif
    </div>
</div>
