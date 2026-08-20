<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Turns the URL somebody copies out of their browser into one that can be
 * framed.
 *
 * A YouTube watch link, a youtu.be short link and a Facebook video page all
 * refuse to load inside an iframe, so pasting one into a video field produced
 * an empty black box. Each platform has a separate embed address; this is the
 * one place that knows them.
 */
class VideoEmbed
{
    public static function url(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        // Already an embed address — leave it alone.
        if (Str::contains($url, ['/embed/', 'player.vimeo.com', 'plugins/video.php'])) {
            return $url;
        }

        if ($id = self::youtubeId($url)) {
            return 'https://www.youtube-nocookie.com/embed/'.$id;
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        if (self::isFacebook($url)) {
            return 'https://www.facebook.com/plugins/video.php?href='.urlencode($url).'&show_text=false';
        }

        return null;
    }

    /** True when the link points at something we know how to frame. */
    public static function isEmbeddable(?string $url): bool
    {
        return self::url($url) !== null;
    }

    /** A still to show before the video loads, where the platform offers one. */
    public static function thumbnail(?string $url): ?string
    {
        $id = self::youtubeId((string) $url);

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }

    private static function youtubeId(string $url): ?string
    {
        $patterns = [
            '~youtu\.be/([\w-]{6,})~i',              // youtu.be/ID
            '~youtube\.com/watch\?(?:.*&)?v=([\w-]{6,})~i',  // watch?v=ID, with or without &list=
            '~youtube\.com/shorts/([\w-]{6,})~i',    // shorts/ID
            '~youtube\.com/live/([\w-]{6,})~i',      // live/ID
            '~youtube\.com/embed/([\w-]{6,})~i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    private static function isFacebook(string $url): bool
    {
        return (bool) preg_match('~(facebook\.com/.+/videos/|facebook\.com/watch/?\?v=|fb\.watch/)~i', $url);
    }
}
