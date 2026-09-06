# Team Page API & Filament

Team pages follow the **About** pattern: shared page texts in **Settings**, members/majors in Filament resources. Header/footer: [LAYOUT_API.md](./LAYOUT_API.md).

Related: [ABOUT_API.md](./ABOUT_API.md)

## Overview

| Section | Controlled in | Stored in |
|---------|---------------|-----------|
| Hero | Settings → **الفريق** | `settings` |
| Listing filters («الكل») | Settings → الفريق | `settings` |
| Member detail labels + bottom intro strip | Settings → الفريق (قسم صفحة التفاصيل) | `settings` |
| Majors / tabs | **الفريق → الأقسام** | `majors` |
| Member card + profile | **الفريق → أعضاء الفريق** | `team_members` |

---

## Filament

### Settings → الفريق

1. **الهيرو** — background, title, description  
2. **فلتر الأقسام** — «الكل» label  
3. **صفحة تفاصيل العضو** — bio/experience/follow labels, intro image+text, related-members title, «عرض الكل», related limit  

Social links per member: **الفريق → أعضاء الفريق → تاب صفحة التفاصيل** (not Settings).

### Team Members resource

Tabs:

- **البطاقة** — major, name, role, photo, sort, active  
- **صفحة التفاصيل** — years of experience, bio (translatable), Facebook / LinkedIn / X / Instagram  

### Majors resource

Category tabs with counts on the listing API.

---

## Database

### `team_members` (profile fields)

| Column | Notes |
|--------|--------|
| `years_of_experience` | int, nullable |
| `bio` | JSON translatable |
| `photo` | Upload under `team/members/` |
| `facebook_url`, `linkedin_url`, `twitter_url`, `instagram_url` | nullable strings |

Migration: `2026_08_05_153600_add_profile_fields_to_team_members_table.php`

---

## API

### Listing

```
GET /api/v1/pages/team
```

Optional: `?major=design` or `?major_uuid=xxxxx`

Returns: `hero`, `filters`, `majors`, `members`

```json
{
  "data": {
    "hero": {
      "image_url": "…",
      "title": { "ar": "صناع الأثر.. الفريق خلف منصة صوت", "en": "…" },
      "description": { "ar": "", "en": "" }
    },
    "filters": {
      "all_label": { "ar": "الكل", "en": "All" }
    },
    "majors": [
      {
        "uuid": "…",
        "name": { "ar": "التصميم", "en": "Design" },
        "slug": "design",
        "sort_order": 1,
        "members_count": 3
      }
    ],
    "members": [
      {
        "uuid": "…",
        "id": 1,
        "image": "…",
        "name": { "ar": "سمير البطل", "en": "…" },
        "role": { "ar": "UI/UX Designer", "en": "…" },
        "major": {
          "uuid": "…",
          "name": { "ar": "التصميم", "en": "Design" },
          "slug": "design"
        }
      }
    ]
  }
}
```

- `image` — member `photo_url` for listing cards.  
- Homepage `GET /api/v1/pages/home` → `team` section uses the same card fields.

Use `majors` for filter tabs; filter members with `?major=design` or `?major_uuid=…`.

### Member detail (this page)

```
GET /api/v1/pages/team/{uuid}
```

**404:** `{ "error": "member_not_found" }`

```json
{
  "data": {
    "hero": { "…": "…" },
    "member": {
      "uuid": "…",
      "name": { "ar": "سمير البطل", "en": "…" },
      "role": { "ar": "UI/UX Designer", "en": "UI/UX Designer" },
      "years_of_experience": 5,
      "bio": { "ar": "…", "en": "…" },
      "photo_url": "…",
      "socials": {
        "facebook": "https://…",
        "linkedin": null,
        "twitter": null,
        "instagram": null
      },
      "sort_order": 0,
      "major": { "uuid": "…", "name": {}, "slug": "design" }
    },
    "labels": {
      "bio": { "ar": "نبذة عنه", "en": "About" },
      "experience_suffix": { "ar": "سنوات من الخبرة", "en": "years of experience" },
      "follow": { "ar": "تابعنا على :", "en": "Follow us on:" }
    },
    "intro": {
      "image_url": "…",
      "body": { "ar": "…", "en": "…" }
    },
    "related": {
      "title": { "ar": "اعضاء الفريق", "en": "Team Members" },
      "view_all": {
        "label": { "ar": "عرض الكل", "en": "View all" },
        "url": "/team"
      },
      "members": [ /* same shape as listing cards */ ]
    }
  }
}
```

Frontend can render experience as:  
`{years_of_experience} {labels.experience_suffix.ar}` → «5 سنوات من الخبرة».

---

## Architecture

```
Api\TeamController@index|show
    → TeamService::page() | ::member()
        → TeamRepository + SettingRepository
```
