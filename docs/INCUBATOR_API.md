<?php

# Incubator Page API

Incubator chrome + landing page. Edited in Filament sidebar **الإعدادات → إعدادات الحاضنة**.

Navbar/footer are separate from the main site layout.

## Filament

**Sidebar → الإعدادات**

| Item | Page |
|------|------|
| الإعدادات العامة | Platform settings (home, about, header, footer, …) |
| إعدادات الحاضنة | Incubator header, footer, landing page |

### إعدادات الحاضنة tabs

| Tab | What it controls |
|-----|------------------|
| الهيدر | Logo, back-to-platform label, nav links, join/support CTAs |
| الفوتر | Logo, about, main links, Sawt section links, newsletter, copyright |
| الصفحة الأولى | Hero … employers, join CTA, **testimonials** (last) |

Courses **cards** come from published **Courses** resource. Full detail: `GET /api/v1/pages/courses/{slug}` (see `docs/COURSES_API.md`). All courses are offline.

Button **URLs are not editable** — the front app owns routing / hash anchors.

Save once to persist new keys.

## API

### Layout (incubator chrome)

| Method | Endpoint |
|--------|----------|
| `GET` | `/api/v1/layout/incubator` |
| `GET` | `/api/v1/layout/incubator/navbar` |
| `GET` | `/api/v1/layout/incubator/footer` |

Public. No auth.

### Landing page

```
GET /api/v1/pages/incubator
```

Public. No auth. **Does not include** navbar/footer — use layout endpoints above.

### Response shape (page)

```json
{
  "data": {
    "hero": {
      "background_url": null,
      "foreground_url": null,
      "image_url": null,
      "badges": {
        "top": { "value": "1,247", "label": { "ar": "هذا الشهر", "en": "This month" } },
        "bottom": { "value": "+340", "label": { "ar": "قصة وثقت", "en": "Stories documented" } }
      },
      "title": { "ar": "حوّل قصتك إلى محتوى يصنع أثرًا", "en": "…" },
      "description": { "ar": "…", "en": "…" },
      "cta": { "label": { "ar": "ابدأ رحلتك التعليمية", "en": "…" } }
    },
    "stats": [
      { "key": "students", "value": "+200", "label": { "ar": "طالب مسجّل", "en": "…" }, "sort_order": 0 }
    ],
    "why": {
      "title": {},
      "subtitle": {},
      "image_url": null,
      "items": [{ "icon_url": null, "title": {}, "description": {}, "sort_order": 0 }]
    },
    "courses": {
      "title": {},
      "subtitle": {},
      "items": [
        {
          "uuid": "…",
          "slug": "graphic-design",
          "title": {},
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
      ]
    },
    "sponsor": {
      "title": { "ar": "ساعد طلاب في الانضمام للحاضنة", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "packages": [
        {
          "title": { "ar": "صحافة ميدانية", "en": "Field journalism" },
          "description": {},
          "duration": { "ar": "8 أسابيع", "en": "8 weeks" },
          "seats": { "ar": "6 مقاعد", "en": "6 seats" },
          "price": "120",
          "currency": "$",
          "cta": { "label": { "ar": "تكفل دورة … ب 120$", "en": "…" } },
          "sort_order": 0
        }
      ],
      "waiting": {
        "title": {},
        "more_label": { "ar": "+28 طالباً آخرين", "en": "…" },
        "students": [
          {
            "name": "ريم س.",
            "meta": { "ar": "إنتاج مرئي، خانيونس", "en": "…" },
            "avatar_url": null,
            "sort_order": 0
          }
        ]
      },
      "impact": {
        "title": { "ar": "أثر البرنامج", "en": "…" },
        "stats": [
          { "value": "+340", "label": { "ar": "شخص يستفيد", "en": "…" }, "sort_order": 0 }
        ]
      }
    },
    "events": {
      "title": { "ar": "استكشف أحدث فعالياتنا", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "categories": [
        { "key": "all", "label": { "ar": "الكل", "en": "All" }, "count": 3, "sort_order": 0 },
        { "key": "economy", "label": { "ar": "الاقتصاد", "en": "Economy" }, "count": 1, "sort_order": 1 }
      ],
      "items": [
        {
          "image_url": null,
          "category_key": "economy",
          "title": {},
          "description": {},
          "starts_at": "2026-05-27T23:00:00+00:00",
          "date_badge": { "day": "27", "month": { "ar": "مايو", "en": "May" } },
          "date_label": { "ar": "الثلاثاء 27/05/2026", "en": "…" },
          "time_label": { "ar": "11:00 م", "en": "11:00 PM" },
          "delivery": { "key": "in_person", "label": { "ar": "وجاهي", "en": "In person" } },
          "format": { "key": "seminar", "label": { "ar": "ندوة", "en": "Seminar" } },
          "tags": { "ar": "وجاهي، ندوة", "en": "In person, Seminar" },
          "sort_order": 0
        }
      ]
    },
    "gallery": {
      "title": { "ar": "الحاضنة بيتك الثاني ، البوم الحاضنة", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "items": [
        {
          "slot": "left_top",
          "type": "image",
          "image_url": null,
          "video_url": null,
          "caption": { "ar": "يوم الإطلاق — الدفعة الثالثة", "en": "…" },
          "subtitle": { "ar": "", "en": "" },
          "sort_order": 0
        },
        {
          "slot": "right_tall",
          "type": "video",
          "image_url": null,
          "video_url": "https://…",
          "caption": { "ar": "مجتمع صانعي المحتوى", "en": "…" },
          "subtitle": {},
          "sort_order": 4
        }
      ]
    },
    "experts": {
      "title": { "ar": "فريق خبراء متخصص", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "items": [
        {
          "uuid": "…",
          "name": { "ar": "طارق الجبالي", "en": "…" },
          "title": { "ar": "…", "en": "…" },
          "experience": { "ar": "7 سنوات", "en": "7 years" },
          "bio": { "ar": "…", "en": "…" },
          "avatar_url": null,
          "link_url": null,
          "socials": [{ "platform": "linkedin", "url": "https://…" }],
          "sort_order": 0
        }
      ]
    },
    "faq": {
      "title": { "ar": "الأسئلة التي تدور ببالك؟", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "image_url": null,
      "items": [
        {
          "question": { "ar": "هل يمكنني نشر أعمالي بعد التدريب؟", "en": "…" },
          "answer": { "ar": "نعم، …", "en": "…" },
          "sort_order": 0
        }
      ],
      "more": {
        "title": { "ar": "لديك سؤال آخر؟", "en": "…" },
        "description": { "ar": "فريقنا جاهز للإجابة — سنردّ عليك خلال ساعات", "en": "…" }
      }
    },
    "employers": {
      "title": { "ar": "يعمل خريجونا لدى جهات موثوقة", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "items": [
        {
          "name": "IHG",
          "logo_url": null,
          "url": null,
          "sort_order": 0
        }
      ]
    },
    "join_cta": {
      "image_url": null,
      "title": {},
      "description": {},
      "button": { "label": {} }
    },
    "testimonials": {
      "title": { "ar": "شهادات وتجارب خريجينا", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "view_all": { "label": { "ar": "عرض الكل", "en": "View all" } },
      "read_more": { "label": { "ar": "اقرأ المزيد", "en": "Read more" } },
      "items": [
        {
          "name": "فهد النعيمي",
          "role": { "ar": "محلل بيانات — تقنية", "en": "…" },
          "quote": { "ar": "…", "en": "…" },
          "avatar_url": null,
          "rating": 5,
          "sort_order": 0
        }
      ]
    }
  }
}
```

