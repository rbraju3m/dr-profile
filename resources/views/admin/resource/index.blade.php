{{-- Generic listing table, rendered from the controller's columns() definition. --}}
<x-layouts.admin :title="$label">
    <x-admin.page-header :title="$label">
        <x-slot:actions>
            @if ($searchable)
                <form method="GET" class="flex gap-2">
                    <input name="q" value="{{ $search }}" placeholder="{{ __('admin.actions.search') }}"
                           class="field-input !w-48 !py-2">
                    <button type="submit" class="btn-secondary !px-3" aria-label="{{ __('admin.actions.search') }}">
                        <x-icon name="search" class="h-4 w-4"/>
                    </button>
                    @if ($search)
                        <a href="{{ route($routeName.'.index') }}" class="btn-ghost !px-3" aria-label="{{ __('admin.actions.reset') }}">
                            <x-icon name="x" class="h-4 w-4"/>
                        </a>
                    @endif
                </form>
            @endif

            <a href="{{ route($routeName.'.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4"/>{{ __('admin.actions.create') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($records->isEmpty())
        <x-empty-state icon="inbox" :title="__('admin.common.empty')" :text="__('admin.common.empty_hint')"/>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            @foreach ($columns as $column)
                                <th scope="col" class="px-5 py-3 text-start font-semibold {{ $column['class'] ?? '' }}">
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                            <th scope="col" class="px-5 py-3 text-end font-semibold">{{ __('admin.common.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($records as $record)
                            <tr class="transition hover:bg-slate-50/70">
                                @foreach ($columns as $column)
                                    @php
                                        $value = isset($column['value'])
                                            ? $column['value']($record)
                                            : data_get($record, $column['key'] ?? '');
                                        $type = $column['type'] ?? 'text';
                                    @endphp

                                    <td class="px-5 py-3.5 {{ $column['class'] ?? '' }}">
                                        @switch($type)
                                            @case('bool')
                                                <x-admin.status-badge :active="(bool) $value"/>
                                                @break

                                            @case('status')
                                                <x-admin.status-badge :status="$value"/>
                                                @break

                                            @case('image')
                                                <span class="block h-10 w-14 overflow-hidden rounded-lg bg-slate-100">
                                                    @if ($value)
                                                        <img src="{{ $value }}" alt="" class="h-full w-full object-cover">
                                                    @else
                                                        <span class="grid h-full w-full place-items-center text-slate-300">
                                                            <x-icon name="image" class="h-4 w-4"/>
                                                        </span>
                                                    @endif
                                                </span>
                                                @break

                                            @case('number')
                                                <span class="tabular-nums text-slate-600">{{ $value }}</span>
                                                @break

                                            @case('html')
                                                {!! $value !!}
                                                @break

                                            @case('strong')
                                                <span class="font-medium text-slate-900">{{ $value }}</span>
                                                @break

                                            @case('muted')
                                                <span class="text-slate-500">{{ $value }}</span>
                                                @break

                                            @default
                                                <span class="text-slate-700">{{ $value }}</span>
                                        @endswitch
                                    </td>
                                @endforeach

                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route($routeName.'.edit', $record) }}"
                                           class="grid h-9 w-9 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-50 hover:text-primary-600"
                                           aria-label="{{ __('admin.actions.edit') }}">
                                            <x-icon name="pencil" class="h-4 w-4"/>
                                        </a>
                                        <x-admin.delete-button :action="route($routeName.'.destroy', $record)"/>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">
                    {{ $records->links() }}
                </div>
            @endif
        </div>

        <p class="mt-4 text-center text-xs text-slate-400">
            {{ __('admin.common.showing', [
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
                'total' => $records->total(),
            ]) }}
        </p>
    @endif
</x-layouts.admin>
