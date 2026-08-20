@props([
    'name', 'label' => null, 'type' => 'text', 'value' => null,
    'required' => false, 'hint' => null, 'placeholder' => null,
])

@php $id = $attributes->get('id', $name); @endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
           value="{{ old($name, $value) }}" @required($required) placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'field-input'.($errors->has($name) ? ' ring-rose-400' : '')]) }}>

    @error($name) <p class="field-error">{{ $message }}</p> @enderror
    @if ($hint && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
