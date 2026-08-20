<x-layouts.admin :title="__('admin.nav.messages')">
    <x-admin.page-header :title="__('admin.nav.messages')">
        <x-slot:actions>
            <a href="{{ route('admin.messages.index', $onlyUnread ? [] : ['unread' => 1]) }}"
               @class(['btn-secondary', 'bg-primary-600 text-white ring-primary-600' => $onlyUnread])>
                {{ __('admin.dashboard.unread_messages') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($messages->isEmpty())
        <x-empty-state icon="inbox" :title="__('admin.common.empty')"/>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <ul class="divide-y divide-slate-100">
                @foreach ($messages as $message)
                    <li>
                        <a href="{{ route('admin.messages.show', $message) }}"
                           class="flex items-start gap-4 px-5 py-4 transition hover:bg-slate-50">
                            <span @class([
                                'mt-1.5 h-2 w-2 shrink-0 rounded-full',
                                'bg-primary-500' => ! $message->is_read,
                                'bg-slate-200' => $message->is_read,
                            ])></span>

                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-baseline gap-x-2">
                                    <span @class(['truncate text-sm text-slate-900', 'font-semibold' => ! $message->is_read])>
                                        {{ $message->name }}
                                    </span>
                                    <span class="text-xs tabular-nums text-slate-400">{{ $message->phone }}</span>
                                </span>
                                @if ($message->subject)
                                    <span class="mt-0.5 block truncate text-sm text-slate-700">{{ $message->subject }}</span>
                                @endif
                                <span class="mt-0.5 block truncate text-xs text-slate-500">{{ Str::limit($message->message, 110) }}</span>
                            </span>

                            <span class="shrink-0 text-xs text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($messages->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $messages->links() }}</div>
            @endif
        </div>
    @endif
</x-layouts.admin>
