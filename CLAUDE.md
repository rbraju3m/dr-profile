# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A bilingual (English / বাংলা) Laravel site for **one doctor** — currently Dr Shaikh Saadiul Islam,
an orthopaedics, trauma and spine specialist in Dhaka. Not a hospital, not a directory. The homepage
*is* the profile: there is no `doctors` table, and `doctor_profile` is a singleton row read through
`DoctorProfile::current()` (memoised per request) or the global `doctor()` helper.

Public: profile, expertise, chambers with weekly schedules, online booking with patient cancellation,
success stories, news/events, health tips, gallery, publications, FAQ, site search, contact.
Back office: a hand-built Blade admin at `/admin` — deliberately no Filament or admin package.

## Commands

```bash
composer install && npm install
php artisan migrate --seed          # demo data; see "Content" before using on a real site
composer serve                      # NOT `artisan serve` — see "Uploads" below
npm run dev                         # or `npm run build`

php artisan test                                        # full suite
php artisan test --filter=SlotServiceTest               # one class
php artisan test --filter=test_a_booked_slot_is_marked  # one method
./vendor/bin/pint                                       # formatting

php artisan doctor:import --dry-run   # load content/doctor.yml, showing changes only
php artisan doctor:import
php artisan demo:purge --all          # remove every seeded record, keeping staff accounts
php artisan posts:signature --dry-run # restate the signature at the foot of every signed post
php artisan profile:share-card        # install resources/brand/share-card.png as the og:image
```

Screenshots, when you need to see a change rather than assert it: Chrome is on this machine.

```bash
composer serve &
google-chrome --headless --disable-gpu --no-sandbox --hide-scrollbars \
    --force-prefers-reduced-motion --window-size=1440,3000 \
    --screenshot=out.png http://127.0.0.1:8000/en
```

`--force-prefers-reduced-motion` matters: without it, anything below the fold is photographed
mid-reveal and looks half-drawn. Admin pages need a session, so fetch the HTML with `curl` through a
cookie jar, rewrite `href="/` to absolute URLs, and open the saved file with `--disable-web-security`
so the stylesheet still loads.

## Environment specifics

- **No `pdo_sqlite` in this PHP build** — only `mysql` and `pgsql`. App and tests both run on MySQL;
  `phpunit.xml` points at a separate `dr_profile_test` schema. Do not "simplify" it back to
  `:memory:` sqlite; it will not boot.
- Laravel 13, Tailwind v4, Alpine 3. Fonts (Inter, Noto Sans Bengali) are self-hosted via the
  `laravel-vite-plugin` `bunny()` helper and emitted by `@fonts` — no CDN.
- **Upload limits differ per SAPI here.** Apache (mod_php) allows 5G; the CLI ini allows 2M/8M, so
  `php artisan serve` rejects ordinary photographs while Apache accepts them. Use `composer serve`,
  which raises them via `PHP_INI_SCAN_DIR` — `-d` flags do not work because `artisan serve` spawns a
  child that re-reads the ini. Before changing an upload limit, check the ini of the SAPI that
  actually serves the request; `php -r` reports the CLI's, not Apache's. Note also that an
  `.htaccess` `php_value` sets a *ceiling*, so adding one here would lower the limit, not raise it.

## The recurring defect in this codebase

Something here is wired up on one side only, and looks finished from the side you are on. It has
happened often enough to be worth naming.

The admin offering a control the site never read: contact settings nothing rendered, service and page
images shown only on detail pages, X and Instagram links absent from the header, a signature upload
displayed nowhere. Then the same shape from other directions — a column header written as a plain
English string, so the panel stayed half-English however it was switched; an "Icon name" free-text
field that accepted `hero` and drew a bare circle; a component pushing scripts into a layout with no
`@stack` to receive them; a dark-mode override that could not reach `text-primary-900/80`, because
an opacity variant is its own class.

**When you add a field, a label, a switch or a style, check it arrives.** Not that it saves — that a
reader sees it. Every guard below exists because that check was skipped once:

