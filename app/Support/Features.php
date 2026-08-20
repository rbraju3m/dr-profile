<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Every part of the public site an administrator can switch off.
 *
 * A switch is one row in `settings`, keyed `feature_<name>`. A missing row
 * means "on", so an existing install shows exactly what it showed before and
 * nothing hides itself by accident.
 *
 * Three kinds of switch live here:
 *
 *  - `pages`  — a whole public section. Off means the routes 404, the link
 *               leaves the header, footer, sitemap and site search.
 *  - `home`   — one band of the homepage.
 *  - `layout` — a piece of furniture in the header or footer.
 *
 * `requires` is what stops a switch from leaving a dead end behind: the
 * homepage expertise band lists services and links to their detail pages, so
 * turning the expertise *page* off takes that band with it whatever its own
 * switch says.
 */
final class Features
{
    /** Prefix under which each switch is stored in the settings table. */
    public const PREFIX = 'feature_';

    /** The settings group these rows are written under. */
    public const GROUP = 'features';

    private const REGISTRY = [
        // ── Public pages ───────────────────────────────────────────────────
        'about' => ['group' => 'pages'],
        'services' => ['group' => 'pages'],
        'chambers' => ['group' => 'pages'],
        'appointment' => ['group' => 'pages'],
        'stories' => ['group' => 'pages'],
        'news' => ['group' => 'pages'],
        'events' => ['group' => 'pages'],
        'blog' => ['group' => 'pages'],
        'gallery' => ['group' => 'pages'],
        'publications' => ['group' => 'pages'],
        'faq' => ['group' => 'pages'],
        'search' => ['group' => 'pages'],
        'contact' => ['group' => 'pages'],

        // ── Homepage bands, in the order they appear ───────────────────────
        'home_hero' => ['group' => 'home'],
        'home_stats' => ['group' => 'home'],
        'home_about' => ['group' => 'home'],
        'home_services' => ['group' => 'home', 'requires' => 'services'],
        'home_chambers' => ['group' => 'home', 'requires' => 'chambers'],
        'home_steps' => ['group' => 'home', 'requires' => 'appointment'],
        'home_stories' => ['group' => 'home', 'requires' => 'stories'],
        'home_testimonials' => ['group' => 'home'],
        'home_news' => ['group' => 'home'],
        'home_blog' => ['group' => 'home', 'requires' => 'blog'],
        'home_faq' => ['group' => 'home', 'requires' => 'faq'],
        'home_cta' => ['group' => 'home'],

        // ── Header and footer ──────────────────────────────────────────────
        'header_topbar' => ['group' => 'layout'],
        'social_links' => ['group' => 'layout'],
        'header_search' => ['group' => 'layout', 'requires' => 'search'],
        'language_switcher' => ['group' => 'layout'],
        'footer_links' => ['group' => 'layout'],
        'footer_chambers' => ['group' => 'layout', 'requires' => 'chambers'],
        'footer_contact' => ['group' => 'layout'],
        'footer_disclaimer' => ['group' => 'layout'],
        'mobile_action_bar' => ['group' => 'layout'],
        'theme_toggle' => ['group' => 'layout'],
    ];

    /**
     * Is this part of the site showing?
     *
     * An unregistered key is treated as on: a mistyped switch that shows a
     * section reads as a bug, whereas one that hides it looks like a decision
     * and can sit unnoticed for months. `FeatureRegistryTest` catches the typo.
     */
    public static function enabled(string $key): bool
    {
        if (! isset(self::REGISTRY[$key])) {
            return true;
        }

        foreach ((array) (self::REGISTRY[$key]['requires'] ?? []) as $parent) {
            if (! self::enabled($parent)) {
                return false;
            }
        }

        return self::stored($key);
    }

    /** Every switch, grouped as the admin form lays them out. */
    public static function groups(): array
    {
        $groups = [];

        foreach (self::REGISTRY as $key => $definition) {
            $groups[$definition['group']][$key] = [
                'enabled' => self::stored($key),
                'requires' => (array) ($definition['requires'] ?? []),
            ];
        }

        return $groups;
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::REGISTRY);
    }

    public static function has(string $key): bool
    {
        return isset(self::REGISTRY[$key]);
    }

    /** Keep only the entries whose feature is showing. */
    public static function filter(array $items, string $key = 'feature'): array
    {
        return array_values(array_filter(
            $items,
            fn (array $item) => ! isset($item[$key]) || self::enabled($item[$key])
        ));
    }

    /** This switch's own position, ignoring anything it depends on. */
    private static function stored(string $key): bool
    {
        $value = Setting::map()->get(self::PREFIX.$key);

        return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
