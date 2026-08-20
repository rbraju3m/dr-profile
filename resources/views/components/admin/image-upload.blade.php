@props(['name', 'label', 'current' => null, 'hint' => null, 'accept' => 'image/*'])

<div x-data="{ preview: null, name: null }">
    <span class="field-label">{{ $label }}</span>

    <div class="flex items-start gap-4">
        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
            <template x-if="preview">
                <img :src="preview" alt="" class="h-full w-full object-cover">
            </template>
            <div x-show="!preview">
                @if ($current)
                    <img src="{{ $current }}" alt="" class="h-full w-full object-cover">
                @else
                    <span class="grid h-full w-full place-items-center text-slate-300">
                        <x-icon name="image" class="h-7 w-7"/>
                    </span>
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <input type="file" name="{{ $name }}" accept="{{ $accept }}"
                   @change="const f = $event.target.files[0]; name = f?.name; preview = f ? URL.createObjectURL(f) : null"
                   class="block w-full text-sm text-slate-500 file:me-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-700 hover:file:bg-primary-100">

            @error($name) <p class="field-error">{{ $message }}</p> @enderror
            @if ($hint)
                <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
            @endif

            @if ($current)
                <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" name="remove_{{ $name }}" value="1"
                           class="h-3.5 w-3.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    {{ __('admin.common.remove_image') }}
                </label>
            @endif
        </div>
    </div>
</div>
