# Creators Page API & Filament

The creators landing page follows the **Team** pattern: page copy in **Settings**, lists in Filament resources, public API without navbar/footer.

Related: [TEAM_API.md](./TEAM_API.md)

## Overview

| Section | Controlled in | Stored in |
|---------|---------------|-----------|
| Hero | Settings → **صناع المحتوى** | `settings` |
| Creators grid | Settings (titles) + **Content Creators** resource | `settings` + `creators` |
| View-all listing | Settings → صفحة عرض الكل + **Content Creators** | `settings` + `creators` |
| Stats | Settings → صناع المحتوى (labels + values) | `settings` (creators group) |
| Global site stats | Settings → إحصائيات الواجهة | `settings` (stats group) |
| Join CTA | Settings → صناع المحتوى | `settings` |
| Partner companies | Settings (title) + **Partner Companies** | `settings` + `creator_partner_companies` |
| Collaboration steps | Settings → صناع المحتوى | `settings` (JSON repeater) |
| FAQ | Settings (title) + **Creator FAQs** | `settings` + `creator_faqs` |

---

## Filament

### Settings → صناع المحتوى

1. **الهيرو** — background, title, description  
2. **شبكة صناع المحتوى** — section title, browse label, view all, grid limit  
3. **الإحصائيات** — section title + stat labels (values in «إحصائيات الواجهة»)  
4. **دعوة الانضمام** — banner image, title, description, button  
5. **الشركات الشريكة** — section title + description  
6. **خطوات التعاون** — diagram labels + steps repeater + CTA  
7. **الأسئلة الشائعة** — section title, subtitle, side image  
8. **صفحة التفاصيل** — bio/followers/socials labels  

### Creators group (sidebar)

- **Content Creators** — profile cards (`role`, `avatar`, `sort_order`, `is_verified`, socials)  
- **Partner Companies** — logos + linked creators  
- **Creator FAQs** — Q&A accordion items  

---

## API

### Listing page

```
GET /api/v1/pages/creators
```

Returns: `hero`, `grid`, `stats`, `join`, `partners`, `collaboration`, `faq`

`join` includes the CTA image/button **and** the 3-step application form copy (`join.form`).

### Submit join request

```
POST /api/v1/pages/creators/join
```

```json
{
  "full_name": "محمد أحمد",
  "phone": "59999999",
  "country_code": "+970",
  "email": "mohamed@gmail.com",
  "content_types": ["art", "comedy"],
  "followers_count": 5000,
  "content_bio": "…",
  "socials": [{ "platform": "instagram", "url": "https://instagram.com/…" }],
  "notes": null
}
```

Admin review: **صنّاع المحتوى → طلبات الانضمام**

**Email uniqueness / reuse**

- If the email already belongs to a **content creator** → `422` `هذا البريد مسجّل مسبقاً كصانع محتوى.`
- If the email belongs to a normal **user**, the join request is allowed. On approve, that user is **reused** (upgraded).
- If a **pending** or **rejected** join request already exists for the email, the same row is **updated**.

**On admin approve**

1. If the email already exists as a content creator → stop (unique email).
2. If a normal user exists with that email → reuse it. Otherwise create a new `users` row from the request.
3. Create the `creators` profile from the request data.
4. Send the acceptance email.
5. **Delete** the join request (it does not stay as approved).

### View all (paginated)

```
GET /api/v1/pages/creators/all
```

Query: `?page=1&per_page=10&q=محمود`

Cards are managed in Filament **صنّاع المحتوى → Content Creators**.  
Per-page count and «متابع» suffix: Settings → صناع المحتوى → **صفحة عرض الكل**.

```json
{
  "data": {
    "hero": {
      "image_url": "…",
      "title": { "ar": "صناع المحتوى في صوت", "en": "…" },
      "description": { "ar": "…", "en": "…" }
    },
    "labels": {
      "followers_suffix": { "ar": "متابع", "en": "followers" }
    },
    "creators": [
      {
        "uuid": "abc12",
        "username": "mahmoud",
        "name": "محمود عبدالله زعيتر",
        "role": { "ar": "ممثل مسرحي", "en": "Stage actor" },
        "avatar_url": "…",
        "followers_count": 31400,
        "is_verified": true,
        "sort_order": 0
      }
    ]
  },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 10,
    "total": 47
  }
}
```

### Creator detail

```
GET /api/v1/pages/creators/{uuid}
```

**404:** `{ "error": "creator_not_found" }`

---

## Architecture

```
Api\CreatorsPageController
    → CreatorsPageService
        → CreatorPageRepository + SettingRepository
```

| Layer | Files |
|-------|--------|
| Controller | `app/Http/Controllers/Api/CreatorsPageController.php` |
| Service | `app/Services/CreatorsPageService.php` |
| Repository | `app/Repositories/CreatorPageRepository.php` |
| Resources | `CreatorCardResource`, `CreatorPartnerCompanyResource`, `CreatorFaqResource` |
| Routes | `routes/api/v1.php` |

---

## Example response (listing)

```json
{
  "data": {
    "hero": {
      "image_url": "…",
      "title": { "ar": "صناع المحتوى في صوت", "en": "…" },
      "description": { "ar": "…", "en": "…" }
    },
    "grid": {
      "title": { "ar": "+47 صانع محتوى ناجح في صوت", "en": "…" },
      "browse_label": { "ar": "تصفح", "en": "Browse" },
      "view_all": { "label": { "ar": "عرض الكل", "en": "…" }, "url": "/creators" },
      "creators": [
        {
          "uuid": "abc12",
          "username": "sara_voice",
          "name": "سارة خليل",
          "role": { "ar": "ممثل مسرحي", "en": "Stage actor" },
          "avatar_url": "…",
          "followers_count": 1200,
          "is_verified": true,
          "sort_order": 0
        }
      ]
    },
    "stats": {
      "title": { "ar": "إنجازات صناع محتوى صوت", "en": "…" },
      "items": [
        { "key": "reach", "value": 4000000, "label": { "ar": "…", "en": "…" }, "suffix": "+" }
      ]
    },
    "join": { "title": {}, "description": {}, "button": { "label": {}, "url": "…" } },
    "partners": {
      "title": {},
      "companies": [
        {
          "uuid": "xyz99",
          "name": { "ar": "شركة الإبداع", "en": "Creativity Co." },
          "logo_url": "…",
          "creators": []
        }
      ]
    },
    "collaboration": {
      "title": {},
      "diagram": {
        "creators": { "title": {}, "subtitle": {} },
        "media": { "image_url": "…", "title": {}, "subtitle": {} },
        "brands": { "title": {}, "subtitle": {} }
      },
      "steps": [
        { "number": 1, "text": { "ar": "…", "en": "…" } },
        { "number": 2, "text": { "ar": "…", "en": "…" } },
        { "number": 3, "text": { "ar": "…", "en": "…" } }
      ],
      "cta": { "label": { "ar": "تواصل مع فريق صوت للانضمام", "en": "…" } }
    },
    "faq": {
      "title": {},
      "items": [{ "uuid": "…", "question": {}, "answer": {} }]
    }
  }
}
```
