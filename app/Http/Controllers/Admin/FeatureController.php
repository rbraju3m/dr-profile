<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Features;
use App\Support\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * One screen holding every show/hide switch for the public site.
 *
 * The editable set comes from App\Support\Features rather than from the
 * request, so a hand-crafted post cannot introduce a switch the site never
 * reads — or write to a settings key that is not a switch at all.
 */
class FeatureController extends Controller
{
    public function edit(): View
    {
        return view('admin.visibility.edit', [
            'groups' => Features::groups(),
            'theme' => Theme::default(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'features' => ['array'],
            'features.*' => ['boolean'],
            'theme_default' => ['required', 'string', 'in:'.implode(',', Theme::CHOICES)],
        ]);

        $submitted = $request->input('features', []);

        foreach (Features::keys() as $key) {
            Setting::put(
                Features::PREFIX.$key,
                filter_var($submitted[$key] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
                Features::GROUP,
                'boolean',
            );
        }

        Setting::put(Theme::SETTING, $data['theme_default'], 'appearance', 'text');

        return redirect()
            ->route('admin.visibility.edit')
            ->with('success', __('admin.flash.saved'));
    }
}
