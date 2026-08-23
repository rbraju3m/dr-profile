<?php

namespace App\Support;

/**
 * What this server will actually accept.
 *
 * Validation rules used to advertise 4 MB while PHP's upload_max_filesize was
 * 2 MB, so a 3 MB photo was rejected by PHP before any rule could explain why.
 * Everything now derives the limit from the ini settings instead of guessing.
 */
class Uploads
{
    /** Largest single file PHP will accept, in bytes. */
    public static function maxBytes(): int
    {
        $perFile = self::toBytes(ini_get('upload_max_filesize'));
        $perPost = self::toBytes(ini_get('post_max_size'));

        // A file also has to fit inside the whole request, and the request
        // carries the rest of the form too — leave a little room for it.
        $candidates = array_filter([$perFile, $perPost > 0 ? $perPost - 262144 : 0]);

        return $candidates ? (int) min($candidates) : 2 * 1024 * 1024;
    }

    public static function maxKilobytes(): int
    {
        return (int) floor(self::maxBytes() / 1024);
    }

    /** The unit a size is written in, which Bangla writes as এমবি / কেবি. */
    private static function unit(string $key): string
    {
        return ' '.__('site.units.'.$key);
    }

    /** "2 MB" — for telling a human before they pick a file. */
    public static function maxLabel(): string
    {
        $bytes = self::maxBytes();

        return $bytes >= 1048576
            ? Number::localizeDigits(round($bytes / 1048576, 1)).self::unit('mb')
            : Number::localizeDigits((int) round($bytes / 1024)).self::unit('kb');
    }

    /** "2.4 MB" — the size of a file the operator just tried to send. */
    public static function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? Number::localizeDigits(round($bytes / 1048576, 1)).self::unit('mb')
            : Number::localizeDigits((int) ceil($bytes / 1024)).self::unit('kb');
    }

    /** Whole-request ceiling, which a bulk upload can exceed on its own. */
    public static function maxPostBytes(): int
    {
        return self::toBytes(ini_get('post_max_size')) ?: 8 * 1024 * 1024;
    }

    public static function maxPostLabel(): string
    {
        return Number::localizeDigits(round(self::maxPostBytes() / 1048576, 1)).self::unit('mb');
    }

    /** Validation rules for a single optional image. */
    public static function imageRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.self::maxKilobytes(),
        ];
    }

    /** Validation rules for an optional PDF. */
    public static function pdfRules(): array
    {
        return ['nullable', 'file', 'mimes:pdf', 'max:'.self::maxKilobytes()];
    }

    private static function toBytes(string|false $value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $unit = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
