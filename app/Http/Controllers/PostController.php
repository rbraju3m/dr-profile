<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    public function news(Request $request): View
    {
        return view('public.posts.index', [
            'type' => 'news',
            'posts' => $this->listing($request, 'news'),
            'categories' => $this->categoriesFor('news'),
            'filters' => $this->filters($request),
        ]);
    }

    public function blog(Request $request): View
    {
        return view('public.posts.index', [
            'type' => 'blog',
            'posts' => $this->listing($request, 'blog'),
            'categories' => $this->categoriesFor('blog'),
            'filters' => $this->filters($request),
        ]);
    }

    /** Events split into upcoming and past rather than a single flat list. */
    public function events(): View
    {
        return view('public.posts.events', [
            'upcoming' => Post::published()->upcomingEvents()->get(),
            'past' => Post::published()->pastEvents()->paginate(config('site.pagination.posts')),
        ]);
    }

    public function show(Post $post, string $type): View
    {
        // Keep /news/x from rendering a blog article and vice versa.
        if (! $post->is_published || $post->type !== $type) {
            throw new NotFoundHttpException;
        }

        $post->incrementQuietly('views');

        return view('public.posts.show', [
            'post' => $post->load('category'),
            'related' => Post::published()
                ->ofType($post->type)
                ->whereKeyNot($post->id)
                ->when($post->post_category_id, fn ($q) => $q->where('post_category_id', $post->post_category_id))
                ->latestFirst()
                ->take(3)
                ->get(),
        ]);
    }

    private function listing(Request $request, string $type)
    {
        $search = $request->string('q')->trim()->toString();
        $category = $request->string('category')->toString();

        return Post::query()
            ->published()
            ->ofType($type)
            ->with('category')
            ->when($category, fn ($q) => $q->whereRelation('category', 'slug', $category))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    foreach (['title_en', 'title_bn', 'excerpt_en', 'excerpt_bn'] as $column) {
                        $inner->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->latestFirst()
            ->paginate(config('site.pagination.posts'))
            ->withQueryString();
    }

    private function categoriesFor(string $type)
    {
        return PostCategory::query()
            ->active()
            ->ordered()
            ->whereHas('posts', fn ($q) => $q->published()->ofType($type))
            ->get();
    }

    /** @return array{q: string, category: string} */
    private function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->trim()->toString(),
            'category' => $request->string('category')->toString(),
        ];
    }
}
