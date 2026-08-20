<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\ScheduleException;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

        $availability = $this->slots->availability($chamber->fresh('schedules'), $date);

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
        $this->assertNull($this->slots->normaliseTime('nonsense'));
        $this->assertNull($this->slots->normaliseTime(''));
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

        return $chamber->load('schedules');
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
