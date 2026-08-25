<?php

namespace Tests\Feature\Admin;

use App\Models\Chamber;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_it_creates_a_service_with_both_languages_and_a_generated_slug(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Cardiac Rehabilitation',
            'name_bn' => 'কার্ডিয়াক পুনর্বাসন',
            'short_description_en' => 'Structured recovery after a cardiac event.',
            'is_active' => 1,
            'is_featured' => 0,
            'sort_order' => 3,
        ])->assertRedirect('/admin/services');

        $service = Service::first();

        $this->assertSame('cardiac-rehabilitation', $service->slug);
        $this->assertSame('কার্ডিয়াক পুনর্বাসন', $service->name_bn);
        $this->assertTrue($service->is_active);
    }

    public function test_duplicate_titles_get_distinct_slugs(): void
    {
        foreach (range(1, 2) as $ignored) {
            $this->actingAs($this->admin)->post('/admin/services', [
                'name_en' => 'Stress Test',
                'is_active' => 1,
            ]);
        }

        $this->assertSame(['stress-test', 'stress-test-2'], Service::orderBy('id')->pluck('slug')->all());
    }

    public function test_it_updates_a_service(): void
    {
        $service = Service::create(['slug' => 'echo', 'name_en' => 'Echo', 'is_active' => true]);

        $this->actingAs($this->admin)->put('/admin/services/echo', [
            'name_en' => 'Echocardiography',
            'name_bn' => 'ইকোকার্ডিওগ্রাফি',
            'slug' => 'echo',
            'is_active' => 1,
        ])->assertRedirect('/admin/services');

        $this->assertSame('Echocardiography', $service->fresh()->name_en);
    }

    public function test_it_deletes_a_service(): void
    {
        Service::create(['slug' => 'echo', 'name_en' => 'Echo', 'is_active' => true]);

        $this->actingAs($this->admin)->delete('/admin/services/echo')->assertRedirect('/admin/services');

        $this->assertSame(0, Service::count());
    }

    public function test_validation_errors_are_returned(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/services', ['name_en' => ''])
            ->assertSessionHasErrors('name_en');

        $this->assertSame(0, Service::count());
    }

    public function test_it_stores_an_uploaded_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Angioplasty',
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('procedure.jpg', 800, 600),
        ])->assertRedirect('/admin/services');

        $service = Service::first();

        $this->assertNotNull($service->image);
        Storage::disk('public')->assertExists($service->image);
    }

    /** Event-only columns must be cleared when the post is not an event. */
    public function test_event_fields_are_cleared_for_non_event_posts(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'type' => 'news',
            'title_en' => 'Clinic reopens',
            'event_start_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'event_venue_en' => 'Auditorium',
            'is_published' => 1,
        ])->assertRedirect('/admin/posts');

        $post = Post::first();

        $this->assertSame('news', $post->type);
        $this->assertNull($post->event_start_at);
        $this->assertNull($post->event_venue_en);
    }

    public function test_an_event_requires_a_start_time(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'type' => 'event',
            'title_en' => 'Heart camp',
            'is_published' => 1,
        ])->assertSessionHasErrors('event_start_at');
    }

    public function test_tags_are_stored_as_a_list(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'type' => 'blog',
            'title_en' => 'Salt and your heart',
            'tags' => 'salt, diet , heart',
            'is_published' => 1,
        ]);

        $this->assertSame(['salt', 'diet', 'heart'], Post::first()->tags);
    }

    public function test_the_post_author_is_stamped_once(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'type' => 'news',
            'title_en' => 'A new wing opens',
            'is_published' => 1,
        ]);

        $post = Post::first();
        $this->assertSame($this->admin->id, $post->author_id);

        $other = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($other)->put('/admin/posts/'.$post->slug, [
            'type' => 'news',
            'title_en' => 'A new wing opens today',
            'slug' => $post->slug,
            'is_published' => 1,
        ]);

        $this->assertSame($this->admin->id, $post->fresh()->author_id);
    }

    public function test_overlapping_sittings_are_rejected(): void
    {
        $chamber = Chamber::create([
            'slug' => 'main', 'name_en' => 'Main', 'is_active' => true, 'accepts_online_booking' => true,
        ]);

        $this->actingAs($this->admin)->post('/admin/chambers/main/schedules', [
            'day_of_week' => 1, 'start_time' => '10:00', 'end_time' => '13:00', 'slot_minutes' => 20,
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post('/admin/chambers/main/schedules', [
            'day_of_week' => 1, 'start_time' => '12:00', 'end_time' => '14:00', 'slot_minutes' => 20,
        ])->assertSessionHasErrors('start_time');

        $this->assertSame(1, $chamber->schedules()->count());
    }

    /**
     * The name a patient is credited by is content like any other: the form has
     * to offer both halves, and the edit page has to give them back. It offered
     * one box, so a Bengali speaker's name was filed as the English one.
     */
    public function test_a_patient_name_is_kept_in_both_languages(): void
    {
        $this->actingAs($this->admin)->post('/admin/testimonials', [
            'patient_name_en' => 'Nasima Khatun',
            'patient_name_bn' => 'নাসিমা খাতুন',
            'content_en' => 'He told me which test could wait and which could not.',
            'rating' => 5,
            'is_published' => 1,
        ])->assertRedirect('/admin/testimonials');

        $testimonial = Testimonial::first();

        $this->assertSame('নাসিমা খাতুন', $testimonial->patient_name_bn);

        $this->actingAs($this->admin)->get('/admin/testimonials/'.$testimonial->id.'/edit')
            ->assertOk()
            ->assertSee('name="patient_name_en"', false)
            ->assertSee('name="patient_name_bn"', false)
            ->assertSee('নাসিমা খাতুন', escape: false);
    }

    public function test_the_last_admin_cannot_be_deleted(): void
    {
        $other = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->actingAs($this->admin)
            ->delete('/admin/users/'.$other->id)
            ->assertRedirect();

        // The only *active* admin is the one signed in, and self-deletion is blocked.
        $this->actingAs($this->admin)->delete('/admin/users/'.$this->admin->id);

        $this->assertNotNull(User::find($this->admin->id));
    }
}
