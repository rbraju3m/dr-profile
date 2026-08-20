<?php

namespace App\Support;

/**
 * Bangla renders numerals in its own digit set; English keeps ASCII.
 * Every user-facing number (times, fees, counts, dates) passes through here.
 */
class Number
{
    private const BANGLA_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    public static function localizeDigits(string|int|float|null $value, ?string $locale = null): string
    {
        $value = (string) $value;
        $locale = $locale ?: app()->getLocale();

        if ($locale !== 'bn' || $value === '') {
            return $value;
        }

        return str_replace(range(0, 9), self::BANGLA_DIGITS, $value);
    }

    /** "৳ ১,২০০" / "BDT 1,200" */
    public static function money(int|float|string|null $amount, ?string $locale = null): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $locale = $locale ?: app()->getLocale();
        $formatted = number_format((float) $amount, 0);

        return '৳ '.self::localizeDigits($formatted, $locale);
    }
}
