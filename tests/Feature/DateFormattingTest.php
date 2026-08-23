<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\Post;
use App\Support\Week;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Carbon's format() writes Latin digits and English month names whatever the
 * locale, so every date on this site is written by App\Support\Week instead.
 *
 * Nine views used to spell that out for themselves —
 * bn_digits(format('j')).' '.__('site.months.'.$m).' '.bn_digits(format('Y')) —
 * which is nine chances for one of them to drift, and is how the admin panel
 * came to show Bangla labels above English dates. The scan below is the half
 * that keeps them from coming back.
 */
class DateFormattingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The booking wizard is the one legitimate reference: it hands the month
     * names to Alpine, which draws the date picker in the browser and so needs
     * the list rather than a formatted date.
     */
    private const MAY_NAME_MONTHS = ['public/appointment/create.blade.php'];

    public function test_no_view_spells_a_date_out_for_itself(): void
    {
        $offenders = [];

        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            if (str_contains($file->getContents(), 'site.months.')) {
                $offenders[] = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
            }
        }

        sort($offenders);

        $this->assertSame(
            self::MAY_NAME_MONTHS,
            $offenders,
            "Views building a date out of site.months.* instead of App\\Support\\Week:\n".implode("\n", $offenders),
        );
    }

    public function test_the_formatters_write_each_shape_in_both_languages(): void
    {
        $moment = Carbon::parse('2026-03-09 17:05:00');

        $this->app->setLocale('en');
        $this->assertSame('9 March 2026', Week::date($moment));
        $this->assertSame('Monday, 9 March 2026', Week::date($moment, withWeekday: true));
        $this->assertSame('9 March', Week::dayMonth($moment));
        $this->assertSame('March 2026', Week::monthYear($moment));
        $this->assertSame('9 March 2026, 5:05 PM', Week::dateTime($moment));

        $this->app->setLocale('bn');
        $this->assertSame('৯ মার্চ ২০২৬', Week::date($moment));
        $this->assertSame('সোমবার, ৯ মার্চ ২০২৬', Week::date($moment, withWeekday: true));
        $this->assertSame('৯ মার্চ', Week::dayMonth($moment));
        $this->assertSame('মার্চ ২০২৬', Week::monthYear($moment));
        $this->assertSame('৯ মার্চ ২০২৬, বিকেল ৫:০৫', Week::dateTime($moment));
    }

    /** Nothing to write is written as nothing, not as a broken date. */
    public function test_a_missing_date_writes_nothing(): void
    {
        foreach ([null, ''] as $empty) {
            $this->assertSame('', Week::date($empty));
            $this->assertSame('', Week::dayMonth($empty));
            $this->assertSame('', Week::monthYear($empty));
            $this->assertSame('', Week::dateTime($empty));
            $this->assertSame('', Week::time($empty));
        }
    }

    /**
     * Bangla has no AM and PM. It names the part of the day and puts it first,
     * and the hour takes টা when nothing follows it. The digits used to
     * localise while the meridiem stayed English, so every generated time read
     * "৭:০০ PM" next to an FAQ that said সন্ধ্যা ৭টা in its own words.
     */
    public function test_bangla_writes_the_part_of_the_day_instead_of_a_meridiem(): void
    {
        $this->app->setLocale('bn');

        $this->assertSame('রাত ১২টা', Week::time('00:00'));      // midnight
        $this->assertSame('ভোর ৪:৩০', Week::time('04:30'));
        $this->assertSame('সকাল ৯টা', Week::time('09:00'));
        $this->assertSame('দুপুর ১২টা', Week::time('12:00'));     // noon
        $this->assertSame('দুপুর ১:৪৫', Week::time('13:45'));
        $this->assertSame('বিকেল ৫টা', Week::time('17:00'));
        $this->assertSame('রাত ১১:৫৯', Week::time('23:59'));

        // The two the site's own FAQ writes by hand, for the Mogbazar sitting.
        $this->assertSame('সন্ধ্যা ৭টা', Week::time('19:00'));
        $this->assertSame('রাত ১০টা', Week::time('22:00'));

        $this->app->setLocale('en');
        $this->assertSame('7:00 PM', Week::time('19:00'));
    }

    /** And the other half: that it reaches the page. */
    public function test_a_public_page_dates_itself_in_the_reader_s_language(): void
    {
        Post::create([
            'slug' => 'a-notice', 'type' => 'news', 'title_en' => 'A Notice',
            'is_published' => true, 'published_at' => Carbon::parse('2026-03-09'),
        ]);

        $this->get('/en/news/a-notice')->assertOk()
            ->assertSee('9 March 2026', escape: false)
            ->assertDontSee('৯ মার্চ ২০২৬', escape: false);

        $this->get('/bn/news/a-notice')->assertOk()
            ->assertSee('৯ মার্চ ২০২৬', escape: false)
            ->assertDontSee('9 March 2026', escape: false);
    }

    /** No Bangla page should print a Latin meridiem anywhere a reader can see. */
    public function test_no_bangla_page_shows_am_or_pm(): void
    {
        $chamber = Chamber::create([
            'slug' => 'mogbazar', 'name_en' => 'Mogbazar', 'name_bn' => 'মগবাজার',
            'is_active' => true, 'accepts_online_booking' => true,
        ]);

        ChamberSchedule::create([
            'chamber_id' => $chamber->id,
            'day_of_week' => Carbon::today()->addDay()->dayOfWeek,
            'start_time' => '19:00', 'end_time' => '22:00',
            'slot_minutes' => 15, 'is_active' => true,
        ]);

        foreach (['/bn/chambers', '/bn/chambers/mogbazar', '/bn/appointment'] as $url) {
            $body = $this->get($url)->assertOk()->getContent();
            $visible = preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $body);

            $this->assertDoesNotMatchRegularExpression(
                '/\d\s*(AM|PM)\b/',
                strip_tags($visible),
                "{$url} prints a Latin meridiem"
            );
        }

        $this->get('/bn/chambers/mogbazar')->assertOk()->assertSee('সন্ধ্যা ৭টা', escape: false);
    }
}
