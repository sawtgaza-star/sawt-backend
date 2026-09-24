# Creators Page API & Filament

The creators landing page follows the **Team** pattern: page copy in **Settings**, lists in Filament resources, public API without navbar/footer.

Related: [TEAM_API.md](./TEAM_API.md)

## Overview

| Section | Controlled in | Stored in |
|---------|---------------|-----------|
| Hero | Settings → **صناع المحتوى** | `settings` |
| Creators grid | Settings (titles) + **Content Creators** resource | `settings` + `creators` |
| View-all listing | Settings → صفحة عرض الكل + **Content Creators** | `settings` + `creators` |
| Creator detail | Settings (labels) + **Content Creators** + Instagram collab reels + Partner Companies | `settings` + `creators` + Instagram Graph |
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
8. **صفحة التفاصيل** — follow/bio/stats suffixes, content + collaborations titles, reels limit  
   Content videos = Instagram reels where the creator is a **collaborator** on the platform account (match via Instagram social URL). 

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

`{uuid}` accepts the public **uuid** or numeric **id** (e.g. `/creators/6`).

Optional query: `?reels_limit=12` (default from Settings `creators_detail_reels_limit`).

**404:** `{ "error": "creator_not_found" }`

#### How «المحتوى» videos are resolved

1. Read the creator’s **Instagram** social URL (Filament → Content Creators → مواقع التواصل).
2. If Settings `reels_enabled` is off → `content.status = disabled`, `items = []`, no Graph call.
3. Fetch Instagram reels from the **platform** account (same credentials as `GET /api/v1/reels`), with Graph `/collaborators` (limit 12 + extras).
4. Keep only reels where that Instagram username is an **accepted collaborator**.

If there is no Instagram link → `content.status = no_instagram` and `items = []`.
If reels are globally disabled → `content.status = disabled` and `collaborations.reel = null`.

```json
{
  "data": {
    "hero": { "image_url": "…", "title": {}, "description": {} },
    "creator": {
      "uuid": "abc12",
      "id": 6,
      "username": "mahmoud",
      "name": "محمود عبد الله زعيتر",
      "role": { "ar": "…", "en": "…" },
      "bio": { "ar": "…", "en": "…" },
      "avatar_url": "…",
      "is_verified": true,
      "instagram_username": "mahmoud_handle",
      "socials": [{ "platform": "instagram", "url": "https://instagram.com/…" }],
      "stats": { "views": 2000000, "followers": 500, "videos": 12 }
    },
    "labels": {
      "follow": { "ar": "متابعة", "en": "Follow" },
      "socials": { "ar": "تابعني على :", "en": "…" },
      "views_suffix": { "ar": "مشاهدة", "en": "views" },
      "followers_suffix": { "ar": "متابع", "en": "followers" },
      "videos_suffix": { "ar": "فيديو", "en": "videos" },
      "content_title": { "ar": "المحتوى", "en": "Content" },
      "collaborations_title": { "ar": "ابرز التعاونات", "en": "…" }
    },
    "content": {
      "title": {},
      "view_more": {},
      "instagram_username": "mahmoud_handle",
      "status": "ok",
      "message": null,
      "items": [
        {
          "id": "…",
          "caption": "…",
          "thumbnail": "…",
          "video_url": "…",
          "permalink": "…",
          "views": 12000,
          "collaborators": [{ "username": "mahmoud_handle", "invite_status": "Accepted" }],
          "sort_order": 0
        }
      ]
    },
    "collaborations": {
      "title": { "ar": "ابرز التعاونات", "en": "…" },
      "description": {},
      "reel": {
        "id": "…",
        "thumbnail": "…",
        "video_url": "…",
        "permalink": "…",
        "caption": "…"
      },
      "items": [
        {
          "uuid": "co1",
          "sort_order": 0,
          "company": {
            "uuid": "co1",
            "name": { "ar": "شركة الإبداع للإنتاج", "en": "…" },
            "category": { "ar": "إنتاج إعلامي", "en": "…" },
            "logo_url": "…"
          },
          "caption": { "ar": "محمود يمتلك…", "en": "…" },
          "rating": 5,
          "author": {
            "name": "رنا الصالح",
            "role": { "ar": "مدير الإنتاج", "en": "…" },
            "photo_url": "…"
          }
        }
      ]
    },
    "collaboration": {
      "title": { "ar": "كيف يبدأ التعاون مع صناع محتوى صوت؟", "en": "…" },
      "description": { "ar": "وصلنا شركات من حول العالم…", "en": "…" },
      "diagram": {
        "creators": { "title": {}, "subtitle": {} },
        "media": { "image_url": "…", "title": {}, "subtitle": {} },
        "brands": { "title": {}, "subtitle": {} }
      },
      "steps_title": { "ar": "خطوات التعاون", "en": "…" },
      "steps": [
        { "number": 1, "text": {} },
        { "number": 2, "text": {} },
        { "number": 3, "text": {} }
      ],
      "cta": {
        "label": { "ar": "تواصل مع فريق صوت للانضمام", "en": "…" }
      }
    },
    "join": {
      "image_url": "…",
      "title": {},
      "description": {},
      "button": { "label": {} }
    }
  }
}
```

> Detail `join` has **no** `form` (form stays on listing `GET /pages/creators` only).

> **Note:** `collaboration` (singular) = «كيف يبدأ التعاون…» diagram + steps.  
> `collaborations` (plural) = «أبرز التعاونات» company tabs + shared reel.

#### «أبرز التعاونات» rules

- Each **item** = one company linked to this creator with its **own caption / rating / author**.
- **`collaborations.reel`** = **one** latest reel from `InstagramService::reels(1)` — **same player for every company tab** (not per company).
- Front: switching the company list only updates caption card + list highlight; keep showing `collaborations.reel`.

#### Filament control (detail)

| Section | Where |
|---------|--------|
| Profile, Instagram link, views override | **Creators → Content Creators** |
| Per-company caption (quote, stars, author) | **Content Creators → tab أبرز التعاونات** (attach company) |
| Company name / category / logo | **Creators → Partner Companies** |
| Detail labels / content reels limit | Settings → صناع المحتوى → **صفحة تفاصيل صانع المحتوى** |
| How-it-works + join form copy | Settings → صناع المحتوى (same as listing) |
| Instagram token | Settings → ريلز إنستغرام |

---

## Architecture

```
Api\CreatorsPageController
    → CreatorsPageService
        → CreatorPageRepository + SettingRepository + InstagramService
```

| Layer | Files |
|-------|--------|
| Controller | `app/Http/Controllers/Api/CreatorsPageController.php` |
| Service | `app/Services/CreatorsPageService.php` |
| Repository | `app/Repositories/CreatorPageRepository.php` |
| Resources | `CreatorDetailResource`, `CreatorCollaborationItemResource`, `CreatorPartnerCompanyResource`, `CreatorFaqResource` |
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
