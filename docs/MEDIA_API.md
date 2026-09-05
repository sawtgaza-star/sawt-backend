# Sawt Media Page API

Sawt Media chrome + landing page. Edited in Filament sidebar **الإعدادات → إعدادات ميديا** (under إعدادات الحاضنة).

Navbar/footer are **separate** from the main platform and from incubator layout.

## Filament

**Sidebar → الإعدادات**

| Item | Page |
|------|------|
| الإعدادات العامة | Platform settings |
| إعدادات الحاضنة | Incubator header, footer, landing |
| إعدادات ميديا | Media header, footer, landing |

### إعدادات ميديا tabs

| Tab | What it controls |
|-----|------------------|
| الهيدر | Logo, back-to-platform, nav links, start-project / book-consultation CTAs |
| الفوتر | Logo, about, main links, Sawt section links, newsletter, copyright |
| الصفحة الأولى | Landing section chrome (hero, about, …). Service **cards** are managed under صوت ميديا → خدمات ميديا |
| تفاصيل الخدمة | Shared hero banner + bottom CTA on `/media/services/{slug}` (images + copy) |
| صفحة التواصل | Contact page (`/media/contact`) — target of ابدأ مشروعك |

**خدمات ميديا** / **أعمال ميديا** / **طلبات الاستشارة** (sidebar group صوت ميديا):
services, works, and consultation inbox (`media_consultation_requests`).

Button **URLs are not editable** — the front app owns routing / hash anchors (`LayoutLinks::MEDIA_PAGE_PATHS`).  
`start_project` → `/media/contact`. Landing consultation form stays at `/media#consultation`.

Consultation form submit: `POST /api/v1/pages/media/consultation` (see below). Admin **قبول/رفض** emails the applicant.

Save once to persist new keys.

## API

### Layout (media chrome)

| Method | Endpoint |
|--------|----------|
| `GET` | `/api/v1/layout/media` |
| `GET` | `/api/v1/layout/media/navbar` |
| `GET` | `/api/v1/layout/media/footer` |

Public. No auth.

### Landing page

```
GET /api/v1/pages/media
```

Public. No auth. **Does not include** navbar/footer — use layout endpoints above.

### Contact page («ابدأ مشروعك»)

```
GET /api/v1/pages/media/contact
```

