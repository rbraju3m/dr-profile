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
                                <x-admin.textarea :name="$key" :label="__('admin.settings.fields.'.$key)" :value="$values->get($key)" rows="3"/>
                            @else
                                <x-admin.input :name="$key" :label="__('admin.settings.fields.'.$key)" :value="$values->get($key)"/>
                            @endif
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <x-admin.form-actions/>
    </form>
</x-layouts.admin>
