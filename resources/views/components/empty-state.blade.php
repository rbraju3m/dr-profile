@props(['icon' => 'inbox', 'title', 'text' => null])

<div class="card flex flex-col items-center px-6 py-16 text-center">
    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
        <x-icon :name="$icon" class="h-7 w-7"/>
    </span>
    <p class="mt-4 font-semibold text-slate-800">{{ $title }}</p>
    @if ($text)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>
    @endif
    {{ $slot }}
</div>
