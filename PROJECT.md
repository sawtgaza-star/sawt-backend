# Sawt Platform — Project Documentation

**Sawt** (صوت) is a Laravel + Filament admin platform for managing media content creators, videos, fundraising campaigns, and donations. The admin panel is bilingual (**Arabic / English**), RTL-aware, and secured with roles & permissions.

---

## Table of contents

1. [Tech stack](#1-tech-stack)
2. [What the platform does](#2-what-the-platform-does)
3. [Project structure](#3-project-structure)
4. [Setup & local run](#4-setup--local-run)
5. [Admin panel (Filament)](#5-admin-panel-filament)
6. [Models & database](#6-models--database)
7. [Authentication & roles](#7-authentication--roles)
8. [Localization (AR / EN)](#8-localization-ar--en)
9. [Translations for content (Spatie)](#9-translations-for-content-spatie)
10. [Short UUID URLs](#10-short-uuid-urls)
11. [Uploads & images](#11-uploads--images)
12. [Global search](#12-global-search)
13. [Theme (light / dark / system)](#13-theme-light--dark--system)
14. [Caching & sessions](#14-caching--sessions)
15. [Settings page](#15-settings-page)
16. [Common artisan commands](#16-common-artisan-commands)
17. [Known notes & next steps](#17-known-notes--next-steps)

---

## 1. Tech stack

| Layer | Technology |
|---|---|
| PHP | `^8.2` |
| Framework | Laravel `^11` |
| Admin UI | Filament `^3.2` |
| Permissions | `spatie/laravel-permission` + `bezhansalleh/filament-shield` |
| Content translations | `spatie/laravel-translatable` + `filament/spatie-laravel-translatable-plugin` |
| API tokens | Laravel Sanctum |
| Database | MySQL (`sawt-platform`) |
| Local server | XAMPP / `php artisan serve` (`http://127.0.0.1:8000`) |

---

## 2. What the platform does

Sawt is built around:

- **Content**: categories, tags, videos (with comments, likes, views)
- **Creators**: profiles, social links, collaborations, join applications
- **Finance**: fundraising campaigns, donations, payment transactions
- **Admin**: users, roles/permissions (Shield), platform settings, dashboard KPIs

The **public frontend** is still minimal (`/`). Most work so far is the **admin panel** at `/admin`.

---

## 3. Project structure

```text
sawt-platform/
├── app/
│   ├── Filament/
│   │   ├── Pages/           # Custom pages (Settings)
│   │   ├── Resources/       # CRUD resources
│   │   └── Widgets/         # Dashboard widgets
│   ├── Http/Middleware/     # SetLocale, etc.
│   ├── Models/              # Eloquent models
│   │   └── Concerns/HasUuid.php
│   └── Providers/Filament/AdminPanelProvider.php
├── database/migrations/     # Schema
├── database/seeders/        # Roles, settings
├── lang/
│   ├── ar.json              # UI Arabic strings
│   ├── en.json              # UI English strings
│   └── vendor/filament-...  # Filament package translations
├── resources/views/filament/hooks/  # Language switcher UI
├── routes/web.php
├── public/storage/          # Public uploads (symlink)
└── storage/app/public/      # Source of uploaded files
```

---

## 4. Setup & local run

### Requirements

- PHP 8.2+, Composer, MySQL (XAMPP)
- Node optional (Vite) if you customize frontend assets

### Steps

```bash
cd d:\xampp\htdocs\sawt-platform
composer install
copy .env.example .env   # if needed
php artisan key:generate

# Create MySQL database: sawt-platform
# Configure DB_* in .env

php artisan migrate
php artisan db:seed              # if you use RolePermissionSeeder / SettingSeeder
php artisan storage:link
php artisan serve
```

Open: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

### Important `.env` values

| Key | Current / recommended | Meaning |
|---|---|---|
| `APP_URL` | `http://127.0.0.1:8000` | Must match the URL you open (affects image URLs) |
| `APP_LOCALE` | `ar` | Default UI language |
| `APP_FALLBACK_LOCALE` | `en` | Fallback language |
| `DB_DATABASE` | `sawt-platform` | MySQL database name |
| `SESSION_DRIVER` | `database` | Sessions table required |
| `CACHE_STORE` | `database` | Cache table required |
| `FILESYSTEM_DISK` | `public` | Uploads go to `storage/app/public` |

---

## 5. Admin panel (Filament)

Configured in `app/Providers/Filament/AdminPanelProvider.php`.

- Path: `/admin`
- Login: enabled
- Plugins: Filament Shield, Spatie Translatable
- Middleware includes `SetLocale`
- Top bar: **AR / EN** language toggle + global search
- Dark mode: enabled (Light / Dark / System)

### Resources (CRUD)

| Resource | Nav group | Main model | Notes |
|---|---|---|---|
| Users | Administration | `User` | Roles via Spatie |
| Settings | Administration | `Setting` | Custom Filament page |
| Campaigns | Finance | `Campaign` | Translatable title/description |
| Donations | Finance | `Donation` | Linked to campaigns |
| Categories | Content | `Category` | Translatable name |
| Tags | Content | `Tag` | Translatable name |
| Videos | Content | `Video` | Translatable title/description |
| Content Creators | Creators | `Creator` | Translatable bio |
| Join Requests | Creators | `CreatorApplication` | Approve / reject flow |

### Widgets

- `KpiOverview` — users, videos, creators, donations this month
- `DonationsChart` — last 6 months
- `TopVideosWidget` — most watched
- `LatestApplicationsWidget` — recent join requests

### Relation managers (examples)

- Video → Comments, Tags
- Creator → Socials, Collaborations
- Campaign → Donations
- Creator Application → Attached socials

---

## 6. Models & database

### Core tables

| Table | Purpose |
|---|---|
| `users` | Platform users / admins |
| `roles` / `permissions` / pivots | Spatie roles & permissions |
| `categories`, `tags`, `videos` | Content taxonomy & media |
| `video_tags`, `comments`, `likes`, `video_views` | Engagement |
| `creators`, `creator_socials`, `creator_collaborations` | Creator profiles |
| `creator_applications`, `creator_application_socials` | Join requests |
| `campaigns`, `donations`, `payment_transactions` | Fundraising |
| `settings` | Key/value platform config |
| `sessions` | DB sessions (`SESSION_DRIVER=database`) |
| `cache` / `cache_locks` | DB cache (`CACHE_STORE=database`) |
| `jobs` / `failed_jobs` | Queues |
| `personal_access_tokens` | Sanctum API tokens |

### Translatable JSON columns (Spatie)

Stored as JSON with locales like `{"ar":"...","en":"..."}`:

- `Category.name`
- `Tag.name`
- `Video.title`, `Video.description`
- `Campaign.title`, `Campaign.description`
- `Creator.bio`
- `CreatorCollaboration.description`

### Short UUID column

Most routable models have a `uuid` column (`varchar(5)`, unique) used in Filament edit URLs.

---

## 7. Authentication & roles

### Filament access

Defined in `app/Models/User.php`:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->status === 'active'
        && $this->hasAnyRole(['super_admin', 'admin', 'moderator']);
}
```

Only **active** users with one of those roles can open `/admin`.

### Roles seeder

`database/seeders/RolePermissionSeeder.php` creates:

- `super_admin`
- `admin`
- `moderator`

Filament Shield manages fine-grained permissions on resources (create/view/update/delete, etc.).

### Password hashing (important)

`User` casts `password` as `hashed`.

Do **not** call `Hash::make()` again in Filament forms — that double-hashes and breaks login. The form only passes the plain password; the model cast hashes once.

---

## 8. Localization (AR / EN)

### Two different systems

| What | Storage | How to switch |
|---|---|---|
| **UI labels** (sidebar, titles, buttons) | `lang/ar.json`, `lang/en.json` + `__()` | Top bar **AR / EN** |
| **Record content** (title, name, bio…) | DB JSON via Spatie | Locale dropdown on create/edit pages |

### How UI language works

1. User clicks AR/EN → route `lang.switch` (`/lang/{locale}`)
2. Locale saved in session: `session(['locale' => 'ar'|'en'])`
3. Middleware `App\Http\Middleware\SetLocale` runs and calls `App::setLocale(...)`
4. Resource labels use methods like `getNavigationLabel()` returning `__('Categories')`

### Key files

- `lang/ar.json`, `lang/en.json`
- `app/Http/Middleware/SetLocale.php`
- `routes/web.php` (`lang.switch`)
- `resources/views/filament/hooks/language-switcher.blade.php`
- Filament translations under `lang/vendor/filament-panels/`

---

## 9. Translations for content (Spatie)

### Panel plugin

Registered in `AdminPanelProvider`:

```php
SpatieLaravelTranslatablePlugin::make()
    ->defaultLocales(['ar', 'en'])
```

### Resource pages

Translatable resources use:

- Resource: `use Filament\Resources\Concerns\Translatable`
- List / Create / Edit pages: matching `Translatable` concern
- Header action: `Actions\LocaleSwitcher::make()`

That switcher is for editing **AR vs EN field values**, not for changing the whole admin UI language.

---

## 10. Short UUID URLs

Admin edit URLs use a **5-character** code instead of numeric IDs.

Example:

- Old: `/admin/campaigns/2/edit`
- New: `/admin/campaigns/fqsnm/edit`

### How it works

1. Trait: `App\Models\Concerns\HasUuid`
   - Generates unique 5-char code on create
   - Sets `getRouteKeyName()` to `uuid`
2. Resources set:

```php
protected static ?string $recordRouteKeyName = 'uuid';
```

### Models using short UUID routing

Users, Campaigns, Categories, Tags, Videos, Creators, Donations, Creator Applications.

---

## 11. Uploads & images

### Disk

Uploads use the **`public`** disk:

- Files: `storage/app/public/...`
- Public URL: `/storage/...` via `php artisan storage:link`

### Filament fields

File uploads and table image columns should specify:

```php
->disk('public')
->visibility('public')  // FileUpload
```

### Resources with images

| Resource | Fields |
|---|---|
| Campaigns | `image` |
| Videos | `cover_url` |
| Creators | `avatar`, `cover` |
| Users | `avatar` |
| Collaborations | `company_logo` |

If images don’t show, check:

1. `php artisan storage:link`
2. `APP_URL` matches your browser URL (`127.0.0.1:8000` vs `localhost`)
3. `FILESYSTEM_DISK=public`

---

## 12. Global search

Navbar search works only for resources that define searchable attributes.

Each main resource implements:

- `$recordTitleAttribute`
- `getGloballySearchableAttributes()`

Examples:

- Campaigns → `title`, `slug`, `uuid`
- Users → `name`, `email`, `phone`, `uuid`
- Videos → `title`, `slug`, `uuid`, `creator.username`

Type at least a few characters; results appear in the dropdown and open the edit (or view) page.

---

## 13. Theme (light / dark / system)

Configured with:

```php
->darkMode(true)
->defaultThemeMode(ThemeMode::System)
```

| Icon | Behavior |
|---|---|
| Sun | Always light |
| Moon | Always dark |
| Monitor (System) | Follows **Windows / OS** light-dark preference |

If Windows is in Light mode, **System** stays light — that is expected. Use the Moon icon to force dark.

---

## 14. Caching & sessions

### Sessions

- Driver: `database`
- Table: `sessions`
- Used for login state and selected locale

### Cache

- Driver: `database`
- Table: `cache`
- **Not** file-based when `CACHE_STORE=database`
- Typical keys: `spatie.permission.cache`, Livewire rate limiters
- Safe to clear: `php artisan cache:clear`

### Other caches

| Path | Purpose |
|---|---|
| `bootstrap/cache/` | Compiled config/routes/packages |
| `storage/framework/views/` | Compiled Blade |
| `storage/framework/cache/` | Only used if `CACHE_STORE=file` |

---

## 15. Settings page

Custom Filament page: `app/Filament/Pages/Settings.php`

- Stores key/value pairs in `settings` via `Setting::get()` / `Setting::set()`
- Groups: general, payment, finance split, contact, social, stats
- Seeded by `SettingSeeder` (defaults match the form keys)

---

## 16. Common artisan commands

```bash
# Database
php artisan migrate
php artisan migrate:fresh --seed   # WARNING: wipes data
php artisan db:seed --class=RolePermissionSeeder

# Filament
php artisan filament:assets
php artisan filament:upgrade
php artisan shield:generate        # regenerate permissions (Shield)

# Storage & cache
php artisan storage:link
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear

# Dev server
php artisan serve
```

---

## 17. Known notes & next steps

### Done in this project (admin foundation)

- Filament admin with Shield roles
- Bilingual UI (`ar` / `en`) + Spatie content translations
- Short UUID routes for all main resources
- Public-disk image uploads
- Global search attributes
- Dashboard KPIs / charts / widgets
- Platform settings page

### Typical next work

- Public website (creator profiles, video pages, donate checkout)
- Payment gateway integration (PayPal / card) tied to `payment_transactions`
- Email notifications for applications & donations
- API with Sanctum for mobile / frontend apps
- Stronger tests (Feature tests for admin + payments)

### Git / Windows note

If Git shows *dubious ownership* under XAMPP, you may need:

```bash
git config --global --add safe.directory D:/xampp/htdocs/sawt-platform
```

---

## Quick reference — admin login

1. Ensure user `status = active`
2. Assign role: `super_admin` / `admin` / `moderator`
3. Password must be hashed **once** (model cast)
4. Open `/admin/login`

---

*Last updated for the Sawt platform codebase as of the current Filament 3 admin setup.*
