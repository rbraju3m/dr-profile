<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Concerns\HasTranslations;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GalleryItem extends Model
{
    use HasMedia, HasTranslations, Sortable;

    protected $guarded = [];

    protected array $translatable = ['title'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function imageUrl(): ?string
    {
        return $this->mediaUrl('image');
    }

    /** Turn a YouTube/Vimeo watch URL into an embeddable one. */
    public function embedUrl(): ?string
    {
        $url = $this->video_url;

        if (blank($url)) {
            return null;
        }

        if (preg_match('~youtu\.be/([\w-]+)~', $url, $m) || preg_match('~youtube\.com/watch\?v=([\w-]+)~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->imageUrl()) {
            return $this->imageUrl();
        }

        if ($this->video_url && preg_match('~(?:youtu\.be/|youtube\.com/watch\?v=)([\w-]+)~', $this->video_url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
        }

        return null;
    }
}
