<?php

namespace Tests\Feature\Admin;

use App\Models\DoctorProfile;
use App\Models\GalleryAlbum;
use App\Models\User;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uploads succeeding is covered elsewhere. This is about them failing.
 *
 * A file that vanishes with "The photo failed to upload." tells the operator
 * nothing they can act on, so each rejection has to name the reason and the
 * limit — and the rules have to match what the server will really accept,
 * rather than advertising a size PHP will refuse.
 */
class UploadFailureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Storage::fake('public');
    }

    // ------------------------------------------------------- the limit itself

    /** Validation must never advertise a size PHP will throw away. */
    public function test_the_accepted_size_never_exceeds_what_php_will_take(): void
    {
        $iniLimit = (int) floor($this->toBytes(ini_get('upload_max_filesize')) / 1024);

        $this->assertLessThanOrEqual(
            $iniLimit,
            Uploads::maxKilobytes(),
            'the app accepts larger files than PHP does, so they disappear with no explanation'
        );

        $this->assertGreaterThan(0, Uploads::maxKilobytes());
    }

    public function test_the_limit_is_stated_in_words_a_person_can_read(): void
    {
        $this->assertMatchesRegularExpression('/^[\d০-৯.,]+ (MB|KB)$/', Uploads::maxLabel());
        $this->assertMatchesRegularExpression('/^[\d০-৯.,]+ MB$/', Uploads::maxPostLabel());
    }

    /** Every upload rule in the admin derives from the server, not a constant. */
    public function test_image_rules_carry_the_server_limit(): void
    {
        $this->assertContains('max:'.Uploads::maxKilobytes(), Uploads::imageRules());
        $this->assertContains('image', Uploads::imageRules());
        $this->assertContains('nullable', Uploads::imageRules());
        $this->assertContains('required', Uploads::imageRules(required: true));
    }

    // ----------------------------------------------------------- the messages

    public function test_an_oversized_image_is_refused_with_the_size_and_the_limit(): void
    {
        $tooBig = Uploads::maxKilobytes() + 512;

        $response = $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Shaikh Saadiul Islam',
            'photo' => UploadedFile::fake()->create('portrait.jpg', $tooBig, 'image/jpeg'),
        ]);

        $response->assertSessionHasErrors('photo');

        $message = session('errors')->first('photo');

        $this->assertStringContainsString(Uploads::maxLabel(), $message, 'the message never states the limit');
        $this->assertStringNotContainsString('kilobytes', $message, 'still using the stock wording');
    }

    public function test_a_non_image_is_refused_by_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Angioplasty',
            'is_active' => 1,
            'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertStringContainsString('image', strtolower(session('errors')->first('image')));
    }

    public function test_a_rejected_upload_leaves_the_existing_photo_alone(): void
    {
        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Shaikh Saadiul Islam',
            'photo' => UploadedFile::fake()->image('good.jpg'),
        ]);

        DoctorProfile::forgetCache();
        $original = DoctorProfile::query()->first()->photo;
        $this->assertNotNull($original);

        // now try to replace it with something too large
        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Shaikh Saadiul Islam',
            'photo' => UploadedFile::fake()->create('huge.jpg', Uploads::maxKilobytes() + 512, 'image/jpeg'),
        ])->assertSessionHasErrors('photo');

        DoctorProfile::forgetCache();

        $this->assertSame($original, DoctorProfile::query()->first()->photo, 'a failed upload destroyed the old photo');
        Storage::disk('public')->assertExists($original);
    }

    public function test_a_rejected_upload_does_not_save_the_rest_of_the_form(): void
    {
        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Correct Name',
        ]);

        $this->actingAs($this->admin)->put('/admin/profile', [
            'name_en' => 'Name That Should Not Stick',
            'photo' => UploadedFile::fake()->create('huge.jpg', Uploads::maxKilobytes() + 512, 'image/jpeg'),
        ])->assertSessionHasErrors('photo');

        DoctorProfile::forgetCache();

        $this->assertSame('Correct Name', DoctorProfile::current()->name_en);
    }

    // ------------------------------------------------------------- the form

    public function test_the_upload_control_states_the_limit_before_a_file_is_chosen(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee(Uploads::maxLabel())
            // the browser-side guard needs the byte figure to compare against
            ->assertSee('max: '.Uploads::maxBytes(), false);
    }

    // ------------------------------------------------------- bulk uploads

    public function test_one_bad_file_does_not_take_a_whole_batch_with_it(): void
    {
        $album = GalleryAlbum::create(['slug' => 'camp', 'title_en' => 'Camp', 'is_active' => true]);

        $this->actingAs($this->admin)->post('/admin/albums/camp/items', [
            'type' => 'image',
            'images' => [
                UploadedFile::fake()->image('fine.jpg'),
                UploadedFile::fake()->create('huge.jpg', Uploads::maxKilobytes() + 512, 'image/jpeg'),
            ],
        ])->assertSessionHasErrors('images.1');

        // Nothing is written when part of the batch is bad, so the operator
        // fixes one file and resubmits rather than hunting for what got through.
        $this->assertSame(0, $album->items()->count());
    }

    // ---------------------------------------------------------- over-limit post

    /**
     * PHP discards a request over post_max_size before any middleware that
     * could flash a message, so the answer is a page that explains itself.
     */
    public function test_an_oversized_request_renders_an_explanatory_page(): void
    {
        // post() takes ($uri, $data, $headers) — a server array has to go
        // through call() or the size check never sees CONTENT_LENGTH.
        $response = $this->actingAs($this->admin)->from('/admin/profile')->call(
            'POST', '/admin/profile', [], [], [],
            ['CONTENT_LENGTH' => (string) (Uploads::maxPostBytes() + 1024)]
        );

        $response->assertStatus(413)
            ->assertSee(__('site.errors.413_title'))
            ->assertSee(Uploads::maxPostLabel());
    }

    public function test_an_oversized_api_style_request_gets_json(): void
    {
        // postJson() computes its own CONTENT_LENGTH, so the header has to be
        // set on a raw call for the size check to see it.
        $response = $this->actingAs($this->admin)->call(
            'POST', '/admin/profile', [], [], [],
            [
                'CONTENT_LENGTH' => (string) (Uploads::maxPostBytes() + 1024),
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        $response->assertStatus(413);
        $this->assertArrayHasKey('message', $response->json());
    }

    private function toBytes(string $value): int
    {
        $unit = strtolower(substr(trim($value), -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
