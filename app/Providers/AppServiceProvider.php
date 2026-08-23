<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\DoctorProfile;
use App\Support\Features;
use App\Support\Number;
use App\Support\SiteNavigation;
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
        $this->registerNumberReplacements();

        // @feature('home_stats') … @endfeature — the switches in App\Support\Features.
        Blade::if('feature', fn (string $key) => Features::enabled($key));

        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // The layout, its partials and every page rendered into its slot need
        // these. Slot content renders in the caller's scope, not the component's,
        // so 'public.*' has to be listed alongside the layout itself.
        View::composer(['components.layouts.public', 'partials.*', 'public.*'], function ($view) {
            // Both lists are memoised on the request, so firing this once per
            // view costs one set of queries per page — see App\Support\SiteNavigation.
            $navigation = SiteNavigation::forRequest(request());

            $view->with([
                'doctor' => DoctorProfile::current(),
                'navChambers' => $navigation->chambers(),
                'footerPages' => $navigation->footerPages(),
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
     * The numbers inside a validation message.
     *
     * The validator substitutes :min and :max with the raw rule parameters, so
     * a Bangla message arrived as "অন্তত 3 অক্ষরের হতে হবে" — Bangla wording
     * wrapped around a Latin numeral, which is the one thing App\Support\Number
     * exists to prevent. `max` is handled with the upload messages below, since
     * it has a second job there.
     */
    private function registerNumberReplacements(): void
    {
        $rules = [
            'min' => [':min'],
            'size' => [':size'],
            'digits' => [':digits'],
            'between' => [':min', ':max'],
            'digits_between' => [':min', ':max'],
        ];

        foreach ($rules as $rule => $placeholders) {
            Validator::replacer($rule, function ($message, $attribute, $rule, $parameters) use ($placeholders) {
                foreach ($placeholders as $index => $placeholder) {
                    $message = str_replace($placeholder, Number::localizeDigits($parameters[$index] ?? ''), $message);
                }

                return $message;
            });
        }
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

            /*
             * A replacer stands in for the framework's own, so anything it
             * declines to handle still has to have its placeholders filled.
             * Handing the message back untouched printed a literal ":max" on
             * every non-file limit in the application — "must not be greater
             * than :max characters" — in both languages.
             */
            if (! $value instanceof UploadedFile) {
                return str_replace(':max', Number::localizeDigits($parameters[0] ?? ''), $message);
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
