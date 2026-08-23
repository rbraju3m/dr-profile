<?php

namespace Tests\Feature;

use App\Exceptions\SlotUnavailableException;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\ScheduleException;
use App\Services\BookingService;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The parts of booking the first BookingTest does not reach: what happens on
 * the day itself, when a chamber sits twice, when two chambers overlap, and
 * what the transaction actually does to keep two patients off one slot.
 *
 * Time is frozen throughout — a lead-time rule tested against the real clock
 * passes or fails depending on when the suite runs.
 */
class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** A chamber sitting on the given weekday. */
    private function chamber(string $slug, int $day, string $start, string $end, int $minutes = 30): Chamber
    {
        $chamber = Chamber::create([
            'slug' => $slug,
            'name_en' => ucfirst($slug),
            'is_active' => true,
            'accepts_online_booking' => true,
        ]);

        ChamberSchedule::create([
            'chamber_id' => $chamber->id,
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'slot_minutes' => $minutes,
            'is_active' => true,
        ]);

        return $chamber->load('activeSchedules');
    }

    private function slots(Chamber $chamber, Carbon $date): array
    {
        return array_column(
            app(SlotService::class)->availability($chamber->fresh('activeSchedules'), $date)->openSlots(),
            'time'
        );
    }

    // ------------------------------------------------------------- lead time

    /**
     * Serials close an hour before the slot, so a patient cannot book a time
     * they could not physically reach.
     */
    public function test_todays_imminent_slots_are_no_longer_offered(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 0));

        $chamber = $this->chamber('today-chamber', Carbon::today()->dayOfWeek, '08:00', '14:00');

        $offered = $this->slots($chamber, Carbon::today());

        // now is 09:00 and the lead time is 60 minutes, so 10:00 is the first
        // slot a patient may still take.
        $this->assertNotContains('08:00:00', $offered, 'a slot in the past was offered');
        $this->assertNotContains('09:00:00', $offered, 'a slot starting now was offered');
        $this->assertNotContains('09:30:00', $offered, 'a slot inside the lead time was offered');
        $this->assertContains('10:00:00', $offered);
        $this->assertContains('13:30:00', $offered);
    }

    public function test_a_slot_later_today_can_still_be_booked(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 0));

        $chamber = $this->chamber('today-chamber', Carbon::today()->dayOfWeek, '08:00', '14:00');

        $this->post('/en/appointment', [
            'chamber_id' => $chamber->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'slot_time' => '11:00:00',
            'patient_name' => 'Same Day Patient',
            'patient_phone' => '01712345678',
            'visit_type' => 'new',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    public function test_booking_a_slot_inside_the_lead_time_is_refused(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 0));

        $chamber = $this->chamber('today-chamber', Carbon::today()->dayOfWeek, '08:00', '14:00');

        $this->post('/en/appointment', [
            'chamber_id' => $chamber->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'slot_time' => '09:30:00',
            'patient_name' => 'Too Late Patient',
            'patient_phone' => '01712345678',
            'visit_type' => 'new',
        ])->assertSessionHasErrors('slot_time');

        $this->assertSame(0, Appointment::count());
    }

    public function test_a_whole_day_that_has_already_passed_offers_nothing(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $chamber = $this->chamber('today-chamber', Carbon::today()->dayOfWeek, '08:00', '14:00');

        $this->assertSame([], $this->slots($chamber, Carbon::today()));
    }

    // --------------------------------------------------------- sitting shape

    /** A morning and an evening sitting on one day merge into a single sorted list. */
    public function test_two_sittings_on_one_day_are_merged_in_order(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('twice-daily', $date->dayOfWeek, '10:00', '11:00', 30);

        ChamberSchedule::create([
            'chamber_id' => $chamber->id,
            'day_of_week' => $date->dayOfWeek,
            'start_time' => '17:00',
            'end_time' => '18:00',
            'slot_minutes' => 30,
            'is_active' => true,
        ]);

        $this->assertSame(
            ['10:00:00', '10:30:00', '17:00:00', '17:30:00'],
            $this->slots($chamber, $date)
        );
    }

    public function test_an_inactive_sitting_is_ignored(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('paused', $date->dayOfWeek, '10:00', '11:00');

        $chamber->schedules()->update(['is_active' => false]);

        $this->assertSame([], $this->slots($chamber, $date));
    }

    /** Two chambers can sit at the same hour; booking one must not block the other. */
    public function test_chambers_hold_their_slots_independently(): void
    {
        $date = Carbon::today()->addDays(2);
        $first = $this->chamber('chamber-one', $date->dayOfWeek, '10:00', '11:00');
        $second = $this->chamber('chamber-two', $date->dayOfWeek, '10:00', '11:00');

        app(BookingService::class)->book($first, $date, '10:00:00', [
            'patient_name' => 'First Chamber Patient',
            'patient_phone' => '01712345678',
        ]);

        $this->assertNotContains('10:00:00', $this->slots($first, $date));
        $this->assertContains('10:00:00', $this->slots($second, $date), 'the other chamber lost a slot it never sold');
    }

    public function test_an_extra_sitting_added_for_one_date_becomes_bookable(): void
    {
        $date = Carbon::today()->addDays(2);
        $closedDay = $date->copy()->addDay();
        $chamber = $this->chamber('extra-sitting', $date->dayOfWeek, '10:00', '11:00');

        ScheduleException::create([
            'chamber_id' => $chamber->id,
            'date' => $closedDay->toDateString(),
            'is_available' => true,
            'start_time' => '15:00',
            'end_time' => '16:00',
            'slot_minutes' => 30,
        ]);

        $this->post('/en/appointment', [
            'chamber_id' => $chamber->id,
            'appointment_date' => $closedDay->toDateString(),
            'slot_time' => '15:30:00',
            'patient_name' => 'Extra Sitting Patient',
            'patient_phone' => '01712345678',
            'visit_type' => 'new',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    // ------------------------------------------------------- slots endpoint

    public function test_the_slots_endpoint_hides_a_chamber_that_is_not_bookable(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('counter-only', $date->dayOfWeek, '10:00', '11:00');
        $chamber->update(['accepts_online_booking' => false]);

        $this->getJson("/en/appointment/slots?chamber_id={$chamber->id}&date={$date->toDateString()}")
            ->assertNotFound();
    }

    public function test_the_slots_endpoint_validates_its_input(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('validating', $date->dayOfWeek, '10:00', '11:00');

        $this->getJson("/en/appointment/slots?chamber_id={$chamber->id}&date=not-a-date")
            ->assertStatus(422);

        $this->getJson('/en/appointment/slots?chamber_id=99999&date='.$date->toDateString())
            ->assertStatus(422);
    }

    public function test_the_slots_endpoint_marks_taken_times_rather_than_omitting_them(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('marking', $date->dayOfWeek, '10:00', '11:00');

        app(BookingService::class)->book($chamber, $date, '10:00:00', [
            'patient_name' => 'Holder',
            'patient_phone' => '01712345678',
        ]);

        $response = $this->getJson("/en/appointment/slots?chamber_id={$chamber->id}&date={$date->toDateString()}")
            ->assertOk()
            ->assertJsonPath('open_count', 1);

        // The taken slot is still listed, struck through, so the patient can see
        // it existed rather than wondering why the day looks half empty.
        $times = collect($response->json('slots'));
        $this->assertTrue($times->firstWhere('time', '10:00:00')['taken']);
        $this->assertFalse($times->firstWhere('time', '10:30:00')['taken']);
    }

    // --------------------------------------------------------- confirmation

    public function test_a_confirmation_email_goes_out_when_an_address_is_given(): void
    {
        Mail::fake();

        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('emailing', $date->dayOfWeek, '10:00', '11:00');

        $this->post('/en/appointment', [
            'chamber_id' => $chamber->id,
            'appointment_date' => $date->toDateString(),
            'slot_time' => '10:00:00',
            'patient_name' => 'Emailed Patient',
            'patient_phone' => '01712345678',
            'patient_email' => 'patient@example.test',
            'visit_type' => 'new',
        ])->assertSessionHasNoErrors();

        Mail::assertSent(AppointmentConfirmation::class, fn ($mail) => $mail->hasTo('patient@example.test'));
    }

    public function test_no_email_is_attempted_when_the_patient_gave_none(): void
    {
        Mail::fake();

        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('silent', $date->dayOfWeek, '10:00', '11:00');

        $this->post('/en/appointment', [
            'chamber_id' => $chamber->id,
            'appointment_date' => $date->toDateString(),
            'slot_time' => '10:00:00',
            'patient_name' => 'Quiet Patient',
            'patient_phone' => '01712345678',
            'visit_type' => 'new',
        ])->assertSessionHasNoErrors();

        Mail::assertNothingSent();
        $this->assertSame(1, Appointment::count());
    }

    /** A mail outage must not lose a booking the patient was already promised. */
    public function test_a_failing_mailer_does_not_lose_the_appointment(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('broken-mail', $date->dayOfWeek, '10:00', '11:00');

        $this->post('/en/appointment', [
            'chamber_id' => $chamber->id,
            'appointment_date' => $date->toDateString(),
            'slot_time' => '10:00:00',
            'patient_name' => 'Undelivered Patient',
            'patient_phone' => '01712345678',
            'patient_email' => 'patient@example.test',
            'visit_type' => 'new',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    public function test_every_booking_gets_a_distinct_dated_serial(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('serials', $date->dayOfWeek, '10:00', '13:00', 30);

        foreach (['10:00:00', '10:30:00', '11:00:00'] as $i => $time) {
            app(BookingService::class)->book($chamber, $date, $time, [
                'patient_name' => "Patient {$i}",
                'patient_phone' => '0171234567'.$i,
            ]);
        }

        $serials = Appointment::pluck('appointment_no');

        $this->assertCount(3, $serials->unique(), 'serial numbers collided');

        foreach ($serials as $serial) {
            $this->assertMatchesRegularExpression('/^APT-'.$date->format('Ymd').'-[A-Z0-9]{6}$/', $serial);
        }
    }

    // --------------------------------------------------------- concurrency

    /**
     * Two patients can press Confirm at the same moment, so availability is
     * re-read inside the transaction while a row lock is held on the chamber.
     * This asserts the lock is genuinely taken — without it the re-read could
     * see stale rows and both bookings would succeed.
     */
    public function test_booking_takes_a_row_lock_on_the_chamber(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('locking', $date->dayOfWeek, '10:00', '11:00');

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });

        app(BookingService::class)->book($chamber, $date, '10:00:00', [
            'patient_name' => 'Locking Patient',
            'patient_phone' => '01712345678',
        ]);

        $locking = array_filter(
            $queries,
            fn (string $sql) => str_contains($sql, 'from `chambers`') && str_contains($sql, 'for update')
        );

        $this->assertNotEmpty($locking, 'the chamber row was never locked, so two patients could take one slot');
    }

    public function test_the_second_of_two_bookings_on_one_slot_is_rejected(): void
    {
        $date = Carbon::today()->addDays(2);
        $chamber = $this->chamber('contended', $date->dayOfWeek, '10:00', '11:00');
        $booking = app(BookingService::class);

        $booking->book($chamber, $date, '10:00:00', [
            'patient_name' => 'First Patient',
            'patient_phone' => '01712345678',
        ]);

        $this->expectException(SlotUnavailableException::class);

        $booking->book($chamber, $date, '10:00:00', [
            'patient_name' => 'Second Patient',
            'patient_phone' => '01812345678',
        ]);
    }
}
