<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\GalleryAlbum;
use App\Models\ScheduleException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * ListingLabelsTest holds the column *headers* to the staff member's language,
 * and FormLabelsTest holds the field labels. Nothing held the rows.
 *
 * So the panel went on printing name_en under a Bangla heading, and dates and
 * counts in Latin digits beside Bangla ones — the same defect those two guards
 * were built for, one layer further in. What a page shows is as much a part of
 * being bilingual as what it calls things.
 *
 * The paired English/Bangla columns in the listings are deliberate and are not
 * covered here: they exist so an operator can see at a glance whether the
 * Bangla has been filled in yet.
 */
class ListingContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $locale = 'bn'): User
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)->withSession(['admin_locale' => $locale]);

        return $user;
    }

    private function chamber(): Chamber
    {
        return Chamber::create([
            'slug' => 'insaf', 'name_en' => 'Insaf Barakah', 'name_bn' => 'ইনসাফ বারাকাহ',
            'address_en' => 'Farmgate', 'address_bn' => 'ফার্মগেট',
            'is_active' => true, 'accepts_online_booking' => true,
        ]);
    }

    private function appointment(Chamber $chamber, ?string $date = null): Appointment
    {
        return Appointment::create([
            'appointment_no' => 'APT-TEST-'.fake()->unique()->numerify('######'),
            'chamber_id' => $chamber->id,
            'patient_name' => 'Shamima Akter', 'patient_phone' => '01712345678',
            'appointment_date' => $date ?? Carbon::today()->addDays(3)->toDateString(),
            'slot_time' => '10:30:00', 'status' => 'pending', 'visit_type' => 'new',
        ]);
    }

    public function test_a_related_name_follows_the_panel_language(): void
    {
        $chamber = $this->chamber();
        $appointment = $this->appointment($chamber);
        // The dashboard names a chamber only in the list of today's sittings.
        $this->appointment($chamber, Carbon::today()->toDateString());
        $this->admin('bn');

        foreach (['/admin', '/admin/appointments', '/admin/appointments/'.$appointment->appointment_no] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('ইনসাফ বারাকাহ', escape: false)
                ->assertDontSee('Insaf Barakah', escape: false);
        }
    }

    public function test_the_same_pages_stay_english_for_an_english_operator(): void
    {
        $chamber = $this->chamber();
        $this->appointment($chamber);
        $this->admin('en');

        $this->get('/admin/appointments')->assertOk()
            ->assertSee('Insaf Barakah', escape: false)
            ->assertDontSee('ইনসাফ বারাকাহ', escape: false);
    }

    /** Dates, times and counts are as much a part of the language as the words. */
    public function test_dates_times_and_counts_are_written_in_bangla_numerals(): void
    {
        $chamber = $this->chamber();
        $appointment = $this->appointment($chamber);
        $this->admin('bn');

        // 10:30 AM, which Bangla writes as সকাল ১০:৩০ — no meridiem at all.
        $listing = $this->get('/admin/appointments')->assertOk()
            ->assertSee('সকাল ১০:৩০', escape: false)
            ->assertDontSee('10:30', escape: false);

        // Anchored to a digit, as `DateFormattingTest` anchors its own sweep.
        // A bare assertDontSee('AM') also matched those two letters landing
        // inside the CSRF token, which a 40-character random string does about
        // once in a hundred runs — and this test duly failed about that often.
        $this->assertDoesNotMatchRegularExpression(
            '/\d\s*(AM|PM)\b/',
            $listing->getContent(),
            'The appointments listing prints a Latin meridiem.'
        );

        $this->get('/admin/appointments/'.$appointment->appointment_no)->assertOk()
            ->assertSee(__('site.months.'.$appointment->appointment_date->month, [], 'bn'), escape: false);

        // The dashboard's headline figures.
        $this->get('/admin')->assertOk()->assertSee('১', escape: false);
    }

    public function test_a_chambers_schedule_page_is_written_in_bangla(): void
    {
        $chamber = $this->chamber();
        ChamberSchedule::create([
            'chamber_id' => $chamber->id, 'day_of_week' => 1,
            'start_time' => '17:00', 'end_time' => '20:00',
            'slot_minutes' => 20, 'max_patients' => 15, 'is_active' => true,
        ]);
        $this->admin('bn');

        $this->get(route('admin.chambers.schedules.index', $chamber))->assertOk()
            ->assertSee('ইনসাফ বারাকাহ', escape: false)
            ->assertSee('বিকেল ৫টা', escape: false)         // 5:00 PM, in Bangla
            ->assertSee('২০ মিনিট', escape: false)          // 20 minutes per patient
            ->assertSee('সর্বোচ্চ ১৫', escape: false)        // capped at 15
            ->assertDontSee('20 min', escape: false)
            ->assertDontSee('max 15', escape: false);
    }

    public function test_an_exception_listing_names_its_chamber_in_bangla(): void
    {
        $chamber = $this->chamber();
        ScheduleException::create([
            'chamber_id' => $chamber->id,
            'date' => Carbon::today()->addDays(4)->toDateString(),
            'is_available' => false, 'reason_en' => 'Conference',
        ]);
        $this->admin('bn');

        $this->get('/admin/exceptions')->assertOk()
            ->assertSee('ইনসাফ বারাকাহ', escape: false)
            ->assertDontSee('Insaf Barakah', escape: false);
    }

    public function test_a_gallery_album_is_titled_in_bangla(): void
    {
        $album = GalleryAlbum::create([
            'slug' => 'clinic', 'title_en' => 'Clinic Tour', 'title_bn' => 'চেম্বার পরিদর্শন',
            'is_active' => true,
        ]);
        $this->admin('bn');

        $this->get(route('admin.albums.items.index', $album))->assertOk()
            ->assertSee('চেম্বার পরিদর্শন', escape: false)
            ->assertDontSee('Clinic Tour', escape: false);
    }

    /** Bangla missing on a row is not a reason to print nothing. */
    public function test_a_row_without_bangla_still_shows_its_english(): void
    {
        $chamber = Chamber::create([
            'slug' => 'brb', 'name_en' => 'BRB Hospital', 'is_active' => true,
        ]);
        $this->appointment($chamber);
        $this->admin('bn');

        $this->get('/admin/appointments')->assertOk()->assertSee('BRB Hospital', escape: false);
    }
}
