{{-- Pass `status` for an appointment state, or `active` for a boolean flag. --}}
@props(['status' => null, 'active' => null, 'label' => null])

@php
    if ($status !== null) {
        $classes = match ($status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'confirmed' => 'bg-primary-100 text-primary-800',
            'completed' => 'bg-accent-100 text-accent-800',
            'cancelled' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-600',
        };
        $text = $label ?? __('site.status.'.$status);
    } else {
        $classes = $active ? 'bg-accent-100 text-accent-800' : 'bg-slate-100 text-slate-500';
        $text = $label ?? ($active ? __('admin.common.active') : __('admin.common.inactive'));
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium $classes"]) }}>{{ $text }}</span>
