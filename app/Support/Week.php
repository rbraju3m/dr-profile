<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Day-of-week and time formatting helpers.
 *
 * day_of_week is stored 0 = Sunday .. 6 = Saturday, matching Carbon::dayOfWeek,
 * so the week starts on Sunday the way Bangladeshi clinic schedules are read.
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

    /** Format a "HH:MM:SS" column or Carbon instance as a localised clock time. */
    public static function time(string|CarbonInterface|null $time): string
    {
        if (blank($time)) {
            return '';
        }

        $carbon = $time instanceof CarbonInterface ? $time : Carbon::parse($time);
        $formatted = $carbon->format('g:i A');

        return Number::localizeDigits($formatted);
    }
}
