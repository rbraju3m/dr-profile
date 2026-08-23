@props(['testimonial'])

<figure class="card flex h-full flex-col p-6">
    <x-icon name="quote" class="h-7 w-7 text-primary-200"/>

    <blockquote class="mt-4 flex-1 text-[17px] leading-relaxed text-slate-700">
        {{ $testimonial->tr('content') }}
    </blockquote>

    <div class="mt-5 flex items-center gap-1 text-amber-400" aria-label="{{ $testimonial->rating }}/5">
        @for ($i = 1; $i <= 5; $i++)
            <x-icon name="star" class="h-4 w-4 {{ $i <= $testimonial->rating ? 'fill-current' : 'text-slate-200' }}"/>
        @endfor
    </div>

    <figcaption class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4">
        <x-avatar :src="$testimonial->photoUrl()" :name="$testimonial->patient_name" class="h-10 w-10"/>
        <span class="min-w-0">
            <span class="block truncate text-sm font-semibold text-slate-900">{{ $testimonial->patient_name }}</span>
            @if ($testimonial->tr('patient_title'))
                <span class="block truncate text-xs text-slate-500">{{ $testimonial->tr('patient_title') }}</span>
            @endif
            {{-- The visit date was collected on every testimonial and printed on none;
                 it is what tells a reader the quote is recent. --}}
            @if ($testimonial->visited_on)
                <span class="block truncate text-xs text-slate-400">
                    {{ __('site.testimonials.visited', [
                        'date' => \App\Support\Week::monthYear($testimonial->visited_on),
                    ]) }}
                </span>
            @endif
        </span>
    </figcaption>
</figure>