| Test | What it refuses to let happen |
|---|---|
| `UploadedImagesAreShownTest` | an upload the admin accepts and no page shows |
| `SocialLinksTest` | a network editable in one place and rendered in another |
| `FeatureVisibilityTest` | a hidden section still linked from a page a visitor can reach |
| `FeatureRegistryTest` | a switch read but unregistered, or registered and read nowhere |
| `ListingLabelsTest` / `FormLabelsTest` | an admin string that will not translate |
| `TranslationParityTest` | a key in one language and not the other |
| `IconPickerTest` | an icon offered that the site cannot draw, and a layout that drops what a component pushes |
| `HomeLayoutTest` | a homepage design that ignores a visibility switch, or drops a band the other two carry |
| `ListingContentTest` | an admin *row* in the wrong language — the labels' guards never looked at the data |
| `TranslationUsageTest` | a string translated into both languages that nothing ever reads |
| `DateFormattingTest` | a view spelling a date out for itself instead of asking `App\Support\Week` |
| `ValidationMessagesTest` | a validation message left in English, or showing a `:placeholder` it meant to fill |
| `SharedNavigationTest` | the header and footer lists fetched once per view instead of once per request |

## Bilingual architecture

Two mechanisms; do not mix them.

1. **UI strings** → `lang/en/*.php` and `lang/bn/*.php`, via `__('site.…')` / `__('admin.…')`. The two
   locales must stay key-for-key identical — `TranslationParityTest` fails on a key present in one
   and absent from the other, which otherwise prints the key itself onto the page.
   This includes everything the admin renders, where a plain string is easy to write and used to
   leave the panel half-English however it was switched: the `columns()` labels in
   `Admin\ResourceController` children (`ListingLabelsTest`, which also fails on a table heading two
   columns the same) and every `label=` / `hint=` on a form field (`FormLabelsTest`). Shared field
   names live in `admin.fields.*` — a slug is a slug whichever table it belongs to. `FormLabelsTest`
   also walks every admin form in both languages looking for a raw `admin.…` key on the page, since
   a key that does not exist prints its own name rather than failing.
2. **Database content** → paired columns (`name_en` / `name_bn`). Models list the *base* names in
   `protected array $translatable` and use `App\Concerns\HasTranslations`, which resolves
   `$model->name` for the active locale and falls back to English when the Bangla column is blank.

Numbers are locale-sensitive — Bangla renders its own digits. Every user-facing number, time, date
part and money value goes through `App\Support\Number` or the `bn_digits()` helper. `App\Support\Week`
is the one place that writes a day name, a date or a clock time — `date()`, `dayMonth()`,
`monthYear()`, `dateTime()`, `name()`, `time()`. Carbon's own `format()` emits Latin digits and
English month names whatever the locale, so a view that reaches for it is the bug; nine of them
spelled the date out by hand before `DateFormattingTest` was written to stop it.

Laravel's own validation wording is translated too, in `lang/*/validation.php`. Nothing in this
repository names those keys — the validator resolves them by rule name — which is why
`TranslationUsageTest` excludes the file. Where a rule needs different wording, put it in
`validation_custom.php` and name it explicitly. Note that a `Validator::replacer` stands in for the
framework's own substitution: whatever it declines to handle, it still has to fill the placeholders
for, or the reader gets a literal `:max`.

### The `{locale}` prefix has two sharp edges

Public routes live under `Route::prefix('{locale}')`.

- Controller arguments are filled **positionally** from route parameters, so leaving `locale` in place
  passes `"en"` as the first argument of every action instead of the bound model. `SetLocale` calls
  `$request->route()->forgetParameter('locale')` after reading it.
- Three middleware cooperate: `DetectLocale` (global, **before routing**, so 404s can still build
  links), `SetLocale` (public group, authoritative, forgets the parameter), `AdminLocale` (`/admin`,
  which has no locale in its URLs).
- The locale is also kept in an **unencrypted cookie**, excluded from `encryptCookies`. That is the
  only signal available to an error raised by global middleware — a 413 renders before the session
  exists, and without the cookie it always came out in English.

## Booking engine

The most intricate logic. Two classes:

- `App\Services\SlotService` turns weekly `chamber_schedules` rows into concrete slots. Precedence:
  a chamber-specific `schedule_exceptions` row → a row with `chamber_id = NULL` (doctor away
  everywhere) → the weekly pattern. Slots held by a pending/confirmed/completed appointment are
  marked taken; **cancelling releases the slot**. Today's slots inside `lead_time_minutes` are dropped.
- `App\Services\BookingService` writes the appointment, re-reading availability *inside* a transaction
  while holding `lockForUpdate()` on the chamber row. There is deliberately **no unique index** on
  `(chamber_id, appointment_date, slot_time)` — that would permanently block a cancelled slot.

