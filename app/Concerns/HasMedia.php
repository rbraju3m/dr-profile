<?php

namespace App\Concerns;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A stored file and the row pointing at it are one thing, and both halves have
 * to go together.
 *
 * Deleting the file was the admin controller's job alone, so every other way a
 * row could leave — tinker, a seeder, a console command, a database cascade —
 * left the file behind with nothing pointing at it. `GalleryItem` was given a
 * `deleting` hook of its own when an album cascade did exactly that; every
 * other model with a file column still had none.
 *
 * Declare the columns and the row takes its files with it, whichever way it
 * goes:
 *
 *     protected array $mediaColumns = ['image', 'mobile_image'];
 */
trait HasMedia
{
    public static function bootHasMedia(): void
    {
        static::deleting(function (Model $model) {
            foreach ($model->mediaColumns() as $column) {
                app(MediaService::class)->delete($model->getAttributeValue($column));
            }
        });
    }

    /**
     * Columns on this model holding a stored file.
     *
     * Read with property_exists rather than `$this->mediaColumns ?? []`: the
     * property and this method share a name, so on a model that declares no
     * property Eloquent's `__get` finds the method instead, takes it for a
     * relationship and throws. A model with nothing to clean up would have
     * failed on its way out of the database.
     *
     * @return array<int, string>
     */
    public function mediaColumns(): array
    {
        return property_exists($this, 'mediaColumns') ? $this->mediaColumns : [];
    }

    /**
     * Resolve a stored media column to a public URL, tolerating absolute URLs
     * and returning null when nothing is set so views can fall back to a placeholder.
     */
    public function mediaUrl(?string $column, ?string $default = null): ?string
    {
        $path = $column ? $this->getAttributeValue($column) : null;

        if (blank($path)) {
            return $default;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
