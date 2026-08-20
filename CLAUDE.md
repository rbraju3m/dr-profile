# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A bilingual (English / বাংলা) Laravel site for **one doctor** — not a hospital and not a doctor directory.
The homepage *is* the profile. There is no `doctors` table; `doctor_profile` is a singleton row read
through `DoctorProfile::current()` (memoised per request) or the global `doctor()` helper.

Public side: profile, expertise, chambers with weekly schedules, online appointment booking,
success stories, news/events, health-tip articles, gallery, publications, FAQ, contact.
Back office: a hand-built Blade admin at `/admin` (deliberately no Filament or admin package).

## Commands

```bash
composer install && npm install
php artisan migrate --seed        # demo content in both languages
npm run dev                       # vite; use `npm run build` for production assets
php artisan serve

php artisan test                                        # full suite
php artisan test --filter=SlotServiceTest               # one class
php artisan test --filter=test_a_booked_slot_is_marked  # one method
./vendor/bin/pint                                       # formatting
```

Demo logins after seeding: `admin@drprofile.test` / `editor@drprofile.test`, password `password`.

## Environment specifics

- **This PHP build has no `pdo_sqlite`** — only `mysql` and `pgsql`. Both the app and the test suite
  run on MySQL; `phpunit.xml` points at the separate `dr_profile_test` schema. Do not "simplify" the
  test config back to `:memory:` sqlite, it will not boot.
- Laravel 13 + Tailwind v4 + Alpine. Fonts (Inter, Noto Sans Bengali) are **self-hosted** through the
  `laravel-vite-plugin` `bunny()` helper and emitted by the `@fonts` Blade directive — no CDN.
- **Upload limits differ per SAPI on this machine.** Apache (mod_php) allows 5G; the CLI ini allows
  only 2M/8M, so `php artisan serve` rejects ordinary photographs while Apache accepts them. Use
  `composer serve`, which raises them with `-d` flags. Before changing an upload limit, check the ini
  of the SAPI that actually serves the request — `php -r` reports the CLI's, which is not the one
  Apache uses. Note also that `.htaccess` `php_value` sets a ceiling: adding one under a vhost with
  `AllowOverride All` would *lower* the effective limit here, not raise it.

## Bilingual architecture

Two separate mechanisms; do not mix them.

1. **UI strings** → `lang/en/*.php` and `lang/bn/*.php`, reached with `__('site.…')` / `__('admin.…')`.
   The two locales must stay key-for-key identical.
2. **Database content** → paired columns (`name_en` / `name_bn`). Models list the *base* names in
   `protected array $translatable` and use `App\Concerns\HasTranslations`, which resolves
   `$model->name` to the active locale and falls back to English when the Bangla column is blank.
   Never add a translatable column without adding both halves and the base name to `$translatable`.

Numbers are locale-sensitive: Bangla renders its own digits. Every user-facing number, time, date part
and money value goes through `App\Support\Number::localizeDigits()` / `::money()` or the `bn_digits()`
helper. `App\Support\Week` formats day names and clock times.

### The `{locale}` route prefix has one sharp edge

Public routes live under `Route::prefix('{locale}')`. Controller arguments are filled **positionally**
from the route's parameters, so leaving `locale` in place passes `"en"` as the first argument of every
action instead of the bound model. `SetLocale` therefore calls `$request->route()->forgetParameter('locale')`
after reading it, and registers `URL::defaults(['locale' => …])` so `route()` needs no locale argument.

Three middleware cooperate:
- `DetectLocale` — global, **before routing**, so 404 pages (rendered when no route matched) still have
  a locale and can generate links.
- `SetLocale` — on the public route group; authoritative, and forgets the parameter.
- `AdminLocale` — on `/admin`, which has no locale in its URLs; follows `session('admin_locale')` and
  still sets the URL default because the panel links out to the public site.

## Booking engine

The only genuinely intricate logic. Two classes:

- `App\Services\SlotService` turns weekly `chamber_schedules` rows into concrete slots for a date.
  Precedence: a chamber-specific `schedule_exceptions` row → a row with `chamber_id = NULL`
  (doctor away everywhere) → the weekly pattern. Slots held by a pending/confirmed/completed
  appointment are marked taken; **cancelling releases the slot**. Today's slots inside
  `site.booking.lead_time_minutes` are dropped.
- `App\Services\BookingService` writes the appointment. It re-reads availability *inside* a transaction
  while holding `lockForUpdate()` on the chamber row, so two patients confirming the same slot
  simultaneously cannot both succeed. There is deliberately **no unique index** on
  `(chamber_id, appointment_date, slot_time)` — that would permanently block a cancelled slot.

Booking rules live in `config/site.php` (`window_days`, `lead_time_minutes`, `max_open_per_phone`).

## Admin panel

`App\Http\Controllers\Admin\ResourceController` is an abstract CRUD base. A child declares `$model`,
`$viewPath`, `$routeName`, `$labelKey`, its `columns()` and its `rules()`; listing, search, pagination,
uploads, slug generation and flash messages are inherited. The index table is rendered generically from
`columns()` by `resources/views/admin/resource/index.blade.php`; each resource still writes its own
`form.blade.php` wrapped in `<x-admin.form-shell>`.

Because the base class type-hints the abstract `Model`, implicit route binding cannot work. Those routes
are registered through the `$resource(...)` closure in `routes/web.php`, which renames the route
parameter to `record`; `ResourceController::resolveRecord()` then looks the row up using each model's
own `getRouteKeyName()`. Register new resources through that closure, not `Route::resource` directly.

Bilingual fields in forms use `<x-admin.bilingual name="title" …>`, which renders EN/BN tabs over the
`title_en` / `title_bn` pair.

## Conventions worth keeping

- **Never cache Eloquent models or Collections.** Laravel 13's cache stores unserialize against an
  allowed-classes allowlist, so anything else returns `__PHP_Incomplete_Class`. Cache plain arrays and
  re-wrap (`Setting::map()` does this); memoise models on a static property instead
  (`DoctorProfile::current()`).
- Uploads go through `App\Services\MediaService` on the `public` disk, with randomised filenames.
  Declare them in a controller's `$mediaFields` map so replace-and-delete is handled.
- Views: public pages live in `resources/views/public/**` and render into `<x-layouts.public>`.
  Slot content renders in the *caller's* scope, so shared variables (`$doctor`, `$navChambers`,
  `$footerPages`) are supplied by a `View::composer` registered for `components.layouts.public`,
  `partials.*` **and** `public.*` in `AppServiceProvider`.
- Icons are inline SVG paths in `resources/views/components/icon.blade.php` — add new glyphs there
  rather than pulling in an icon package.
- Tailwind v4 cannot `@apply` a class declared in the same layer. Shared component shapes in
  `resources/css/app.css` are applied through grouped selectors (`.btn, .btn-primary, … { @apply … }`).
