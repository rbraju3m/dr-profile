<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\ScheduleException;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SlotServiceTest extends TestCase
{
    use RefreshDatabase;

    private SlotService $slots;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slots = app(SlotService::class);
    }

    /** A chamber that sits 10:00–11:00 in 20-minute slots offers exactly three. */
    public function test_it_expands_a_sitting_into_slots(): void
    {
        $date = $this->nextWeekday(1); // a Monday
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        $availability = $this->slots->availability($chamber, $date);

        $this->assertTrue($availability->isOpen);
        $this->assertSame(3, $availability->openCount());
        $this->assertSame(
            ['10:00:00', '10:20:00', '10:40:00'],
            array_column($availability->slots, 'time')
        );
    }

    public function test_a_day_without_a_sitting_is_closed(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        $availability = $this->slots->availability($chamber, $date->copy()->addDay());

        $this->assertFalse($availability->isOpen);
        $this->assertSame(0, $availability->openCount());
    }

    public function test_a_booked_slot_is_marked_taken_and_not_offered(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        Appointment::create([
            'appointment_no' => 'APT-TEST-0001',
            'chamber_id' => $chamber->id,
            'patient_name' => 'Booked Patient',
            'patient_phone' => '01712345678',
            'appointment_date' => $date->toDateString(),
            'slot_time' => '10:20:00',
            'status' => 'confirmed',
        ]);

        $availability = $this->slots->availability($chamber->fresh('activeSchedules'), $date);

        $this->assertSame(2, $availability->openCount());
        $this->assertFalse($availability->offers('10:20:00'));
    }

    public function test_a_cancelled_appointment_releases_its_slot(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        Appointment::create([
            'appointment_no' => 'APT-TEST-0002',
            'chamber_id' => $chamber->id,
            'patient_name' => 'Cancelled Patient',
            'patient_phone' => '01712345678',
            'appointment_date' => $date->toDateString(),
            'slot_time' => '10:20:00',
            'status' => 'cancelled',
        ]);

        $this->assertTrue($this->slots->availability($chamber, $date)->offers('10:20:00'));
    }

    public function test_an_exception_closes_the_date(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        ScheduleException::create([
            'chamber_id' => $chamber->id,
            'date' => $date->toDateString(),
            'is_available' => false,
            'reason_en' => 'Conference',
        ]);

        $availability = $this->slots->availability($chamber, $date);

        $this->assertFalse($availability->isOpen);
        $this->assertSame('Conference', $availability->closedReason);
    }

    /** chamber_id NULL means the doctor is away from every chamber that day. */
    public function test_a_site_wide_exception_closes_every_chamber(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        ScheduleException::create([
            'chamber_id' => null,
            'date' => $date->toDateString(),
            'is_available' => false,
        ]);

        $this->assertFalse($this->slots->availability($chamber, $date)->isOpen);
    }

    public function test_an_exception_can_open_an_extra_sitting_on_a_closed_day(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);
        $closedDay = $date->copy()->addDay();

        ScheduleException::create([
            'chamber_id' => $chamber->id,
            'date' => $closedDay->toDateString(),
            'is_available' => true,
            'start_time' => '15:00',
            'end_time' => '16:00',
            'slot_minutes' => 30,
        ]);

        $availability = $this->slots->availability($chamber, $closedDay);

        $this->assertTrue($availability->isOpen);
        $this->assertSame(['15:00:00', '15:30:00'], array_column($availability->slots, 'time'));
    }

    public function test_max_patients_caps_the_number_of_slots(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '13:00', 20, maxPatients: 4);

        $this->assertSame(4, $this->slots->availability($chamber, $date)->openCount());
    }

    public function test_past_dates_are_closed(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        $availability = $this->slots->availability($chamber, Carbon::yesterday());

        $this->assertFalse($availability->isOpen);
    }

    public function test_the_booking_window_is_enforced(): void
    {
        $this->assertTrue($this->slots->isWithinWindow(Carbon::today()->addDays(5)));
        $this->assertFalse($this->slots->isWithinWindow(Carbon::today()->addDays(400)));
        $this->assertFalse($this->slots->isWithinWindow(Carbon::yesterday()));
    }

    public function test_it_normalises_time_strings_and_rejects_junk(): void
    {
        $this->assertSame('09:05:00', $this->slots->normaliseTime('09:05'));
        $this->assertSame('09:05:00', $this->slots->normaliseTime('09:05:00'));

        // A one-digit hour is a shape the pattern accepts, so it has to survive
        // the padding as well — it used to be read as junk and cost the slot.
        $this->assertSame('09:05:00', $this->slots->normaliseTime('9:05'));
        $this->assertSame('09:05:00', $this->slots->normaliseTime('9:05:00'));

        $this->assertNull($this->slots->normaliseTime('nonsense'));
        $this->assertNull($this->slots->normaliseTime(''));
        $this->assertNull($this->slots->normaliseTime(null));
        $this->assertNull($this->slots->normaliseTime('25:00'));
        $this->assertNull($this->slots->normaliseTime('10:75'));
        $this->assertNull($this->slots->normaliseTime('10:30:99'));
    }

    // -------------------------------------------------- the batched window

    /**
     * window() exists only to answer the same question as availability() for a
     * run of days without paying two queries each time. The moment the two can
     * disagree it is worse than the loop it replaced, so this walks the whole
     * span and holds them to the same answers.
     */
    public function test_the_window_agrees_with_asking_one_day_at_a_time(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '12:00', 20);

        // Something of everything inside the span: a closure, an extra sitting
        // on a day with no weekly pattern, and a slot already taken.
        ScheduleException::create([
            'chamber_id' => $chamber->id, 'date' => $date->toDateString(),
            'is_available' => false, 'reason_en' => 'Conference',
        ]);
        ScheduleException::create([
            'chamber_id' => $chamber->id, 'date' => $date->copy()->addDay()->toDateString(),
            'is_available' => true, 'start_time' => '15:00', 'end_time' => '16:00', 'slot_minutes' => 30,
        ]);
        ScheduleException::create([
            'chamber_id' => null, 'date' => $date->copy()->addDays(2)->toDateString(),
            'is_available' => false,
        ]);
        Appointment::create([
            'appointment_no' => 'APT-TEST-WINDOW', 'chamber_id' => $chamber->id,
            'patient_name' => 'Someone', 'patient_phone' => '01712345678',
            'appointment_date' => $date->copy()->addDays(7)->toDateString(),
            'slot_time' => '10:20:00', 'status' => 'confirmed', 'visit_type' => 'new',
        ]);

        $window = $this->slots->window($chamber, 20);

        $this->assertCount(21, $window);

        foreach ($window as $key => $batched) {
            $single = $this->slots->availability($chamber, Carbon::parse($key));

            $this->assertSame($single->isOpen, $batched->isOpen, "isOpen differs on {$key}");
            $this->assertSame($single->closedReason, $batched->closedReason, "closedReason differs on {$key}");
            $this->assertSame($single->slots, $batched->slots, "slots differ on {$key}");
        }
    }

    /**
     * A chamber's own exception outranks the site-wide one. exceptionFor()
     * orders the pair so the specific row is read first; the batched read
     * orders it the other way, because keyBy() keeps the last of a repeated
     * key. Two orderings, one rule — worth pinning from both directions.
     */
    public function test_a_chamber_exception_outranks_a_site_wide_one_on_the_same_date(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        ScheduleException::create([
            'chamber_id' => null, 'date' => $date->toDateString(), 'is_available' => false,
            'reason_en' => 'Away everywhere',
        ]);
        ScheduleException::create([
            'chamber_id' => $chamber->id, 'date' => $date->toDateString(), 'is_available' => true,
            'start_time' => '18:00', 'end_time' => '19:00', 'slot_minutes' => 30,
        ]);

        $batched = $this->slots->window($chamber)[$date->toDateString()];
        $single = $this->slots->availability($chamber, $date);

        foreach (['batched' => $batched, 'single' => $single] as $label => $availability) {
            $this->assertTrue($availability->isOpen, "the chamber override lost to the site-wide row ({$label})");
            $this->assertSame(['18:00:00', '18:30:00'], array_column($availability->slots, 'time'), $label);
        }
    }

    /** However long the window, it costs the same handful of queries. */
    public function test_the_window_does_not_query_per_day(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '12:00', 20);

        $count = function (int $days) use ($chamber): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->slots->window($chamber, $days);

            return count(DB::getQueryLog());
        };

        $this->assertSame($count(7), $count(60), 'the window costs more the longer it gets');
    }

    public function test_the_next_available_date_skips_days_that_are_closed(): void
    {
        // Tomorrow's weekday, so the first sitting is the very next day: near
        // enough that nothing earlier can win, far enough that the lead time on
        // today's slots does not come into it.
        $first = Carbon::tomorrow();
        $chamber = $this->chamberSitting($first->dayOfWeek, '10:00', '11:00', 20);

        $this->assertSame($first->toDateString(), $this->slots->nextAvailableDate($chamber)?->toDateString());

        ScheduleException::create([
            'chamber_id' => $chamber->id, 'date' => $first->toDateString(), 'is_available' => false,
        ]);

        $this->assertSame(
            $first->copy()->addWeek()->toDateString(),
            $this->slots->nextAvailableDate($chamber)?->toDateString(),
            'a closed day was still offered as the next free date',
        );
    }

    public function test_a_chamber_that_never_sits_has_no_next_date(): void
    {
        $chamber = Chamber::create([
            'slug' => 'no-sittings', 'name_en' => 'No Sittings', 'is_active' => true,
        ]);

        $this->assertNull($this->slots->nextAvailableDate($chamber));
    }

    public function test_the_calendar_reports_each_day_of_the_window(): void
    {
        $date = $this->nextWeekday(1);
        $chamber = $this->chamberSitting($date->dayOfWeek, '10:00', '11:00', 20);

        $calendar = collect($this->slots->calendar($chamber, 13));
        $open = $calendar->firstWhere('date', $date->toDateString());

        $this->assertCount(14, $calendar);
        $this->assertTrue($open['open']);
        $this->assertSame(3, $open['count']);
        $this->assertSame($date->dayOfWeek, $open['day']);
        $this->assertFalse($calendar->firstWhere('date', $date->copy()->addDay()->toDateString())['open']);
    }

    // ------------------------------------------------------------ helpers

    private function chamberSitting(int $day, string $start, string $end, int $minutes, ?int $maxPatients = null): Chamber
    {
        $chamber = Chamber::create([
            'slug' => 'test-chamber-'.uniqid(),
            'name_en' => 'Test Chamber',
            'is_active' => true,
            'accepts_online_booking' => true,
        ]);

        ChamberSchedule::create([
            'chamber_id' => $chamber->id,
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'slot_minutes' => $minutes,
            'max_patients' => $maxPatients,
            'is_active' => true,
        ]);

        return $chamber->load('activeSchedules');
    }

    /** A future date on the given weekday, always at least two days out. */
    private function nextWeekday(int $dayOfWeek): Carbon
    {
        $date = Carbon::today()->addDays(2);

        while ($date->dayOfWeek !== $dayOfWeek) {
            $date->addDay();
        }

        return $date;
    }
}
