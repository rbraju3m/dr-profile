<?php

namespace Tests\Feature;

use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * The framework's own wording — required, unique, max — is translated in
 * lang/*_/validation.php. It used not to be, in either direction: there was no
 * such file, so every rule the validator raised came out in English however the
 * panel was switched, and TranslationParityTest could not see it because the
 * absence was symmetric.
 *
 * The second half here is the placeholder check, which is what turned the
 * translation up as a bug rather than a gap: this application registers its own
 * replacer for `max`, to explain an oversized upload in bytes. A replacer
 * stands in for the framework's, so the one case it declined to handle — every
 * non-file limit in the application — was printing ":max" at the reader.
 */
class ValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    /** A spread wide enough to cover the rules this application actually uses. */
    private const CASES = [
        ['required', [], ['f' => 'required']],
        ['email', ['f' => 'nope'], ['f' => 'email']],
        ['max.string', ['f' => 'abcdefghij'], ['f' => 'max:5']],
        ['max.numeric', ['f' => 99], ['f' => 'numeric|max:10']],
        ['max.array', ['f' => [1, 2, 3]], ['f' => 'array|max:2']],
        ['min.string', ['f' => 'ab'], ['f' => 'min:5']],
        ['between.numeric', ['f' => 500], ['f' => 'numeric|between:0,130']],
        ['in', ['f' => 'zzz'], ['f' => 'in:new,followup']],
        ['integer', ['f' => 'x'], ['f' => 'integer']],
        ['numeric', ['f' => 'x'], ['f' => 'numeric']],
        ['url', ['f' => 'nope'], ['f' => 'url']],
        ['date', ['f' => 'nope'], ['f' => 'date']],
        ['date_format', ['f' => 'nope'], ['f' => 'date_format:Y-m-d']],
        ['boolean', ['f' => 'maybe'], ['f' => 'boolean']],
        ['string', ['f' => 123], ['f' => 'string']],
        ['size.string', ['f' => 'ab'], ['f' => 'size:5']],
        ['required_if', ['g' => 1], ['f' => 'required_if:g,1']],
        ['confirmed', ['f' => 'a'], ['f' => 'confirmed']],
        ['regex', ['f' => 'x'], ['f' => 'regex:/^\d+$/']],
        ['digits', ['f' => '12'], ['f' => 'digits:5']],
    ];

    /** @return list<string> */
    private function messagesIn(string $locale): array
    {
        $this->app->setLocale($locale);

        return array_map(
            fn (array $case) => Validator::make($case[1], $case[2])->errors()->first(),
            self::CASES,
        );
    }

    public function test_no_message_shows_a_placeholder_it_meant_to_fill(): void
    {
        foreach (['en', 'bn'] as $locale) {
            foreach (array_combine(array_column(self::CASES, 0), $this->messagesIn($locale)) as $rule => $message) {
                $this->assertNotSame('', $message, "{$rule} produced no message in {$locale}");
                $this->assertDoesNotMatchRegularExpression(
                    '/:(attribute|value|values|other|min|max|size|date|format|digits|decimal|input|encoding)\b/',
                    $message,
                    "{$rule} left a placeholder unfilled in {$locale}: {$message}",
                );
            }
        }
    }

    public function test_the_framework_wording_is_translated(): void
    {
        $english = $this->messagesIn('en');
        $bangla = $this->messagesIn('bn');

        foreach (self::CASES as $i => [$rule]) {
            $this->assertNotSame($english[$i], $bangla[$i], "{$rule} reads the same in both languages");
            $this->assertMatchesRegularExpression('/\p{Bengali}/u', $bangla[$i], "{$rule} has no Bangla in it: {$bangla[$i]}");
        }
    }

    /**
     * Bangla writes its own numerals. A message that reads "অন্তত 3 অক্ষরের" is
     * Bangla wording wrapped around a Latin numeral, which is exactly what
     * App\Support\Number exists to prevent — and the validator substitutes its
     * :min and :max straight from the rule, so it has to be done on the way out.
     */
    public function test_numbers_inside_a_bangla_message_are_written_in_bangla(): void
    {
        $this->app->setLocale('bn');

        $numeric = [
            'min' => [['f' => 'ab'], ['f' => 'min:5'], '৫'],
            'max' => [['f' => 'abcdefghij'], ['f' => 'max:5'], '৫'],
            'between' => [['f' => 500], ['f' => 'numeric|between:0,130'], '১৩০'],
            'size' => [['f' => 'ab'], ['f' => 'size:5'], '৫'],
            'digits' => [['f' => '12'], ['f' => 'digits:5'], '৫'],
        ];

        foreach ($numeric as $rule => [$data, $rules, $expected]) {
            $message = Validator::make($data, $rules)->errors()->first();

            $this->assertStringContainsString($expected, $message, "{$rule} did not localise its numeral: {$message}");
            $this->assertDoesNotMatchRegularExpression('/[0-9]/', $message, "{$rule} left a Latin numeral in: {$message}");
        }
    }

    /** And English keeps its own. */
    public function test_numbers_inside_an_english_message_are_left_alone(): void
    {
        $this->app->setLocale('en');

        $this->assertStringContainsString('5', Validator::make(['f' => 'ab'], ['f' => 'min:5'])->errors()->first());
        $this->assertStringContainsString('130', Validator::make(['f' => 500], ['f' => 'numeric|between:0,130'])->errors()->first());
    }

    /** The upload replacer still does the job it was written for. */
    public function test_an_oversized_upload_is_still_explained_in_bytes(): void
    {
        $this->app->setLocale('en');

        $message = Validator::make(
            ['photo' => UploadedFile::fake()->create('big.jpg', Uploads::maxKilobytes() + 512, 'image/jpeg')],
            ['photo' => Uploads::imageRules()],
        )->errors()->first();

        $this->assertStringContainsString(Uploads::maxLabel(), $message);
        $this->assertStringNotContainsString(':max', $message);
    }
}
