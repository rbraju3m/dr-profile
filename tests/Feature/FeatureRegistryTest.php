<?php

namespace Tests\Feature;

use App\Support\Features;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * `Features::enabled()` treats a name it does not know as "on", so a mistyped
 * switch would quietly become a control that does nothing — the defect this
 * codebase keeps meeting. These tests are where the typo surfaces instead.
 */
class FeatureRegistryTest extends TestCase
{
    /** Every `@feature('x')`, `feature('x')` and `feature:x` used anywhere. */
    private function referencedKeys(): array
    {
        $sources = [base_path('routes/web.php')];

        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            $sources[] = $file->getRealPath();
        }

        // Switches are read from PHP as well — Theme::switchable() is one.
        foreach (Finder::create()->files()->in(app_path())->name('*.php') as $file) {
            $sources[] = $file->getRealPath();
        }

        $keys = [];

        foreach ($sources as $path) {
            $body = file_get_contents($path);

            preg_match_all("/@?feature\('([a-z_]+)'\)/", $body, $inline);
            preg_match_all("/'feature:([a-z_]+)'/", $body, $middleware);
            preg_match_all("/Features::enabled\('([a-z_]+)'\)/", $body, $calls);

            foreach (array_merge($inline[1], $middleware[1], $calls[1]) as $key) {
                $keys[$key][] = str_replace(base_path().'/', '', $path);
            }
        }

        return $keys;
    }

    public function test_every_switch_the_site_asks_about_is_registered(): void
    {
        foreach ($this->referencedKeys() as $key => $files) {
            $this->assertTrue(
                Features::has($key),
                "Unknown feature '{$key}' used in ".implode(', ', array_unique($files))
            );
        }
    }

    /** A switch nothing reads is a control that does nothing — the other half. */
    public function test_every_registered_switch_governs_something(): void
    {
        $referenced = array_keys($this->referencedKeys());

        foreach (Features::keys() as $key) {
            $this->assertContains($key, $referenced, "Feature '{$key}' is offered in the admin but read nowhere.");
        }
    }

    public function test_every_switch_is_labelled_in_both_languages(): void
    {
        foreach (['en', 'bn'] as $locale) {
            $labels = require lang_path("{$locale}/admin.php");

            foreach (Features::keys() as $key) {
                $this->assertArrayHasKey($key, $labels['visibility']['features'], "Missing {$locale} label for '{$key}'.");
                $this->assertNotSame('', trim($labels['visibility']['features'][$key]));
            }

            foreach (array_keys(Features::groups()) as $group) {
                $this->assertArrayHasKey($group, $labels['visibility']['groups']);
                $this->assertArrayHasKey($group, $labels['visibility']['group_intro']);
            }
        }
    }

    public function test_a_switch_that_depends_on_another_follows_it_down(): void
    {
        foreach (Features::groups() as $features) {
            foreach ($features as $key => $feature) {
                foreach ($feature['requires'] as $parent) {
                    $this->assertTrue(Features::has($parent), "'{$key}' requires unknown feature '{$parent}'.");
                }
            }
        }
    }
}
