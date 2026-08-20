<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\DoctorProfile;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * An upload field that saves a file the site never displays is the same defect
 * as one that fails silently: the operator does the work and sees no result.
 *
 * These pin every image column to a public page, and pin the URL to a
 * root-relative form so a change of host or port cannot break it.
 */
class UploadedImagesAreShownTest extends TestCase
{
    use RefreshDatabase;

    /** Storage URLs must not carry a host, or they break when APP_URL drifts. */
    public function test_stored_media_urls_are_host_relative(): void
    {
        $chamber = Chamber::create([
            'slug' => 'clinic', 'name_en' => 'Clinic', 'is_active' => true,
            'image' => 'chambers/example.png',
        ]);

        $this->assertSame('/storage/chambers/example.png', $chamber->imageUrl());
        $this->assertStringStartsNotWith('http', $chamber->imageUrl());
    }

    public function test_an_absolute_url_is_still_used_where_a_host_is_required(): void
    {
        // og:image is read by other servers, so it cannot be relative.
        DoctorProfile::query()->firstOrNew([])->fill([
            'name_en' => 'Shaikh Saadiul Islam',
            'photo' => 'profile/portrait.jpg',
        ])->save();
        DoctorProfile::forgetCache();

        $this->get('/en')
            ->assertOk()
            ->assertSee('og:image" content="'.url('/storage/profile/portrait.jpg'), false);
    }

    public function test_a_chamber_photo_appears_on_the_listing_and_the_detail_page(): void
    {
        Chamber::create([
            'slug' => 'insaf', 'name_en' => 'Insaf Barakah', 'is_active' => true,
            'image' => 'chambers/insaf.png',
        ]);

        $this->get('/en/chambers')->assertOk()->assertSee('/storage/chambers/insaf.png', false);
        $this->get('/en/chambers/insaf')->assertOk()->assertSee('/storage/chambers/insaf.png', false);
    }

    public function test_a_service_photo_appears_on_the_listing_and_the_detail_page(): void
    {
        Service::create([
            'slug' => 'spine-surgery', 'name_en' => 'Spine Surgery', 'is_active' => true,
            'image' => 'services/spine.png',
        ]);

        $this->get('/en/expertise')->assertOk()->assertSee('/storage/services/spine.png', false);
        $this->get('/en/expertise/spine-surgery')->assertOk()->assertSee('/storage/services/spine.png', false);
    }

    /** Without a photo the card falls back to its icon rather than a gap. */
    public function test_a_service_without_a_photo_still_shows_its_icon(): void
    {
        Service::create([
            'slug' => 'chronic-pain', 'name_en' => 'Chronic Pain', 'icon' => 'bone', 'is_active' => true,
        ]);

        $this->get('/en/expertise')
            ->assertOk()
            ->assertSee('Chronic Pain')
            ->assertDontSee('/storage/services/', false);
    }

    public function test_a_page_banner_is_displayed(): void
    {
        Page::create([
            'slug' => 'privacy', 'title_en' => 'Privacy', 'is_published' => true,
            'banner_image' => 'pages/banner.jpg',
        ]);

        $this->get('/en/p/privacy')->assertOk()->assertSee('/storage/pages/banner.jpg', false);
    }

    public function test_images_are_shown_in_both_languages(): void
    {
        Service::create([
            'slug' => 'ortho', 'name_en' => 'Orthopaedic Surgery', 'name_bn' => 'অর্থোপেডিক সার্জারি',
            'is_active' => true, 'image' => 'services/ortho.png',
        ]);

        $this->get('/bn/expertise')
            ->assertOk()
            ->assertSee('অর্থোপেডিক সার্জারি')
            ->assertSee('/storage/services/ortho.png', false);
    }
}
