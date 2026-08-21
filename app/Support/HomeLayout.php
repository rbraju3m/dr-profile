<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Which of the three homepage designs the site is served in.
 *
 * The homepage *is* this practice's profile, and the same profile reads very
 * differently depending on how it is arranged: a stacked brochure, a compact
 * card-led front page, or a magazine spread. Rather than settle that for the
 * doctor once, the admin picks it in Sections & Visibility, exactly as they
 * pick the theme.
 *
 * All three render the same bands from the same data and honour the same
 * switches — a layout changes the shape of the page, never what is on it. That
 * is what `HomeLayoutTest` holds: turning a band off must empty it in every
 * design, or one of the three would quietly ignore the admin.
 *
 * An unrecognised (or missing) setting falls back to `classic`, the design the
 * site shipped with, so an install that has never touched this looks unchanged.
 */
final class HomeLayout
{
    public const SETTING = 'home_layout';

    /** @var list<string> */
    public const CHOICES = ['classic', 'spotlight', 'editorial'];

    /** The design this request renders in. */
    public static function current(): string
    {
        $stored = Setting::get(self::SETTING);

        return in_array($stored, self::CHOICES, true) ? $stored : self::CHOICES[0];
    }

    /** The view that draws it. */
    public static function view(): string
    {
        return 'public.home.'.self::current();
    }
}
