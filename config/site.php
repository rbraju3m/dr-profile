<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    | Every public URL is prefixed with one of these codes (/en/..., /bn/...).
    | `dir` and `font` drive the <html> attributes and typography stack.
    */
    'locales' => [
        'en' => ['name' => 'English', 'native' => 'English', 'short' => 'EN', 'dir' => 'ltr'],
        'bn' => ['name' => 'Bangla', 'native' => 'বাংলা', 'short' => 'বাং', 'dir' => 'ltr'],
    ],

    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Appointments
    |--------------------------------------------------------------------------
    */
    'booking' => [
        // How many days ahead a patient may book.
        'window_days' => 30,
        // Slots this many minutes from now are no longer offered for today.
        'lead_time_minutes' => 60,
        // Same phone number may not hold more than this many open appointments.
        'max_open_per_phone' => 3,
    ],

    'pagination' => [
        'posts' => 9,
        'stories' => 9,
        'gallery' => 12,
    ],
];
