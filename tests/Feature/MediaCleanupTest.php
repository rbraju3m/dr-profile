<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ResourceController;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A stored file and the row pointing at it are one thing.
 *
 * Deleting the file used to be `ResourceController::destroy()`'s job alone, so
 * a row leaving any other way — tinker, a seeder, a console command, a database
 * cascade — left its file behind for good: nothing points at it any more, so
 * nothing will ever find it to clean up. `GalleryItem` was given a hook when an
 * album cascade did exactly that, and the other eleven models were left as they
 * were until the three political posts had to be deleted by hand around it.
 *
 * The registry is checked from both ends, as the feature switches are: a column
 * declared that the table does not have deletes nothing and says so never, and
 * an upload the admin accepts on a column no model declares is a file that
 * outlives every row that referenced it.
 */
class MediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /** @return array<int, class-string<Model>> */
    private function models(): array
    {
        return collect(glob(app_path('Models/*.php')))
            ->map(fn ($file) => 'App\\Models\\'.basename($file, '.php'))
            ->filter(fn ($class) => method_exists($class, 'mediaColumns'))
            ->values()->all();
    }

    public function test_every_declared_media_column_exists_on_its_table(): void
    {
        foreach ($this->models() as $class) {
            $model = new $class;

            foreach ($model->mediaColumns() as $column) {
                $this->assertTrue(
                    Schema::hasColumn($model->getTable(), $column),
                    "{$class} declares {$column} as a media column, but the table has no such column."
                );
            }
        }
    }

    /**
     * The other end: an admin resource accepts an upload into a column, so the
     * model behind it has to know that column holds a file.
     */
    public function test_every_uploadable_column_is_declared_on_its_model(): void
    {
        foreach (glob(app_path('Http/Controllers/Admin/*.php')) as $file) {
            $class = 'App\\Http\\Controllers\\Admin\\'.basename($file, '.php');

            if (! is_subclass_of($class, ResourceController::class)) {
                continue;
            }

            $controller = new \ReflectionClass($class);
            $fields = $controller->getDefaultProperties()['mediaFields'] ?? [];
            $model = $controller->getDefaultProperties()['model'] ?? null;

            if (! $fields || ! $model) {
                continue;
            }

            $declared = (new $model)->mediaColumns();

            foreach (array_keys($fields) as $field) {
                $this->assertContains(
                    $field, $declared,
                    "{$class} uploads into {$field}, but {$model} does not declare it, so the file survives the row."
                );
            }
        }
    }

    public function test_deleting_a_row_deletes_its_file(): void
    {
        Storage::disk('public')->put('posts/leaflet.png', 'x');

        $post = Post::create([
            'slug' => 'leaflet', 'type' => 'news', 'title_en' => 'Leaflet',
            'image' => 'posts/leaflet.png', 'is_published' => false,
        ]);

        $post->delete();

        $this->assertFalse(Storage::disk('public')->exists('posts/leaflet.png'));
    }

    /** More than one column, and every one of them goes. */
    public function test_a_row_with_two_files_takes_both(): void
    {
        Storage::disk('public')->put('sliders/wide.png', 'x');
        Storage::disk('public')->put('sliders/narrow.png', 'x');

        Slider::create([
            'title_en' => 'Welcome', 'image' => 'sliders/wide.png',
            'mobile_image' => 'sliders/narrow.png', 'is_active' => true,
        ])->delete();

        $this->assertFalse(Storage::disk('public')->exists('sliders/wide.png'));
        $this->assertFalse(Storage::disk('public')->exists('sliders/narrow.png'));
    }

    /** The case that started it: a cascade never loads the rows it removes. */
    public function test_deleting_an_album_takes_its_items_files_too(): void
    {
        Storage::disk('public')->put('albums/cover.png', 'x');
        Storage::disk('public')->put('gallery/inside.png', 'x');

        $album = GalleryAlbum::create([
            'slug' => 'camp', 'title_en' => 'Free Camp',
            'cover_image' => 'albums/cover.png', 'is_active' => true,
        ]);

        GalleryItem::create([
            'gallery_album_id' => $album->id, 'type' => 'image',
            'image' => 'gallery/inside.png', 'is_active' => true,
        ]);

        $album->delete();

        $this->assertFalse(Storage::disk('public')->exists('albums/cover.png'));
        $this->assertFalse(Storage::disk('public')->exists('gallery/inside.png'));
    }

    /** The admin panel is one caller of the same thing now, not a second copy. */
    public function test_deleting_through_the_admin_panel_still_removes_the_file(): void
    {
        Storage::disk('public')->put('posts/panel.png', 'x');

        $post = Post::create([
            'slug' => 'panel', 'type' => 'news', 'title_en' => 'Panel',
            'image' => 'posts/panel.png', 'is_published' => false,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->delete('/admin/posts/'.$post->slug)->assertRedirect();

        $this->assertFalse(Storage::disk('public')->exists('posts/panel.png'));
        $this->assertNull(Post::find($post->id));
    }

    /** A row with no file must not throw on the way out. */
    public function test_a_row_with_no_file_deletes_cleanly(): void
    {
        $post = Post::create([
            'slug' => 'plain', 'type' => 'news', 'title_en' => 'Plain', 'is_published' => false,
        ]);

        $post->delete();

        $this->assertNull(Post::find($post->id));
    }
}
