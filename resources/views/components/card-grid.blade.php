{{--
    A row of cards that gives itself only as many tracks as it has cards.

    A fixed three-column track leaves one or two columns empty whenever a section
    holds one, two or four rows, and the gap is wide enough to read as a card that
    failed to load. Short rows narrow and centre instead, so what emptiness is left
    sits either side rather than all to one edge; four goes to a 2x2 rather than
    3+1, which orphans the fourth.

    `cols="2"` is for a row inside a page column that is already narrow — a
    sidebar, a detail page's main column — where there is no third track to lose
    and a lone card simply takes the width it has.
--}}
@props(['count', 'cols' => 3, 'twoUp' => 'sm'])

@php
    $two = $twoUp === 'md' ? 'md:grid-cols-2' : 'sm:grid-cols-2';

    $shape = $cols < 3
        ? ($count === 1 ? '' : $two)
        : match ((int) $count) {
            1 => 'mx-auto max-w-xl',
            2, 4 => 'mx-auto max-w-4xl '.$two,
            default => $two.' lg:grid-cols-3',
        };
@endphp

<div {{ $attributes->class(['grid gap-6', $shape]) }}>
    {{ $slot }}
</div>
