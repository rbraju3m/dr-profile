<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMedia
{
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
