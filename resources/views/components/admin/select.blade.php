@props(['name', 'label' => null, 'options' => [], 'value' => null, 'required' => false, 'placeholder' => null, 'hint' => null])

@php $id = $attributes->get('id', $name); @endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <select name="{{ $name }}" id="{{ $id }}" @required($required)
            {{ $attributes->merge(['class' => 'field-input'.($errors->has($name) ? ' ring-rose-400' : '')]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @error($name) <p class="field-error">{{ $message }}</p> @enderror
    @if ($hint && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
