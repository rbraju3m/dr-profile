<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The header chambers and footer pages are handed to views by a composer
 * registered for the layout, its partials *and* public.*, because slot content
 * renders in the caller's scope. That means it fires six or seven times to draw
 * one page, and it used to run its two queries on every firing.
 *
 * App\Support\SiteNavigation memoises them for the request. These hold the two
 * halves of that being correct: built once, and not one request later.
 */
class SharedNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function seedNavigation(): void
    {
        Chamber::create(['slug' => 'first', 'name_en' => 'First Chamber', 'is_active' => true]);
        Page::create([
            'slug' => 'terms', 'title_en' => 'Terms', 'is_published' => true, 'show_in_footer' => true,
        ]);
    }

    /** One page, one set of queries, however many views ask for them. */
    public function test_the_shared_lists_are_built_once_per_request(): void
    {
        $this->seedNavigation();

        // The FAQ page is used rather than the homepage because its controller
        // fetches no chambers of its own, so anything counted here is the
        // composer's doing and nobody else's.
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/en/faq')->assertOk()->assertSee('First Chamber')->assertSee('Terms');
        $log = collect(DB::getQueryLog())->pluck('query');

        $this->assertSame(1, $log->filter(fn ($q) => str_contains($q, 'from `chambers`'))->count(),
            'the chamber list was fetched more than once for one page');

        $this->assertSame(1, $log->filter(fn ($q) => str_contains($q, 'from `pages`') && str_contains($q, 'show_in_footer'))->count(),
            'the footer page list was fetched more than once for one page');
    }

    /**
     * Memoised for the request, not beyond it. A static property or a scoped
     * container binding would both survive into the next request here and serve
     * a menu that no longer matches the database.
     */
    public function test_the_lists_do_not_survive_into_the_next_request(): void
    {
        $this->get('/en/faq')->assertOk()->assertDontSee('Late Chamber');

        $this->seedNavigation();
        Chamber::create(['slug' => 'late', 'name_en' => 'Late Chamber', 'is_active' => true]);

        $this->get('/en/faq')->assertOk()->assertSee('Late Chamber');
    }
}
