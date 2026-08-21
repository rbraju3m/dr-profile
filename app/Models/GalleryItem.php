<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use App\Services\MediaService;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['title'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * The file goes with the row. Doing this on the model rather than in the
     * controller means a deletion from tinker, a seeder or an album cascade is
     * cleaned up too.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $item) {
            app(MediaService::class)->delete($item->image);
        });
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }

    /** Turn whatever link was pasted into one that can be framed. */
    public function embedUrl(): ?string
    {
        return VideoEmbed::url($this->video_url);
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->imageUrl()) {
            return $this->imageUrl();
        }

        return VideoEmbed::thumbnail($this->video_url);
    }
}
