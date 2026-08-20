<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Publication;
use App\Models\Service;
use App\Models\SuccessStory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Site-wide search across everything a visitor can read.
 *
 * Deliberately a LIKE scan rather than a search engine: the content here is a
 * few hundred rows at most, and both language columns have to be searched, so
 * the cost of a real index is not worth paying yet.
 */
class SearchController extends Controller
{
    private const PER_TYPE = 5;

    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();

        return view('public.search', [
            'query' => $query,
            'groups' => strlen($query) >= 2 ? $this->search($query) : collect(),
        ]);
    }

    /** @return Collection<string, Collection> */
    private function search(string $term): Collection
    {
        $groups = collect([
            __('site.nav.services') => Service::query()
                ->active()
                ->where($this->matching($term, ['name_en', 'name_bn', 'short_description_en', 'short_description_bn']))
                ->take(self::PER_TYPE)->get()
                ->map(fn (Service $s) => $this->hit($s->tr('name'), $s->tr('short_description'), route('services.show', $s), 'stethoscope')),

            __('site.nav.success_stories') => SuccessStory::query()
                ->published()
                ->where($this->matching($term, ['title_en', 'title_bn', 'summary_en', 'summary_bn']))
                ->take(self::PER_TYPE)->get()
                ->map(fn (SuccessStory $s) => $this->hit($s->tr('title'), $s->tr('summary'), route('stories.show', $s), 'heart')),

            __('site.nav.news_events') => Post::query()
                ->published()->whereIn('type', ['news', 'event'])
                ->where($this->matching($term, ['title_en', 'title_bn', 'excerpt_en', 'excerpt_bn']))
                ->take(self::PER_TYPE)->get()
                ->map(fn (Post $p) => $this->hit(
                    $p->tr('title'),
                    $p->tr('excerpt'),
                    route($p->type === 'event' ? 'events.show' : 'news.show', $p),
                    $p->type === 'event' ? 'calendar' : 'file-text',
                )),

            __('site.nav.blog') => Post::query()
                ->published()->blog()
                ->where($this->matching($term, ['title_en', 'title_bn', 'excerpt_en', 'excerpt_bn']))
                ->take(self::PER_TYPE)->get()
                ->map(fn (Post $p) => $this->hit($p->tr('title'), $p->tr('excerpt'), route('blog.show', $p), 'book-open')),

            __('site.nav.faq') => Faq::query()
                ->active()
                ->where($this->matching($term, ['question_en', 'question_bn', 'answer_en', 'answer_bn']))
                ->take(self::PER_TYPE)->get()
                ->map(fn (Faq $f) => $this->hit($f->tr('question'), strip_tags((string) $f->tr('answer')), route('faq.index').'#faq', 'info')),

            __('site.nav.publications') => Publication::query()
                ->active()
                ->where($this->matching($term, ['title_en', 'title_bn', 'authors', 'venue_en']))
                ->take(self::PER_TYPE)->get()
                ->map(fn (Publication $p) => $this->hit($p->tr('title'), $p->authors, route('publications.index'), 'graduation-cap')),
        ]);

        return $groups->reject(fn (Collection $hits) => $hits->isEmpty());
    }

    /** Match the term against every given column, in either language. */
    private function matching(string $term, array $columns): callable
    {
        return function (Builder $query) use ($term, $columns) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%'.$this->escape($term).'%');
            }
        };
    }

    /** % and _ are wildcards in LIKE; a patient typing them means them literally. */
    private function escape(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term);
    }

    private function hit(?string $title, ?string $excerpt, string $url, string $icon): array
    {
        return [
            'title' => $title,
            'excerpt' => \Str::limit(trim((string) $excerpt), 140),
            'url' => $url,
            'icon' => $icon,
        ];
    }
}
