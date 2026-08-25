<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * `og:image` is the one image on this site that goes everywhere — every share
 * of every page, on every network. It held a photograph of two stray dogs.
 *
 * What the command has to get right is that the column and the file are two
 * halves of the same thing: the column is in the database, the file is on the
 * storage disk, and neither travels with the code. So the test that matters is
 * not that the row changed but that the page ends up pointing at a file that is
 * actually there.
 */
class ShareCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The public disk is the real one in this suite; without a fake, every
        // run of these tests leaves another card behind in storage/app/public.
        Storage::fake('public');

        DoctorProfile::query()->delete();
        DoctorProfile::create(['name_en' => 'Shaikh Saadiul Islam', 'title_en' => 'Dr.']);
        DoctorProfile::forgetCache();
    }

    public function test_the_card_ships_with_the_repository(): void
    {
        $this->assertFileExists(resource_path('brand/share-card.png'));

        [$width, $height] = getimagesize(resource_path('brand/share-card.png'));

        // Below 1200x630 the networks crop or refuse it.
        $this->assertGreaterThanOrEqual(1200, $width);
        $this->assertGreaterThanOrEqual(630, $height);
    }

    public function test_it_installs_the_card_and_the_page_points_at_a_file_that_exists(): void
    {
        $this->artisan('profile:share-card')->assertSuccessful();

        DoctorProfile::forgetCache();
        $path = DoctorProfile::current()->og_image;

        $this->assertNotEmpty($path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        $this->get('/en')->assertOk()->assertSee('/storage/'.$path, escape: false);
    }

    /** The old file goes with it, or the disk keeps every card ever installed. */
    public function test_it_replaces_what_was_there(): void
    {
        Storage::disk('public')->put('profile/dogs.png', 'not a doctor');

        $profile = DoctorProfile::current();
        $profile->og_image = 'profile/dogs.png';
        $profile->save();
        DoctorProfile::forgetCache();

        $this->artisan('profile:share-card')->assertSuccessful();

        DoctorProfile::forgetCache();
        $this->assertNotSame('profile/dogs.png', DoctorProfile::current()->og_image);
        $this->assertFalse(Storage::disk('public')->exists('profile/dogs.png'));
    }

    /** Filenames are randomised, so "the same card" can only mean the same bytes. */
    public function test_running_it_again_leaves_the_installed_card_alone(): void
    {
        $this->artisan('profile:share-card')->assertSuccessful();

        DoctorProfile::forgetCache();
        $first = DoctorProfile::current()->og_image;

        $this->artisan('profile:share-card')->assertSuccessful();

        DoctorProfile::forgetCache();
        $this->assertSame($first, DoctorProfile::current()->og_image);
        $this->assertTrue(Storage::disk('public')->exists($first));
    }
}
