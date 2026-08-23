<?php

namespace App\Support;

/**
 * One place that decides whether two mobile numbers belong to the same person.
 *
 * A Bangladeshi number is written three ways — 01712345678, 8801712345678 and
 * +8801712345678 — and patients use all three. The last nine digits are what
 * the three forms agree on, so that is what any comparison is made against.
 *
 * Cancelling already worked this way. The open-appointment limit did not: it
 * compared the raw string, so one person could hold three times the allowance
 * simply by typing their own number a different way each time.
 */
final class Phone
{
    /** How many trailing digits two numbers must share to be the same line. */
    public const KEY_LENGTH = 9;

    /** Just the digits — no +, spaces or dashes. */
    public static function digits(?string $phone): string
    {
        return preg_replace('/\D/', '', (string) $phone);
    }

    /**
     * The comparable tail, or '' when there are too few digits to compare.
     * An empty key matches nothing, including another empty one.
     */
    public static function key(?string $phone): string
    {
        $digits = self::digits($phone);

        return strlen($digits) >= self::KEY_LENGTH ? substr($digits, -self::KEY_LENGTH) : '';
    }

    public static function matches(?string $a, ?string $b): bool
    {
        $key = self::key($a);

        return $key !== '' && $key === self::key($b);
    }

    /**
     * The form the number is stored in: 01XXXXXXXXX, country code dropped.
     *
     * Anything that is not recognisably a local mobile number is handed back
     * exactly as it was typed — validation is what rejects it, and mangling it
     * first would only make the error message describe something else.
     */
    public static function canonical(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        return preg_match('/^(?:88)?(01[3-9]\d{8})$/', self::digits($phone), $m) ? $m[1] : $phone;
    }
}
