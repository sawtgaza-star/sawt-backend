# Team API & Filament

Documentation for the **Team** feature (majors tabs + team members).

Related: [AUTH_API.md](./AUTH_API.md) — JWT register / login / logout.

## Overview

The team page shows:

1. **Majors** — tabs/filters (e.g. Design Team, Marketing Team)
2. **Team members** — cards linked to a major (photo, name, role)

Majors and members are managed from the Filament admin panel. The public API returns both for the frontend.

---

## Database

### `majors`

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint | PK |
| `uuid` | string | Unique route key |
| `name` | json | Translatable (ar / en) |
| `slug` | string | Unique, used in API filter (`?major=design`) |
| `sort_order` | int | Tab order |
| `is_active` | bool | Soft hide without delete |
| `timestamps` | | |

### `team_members`

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint | PK |
| `uuid` | string | Unique |
| `major_id` | FK → `majors` | Cascade on delete |
| `name` | json | Translatable |
| `role` | json | Job title (e.g. UI/UX Designer) |
| `photo` | string | Stored on `public` disk under `team/members` |
| `sort_order` | int | Card order |
| `is_active` | bool | |
| `timestamps` | | |

**Relation:** `Major` hasMany `TeamMember` · `TeamMember` belongsTo `Major`

Migration: `database/migrations/2026_08_04_120000_create_team_tables.php`

---

## Filament (Admin)

Navigation group: **الفريق / Team**

| Resource | Path | Purpose |
|----------|------|---------|
| Majors | `MajorResource` | CRUD for tabs (name, slug, sort, active) |
| Team Members | `TeamMemberResource` | CRUD for members; select major via relation |

**Suggested order:** create majors first, then add members and assign each to a major.

---

## Architecture

```
Api\TeamController
    → TeamService
        → TeamRepository (TeamRepositoryInterface)
            → Major / TeamMember models
```

| Layer | Files |
|-------|--------|
| Models | `app/Models/Major.php`, `app/Models/TeamMember.php` |
| Repository | `app/Repositories/TeamRepository.php` |
| Service | `app/Services/TeamService.php` |
| Controller | `app/Http/Controllers/Api/TeamController.php` |
| Resources | `app/Http/Resources/MajorResource.php`, `TeamMemberResource.php` |
| Binding | `AppServiceProvider` → `TeamRepositoryInterface` → `TeamRepository` |

---

## API

Base URL: `/api/v1`

### `GET /api/v1/team`

Returns all active majors (with member counts) and all active members.

**Optional filters**

| Query | Example | Description |
|-------|---------|-------------|
| `major` | `?major=design` | Filter members by major slug |
| `major_uuid` | `?major_uuid=abc12` | Filter members by major uuid |

**Success `200`**

```json
{
  "data": {
    "majors": [
      {
        "uuid": "xxxxx",
        "name": { "ar": "فريق التصميم", "en": "Design Team" },
        "slug": "design",
        "sort_order": 1,
        "members_count": 3
      }
    ],
    "members": [
      {
        "uuid": "yyyyy",
        "name": { "ar": "سمير البطل", "en": "Samir Al-Batal" },
        "role": { "ar": "مصمم واجهات", "en": "UI/UX Designer" },
        "photo_url": "http://.../storage/team/members/...",
        "sort_order": 0,
        "major": {
          "uuid": "xxxxx",
          "name": { "ar": "فريق التصميم", "en": "Design Team" },
          "slug": "design"
        }
      }
    ]
  }
}
```

**Major not found `404`**

If `major` or `major_uuid` does not match an active major:

```json
{
  "message": "لا يوجد قسم (major) بهذا الاسم.",
  "error": "major_not_found"
}
```

---

## Postman quick test

1. Create majors + members in Filament.
2. `GET http://127.0.0.1/sawt-platform/public/api/v1/team`
3. `GET .../api/v1/team?major=design`
4. `GET .../api/v1/team?major=not-real` → expect 404

Header: `Accept: application/json`

---

## Notes

- Only `is_active = true` majors and members are returned by the API.
- Inactive majors cannot be used as filters (treated as not found).
- Translatable fields use Spatie + Filament locale switcher (ar / en).
