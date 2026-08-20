<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The forms were written in English — `label="Short bio"` — so the panel spoke
 * Bangla in its menus and tables and English in every field a staff member
 * actually fills in. The labels now come from lang/*, and these tests hold
 * both ends: that none is a plain string, and that every key they name exists.
 */
class FormLabelsTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(string $locale = 'en'): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->withSession(['admin_locale' => $locale]);
    }

    public function test_no_field_label_or_hint_is_written_as_a_plain_string(): void
    {
        $offenders = [];

        foreach (Finder::create()->files()->in(resource_path('views/admin'))->name('*.blade.php') as $file) {
            preg_match_all('/(?<![:\w])(label|hint)="([A-Z][^"]*)"/', $file->getContents(), $matches, PREG_SET_ORDER);

            foreach ($matches as [$whole, $attribute, $text]) {
                $offenders[] = $file->getRelativePathname().": {$attribute}=\"{$text}\"";
            }
        }

        $this->assertSame([], $offenders, "Fields that will not translate:\n".implode("\n", $offenders));
    }

    public static function formProvider(): array
    {
        $forms = [
            'profile' => ['profile'],
            'settings' => ['settings'],
        ];

        foreach ([
            'credentials', 'services', 'chambers', 'exceptions', 'stories', 'post-categories',
            'posts', 'testimonials', 'faqs', 'publications', 'albums', 'pages', 'sliders',
            'stats', 'users',
        ] as $resource) {
            $forms[$resource] = ["{$resource}/create"];
        }

        return $forms;
    }

    /**
     * A key that does not exist does not fail — Laravel prints the key itself,
     * so `admin.fields.short_bio` lands on the page looking like a label. This
     * walks every form in both languages looking for exactly that.
     */
    #[DataProvider('formProvider')]
    public function test_every_key_a_form_names_actually_exists(string $path): void
    {
        foreach (['en', 'bn'] as $locale) {
            $this->signIn($locale);

            $html = $this->get("/admin/{$path}")->assertOk()->getContent();

            foreach (['admin.fields.', 'admin.hints.', 'admin.common.', 'admin.settings.'] as $prefix) {
                $this->assertStringNotContainsString(
                    $prefix,
                    $html,
                    "{$path} in {$locale} shows a raw translation key."
                );
            }
        }
    }

    /** And the half that proves it reaches the reader. */
    public function test_a_form_is_filled_out_in_the_language_the_staff_member_reads(): void
    {
        $this->signIn('bn');

        $this->get('/admin/profile')->assertOk()
            ->assertSee(__('admin.fields.short_bio', [], 'bn'), escape: false)
            ->assertDontSee('Short bio', escape: false);
    }
}
