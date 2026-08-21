@props(['name', 'label', 'current' => null, 'hint' => null, 'accept' => 'image/*'])

@php
    $maxBytes = App\Support\Uploads::maxBytes();
    $maxLabel = App\Support\Uploads::maxLabel();

    // This component also takes PDFs. A document has no thumbnail, so drawing
    // one into an <img> gave a broken-image icon and the operator no way to
    // tell whether a file was attached at all.
    $isImage = str_starts_with($accept, 'image/');
@endphp

{{--
    Size is checked here as well as on the server. Catching it in the browser
    saves a round trip and, more to the point, saves the operator retyping a
    long form they had just filled in.
--}}
<div x-data="{
        preview: null,
        rejected: null,
        max: {{ $maxBytes }},

        pick(event) {
            const file = event.target.files[0]
            this.preview = null
            this.rejected = null

            if (!file) return

            if (file.size > this.max) {
                this.rejected = this.human(file.size)
                event.target.value = ''
                return
            }

            this.preview = @js($isImage) ? URL.createObjectURL(file) : file.name
        },

        human(bytes) {
            return bytes >= 1048576
                ? (bytes / 1048576).toFixed(1) + ' MB'
                : Math.ceil(bytes / 1024) + ' KB'
        },
     }">
    <span class="field-label">{{ $label }}</span>

    <div class="flex items-start gap-4">
        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
            <template x-if="preview">
                @if ($isImage)
                    <img :src="preview" alt="" class="h-full w-full object-cover">
                @else
                    <span class="grid h-full w-full place-content-center justify-items-center gap-1 p-2 text-center text-slate-500">
                        <x-icon name="file-text" class="h-7 w-7"/>
                        <span class="w-full truncate text-[10px]" x-text="preview"></span>
                    </span>
                @endif
            </template>
            <div x-show="!preview" class="h-full w-full">
                @if ($current && $isImage)
                    <img src="{{ $current }}" alt="" class="h-full w-full object-cover">
                @elseif ($current)
                    <a href="{{ $current }}" target="_blank" rel="noopener noreferrer"
                       title="{{ __('admin.common.view_file') }}"
                       class="grid h-full w-full place-content-center justify-items-center gap-1 text-slate-500 hover:text-primary-600">
                        <x-icon name="file-text" class="h-7 w-7"/>
                        <span class="text-[10px] font-medium">{{ __('admin.common.view_file') }}</span>
                    </a>
                @else
                    <span class="grid h-full w-full place-items-center text-slate-300">
                        <x-icon name="{{ $isImage ? 'image' : 'file-text' }}" class="h-7 w-7"/>
                    </span>
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <input type="file" name="{{ $name }}" accept="{{ $accept }}" @change="pick($event)"
                   class="block w-full text-sm text-slate-500 file:me-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-700 hover:file:bg-primary-100">

            {{-- Refused in the browser, before anything is sent --}}
            <p x-show="rejected" x-cloak class="field-error"
               x-text="@js(__('validation_custom.upload.too_large', ['size' => '__SIZE__', 'max' => $maxLabel])).replace('__SIZE__', rejected)"></p>

            {{-- Refused by the server --}}
            @error($name) <p class="field-error">{{ $message }}</p> @enderror

            <p class="mt-1.5 text-xs text-slate-400">
                {{ $hint ?? __('admin.common.upload_hint', ['max' => $maxLabel]) }}
            </p>

            @if ($current)
                <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" name="remove_{{ $name }}" value="1"
                           class="h-3.5 w-3.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    {{ $isImage ? __('admin.common.remove_image') : __('admin.common.remove_file') }}
                </label>
            @endif
        </div>
    </div>
</div>
