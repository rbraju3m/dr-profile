<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ContactMessage;
use App\Models\DoctorProfile;
use App\Models\Page;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // The layout, its partials and every page rendered into its slot need
        // these. Slot content renders in the caller's scope, not the component's,
        // so 'public.*' has to be listed alongside the layout itself.
        View::composer(['components.layouts.public', 'partials.*', 'public.*'], function ($view) {
            $view->with([
                'doctor' => DoctorProfile::current(),
                'navChambers' => Chamber::active()->ordered()->get(),
                'footerPages' => Page::published()->where('show_in_footer', true)->get(),
            ]);
        });

        // Sidebar badges for anything waiting on staff attention.
        View::composer('components.layouts.admin', function ($view) {
            $view->with([
                'pendingAppointments' => Appointment::where('status', 'pending')->upcoming()->count() ?: null,
                'unreadMessages' => ContactMessage::unread()->count() ?: null,
            ]);
        });
    }
}
