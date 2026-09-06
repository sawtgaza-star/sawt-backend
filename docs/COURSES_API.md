# Courses API

Offline incubator courses. Edited in Filament **الكورسات**.

All courses are **offline only** (`delivery.mode` is always `offline`).

DB: `2026_07_11_100001_create_courses_table.php` creates `courses` with the Filament offline/detail columns (`location`, schedule, `duration_*`, `rating`, `is_coming_soon`, `objectives`, `modules`, `outcomes_*`, `benefits`, `selection_steps`, …).

## Filament

**Sidebar → الكورسات**

| Tab | Content |
|-----|---------|
| أساسي | Title, slug, description, **incubator card image**, **course trainer**, **course category**, level, status |
| الجدول والمقاعد | Location, start/end, registration deadline, weeks, seats, card meta (hours, rating, coming soon) |
| أهداف البرنامج | Objectives cards + **icon** upload |
| محاور البرنامج | Modules + optional lessons with duration |
| المخرجات والمزايا | Before / after lists; benefits + **icon** |
| التسجيل والقبول | Requirements; selection steps + **icon** |

## Endpoints

| Method | Endpoint | Notes |
|--------|----------|-------|
| `GET` | `/api/v1/pages/courses` | Published listing (paginated) |
| `GET` | `/api/v1/pages/courses/{slugOrUuid}` | Full detail — **slug** (`graphic-design`) or **uuid** (`z56aj`) |

Also listed (cards only) inside `GET /api/v1/pages/incubator` → `courses.items`.

Public. No auth.

### Card shape (listing / incubator)

Card-only fields (full detail: `GET /pages/courses/{slugOrUuid}`).

```json
{
  "uuid": "…",
  "slug": "graphic-design",
  "title": { "ar": "…", "en": "…" },
  "description": {},
  "image_url": "…",
  "category": { "ar": "التصميم", "en": "Design" },
  "trainer": { "name": {}, "avatar_url": null },
  "level": { "ar": "مبتدئ", "en": "Beginner" },
  "duration_hours": "15 ساعة",
  "sessions_hours": "4 ساعات",
  "rating": 5,
  "is_coming_soon": false,
  "cta": { "key": "details", "label": { "ar": "تفاصيل الكورس", "en": "Course details" } }
}
```

When `is_coming_soon` is true, CTA becomes `waitlist` / «انضم لقائمة الانتظار».

### Detail shape

```json
{
  "data": {
    "uuid": "…",
    "slug": "graphic-design",
    "title": {},
    "description": {},
    "delivery": { "mode": "offline", "label": {} },
    "location": { "name": null, "details": null },
    "category": {},
    "level": {},
    "rating": 5,
    "is_coming_soon": false,
    "card": { "duration_hours": "15 ساعة", "sessions_hours": "4 ساعات" },
    "schedule": {
      "starts_at": "2026-09-07T00:00:00+00:00",
      "ends_at": null,
      "registration_ends_at": "2026-08-30T00:00:00+00:00",
      "duration_weeks": 4,
      "duration_label": { "ar": "4 أسابيع", "en": "4 weeks" },
      "modules_count": 7,
      "max_seats": 25
    },
    "objectives": [{ "icon_url": null, "title": {}, "description": {}, "sort_order": 0 }],
    "modules": [{
      "title": {},
      "lessons": [{ "title": {}, "duration": "15 دقيقة" }],
      "sort_order": 0
    }],
    "outcomes": {
      "before": [{ "ar": "…", "en": "…" }],
      "after": [{ "ar": "…", "en": "…" }]
    },
    "benefits": [{ "icon_url": null, "ar": "…", "en": "…" }],
    "requirements": [{ "ar": "…", "en": "…" }],
    "selection_steps": [{ "icon_url": null, "title": {}, "description": {}, "sort_order": 0 }],
    "trainer": {
      "uuid": "…",
      "name": "محمد العارف",
      "avatar_url": null,
      "title": {},
      "bio": {},
      "phone": null,
      "email": null,
      "socials": [{ "platform": "instagram", "url": "…" }]
    },
    "cta": { "key": "enroll", "label": { "ar": "اشترك الآن", "en": "Enroll now" } }
  }
}
```

Front owns routing (e.g. `/courses/{slug}`). Use `registration_ends_at` for the countdown.

**Note:** Course `image` is for **incubator listing cards only**. Detail page does **not** return `image_url` (front uses a static visual).
