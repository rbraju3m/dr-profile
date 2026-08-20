<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The header and footer each used to keep their own hardcoded list of networks,
 * which is how Instagram became editable but absent from the header, and X
 * editable and shown nowhere. Both now read DoctorProfile::socialLinks(), and
 * these tests exist to keep it that way.
 */
class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    private const ALL = [
        'facebook_url' => 'https://facebook.com/example',
        'instagram_url' => 'https://instagram.com/example',
        'youtube_url' => 'https://youtube.com/@example',
        'tiktok_url' => 'https://tiktok.com/@example',
        'linkedin_url' => 'https://linkedin.com/company/example',
        'x_url' => 'https://x.com/example',
    ];

    private function profile(array $links = self::ALL): DoctorProfile
    {
        DoctorProfile::query()->firstOrNew([])->fill(['name_en' => 'Shaikh Saadiul Islam'] + $links)->save();
        DoctorProfile::forgetCache();

        return DoctorProfile::current();
    }

    public function test_every_editable_network_is_returned(): void
    {
        $networks = collect($this->profile()->socialLinks())->pluck('network')->all();

        foreach (array_keys(self::ALL) as $field) {
            $this->assertContains(str_replace('_url', '', $field), $networks);
        }
    }

    public function test_networks_that_are_blank_are_left_out(): void
    {
        $links = $this->profile(['facebook_url' => 'https://facebook.com/example'])->socialLinks();

        $this->assertCount(1, $links);
        $this->assertSame('facebook', $links[0]['network']);
    }

    /** The defect this replaced: a field editable in the admin but shown nowhere. */
    public function test_every_network_the_admin_offers_reaches_the_public_page(): void
    {
        $this->profile();

        $html = $this->get('/en')->assertOk()->getContent();

        foreach (self::ALL as $field => $url) {
            $this->assertStringContainsString($url, $html, "[{$field}] is editable but never rendered");
        }
    }

    public function test_the_header_and_the_footer_agree(): void
    {
        $this->profile();
        $html = $this->get('/en')->assertOk()->getContent();

        [, $afterHeader] = explode('<main', $html, 2);
        [$header] = explode('<main', $html, 2);

        foreach (['facebook.com', 'instagram.com', 'tiktok.com', 'linkedin.com'] as $host) {
            $this->assertStringContainsString($host, $header, "{$host} missing from the header");
            $this->assertStringContainsString($host, $afterHeader, "{$host} missing from the footer");
        }
    }

    public function test_links_open_in_a_new_tab_without_leaking_the_referrer(): void
    {
        $this->profile(['facebook_url' => 'https://facebook.com/example']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_the_admin_form_offers_every_network(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $response = $this->actingAs($admin)->get('/admin/profile')->assertOk();

        foreach (array_keys(self::ALL) as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }
    }

    public function test_a_new_network_saves_from_the_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->put('/admin/profile', [
            'name_en' => 'Shaikh Saadiul Islam',
            'tiktok_url' => 'https://www.tiktok.com/@drshaikhsaadiulislam',
        ])->assertRedirect('/admin/profile')->assertSessionHasNoErrors();

        DoctorProfile::forgetCache();

        $this->assertSame(
            'https://www.tiktok.com/@drshaikhsaadiulislam',
            DoctorProfile::current()->tiktok_url
        );
    }

    public function test_a_malformed_url_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->put('/admin/profile', [
            'name_en' => 'Shaikh Saadiul Islam',
            'tiktok_url' => 'not-a-url',
        ])->assertSessionHasErrors('tiktok_url');
    }
}
