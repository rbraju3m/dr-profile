<?php

namespace Tests\Unit;

use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Support\Sittings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One doctor, so two chambers open at the same hour is not a preference — it is
 * impossible, and the booking form will sell the hour twice. The admin form and
 * doctor:import both refuse it through this class, so the rule is tested here
 * once rather than from each end.
 */
class SittingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_chambers_open_at_the_same_hour_is_a_conflict(): void
    {
        $this->sitting('one', 1, '17:00', '20:00');
        $this->sitting('two', 1, '19:00', '22:00');

        $conflicts = Sittings::conflicts();

        $this->assertCount(1, $conflicts);
        $this->assertSame(1, $conflicts[0]['day']);
        $this->assertSame('19:00', $conflicts[0]['from']);
        $this->assertSame('20:00', $conflicts[0]['to']);
    }

    /**
     * A sitting that begins exactly when another ends is back-to-back, not an
     * overlap. MySQL returns "20:00:00" and the admin form sends "20:00";
     * compared as plain strings the shorter one sorts first, which would make
     * every clean handover look like a clash.
     */
    public function test_sittings_that_meet_at_the_boundary_are_not_a_conflict(): void
    {
        $this->sitting('one', 1, '17:00', '20:00');
        $this->sitting('two', 1, '20:00', '22:00');

        $this->assertCount(0, Sittings::conflicts());
    }

    public function test_different_days_never_conflict(): void
    {
        $this->sitting('one', 1, '17:00', '20:00');
        $this->sitting('two', 2, '17:00', '20:00');

        $this->assertCount(0, Sittings::conflicts());
    }

    /** A chamber switched off is seeing nobody, so it cannot double-book him. */
    public function test_an_inactive_chamber_does_not_conflict(): void
    {
        $this->sitting('one', 1, '17:00', '20:00');
        $this->sitting('two', 1, '17:00', '20:00', active: false);

        $this->assertCount(0, Sittings::conflicts());
    }

    /**
     * Two sittings overlapping inside one chamber are a different fault, caught
     * by a different guard. Counting them here would report the same row twice.
     */
    public function test_an_overlap_within_one_chamber_is_not_counted_here(): void
    {
        $chamber = $this->sitting('one', 1, '17:00', '20:00');

        ChamberSchedule::create([
            'chamber_id' => $chamber->id,
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
            'slot_minutes' => 20,
            'is_active' => true,
        ]);

        $this->assertCount(0, Sittings::conflicts());
    }

    /** The page warning names the other chamber, never the one being read. */
    public function test_it_reports_the_other_chamber_for_a_given_one(): void
    {
        $one = $this->sitting('one', 1, '17:00', '20:00');
        $this->sitting('two', 1, '19:00', '22:00');

        $for = Sittings::conflictsFor($one);

        $this->assertCount(1, $for);
        $this->assertSame('two', $for[0]['other']->slug);
    }

    /** A proposed sitting is refused by the clash the admin form asks about. */
    public function test_it_finds_the_clash_a_proposed_sitting_would_cause(): void
    {
        $this->sitting('one', 1, '17:00', '20:00');
        $two = Chamber::create(['slug' => 'two-b', 'name_en' => 'Two B', 'is_active' => true]);

        $this->assertNotNull(Sittings::clash($two, 1, '19:00', '21:00'));
        $this->assertNull(Sittings::clash($two, 1, '20:00', '21:00'));
        $this->assertNull(Sittings::clash($two, 2, '19:00', '21:00'));
    }

    private function sitting(string $slug, int $day, string $start, string $end, bool $active = true): Chamber
    {
        $chamber = Chamber::create([
            'slug' => $slug,
            'name_en' => ucfirst($slug).' Chamber',
            'is_active' => $active,
        ]);

        ChamberSchedule::create([
            'chamber_id' => $chamber->id,
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'slot_minutes' => 20,
            'is_active' => true,
        ]);

        return $chamber;
    }
}
