<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Services\MediaService;
use App\Support\Uploads;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The doctor profile is a singleton, so there is no index or create —
 * just one long edit form.
 */
class ProfileController extends Controller
{
    private const MEDIA = [
        'photo' => 'profile',
        'hero_image' => 'profile',
        'og_image' => 'profile',
        'cv_file' => 'profile',
    ];

    public function edit(): View
    {
        return view('admin.profile.edit', [
            'profile' => DoctorProfile::current(),
        ]);
    }

    public function update(Request $request, MediaService $media): RedirectResponse
    {
        $profile = DoctorProfile::query()->firstOrNew([]);

        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:150'],
            'name_bn' => ['nullable', 'string', 'max:150'],
            'title_en' => ['nullable', 'string', 'max:60'],
            'title_bn' => ['nullable', 'string', 'max:60'],
            'designation_en' => ['nullable', 'string', 'max:200'],
            'designation_bn' => ['nullable', 'string', 'max:200'],
            'tagline_en' => ['nullable', 'string', 'max:250'],
            'tagline_bn' => ['nullable', 'string', 'max:250'],
            'degrees_en' => ['nullable', 'string', 'max:400'],
            'degrees_bn' => ['nullable', 'string', 'max:400'],
            'short_bio_en' => ['nullable', 'string', 'max:800'],
            'short_bio_bn' => ['nullable', 'string', 'max:800'],
            'bio_en' => ['nullable', 'string'],
            'bio_bn' => ['nullable', 'string'],
            'philosophy_en' => ['nullable', 'string'],
            'philosophy_bn' => ['nullable', 'string'],
            'gender' => ['nullable', 'in:male,female,other'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bmdc_reg_no' => ['nullable', 'string', 'max:60'],
            'languages_en' => ['nullable', 'string', 'max:200'],
            'languages_bn' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'hotline' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'facebook_url' => ['nullable', 'url', 'max:300'],
            'youtube_url' => ['nullable', 'url', 'max:300'],
            'linkedin_url' => ['nullable', 'url', 'max:300'],
            'instagram_url' => ['nullable', 'url', 'max:300'],
            'tiktok_url' => ['nullable', 'url', 'max:300'],
            'x_url' => ['nullable', 'url', 'max:300'],
            'meta_title_en' => ['nullable', 'string', 'max:180'],
            'meta_title_bn' => ['nullable', 'string', 'max:180'],
            'meta_description_en' => ['nullable', 'string', 'max:300'],
            'meta_description_bn' => ['nullable', 'string', 'max:300'],
            'photo' => Uploads::imageRules(),
            'hero_image' => Uploads::imageRules(),
            'og_image' => Uploads::imageRules(),
            'cv_file' => Uploads::pdfRules(),
        ]);

        foreach (self::MEDIA as $field => $folder) {
            $data[$field] = $media->replace(
                $request->file($field),
                $profile->{$field},
                $folder,
                $request->boolean("remove_{$field}"),
            );
        }

        $profile->fill(array_map(fn ($v) => $v === '' ? null : $v, $data))->save();

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', __('admin.flash.saved'));
    }
}
