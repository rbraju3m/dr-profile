# dr-profile

A bilingual (English / বাংলা) profile and appointment website for a single doctor, built with Laravel 13,
Tailwind CSS v4 and Alpine.js.

## Features

**Public site**
- Doctor profile with biography, care philosophy and a credential timeline (education, experience,
  training, awards, memberships, certifications)
- Areas of expertise, each with its own page
- Multiple chambers, each with a weekly schedule, fees, map and contact details
- Online appointment booking — pick a chamber, date and free time slot, get a serial number instantly
- Serial-number lookup so patients can re-open their confirmation
- Patient success stories, news, events, health-tip articles, photo/video gallery, publications, FAQ
- Contact form, sitemap, JSON-LD `Physician` structured data, `hreflang` alternates

**Admin panel** (`/admin`, hand-built — no admin package)
- Dashboard with today's list, appointment states and unread messages
- Appointments: filter, change status, edit patient details, CSV export
- Manage the profile, credentials, expertise, chambers, weekly schedules, leave days, success stories,
  news/events/blog, testimonials, publications, gallery, FAQs, pages, hero slides, statistics,
  settings and staff users
- Every content field is editable in both languages side by side
- Two roles: `admin` (everything) and `editor` (content and appointments)

## Requirements

PHP 8.3+ with `pdo_mysql`, Composer, Node 20+, MySQL 8.

> This project runs on MySQL rather than SQLite because the target environment's PHP build ships
> without `pdo_sqlite`. The test suite uses a separate `dr_profile_test` schema.

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
# set DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env

mysql -u root -p -e "CREATE DATABASE dr_profile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
php artisan storage:link

npm run dev          # or: npm run build
php artisan serve
```

The seeder loads a complete demo practice in both languages — profile, three chambers with schedules,
ten areas of expertise, success stories, news, events, articles, testimonials, FAQs, publications and
sample appointments — so the site is fully populated on first run.

**Demo logins**

| Email | Password | Role |
|---|---|---|
| `admin@drprofile.test` | `password` | admin |
| `editor@drprofile.test` | `password` | editor |

Change these before deploying anywhere public.

## Tests

```bash
mysql -u root -p -e "CREATE DATABASE dr_profile_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan test
```

Covers slot generation, booking and double-booking, the booking window, locale routing and fallback,
admin authentication and authorisation, CRUD, and every public page in both languages.

## Configuration

`config/site.php` holds the locale list and the booking rules — how far ahead patients may book
(`window_days`), how close to a sitting online serials close (`lead_time_minutes`) and how many open
appointments one phone number may hold (`max_open_per_phone`).

Site name, contact details and the booking notice are editable in the admin under **Settings**.
