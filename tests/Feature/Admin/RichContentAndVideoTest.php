<?php

namespace Tests\Feature\Admin;

use App\Models\DoctorProfile;
use App\Models\GalleryItem;
use App\Models\SuccessStory;
use App\Models\User;
use App\Support\VideoEmbed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RichContentAndVideoTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------ video

    /**
     * A watch link is what somebody copies from the address bar, and it cannot
     * be framed. Pasting one used to produce an empty black box.
     */
    public static function videoProvider(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=PNS2JFYP0ws', 'youtube-nocookie.com/embed/PNS2JFYP0ws'],
            'youtube watch with playlist' => ['https://www.youtube.com/watch?v=PNS2JFYP0ws&list=RDPNS2JFYP0ws&start_radio=1', 'youtube-nocookie.com/embed/PNS2JFYP0ws'],
            'youtu.be short link' => ['https://youtu.be/PNS2JFYP0ws', 'youtube-nocookie.com/embed/PNS2JFYP0ws'],
            'youtube shorts' => ['https://www.youtube.com/shorts/PNS2JFYP0ws', 'youtube-nocookie.com/embed/PNS2JFYP0ws'],
            'already an embed' => ['https://www.youtube.com/embed/PNS2JFYP0ws', 'embed/PNS2JFYP0ws'],
            'vimeo' => ['https://vimeo.com/123456789', 'player.vimeo.com/video/123456789'],
            'facebook video' => ['https://www.facebook.com/drshaikhsaadiulislam/videos/1234567890/', 'facebook.com/plugins/video.php'],
            'fb.watch' => ['https://fb.watch/abc123/', 'facebook.com/plugins/video.php'],
        ];
    }

    #[DataProvider('videoProvider')]
    public function test_a_pasted_link_becomes_something_that_can_be_framed(string $pasted, string $expected): void
    {
        $this->assertStringContainsString($expected, VideoEmbed::url($pasted));
    }

    public function test_a_link_that_cannot_be_framed_is_reported_rather_than_guessed(): void
    {
        $this->assertNull(VideoEmbed::url('https://example.com/some-page'));
        $this->assertNull(VideoEmbed::url(''));
        $this->assertFalse(VideoEmbed::isEmbeddable('https://example.com/some-page'));
    }

    public function test_a_story_video_is_embedded_rather_than_linked_raw(): void
    {
        $story = SuccessStory::create([
            'slug' => 'a-recovery', 'title_en' => 'A recovery', 'is_published' => true,
            'video_url' => 'https://www.youtube.com/watch?v=PNS2JFYP0ws&list=RDPNS2JFYP0ws',
        ]);

        $this->get('/en/success-stories/'.$story->slug)
            ->assertOk()
            ->assertSee('youtube-nocookie.com/embed/PNS2JFYP0ws', false)
            ->assertDontSee('<iframe src="https://www.youtube.com/watch', false);
    }

    /** An unframable link gets a button, not an empty black box. */
    public function test_a_story_with_an_unframable_link_offers_it_as_a_link(): void
    {
        $story = SuccessStory::create([
            'slug' => 'elsewhere', 'title_en' => 'Elsewhere', 'is_published' => true,
            'video_url' => 'https://example.com/video-page',
        ]);

        $this->get('/en/success-stories/'.$story->slug)
            ->assertOk()
            ->assertSee(__('site.stories.watch'))
            ->assertDontSee('<iframe', false);
    }

    public function test_the_gallery_uses_the_same_resolver(): void
    {
        $item = new GalleryItem(['type' => 'video', 'video_url' => 'https://youtu.be/PNS2JFYP0ws']);

        $this->assertStringContainsString('youtube-nocookie.com/embed/PNS2JFYP0ws', $item->embedUrl());
        $this->assertStringContainsString('img.youtube.com/vi/PNS2JFYP0ws', $item->thumbnailUrl());
    }

    // ------------------------------------------------------- rich content

    public function test_the_editor_is_offered_for_fields_rendered_as_html(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/profile')
            ->assertOk()
            ->assertSee("richText('bio_en')", false)
            ->assertSee("richText('bio_bn')", false)
            ->assertSee('name="bio_en"', false);
    }

    /** Plain-text fields must not get one, or their markup would show verbatim. */
    public function test_plain_text_fields_keep_a_plain_control(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/profile')
            ->assertOk()
            ->assertDontSee("richText('short_bio_en')", false)
            ->assertDontSee("richText('meta_description_en')", false);
    }

    public function test_html_written_in_the_editor_survives_a_save_and_reaches_the_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $html = '<p>He treats <strong>spine</strong> conditions.</p><ul><li>Disc problems</li></ul>';

        $this->actingAs($admin)->put('/admin/profile', [
            'name_en' => 'Shaikh Saadiul Islam',
            'bio_en' => $html,
        ])->assertSessionHasNoErrors();

        DoctorProfile::forgetCache();

        $this->assertSame($html, DoctorProfile::current()->bio_en);

        $this->get('/en/about')
            ->assertOk()
            ->assertSee('<strong>spine</strong>', false)
            ->assertSee('<li>Disc problems</li>', false);
    }
}
