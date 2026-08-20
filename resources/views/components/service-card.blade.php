@props(['service'])

<a href="{{ route('services.show', $service) }}" class="card-hover group flex flex-col">
    {{-- A photograph if one has been uploaded; the icon is the fallback, not
         the other way round. The admin has always accepted the image. --}}
    @if ($service->imageUrl())
        <x-media-frame :src="$service->imageUrl()" :alt="$service->tr('name')"
                       :icon="$service->icon ?: 'stethoscope'" ratio="aspect-[16/10]" :seed="$service->slug"/>
    @endif

    <div class="flex flex-1 flex-col p-6">
        @unless ($service->imageUrl())
            <span class="grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                <x-icon :name="$service->icon ?: 'stethoscope'" class="h-6 w-6"/>
            </span>
        @endunless

    <h3 class="mt-4 text-[17px] font-semibold leading-snug text-slate-900 transition group-hover:text-primary-700">
        {{ $service->tr('name') }}
    </h3>

    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-500">
        {{ Str::limit($service->tr('short_description'), 110) }}
    </p>

        <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600">
            {{ __('site.actions.view_details') }}
            <x-icon name="arrow-right" class="lean h-4 w-4 rtl:rotate-180"/>
        </span>
    </div>
</a>
