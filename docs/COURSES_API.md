# Courses API

Offline incubator courses. Edited in Filament **الكورسات**.

All courses are **offline only** (`delivery.mode` is always `offline`).

DB: `2026_07_11_100001_create_courses_table.php` creates `courses` with the Filament offline/detail columns (`location`, schedule, `duration_*`, `rating`, `is_coming_soon`, `objectives`, `modules`, `outcomes_*`, `benefits`, `selection_steps`, …).

## Filament

**Sidebar → الكورسات**

| Item | Content |
|------|---------|
| **الكورسات** | Course CRUD (tabs below) |
| **طلبات الانضمام** | Waitlist / enroll requests — accept or reject |

| Tab | Content |
|-----|---------|
| أساسي | Title, slug, description, **incubator card image**, **course trainer**, **course category**, level, status |
| الجدول والمقاعد | Location, start/end, registration deadline, weeks, seats, card meta (hours, rating, coming soon) |
| أهداف البرنامج | Objectives cards + **icon** upload |
| محاور البرنامج | Modules + optional lessons with duration |
| المخرجات والمزايا | Before / after lists; benefits + **icon** |
| التسجيل والقبول | Requirements; selection steps + **icon** |

## Endpoints

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `GET` | `/api/v1/pages/courses` | Public | Published listing (paginated) |
| `GET` | `/api/v1/pages/courses/{slugOrUuid}` | Public | Full detail — **slug** or **uuid** |
| `POST` | `/api/v1/pages/courses/{slugOrUuid}/join` | **JWT** (`Authorization: Bearer …`) | Waitlist / enroll request → Filament **طلبات الانضمام** |

Also listed (cards only) inside `GET /api/v1/pages/incubator` → `courses.items`.

### Join / waitlist (`POST …/join`)

1. Front sees CTA `key: waitlist` or `enroll` with `requires_auth: true`.
2. If guest → open login/register (`POST /api/v1/auth/login` or `/register`), then retry.
3. Authenticated → `POST` with optional body (defaults to profile):

```json
{
  "full_name": "أحمد",
  "phone": "+970…",
  "email": "a@example.com",
  "message": "أرغب بالانضمام"
}
```

**201** returns the pending request plus a `confirmation` object for the success modal:

```json
{
  "message": "مرحباً محمد، تم تسجيلك بقائمة الانتظار بنجاح",
  "data": {
    "uuid": "…",
    "status": "pending",
    "email_masked": "m***@example.com",
    "course": { "uuid": "…", "slug": "…", "title": {}, "is_coming_soon": true },
    "confirmation": {
      "title": "مرحباً محمد، تم تسجيلك بقائمة الانتظار بنجاح",
      "subtitle": "الكورس حالياً قيد الإعداد، تم إضافتك إلى قائمة الانتظار وسنتواصل معك فور توفره.",
      "user_name": "محمد …",
      "course_name": { "ar": "…", "en": "…" },
      "course_status": { "key": "preparing", "label": { "ar": "قيد الإعداد", "en": "Under preparation" } },
      "email_notice": "سيصلك إشعار على بريدك: m***@example.com",
      "email_masked": "m***@example.com",
      "cta": {
        "key": "browse_courses",
        "label": { "ar": "تصفح كورسات ثانية", "en": "Browse other courses" },
        "path": "/api/v1/pages/courses",
        "url": "/incubator"
      }
    }
  }
}
```

Coming-soon courses (`is_coming_soon`) skip seat limits so waitlist stays open.

Admin **قبول** / **رفض** queues `SendCourseJoinStatusEmailJob` (database queue). Email CTAs use `FRONTEND_URL` (not `APP_URL`).

### Email / front URLs

| Env | Example | Purpose |
|-----|---------|---------|
| `APP_URL` | `https://api.example.com` | Laravel / API |
| `FRONTEND_URL` | `https://sawtgaza.com` | Buttons in emails |
| `FRONTEND_COURSE_PATH` | `/courses/{slug}` | Course page path |
| `FRONTEND_INCUBATOR_PATH` | `/incubator` | Browse courses |

### Queue in production

Keep `QUEUE_CONNECTION=database` and either:

1. **Cron (shared hosting):** every minute run `php artisan schedule:run` — app already schedules `queue:work --stop-when-empty`.
2. **Supervisor (VPS):** long-running `php artisan queue:work --sleep=3 --tries=3`.

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
  "cta": {
    "key": "details",
    "label": { "ar": "تفاصيل الكورس", "en": "Course details" }
  }
}
```

When `is_coming_soon` is true, CTA becomes:

```json
{
  "key": "waitlist",
  "requires_auth": true,
  "method": "POST",
  "path": "/api/v1/pages/courses/digital-content-marketing/join",
  "label": { "ar": "انضم لقائمة الانتظار", "en": "Join the waitlist" }
}
```

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
    "cta": {
      "key": "enroll",
      "requires_auth": true,
      "method": "POST",
      "path": "/api/v1/pages/courses/graphic-design/join",
      "label": { "ar": "اشترك الآن", "en": "Enroll now" }
    }
  }
}
```

Front owns routing (e.g. `/courses/{slug}`). Use `registration_ends_at` for the countdown.

**Note:** Course `image` is for **incubator listing cards only**. Detail page does **not** return `image_url` (front uses a static visual).
