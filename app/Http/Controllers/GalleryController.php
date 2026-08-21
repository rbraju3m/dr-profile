<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('public.gallery.index', [
            'albums' => GalleryAlbum::query()
                ->active()
                ->ordered()
                ->withCount(['items' => fn ($q) => $q->where('is_active', true)])
                ->with('activeItems')
                ->get(),
        ]);
    }

    public function show(GalleryAlbum $album): View
    {
        abort_unless($album->is_active, 404);

        return view('public.gallery.show', [
            'album' => $album,
            'items' => $album->items()->where('is_active', true)->paginate(config('site.pagination.gallery')),
            'others' => GalleryAlbum::active()->ordered()->whereKeyNot($album->id)->get(),
        ]);
    }
}
