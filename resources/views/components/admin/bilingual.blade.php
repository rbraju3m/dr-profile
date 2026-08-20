{{--
    Renders the English and Bangla halves of a paired column (name_en / name_bn)
    as two tabs, so the admin never has to hunt for the other language.
--}}
@props([
    'name', 'label', 'record' => null, 'type' => 'input',
    'rows' => 4, 'required' => false, 'hint' => null,
])

@php
    $enName = $name.'_en';
    $bnName = $name.'_bn';
    $hasError = $errors->has($enName) || $errors->has($bnName);
@endphp

<div x-data="{ lang: '{{ $errors->has($bnName) && ! $errors->has($enName) ? 'bn' : 'en' }}' }"
     @class(['rounded-xl border p-4', 'border-rose-300 bg-rose-50/30' => $hasError, 'border-slate-200' => ! $hasError])>

    <div class="mb-3 flex items-center justify-between gap-3">
        <span class="text-sm font-medium text-slate-700">
            {{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif
        </span>

        <div class="flex rounded-lg bg-slate-100 p-0.5">
            @foreach (['en' => __('admin.common.english'), 'bn' => __('admin.common.bangla')] as $code => $tab)
                <button type="button" @click="lang = '{{ $code }}'"
                        :class="lang === '{{ $code }}' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition">{{ $tab }}</button>
            @endforeach
        </div>
    </div>

    @foreach (['en', 'bn'] as $code)
        @php
            $field = $name.'_'.$code;
            $isBangla = $code === 'bn';
        @endphp

        <div x-show="lang === '{{ $code }}'" x-cloak>
            @if ($type === 'textarea')
                <textarea name="{{ $field }}" rows="{{ $rows }}"
                          @required($required && ! $isBangla)
                          @class(['field-input', 'ring-rose-400' => $errors->has($field), 'font-[var(--font-bangla)]' => $isBangla])>{{ old($field, $record?->{$field}) }}</textarea>
            @else
                <input type="text" name="{{ $field }}" value="{{ old($field, $record?->{$field}) }}"
                       @required($required && ! $isBangla)
                       @class(['field-input', 'ring-rose-400' => $errors->has($field), 'font-[var(--font-bangla)]' => $isBangla])>
            @endif

            @error($field) <p class="field-error">{{ $message }}</p> @enderror

            @if ($isBangla && ! $errors->has($field))
                <p class="mt-1 text-xs text-slate-400">{{ __('admin.common.bangla_hint') }}</p>
            @elseif (! $isBangla && $hint)
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            @endif
        </div>
    @endforeach
</div>
