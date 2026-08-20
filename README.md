# Dr Shaikh Saadiul Islam

A bilingual (English / বাংলা) profile and appointment site for a single doctor — an orthopaedics,
trauma and spine specialist practising in Dhaka. Laravel 13, Tailwind CSS v4, Alpine.js.

## What it does

**For patients**

- Profile, biography and a credential timeline — education, experience, training, memberships
- Areas of expertise, each with its own page
- Chambers with weekly sitting times, fees, address and map
- Online booking: pick a chamber, a date and a genuinely free time slot; a serial number is issued
  immediately and emailed if an address was given
- Cancel online with the serial number and the phone number used to book — the slot goes straight
  back on sale
- Look up an appointment again from its serial number
- Success stories, news, events, health tips, photo and video gallery, publications, FAQ
- Search across everything, in either language
- Contact form
- Every page in both languages, with Bangla numerals, dates and currency
- Light or dark, following the reader's device or the site's own setting

**For the practice** — `/admin`

- Dashboard: today's list, appointment states, unread messages
- Appointments: filter by date, status or chamber; change status; correct patient details; export CSV
- Manage the profile, credentials, expertise, chambers, weekly schedules, leave days, success
  stories, news/events/articles, testimonials, publications, gallery, FAQs, pages, hero slides,
  statistics, settings and staff users
- Every content field editable in both languages side by side, with a rich text editor for the
  fields that render as HTML
- Drag rows into the order patients see them; switch anything active or inactive from the listing
- **Sections & Visibility**: switch off any part of the public site — a whole section, one band of
  the homepage, or a piece of the header and footer. A section that is off loses its links, leaves
  the sitemap and site search, and its pages answer "not found"
- Choose the site's theme — light, dark, or whatever the reader's device asks for — and whether
  readers may change it for themselves
- Pick icons from the glyphs the site can actually draw, rather than typing a name and hoping
- Two roles: `admin` (everything) and `editor` (content and appointments, no settings or users)

## Requirements

PHP 8.3+ with `pdo_mysql`, Composer, Node 20+, MySQL 8.

> This project runs on MySQL rather than SQLite because the target machine's PHP has no `pdo_sqlite`.
> The test suite uses a separate `dr_profile_test` schema.

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
# set DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env

mysql -u root -p -e "CREATE DATABASE dr_profile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan storage:link

npm run build
composer serve
```

`composer serve` rather than `php artisan serve`: the CLI php.ini caps uploads at 2 MB, which rejects
ordinary photographs. The composer script raises it for development. Apache already permits far more.

## Getting the practice's details in

Content lives in `content/doctor.yml` — profile, chambers and their weekly sittings, expertise,
credentials, statistics, FAQs, pages and settings, every field bilingual. Leave a `_bn` value blank
and Bangla visitors see the English.

```bash
php artisan doctor:import --dry-run   # show what would change
php artisan doctor:import             # write it
```

Re-running an edited file corrects the existing records rather than duplicating them. Fields still
holding the template's `REPLACE ME` text are refused and listed by name, so a half-finished file
cannot publish a placeholder as somebody's qualification.

Everything in that file can also be edited through the admin. Use whichever suits — the YAML is
better when you want the set version-controlled and repeatable.

To clear demo content: `php artisan demo:purge` removes seeded stories, testimonials and
publications; `--all` clears every seeded record and keeps the staff accounts so you can still sign in.

Photographs go in `incoming/` (git-ignored) — see `incoming/README.md` for the folder layout and
`incoming/SLUGS.txt` for the filenames to match.

## Tests

```bash
mysql -u root -p -e "CREATE DATABASE dr_profile_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan test
```

323 tests covering slot generation, booking and double-booking, cancellation, the booking window and
lead time, locale routing and fallback, the language switcher, search, uploads and their failure
messages, the file lifecycle, admin authorisation, CRUD, list reordering and switches, and every
public page in both languages.

Several exist to stop one particular defect returning — a control that looks like it works and
changes nothing. They check that every visibility switch removes its section *and* every link into
it, that both languages carry the same keys and that no admin label is left as an English string,
and that an icon the admin offers is one the site can draw.

## Configuration

`config/site.php` holds the locales and the booking rules — how far ahead patients may book
(`window_days`), how close to a sitting online serials close (`lead_time_minutes`) and how many open
appointments one phone number may hold (`max_open_per_phone`).

## Before this goes live

These need the doctor, not the code.

- [ ] **`slot_minutes` is a guess of 15**, giving twelve serials per three-hour sitting. If he sees
      more patients than that, the site will turn people away. Confirm and change it in
      `content/doctor.yml`.
- [ ] **BMDC registration number** — not published on saadiul.com; it appears on the profile and
      patients rely on it, so verify it against the BMDC register before publishing.
- [ ] **Consultation fees** — not published anywhere, currently shown as "—".
- [ ] **Conflicting statistics** — saadiul.com claims 980+ patients treated *and* 3,500+ successful
      surgeries. Both cannot be right, so neither was loaded.
- [ ] **Conflicting designation** — his site says *Resident Surgeon*, his own poster says *Assistant
      Professor (Spine Surgery), NITOR*. The profile currently says Resident Surgeon.
- [ ] **Read the English.** His Bangla is quoted from his own site; the English beside it is a
      translation and should be checked.
- [ ] **Testimonials, publications, statistics and gallery are empty on purpose** — patient quotes
      and research papers cannot be written on his behalf. Add them when he supplies them.
- [ ] **Two statistics carry icon names the site cannot draw** — "Surgery Patient" says `hero` and
      "Follow Up Patient" says `admin`, so both render as bare circles. Open each in Statistics and
      pick from the grid; the form flags them.
- [ ] **The meta description still offers booking.** If online booking is ever switched off, the
      description on every page ("book an appointment with…") needs rewording in Doctor Profile.
- [ ] **Change the admin password and email.** The seeded accounts use `@drprofile.test` addresses,
      and there is no password reset flow, so a fake address means `php artisan tinker` is the only
      way back in.
- [ ] **`APP_URL`** must match the real host, and `APP_DEBUG=false` in production.