Public. No auth. Matches [sawtgaza.com/media/contact](https://sawtgaza.com/media/contact).  
Navbar / hero / packages CTA `key: start_project` resolves to path `/media/contact`.

```json
{
  "data": {
    "hero": { "title": {}, "subtitle": {} },
    "intro": { "title": {}, "body": {} },
    "channels": {
      "whatsapp": {
        "key": "whatsapp",
        "label": {},
        "hint": {},
        "value": "+972…",
        "href": "https://wa.me/972…"
      },
      "email": {
        "key": "email",
        "label": {},
        "hint": {},
        "value": "info@sawtgaza.com",
        "href": "mailto:info@sawtgaza.com"
      }
    },
    "trust": { "value": "+150", "label": {} },
    "cta_key": "start_project"
  }
}
```

WhatsApp / email: media overrides if set, otherwise `support_whatsapp` / `contact_phone` / `contact_email` from الإعدادات العامة.

### Book consultation («احجز استشارتك»)

```
POST /api/v1/pages/media/consultation
```

Public. No auth. Landing form at `/media#consultation`.  
Service options come from `GET /api/v1/pages/media` → `consultation.form.services` (or any active service slug/uuid).

**Body (JSON):**

| Field | Required | Notes |
|-------|----------|--------|
| `name` | yes | Full name |
| `email` | yes | Valid email |
| `phone` | yes | Local number |
| `country_code` | no | e.g. `+970` |
| `service` | yes | Active service **slug** or **uuid** |

```json
{
  "name": "محمد احمد",
  "email": "mohamed@gmail.com",
  "phone": "59999999",
  "country_code": "+970",
  "service": "photography"
}
```

**201** → request stored as `pending`. Admin: **صوت ميديا → طلبات الاستشارة** (قبول / رفض → email to `email`).

```json
{
  "message": "تم استلام طلب الاستشارة، سنتواصل معك قريباً.",
  "data": {
    "uuid": "gqltr",
    "name": "محمد احمد",
    "email": "mohamed@gmail.com",
    "phone": "59999999",
    "country_code": "+970",
    "service": { "slug": "photography", "title": "التصوير الاحترافي" },
    "status": "pending",
    "created_at": "…"
  }
}
```

**422** → validation errors (`message` + `errors`).

### Works list (/media/works)

```
GET /api/v1/pages/media/works
```

Public archive page ([sawtgaza.com/media/works](https://sawtgaza.com/media/works/)).  
Returns **all active** works plus filter options — frontend filters client-side.

| Filter | UI label | Match items by |
|--------|----------|----------------|
| `filters.services` | القسم | `items[].service.value` (= service slug) |
| `filters.tags` | التخصص | `items[].tag.value` |

```json
{
  "data": {
    "path": "/media/works",
    "hero": {
      "title": { "ar": "…", "en": "…" },
      "breadcrumb": {
        "home": { "key": "home", "path": "/media", "label": {} },
        "current": {}
      }
    },
    "filters": {
      "services": {
        "key": "service",
        "label": { "ar": "القسم", "en": "Section" },
        "placeholder": { "ar": "—", "en": "—" },
        "options": [
          { "uuid": "…", "slug": "video", "value": "video", "label": {} }
        ]
      },
      "tags": {
        "key": "tag",
        "label": { "ar": "التخصص", "en": "Specialty" },
        "placeholder": { "ar": "—", "en": "—" },
        "options": [
          { "value": "app-design", "label": { "ar": "تصميم التطبيقات", "en": "App design" } }
        ]
      }
    },
    "items": [
      {
        "slug": "film",
        "category": {},
        "date": {},
        "title": {},
        "description": {},
        "image_url": null,
        "service": { "slug": "video", "value": "video", "label": {} },
        "tag": { "value": "app-design", "label": {} },
        "sort_order": 0
      }
    ]
  }
}
```

Landing «أبرز أعمالنا» (only `show_on_landing`) still comes from `GET /api/v1/pages/media` → `works`.

### Work detail («نماذج من أعمالنا»)

```
GET /api/v1/pages/media/works/{slugOrUuid}
```

Public. Example: [sawtgaza.com/media/works/film](https://sawtgaza.com/media/works/film).  
Accepts **slug** or **uuid**. Managed in **صوت ميديا → أعمال ميديا** (link to a service for the service-detail samples section).

Hero + bottom CTA chrome = same shared settings as service detail (**إعدادات ميديا → تفاصيل الخدمة**).

### Services list

```
GET /api/v1/pages/media/services
```

Public. Active services from table `media_services` + section chrome from settings.

```json
{
  "data": {
    "eyebrow": {},
    "title": {},
    "subtitle": {},
    "cta": { "label": {} },
    "items": [
      {
        "slug": "photography",
        "uuid": "noryd",
        "path": "/media/services/photography",
        "number": "01",
        "title": {},
        "tagline": {},
        "description": {},
        "tags": { "ar": ["تصوير المنتجات"], "en": ["Drone"] },
        "image_url": null,
        "sort_order": 0
      }
    ]
  }
}
```

### Services options (dropdown)

```
GET /api/v1/pages/media/services/options
```

Public. Lean list for selects / consultation form — **id, uuid, slug, name** only.

```json
{
  "data": [
    {
      "id": 1,
      "uuid": "horyd",
      "slug": "photography",
      "name": { "ar": "التصوير الاحترافي", "en": "Professional photography" }
    }
  ]
}
```

### Service detail page

```
GET /api/v1/pages/media/services/{slugOrUuid}
```

Public. No auth. `{slugOrUuid}` accepts **slug** (e.g. `photography`) or **uuid** (e.g. `noryd`).  
Example: [sawtgaza.com/media/services/photography](https://sawtgaza.com/media/services/photography).

Edited in **صوت ميديا → خدمات ميديا** (table `media_services`). Shared detail titles/CTA chrome: **إعدادات ميديا → الصفحة الأولى → الخدمات**.

`404` when slug is missing (`error: media_service_not_found`).

```json
{
  "data": {
    "uuid": "noryd",
    "slug": "photography",
    "path": "/media/services/photography",
    "hero": {
      "image_url": null,
      "title": {},
      "breadcrumb": {
        "home": { "key": "media", "path": "/media", "label": {} },
        "services": { "key": "services", "path": "/media#services", "label": {} },
        "current": {}
      }
    },
    "service": {
      "number": "01",
      "title": {},
      "tagline": {},
      "image_url": null,
      "gallery": [{ "url": "…", "sort_order": 0 }]
    },
    "includes": {
      "title": {},
      "body": {},
      "items": { "ar": ["…"], "en": ["…"] }
    },
    "works": {
      "title": {},
      "more": { "key": "works", "path": "/media#works", "label": {} },
      "items": [
        {
          "category": {},
          "date": {},
          "title": {},
          "description": {},
          "image_url": null,
          "sort_order": 0
        }
      ]
    },
    "cta": {
      "image_url": null,
      "title": {},
      "body": {},
      "button": { "key": "start_project", "path": "/media/contact", "label": {} }
    }
  }
}
```

Landing `services.items[]` also include `slug` + `path` so the front can link cards to this endpoint.

### Response shape (page)

```json
{
  "data": {
    "hero": {
      "eyebrow": {},
      "phrases": [{ "label": { "ar": "إنتاج الفيديوهات", "en": "Video production" }, "sort_order": 0 }],
      "description": {},
      "cta": {
        "primary": { "key": "start_project", "label": {} },
        "secondary": { "key": "services", "label": {} }
      },
      "badge": { "value": "98%", "label": {} }
    },
    "about": {
      "eyebrow": {},
      "title": {},
      "body": {},
      "vision": { "title": {}, "text": {} },
      "mission": { "title": {}, "text": {} },
      "images": [
        { "key": "top_start", "url": null, "sort_order": 0 },
        { "key": "top_end", "url": null, "sort_order": 1 },
        { "key": "bottom_start", "url": null, "sort_order": 2 },
        { "key": "bottom_end", "url": null, "sort_order": 3 }
      ],
      "badge": { "value": "98%", "label": {} }
    },
    "stats": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "items": [{ "value": "120+", "label": {}, "sort_order": 0 }]
    },
    "services": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "cta": { "label": {} },
      "items": [
        {
          "slug": "photography",
          "path": "/media/services/photography",
          "number": "01",
          "title": {},
          "tagline": {},
          "description": {},
          "tags": { "ar": ["تصوير المنتجات"], "en": ["Drone"] },
          "image_url": null,
          "sort_order": 0
        }
      ]
    },
    "why": { "eyebrow": {}, "title": {}, "subtitle": {}, "items": [] },
    "methodology": { "eyebrow": {}, "title": {}, "subtitle": {}, "steps": [] },
    "works": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "more": { "label": {} },
      "items": []
    },
    "audiences": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "items": [
        {
          "title": {},
          "tagline": {},
          "description": {},
          "bullets": { "ar": ["…"], "en": ["…"] },
          "sort_order": 0
        }
      ]
    },
    "partners": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "items": [{ "name": "IHG", "logo_url": null, "url": null, "sort_order": 0 }]
    },
    "consultation": {
      "eyebrow": {},
      "title": {},
      "body": {},
      "bullets": { "ar": [], "en": [] },
      "form": {
        "title": {},
        "submit_path": "/api/v1/pages/media/consultation",
        "method": "POST",
        "fields": { "name": {}, "phone": {}, "country_code": {}, "email": {}, "service": {} },
        "services": [{ "uuid": "…", "slug": "photography", "value": "photography", "label": {} }],
        "submit": { "label": {} }
      }
    },
    "packages": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "cta": { "key": "start_project", "label": {} },
      "items": [
        {
          "title": {},
          "tagline": {},
          "description": {},
          "features": {
            "ar": [{ "title": "…", "description": "…" }],
            "en": []
          },
          "sort_order": 0
        }
      ]
    },
    "testimonials": { "eyebrow": {}, "title": {}, "subtitle": {}, "items": [] },
    "faq": { "eyebrow": {}, "title": {}, "subtitle": {}, "items": [] }
  }
}
```

### Navbar shape (summary)

```json
{
  "data": {
    "site_name": "صوت ميديا",
    "logo_url": null,
    "back_to_platform": { "key": "platform", "label": {} },
    "topbar": { "language": { "label": { "ar": "En", "en": "Ar" } } },
    "nav": {
      "primary": [
        { "key": "methodology", "label": {} },
        { "key": "services", "label": {} },
        { "key": "works", "label": {} },
        { "key": "about", "label": {} }
      ]
    },
    "actions": {
      "start_project": { "key": "start_project", "path": "/media/contact", "label": {} }
    }
  }
}
```

Nav keys map to paths in `LayoutLinks::MEDIA_PAGE_PATHS` (e.g. `services` → `/media#services`).

Navbar has **no** `socials` / `contact`. Those appear only on **footer**.

### Footer shape (summary)

```json
{
  "data": {
    "logo_url": null,
    "about": {},
    "main": { "title": {}, "links": [] },
    "sawt": { "title": {}, "links": [] },
    "newsletter": {},
    "contact": { "phone": "", "email": "" },
    "socials_label": {},
    "socials": [{ "platform": "instagram", "url": "https://..." }],
    "copyright": {},
    "brand": "SAWTGAZA"
  }
}
```

`contact` and `socials` use the shared values from **الإعدادات العامة** (phone/email + social URLs). Empty social URLs are omitted.
