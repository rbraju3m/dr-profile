<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ContactMessage;
use App\Models\Credential;
use App\Models\DoctorProfile;
use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Publication;
use App\Models\ScheduleException;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Stat;
use App\Models\SuccessStory;
use App\Models\Testimonial;
use App\Services\MediaService;
use App\Support\Features;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Removes the seeded content that makes claims on the doctor's behalf.
 *
 * The demo seeder invents patient recoveries, patient quotes and research
 * papers. Harmless while the profile is fictional; the moment a real name goes
 * on the site they become false medical and academic claims, so they must go
 * before the site is anyone's.
 *
 * Neutral scaffolding — service descriptions, FAQs, pages — is left alone
 * unless --all is passed, because it is generic and meant to be edited.
 */
class PurgeDemoContent extends Command
{
    protected $signature = 'demo:purge
        {--all : Remove every seeded record, leaving a blank but working site}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Delete seeded content that would be false under a real doctor’s name';

    public function handle(MediaService $media): int
    {
        // Ordered so children go before the rows they point at.
        $targets = [
            'Success stories' => SuccessStory::class,
            'Testimonials' => Testimonial::class,
            'Publications' => Publication::class,
        ];

        if ($this->option('all')) {
            $targets = array_merge($targets, [
                'News, events and articles' => Post::class,
                'Post categories' => PostCategory::class,
                'Gallery albums' => GalleryAlbum::class,
                'Credentials' => Credential::class,
                'Appointments' => Appointment::class,
                'Schedule exceptions' => ScheduleException::class,
                'Chambers (and their schedules)' => Chamber::class,
                'Areas of expertise' => Service::class,
                'FAQs' => Faq::class,
                'Homepage statistics' => Stat::class,
                'Hero slides' => Slider::class,
                'Pages' => Page::class,
                'Contact messages' => ContactMessage::class,
            ]);
        }

        $this->newLine();
        $this->line('About to delete:');

        $total = 0;

        foreach ($targets as $label => $class) {
            $count = $class::count();
            $total += $count;
            $this->line(sprintf('  %-28s %d', $label, $count));
        }

        if ($total === 0) {
            $this->info('Nothing to delete.');

            return self::SUCCESS;
        }

        $this->newLine();

        if (! $this->option('force') && ! $this->confirm("Delete these {$total} records permanently?", false)) {
            $this->line('Cancelled, nothing was changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($targets, $media) {
            foreach ($targets as $class) {
                foreach ($class::all() as $record) {
                    // Drop the uploaded file too, or the public disk keeps orphans.
                    foreach (['image', 'photo'] as $column) {
                        if (array_key_exists($column, $record->getAttributes())) {
                            $media->delete($record->{$column});
                        }
                    }

                    $record->delete();
                }
            }
        });

        if ($this->option('all')) {
            $this->purgeProfileAndSettings($media);
        }

        $this->newLine();
        $this->info("Deleted {$total} records.");

        if ($this->option('all')) {
            $this->line('Staff accounts were kept so you can still sign in to /admin.');
            $this->line('Rebuild the site with: php artisan doctor:import');
        } else {
            $this->line('Kept: services, FAQs, chambers, credentials, pages and settings —');
            $this->line('edit or replace those from the admin, or via php artisan doctor:import.');
        }

        return self::SUCCESS;
    }

    /**
     * The profile row and the contact settings are demo data too — a fictional
     * name, an invented BMDC number and a hotline that belongs to nobody.
     */
    private function purgeProfileAndSettings(MediaService $media): void
    {
        foreach (DoctorProfile::all() as $profile) {
            foreach (['photo', 'hero_image', 'signature', 'og_image', 'cv_file'] as $column) {
                $media->delete($profile->{$column});
            }

            $profile->delete();
        }

        DoctorProfile::forgetCache();

        /*
         * The show/hide switches survive: they are decisions about how this
         * site is laid out, not invented content, and resetting them would
         * silently bring back sections the owner had turned off.
         */
        Setting::query()->where('group', '!=', Features::GROUP)->delete();
        Setting::forgetCache();

        $this->line('  Doctor profile and site settings cleared (visibility switches kept)');
    }
}
