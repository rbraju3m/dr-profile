<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\SuccessStory;
use App\Support\Features;
use Illuminate\Http\Response;

/**
 * Both locales of every public URL, so search engines index the Bangla and
 * English versions as alternates of one another.
 *
 * Sections switched off in the admin are left out: their URLs answer 404, and
 * listing them here would ask a crawler to fetch pages that no longer exist.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        foreach (array_keys(config('site.locales')) as $locale) {
            foreach ([
                'home' => ['1.0', null], 'about' => ['0.9', 'about'], 'services.index' => ['0.9', 'services'],
                'chambers.index' => ['0.9', 'chambers'], 'appointment.create' => ['1.0', 'appointment'],
                'stories.index' => ['0.8', 'stories'], 'news.index' => ['0.7', 'news'], 'events.index' => ['0.7', 'events'],
                'blog.index' => ['0.7', 'blog'], 'gallery.index' => ['0.6', 'gallery'],
                'publications.index' => ['0.5', 'publications'], 'faq.index' => ['0.6', 'faq'], 'contact.create' => ['0.7', 'contact'],
            ] as $name => [$priority, $feature]) {
                if ($feature && ! Features::enabled($feature)) {
                    continue;
                }

                $urls[] = ['loc' => route($name, ['locale' => $locale]), 'priority' => $priority, 'lastmod' => null];
            }

            foreach ([
                ['services.show', 'services', fn () => Service::active()->get()],
                ['chambers.show', 'chambers', fn () => Chamber::active()->get()],
                ['stories.show', 'stories', fn () => SuccessStory::published()->get()],
                ['gallery.show', 'gallery', fn () => GalleryAlbum::active()->get()],
                ['pages.show', null, fn () => Page::published()->get()],
            ] as [$route, $feature, $query]) {
                if ($feature && ! Features::enabled($feature)) {
                    continue;
                }

                $records = $query();

                foreach ($records as $record) {
                    $urls[] = [
                        'loc' => route($route, ['locale' => $locale, $this->parameterFor($route) => $record]),
                        'priority' => '0.6',
                        'lastmod' => $record->updated_at?->toAtomString(),
                    ];
                }
            }

            foreach ([
                'news' => ['news.show', 'news'],
                'event' => ['events.show', 'events'],
                'blog' => ['blog.show', 'blog'],
            ] as $type => [$route, $feature]) {
                if (! Features::enabled($feature)) {
                    continue;
                }

                foreach (Post::published()->ofType($type)->get() as $post) {
                    $urls[] = [
                        'loc' => route($route, ['locale' => $locale, 'post' => $post]),
                        'priority' => '0.6',
                        'lastmod' => $post->updated_at?->toAtomString(),
                    ];
                }
            }
        }

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    private function parameterFor(string $route): string
    {
        return match ($route) {
            'services.show' => 'service',
            'chambers.show' => 'chamber',
            'stories.show' => 'story',
            'gallery.show' => 'album',
            default => 'page',
        };
    }
}
