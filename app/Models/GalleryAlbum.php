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

    /** @var array<int, string> */
    protected array $mediaColumns = ['cover_image'];

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

    /** What a visitor is allowed to see — the listing must never reach past this. */
    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }

    /**
     * The album's own cover, or the first picture inside it.
     *
     * "Inside it" means the first *active* one: falling back to `items` put a
     * photograph the admin had switched off on the front of the album, in the
     * one place they would never think to look for it. A video-only album
     * borrows the platform's still rather than showing nothing.
     */
    public function coverUrl(): ?string
    {
        if ($own = $this->mediaUrl('cover_image')) {
            return $own;
        }

        return $this->activeItems
            ->map(fn (GalleryItem $item) => $item->thumbnailUrl())
            ->first(fn (?string $url) => filled($url));
    }
}
