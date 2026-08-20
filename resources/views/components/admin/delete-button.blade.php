@props(['action', 'label' => null])

<form method="POST" action="{{ $action }}" class="inline"
      onsubmit="return confirm('{{ __('admin.actions.confirm_delete') }}')">
    @csrf
    @method('DELETE')
    <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
            aria-label="{{ $label ?? __('admin.actions.delete') }}">
        <x-icon name="trash" class="h-4 w-4"/>
    </button>
</form>
