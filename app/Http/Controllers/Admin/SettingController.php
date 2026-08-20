<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * The editable set is declared here rather than read from the table, so a
     * stray row cannot introduce an unexpected field into the form.
     */
    public const FIELDS = [
        'general' => [
            'site_name_en' => 'text',
            'site_name_bn' => 'text',
            'footer_note_en' => 'textarea',
            'footer_note_bn' => 'textarea',
        ],
        'contact' => [
            'contact_email' => 'text',
            'contact_phone' => 'text',
            'contact_hotline' => 'text',
            'contact_address_en' => 'textarea',
            'contact_address_bn' => 'textarea',
        ],
        'booking' => [
            'appointment_notice_en' => 'textarea',
            'appointment_notice_bn' => 'textarea',
        ],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'groups' => self::FIELDS,
            'values' => Setting::map(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = collect(self::FIELDS)->flatMap(fn ($fields, $group) => array_keys($fields));

        $data = $request->validate(
            $keys->mapWithKeys(fn (string $key) => [$key => ['nullable', 'string', 'max:1000']])->all()
        );

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $key => $type) {
                Setting::put($key, $data[$key] ?? null, $group, $type);
            }
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', __('admin.flash.saved'));
    }
}
