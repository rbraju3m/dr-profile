<?php

namespace Tests\Feature;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * TranslationParityTest catches a key that exists in one language and not the
 * other, which prints the key itself onto the page. This catches the other end
 * of the same defect: a key translated into both languages that nothing ever
 * asks for.
 *
 * It is the shape FeatureRegistryTest already guards for the visibility
 * switches — a name read but unregistered, or registered and read nowhere —
 * and it earns its place for the same reason. Sixteen of these had collected:
 * a dashboard summary written twice over, a heading for a band that was never
 * built, and — worth more than the tidying — "Next sitting", translated into
 * both languages while the chamber card went on labelling that date "Select a
 * date". An orphaned key is often a wired-up string pointing at a missing one.
 */
class TranslationUsageTest extends TestCase
{
    /** Only English is scanned; parity is what keeps Bangla in step. */
    private const LOCALE = 'en';

    /**
     * Files whose keys nothing here names, because the framework looks them up
     * by rule name — "required", "max.string" — from inside the validator.
     * Scanning for a literal would report all 137 of them as orphaned.
     */
    private const RESOLVED_BY_THE_FRAMEWORK = ['validation'];

    /** Cached between the assertions below — the scan reads every source file. */
    private static ?array $scan = null;

    public function test_every_translated_string_is_asked_for_somewhere(): void
    {
        $used = $this->literals();
        $prefixes = $this->prefixes();

        $orphans = array_values(array_filter(
            array_keys($this->defined()),
            fn (string $key) => ! isset($used[$key])
                && ! array_filter($prefixes, fn (string $p) => str_starts_with($key, $p)),
        ));

        sort($orphans);

        $this->assertSame([], $orphans, count($orphans)." translated string(s) that nothing reads:\n  ".implode("\n  ", $orphans));
    }

    /** Sanity: the scanner finds real usages, so an empty result means something. */
    public function test_the_scan_actually_finds_keys(): void
    {
        $this->assertArrayHasKey('site.nav.home', $this->literals());
        $this->assertGreaterThan(300, count($this->defined()));
        $this->assertContains('site.months.', $this->prefixes());

        // The exclusion is real, not a name that no longer matches a file.
        foreach (self::RESOLVED_BY_THE_FRAMEWORK as $file) {
            $this->assertFileExists(lang_path(self::LOCALE."/{$file}.php"));
        }
    }

    /** Every key in lang/en, flattened to dotted form. */
    private function defined(): array
    {
        $flatten = function (array $values, string $prefix) use (&$flatten): array {
            $out = [];
            foreach ($values as $key => $value) {
                $dotted = $prefix.'.'.$key;
                $out += is_array($value) ? $flatten($value, $dotted) : [$dotted => true];
            }

            return $out;
        };

        $keys = [];

        foreach (Finder::create()->files()->in(lang_path(self::LOCALE))->name('*.php') as $file) {
            if (in_array($file->getFilenameWithoutExtension(), self::RESOLVED_BY_THE_FRAMEWORK, true)) {
                continue;
            }

            $keys += $flatten(require $file->getRealPath(), $file->getFilenameWithoutExtension());
        }

        return $keys;
    }

    /** Whole keys named outright: __('site.nav.home'). */
    private function literals(): array
    {
        $found = [];

        foreach ($this->sources() as $contents) {
            preg_match_all('/[\'"]('.$this->namespaces().'\.[a-z0-9_.]+)[\'"]/i', $contents, $matches);

            foreach ($matches[1] as $key) {
                if (! str_ends_with($key, '.')) {
                    $found[$key] = true;
                }
            }
        }

        return $found;
    }

    /**
     * Keys built at runtime, reduced to the part that is written down:
     * __('site.months.'.$month) and __("site.home.step_{$n}_title") both mean
     * every key beneath what precedes the variable.
     *
     * @return list<string>
     */
    private function prefixes(): array
    {
        $found = [];

        foreach ($this->sources() as $contents) {
            preg_match_all('/[\'"]('.$this->namespaces().'\.[a-z0-9_.]*\.)[\'"]/i', $contents, $concatenated);
            preg_match_all('/"('.$this->namespaces().'\.[a-z0-9_.]*?)\{/i', $contents, $interpolated);

            foreach (array_merge($concatenated[1], $interpolated[1]) as $prefix) {
                $found[$prefix] = true;
            }
        }

        return array_keys($found);
    }

    /**
     * The lang files present, as a regex alternation. Taken from the directory
     * rather than written out, so a new lang file is covered the day it lands.
     */
    private function namespaces(): string
    {
        $names = array_map(
            fn ($file) => preg_quote($file->getFilenameWithoutExtension(), '/'),
            iterator_to_array(Finder::create()->files()->in(lang_path(self::LOCALE))->name('*.php'), false),
        );

        return '(?:'.implode('|', $names).')';
    }

    /** @return list<string> */
    private function sources(): array
    {
        if (self::$scan !== null) {
            return self::$scan;
        }

        $files = Finder::create()
            ->files()
            ->in([app_path(), resource_path('views'), base_path('routes'), base_path('bootstrap')])
            ->name('*.php');

        return self::$scan = array_map(fn ($file) => $file->getContents(), iterator_to_array($files, false));
    }
}