A serial is printed on a slip, mailed and read out over the phone, so it says *which* appointment
and not that the appointment is yours. Reading one costs the same proof as cancelling it: the mobile
number it was booked with, through the lookup form, which grants access for the session
(`App\Support\PatientAccess`). `appointment.show` resolves its record by hand rather than by the
router so that an unknown serial and someone else's serial end at the same form — a 404 for one and a
redirect for the other would make the page a way of finding live serials.

Every comparison of two numbers goes through `App\Support\Phone`, which matches on the last nine
digits so `+8801…` and `01…` are one person. Cancelling always did this; the open-appointment limit
compared the raw string, so one patient could take the allowance three times by spelling their own
number three ways. Rules live in `config/site.php`.

## Admin panel

`App\Http\Controllers\Admin\ResourceController` is an abstract CRUD base. A child declares `$model`,
`$viewPath`, `$routeName`, `$labelKey`, `columns()` and `rules()`; listing, search, pagination,
uploads, slugging and flash messages are inherited. The index table renders generically from
`columns()`; each resource writes its own `form.blade.php` wrapped in `<x-admin.form-shell>`.

- Because the base class type-hints the abstract `Model`, implicit route binding cannot work. Those
  routes go through the `$resource(...)` closure in `routes/web.php`, which renames the route
  parameter to `record`; `resolveRecord()` then looks the row up using each model's own
  `getRouteKeyName()`. **Register new resources through that closure**, not `Route::resource`.
- `reorder()` stores a drag-and-drop order; `toggle()` flips a status flag from the listing. Only the
  columns in `ResourceController::TOGGLEABLE` may be switched, whatever the request asks — otherwise
  the endpoint would set any column on any row.
- Bilingual fields use `<x-admin.bilingual>`, which renders EN/BN tabs over a column pair.
  `type="rich"` mounts Quill — use it **only** for fields rendered with `{!! !!}`: `answer`, `bio`,
  `content`, `description`, `philosophy`. An editor on a field printed with `{{ }}` would show its
  own markup to visitors.

## Showing and hiding parts of the site

Every public section, every homepage band and the header/footer furniture can be switched off from
**Sections & Visibility** (`/admin/visibility`, admin role only). The registry is
`App\Support\Features`; each switch is a `settings` row keyed `feature_<name>`, and a **missing row
means on**, so nothing hides itself on an existing install.

Switching a section off is not cosmetic. It must leave *every* way in:

- the routes answer 404 — public routes are grouped behind `->middleware('feature:<name>')`;
- the link leaves the header, the footer, the homepage and any page that cross-links to it;
- the URLs leave `sitemap.xml` and the rows leave site search.

Read a switch with `feature('key')` in PHP or `@feature('key') … @endfeature` in Blade (a
`Blade::if` registered in `AppServiceProvider`). `Features::filter()` drops the entries of a nav
array whose `feature` key is off.

`requires` in the registry stops a switch from leaving a dead end: the homepage expertise band lists
services and links to their detail pages, so it follows the `services` switch down whatever its own
says. When a band or a button links into another section, gate the *link* on that section's switch —
`home.blade.php` does this for both hero buttons.

`FeatureRegistryTest` fails on a name used in a view but missing from the registry (an unknown name
reads as "on", so the typo would otherwise be silent) and on a registered name nothing reads — the
same recurring defect from both ends. `FeatureVisibilityTest` walks every section on and off. Two sweeps guard the "no dead ends" rule:
one checks that no page a visitor can still reach links into a hidden section, in both languages;
the other goes further for booking, which is offered from more places than anything else — the
header, the phone bar, every chamber card, four sidebars and the error pages — and checks its
wording is gone too.

## Three homepage layouts

The homepage has three designs and the admin picks one in **Sections & Visibility**, beside the
theme. `App\Support\HomeLayout` resolves it exactly as `Theme` does: a `home_layout` settings row,
`CHOICES` of `classic`, `spotlight` and `editorial`, and anything else falling back to `classic` —
the design the site shipped with — so an install that never touches this looks unchanged.

- `HomeController` renders `HomeLayout::view()`, which is `public.home.<layout>`. The three views
  live in `resources/views/public/home/`; the composer registered for `public.*` still reaches them.
- **All three carry the same bands from the same data and obey the same switches.** A layout changes
  the shape of the page, never what is on it — so every band keeps its `@feature(...)` guard and its
  emptiness guard in each design, and a "view all" link stays gated on the section it points at.
  `HomeLayoutTest` walks the bands and the switches through all three; the recurring defect wearing a
  new hat is a band that reaches one design and not the others.
