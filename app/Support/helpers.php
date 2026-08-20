<?php

use App\Models\DoctorProfile;
use App\Models\Setting;
use App\Support\Features;
use App\Support\Number;
use App\Support\Theme;

if (! function_exists('setting')) {
    /** Read a site setting by key, with an optional default. */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('doctor')) {
    /** The single doctor this platform profiles. */
    function doctor(): DoctorProfile
    {
        return DoctorProfile::current();
    }
}

if (! function_exists('bn_digits')) {
    /** Localise numerals for the active locale (Bangla digits under `bn`). */
    function bn_digits(string|int|float|null $value): string
    {
        return Number::localizeDigits($value);
    }
}

if (! function_exists('feature')) {
    /** Is this part of the public site switched on in the admin? */
    function feature(string $key): bool
    {
        return Features::enabled($key);
    }
}

if (! function_exists('theme')) {
    /** The skin this request renders in: light, dark, or system. */
    function theme(): string
    {
        return Theme::current();
    }
}
