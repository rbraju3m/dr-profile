{{-- Wraps every resource form: title, method spoofing, file support, footer. --}}
@props(['record', 'routeName', 'label', 'files' => false])

<x-layouts.admin :title="($record ? __('admin.actions.edit') : __('admin.actions.create')).' — '.$label">
    <x-admin.page-header
        :title="($record ? __('admin.actions.edit') : __('admin.actions.create')).' · '.$label"
        :subtitle="__('admin.common.required_note')"
        :back="route($routeName.'.index')"/>

    <form method="POST" action="{{ $record ? route($routeName.'.update', $record) : route($routeName.'.store') }}"
          @if ($files) enctype="multipart/form-data" @endif
          class="space-y-6">
        @csrf
        @if ($record) @method('PUT') @endif

        {{ $slot }}

        <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6">
            <button type="submit" class="btn-primary btn-lg">
                <x-icon name="check" class="h-5 w-5"/>{{ __('admin.actions.save_changes') }}
            </button>
            <a href="{{ route($routeName.'.index') }}" class="btn-ghost">{{ __('admin.actions.cancel') }}</a>
        </div>
    </form>
</x-layouts.admin>
