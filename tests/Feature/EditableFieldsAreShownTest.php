<?php

namespace Tests\Feature;

use App\Models\Credential;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\Publication;
use App\Models\Service;
use App\Models\SuccessStory;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The same rule `UploadedImagesAreShownTest` holds for uploads, held for the
 * text the admin types.
 *
 * A field on an admin form that saves and is never printed is invisible work:
 * the operator fills it in, sees nothing change, and cannot tell a bug from a
 * decision. Every case below was one — a credential's location, a publication's
 * abstract, the date a patient visited — collected for as long as the table has
 * existed and rendered on no page a visitor could reach.
 */
class EditableFieldsAreShownTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_credentials_location_appears_on_the_about_page(): void
    {
        Credential::create([
            'type' => 'education', 'title_en' => 'MBBS', 'organization_en' => 'Dhaka Medical College',
            'location_en' => 'Dhaka, Bangladesh', 'start_year' => 2004, 'end_year' => 2010, 'is_active' => true,
        ]);

        $this->get('/en/about')
            ->assertOk()
            ->assertSee('Dhaka Medical College')
            ->assertSee('Dhaka, Bangladesh');
    }

    public function test_a_publications_abstract_appears_on_the_publications_page(): void
    {
        Publication::create([
            'type' => 'journal', 'title_en' => 'Lumbar Fusion Outcomes', 'year' => 2023,
            'abstract_en' => 'A five-year review of fusion outcomes in a Dhaka cohort.',
            'is_active' => true,
        ]);

        $this->get('/en/publications')
            ->assertOk()
            ->assertSee('A five-year review of fusion outcomes in a Dhaka cohort.');
    }

    /** It is also how a reader finds one — the search covered English only. */
    public function test_a_publication_is_searchable_by_its_bangla_venue(): void
    {
        Publication::create([
            'type' => 'journal', 'title_en' => 'Spine Registry', 'venue_en' => 'Spine Journal',
            'venue_bn' => 'মেরুদণ্ড জার্নাল', 'year' => 2022, 'is_active' => true,
        ]);

        $this->get('/bn/search?q='.urlencode('মেরুদণ্ড জার্নাল'))
            ->assertOk()
            ->assertSee('Spine Registry');
    }

    public function test_a_testimonials_visit_date_is_shown_with_the_quote(): void
    {
        Testimonial::create([
            'patient_name_en' => 'A. Karim', 'content_en' => 'Walked out the same week.',
            'visited_on' => '2025-03-14', 'rating' => 5, 'is_published' => true,
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Walked out the same week.')
            ->assertSee('March 2025');
    }

    /** A cover is the album's shop window; a switched-off photo must not be in it. */
    public function test_a_deactivated_photo_is_never_used_as_an_album_cover(): void
    {
        $album = GalleryAlbum::create(['slug' => 'camp', 'title_en' => 'Free Camp', 'is_active' => true]);

        GalleryItem::create([
            'gallery_album_id' => $album->id, 'type' => 'image', 'sort_order' => 1,
            'image' => 'gallery/withdrawn.png', 'is_active' => false,
        ]);
        GalleryItem::create([
            'gallery_album_id' => $album->id, 'type' => 'image', 'sort_order' => 2,
            'image' => 'gallery/published.png', 'is_active' => true,
        ]);

        $this->get('/en/gallery')
            ->assertOk()
            ->assertSee('/storage/gallery/published.png', false)
            ->assertDontSee('/storage/gallery/withdrawn.png', false);
    }

    /** A filter that returns nothing is a dead end wearing a chip. */
    public function test_the_story_filter_only_offers_services_with_a_published_story(): void
    {
        $listed = Service::create(['slug' => 'spine', 'name_en' => 'Spine Surgery', 'is_active' => true]);
        $hidden = Service::create(['slug' => 'knee', 'name_en' => 'Knee Replacement', 'is_active' => true]);

        SuccessStory::create([
            'slug' => 'walked-again', 'title_en' => 'Walked Again', 'service_id' => $listed->id,
            'is_published' => true, 'published_at' => now()->subDay(),
        ]);
        SuccessStory::create([
            'slug' => 'still-a-draft', 'title_en' => 'Still A Draft', 'service_id' => $hidden->id,
            'is_published' => false,
        ]);

        $this->get('/en/success-stories')
            ->assertOk()
            ->assertSee('Spine Surgery')
            ->assertDontSee('Knee Replacement');
    }
}
