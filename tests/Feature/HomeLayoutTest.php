<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\Faq;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\User;
use App\Support\Features;
use App\Support\HomeLayout;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Three designs for one homepage.
 *
 * The danger with a second and third layout is the repository's recurring
 * defect wearing a new hat: a band that reaches only one of them, or a
 * visibility switch that one design quietly ignores. So every assertion below
 * runs against all three — what the admin turns off must disappear from the
 * page whichever design is live, and what they turn on must arrive.
 */
class HomeLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public static function layoutProvider(): array
    {
        return array_map(fn (string $layout) => [$layout], array_combine(HomeLayout::CHOICES, HomeLayout::CHOICES));
    }

    private function use(string $layout): void
    {
        Setting::put(HomeLayout::SETTING, $layout, 'appearance', 'text');
    }

    private function turnOff(string $feature): void
    {
        Setting::put(Features::PREFIX.$feature, '0', Features::GROUP, 'boolean');
    }

    /** A site that has never been told which design to use keeps the one it shipped with. */
    public function test_the_homepage_is_classic_until_the_admin_says_otherwise(): void
    {
        $this->assertSame('classic', HomeLayout::current());
        $this->assertSame('public.home.classic', $this->get('/en')->assertOk()->original->name());
    }

    /** A stored value the site cannot draw must not blank the homepage. */
    public function test_an_unknown_design_falls_back_rather_than_failing(): void
    {
        Setting::put(HomeLayout::SETTING, 'broadsheet', 'appearance', 'text');

        $this->assertSame('classic', HomeLayout::current());
        $this->get('/en')->assertOk();
    }

    #[DataProvider('layoutProvider')]
    public function test_each_design_draws_the_homepage_in_both_languages(string $layout): void
    {
        $this->use($layout);

        foreach (['en', 'bn'] as $locale) {
            $response = $this->get("/{$locale}")->assertOk();

            $this->assertSame("public.home.{$layout}", $response->original->name());
            $response->assertDontSee('site.home.', escape: false);   // an untranslated key printing itself
        }
    }

    /**
     * The bands themselves. Every design is fed by the same controller, so a
     * band that reaches one and not another is a hole in that design's markup.
     */
    #[DataProvider('layoutProvider')]
    public function test_every_band_reaches_every_design(string $layout): void
    {
        $this->use($layout);

        $response = $this->get('/en')->assertOk();

        $response->assertSee('heroCarousel(', escape: false);
        $response->assertSee(Stat::active()->ordered()->firstOrFail()->label_en);
        $response->assertSee(Service::active()->where('is_featured', true)->ordered()->firstOrFail()->name_en);
        $response->assertSee(Chamber::active()->ordered()->firstOrFail()->name_en);
        $response->assertSee(__('site.home.step_1_title'), escape: false);
        $response->assertSee(Faq::active()->ordered()->firstOrFail()->question_en);
        $response->assertSee(__('site.home.cta_heading'), escape: false);
    }

    /**
     * The switches. Each of these is a band a visitor would otherwise still
     * meet — the point of the admin screen is that they do not.
     *
     * The markers are chosen to appear in one band only: a service name also
     * labels a success story, and a chamber name also fills the footer.
     */
    #[DataProvider('layoutProvider')]
    public function test_a_band_switched_off_leaves_every_design(string $layout): void
    {
        $this->use($layout);

        $markers = [
            'home_hero' => 'heroCarousel(',
            'home_stats' => Stat::active()->ordered()->firstOrFail()->label_en,
            'home_steps' => __('site.home.step_1_title'),
            'home_faq' => Faq::active()->ordered()->firstOrFail()->question_en,
            'home_cta' => __('site.home.cta_heading'),
        ];

        foreach ($markers as $feature => $marker) {
            $this->get('/en')->assertOk()->assertSee($marker);

            $this->turnOff($feature);

            $this->get('/en')->assertOk()->assertDontSee($marker);
        }
    }

    /** Hiding a whole section must not leave the homepage linking into it. */
    #[DataProvider('layoutProvider')]
    public function test_no_design_links_into_a_hidden_section(string $layout): void
    {
        $this->use($layout);

        foreach (['services' => '/en/expertise', 'chambers' => '/en/chambers', 'stories' => '/en/success-stories', 'faq' => '/en/faq'] as $feature => $path) {
            $this->get('/en')->assertOk()->assertSee($path, escape: false);

            $this->turnOff($feature);

            $this->get('/en')->assertOk()->assertDontSee('"'.$path, escape: false);
        }
    }

    /** The admin's choice has to be the one the visitor gets. */
    public function test_the_admin_screen_saves_the_design_and_the_site_follows(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/visibility')->assertOk()
            ->assertSee(__('admin.visibility.layout.title'), escape: false)
            ->assertSee(__('admin.visibility.layout.editorial'), escape: false);

        $this->actingAs($admin)
            ->put('/admin/visibility', [
                'features' => collect(Features::keys())->mapWithKeys(fn (string $key) => [$key => '1'])->all(),
                'theme_default' => 'light',
                'home_layout' => 'editorial',
            ])
            ->assertRedirect('/admin/visibility');

        $this->assertSame('editorial', HomeLayout::current());
        $this->assertSame('public.home.editorial', $this->get('/en')->assertOk()->original->name());
    }

    /** And a design the site cannot draw must not be storable. */
    public function test_the_admin_refuses_a_design_that_does_not_exist(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->put('/admin/visibility', [
                'features' => collect(Features::keys())->mapWithKeys(fn (string $key) => [$key => '1'])->all(),
                'theme_default' => 'light',
                'home_layout' => 'broadsheet',
            ])
            ->assertSessionHasErrors('home_layout');

        $this->assertSame('classic', HomeLayout::current());
    }
}
