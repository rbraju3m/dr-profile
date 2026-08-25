<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\SuccessStory;
use App\Models\Testimonial;
use App\Support\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

    /**
     * A patient's name was the one piece of content on these two tables kept in
     * a single column, so a story credited to মুগ্ধ was credited to মুগ্ধ on the
     * English page too — and it is the field on those forms a Bengali speaker
     * is likeliest to type in Bengali.
     */
    public function test_a_patients_name_follows_the_locale(): void
    {
        SuccessStory::create([
            'slug' => 'walked-again',
            'title_en' => 'Walked Again', 'title_bn' => 'আবার হাঁটলেন',
            'patient_name_en' => 'Mugdho', 'patient_name_bn' => 'মুগ্ধ',
            'is_published' => true, 'published_at' => now()->subDay(),
        ]);

        Testimonial::create([
            'patient_name_en' => 'Nasima Khatun', 'patient_name_bn' => 'নাসিমা খাতুন',
            'content_en' => 'He told me which test could wait.',
            'content_bn' => 'কোন পরীক্ষা এখন না করলেও চলবে তিনি বলে দিয়েছেন।',
            'rating' => 5, 'is_published' => true,
        ]);

        $this->get('/en/success-stories/walked-again')->assertOk()
            ->assertSee('Mugdho')->assertDontSee('মুগ্ধ', escape: false);
        $this->get('/bn/success-stories/walked-again')->assertOk()
            ->assertSee('মুগ্ধ', escape: false)->assertDontSee('Mugdho');

        $this->get('/en')->assertOk()
            ->assertSee('Nasima Khatun')->assertDontSee('নাসিমা খাতুন', escape: false);
        $this->get('/bn')->assertOk()
            ->assertSee('নাসিমা খাতুন', escape: false)->assertDontSee('Nasima Khatun');
    }

    /**
     * The same wiring checked from both ends, as the feature registry is: a
     * column pair the model never declares is never resolved for the reader's
     * locale, and a declared field with no pair behind it resolves to nothing
     * at all. `patient_name` was the first kind for as long as the two tables
     * had existed, and looked finished from the admin form.
     */
    public function test_every_column_pair_is_declared_and_every_declaration_has_its_pair(): void
    {
        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            $model = new $class;

            if (! $model instanceof Model || ! Schema::hasTable($model->getTable())) {
                continue;
            }

            $table = $model->getTable();
            $declared = method_exists($model, 'translatableFields') ? $model->translatableFields() : [];

            $paired = [];
            foreach (Schema::getColumnListing($table) as $column) {
                if (str_ends_with($column, '_en')) {
                    $paired[] = substr($column, 0, -3);
                }
            }

            $this->assertSame([], array_values(array_diff($paired, $declared)),
                "{$class} has an English column whose base name is not in \$translatable, so it never follows the locale.");

            foreach ($declared as $field) {
                $this->assertTrue(Schema::hasColumn($table, $field.'_en'),
                    "{$class} declares {$field} translatable but has no {$field}_en column.");
                $this->assertTrue(Schema::hasColumn($table, $field.'_bn'),
                    "{$class} declares {$field} translatable but has no {$field}_bn column.");
            }
        }
    }
}
