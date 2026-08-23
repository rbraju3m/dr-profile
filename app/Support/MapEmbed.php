<?php

namespace App\Support;

/**
 * Turns whatever somebody pastes into a chamber's map field into an address
 * that can be framed — or into nothing at all.
 *
 * The field used to be printed raw, because what Google hands you is a whole
 * <iframe> element and printing it was the shortest way to make the map
 * appear. That made this the one place on the site where an editor could put
 * markup of their own choosing onto a page every visitor reads, and an editor
 * is not an administrator.
 *
 * It is not a hypothetical. The snippet already in this database came from a
 * third-party "map generator" and carried a <style> block and a backlink to an
 * unrelated site along with the map.
 *
 * So: keep the src, discard the element, refuse any address that is not a map,
 * and let the Blade view draw the frame.
 */
final class MapEmbed
{
    /** Hosts that serve maps meant to be framed. */
    private const HOSTS = [
        'google.com', 'www.google.com', 'maps.google.com',
        'openstreetmap.org', 'www.openstreetmap.org',
    ];

    /** The address of a frameable map, or null when there is nothing safe to show. */
    public static function url(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // A pasted <iframe …> — the address is the only part worth keeping.
        if (preg_match('~<iframe\b[^>]*?\ssrc=["\']([^"\']+)["\']~i', $value, $m)) {
            $value = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
        }

        return self::isMap($value) ? $value : null;
    }

    /** Would this paste produce a map? What the admin form asks before saving. */
    public static function isEmbeddable(?string $value): bool
    {
        return self::url($value) !== null;
    }

    private static function isMap(string $url): bool
    {
        $parts = parse_url($url);

        // https only: an http frame is blocked as mixed content anyway, and
        // anything without a host is a scheme we have no business rendering.
        if (($parts['scheme'] ?? '') !== 'https' || ! isset($parts['host'])) {
            return false;
        }

        if (! in_array(strtolower($parts['host']), self::HOSTS, true)) {
            return false;
        }

        $path = rtrim($parts['path'] ?? '', '/');
        parse_str($parts['query'] ?? '', $query);

        return match (true) {
            // Share → Embed a map.
            str_starts_with($path, '/maps/embed') => true,
            // The older share link, which asks for the embed with a parameter.
            $path === '/maps' => ($query['output'] ?? null) === 'embed',
            // OpenStreetMap's own export.
            $path === '/export/embed.html' => true,
            default => false,
        };
    }
}
