# Layout API (Navbar & Footer)

Shared site chrome from Filament **Settings → الهيدر** and **Settings → الفوتر**. Other page APIs (about, team, creators) do not include this; fetch it once for the whole app.

## Filament

| Section | Tab | Stored in |
|---------|-----|-----------|
| Logo + nav labels | Settings → **الهيدر** | `home_logo`, `header_nav_links`, `header_socials_label_*`, `header_auth_*_label_*` |
| Footer copy + columns + contact | Settings → **الفوتر** | `footer_*`, `contact_phone`, `contact_email` |
| Phone / email (also) | Settings → **التواصل** | `contact_phone`, `contact_email` |
| Social URLs | Settings → **التواصل الاجتماعي** | `facebook_url`, `instagram_url`, … |

Nav **keys** are fixed (`home`, `about`, `content`, …). Admins can change labels, order, and visibility — not the destination key.

Layout split (matches the public site navbar):

| Key | Where it appears |
|-----|------------------|
| `support` | Top bar — «ادعم صوت» button |
| `incubator`, `media` | Secondary row (left of logo) |
| All other visible keys | Primary nav row |

## API

Public. No auth.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/layout` | Navbar + footer together |
| `GET` | `/api/v1/layout/navbar` | Navbar only |
| `GET` | `/api/v1/layout/footer` | Footer only |

### Navbar

```
GET /api/v1/layout/navbar
```

```json
{
  "data": {
    "site_name": "Sawt",
    "logo_url": "…",
    "topbar": {
      "socials_label": { "ar": "وسائل التواصل الاجتماعي", "en": "Social Media" },
      "socials": [
        { "platform": "instagram", "url": "https://instagram.com/…" },
        { "platform": "twitter", "url": "https://x.com/…" }
      ],
      "support": {
        "key": "support",
        "label": { "ar": "ادعم صوت", "en": "Support Sawt" },
        "url": "/support"
      },
      "auth": {
        "register": { "label": { "ar": "أنشئ حساب", "en": "Create account" }, "url": "/register" },
        "login": { "label": { "ar": "تسجيل الدخول", "en": "Sign in" }, "url": "/login" }
      },
      "search_placeholder": { "ar": "ابحث هنا...", "en": "Search here..." },
      "language": { "label": { "ar": "En", "en": "Ar" } }
    },
    "nav": {
      "primary": [
        { "key": "home", "label": { "ar": "الرئيسية", "en": "Home" }, "url": "/" },
        { "key": "about", "label": { "ar": "من نحن", "en": "About Us" }, "url": "/about" }
      ],
      "secondary": [
        { "key": "incubator", "label": { "ar": "حاضنة صوت", "en": "Sawt Incubator" }, "url": "/incubator", "external": true },
        { "key": "media", "label": { "ar": "صوت ميديا", "en": "Sawt Media" }, "url": "/media", "external": true }
      ]
    }
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
    "contact": {
      "phone": "+972567247…",
      "email": "info@sawtgaza.com"
    },
    "socials": [{ "platform": "instagram", "url": "https://instagram.com/…" }],
    "copyright": { "ar": "© جميع الحقوق محفوظة. 2026", "en": "© All rights reserved. 2026" },
    "brand": "SAWTGAZA"
  }
}
```

### Combined (optional)

```
GET /api/v1/layout
```

Returns `{ "data": { "navbar": { … }, "footer": { … } } }` using the shapes above.

Hidden nav/footer items (`is_visible = false`) are omitted. Empty social URLs are omitted. If `support` is hidden, `topbar.support` is `null`.

`url` values are frontend paths (`/about`, `/creators`). Quick links may use a custom URL from Settings.

## Architecture

```
Api\LayoutController
    → LayoutService
        → SettingRepository
```
