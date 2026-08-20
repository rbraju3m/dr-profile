@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'required' => false, 'hint' => null, 'rich' => false])

@php $id = $attributes->get('id', $name); @endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" @required($required)
              {{ $attributes->merge(['class' => 'field-input'.($errors->has($name) ? ' ring-rose-400' : '').($rich ? ' font-mono text-xs' : '')]) }}>{{ old($name, $value) }}</textarea>

    @error($name) <p class="field-error">{{ $message }}</p> @enderror
    @if ($hint && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @if ($rich && ! $hint)
        <p class="mt-1 text-xs text-slate-400">HTML allowed: &lt;p&gt; &lt;strong&gt; &lt;em&gt; &lt;ul&gt; &lt;li&gt; &lt;h2&gt; &lt;a&gt;</p>
    @endif
</div>
