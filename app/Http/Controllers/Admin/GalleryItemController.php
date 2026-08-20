<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GalleryItemController extends Controller
{
    public function index(GalleryAlbum $album): View
    {
        return view('admin.items.index', [
            'album' => $album,
            'items' => $album->items()->get(),
        ]);
    }

    public function store(Request $request, GalleryAlbum $album, MediaService $media): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:image,video'],
            'title_en' => ['nullable', 'string', 'max:150'],
            'title_bn' => ['nullable', 'string', 'max:150'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:6144'],
            'video_url' => ['nullable', 'url', 'max:500', 'required_if:type,video'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['type'] === 'video') {
            $album->items()->create([
                'type' => 'video',
                'title_en' => $data['title_en'] ?? null,
                'title_bn' => $data['title_bn'] ?? null,
                'video_url' => $data['video_url'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => true,
            ]);

            return back()->with('success', __('admin.flash.created', ['item' => __('site.gallery.videos')]));
        }

        // Images upload in bulk — one row per file.
        foreach ($request->file('images', []) as $index => $file) {
            $album->items()->create([
                'type' => 'image',
                'title_en' => $data['title_en'] ?? null,
                'title_bn' => $data['title_bn'] ?? null,
                'image' => $media->store($file, 'gallery'),
                'sort_order' => ($data['sort_order'] ?? 0) + $index,
                'is_active' => true,
            ]);
        }

        return back()->with('success', __('admin.flash.created', ['item' => __('site.gallery.photos')]));
    }

    public function destroy(GalleryItem $item, MediaService $media): RedirectResponse
    {
        $album = $item->album;
        $media->delete($item->image);
        $item->delete();

        return redirect()
            ->route('admin.albums.items.index', $album)
            ->with('success', __('admin.flash.deleted', ['item' => __('admin.nav.albums')]));
    }
}
