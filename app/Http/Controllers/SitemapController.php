<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\SuccessStory;
use Illuminate\Http\Response;

/**
 * Both locales of every public URL, so search engines index the Bangla and
 * English versions as alternates of one another.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        foreach (array_keys(config('site.locales')) as $locale) {
            foreach ([
                'home' => '1.0', 'about' => '0.9', 'services.index' => '0.9',
                'chambers.index' => '0.9', 'appointment.create' => '1.0',
                'stories.index' => '0.8', 'news.index' => '0.7', 'events.index' => '0.7',
                'blog.index' => '0.7', 'gallery.index' => '0.6',
                'publications.index' => '0.5', 'faq.index' => '0.6', 'contact.create' => '0.7',
            ] as $name => $priority) {
                $urls[] = ['loc' => route($name, ['locale' => $locale]), 'priority' => $priority, 'lastmod' => null];
            }

            foreach ([
                ['services.show', Service::active()->get()],
                ['chambers.show', Chamber::active()->get()],
                ['stories.show', SuccessStory::published()->get()],
                ['gallery.show', GalleryAlbum::active()->get()],
                ['pages.show', Page::published()->get()],
            ] as [$route, $records]) {
                foreach ($records as $record) {
                    $urls[] = [
                        'loc' => route($route, ['locale' => $locale, $this->parameterFor($route) => $record]),
                        'priority' => '0.6',
                        'lastmod' => $record->updated_at?->toAtomString(),
                    ];
                }
            }

            foreach (['news' => 'news.show', 'event' => 'events.show', 'blog' => 'blog.show'] as $type => $route) {
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
