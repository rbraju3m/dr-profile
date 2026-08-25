<?php

namespace App\Console\Commands;

use App\Models\DoctorProfile;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Installs resources/brand/share-card.png as the profile's og:image.
 *
 * The picture that shipped in that column was a photograph of two stray dogs —
 * a test upload nobody looked at again — and og:image is the one image that
 * goes everywhere: every share of every page, on every network, is that
 * picture. It falls back to the profile photo when the column is empty, which
 * is no better here, because the photo is a graphic stating a designation he
 * does not hold.
 *
 * So the card carries only his name and his specialty, in both languages, set
 * in the site's own hero language. Both are current and neither is a claim
 * anyone has to check.
 *
 * The column is a database value and the file lives on the storage disk, so
 * neither reaches another install through git. This command is how it travels.
 * Running it twice is harmless: the card already in place is recognised by its
 * contents and left alone.
 */
class InstallShareCard extends Command
{
    protected $signature = 'profile:share-card
        {--force : Install the card again even if the one in place is identical}';

    protected $description = 'Install the share card as the profile’s og:image';

    private const SOURCE = 'brand/share-card.png';

    public function handle(MediaService $media): int
    {
        $source = resource_path(self::SOURCE);

        if (! is_file($source)) {
            $this->error('No card at resources/'.self::SOURCE.'.');

            return self::FAILURE;
        }

        $profile = DoctorProfile::current();
        $current = $profile->og_image;

        if (! $this->option('force') && $this->alreadyInstalled($current, $source)) {
            $this->info('The share card in place is already this one. Nothing to do.');

            return self::SUCCESS;
        }

        $profile->og_image = $media->replace(
            new UploadedFile($source, 'share-card.png', 'image/png', null, true),
            $current,
            'profile',
            field: 'og_image',
        );

        $profile->save();

        $this->line('  was  '.($current ?: '— nothing, so pages fell back to the profile photo'));
        $this->line('  now  '.$profile->og_image);
        $this->newLine();
        $this->info('Share card installed.');

        return self::SUCCESS;
    }

    /** Compared by contents, not by name: MediaService randomises every filename. */
    private function alreadyInstalled(?string $current, string $source): bool
    {
        if (blank($current) || ! Storage::disk('public')->exists($current)) {
            return false;
        }

        return hash_file('sha256', $source) === hash('sha256', Storage::disk('public')->get($current));
    }
}
