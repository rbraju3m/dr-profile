{{--
    Sticks to the foot of the viewport instead of the foot of the document.

    These forms are long — the doctor profile is several screens — and having to
    scroll to the bottom to save, then scroll back to carry on editing, is a
    tax paid on every single edit.
--}}
@props(['cancel' => null, 'label' => null])

<div class="sticky bottom-0 z-30 -mx-4 mt-6 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn-primary btn-lg">
            <x-icon name="check" class="h-5 w-5"/>{{ $label ?? __('admin.actions.save_changes') }}
        </button>

        @if ($cancel)
            <a href="{{ $cancel }}" class="btn-ghost">{{ __('admin.actions.cancel') }}</a>
        @endif

        {{ $slot }}
    </div>
</div>
