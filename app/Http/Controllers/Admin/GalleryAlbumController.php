<?php

namespace App\Http\Controllers\Admin;

use App\Models\GalleryAlbum;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class GalleryAlbumController extends ResourceController
{
    protected string $model = GalleryAlbum::class;

    protected string $viewPath = 'albums';

    protected string $routeName = 'admin.albums';

    protected string $labelKey = 'admin.nav.albums';

    protected bool $reorderable = true;

    protected array $searchable = ['title_en', 'title_bn'];

    protected array $mediaFields = ['cover_image' => 'albums'];

    protected ?string $slugSource = 'title_en';

    protected function indexQuery(): Builder
    {
        return GalleryAlbum::query()->withCount('items')->orderBy('sort_order')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.image'), 'type' => 'image', 'value' => fn (GalleryAlbum $a) => $a->mediaUrl('cover_image'), 'class' => 'w-20'],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            [
                'label' => __('site.gallery.photos'),
                'type' => 'html',
                'value' => fn (GalleryAlbum $a) => '<a href="'.route('admin.albums.items.index', $a).'" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 hover:bg-primary-100">'
                    .$a->items_count.' · '.e(__('admin.actions.edit')).'</a>',
            ],
            ['label' => __('admin.common.order'), 'type' => 'number', 'key' => 'sort_order', 'class' => 'w-16'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title_en' => ['required', 'string', 'max:150'],
            'title_bn' => ['nullable', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('gallery_albums', 'slug')->ignore($record?->id)],
            'description_en' => ['nullable', 'string', 'max:600'],
            'description_bn' => ['nullable', 'string', 'max:600'],
            'cover_image' => Uploads::imageRules(),
            'event_date' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
