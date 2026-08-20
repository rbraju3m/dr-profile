<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cookie;

/**
 * Which of the two skins the page renders in.
 *
 * The admin chooses the site's default — light, dark, or whatever the reader's
 * device asks for. A reader may then override it for themselves, but only
 * while the header switch is on: turning that switch off returns the decision
 * to the admin entirely.
 *
 * The choice is kept in an unencrypted cookie for the same reason the locale
 * is: it has to be readable while the page is being built, before anything
 * else about the visitor is known, or the first paint arrives in the wrong
 * theme and flickers.
 */
final class Theme
{
    public const COOKIE = 'theme';

    public const SETTING = 'theme_default';

    /** @var list<string> */
    public const CHOICES = ['light', 'dark', 'system'];

    /** The admin's setting, ignoring the reader. */
    public static function default(): string
    {
        $stored = Setting::get(self::SETTING);

        return in_array($stored, self::CHOICES, true) ? $stored : 'light';
    }

    /** Is the reader allowed to overrule it? */
    public static function switchable(): bool
    {
        return Features::enabled('theme_toggle');
    }

    /**
     * What this request should render as: `light`, `dark`, or `system` when
     * only the browser can answer.
     */
    public static function current(): string
    {
        return self::resolve(self::switchable());
    }

    /**
     * The reader's own standing choice, which is what the switch itself has to
     * show. It differs from current() in one case that matters: `system` is a
     * choice, but it renders as light or dark.
     */
    public static function preference(bool $staff = false): string
    {
        return self::resolve($staff || self::switchable());
    }

    /**
     * The admin panel always honours the reader's own choice. The public
     * switch is a decision about what visitors are offered; staff spend hours
     * in here, and hiding it from them would be answering a different question.
     */
    public static function forStaff(): string
    {
        return self::resolve(true);
    }

    private static function resolve(bool $honourCookie): string
    {
        if (! $honourCookie) {
            return self::default();
        }

        $chosen = Cookie::get(self::COOKIE) ?? request()->cookie(self::COOKIE);

        return in_array($chosen, self::CHOICES, true) ? $chosen : self::default();
    }
}
