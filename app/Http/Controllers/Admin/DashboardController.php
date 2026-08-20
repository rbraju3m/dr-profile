<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Service;
use App\Models\SuccessStory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        return view('admin.dashboard', [
            'counts' => [
                'pending' => Appointment::where('status', 'pending')->upcoming()->count(),
                'confirmed' => Appointment::where('status', 'confirmed')->upcoming()->count(),
                'completed' => Appointment::where('status', 'completed')->count(),
                'cancelled' => Appointment::where('status', 'cancelled')->count(),
                'today' => Appointment::forDate($today)->blocking()->count(),
                'week' => Appointment::whereBetween('appointment_date', [$today, $today->copy()->addDays(7)])->blocking()->count(),
                'total' => Appointment::count(),
                'unread' => ContactMessage::unread()->count(),
            ],
            'content' => [
                __('admin.nav.services') => Service::count(),
                __('admin.nav.stories') => SuccessStory::count(),
                __('site.nav.news') => Post::news()->count(),
                __('site.nav.events') => Post::events()->count(),
                __('site.nav.blog') => Post::blog()->count(),
            ],
            'todayAppointments' => Appointment::with('chamber')
                ->forDate($today)
                ->blocking()
                ->orderBy('slot_time')
                ->get(),
            'recent' => Appointment::with('chamber')->latest()->take(8)->get(),
            'messages' => ContactMessage::latest()->take(5)->get(),
        ]);
    }

    /** Switches the admin UI language (the public site uses the URL prefix instead). */
    public function language(Request $request): RedirectResponse
    {
        $locale = $request->string('locale')->toString();

        if (array_key_exists($locale, config('site.locales'))) {
            $request->session()->put('admin_locale', $locale);
            // Also as a cookie, so errors raised before the session starts —
            // an oversized upload, for one — still speak the right language.
            Cookie::queue('locale', $locale, 60 * 24 * 365);
        }

        return back();
    }
}
