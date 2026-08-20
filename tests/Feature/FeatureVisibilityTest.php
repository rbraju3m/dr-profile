<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SuccessStory;
use App\Models\User;
use App\Support\Features;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The admin can hide any part of the public site. These tests hold the two
 * halves of that promise together: a switch that is off must remove the
 * section *and* its links, and a switch that is on must leave the site exactly
 * as it was.
 *
 * This is the same defect the repository keeps meeting from the other side —
 * a control in the admin that changes nothing on the page.
 */
class FeatureVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function turnOff(string ...$keys): void
    {
        foreach ($keys as $key) {
            Setting::put(Features::PREFIX.$key, '0', Features::GROUP, 'boolean');
        }
    }

    /** Every public section, its listing URL and the link that reaches it. */
    public static function sectionProvider(): array
    {
        return [
            'about' => ['about', '/en/about'],
            'expertise' => ['services', '/en/expertise'],
            'chambers' => ['chambers', '/en/chambers'],
            'booking' => ['appointment', '/en/appointment'],
            'stories' => ['stories', '/en/success-stories'],
            'news' => ['news', '/en/news'],
            'events' => ['events', '/en/events'],
            'health tips' => ['blog', '/en/health-tips'],
            'gallery' => ['gallery', '/en/gallery'],
            'publications' => ['publications', '/en/publications'],
            'faq' => ['faq', '/en/faq'],
            'search' => ['search', '/en/search'],
            'contact' => ['contact', '/en/contact'],
        ];
    }

    #[DataProvider('sectionProvider')]
    public function test_a_section_is_reachable_and_linked_while_it_is_on(string $feature, string $path): void
    {
        $this->get($path)->assertOk();
        $this->get('/en')->assertOk()->assertSee($path, escape: false);
    }

    #[DataProvider('sectionProvider')]
    public function test_a_section_that_is_off_stops_answering(string $feature, string $path): void
    {
        $this->turnOff($feature);

        $this->get($path)->assertNotFound();
    }

    /**
     * The failure that matters most: the page 404s but the menu still points
     * at it, so the visitor is sent to a dead end from the homepage.
     */
    #[DataProvider('sectionProvider')]
    public function test_a_section_that_is_off_is_not_linked_from_anywhere(string $feature, string $path): void
    {
        $this->turnOff($feature);

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString('"'.$path.'"', $html);
        $this->assertStringNotContainsString('"'.$path.'/', $html);
    }

    #[DataProvider('sectionProvider')]
    public function test_a_section_that_is_off_leaves_the_sitemap(string $feature, string $path): void
    {
        // The search page is deliberately not advertised to crawlers.
        if ($feature === 'search') {
            $this->markTestSkipped('The sitemap does not list the search page.');
        }

        $this->get('/sitemap.xml')->assertOk()->assertSee(url($path), escape: false);

        $this->turnOff($feature);

        $this->get('/sitemap.xml')->assertOk()->assertDontSee(url($path), escape: false);
    }

    /** Detail URLs go with their listing, not only the listing itself. */
    public function test_detail_pages_close_with_their_section(): void
    {
        $story = SuccessStory::published()->firstOrFail();
        $this->get("/en/success-stories/{$story->slug}")->assertOk();

        $this->turnOff('stories');

        $this->get("/en/success-stories/{$story->slug}")->assertNotFound();
    }

    public function test_a_hidden_section_drops_out_of_site_search(): void
    {
        $service = Service::active()->firstOrFail();
        $term = Str::of($service->name_en)->explode(' ')->first();
        // Built with an explicit locale: outside a request there is no default.
        $link = route('services.show', ['locale' => 'en', 'service' => $service]);

        $this->get('/en/search?q='.urlencode($term))->assertOk()->assertSee($link, escape: false);

        $this->turnOff('services');

        $this->get('/en/search?q='.urlencode($term))->assertOk()->assertDontSee($link, escape: false);
    }

    public function test_homepage_bands_can_be_hidden_one_at_a_time(): void
    {
        $this->get('/en')->assertOk()
            ->assertSee(__('site.home.steps_heading'), escape: false)
            ->assertSee(__('site.home.cta_heading'), escape: false);

        $this->turnOff('home_steps', 'home_cta');

        $this->get('/en')->assertOk()
            ->assertDontSee(__('site.home.steps_heading'), escape: false)
            ->assertDontSee(__('site.home.cta_heading'), escape: false);
    }

    /**
     * A band that lists rows from a section takes that section's switch with
     * it — otherwise the homepage would keep offering cards whose detail pages
     * have stopped answering.
     */
    public function test_a_homepage_band_follows_the_page_it_links_to(): void
    {
        $this->assertTrue(Features::enabled('home_services'));

        $this->turnOff('services');

        $this->assertFalse(Features::enabled('home_services'));
        $this->get('/en')->assertOk()->assertDontSee(__('site.home.expertise_heading'), escape: false);
    }

    /**
     * Booking is offered from more places than any other section — the header,
     * the mobile drawer, the fixed bar on phones, the hero, the closing CTA,
     * every chamber card and the sidebar of the chamber, service, story and
     * post pages, plus the error pages. Switching it off has to clear all of
     * them, so this walks the whole site rather than trusting a list.
     *
     * Only page bodies are scanned: the doctor's meta description is prose he
     * writes himself, and if it mentions booking that is his to edit.
     */
    public function test_booking_leaves_every_page_when_it_is_off(): void
    {
        $this->turnOff('appointment');

        $phrases = [
            __('site.actions.book_appointment'),
            __('site.actions.book_now'),
            __('site.nav.appointment'),
        ];

        $leaks = [];

        foreach (['en', 'bn'] as $locale) {
            foreach ($this->everyPublicPath() as $path) {
                // The last path is a deliberate miss: the 404 page offers booking too.
                $body = Str::after($this->get("/{$locale}{$path}")->getContent(), '<body');

                if (str_contains($body, "/{$locale}/appointment")) {
                    $leaks[] = "{$locale}{$path} still links to booking";
                }

                foreach (array_unique($phrases) as $phrase) {
                    if (str_contains($body, $phrase)) {
                        $leaks[] = "{$locale}{$path} still shows “{$phrase}”";
                    }
                }
            }
        }

        $this->assertSame([], $leaks, "Booking is still on offer:\n".implode("\n", $leaks));
    }

    /**
     * The booking sweep above, generalised: with a section off, no page a
     * visitor can still reach may link into it. Checking links rather than
     * wording is what makes this reliable — "News" appears in prose, but
     * href="/en/news" is always a way in.
     */
    #[DataProvider('sectionProvider')]
    public function test_no_reachable_page_links_into_a_hidden_section(string $feature, string $path): void
    {
        $this->turnOff($feature);

        $leaks = [];

        foreach (['en', 'bn'] as $locale) {
            $target = "/{$locale}{$path}";

            foreach ($this->everyPublicPath() as $page) {
                $body = Str::after($this->get("/{$locale}{$page}")->getContent(), '<body');

                foreach (['"'.$target.'"', '"'.$target.'/', '"'.$target.'?'] as $needle) {
                    if (str_contains($body, $needle)) {
                        $leaks[] = "{$locale}{$page} links to {$target}";
                        break;
                    }
                }
            }
        }

        $this->assertSame([], $leaks, "Links into a hidden section:\n".implode("\n", $leaks));
    }

    /** One URL of every shape a visitor can reach, listings and details alike. */
    private function everyPublicPath(): array
    {
        $post = Post::published()->firstOrFail();
        $section = match ($post->type) {
            'blog' => 'health-tips',
            'event' => 'events',
            default => 'news',
        };

        return [
            '', '/about',
            '/expertise', '/expertise/'.Service::active()->firstOrFail()->slug,
            '/chambers', '/chambers/'.Chamber::active()->firstOrFail()->slug,
            '/success-stories', '/success-stories/'.SuccessStory::published()->firstOrFail()->slug,
            '/news', '/events', '/health-tips', "/{$section}/{$post->slug}",
            '/gallery', '/gallery/'.GalleryAlbum::active()->firstOrFail()->slug,
            '/publications', '/faq', '/contact', '/search?q=heart',
            '/p/'.Page::published()->firstOrFail()->slug,
            '/no-such-page',
        ];
    }

    /** One homepage band carries two switchable sections; each must obey its own. */
    public function test_the_news_band_stops_listing_events_when_events_are_off(): void
    {
        $event = Post::published()->where('type', 'event')->firstOrFail();
        $event->update(['published_at' => now()]);

        $this->get('/en')->assertOk()->assertSee('/en/events/'.$event->slug, escape: false);

        $this->turnOff('events');

        $this->get('/en')->assertOk()->assertDontSee('/en/events/'.$event->slug, escape: false);
    }

    public function test_header_and_footer_furniture_can_be_hidden(): void
    {
        $this->get('/en')->assertOk()->assertSee(__('site.footer.disclaimer'), escape: false);

        $this->turnOff('footer_disclaimer');

        $this->get('/en')->assertOk()->assertDontSee(__('site.footer.disclaimer'), escape: false);
    }

    public function test_the_admin_screen_saves_every_switch(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/visibility')->assertOk()
            ->assertSee(__('admin.visibility.features.gallery'), escape: false);

        // The form posts the whole set — a box left unticked arrives as "0"
        // from the hidden field beside it, not as a missing key.
        $posted = collect(Features::keys())->mapWithKeys(fn (string $key) => [$key => '1'])->all();
        $posted['gallery'] = '0';

        $this->actingAs($admin)
            ->put('/admin/visibility', ['features' => $posted, 'theme_default' => 'light'])
            ->assertRedirect('/admin/visibility');

        $this->assertFalse(Features::enabled('gallery'));
        $this->assertTrue(Features::enabled('faq'));
    }

    public function test_only_registered_switches_can_be_written(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $siteName = Setting::get('site_name_en');

        $this->actingAs($admin)
            ->put('/admin/visibility', ['features' => ['site_name_en' => '0', 'gallery' => '1'], 'theme_default' => 'light'])
            ->assertRedirect();

        // Neither a switch of its own, nor a way at the real setting.
        $this->assertNull(Setting::get(Features::PREFIX.'site_name_en'));
        $this->assertSame($siteName, Setting::get('site_name_en'));
    }

    public function test_staff_who_are_not_admins_cannot_reach_the_screen(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);

        $this->actingAs($editor)->get('/admin/visibility')->assertForbidden();
    }
}
