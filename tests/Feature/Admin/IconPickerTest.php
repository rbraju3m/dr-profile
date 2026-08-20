<?php

namespace Tests\Feature\Admin;

use App\Models\Stat;
use App\Models\User;
use App\Support\Icons;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The icon field used to be free text, so "hero" and "admin" were saved and the
 * public page drew a bare circle where a glyph belonged. Nothing on either side
 * noticed: the admin accepted anything and the component fell back silently.
 */
class IconPickerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_form_offers_the_glyphs_the_site_can_draw(): void
    {
        $this->admin();

        $html = $this->get('/admin/stats/create')->assertOk()->getContent();

        $this->assertStringContainsString('iconPicker(', $html);

        foreach (['stethoscope', 'award', 'heart-pulse'] as $name) {
            $this->assertStringContainsString($name, $html);
        }
    }

    public function test_a_name_the_site_cannot_draw_is_refused(): void
    {
        $this->admin();

        $this->post('/admin/stats', [
            'label_en' => 'Happy patients',
            'value' => 100,
            'icon' => 'hero',
        ])->assertSessionHasErrors('icon');

        $this->assertSame(0, Stat::count());
    }

    public function test_a_real_glyph_is_accepted(): void
    {
        $this->admin();

        $this->post('/admin/stats', [
            'label_en' => 'Happy patients',
            'value' => 100,
            'icon' => 'award',
        ])->assertSessionHasNoErrors();

        $this->assertSame('award', Stat::firstOrFail()->icon);
    }

    /** A row saved before the picker existed should say so rather than sit there. */
    public function test_a_stored_name_that_is_not_a_glyph_is_flagged_on_the_form(): void
    {
        $this->admin();
        $stat = Stat::create(['label_en' => 'Happy patients', 'value' => 100, 'icon' => 'hero']);

        $this->get("/admin/stats/{$stat->getRouteKey()}/edit")->assertOk()
            ->assertSee(__('admin.icons.unknown', ['name' => 'hero']), escape: false);
    }

    /**
     * The picker prints its path data through @push('scripts'), and the admin
     * layout had no @stack for it — so the grid rendered 77 blank squares and
     * looked like an empty box.
     */
    public function test_the_admin_layout_renders_what_a_component_pushes(): void
    {
        $this->admin();

        $this->get('/admin/stats/create')->assertOk()->assertSee('id="icon-paths"', escape: false);
    }

    public function test_every_glyph_the_registry_offers_can_be_drawn(): void
    {
        foreach (Icons::names() as $name) {
            if ($name === Icons::FALLBACK) {
                continue;
            }

            $this->assertNotSame(
                Icons::path(Icons::FALLBACK),
                Icons::path($name),
                "'{$name}' is offered but draws the fallback."
            );
        }

        $this->assertTrue(Icons::has(Icons::FALLBACK), 'the fallback itself must be drawable');
    }
}
