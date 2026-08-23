<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * How this site writes days, dates and clock times, in either language.
 *
 * day_of_week is stored 0 = Sunday .. 6 = Saturday, matching Carbon::dayOfWeek,
 * so the week starts on Sunday the way Bangladeshi clinic schedules are read.
 *
 * Carbon's own format() is not enough on its own: it writes Latin digits and
 * English month names whatever the locale, which is how the admin panel came to
 * show Bangla labels above English dates.
 */
class Week
{
    public const DAYS = [0, 1, 2, 3, 4, 5, 6];

    public static function name(int $day): string
    {
        return __('site.days.'.$day);
    }

    public static function shortName(int $day): string
    {
        return __('site.days_short.'.$day);
    }

    /**
     * "23 August 2026" — localised digits and month names, and optionally the
     * weekday in front of it.
     */
    public static function date(CarbonInterface|string|null $date, bool $withWeekday = false): string
    {
        if (blank($date)) {
            return '';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        $written = Number::localizeDigits($carbon->format('j'))
            .' '.__('site.months.'.$carbon->month)
            .' '.Number::localizeDigits($carbon->format('Y'));

        return $withWeekday ? self::name($carbon->dayOfWeek).', '.$written : $written;
    }

    /**
     * "23 August" — a date whose year the surrounding text already implies, as
     * on a card offering the next free sitting a fortnight out.
     */
    public static function dayMonth(CarbonInterface|string|null $date): string
    {
        if (blank($date)) {
            return '';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return Number::localizeDigits($carbon->format('j')).' '.__('site.months.'.$carbon->month);
    }

    /** "August 2026" — a month, where the day of it is not the point. */
    public static function monthYear(CarbonInterface|string|null $date): string
    {
        if (blank($date)) {
            return '';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return __('site.months.'.$carbon->month).' '.Number::localizeDigits($carbon->format('Y'));
    }

    /** "23 August 2026, 10:30 AM". */
    public static function dateTime(CarbonInterface|string|null $moment): string
    {
        if (blank($moment)) {
            return '';
        }

        $carbon = $moment instanceof CarbonInterface ? $moment : Carbon::parse($moment);

        return self::date($carbon).', '.self::time($carbon);
    }

    /**
     * Format a "HH:MM:SS" column or Carbon instance as a localised clock time.
     *
     * Bangla does not say AM and PM. It names the part of the day and puts it
     * first — সন্ধ্যা ৭টা, not ৭:০০ PM — which is what the FAQ on this site has
     * always said in its own words while every generated time beside it read
     * "৭:০০ PM": Bangla digits with an English meridiem stuck on the end.
     *
     * On the hour takes টা (সন্ধ্যা ৭টা); past it takes the clock (সন্ধ্যা ৭:১৫).
     */
    public static function time(string|CarbonInterface|null $time): string
    {
        if (blank($time)) {
            return '';
        }

        $carbon = $time instanceof CarbonInterface ? $time : Carbon::parse($time);

        if (app()->getLocale() !== 'bn') {
            return $carbon->format('g:i A');
        }

        $minute = (int) $carbon->format('i');

        $clock = $minute === 0
            ? Number::localizeDigits($carbon->format('g')).__('site.oclock')
            : Number::localizeDigits($carbon->format('g:i'));

        return __('site.day_parts.'.self::dayPart((int) $carbon->format('G'))).' '.$clock;
    }

    /**
     * Which word Bangla puts before the hour.
     *
     * The boundaries are the ones the site's own content already uses: its FAQ
     * writes the Mogbazar sitting as "সন্ধ্যা ৭টা থেকে রাত ১০টা", so 7pm is
     * সন্ধ্যা and 10pm is রাত. Midnight and noon fall out correctly — 12am is
     * রাত ১২টা and 12pm is দুপুর ১২টা.
     */
    private static function dayPart(int $hour): string
    {
        return match (true) {
            $hour < 4 => 'night',
            $hour < 6 => 'dawn',
            $hour < 12 => 'morning',
            $hour < 15 => 'noon',
            $hour < 18 => 'afternoon',
            $hour < 20 => 'evening',
            default => 'night',
        };
    }
}
