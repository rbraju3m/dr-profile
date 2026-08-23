<?php

namespace App\Support;

use App\Models\Chamber;
use App\Models\ChamberSchedule;
use Illuminate\Support\Collection;

/**
 * This site belongs to one doctor. Two chambers open at the same hour is not a
 * scheduling preference he might have — it is impossible. He can only be in one
 * of them, and the booking form, which reads each chamber's sittings on its own,
 * will cheerfully sell the same hour twice.
 *
 * The admin form has always refused a sitting that overlaps another *at the same
 * chamber*. That guard was looking at one side only: it never asked what the
 * other chambers were doing, so three chambers ended up open on a Sunday evening
 * and nothing said a word.
 */
final class Sittings
{
    /**
     * The sitting a proposed one would collide with, or null.
     *
     * Only sittings that can actually produce slots count — an inactive chamber
     * is seeing nobody — except at the chamber being edited, whose own rows
     * always count, or a disabled chamber could be given a self-overlapping day.
     */
    public static function clash(Chamber $for, int $day, string $start, string $end, ?int $ignore = null): ?ChamberSchedule
    {
        return ChamberSchedule::query()
            ->with('chamber')
            ->where('is_active', true)
            ->where('day_of_week', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore))
            ->where(fn ($q) => $q
                ->where('chamber_id', $for->getKey())
                ->orWhereHas('chamber', fn ($c) => $c->where('is_active', true))
            )
            ->orderBy('start_time')
            ->first();
    }

    /**
     * Every pair of live sittings that overlap, across every chamber.
     *
     * @return Collection<int, array{day: int, from: string, to: string, a: ChamberSchedule, b: ChamberSchedule}>
     */
    public static function conflicts(): Collection
    {
        $live = ChamberSchedule::query()
            ->with('chamber')
            ->where('is_active', true)
            ->get()
            ->filter(fn (ChamberSchedule $s) => $s->chamber?->is_active)
            ->values();

        $pairs = collect();

        foreach ($live as $i => $a) {
            foreach ($live->slice($i + 1) as $b) {
                if ($a->day_of_week !== $b->day_of_week || $a->chamber_id === $b->chamber_id) {
                    continue;
                }

                [$aStart, $aEnd] = [self::clock($a->start_time), self::clock($a->end_time)];
                [$bStart, $bEnd] = [self::clock($b->start_time), self::clock($b->end_time)];

                if ($aStart < $bEnd && $bStart < $aEnd) {
                    $pairs->push([
                        'day' => $a->day_of_week,
                        'from' => max($aStart, $bStart),
                        'to' => min($aEnd, $bEnd),
                        'a' => $a,
                        'b' => $b,
                    ]);
                }
            }
        }

        return $pairs->sortBy(['day', 'from'])->values();
    }

    /**
     * The clashes one chamber is part of, for the warning on its own page —
     * each paired with the *other* chamber, which is what the reader needs named.
     *
     * @return Collection<int, array{day: int, from: string, to: string, other: Chamber}>
     */
    public static function conflictsFor(Chamber $chamber): Collection
    {
        return self::conflicts()
            ->filter(fn (array $pair) => $pair['a']->chamber_id === $chamber->getKey()
                || $pair['b']->chamber_id === $chamber->getKey())
            ->map(fn (array $pair) => [
                'day' => $pair['day'],
                'from' => $pair['from'],
                'to' => $pair['to'],
                'other' => $pair['a']->chamber_id === $chamber->getKey()
                    ? $pair['b']->chamber
                    : $pair['a']->chamber,
            ])
            ->values();
    }

    /**
     * MySQL hands back "19:00:00" and the admin form sends "19:00". Comparing
     * those two as strings makes 19:00 *less than* 19:00:00, so a sitting that
     * starts exactly when another ends would read as an overlap.
     */
    private static function clock(string $time): string
    {
        return substr($time, 0, 5);
    }
}
