<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin has always allowed several hero slides with a sort order. It used
 * to render only the first one, so these assert every slide reaches the page
 * and that the controls appear only when there is something to control.
 */
class HeroCarouselTest extends TestCase
{
    use RefreshDatabase;

    private function slide(int $order, string $title, array $extra = []): Slider
    {
        return Slider::create(array_merge([
            'title_en' => $title,
            'title_bn' => $title.' (bn)',
            'subtitle_en' => $title.' subtitle',
            'image' => '',
            'sort_order' => $order,
            'is_active' => true,
        ], $extra));
    }

    public function test_every_active_slide_is_rendered(): void
    {
        $this->slide(0, 'First slide');
        $this->slide(1, 'Second slide');
        $this->slide(2, 'Third slide');

        $this->get('/en')
            ->assertOk()
            ->assertSee('heroCarousel(3)', false)
            ->assertSee('First slide')
            ->assertSee('Second slide')
            ->assertSee('Third slide');
    }

    public function test_slides_appear_in_their_sort_order(): void
    {
        $this->slide(2, 'Last slide');
        $this->slide(0, 'Opening slide');

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Last slide'),
            strpos($html, 'Opening slide'),
            'slides ignored their sort order'
        );
    }

    public function test_an_inactive_slide_is_left_out(): void
    {
        $this->slide(0, 'Shown slide');
        $this->slide(1, 'Hidden slide', ['is_active' => false]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Shown slide')
            ->assertDontSee('Hidden slide')
            ->assertSee('heroCarousel(1)', false);
    }

    public function test_controls_are_hidden_when_there_is_only_one_slide(): void
    {
        $this->slide(0, 'Only slide');

        $this->get('/en')->assertOk()->assertDontSee('role="tablist"', false);
    }

    public function test_controls_appear_once_there_is_more_than_one(): void
    {
        $this->slide(0, 'First slide');
        $this->slide(1, 'Second slide');

        $this->get('/en')->assertOk()->assertSee('role="tablist"', false);
    }

    /** A phone should not download the wide crop. */
    public function test_a_mobile_crop_is_offered_as_a_picture_source(): void
    {
        $this->slide(0, 'With images', [
            'image' => 'sliders/desktop.jpg',
            'mobile_image' => 'sliders/mobile.jpg',
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('media="(max-width: 640px)"', false)
            ->assertSee('sliders/mobile.jpg', false)
            ->assertSee('sliders/desktop.jpg', false);
    }

    public function test_no_source_element_when_there_is_no_separate_mobile_crop(): void
    {
        $this->slide(0, 'Desktop only', ['image' => 'sliders/desktop.jpg']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('sliders/desktop.jpg', false)
            ->assertDontSee('media="(max-width: 640px)"', false);
    }

    public function test_slides_are_translated(): void
    {
        $this->slide(0, 'English headline');

        $this->get('/bn')->assertOk()->assertSee('English headline (bn)');
    }

    /** With no slides configured the hero still has to say something. */
    public function test_the_hero_falls_back_to_the_profile_when_there_are_no_slides(): void
    {
        DoctorProfile::query()->firstOrNew([])->fill([
            'name_en' => 'Shaikh Saadiul Islam',
            'title_en' => 'Dr.',
            'tagline_en' => 'A tagline from the profile',
        ])->save();
        DoctorProfile::forgetCache();

        $this->get('/en')
            ->assertOk()
            ->assertSee('A tagline from the profile')
            ->assertSee('heroCarousel(1)', false)
            ->assertDontSee('role="tablist"', false);
    }
}
