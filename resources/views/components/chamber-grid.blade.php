@props(['chambers', 'nextDates' => []])

<x-card-grid x-data x-reveal.stagger :count="$chambers->count()" {{ $attributes }}>
    @foreach ($chambers as $chamber)
        <x-chamber-card :chamber="$chamber" :next-date="$nextDates[$chamber->id] ?? null"/>
    @endforeach
</x-card-grid>
