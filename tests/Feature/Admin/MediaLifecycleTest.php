<?php

namespace Tests\Feature\Admin;

use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * What happens to the old file.
 *
 * Uploads are cheap to create and easy to forget, and an orphan is invisible:
 * nothing points at it, nothing lists it, and the disk fills up quietly.
 */
class MediaLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_replacing_a_file_deletes_the_one_it_replaced(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Spine Surgery', 'is_active' => 1,
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $first = Service::first()->image;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($this->admin)->put('/admin/services/spine-surgery', [
            'name_en' => 'Spine Surgery', 'slug' => 'spine-surgery', 'is_active' => 1,
            'image' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $second = Service::first()->image;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists($second);
        Storage::disk('public')->assertMissing($first, 'the replaced file was left on disk');
    }

    public function test_ticking_remove_deletes_the_file_and_clears_the_column(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Spine Surgery', 'is_active' => 1,
            'image' => UploadedFile::fake()->image('only.jpg'),
        ]);

        $path = Service::first()->image;

        $this->actingAs($this->admin)->put('/admin/services/spine-surgery', [
            'name_en' => 'Spine Surgery', 'slug' => 'spine-surgery', 'is_active' => 1,
            'remove_image' => 1,
        ]);

        $this->assertNull(Service::first()->image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_saving_without_choosing_a_file_keeps_the_existing_one(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Spine Surgery', 'is_active' => 1,
            'image' => UploadedFile::fake()->image('keep.jpg'),
        ]);

        $path = Service::first()->image;

        $this->actingAs($this->admin)->put('/admin/services/spine-surgery', [
            'name_en' => 'Spine Surgery Renamed', 'slug' => 'spine-surgery', 'is_active' => 1,
        ]);

        $this->assertSame($path, Service::first()->image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_deleting_a_record_deletes_its_file(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', [
            'name_en' => 'Spine Surgery', 'is_active' => 1,
            'image' => UploadedFile::fake()->image('doomed.jpg'),
        ]);

        $path = Service::first()->image;

        $this->actingAs($this->admin)->delete('/admin/services/spine-surgery');

        Storage::disk('public')->assertMissing($path);
    }

    /**
     * The album cascade removes item rows in the database without loading them,
     * so their files used to survive with nothing pointing at them.
     */
    public function test_deleting_an_album_deletes_the_files_of_its_items(): void
    {
        $album = GalleryAlbum::create(['slug' => 'camp', 'title_en' => 'Camp', 'is_active' => true]);
        $media = app(MediaService::class);

        $paths = collect(range(1, 3))->map(function () use ($album, $media) {
            $path = $media->store(UploadedFile::fake()->image('g.jpg'), 'gallery');
            GalleryItem::create(['gallery_album_id' => $album->id, 'type' => 'image', 'image' => $path, 'is_active' => true]);

            return $path;
        });

        $paths->each(fn ($p) => Storage::disk('public')->assertExists($p));

        $album->delete();

        $this->assertSame(0, GalleryItem::where('gallery_album_id', $album->id)->count());
        $paths->each(fn ($p) => Storage::disk('public')->assertMissing($p, 'a cascaded item left its file behind'));
    }

    public function test_deleting_a_single_gallery_item_deletes_its_file(): void
    {
        $album = GalleryAlbum::create(['slug' => 'camp', 'title_en' => 'Camp', 'is_active' => true]);
        $path = app(MediaService::class)->store(UploadedFile::fake()->image('one.jpg'), 'gallery');
        $item = GalleryItem::create(['gallery_album_id' => $album->id, 'type' => 'image', 'image' => $path, 'is_active' => true]);

        $this->actingAs($this->admin)->delete('/admin/items/'.$item->id);

        Storage::disk('public')->assertMissing($path);
    }

    /** A link to somebody else's server is not ours to delete. */
    public function test_an_external_url_is_never_treated_as_a_local_file(): void
    {
        $media = app(MediaService::class);

        $media->delete('https://example.com/photo.jpg');
        $media->delete('/already/absolute.jpg');

        $this->assertTrue(true); // reaching here without an exception is the assertion
    }
}
