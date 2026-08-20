@props(['name', 'label', 'value' => false, 'hint' => null])

<label class="switch-row">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $value))
           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
    <span class="min-w-0">
        <span class="block text-sm font-medium text-slate-800">{{ $label }}</span>
        @if ($hint)
            <span class="mt-0.5 block text-xs text-slate-500">{{ $hint }}</span>
        @endif
    </span>
</label>
