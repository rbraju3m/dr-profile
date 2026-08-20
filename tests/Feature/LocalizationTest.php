<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Support\Number;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_url_redirects_to_a_locale(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_an_unknown_locale_is_not_routed(): void
    {
        $this->get('/fr')->assertNotFound();
    }

    public function test_the_html_lang_attribute_follows_the_url(): void
    {
        $this->get('/en')->assertOk()->assertSee('<html lang="en"', false);
        $this->get('/bn')->assertOk()->assertSee('<html lang="bn"', false);
    }

    public function test_ui_strings_are_translated(): void
    {
        $this->get('/en/contact')->assertOk()->assertSee('Send Message');
        $this->get('/bn/contact')->assertOk()->assertSee('বার্তা পাঠান');
    }

    public function test_database_content_follows_the_locale(): void
    {
        Service::create([
            'slug' => 'echo',
            'name_en' => 'Echocardiography',
            'name_bn' => 'ইকোকার্ডিওগ্রাফি',
            'is_active' => true,
        ]);

        $this->get('/en/expertise')->assertOk()->assertSee('Echocardiography');
        $this->get('/bn/expertise')->assertOk()->assertSee('ইকোকার্ডিওগ্রাফি');
    }

    /** An empty Bangla column must fall back to English rather than render blank. */
    public function test_missing_bangla_content_falls_back_to_english(): void
    {
        $service = Service::create([
            'slug' => 'pacemaker',
            'name_en' => 'Pacemaker Implantation',
            'name_bn' => null,
            'is_active' => true,
        ]);

        app()->setLocale('bn');

        $this->assertSame('Pacemaker Implantation', $service->tr('name'));
        $this->assertSame('Pacemaker Implantation', $service->name);
    }

    public function test_numerals_are_localised_for_bangla(): void
    {
        $this->assertSame('2026', Number::localizeDigits('2026', 'en'));
        $this->assertSame('২০২৬', Number::localizeDigits('2026', 'bn'));
        $this->assertSame('৳ ১,২০০', Number::money(1200, 'bn'));
        $this->assertSame('৳ 1,200', Number::money(1200, 'en'));
    }

    public function test_pages_advertise_both_languages_to_search_engines(): void
    {
        $this->get('/en/faq')
            ->assertOk()
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="bn"', false);
    }

    /** The locale prefix must not be passed to the controller as an argument. */
    public function test_route_model_binding_works_under_the_locale_prefix(): void
    {
        Service::create([
            'slug' => 'holter',
            'name_en' => 'Holter Monitoring',
            'is_active' => true,
        ]);

        $this->get('/en/expertise/holter')->assertOk()->assertSee('Holter Monitoring');
        $this->get('/bn/expertise/holter')->assertOk();
    }
}
