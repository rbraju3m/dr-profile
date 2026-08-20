<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    /** 404s render before routing, so the locale must still resolve. */
    public function test_the_404_page_renders_in_the_url_locale(): void
    {
        $this->get('/en/nothing-here')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('<html lang="en"', false);

        $this->get('/bn/nothing-here')
            ->assertNotFound()
            ->assertSee('পৃষ্ঠাটি পাওয়া যায়নি')
            ->assertSee('<html lang="bn"', false);
    }

    public function test_a_404_outside_any_locale_still_renders(): void
    {
        $this->get('/totally-unknown')->assertNotFound()->assertSee('Page not found');
    }

    public function test_the_sitemap_lists_both_locales(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('/en/about', false)
            ->assertSee('/bn/about', false);
    }
}
