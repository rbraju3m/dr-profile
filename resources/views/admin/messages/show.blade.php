<x-layouts.admin :title="$message->subject ?: $message->name">
    <x-admin.page-header :title="$message->subject ?: __('site.contact.message')"
                         :subtitle="$message->created_at->format('d M Y, g:i A')"
                         :back="route('admin.messages.index')"/>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $message->message }}</p>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card :title="__('site.footer.contact')" flush>
                <dl class="divide-y divide-slate-100">
                    @foreach ([
                        [__('site.contact.name'), $message->name, null],
                        [__('site.contact.phone'), $message->phone, 'tel:'.$message->phone],
                        [__('site.contact.email'), $message->email, $message->email ? 'mailto:'.$message->email : null],
                    ] as [$dtLabel, $value, $href])
                        @if ($value)
                            <div class="px-5 py-3">
                                <dt class="text-xs text-slate-500">{{ $dtLabel }}</dt>
                                <dd class="mt-0.5 text-sm text-slate-800">
                                    @if ($href)
                                        <a href="{{ $href }}" class="text-primary-700 hover:underline">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-2">
                    <a href="tel:{{ $message->phone }}" class="btn-primary w-full">
                        <x-icon name="phone" class="h-4 w-4"/>{{ __('site.actions.call_now') }}
                    </a>
                    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}"
                          onsubmit="return confirm('{{ __('admin.actions.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-ghost w-full !text-rose-600 hover:!bg-rose-50">
                            <x-icon name="trash" class="h-4 w-4"/>{{ __('admin.actions.delete') }}
                        </button>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
