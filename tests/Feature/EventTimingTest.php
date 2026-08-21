<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * An event is over when it ends, not when it starts.
 *
 * The listing split on `event_start_at` alone, so a three-day conference moved
 * to "Past Events" on its opening morning and its registration button closed
 * with two days still to run — while `event_end_at`, which the admin form has
 * always asked for, was read by nothing but a clock label on the detail page.
 */
class EventTimingTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $slug, string $title, ?string $start, ?string $end): Post
    {
        return Post::create([
            'type' => 'event', 'slug' => $slug, 'title_en' => $title,
            'event_start_at' => $start, 'event_end_at' => $end,
            'is_published' => true, 'published_at' => now()->subWeek(),
        ]);
    }

    public function test_an_event_still_running_is_listed_as_upcoming(): void
    {
        $running = $this->event('spine-congress', 'Spine Congress', now()->subDay()->toDateTimeString(), now()->addDays(2)->toDateTimeString());

        $this->assertTrue($running->isUpcoming());
        $this->assertTrue($running->isInProgress());
        $this->assertTrue(Post::query()->upcomingEvents()->whereKey($running->id)->exists());
        $this->assertFalse(Post::query()->pastEvents()->whereKey($running->id)->exists());
    }

    public function test_an_event_that_has_finished_is_listed_as_past(): void
    {
        $done = $this->event('old-camp', 'Old Camp', now()->subDays(5)->toDateTimeString(), now()->subDays(3)->toDateTimeString());

        $this->assertFalse($done->isUpcoming());
        $this->assertTrue(Post::query()->pastEvents()->whereKey($done->id)->exists());
        $this->assertFalse(Post::query()->upcomingEvents()->whereKey($done->id)->exists());
    }

    /** With no end given the start is the end — the behaviour that already existed. */
    public function test_an_event_without_an_end_still_splits_on_its_start(): void
    {
        $soon = $this->event('talk-soon', 'Talk Soon', now()->addDay()->toDateTimeString(), null);
        $gone = $this->event('talk-gone', 'Talk Gone', now()->subDay()->toDateTimeString(), null);

        $this->assertTrue(Post::query()->upcomingEvents()->whereKey($soon->id)->exists());
        $this->assertTrue(Post::query()->pastEvents()->whereKey($gone->id)->exists());
    }

    /** Every event belongs to exactly one of the two lists the page renders. */
    public function test_the_events_page_files_a_running_event_under_upcoming(): void
    {
        $this->event('running-now', 'Running Now', now()->subHours(3)->toDateTimeString(), now()->addDays(2)->toDateTimeString());

        $upcoming = Post::published()->upcomingEvents()->get();
        $past = Post::published()->pastEvents()->get();

        $this->assertCount(1, $upcoming);
        $this->assertCount(0, $past);
        $this->get('/en/events')->assertOk()->assertSee('Running Now');
    }

    /** The closing date has to be said, or a multi-day event reads as one afternoon. */
    public function test_a_multi_day_event_shows_the_date_it_ends(): void
    {
        $this->event(
            'three-days', 'Three Day Congress',
            now()->addDays(10)->setTime(9, 0)->toDateTimeString(),
            now()->addDays(12)->setTime(17, 0)->toDateTimeString(),
        );

        $ends = now()->addDays(12);

        $this->get('/en/events/three-days')
            ->assertOk()
            ->assertSee($ends->format('j').' '.__('site.months.'.$ends->month), false);
    }

    /** Registration must stay open while the event is still on. */
    public function test_registration_stays_open_for_an_event_in_progress(): void
    {
        $this->event('register-now', 'Register Now', now()->subHour()->toDateTimeString(), now()->addDay()->toDateTimeString())
            ->update(['event_registration_url' => 'https://example.test/register']);

        $this->get('/en/events/register-now')
            ->assertOk()
            ->assertSee('https://example.test/register', false);
    }
}
