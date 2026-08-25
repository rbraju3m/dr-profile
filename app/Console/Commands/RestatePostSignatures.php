<?php

namespace App\Console\Commands;

use App\Models\DoctorProfile;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rewrites the signature block at the foot of every post that carries one.
 *
 * Three posts were pasted here from social media, bringing a signature with
 * them that said "Assistant Professor (Spine Surgery), NITOR" while the profile,
 * the short bio and the current experience credential all said Resident Surgeon.
 * He has confirmed Resident Surgeon is the post he holds, so the pasted copy is
 * the stale one.
 *
 * They arrived as plain text in a field rendered with {!! !!}, so every line
 * after the first collapsed into the paragraph before it and the signature read
 * as the tail of a sentence. Each post is rebuilt as paragraphs, and all of them
 * are given the same block — the three held three spellings of it between them,
 * which is how it drifted from the profile without anyone noticing.
 *
 * The block is written out literally rather than generated from the profile.
 * Generating it is the durable fix and would stop this recurring, but the
 * profile spells his degrees in a different order and spacing than he does when
 * he signs, and changing how he signs is not this command's decision to make.
 *
 * Safe to run twice: a post already carrying the block is rebuilt to the same
 * thing, so the second run reports no change rather than eating the body.
 */
class RestatePostSignatures extends Command
{
    protected $signature = 'posts:signature
        {--dry-run : Report which posts would change without writing anything}';

    protected $description = 'Restate the signature at the foot of every post that carries one';

    /** What every signed post should end with, in both languages. */
    private const SIGNATURE = [
        'Dr. Shaikh Saadiul Islam',
        'MBBS(DMC), BCS(H), MRCS(A) UK, FCPS(Ortho), MS(Ortho), FACS(USA)',
        'Resident Surgeon (R.S.), NITOR',
    ];

    public function handle(): int
    {
        // The name is what marks where the body ends and the signature begins,
        // and it is the one part of the block already in the database.
        $marker = DoctorProfile::current()?->tr('name', 'en');

        if (blank($marker)) {
            $this->error('The profile has no English name, so there is nothing to find the signature by.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        if ($dry) {
            $this->warn('Dry run — nothing will be written.');
        }

        $changed = 0;

        DB::transaction(function () use ($marker, $dry, &$changed) {
            foreach (Post::all() as $post) {
                $touched = [];

                foreach (['content_en', 'content_bn'] as $column) {
                    $before = (string) $post->{$column};

                    if (! str_contains($before, $marker)) {
                        continue;
                    }

                    $after = $this->rebuild($before, $marker);

                    if ($after === $before) {
                        continue;
                    }

                    $post->{$column} = $after;
                    $touched[] = $column === 'content_en' ? 'en' : 'bn';
                }

                if (! $touched) {
                    continue;
                }

                $this->line('  '.$post->slug.'  ('.implode(', ', $touched).')');
                $changed++;

                if (! $dry) {
                    $post->save();
                }
            }
        });

        $this->newLine();

        $this->info(match (true) {
            $changed === 0 => 'Every signed post already reads the same. Nothing to do.',
            $dry => "{$changed} post(s) would be rewritten.",
            default => "{$changed} post(s) rewritten.",
        });

        return self::SUCCESS;
    }

    /** Keep the post's own words, replace everything from the name onwards. */
    private function rebuild(string $content, string $marker): string
    {
        $body = [];

        foreach ($this->paragraphs($content) as $paragraph) {
            if (str_contains($paragraph, $marker)) {
                break;
            }

            $body[] = $paragraph;
        }

        return collect($body)->concat(self::SIGNATURE)
            ->map(fn (string $line) => '<p>'.$line.'</p>')
            ->implode('');
    }

    /**
     * Posts arrive here in two shapes: pasted plain text with line breaks, and
     * the paragraphs this command leaves behind. Reading only one of them is
     * what would make a second run destructive — the whole post is a single
     * line once it is HTML, so the name would be found in it and the body
     * thrown away with the signature.
     *
     * @return array<int, string>
     */
    private function paragraphs(string $content): array
    {
        if (preg_match_all('#<p[^>]*>(.*?)</p>#si', $content, $matches)) {
            $lines = $matches[1];
        } else {
            $lines = preg_split('/\R/u', $content) ?: [];
        }

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }
}
