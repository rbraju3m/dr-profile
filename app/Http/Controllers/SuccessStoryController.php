<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\SuccessStory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SuccessStoryController extends Controller
{
    public function index(Request $request): View
    {
        $service = $request->string('service')->toString();

        $stories = SuccessStory::query()
            ->published()
            ->with('service')
            ->when($service, fn ($q) => $q->whereRelation('service', 'slug', $service))
            ->latestFirst()
            ->paginate(config('site.pagination.stories'))
            ->withQueryString();

        return view('public.stories.index', [
            'stories' => $stories,
            'services' => Service::active()->ordered()->whereHas('successStories')->get(),
            'activeService' => $service,
        ]);
    }

    public function show(SuccessStory $story): View
    {
        abort_unless($story->is_published, 404);

        $story->incrementQuietly('views');

        return view('public.stories.show', [
            'story' => $story->load('service'),
            'related' => SuccessStory::published()
                ->whereKeyNot($story->id)
                ->when($story->service_id, fn ($q) => $q->where('service_id', $story->service_id))
                ->latestFirst()
                ->take(3)
                ->get(),
        ]);
    }
}
