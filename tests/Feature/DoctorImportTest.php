<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `doctor:import` had no test at all, and the gap showed: the fix that stopped
 * it forcing every chamber active was made without one, and the same hardcoded
 * `is_active = true` sat on in the services loop for another two days.
 *
 * The rule both follow is that the file is the source of truth — a service is
 * on unless the file says otherwise — so what has to be guarded is that "says
 * otherwise" is heard at all, and that silence still means on, or an existing
 * install would hide six expertise pages the first time anyone re-imported.
 */
class DoctorImportTest extends TestCase
{
    use RefreshDatabase;

    private const FILE = 'tests/Fixtures/services-switched-off.yml';

    private function services(): void
    {
        Service::create(['slug' => 'spine-surgery', 'name_en' => 'Spine Surgery', 'is_active' => true]);
        Service::create(['slug' => 'joint-and-knee-pain', 'name_en' => 'Joint and Knee Pain Treatment', 'is_active' => true]);
    }

    private function import(): void
    {
        $this->artisan('doctor:import', ['--file' => self::FILE])->assertSuccessful();
    }

    public function test_the_file_can_switch_a_service_off(): void
    {
        $this->services();
        $this->import();

        $this->assertFalse(Service::where('slug', 'spine-surgery')->first()->is_active);
    }

    /** Silence means on, so an install that never names the key is unchanged. */
    public function test_a_service_the_file_says_nothing_about_stays_on(): void
    {
        $this->services();
        $this->import();

        $this->assertTrue(Service::where('slug', 'joint-and-knee-pain')->first()->is_active);
    }

    /** The state has to survive the next import, or it is only a pause. */
    public function test_the_off_state_survives_re_importing(): void
    {
        $this->services();
        $this->import();
        $this->import();

        $this->assertFalse(Service::where('slug', 'spine-surgery')->first()->is_active);
    }

    /**
     * Being off in a column is not the same as being gone from the site. This is
     * the half the switch exists for, and the half nothing checked.
     */
    public function test_a_service_the_file_switched_off_leaves_the_site(): void
    {
        $this->services();
        $this->import();

        // The file gives both names, so each page has to be read in its own
        // language — the English name is only a fallback for a blank column.
        $off = ['en' => 'Spine Surgery', 'bn' => 'স্পাইন সার্জারি'];
        $on = ['en' => 'Joint and Knee Pain Treatment', 'bn' => 'জয়েন্ট ও হাঁটু ব্যথার চিকিৎসা'];

        foreach (['en', 'bn'] as $locale) {
            $this->get("/{$locale}/expertise")->assertOk()
                ->assertSee($on[$locale], escape: false)
                ->assertDontSee($off[$locale], escape: false)
                ->assertDontSee($off['en'], escape: false);

            $this->get("/{$locale}/expertise/spine-surgery")->assertNotFound();
        }
    }
}