### Navbar shape

```json
{
  "data": {
    "site_name": "حاضنة صوت",
    "logo_url": "…",
    "back_to_platform": { "key": "platform", "label": {} },
    "socials_label": { "ar": "وسائل التواصل الاجتماعي", "en": "Social Media" },
    "socials": [
      { "platform": "instagram", "url": "https://instagram.com/…" },
      { "platform": "twitter", "url": "https://x.com/…" }
    ],
    "topbar": {
      "socials_label": {},
      "socials": [
        { "platform": "instagram", "url": "https://instagram.com/…" }
      ],
      "language": { "label": { "ar": "En", "en": "Ar" } }
    },
    "nav": {
      "primary": [
        { "key": "about", "label": {} },
        { "key": "courses", "label": {} },
        { "key": "workshops", "label": {} }
      ]
    },
    "actions": {
      "join": { "key": "join", "label": {} },
      "support": { "key": "support_students", "label": {} }
    }
  }
}
```

Nav / action **URLs are not returned** — the front app owns routing from `key`. Social `url` values come from **الإعدادات العامة → التواصل الاجتماعي**. Only platforms with a non-empty URL are returned.

## Architecture

```
Api\IncubatorController@show
    → IncubatorService::page()
        → SettingRepository + Course (published list)

Api\LayoutController@incubator|incubatorNavbar|incubatorFooter
    → LayoutService::incubatorPage() | incubatorNavbar() | incubatorFooter()
```

Related: [LAYOUT_API.md](./LAYOUT_API.md), [HOME_API.md](./HOME_API.md)
