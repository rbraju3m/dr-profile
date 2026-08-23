<?php

namespace App\Console\Commands;

use App\Models\Chamber;
use App\Models\ChamberSchedule;
use App\Models\Credential;
use App\Models\DoctorProfile;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Stat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads the practice's real details from content/doctor.yml.
 *
 * Safe to run repeatedly: everything is matched on slug (or on the singleton
 * profile row) and updated in place, so re-importing an edited file corrects
 * the existing records instead of duplicating them.
 */
class ImportDoctorDetails extends Command
{
    protected $signature = 'doctor:import
        {--file=content/doctor.yml : Path to the details file}
        {--dry-run : Report what would change without writing anything}';

    protected $description = "Import the doctor's details from a YAML file";

    /** Placeholder text the template ships with; never written to the database. */
    private const PLACEHOLDER = 'REPLACE ME';

    private array $skipped = [];

    public function handle(): int
    {
        $path = base_path($this->option('file'));

        if (! is_file($path)) {
            $this->error("No such file: {$path}");
            $this->line('Copy content/doctor.yml, fill it in, then run this again.');

            return self::FAILURE;
        }

        try {
            $data = Yaml::parseFile($path) ?? [];
        } catch (ParseException $e) {
            $this->error('That file is not valid YAML: '.$e->getMessage());

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        if ($dry) {
            $this->warn('Dry run — nothing will be written.');
        }

        DB::beginTransaction();

        try {
            $this->importProfile($data['profile'] ?? []);
            $this->importChambers($data['chambers'] ?? []);
            $this->importServices($data['services'] ?? []);
            $this->importCredentials($data['credentials'] ?? []);
            $this->importStats($data['stats'] ?? []);
            $this->importFaqs($data['faqs'] ?? []);
            $this->importPages($data['pages'] ?? []);
            $this->importSettings($data['settings'] ?? []);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed, nothing was changed: '.$e->getMessage());

            return self::FAILURE;
        }

        $dry ? DB::rollBack() : DB::commit();

        if ($this->skipped) {
            $this->newLine();
            $this->warn('Left untouched because they still hold template text:');
            foreach ($this->skipped as $field) {
                $this->line('  · '.$field);
            }
        }

        $this->newLine();
        $this->info($dry ? 'Dry run complete.' : 'Import complete.');

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- pieces

    private function importProfile(array $profile): void
    {
        if (! $profile) {
            return;
        }

        $clean = $this->clean($profile, 'profile');

        if (! $clean) {
            return;
        }

        DoctorProfile::query()->firstOrNew([])->fill($clean)->save();
        DoctorProfile::forgetCache();

        $this->line('  profile      updated ('.count($clean).' fields)');
    }

    private function importChambers(array $chambers): void
    {
        foreach ($chambers as $i => $row) {
            $schedules = $row['schedules'] ?? [];
            unset($row['schedules']);

            $clean = $this->clean($row, "chambers[$i]");

            if (blank($clean['slug'] ?? null) || blank($clean['name_en'] ?? null)) {
                continue;
            }

            $clean['slug'] = Str::slug($clean['slug']);
            $clean['sort_order'] = $i;
            $clean['is_active'] = true;

            $chamber = Chamber::updateOrCreate(['slug' => $clean['slug']], $clean);

            // Replace the weekly pattern wholesale — the file is the source of truth.
            $chamber->schedules()->delete();

            foreach ($schedules as $sitting) {
                $start = $sitting['start'] ?? '17:00';
                $end = $sitting['end'] ?? '20:00';

                // The admin form refuses a sitting that ends before it starts;
                // the file has to be held to the same rule, or it writes a row
                // that offers no slots and says nothing about why.
                if (strtotime($end) <= strtotime($start)) {
                    throw new \RuntimeException(
                        "chamber “{$clean['slug']}” has a sitting from {$start} to {$end}; a sitting must end after it starts."
                    );
                }

                ChamberSchedule::create([
                    'chamber_id' => $chamber->id,
                    'day_of_week' => (int) ($sitting['day'] ?? 0),
                    'start_time' => $start,
                    'end_time' => $end,
                    'slot_minutes' => (int) ($sitting['slot_minutes'] ?? 20),
                    'max_patients' => $sitting['max_patients'] ?? null,
                    'is_active' => true,
                ]);
            }

            $this->line('  chamber      '.$chamber->slug.' ('.count($schedules).' sittings)');
        }
    }

    private function importServices(array $services): void
    {
        foreach ($services as $i => $row) {
            $clean = $this->clean($row, "services[$i]");

            if (blank($clean['slug'] ?? null) || blank($clean['name_en'] ?? null)) {
                continue;
            }

            $clean['slug'] = Str::slug($clean['slug']);
            $clean['sort_order'] = $i;
            $clean['is_active'] = true;

            Service::updateOrCreate(['slug' => $clean['slug']], $clean);
            $this->line('  service      '.$clean['slug']);
        }
    }

    private function importCredentials(array $credentials): void
    {
        $written = 0;

        foreach ($credentials as $i => $row) {
            $clean = $this->clean($row, "credentials[$i]");

            if (blank($clean['title_en'] ?? null)) {
                continue;
            }

            Credential::updateOrCreate(
                ['type' => $clean['type'] ?? 'education', 'title_en' => $clean['title_en']],
                $clean + ['sort_order' => $i, 'is_active' => true]
            );
            $written++;
        }

        if ($written) {
            $this->line("  credentials  {$written} written");
        }
    }

    private function importStats(array $stats): void
    {
        foreach ($stats as $i => $row) {
            $clean = $this->clean($row, "stats[$i]");

            if (blank($clean['label_en'] ?? null)) {
                continue;
            }

            Stat::updateOrCreate(
                ['label_en' => $clean['label_en']],
                $clean + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }

    private function importFaqs(array $faqs): void
    {
        $written = 0;

        foreach ($faqs as $i => $row) {
            $clean = $this->clean($row, "faqs[$i]");

            if (blank($clean['question_en'] ?? null)) {
                continue;
            }

            Faq::updateOrCreate(
                ['question_en' => $clean['question_en']],
                $clean + ['group' => $clean['group'] ?? 'general', 'sort_order' => $i, 'is_active' => true]
            );
            $written++;
        }

        if ($written) {
            $this->line("  faqs         {$written} written");
        }
    }

    private function importPages(array $pages): void
    {
        foreach ($pages as $row) {
            $clean = $this->clean($row, 'pages');

            if (blank($clean['slug'] ?? null) || blank($clean['title_en'] ?? null)) {
                continue;
            }

            $clean['slug'] = Str::slug($clean['slug']);

            Page::updateOrCreate(['slug' => $clean['slug']], $clean + ['is_published' => true]);
            $this->line('  page         '.$clean['slug']);
        }
    }

    private function importSettings(array $settings): void
    {
        $written = 0;

        foreach ($this->clean($settings, 'settings') as $key => $value) {
            Setting::put($key, $value);
            $written++;
        }

        Setting::forgetCache();

        if ($written) {
            $this->line("  settings     {$written} keys");
        }
    }

    /**
     * Drop empty values and anything still carrying the template's placeholder,
     * recording the latter so the operator is told rather than left guessing.
     */
    private function clean(array $row, string $context): array
    {
        $out = [];

        foreach ($row as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                continue;
            }

            if (is_string($value) && str_contains($value, self::PLACEHOLDER)) {
                $this->skipped[] = "{$context}.{$key}";

                continue;
            }

            if (is_string($value) && str_contains(Str::upper($value), 'REPLACE-ME')) {
                $this->skipped[] = "{$context}.{$key}";

                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }
}
