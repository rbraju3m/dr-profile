<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\SuccessStory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Walks every public page in both languages against the seeded demo content.
 */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public static function localeProvider(): array
    {
        return ['english' => ['en'], 'bangla' => ['bn']];
    }

    #[DataProvider('localeProvider')]
    public function test_every_listing_page_renders(string $locale): void
    {
        $paths = [
            '', '/about', '/expertise', '/chambers', '/appointment', '/appointment/lookup',
            '/success-stories', '/news', '/events', '/health-tips', '/gallery',
            '/publications', '/faq', '/contact',
        ];

        foreach ($paths as $path) {
            $this->get("/{$locale}{$path}")->assertOk();
        }
    }

    #[DataProvider('localeProvider')]
    public function test_every_detail_page_renders(string $locale): void
    {
        $records = [
            '/expertise/' => Service::first(),
            '/chambers/' => Chamber::first(),
            '/success-stories/' => SuccessStory::first(),
            '/news/' => Post::news()->first(),
            '/events/' => Post::events()->first(),
            '/health-tips/' => Post::blog()->first(),
            '/gallery/' => GalleryAlbum::first(),
            '/p/' => Page::first(),
        ];

        foreach ($records as $prefix => $record) {
            $this->assertNotNull($record, "seeder produced no record for {$prefix}");
            $this->get("/{$locale}{$prefix}{$record->slug}")->assertOk();
        }
    }

    /** A blog slug under /news must 404 rather than render the wrong section. */
    public function test_a_post_is_not_reachable_under_the_wrong_type(): void
    {
        $blog = Post::blog()->first();

        $this->get('/en/news/'.$blog->slug)->assertNotFound();
        $this->get('/en/health-tips/'.$blog->slug)->assertOk();
    }

    public function test_unpublished_content_is_hidden(): void
    {
        $story = SuccessStory::first();
        $story->update(['is_published' => false]);

        $this->get('/en/success-stories/'.$story->slug)->assertNotFound();
    }

    public function test_an_inactive_service_is_hidden(): void
    {
        $service = Service::first();
        $service->update(['is_active' => false]);

        $this->get('/en/expertise/'.$service->slug)->assertNotFound();
    }

    /**
     * A service without a long description still has a hero, a picture, a
     * booking button and its siblings. The six of them shared one description
     * — the doctor's biography — and clearing it left an empty prose block
     * holding the page open.
     */
    public function test_a_service_with_no_description_renders_without_an_empty_prose_block(): void
    {
        $service = Service::first();
        $service->update(['description_en' => null, 'description_bn' => null]);

        foreach (['en' => $service->name_en, 'bn' => $service->name_bn] as $locale => $name) {
            $this->get("/{$locale}/expertise/".$service->slug)
                ->assertOk()
                ->assertSee($name)
                ->assertDontSee('prose-content', escape: false);
        }
    }

    public function test_the_home_page_shows_the_doctor_and_a_booking_call_to_action(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('Ayesha Rahman')
            ->assertSee('Book an Appointment');

        $this->get('/bn')
            ->assertOk()
            ->assertSee('আয়েশা রহমান')
            ->assertSee('অ্যাপয়েন্টমেন্ট নিন');
    }

    public function test_a_missing_page_returns_404(): void
    {
        $this->get('/en/expertise/does-not-exist')->assertNotFound();
        $this->get('/en/nothing-here')->assertNotFound();
    }
}
