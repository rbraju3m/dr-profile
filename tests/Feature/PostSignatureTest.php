<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `posts:signature` exists to carry one content fix to installs that only get
 * the code — the posts live in the database and not in `content/doctor.yml`, so
 * `doctor:import` cannot reach them.
 *
 * The trap it has to avoid is its own second run. A pasted post is plain text
 * with line breaks; once rewritten it is a single line of HTML, and a command
 * that only knew how to read the first shape would find the name in that one
 * line, treat the whole post as signature, and leave nothing but the signature
 * behind. That is tested here before anything else.
 */
class PostSignatureTest extends TestCase
{
    use RefreshDatabase;

    private const PASTED = "Is your lower back pain spreading down your leg? Take care lifting.\n"
        ."Dr. Shaikh Saadiul Islam\n"
        ."MBBS(DMC),BCS(H),FCPS(Ortho)\n"
        .'Assistant Professor (Spine Surgery), NITOR';

    protected function setUp(): void
    {
        parent::setUp();

        DoctorProfile::query()->delete();
        DoctorProfile::create(['name_en' => 'Shaikh Saadiul Islam', 'title_en' => 'Dr.']);
        DoctorProfile::forgetCache();
    }

    private function signedPost(array $attributes = []): Post
    {
        // $attributes first: with `+` the left-hand key wins, so defaults on
        // that side would quietly ignore every override.
        return Post::create($attributes + [
            'slug' => 'lower-back-pain', 'type' => 'news',
            'title_en' => 'Is your lower back pain spreading down your leg?',
            'content_en' => self::PASTED, 'content_bn' => self::PASTED,
            'is_published' => true, 'published_at' => now()->subDay(),
        ]);
    }

    public function test_it_restates_the_designation_and_keeps_the_post_s_own_words(): void
    {
        $post = $this->signedPost();

        $this->artisan('posts:signature')->assertSuccessful();

        $content = $post->fresh()->content_en;

        $this->assertStringContainsString('Resident Surgeon (R.S.), NITOR', $content);
        $this->assertStringNotContainsString('Assistant Professor', $content);
        $this->assertStringContainsString('Is your lower back pain spreading down your leg?', $content);
    }

    /** Rendered with {!! !!}, so a line break is not a paragraph. */
    public function test_it_leaves_the_post_as_paragraphs(): void
    {
        $post = $this->signedPost();

        $this->artisan('posts:signature')->assertSuccessful();

        $this->assertStringStartsWith('<p>Is your lower back pain', $post->fresh()->content_en);
        $this->assertStringEndsWith('<p>Resident Surgeon (R.S.), NITOR</p>', $post->fresh()->content_en);
    }

    /** Both languages, or the Bangla page keeps the designation he does not hold. */
    public function test_it_restates_both_languages(): void
    {
        $post = $this->signedPost();

        $this->artisan('posts:signature')->assertSuccessful();

        $this->assertStringNotContainsString('Assistant Professor', $post->fresh()->content_bn);
    }

    /**
     * The one that matters: the second run reads HTML, not line breaks. Read
     * wrongly, the whole post is one line, the name is in it, and the body goes.
     */
    public function test_running_it_again_changes_nothing(): void
    {
        $post = $this->signedPost();

        $this->artisan('posts:signature')->assertSuccessful();
        $once = $post->fresh()->content_en;

        $this->artisan('posts:signature')->assertSuccessful();

        $this->assertSame($once, $post->fresh()->content_en);
        $this->assertStringContainsString('Is your lower back pain spreading down your leg?', $post->fresh()->content_en);
    }

    public function test_a_post_with_no_signature_is_left_alone(): void
    {
        $post = $this->signedPost(['slug' => 'unsigned', 'content_en' => 'A post nobody signed.', 'content_bn' => '']);

        $this->artisan('posts:signature')->assertSuccessful();

        $this->assertSame('A post nobody signed.', $post->fresh()->content_en);
    }

    public function test_a_dry_run_writes_nothing(): void
    {
        $post = $this->signedPost();

        $this->artisan('posts:signature', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(self::PASTED, $post->fresh()->content_en);
    }
}
