<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Listing columns are declared in PHP, where it costs nothing to write the
 * header as a plain English string — and twenty of them were, so the admin
 * stayed half-English however it was switched. This is the same defect the
 * repository keeps meeting: something that looks bilingual and is not.
 */
class ListingLabelsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $locale = 'en'): User
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)->withSession(['admin_locale' => $locale]);

        return $user;
    }

    public function test_no_column_header_is_written_as_a_plain_string(): void
    {
        $offenders = [];

        foreach (Finder::create()->files()->in(app_path('Http/Controllers/Admin'))->name('*.php') as $file) {
            preg_match_all("/'label' => '([^']*)'/", $file->getContents(), $matches);

            foreach ($matches[1] as $label) {
                $offenders[] = $file->getFilename().": “{$label}”";
            }
        }

        $this->assertSame([], $offenders, "Column headers that will not translate:\n".implode("\n", $offenders));
    }

    /** The other half: that the translation actually reaches the table. */
    public function test_headers_are_rendered_in_the_language_the_staff_member_picked(): void
    {
        $this->admin('bn');

        $this->get('/admin/users')->assertOk()
            ->assertSee(__('admin.common.role', [], 'bn'), escape: false)
            ->assertDontSee('Last login', escape: false);
    }

    /**
     * The posts table headed two adjacent columns "Published" — the date and
     * the switch — which reads as a mistake in either language. No table
     * should name two of its columns the same thing.
     */
    public function test_no_table_heads_two_columns_with_the_same_name(): void
    {
        $this->admin();
        Post::create([
            'slug' => 'a-notice',
            'type' => 'news',
            'title_en' => 'A notice',
            'is_published' => true,
            'published_at' => now(),
        ]);

        foreach (['posts', 'users', 'chambers', 'publications', 'credentials', 'pages'] as $resource) {
            $html = $this->get("/admin/{$resource}")->assertOk()->getContent();

            preg_match('#<thead.*?</thead>#s', $html, $head);
            preg_match_all('#<th[^>]*>(.*?)</th>#s', $head[0] ?? '', $cells);

            $headers = array_values(array_filter(array_map(
                fn (string $cell) => trim(strip_tags($cell)),
                $cells[1]
            )));

            $this->assertSame(
                array_unique($headers),
                $headers,
                "The {$resource} table heads two columns the same: ".implode(', ', $headers)
            );
        }
    }
}
