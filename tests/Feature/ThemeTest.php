<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\Features;
use App\Support\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Light and dark. The admin picks what the site is served in; the reader may
 * overrule it for themselves only while the header switch is on.
 */
class ThemeTest extends TestCase
{
    use RefreshDatabase;

    private function setDefault(string $theme): void
    {
        Setting::put(Theme::SETTING, $theme, 'appearance', 'text');
    }

    private function switchOff(string $feature): void
    {
        Setting::put(Features::PREFIX.$feature, '0', Features::GROUP, 'boolean');
    }

    public function test_a_site_with_no_setting_is_light(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString('class="dark"', $html);
        $this->assertStringContainsString('content="light"', $html);
    }

    public function test_the_admin_default_reaches_the_page(): void
    {
        $this->setDefault('dark');

        $this->get('/en')->assertOk()
            ->assertSee('class="dark"', escape: false)
            ->assertSee('content="dark"', escape: false);
    }

    /**
     * "Follow the device" cannot be decided on the server, so the class is left
     * off and the inline script settles it before the first paint.
     */
    public function test_following_the_device_is_left_to_the_browser(): void
    {
        $this->setDefault('system');

        $this->get('/en')->assertOk()
            ->assertDontSee('class="dark"', escape: false)
            ->assertSee('content="light dark"', escape: false)
            ->assertSee('prefers-color-scheme: dark', escape: false);
    }

    public function test_a_reader_can_overrule_the_default(): void
    {
        $this->setDefault('light');

        $this->withUnencryptedCookie('theme', 'dark')
            ->get('/en')->assertOk()->assertSee('class="dark"', escape: false);
    }

    /** With the switch off the choice belongs to the admin, cookie or not. */
    public function test_the_cookie_is_ignored_when_the_switch_is_off(): void
    {
        $this->setDefault('light');
        $this->switchOff('theme_toggle');

        $this->withUnencryptedCookie('theme', 'dark')
            ->get('/en')->assertOk()->assertDontSee('class="dark"', escape: false);
    }

    public function test_the_switch_is_shown_only_while_it_is_on(): void
    {
        $this->get('/en')->assertOk()->assertSee(__('site.theme.to_dark'), escape: false);

        $this->switchOff('theme_toggle');

        $this->get('/en')->assertOk()->assertDontSee(__('site.theme.to_dark'), escape: false);
    }

    public function test_the_admin_panel_follows_the_same_theme(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->setDefault('dark');

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('class="dark"', escape: false);
    }

    /**
     * Staff keep their own choice whatever the public site offers — the switch
     * governs what visitors are given, not what the back office looks like.
     */
    public function test_staff_keep_their_choice_even_with_the_public_switch_off(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->setDefault('light');
        $this->switchOff('theme_toggle');

        $this->actingAs($admin)
            ->withUnencryptedCookie('theme', 'dark')
            ->get('/admin')->assertOk()->assertSee('class="dark"', escape: false);
    }

    public function test_the_default_is_saved_from_the_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/visibility')->assertOk()
            ->assertSee(__('admin.visibility.appearance.title'), escape: false);

        $this->actingAs($admin)
            ->put('/admin/visibility', [
                'features' => collect(Features::keys())->mapWithKeys(fn ($k) => [$k => '1'])->all(),
                'theme_default' => 'dark',
            ])
            ->assertRedirect('/admin/visibility');

        $this->assertSame('dark', Theme::default());
    }

    public function test_a_theme_that_is_not_offered_is_refused(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->put('/admin/visibility', ['theme_default' => 'neon'])
            ->assertSessionHasErrors('theme_default');

        $this->assertSame('light', Theme::default());
    }
}
