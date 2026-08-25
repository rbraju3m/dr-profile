<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SuccessStory;
use App\Support\HomeLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A success story can be published without a patient's name, and one on this
 * site is: the name it carried was the word from its own title, pasted through
 * rather than chosen, and naming a patient on a medical story is not something
 * to do by accident.
 *
 * The card and the editorial homepage both printed that name unguarded while
 * guarding the age and the location beside it, so an empty name left a lone
 * user icon and a "·" with nothing before it. `<x-story-meta>` draws the line
 * for both now, separators between what is there rather than beside each field.
 */
class StoryMetaTest extends TestCase
{
    use RefreshDatabase;

    private function story(array $attributes = []): SuccessStory
    {
        return SuccessStory::create($attributes + [
            'slug' => 'walked-again', 'title_en' => 'Walked Again', 'title_bn' => 'আবার হাঁটলেন',
            'summary_en' => 'A short account.', 'is_featured' => true,
            'is_published' => true, 'published_at' => now()->subDay(),
        ]);
    }

    public function test_a_story_with_no_patient_details_draws_no_meta_line(): void
    {
        $this->story();

        $this->get('/en/success-stories')->assertOk()->assertDontSee('story-meta');
        $this->get('/en/success-stories/walked-again')->assertOk();
    }

    /** The separator belongs between two parts, never in front of the first. */
    public function test_a_location_without_a_name_is_not_preceded_by_a_separator(): void
    {
        $this->story(['patient_location_en' => 'Mogbazar']);

        $html = $this->get('/en/success-stories')->assertOk()->assertSee('Mogbazar')->getContent();

        $meta = $this->metaLine($html);

        // Without this the check passes on a card that drew no meta line at all.
        $this->assertNotSame('', $meta, 'The card drew no meta line, so this proves nothing.');
        $this->assertStringNotContainsString('·', $meta, 'The meta line opens with a separator and nothing before it.');
    }

    public function test_the_parts_that_are_there_are_separated(): void
    {
        $this->story(['patient_name_en' => 'A. Karim', 'patient_age' => 40, 'patient_location_en' => 'Mogbazar']);

        $meta = $this->metaLine($this->get('/en/success-stories')->assertOk()->getContent());

        $this->assertSame(2, substr_count($meta, '·'), 'Three parts should be joined by two separators.');
    }

    /** The detail page builds its own list, and it has always guarded each row. */
    public function test_the_detail_page_omits_the_rows_it_has_no_value_for(): void
    {
        $this->story(['patient_location_en' => 'Mogbazar']);

        $this->get('/en/success-stories/walked-again')
            ->assertOk()
            ->assertSee('Mogbazar')
            ->assertDontSee(__('site.booking.patient_age'));
    }

    /** All three designs draw the same band from the same data. */
    public function test_no_homepage_layout_draws_a_meta_line_for_a_nameless_story(): void
    {
        $this->story();

        foreach (HomeLayout::CHOICES as $layout) {
            Setting::updateOrCreate(['key' => 'home_layout'], ['value' => $layout, 'group' => 'general']);
            Setting::forgetCache();

            $this->get('/en')->assertOk()->assertDontSee('story-meta');
        }
    }

    /** Everything between the story's summary and the end of its card. */
    private function metaLine(string $html): string
    {
        if (! preg_match('/<div class="[^"]*story-meta[^"]*".*?<\/div>/s', $html, $m)) {
            return '';
        }

        return $m[0];
    }
}
