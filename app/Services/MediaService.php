<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Uploads go to the public disk under a per-resource folder. Filenames are
 * randomised so two uploads called "photo.jpg" cannot overwrite each other.
 */
class MediaService
{
    /**
     * The extension is taken from the file's own contents, not from the name
     * the browser sent.
     *
     * Validation already checks the type, so this changes nothing in practice —
     * but the filename is the one part of an upload its sender chooses, and it
     * has no business deciding what the file on disk is called.
     */
    public function store(UploadedFile $file, string $folder, string $field = 'file'): string
    {
        $extension = preg_replace(
            '/[^a-z0-9]/',
            '',
            Str::lower($file->extension() ?: $file->getClientOriginalExtension()),
        ) ?: 'bin';

        $path = $file->storeAs($folder, Str::random(24).'.'.$extension, 'public');

        /*
         * storeAs() returns false when the disk refuses the write — no space,
         * no permission. Handing that back as though it were a path wrote
         * `false` into the column and told the operator their upload had
         * saved. In replace() it was worse than that: the file they were
         * replacing had already been deleted by the time anyone could notice.
         *
         * The wording for this has been sitting in validation_custom since
         * before anything threw it.
         */
        if ($path === false) {
            throw ValidationException::withMessages([
                $field => __('validation_custom.upload.store_failed'),
            ]);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Replace an existing file, deleting the old one only after the new one
     * lands — and not at all if it never does, since store() throws.
     */
    public function replace(?UploadedFile $file, ?string $current, string $folder, bool $remove = false, string $field = 'file'): ?string
    {
        if ($file) {
            $path = $this->store($file, $folder, $field);
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
