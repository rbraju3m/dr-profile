<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The database cascade would remove the item rows without ever loading
     * them, so their images were left on disk with nothing pointing at them.
     * Deleting through Eloquent lets each item clean up after itself.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $album) {
            $album->items()->get()->each->delete();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    public function coverUrl(): ?string
    {
        return $this->mediaUrl('cover_image') ?? $this->items->first()?->imageUrl();
    }
}
