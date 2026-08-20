<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dragging a row and flicking a switch both write to the database from a
 * single gesture, with no form and no confirmation, so both need holding to
 * the same standards as a form post.
 */
class ListControlsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function services(int $count = 3): array
    {
        return collect(range(1, $count))->map(fn ($i) => Service::create([
            'slug' => "service-{$i}", 'name_en' => "Service {$i}",
            'is_active' => true, 'sort_order' => $i - 1,
        ]))->all();
    }

    // ------------------------------------------------------------ reordering

    public function test_dragging_rows_stores_the_new_order(): void
    {
        [$a, $b, $c] = $this->services();

        $this->actingAs($this->admin)
            ->postJson('/admin/services/reorder', ['ids' => [$c->id, $a->id, $b->id]])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(
            ['Service 3', 'Service 1', 'Service 2'],
            Service::orderBy('sort_order')->pluck('name_en')->all()
        );
    }

    /** The order the admin chose is the order patients see. */
    public function test_the_new_order_reaches_the_public_page(): void
    {
        [$a, , $c] = $this->services();

        $this->actingAs($this->admin)
            ->postJson('/admin/services/reorder', ['ids' => [$c->id, $a->id]]);

        $html = $this->get('/en/expertise')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Service 1'),
            strpos($html, 'Service 3'),
            'the drag order was ignored on the public listing'
        );
    }

    public function test_ids_from_another_resource_are_ignored(): void
    {
        [$a, $b] = $this->services(2);
        $faq = Faq::create(['group' => 'general', 'question_en' => 'Q', 'answer_en' => 'A', 'sort_order' => 99]);

        $this->actingAs($this->admin)
            ->postJson('/admin/services/reorder', ['ids' => [$faq->id + 10000, $b->id, $a->id]])
            ->assertOk();

        // The unknown id is skipped; the real rows still take their positions.
        $this->assertSame(99, $faq->fresh()->sort_order);
        $this->assertSame(['Service 2', 'Service 1'], Service::orderBy('sort_order')->pluck('name_en')->all());
    }

    public function test_reordering_requires_a_signed_in_user(): void
    {
        [$a, $b] = $this->services(2);

        $this->postJson('/admin/services/reorder', ['ids' => [$b->id, $a->id]])->assertUnauthorized();
    }

    public function test_reordering_validates_its_input(): void
    {
        $this->actingAs($this->admin)->postJson('/admin/services/reorder', [])->assertStatus(422);
        $this->actingAs($this->admin)->postJson('/admin/services/reorder', ['ids' => 'nope'])->assertStatus(422);
    }

    // -------------------------------------------------------------- switches

    public function test_a_switch_flips_the_flag(): void
    {
        [$service] = $this->services(1);

        $this->actingAs($this->admin)
            ->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'is_active'])
            ->assertOk()
            ->assertJsonPath('value', false);

        $this->assertFalse($service->fresh()->is_active);

        $this->actingAs($this->admin)
            ->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'is_active'])
            ->assertJsonPath('value', true);
    }

    /** Switching a service off must remove it from the site. */
    public function test_switching_something_off_hides_it_from_patients(): void
    {
        [$service] = $this->services(1);

        $this->get('/en/expertise')->assertOk()->assertSee('Service 1');

        $this->actingAs($this->admin)
            ->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'is_active']);

        $this->get('/en/expertise')->assertOk()->assertDontSee('Service 1');
    }

    /** Otherwise the endpoint would set any column on any row. */
    public function test_only_whitelisted_flags_can_be_switched(): void
    {
        [$service] = $this->services(1);

        $this->actingAs($this->admin)
            ->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'name_en'])
            ->assertStatus(422);

        $this->actingAs($this->admin)
            ->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'slug'])
            ->assertStatus(422);

        $this->assertSame('Service 1', $service->fresh()->name_en);
    }

    public function test_a_flag_the_model_does_not_have_is_refused(): void
    {
        [$service] = $this->services(1);

        // services carry no show_in_footer column
        $this->actingAs($this->admin)
            ->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'show_in_footer'])
            ->assertNotFound();
    }

    public function test_switching_requires_a_signed_in_user(): void
    {
        [$service] = $this->services(1);

        $this->postJson('/admin/services/'.$service->slug.'/toggle', ['column' => 'is_active'])
            ->assertUnauthorized();

        $this->assertTrue($service->fresh()->is_active);
    }

    public function test_the_listing_renders_a_switch_for_each_flag(): void
    {
        $this->services(2);

        $this->actingAs($this->admin)
            ->get('/admin/services')
            ->assertOk()
            ->assertSee('aria-pressed', false)
            ->assertSee('toggleSwitch(', false);
    }
}
