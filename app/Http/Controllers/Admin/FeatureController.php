<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Features;
use App\Support\HomeLayout;
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
        $groups = Features::groups();

        return view('admin.visibility.edit', [
            'groups' => $groups,
            // How much of the site is switched off, said once at the top: the
            // form is long enough that a single flipped switch near the bottom
            // is easy to lose track of.
            'hidden' => collect($groups)->flatten(1)->where('enabled', '===', false)->count(),
            'theme' => Theme::default(),
            'layout' => HomeLayout::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'features' => ['array'],
            'features.*' => ['boolean'],
            'theme_default' => ['required', 'string', 'in:'.implode(',', Theme::CHOICES)],
            'home_layout' => ['required', 'string', 'in:'.implode(',', HomeLayout::CHOICES)],
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
        Setting::put(HomeLayout::SETTING, $data['home_layout'], 'appearance', 'text');

        return redirect()
            ->route('admin.visibility.edit')
            ->with('success', __('admin.flash.saved'));
    }
}
