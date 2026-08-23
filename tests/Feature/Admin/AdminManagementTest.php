<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SettingController;
use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\ContactMessage;
use App\Models\DoctorProfile;
use App\Models\GalleryAlbum;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers the parts of the admin that change what patients see.
 *
 * The CRUD tests next door check that a form saves a row. These check the
 * consequence: that cancelling an appointment hands the slot back, that closing
 * a date really closes it, that editing the profile reaches the public page
 * rather than sitting behind a stale cache.
 */
class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Chamber $chamber;

    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->date = Carbon::today()->addDays(3);

        $this->chamber = Chamber::create([
            'slug' => 'main-chamber',
            'name_en' => 'Main Chamber',
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

    private function appointment(array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'appointment_no' => 'APT-TEST-'.fake()->unique()->numerify('####'),
            'chamber_id' => $this->chamber->id,
            'patient_name' => 'Rahima Khatun',
            'patient_phone' => '01712345678',
            'appointment_date' => $this->date->toDateString(),
            'slot_time' => '10:30:00',
            'status' => 'pending',
        ], $overrides));
    }

    private function slotsOpen(): int
    {
        return app(SlotService::class)
            ->availability($this->chamber->fresh('activeSchedules'), $this->date)
            ->openCount();
    }

    // ---------------------------------------------------------- appointments

    public function test_the_dashboard_counts_what_is_waiting(): void
    {
        $this->appointment(['status' => 'pending']);
        $this->appointment(['slot_time' => '11:00:00', 'status' => 'confirmed']);
        ContactMessage::create(['name' => 'A', 'phone' => '01712345678', 'message' => 'hello there', 'is_read' => false]);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('Unread messages');
    }

    public function test_confirming_an_appointment_stamps_the_time(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->from('/admin/appointments/'.$appointment->appointment_no)
            ->patch('/admin/appointments/'.$appointment->appointment_no.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $appointment->refresh();

        $this->assertSame('confirmed', $appointment->status);
        $this->assertNotNull($appointment->confirmed_at);
    }

    /** The consequence that matters: a cancelled slot goes back on sale. */
    public function test_cancelling_from_the_admin_releases_the_slot_to_patients(): void
    {
        $appointment = $this->appointment(['slot_time' => '10:30:00']);

        $held = $this->slotsOpen();

        $this->actingAs($this->admin)
            ->from('/admin/appointments/'.$appointment->appointment_no)
            ->patch('/admin/appointments/'.$appointment->appointment_no.'/status', [
                'status' => 'cancelled',
                'cancelled_reason' => 'Patient rang to cancel',
            ]);

        $appointment->refresh();

        $this->assertSame('cancelled', $appointment->status);
        $this->assertNotNull($appointment->cancelled_at);
        $this->assertSame('Patient rang to cancel', $appointment->cancelled_reason);
        $this->assertSame($held + 1, $this->slotsOpen(), 'cancelling did not free the slot');
    }

    public function test_an_unknown_status_is_rejected(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->from('/admin/appointments/'.$appointment->appointment_no)
            ->patch('/admin/appointments/'.$appointment->appointment_no.'/status', ['status' => 'archived'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_staff_can_correct_patient_details(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->put('/admin/appointments/'.$appointment->appointment_no, [
                'patient_name' => 'Rahima Begum',
                'patient_phone' => '01812345678',
                'admin_note' => 'Name corrected at the counter',
            ])
            ->assertRedirect('/admin/appointments');

        $appointment->refresh();

        $this->assertSame('Rahima Begum', $appointment->patient_name);
        $this->assertSame('Name corrected at the counter', $appointment->admin_note);
    }

    public function test_appointments_can_be_filtered(): void
    {
        $this->appointment(['patient_name' => 'Findable Patient', 'status' => 'pending']);
        $this->appointment(['slot_time' => '11:00:00', 'patient_name' => 'Other Patient', 'status' => 'cancelled']);

        $this->actingAs($this->admin)
            ->get('/admin/appointments?status=pending')
            ->assertOk()
            ->assertSee('Findable Patient')
            ->assertDontSee('Other Patient');

        $this->actingAs($this->admin)
            ->get('/admin/appointments?q=Other')
            ->assertOk()
            ->assertSee('Other Patient')
            ->assertDontSee('Findable Patient');
    }

    public function test_the_export_streams_the_filtered_appointments_as_csv(): void
    {
        $this->appointment(['patient_name' => 'Exported Patient', 'status' => 'confirmed']);
        $this->appointment(['slot_time' => '11:00:00', 'patient_name' => 'Cancelled Patient', 'status' => 'cancelled']);

        $response = $this->actingAs($this->admin)->get('/admin/appointments/export?status=confirmed');

        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Serial,Date,Time,Status', $csv);
        $this->assertStringContainsString('Exported Patient', $csv);
        $this->assertStringNotContainsString('Cancelled Patient', $csv);
        // Excel needs the BOM to read Bangla names correctly.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
    }

    // ------------------------------------------------------------- schedules

    public function test_adding_a_sitting_opens_slots_to_patients(): void
    {
        $emptyDay = $this->date->copy()->addDay();

        $this->assertSame(
            0,
            app(SlotService::class)->availability($this->chamber, $emptyDay)->openCount()
        );

        $this->actingAs($this->admin)->post('/admin/chambers/main-chamber/schedules', [
            'day_of_week' => $emptyDay->dayOfWeek,
            'start_time' => '15:00',
            'end_time' => '16:00',
            'slot_minutes' => 30,
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            2,
            app(SlotService::class)->availability($this->chamber->fresh('activeSchedules'), $emptyDay)->openCount()
        );
    }

    /**
     * The overlap guard used to ask only about the chamber being edited, so the
     * doctor could be booked into two of them for the same hour and the booking
     * form would sell both. He is one man; the guard has to look at all of them.
     */
    public function test_a_sitting_cannot_overlap_one_at_another_chamber(): void
    {
        $other = Chamber::create([
            'slug' => 'second-chamber',
            'name_en' => 'Second Chamber',
            'is_active' => true,
            'accepts_online_booking' => true,
        ]);

        // The main chamber already sits 10:00–12:00 on this weekday.
        $this->actingAs($this->admin)
            ->post('/admin/chambers/second-chamber/schedules', [
                'day_of_week' => $this->date->dayOfWeek,
                'start_time' => '11:00',
                'end_time' => '13:00',
                'slot_minutes' => 30,
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame(0, $other->schedules()->count());

        // Butting up against it is not an overlap, and must still be allowed.
        $this->actingAs($this->admin)
            ->post('/admin/chambers/second-chamber/schedules', [
                'day_of_week' => $this->date->dayOfWeek,
                'start_time' => '12:00',
                'end_time' => '14:00',
                'slot_minutes' => 30,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $other->schedules()->count());
    }

    /** The clash is named where it is edited, not left for someone to notice. */
    public function test_the_schedule_page_names_a_chamber_it_clashes_with(): void
    {
        $other = Chamber::create([
            'slug' => 'second-chamber',
            'name_en' => 'Second Chamber',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/chambers/main-chamber/schedules')
            ->assertOk()
            ->assertDontSee('Second Chamber');

        // Written straight to the table, the way the rows already in this
        // database were, before the guard covered every chamber.
        ChamberSchedule::create([
            'chamber_id' => $other->id,
            'day_of_week' => $this->date->dayOfWeek,
            'start_time' => '11:00',
            'end_time' => '13:00',
            'slot_minutes' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/chambers/main-chamber/schedules')
            ->assertOk()
            ->assertSee('Second Chamber');
    }

    public function test_removing_a_sitting_closes_the_day(): void
    {
        $schedule = $this->chamber->schedules()->first();

        $this->actingAs($this->admin)
            ->delete('/admin/schedules/'.$schedule->id)
            ->assertRedirect();

        $this->assertSame(0, $this->slotsOpen());
    }

    /** Marking a leave day in the admin must actually stop patients booking it. */
    public function test_closing_a_date_blocks_booking_on_the_public_site(): void
    {
        $this->assertGreaterThan(0, $this->slotsOpen());

        $this->actingAs($this->admin)->post('/admin/exceptions', [
            'chamber_id' => $this->chamber->id,
            'date' => $this->date->toDateString(),
            'is_available' => 0,
            'reason_en' => 'Away at a conference',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $this->slotsOpen());

        $this->getJson('/en/appointment/slots?chamber_id='.$this->chamber->id.'&date='.$this->date->toDateString())
            ->assertOk()
            ->assertJsonPath('open', false)
            ->assertJsonPath('reason', 'Away at a conference');
    }

    // --------------------------------------------------------------- profile

    public function test_editing_the_profile_reaches_the_public_site(): void
    {
        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Nusrat Jahan',
            'name_bn' => 'নুসরাত জাহান',
            'title_en' => 'Dr.',
            'designation_en' => 'Consultant Physician',
            'short_bio_en' => 'A short biography for the homepage.',
        ])->assertRedirect('/admin/profile');

        // Nothing may be served from a stale singleton cache.
        DoctorProfile::forgetCache();

        $this->get('/en')->assertOk()->assertSee('Dr. Nusrat Jahan');
        $this->get('/bn')->assertOk()->assertSee('নুসরাত জাহান');
    }

    public function test_the_profile_requires_a_name(): void
    {
        $this->actingAs($this->admin)
            ->put('/admin/profile', ['name_en' => ''])
            ->assertSessionHasErrors('name_en');
    }

    public function test_uploading_a_portrait_stores_it_and_replaces_the_old_one(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Nusrat Jahan',
            'photo' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $first = DoctorProfile::query()->first()->photo;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Nusrat Jahan',
            'photo' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $second = DoctorProfile::query()->first()->photo;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists($second);
        Storage::disk('public')->assertMissing($first, 'the replaced portrait was left behind on disk');
    }

    // -------------------------------------------------------------- settings

    public function test_settings_are_saved_and_shown_on_the_public_site(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'site_name_en' => 'The Heart Clinic',
            'footer_note_en' => 'Cardiology consultation in Dhaka.',
            'contact_address_en' => 'Road 11, Banani, Dhaka 1213',
        ])->assertRedirect('/admin/settings');

        Setting::forgetCache();

        $this->assertSame('The Heart Clinic', Setting::get('site_name_en'));

        $this->get('/en')
            ->assertOk()
            ->assertSee('Cardiology consultation in Dhaka.')
            ->assertSee('Road 11, Banani, Dhaka 1213');
    }

    /**
     * The settings screen must not offer a field nothing renders. Contact
     * details belong to the profile; a duplicate here would save happily and
     * change nothing, which is worse than not offering it at all.
     */
    public function test_the_settings_screen_only_offers_fields_the_site_uses(): void
    {
        $dead = ['contact_email', 'contact_phone', 'contact_hotline'];
        $offered = collect(SettingController::FIELDS)
            ->flatMap(fn (array $fields) => array_keys($fields))
            ->all();

        foreach ($dead as $key) {
            $this->assertNotContains($key, $offered, "[{$key}] is editable but never rendered");
        }
    }

    public function test_the_hotline_comes_from_the_profile_not_settings(): void
    {
        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Nusrat Jahan',
            'hotline' => '16263',
        ])->assertRedirect('/admin/profile');

        DoctorProfile::forgetCache();

        $this->get('/en')->assertOk()->assertSee('16263');
    }

    public function test_an_editor_cannot_change_settings(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);

        $this->actingAs($editor)
            ->put('/admin/settings', ['site_name_en' => 'Hijacked'])
            ->assertForbidden();

        $this->assertNull(Setting::get('site_name_en'));
    }

    // -------------------------------------------------------------- messages

    public function test_opening_a_message_marks_it_read(): void
    {
        $message = ContactMessage::create([
            'name' => 'Karim Ahmed',
            'phone' => '01712345678',
            'message' => 'When is the next available appointment?',
            'is_read' => false,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/messages/'.$message->id)
            ->assertOk()
            ->assertSee('Karim Ahmed');

        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_messages_can_be_filtered_to_unread(): void
    {
        ContactMessage::create(['name' => 'Unread Person', 'phone' => '01712345678', 'message' => 'first message here', 'is_read' => false]);
        ContactMessage::create(['name' => 'Read Person', 'phone' => '01712345679', 'message' => 'second message here', 'is_read' => true]);

        $this->actingAs($this->admin)
            ->get('/admin/messages?unread=1')
            ->assertOk()
            ->assertSee('Unread Person')
            ->assertDontSee('Read Person');
    }

    // --------------------------------------------------------------- gallery

    public function test_a_bulk_upload_creates_one_gallery_item_per_file(): void
    {
        Storage::fake('public');

        $album = GalleryAlbum::create(['slug' => 'camps', 'title_en' => 'Camps', 'is_active' => true]);

        $this->actingAs($this->admin)->post('/admin/albums/camps/items', [
            'type' => 'image',
            'images' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
                UploadedFile::fake()->image('three.jpg'),
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(3, $album->items()->count());

        foreach ($album->items as $item) {
            Storage::disk('public')->assertExists($item->image);
        }
    }

    public function test_a_video_item_requires_a_url(): void
    {
        $album = GalleryAlbum::create(['slug' => 'videos', 'title_en' => 'Videos', 'is_active' => true]);

        $this->actingAs($this->admin)
            ->post('/admin/albums/videos/items', ['type' => 'video'])
            ->assertSessionHasErrors('video_url');

        $this->assertSame(0, $album->items()->count());
    }

    // ------------------------------------------------------------ session/authz

    /** Deactivating an account must not leave its existing session usable. */
    public function test_a_deactivated_user_is_logged_out_mid_session(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);

        $this->actingAs($editor)->get('/admin')->assertOk();

        $editor->forceFill(['is_active' => false])->save();

        $this->actingAs($editor)->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_deleting_a_record_removes_its_uploaded_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Angioplasty',
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('procedure.jpg'),
        ]);

        $service = Service::first();
        $path = $service->image;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->admin)->delete('/admin/services/'.$service->slug);

        Storage::disk('public')->assertMissing($path, 'the upload was orphaned on disk');
    }
}
