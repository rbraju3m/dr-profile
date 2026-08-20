<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The two locales must stay key-for-key identical. A key present in one and
 * missing from the other does not fail loudly — Laravel prints the key itself,
 * so `admin.common.role` appears on the page and nobody notices until a reader
 * does.
 */
class TranslationParityTest extends TestCase
{
    /** @return list<string> dotted keys, depth first */
    private function flatten(array $items, string $prefix = ''): array
    {
        $keys = [];

        foreach ($items as $key => $value) {
            $dotted = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            $keys = array_merge($keys, is_array($value) ? $this->flatten($value, $dotted) : [$dotted]);
        }

        return $keys;
    }

    /**
     * Data providers run before the application boots, so the path is built by
     * hand rather than through lang_path().
     */
    public static function fileProvider(): array
    {
        $files = [];

        foreach (glob(dirname(__DIR__, 2).'/lang/en/*.php') as $path) {
            $name = basename($path, '.php');
            $files[$name] = [$name];
        }

        return $files;
    }

    #[DataProvider('fileProvider')]
    public function test_both_languages_carry_the_same_keys(string $file): void
    {
        $this->assertFileExists(lang_path("bn/{$file}.php"), "lang/bn/{$file}.php is missing entirely.");

        $en = $this->flatten(require lang_path("en/{$file}.php"));
        $bn = $this->flatten(require lang_path("bn/{$file}.php"));

        sort($en);
        sort($bn);

        $this->assertSame([], array_values(array_diff($en, $bn)), "Missing from lang/bn/{$file}.php");
        $this->assertSame([], array_values(array_diff($bn, $en)), "Missing from lang/en/{$file}.php");
    }

    /** An empty string is a key that translates to nothing on the page. */
    #[DataProvider('fileProvider')]
    public function test_no_string_is_left_blank(string $file): void
    {
        foreach (['en', 'bn'] as $locale) {
            $strings = require lang_path("{$locale}/{$file}.php");

            foreach ($this->flatten($strings) as $key) {
                $value = data_get($strings, $key);

                $this->assertNotSame('', trim((string) $value), "lang/{$locale}/{$file}.php: {$key} is blank.");
            }
        }
    }
}