- Shared pieces stay shared. The trust facts (experience, registration, hotline, languages) are built
  once in `HomeController::trustFacts()`, and the slide machinery is one partial,
  `public.home.partials.carousel-script`, pushed by whichever design drew a hero — built twice, the
  designs drift.
- Classic stacks full-width bands; Spotlight is card-led, with the hero photograph off the end edge
  and the statistics riding up over it; Editorial is a magazine — hairline rules, large type, a
  numbered contents list in place of the expertise grid. All three use the one motion vocabulary and
  the same component classes, so dark mode needs nothing per layout.

## Light and dark

The site ships both themes. `App\Support\Theme` resolves which one a request renders in:

- the admin picks the site default in **Sections & Visibility** — Light, Dark, or *Follow the device*;
- a reader may overrule it for themselves while the `theme_toggle` switch is on. Their choice lives
  in an unencrypted `theme` cookie, for the same reason the locale does: it has to be readable while
  the first byte is being built, or the page paints in the wrong theme and flickers;
- `Theme::forStaff()` (the admin layout and the sign-in page) always honours the cookie. The public
  switch decides what *visitors* are offered, not what the back office looks like.

`Follow the device` cannot be answered on the server, so no class is emitted and
`partials/theme-script` settles it from `prefers-color-scheme` before the first paint.

How the styling works — this is the part that is easy to get wrong:

- The theme is a **class on `<html>`**, so `app.css` declares `@custom-variant dark` rather than
  using Tailwind's default `prefers-color-scheme` behaviour.
- Utilities compile to `var(--color-…)`, so **the neutral ramp is inverted once** under `.dark` and
  every page follows. The brand ramps are *not* moved: `text-primary-100` is ink on the dark hero and
  `bg-primary-950` is that hero's background, so a token that moved would take one with the other.
  Their light tints are re-pointed one utility at a time in the block at the foot of `app.css`.
- Component classes (`.card`, `.chip`, `.btn-secondary`, `.field-input`) take their surface from
  `var(--surface-1)` and friends rather than a `.dark .card` rule, because a `.dark`-prefixed rule
  outranks a utility and `<div class="card bg-primary-900">` must stay teal in both themes.
- **Do not mark a colour utility `!important` to beat a component class.** It never needed to — the
  utilities layer already outranks components — and an unlayered dark override cannot win against a
  layered `!important`, so the marked element silently keeps its light colour.
- An **opacity variant is its own class**: `bg-primary-50/40` is never touched by the override on
  `bg-primary-50`. Where a tint has to flip, give it a token and a component class — `.switch-row`
  is the ticked checkbox row, `.switch-knob` the toggle's white knob, which rides on a coloured
  track and so stays white in both themes.
- Scrims (`bg-black/60`) and overlays on always-dark bands (`bg-white/10`, `text-white`) stay as they
  are: those surfaces are dark in both themes.

The reader's switch has **three** positions — light, dark, follow the device — because a plain flip
is a one-way door: the moment a reader touches it their cookie outranks their phone for a year.

`ThemeTest` covers the default, the reader's override, handing the choice back to the device, the
switch being off, and the admin panel.

## Media

- Uploads go through `App\Services\MediaService` on the `public` disk with randomised filenames, the
  extension read from the file's own contents rather than the name the browser sent. Declare them in
  a controller's `$mediaFields` so replace-and-delete is handled. `storeAs()` returns `false` when the
  disk refuses a write, and `store()` throws a `ValidationException` on that field rather than passing
  it on: returning it wrote `false` into the column, told the operator it had saved, and — in
  `replace()` — had already deleted the file being replaced.
- **Stored-media URLs are root-relative** (`config/filesystems.php` sets `'url' => '/storage'`).
  Building them from `APP_URL` broke every image the moment the app was reached on a different host
  or port. `og:image` and structured data still wrap them in `url()` because other servers read those.
- `App\Support\Uploads` derives every size rule from the ini at runtime, so validation can never
  advertise a size PHP will refuse.
- `<x-media-frame>` takes `fit`: `cover` crops (photographs), `contain` letterboxes inside the tile
  (posters carrying text — a centre crop through a Bangla headline destroys the image), `natural`
  drops the fixed ratio for detail pages.
- Gallery albums cascade to items in the database. Because a DB cascade never loads the rows, item
  files were orphaned; `GalleryItem` now deletes its own file on `deleting`, and `GalleryAlbum`
  deletes items through Eloquent so those events fire.
- `App\Support\VideoEmbed` converts what people paste — YouTube watch/short/shorts/live, Vimeo,
  Facebook videos, **reels**, share links and `fb.watch` — into something framable. Facebook embeds
  fail silently on non-public videos, so every FB embed also renders a "Watch on Facebook" link.

