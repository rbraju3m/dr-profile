<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Lang;

abstract class Controller
{
    /**
     * What a long-form field may hold.
     *
     * Every rich-text column in this schema is a longText, so the database was
     * never the limit — and neither was anything else. A paste could arrive as
     * large as the server would accept, which under Apache here is 5G. Fifty
     * thousand characters is many times longer than anything this site
     * publishes, and is still a bound.
     */
    protected const LONG_TEXT = ['nullable', 'string', 'max:50000'];

    /** The same, where the field cannot be left empty. */
    protected const LONG_TEXT_REQUIRED = ['required', 'string', 'max:50000'];

    /**
     * Name the fields in the language the operator is working in.
     *
     * Left to itself the validator humanises the column — "The name en field is
     * required." — which is neither English nor Bangla, and in the Bangla panel
     * reads as a translation that gave up halfway. The labels the forms already
     * put above these controls live in admin.fields.*, keyed by the base column
     * name, so anything with an entry there gets named properly and anything
     * without keeps the old fallback.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, string>
     */
    protected function attributeNames(array $rules): array
    {
        $names = [];

        foreach (array_keys($rules) as $field) {
            $key = 'admin.fields.'.preg_replace('/_(en|bn)$/', '', $field);

            if (! Lang::has($key)) {
                continue;
            }

            // A bilingual pair needs to say which half it is talking about.
            $names[$field] = __($key).match (true) {
                str_ends_with($field, '_en') => ' ('.__('admin.common.english').')',
                str_ends_with($field, '_bn') => ' ('.__('admin.common.bangla').')',
                default => '',
            };
        }

        return $names;
    }
}
