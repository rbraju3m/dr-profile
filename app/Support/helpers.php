<?php

use App\Models\DoctorProfile;
use App\Models\Setting;
use App\Support\Number;

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
