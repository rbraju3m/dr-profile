<?php

namespace Tests\Feature;

use App\Models\GalleryAlbum;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The lightbox's own controls sit on top of its backdrop, so whatever closes it
 * must not fire for a click on the arrows: closing on any click *outside the
 * picture* swallowed the previous and next buttons, and the viewer shut instead
 * of moving on. Guards the arrangement from both sides — the closer is scoped to
 * the backdrop itself, and the arrows are still there to be clicked.
 */
class GalleryLightboxTest extends TestCase
{
    use RefreshDatabase;

    private function album(): GalleryAlbum
    {
        $this->seed(DatabaseSeeder::class);

        return GalleryAlbum::has('items')->firstOrFail();
    }

    public function test_the_arrows_are_not_inside_the_region_that_closes_the_lightbox(): void
    {
        $html = $this->get(route('gallery.show', ['locale' => 'en', 'album' => $this->album()]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('click.outside', $html,
            'A lightbox that closes on any click outside the picture also closes on its own arrows.');

        $this->assertStringContainsString('@click.self="open = false"', $html,
            'Only a click on the backdrop itself should close the lightbox.');
    }

    public function test_the_lightbox_still_offers_a_way_out_and_a_way_through(): void
    {
        $html = $this->get(route('gallery.show', ['locale' => 'en', 'album' => $this->album()]))
            ->assertOk()
            ->getContent();

        foreach ([__('site.actions.previous'), __('site.actions.next'), __('site.nav.close_menu')] as $label) {
            $this->assertStringContainsString('aria-label="'.$label.'"', $html);
        }

        $this->assertStringContainsString('index = (index + 1) % items.length', $html);
        $this->assertStringContainsString('index = (index - 1 + items.length) % items.length', $html);
    }

    /**
     * Gallery material is the practice's own posters — a Bangla headline across
     * the top and the doctor down one side. Post and story cards letterbox for
     * exactly this reason; the gallery cropped, and took the headline off both
     * the album cover and every tile inside it.
     */
    public function test_gallery_pictures_are_letterboxed_rather_than_cropped(): void
    {
        $album = $this->album();

        foreach ([
            route('gallery.index', ['locale' => 'en']),
            route('gallery.show', ['locale' => 'en', 'album' => $album]),
        ] as $url) {
            $this->assertStringContainsString('object-contain', $this->get($url)->assertOk()->getContent(),
                "A poster centre-cropped into a tile loses the headline that is the point of it: {$url}");
        }
    }
}
