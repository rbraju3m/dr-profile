<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The switcher's job is to move the visitor between locales without moving them
 * off the page they are reading. These tests pin that behaviour down, because
 * it is easy to break: the link is built by rewriting one segment of the
 * current URL, so any route whose path or query happens to resemble a locale
 * code is a candidate for going wrong.
 */
class LanguageSwitcherTest extends TestCase
{
    use RefreshDatabase;

    /** Pull the switcher's link for one locale out of the rendered page. */
    private function switchLink(string $html, string $locale): ?string
    {
        preg_match('/<a href="([^"]+)" hreflang="'.$locale.'"/', $html, $m);

        return isset($m[1]) ? html_entity_decode($m[1]) : null;
    }

    public function test_it_offers_a_link_to_every_configured_locale(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        foreach (array_keys(config('site.locales')) as $locale) {
            $this->assertNotNull(
                $this->switchLink($html, $locale),
                "no switcher link rendered for [{$locale}]"
            );
        }
    }

    public function test_it_shows_each_language_in_its_own_script(): void
    {
        $this->get('/en')->assertOk()->assertSee('বাংলা');
        $this->get('/bn')->assertOk()->assertSee('English');
    }

    public function test_switching_from_the_home_page_lands_on_the_other_home_page(): void
    {
        $html = $this->get('/en')->getContent();

        $this->assertSame(url('/bn'), $this->switchLink($html, 'bn'));
    }

    /** The whole point: you stay on the page you were reading. */
    public function test_switching_keeps_the_visitor_on_the_same_page(): void
    {
        Service::create(['slug' => 'echocardiography', 'name_en' => 'Echocardiography', 'is_active' => true]);

        $html = $this->get('/en/expertise/echocardiography')->assertOk()->getContent();

        $this->assertSame(url('/bn/expertise/echocardiography'), $this->switchLink($html, 'bn'));
    }

    public function test_switching_preserves_the_query_string(): void
    {
        $html = $this->get('/en/news?q=heart&category=announcements')->assertOk()->getContent();
        $link = $this->switchLink($html, 'bn');

        $this->assertStringStartsWith(url('/bn/news').'?', $link);
        $this->assertStringContainsString('q=heart', $link);
        $this->assertStringContainsString('category=announcements', $link);
    }

    public function test_switching_preserves_the_page_number(): void
    {
        foreach (range(1, 12) as $i) {
            Post::create([
                'slug' => "article-{$i}",
                'type' => 'blog',
                'title_en' => "Article {$i}",
                'is_published' => true,
                'published_at' => now()->subDays($i),
            ]);
        }

        $html = $this->get('/en/health-tips?page=2')->assertOk()->getContent();

        $this->assertStringContainsString('page=2', $this->switchLink($html, 'bn'));
    }

    /**
     * The link is built by rewriting the first "/xx/" it finds. A slug that
     * begins with a locale code must not be rewritten instead of the prefix.
     */
    public function test_a_slug_that_looks_like_a_locale_is_not_rewritten(): void
    {
        Service::create(['slug' => 'en', 'name_en' => 'Endoscopy', 'is_active' => true]);

        $html = $this->get('/en/expertise/en')->assertOk()->getContent();

        $this->assertSame(url('/bn/expertise/en'), $this->switchLink($html, 'bn'));
    }

    public function test_the_link_for_the_current_locale_points_at_the_current_page(): void
    {
        $html = $this->get('/bn/contact')->assertOk()->getContent();

        $this->assertSame(url('/bn/contact'), $this->switchLink($html, 'bn'));
    }

    public function test_following_the_link_actually_switches_language(): void
    {
        $html = $this->get('/en/contact')->getContent();
        $link = $this->switchLink($html, 'bn');

        $this->get($link)
            ->assertOk()
            ->assertSee('<html lang="bn"', false)
            ->assertSee('বার্তা পাঠান');
    }

    /** Visiting a locale remembers it, so the bare domain sends you back there. */
    public function test_the_chosen_locale_is_remembered_for_the_root_url(): void
    {
        $this->get('/en')->assertOk();
        $this->get('/')->assertRedirect('/en');

        $this->get('/bn')->assertOk();
        $this->get('/')->assertRedirect('/bn');
    }

    public function test_the_admin_switcher_changes_the_panel_language(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Dashboard');

        $this->actingAs($admin)
            ->from('/admin')
            ->post('/admin/language', ['locale' => 'bn'])
            ->assertRedirect('/admin');

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('ড্যাশবোর্ড');
    }

    public function test_the_admin_switcher_ignores_an_unknown_locale(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->from('/admin')->post('/admin/language', ['locale' => 'bn']);
        $this->actingAs($admin)->from('/admin')->post('/admin/language', ['locale' => 'fr']);

        // Still Bangla — the bogus value was discarded rather than applied.
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('ড্যাশবোর্ড');
    }
}
