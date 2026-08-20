<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Chamber $chamber;

    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->date = Carbon::today()->addDays(3);

        $this->chamber = Chamber::create([
            'slug' => 'booking-chamber',
            'name_en' => 'Booking Chamber',
            'name_bn' => 'বুকিং চেম্বার',
            'is_active' => true,
            'accepts_online_booking' => true,
        ]);

        ChamberSchedule::create([
            'chamber_id' => $this->chamber->id,
            'day_of_week' => $this->date->dayOfWeek,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'slot_minutes' => 30,
            'is_active' => true,
        ]);
    }

    public function test_the_booking_page_loads_in_both_languages(): void
    {
        $this->get('/en/appointment')->assertOk()->assertSee('Booking Chamber');
        $this->get('/bn/appointment')->assertOk()->assertSee('বুকিং চেম্বার');
    }

    public function test_the_slots_endpoint_returns_availability(): void
    {
        $response = $this->getJson('/en/appointment/slots?chamber_id='.$this->chamber->id.'&date='.$this->date->toDateString());

        $response->assertOk()
            ->assertJsonPath('open', true)
            ->assertJsonPath('open_count', 4);
    }

    public function test_a_patient_can_book_a_free_slot(): void
    {
        $response = $this->post('/en/appointment', $this->payload());

        $appointment = Appointment::first();

        $this->assertNotNull($appointment);
        $response->assertRedirect('/en/appointment/'.$appointment->appointment_no);

        $this->assertSame('Rahim Uddin', $appointment->patient_name);
        $this->assertSame('pending', $appointment->status);
        $this->assertSame('10:30:00', $appointment->slot_time);
        $this->assertSame($this->chamber->id, $appointment->chamber_id);
    }

    public function test_the_confirmation_page_shows_the_serial_number(): void
    {
        $this->post('/en/appointment', $this->payload());
        $appointment = Appointment::first();

        $this->get('/en/appointment/'.$appointment->appointment_no)
            ->assertOk()
            ->assertSee($appointment->appointment_no)
            ->assertSee('Rahim Uddin');
    }

    public function test_the_same_slot_cannot_be_booked_twice(): void
    {
        $this->post('/en/appointment', $this->payload());

        $response = $this->post('/en/appointment', $this->payload([
            'patient_name' => 'Second Patient',
            'patient_phone' => '01812345678',
        ]));

        $response->assertSessionHasErrors('slot_time');
        $this->assertSame(1, Appointment::count());
    }

    public function test_a_slot_outside_the_sitting_is_rejected(): void
    {
        $response = $this->post('/en/appointment', $this->payload(['slot_time' => '23:00:00']));

        $response->assertSessionHasErrors('slot_time');
        $this->assertSame(0, Appointment::count());
    }

    public function test_a_date_beyond_the_booking_window_is_rejected(): void
    {
        $response = $this->post('/en/appointment', $this->payload([
            'appointment_date' => Carbon::today()->addDays(400)->toDateString(),
        ]));

        $response->assertSessionHasErrors('slot_time');
        $this->assertSame(0, Appointment::count());
    }

    public function test_an_invalid_phone_number_is_rejected(): void
    {
        $response = $this->post('/en/appointment', $this->payload(['patient_phone' => '12345']));

        $response->assertSessionHasErrors('patient_phone');
        $this->assertSame(0, Appointment::count());
    }

    public function test_one_phone_number_cannot_hold_more_than_three_open_appointments(): void
    {
        $slots = app(SlotService::class);
        $available = array_column($slots->availability($this->chamber, $this->date)->openSlots(), 'time');

        foreach (array_slice($available, 0, 3) as $slot) {
            $this->post('/en/appointment', $this->payload(['slot_time' => $slot]));
        }

        $this->assertSame(3, Appointment::count());

        $response = $this->post('/en/appointment', $this->payload(['slot_time' => $available[3]]));

        $response->assertSessionHasErrors('slot_time');
        $this->assertSame(3, Appointment::count());
    }

    public function test_a_chamber_with_online_booking_disabled_is_rejected(): void
    {
        $this->chamber->update(['accepts_online_booking' => false]);

        $this->post('/en/appointment', $this->payload())->assertSessionHasErrors('chamber_id');
        $this->assertSame(0, Appointment::count());
    }

    public function test_the_serial_lookup_finds_an_appointment(): void
    {
        $this->post('/en/appointment', $this->payload());
        $appointment = Appointment::first();

        $this->get('/en/appointment/lookup?serial='.$appointment->appointment_no)
            ->assertRedirect('/en/appointment/'.$appointment->appointment_no);

        $this->get('/en/appointment/lookup?serial=APT-NOPE-0000')
            ->assertOk()
            ->assertSee('No appointment matches');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'chamber_id' => $this->chamber->id,
            'appointment_date' => $this->date->toDateString(),
            'slot_time' => '10:30:00',
            'patient_name' => 'Rahim Uddin',
            'patient_phone' => '01712345678',
            'visit_type' => 'new',
        ], $overrides);
    }
}
