<x-layouts.admin :title="__('admin.settings.title')">
    <x-admin.page-header :title="__('admin.settings.title')" :subtitle="__('admin.settings.intro')"/>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($groups as $group => $fields)
                <x-admin.card :title="__('admin.settings.groups.'.$group)">
                    <div class="space-y-4">
                        @foreach ($fields as $key => $type)
                            @if ($type === 'textarea')
                                <x-admin.textarea :name="$key" :label="Str::headline($key)" :value="$values->get($key)" rows="3"/>
                            @else
                                <x-admin.input :name="$key" :label="Str::headline($key)" :value="$values->get($key)"/>
                            @endif
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
            <button type="submit" class="btn-primary btn-lg">
                <x-icon name="check" class="h-5 w-5"/>{{ __('admin.actions.save_changes') }}
            </button>
        </div>
    </form>
</x-layouts.admin>
