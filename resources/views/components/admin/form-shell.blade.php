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

        <x-admin.form-actions :cancel="route($routeName.'.index')"/>
    </form>
</x-layouts.admin>