## Front-end conventions

- Public pages live in `resources/views/public/**` and render into `<x-layouts.public>`. Slot content
  renders in the *caller's* scope, so shared variables (`$doctor`, `$navChambers`, `$footerPages`)
  come from a `View::composer` registered for `components.layouts.public`, `partials.*` **and**
  `public.*` in `AppServiceProvider`.
- Motion lives in two custom Alpine directives: `x-reveal` (with `.stagger`) and `x-counter`. Both
  check `prefers-reduced-motion` before registering any observer. **Alpine v3 only walks trees rooted
  at an `x-data`**, so an element carrying `x-reveal` needs its own — the markup was correct and
  nothing moved until each got one.
- **One motion vocabulary, one curve** (`--ease-soft`). Everything that moves is one of: the reveal
  (fade + 16px rise, 500ms, 70ms between siblings), a 2px hover lift with a shadow bloom
  (`.card-hover`), a hairline that draws itself in (`.rule-draw`, `.line-draw`), the hero
  photograph's single slow drift (`.ken-burns`), an arrow that leans towards its destination
  (`.lean`), and `x-counter`. Nothing loops, and nothing moves that the reader did not cause. Adding
  a seventh effect means asking whether it replaces one of these.
- Headlines carry `.display`, which sets the tight tracking and line height in one place —
  and unsets both for Bangla, whose conjuncts collide at Latin metrics.
- Every inner page opens with `<x-page-hero>`, which carries the homepage hero's language (hairline
  grid, display type, accent rule) so the site reads as one thing. Its rule uses `.rule-in`, a CSS
  animation rather than `x-reveal`: above the fold there is nothing to wait for, and a title that
  stays invisible when JS fails is not a trade worth making.
- Screenshots of anything below the fold catch reveals **mid-animation** and look half-drawn. Render
  with `--force-prefers-reduced-motion` to photograph the settled state.
- Icons are inline SVG in `App\Support\Icons` — add glyphs there rather than pulling in an icon
  package. They live in PHP rather than in the Blade component so the admin can *offer* the list:
  the icon field used to be free text, and "hero" and "admin" were duly typed in and drawn as bare
  circles on the public page. `<x-admin.icon-picker>` shows every glyph and `Rule::in(Icons::names())`
  refuses anything else.
- Tailwind v4 cannot `@apply` a class declared in the same layer; shared component shapes in
  `resources/css/app.css` use grouped selectors (`.btn, .btn-primary, … { @apply … }`).
- Social links come from `DoctorProfile::socialLinks()` and render through `<x-social-links>`. Header
  and footer previously kept separate hardcoded lists, which is how networks went missing.

## Content

`content/doctor.yml` is the source of truth for the practice's details — profile, chambers with
weekly sittings, expertise, credentials, statistics, FAQs, pages and settings, all bilingual.
`doctor:import` upserts by slug so re-running an edited file corrects rather than duplicates, runs in
one transaction, refuses fields still holding the template's `REPLACE ME` text, and reports them by
name. `--dry-run` shows the changes without writing.

**Do not invent content for this site.** It carries a real doctor's name and takes patient bookings.
Testimonials, publications and statistics are deliberately empty because they cannot be written on
his behalf; the seeder's invented patient stories and qualifications were purged for the same reason.
If a section looks empty, that is usually a decision, not an oversight.

## Other conventions

- **Never cache Eloquent models or Collections.** Laravel 13's cache stores unserialize against an
  allowed-classes allowlist, so anything else returns `__PHP_Incomplete_Class`. Cache plain arrays and
  re-wrap (`Setting::map()`), or memoise on a static property (`DoctorProfile::current()`).
- **A static memo outlives a test.** `Setting::map()` and `DoctorProfile::current()` both hold one —
  without it every `feature()` call was its own `SELECT` against the cache table, ninety-five to draw
  a homepage — and `RefreshDatabase` empties the table underneath it. `tests/TestCase::setUp()` drops
  both, or one test's settings decide the next test's feature switches.
- Anything a view needs on every public page but only once per request — the header chambers, the
  footer pages — belongs in `App\Support\SiteNavigation`, memoised on the **request**. A static
  property or a `scoped` binding both survive into the next request inside a test, and would serve a
  menu that no longer matches the database.
- Unauthenticated visitors to `/admin` are sent to `admin.login` via `redirectGuestsTo` — there is no
  public `login` route.
