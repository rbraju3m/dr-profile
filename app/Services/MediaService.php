<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Uploads go to the public disk under a per-resource folder. Filenames are
 * randomised so two uploads called "photo.jpg" cannot overwrite each other.
 */
class MediaService
{
    public function store(UploadedFile $file, string $folder): string
    {
        $name = Str::random(24).'.'.$file->getClientOriginalExtension();

        return $file->storeAs($folder, $name, 'public');
    }

    public function delete(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /** Replace an existing file, deleting the old one only after the new one lands. */
    public function replace(?UploadedFile $file, ?string $current, string $folder, bool $remove = false): ?string
    {
        if ($file) {
            $path = $this->store($file, $folder);
            $this->delete($current);

            return $path;
        }

        if ($remove) {
            $this->delete($current);

            return null;
        }

        return $current;
    }
}
