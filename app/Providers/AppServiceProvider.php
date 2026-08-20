<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ContactMessage;
use App\Models\DoctorProfile;
use App\Models\Page;
use App\Support\Features;
use App\Support\Uploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
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
        $this->registerUploadMessages();

        // @feature('home_stats') … @endfeature — the switches in App\Support\Features.
        Blade::if('feature', fn (string $key) => Features::enabled($key));

        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // The layout, its partials and every page rendered into its slot need
        // these. Slot content renders in the caller's scope, not the component's,
        // so 'public.*' has to be listed alongside the layout itself.
        View::composer(['components.layouts.public', 'partials.*', 'public.*'], function ($view) {
            $view->with([
                'doctor' => DoctorProfile::current(),
                'navChambers' => Chamber::active()->ordered()->get(),
                'footerPages' => Page::published()->where('show_in_footer', true)->orderBy('sort_order')->orderBy('id')->get(),
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

    /**
     * Laravel's stock upload wording ("The photo failed to upload.") does not
     * say why or what the limit is. These replace it everywhere a file is
     * accepted, with the server's real limit filled in.
     */
    private function registerUploadMessages(): void
    {
        Validator::replacer('max', function ($message, $attribute, $rule, $parameters, $validator) {
            $value = $validator->getData()[$attribute] ?? null;

            if (! $value instanceof UploadedFile) {
                return $message;
            }

            return __('validation_custom.upload.too_large', [
                'size' => Uploads::formatBytes($value->getSize() ?: 0),
                'max' => Uploads::maxLabel(),
            ]);
        });

        Validator::replacer('image', fn () => __('validation_custom.upload.not_an_image'));

        Validator::replacer('uploaded', fn () => __('validation_custom.upload.failed', [
            'max' => Uploads::maxLabel(),
        ]));

        Validator::replacer('mimes', fn ($message, $attribute, $rule, $parameters) => __(
            'validation_custom.upload.wrong_type',
            ['values' => implode(', ', $parameters)]
        ));
    }
}
