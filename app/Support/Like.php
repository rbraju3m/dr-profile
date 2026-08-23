<?php

namespace App\Support;

/**
 * % and _ are wildcards in a LIKE clause; somebody typing them into a search
 * box means them literally.
 *
 * Left unescaped, a search for "100%" matches every row rather than none, and
 * "01_" matches every number with any third digit. This was written out twice
 * already — in the admin listings and in site search — and the appointment
 * filter, which is the one place patients' own details are searched, had been
 * written a third time without it.
 */
final class Like
{
    /** Escape a user's term for use inside a LIKE pattern. */
    public static function escape(string $term): string
    {
        // The backslash goes first, or it doubles the escapes added after it.
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term);
    }

    /** The same term wrapped as a "contains" pattern. */
    public static function contains(string $term): string
    {
        return '%'.self::escape($term).'%';
    }
}
