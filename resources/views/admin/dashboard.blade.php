<x-layouts.admin :title="__('admin.dashboard.title')">
    <p class="mb-6 text-sm text-slate-500">
        {{ __('admin.dashboard.greeting', ['name' => auth()->user()->name]) }}
    </p>

    {{-- Headline numbers --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['calendar', __('admin.dashboard.today'), $counts['today'], 'bg-primary-50 text-primary-600', route('admin.appointments.index', ['from' => now()->toDateString(), 'to' => now()->toDateString()])],
            ['clock', __('admin.dashboard.pending'), $counts['pending'], 'bg-amber-50 text-amber-600', route('admin.appointments.index', ['status' => 'pending'])],
            ['calendar-check', __('admin.dashboard.upcoming'), $counts['week'], 'bg-accent-50 text-accent-600', route('admin.appointments.index')],
            ['inbox', __('admin.dashboard.unread_messages'), $counts['unread'], 'bg-rose-50 text-rose-600', route('admin.messages.index', ['unread' => 1])],
        ] as [$icon, $label, $value, $tone, $url])
            <a href="{{ $url }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-primary-200 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl {{ $tone }}">
                        <x-icon :name="$icon" class="h-5 w-5"/>
                    </span>
                    <x-icon name="arrow-up-right" class="h-4 w-4 text-slate-300 transition group-hover:text-primary-500"/>
                </div>
                <p class="mt-4 text-2xl font-bold tabular-nums text-slate-900">{{ number_format($value) }}</p>
                <p class="mt-0.5 text-sm text-slate-500">{{ $label }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Today's list --}}
        <x-admin.card class="xl:col-span-2" :title="__('admin.dashboard.today')" flush>
            @if ($todayAppointments->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-400">{{ __('admin.dashboard.no_appointments_today') }}</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($todayAppointments as $appointment)
                        <li class="flex items-center gap-4 px-5 py-3.5">
                            <span class="w-20 shrink-0 text-sm font-semibold tabular-nums text-primary-700">
                                {{ \Illuminate\Support\Carbon::parse($appointment->slot_time)->format('g:i A') }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="block truncate text-sm font-medium text-slate-900 hover:text-primary-700">
                                    {{ $appointment->patient_name }}
                                </a>
                                <span class="block truncate text-xs text-slate-500">
                                    {{ $appointment->chamber?->name_en }} · {{ $appointment->patient_phone }}
                                </span>
                            </span>
                            <x-admin.status-badge :status="$appointment->status"/>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        {{-- Status split + content counts --}}
        <div class="space-y-6">
            <x-admin.card :title="__('admin.appointments.status')">
                <ul class="space-y-3">
                    @foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status)
                        @php
                            $value = $counts[$status];
                            $percent = $counts['total'] > 0 ? round($value / $counts['total'] * 100) : 0;
                        @endphp
                        <li>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="text-slate-600">{{ __('site.status.'.$status) }}</span>
                                <span class="font-semibold tabular-nums text-slate-900">{{ number_format($value) }}</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div @class([
                                    'h-full rounded-full',
                                    'bg-amber-400' => $status === 'pending',
                                    'bg-primary-500' => $status === 'confirmed',
                                    'bg-accent-500' => $status === 'completed',
                                    'bg-rose-400' => $status === 'cancelled',
                                ]) style="width: {{ max($percent, 2) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>

            <x-admin.card :title="__('admin.dashboard.content_summary')">
                <dl class="grid grid-cols-2 gap-3">
                    @foreach ($content as $label => $value)
                        <div class="rounded-xl bg-slate-50 p-3">
                            <dt class="truncate text-xs text-slate-500">{{ $label }}</dt>
                            <dd class="mt-0.5 text-lg font-bold tabular-nums text-slate-900">{{ number_format($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-admin.card :title="__('admin.dashboard.recent_appointments')" flush>
            <ul class="divide-y divide-slate-100">
                @forelse ($recent as $appointment)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <span class="min-w-0 flex-1">
                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="block truncate text-sm font-medium text-slate-900 hover:text-primary-700">
                                {{ $appointment->patient_name }}
                            </a>
                            <span class="block text-xs text-slate-500">
                                {{ $appointment->appointment_no }} · {{ $appointment->appointment_date->format('d M Y') }}
                            </span>
                        </span>
                        <x-admin.status-badge :status="$appointment->status"/>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-slate-400">{{ __('admin.common.empty') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card :title="__('admin.dashboard.recent_messages')" flush>
            <ul class="divide-y divide-slate-100">
                @forelse ($messages as $message)
                    <li class="px-5 py-3">
                        <a href="{{ route('admin.messages.show', $message) }}" class="group block">
                            <span class="flex items-center gap-2">
                                @unless ($message->is_read)
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-primary-500" aria-label="unread"></span>
                                @endunless
                                <span class="truncate text-sm font-medium text-slate-900 group-hover:text-primary-700">{{ $message->name }}</span>
                                <span class="ms-auto shrink-0 text-xs text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                            </span>
                            <span class="mt-0.5 block truncate text-xs text-slate-500">{{ Str::limit($message->message, 80) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-slate-400">{{ __('admin.common.empty') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-layouts.admin>
