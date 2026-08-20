<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Service;
use App\Models\SuccessStory;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CancellationAndSearchTest extends TestCase
{
    use RefreshDatabase;

    private Chamber $chamber;

    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->date = Carbon::today()->addDays(3);

        $this->chamber = Chamber::create([
            'slug' => 'main', 'name_en' => 'Main Chamber',
            'is_active' => true, 'accepts_online_booking' => true,
        ]);

        ChamberSchedule::create([
            'chamber_id' => $this->chamber->id,
            'day_of_week' => $this->date->dayOfWeek,
            'start_time' => '10:00', 'end_time' => '12:00',
            'slot_minutes' => 30, 'is_active' => true,
        ]);
    }

    private function booking(array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'appointment_no' => 'APT-CANCEL-'.fake()->unique()->numerify('###'),
            'chamber_id' => $this->chamber->id,
            'patient_name' => 'Shamima Akter',
            'patient_phone' => '01712345678',
            'appointment_date' => $this->date->toDateString(),
            'slot_time' => '10:30:00',
            'status' => 'confirmed',
        ], $overrides));
    }

    private function slotsOpen(): int
    {
        return app(SlotService::class)
            ->availability($this->chamber->fresh('schedules'), $this->date)
            ->openCount();
    }

    // ----------------------------------------------------------- cancelling

    public function test_a_patient_can_cancel_with_the_number_they_booked_with(): void
    {
        $appointment = $this->booking();
        $before = $this->slotsOpen();

        $this->post('/en/appointment/'.$appointment->appointment_no.'/cancel', [
            'phone' => '01712345678',
        ])->assertRedirect('/en/appointment/'.$appointment->appointment_no);

        $appointment->refresh();

        $this->assertSame('cancelled', $appointment->status);
        $this->assertNotNull($appointment->cancelled_at);
        $this->assertSame($before + 1, $this->slotsOpen(), 'the cancelled slot was not released');
    }

    /** The serial is printed on a slip; it is not on its own proof of identity. */
    public function test_a_wrong_number_cannot_cancel_someone_elses_appointment(): void
    {
        $appointment = $this->booking();

        $this->post('/en/appointment/'.$appointment->appointment_no.'/cancel', [
            'phone' => '01999999999',
        ])->assertSessionHasErrors('phone');

        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_the_number_matches_with_or_without_the_country_code(): void
    {
        $appointment = $this->booking(['patient_phone' => '+8801712345678']);

        $this->post('/en/appointment/'.$appointment->appointment_no.'/cancel', [
            'phone' => '01712345678',
        ])->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $appointment->fresh()->status);
    }

    public function test_a_past_appointment_cannot_be_cancelled_online(): void
    {
        $appointment = $this->booking([
            'appointment_date' => Carbon::today()->subDay()->toDateString(),
        ]);

        $this->post('/en/appointment/'.$appointment->appointment_no.'/cancel', [
            'phone' => '01712345678',
        ])->assertSessionHasErrors('phone');

        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_an_already_cancelled_appointment_cannot_be_cancelled_again(): void
    {
        $appointment = $this->booking(['status' => 'cancelled']);

        $this->post('/en/appointment/'.$appointment->appointment_no.'/cancel', [
            'phone' => '01712345678',
        ])->assertSessionHasErrors('phone');
    }

    public function test_the_cancel_form_only_shows_while_cancelling_is_possible(): void
    {
        $upcoming = $this->booking();
        $this->get('/en/appointment/'.$upcoming->appointment_no)
            ->assertOk()
            ->assertSee('Cancel this appointment');

        $done = $this->booking(['slot_time' => '11:00:00', 'status' => 'completed']);
        $this->get('/en/appointment/'.$done->appointment_no)
            ->assertOk()
            ->assertDontSee('Cancel this appointment');
    }

    // -------------------------------------------------------------- search

    private function seedSearchableContent(): void
    {
        Service::create([
            'slug' => 'echocardiography', 'name_en' => 'Echocardiography',
            'name_bn' => 'ইকোকার্ডিওগ্রাফি', 'is_active' => true,
        ]);

        SuccessStory::create([
            'slug' => 'a-quiet-recovery', 'title_en' => 'A quiet recovery',
            'summary_en' => 'Echocardiography found the cause.', 'is_published' => true,
        ]);

        Post::create([
            'slug' => 'salt-and-your-heart', 'type' => 'blog',
            'title_en' => 'Salt and your heart', 'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        Faq::create([
            'group' => 'appointment', 'question_en' => 'How do I book?',
            'answer_en' => 'Use the booking form.', 'is_active' => true,
        ]);
    }

    public function test_search_finds_content_across_several_sections(): void
    {
        $this->seedSearchableContent();

        $this->get('/en/search?q=echocardiography')
            ->assertOk()
            ->assertSee('Echocardiography')
            ->assertSee('A quiet recovery');
    }

    public function test_search_matches_bangla_as_well_as_english(): void
    {
        $this->seedSearchableContent();

        $this->get('/bn/search?q=ইকো')->assertOk()->assertSee('ইকোকার্ডিওগ্রাফি');
    }

    public function test_search_says_so_when_nothing_matches(): void
    {
        $this->seedSearchableContent();

        $this->get('/en/search?q=zzzznothing')
            ->assertOk()
            ->assertSee('Nothing matched your search.');
    }

    public function test_an_empty_search_just_shows_the_form(): void
    {
        $this->get('/en/search')->assertOk()->assertDontSee('Nothing matched your search.');
    }

    public function test_search_does_not_surface_unpublished_content(): void
    {
        Post::create([
            'slug' => 'draft-article', 'type' => 'blog',
            'title_en' => 'Draft article about hearts', 'is_published' => false,
        ]);

        $this->get('/en/search?q=Draft')->assertOk()->assertDontSee('Draft article about hearts');
    }

    /** A patient typing % means the character, not "match everything". */
    public function test_wildcard_characters_are_treated_literally(): void
    {
        $this->seedSearchableContent();

        $this->get('/en/search?q=%25')
            ->assertOk()
            ->assertSee('Nothing matched your search.');
    }

    public function test_the_search_page_is_reachable_from_the_header(): void
    {
        $this->get('/en')->assertOk()->assertSee(url('/en/search'), false);
    }
}
