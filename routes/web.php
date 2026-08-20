<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ChamberController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuccessStoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site — every URL carries its locale: /en/… or /bn/…
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect('/'.(session('locale') ?: config('site.default_locale'))));

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::prefix('{locale}')
    ->where(['locale' => 'en|bn'])
    ->middleware('locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('about', [ProfileController::class, 'show'])->name('about');

        Route::get('expertise', [ServiceController::class, 'index'])->name('services.index');
        Route::get('expertise/{service}', [ServiceController::class, 'show'])->name('services.show');

        Route::get('chambers', [ChamberController::class, 'index'])->name('chambers.index');
        Route::get('chambers/{chamber}', [ChamberController::class, 'show'])->name('chambers.show');

        // Booking — the slots endpoint feeds the Alpine wizard.
        Route::get('appointment', [AppointmentController::class, 'create'])->name('appointment.create');
        Route::get('appointment/slots', [AppointmentController::class, 'slots'])->name('appointment.slots');
        Route::post('appointment', [AppointmentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('appointment.store');
        Route::get('appointment/lookup', [AppointmentController::class, 'lookup'])->name('appointment.lookup');
        Route::get('appointment/{appointment}', [AppointmentController::class, 'show'])->name('appointment.show');
        Route::post('appointment/{appointment}/cancel', [AppointmentController::class, 'cancel'])
            ->middleware('throttle:6,1')
            ->name('appointment.cancel');

        Route::get('success-stories', [SuccessStoryController::class, 'index'])->name('stories.index');
        Route::get('success-stories/{story}', [SuccessStoryController::class, 'show'])->name('stories.show');

        Route::get('news', [PostController::class, 'news'])->name('news.index');
        Route::get('news/{post}', [PostController::class, 'show'])->defaults('type', 'news')->name('news.show');
        Route::get('events', [PostController::class, 'events'])->name('events.index');
        Route::get('events/{post}', [PostController::class, 'show'])->defaults('type', 'event')->name('events.show');
        Route::get('health-tips', [PostController::class, 'blog'])->name('blog.index');
        Route::get('health-tips/{post}', [PostController::class, 'show'])->defaults('type', 'blog')->name('blog.show');

        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
        Route::get('gallery/{album}', [GalleryController::class, 'show'])->name('gallery.show');

        Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
        Route::get('faq', [FaqController::class, 'index'])->name('faq.index');
        Route::get('search', [SearchController::class, 'index'])->name('search');

        Route::get('contact', [ContactController::class, 'create'])->name('contact.create');
        Route::post('contact', [ContactController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('contact.store');

        Route::get('p/{page}', [PageController::class, 'show'])->name('pages.show');
    });

/*
|--------------------------------------------------------------------------
| Admin — session-locale, no URL prefix
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('admin.locale')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('login.store');
    });

    Route::post('logout', [Admin\AuthController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::post('language', [Admin\DashboardController::class, 'language'])->name('language');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        /*
         * Every resource handled by Admin\ResourceController names its route
         * parameter "record", matching the base controller's signature.
         */
        $resource = function (string $uri, string $controller) {
            // Drag-and-drop ordering posts here. Declared before the resource
            // routes so "reorder" is never mistaken for a record key.
            Route::post($uri.'/reorder', [$controller, 'reorder'])->name($uri.'.reorder');
            Route::post($uri.'/{record}/toggle', [$controller, 'toggle'])->name($uri.'.toggle');

            return Route::resource($uri, $controller)
                ->parameters([$uri => 'record'])
                ->except('show');
        };

        $resource('credentials', Admin\CredentialController::class);
        $resource('services', Admin\ServiceController::class);
        $resource('chambers', Admin\ChamberController::class);
        Route::resource('chambers.schedules', Admin\ChamberScheduleController::class)
            ->only(['index', 'store', 'destroy'])
            ->shallow();
        $resource('exceptions', Admin\ScheduleExceptionController::class);

        Route::get('appointments/export', [Admin\AppointmentController::class, 'export'])->name('appointments.export');
        Route::resource('appointments', Admin\AppointmentController::class)->except('create', 'store');
        Route::patch('appointments/{appointment}/status', [Admin\AppointmentController::class, 'updateStatus'])
            ->name('appointments.status');

        $resource('stories', Admin\SuccessStoryController::class);
        $resource('post-categories', Admin\PostCategoryController::class);
        $resource('posts', Admin\PostController::class);
        $resource('testimonials', Admin\TestimonialController::class);
        $resource('faqs', Admin\FaqController::class);
        $resource('publications', Admin\PublicationController::class);
        $resource('albums', Admin\GalleryAlbumController::class);
        Route::resource('albums.items', Admin\GalleryItemController::class)->only(['index', 'store', 'destroy'])->shallow();
        $resource('pages', Admin\PageController::class);
        $resource('sliders', Admin\SliderController::class);
        $resource('stats', Admin\StatController::class);

        Route::get('messages', [Admin\ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\ContactMessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [Admin\ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::middleware('staff:admin')->group(function () use ($resource) {
            Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
            $resource('users', Admin\UserController::class);
        });
    });
});
