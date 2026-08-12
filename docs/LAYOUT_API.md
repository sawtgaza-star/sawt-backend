# Layout API (Header & Footer)

Shared site chrome from Filament **Settings → الهيدر** and **Settings → الفوتر**. Other page APIs (about, team, creators) do not include this; fetch it once for the whole app.

## Filament

| Section | Tab | Stored in |
|---------|-----|-----------|
| Logo + nav labels | Settings → **الهيدر** | `home_logo`, `header_nav_links` |
| Footer copy + columns | Settings → **الفوتر** | `footer_*` |
| Phone / email | Settings → **التواصل** | `contact_phone`, `contact_email` |
| Social URLs | Settings → **التواصل الاجتماعي** | `facebook_url`, `instagram_url`, … |

Nav **keys** are fixed (`home`, `about`, `content`, …). Admins can change labels, order, and visibility — not the destination key.

## API

Public. No auth.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/layout` | Header + footer together |
| `GET` | `/api/v1/layout/header` | Header only |
| `GET` | `/api/v1/layout/footer` | Footer only |

### Header

```
GET /api/v1/layout/header
```

```json
{
  "data": {
    "site_name": "Sawt",
    "logo_url": "…",
    "search_placeholder": { "ar": "…", "en": "…" },
    "auth": { "register": {}, "login": {} },
    "nav": []
  }
}
```

### Footer

```
GET /api/v1/layout/footer
```

```json
{
  "data": {
    "logo_url": "…",
    "about": { "ar": "…", "en": "…" },
    "main": {},
    "quick": {},
    "newsletter": {},
    "contact": {},
    "socials": [],
    "copyright": {},
    "brand": "SAWTGAZA"
  }
}
```

### Combined (optional)

```
GET /api/v1/layout
```

### Response

```json
{
  "data": {
    "header": {
      "site_name": "Sawt",
      "logo_url": "http://127.0.0.1:8000/storage/home/logo.png",
      "search_placeholder": { "ar": "ابحث هنا...", "en": "Search here..." },
      "auth": {
        "register": { "label": { "ar": "أنشئ حساب", "en": "Create account" }, "url": "/register" },
        "login": { "label": { "ar": "تسجيل الدخول", "en": "Sign in" }, "url": "/login" }
      },
      "nav": [
        { "key": "home", "label": { "ar": "الرئيسية", "en": "Home" }, "url": "/" },
        { "key": "about", "label": { "ar": "من نحن", "en": "About Us" }, "url": "/about" },
        { "key": "creators", "label": { "ar": "صناع المحتوى", "en": "Content Creators" }, "url": "/creators" }
      ]
    },
    "footer": {
      "logo_url": "…",
      "about": { "ar": "…", "en": "…" },
      "main": {
        "title": { "ar": "الأقسام الرئيسية", "en": "Main Sections" },
        "links": [{ "key": "home", "label": { "ar": "الرئيسية", "en": "Home" }, "url": "/" }]
      },
      "quick": {
        "title": { "ar": "روابط سريعة", "en": "Quick Links" },
        "links": [{ "key": "faq", "label": { "ar": "الأسئلة الشائعة", "en": "FAQs" }, "url": "/faq" }]
      },
      "newsletter": {
        "title": { "ar": "ابقَ على اطلاع", "en": "Stay Updated" },
        "description": { "ar": "…", "en": "…" },
        "email_placeholder": { "ar": "ادخل بريدك الالكتروني", "en": "Enter your email" }
      },
      "contact": { "phone": "+972…", "email": "info@sawtgaza.com" },
      "socials": [{ "platform": "instagram", "url": "https://instagram.com/…" }],
      "copyright": { "ar": "© جميع الحقوق محفوظة. 2026", "en": "© All rights reserved. 2026" },
      "brand": "SAWTGAZA"
    }
  }
}
```

Hidden nav/footer items (`is_visible = false`) are omitted. Empty social URLs are omitted.

`url` values are frontend paths (`/about`, `/creators`). Quick links may be a custom URL from Settings.

## Architecture

```
Api\LayoutController
    → LayoutService
        → SettingRepository
```
