# Collaborate Page API

Collaboration landing page (`/collaborate`): hero from **Settings → التعاون**, type cards from **التعاون → أنواع التعاون**.

All collaboration form submissions share one table: **`collaboration_join_requests`**, distinguished by `type` (`creator`, `sponsorship`, `partnership`, `other`).

Creator page CTA join remains separate: `POST /api/v1/pages/creators/join` → `creator_join_requests`.

Navbar/footer: [LAYOUT_API.md](./LAYOUT_API.md).

## Filament

| Section | Where |
|---------|--------|
| Hero | Settings → **التعاون** |
| Type cards | **التعاون → أنواع التعاون** |
| All collaboration requests | **التعاون → طلبات التعاون** |

---

## Landing page

```
GET /api/v1/pages/collaborate
```

---

## Sponsorship / Funding (`type = sponsorship`)

Frontend route: `/collaborate/funding`

### Form config

```
GET /api/v1/pages/collaborate/sponsorship/form
```

Returns stepper labels, support type options, and `submit_url`.

### Submit

```
POST /api/v1/pages/collaborate/sponsorship
```

Use `multipart/form-data` when uploading `attachment`.

| Field | Step | Required |
|-------|------|----------|
| `company_name` | 1 | yes |
| `email` | 1 | yes |
| `phone` | 1 | yes |
| `website` | 1 | no (`www.example.com` is auto-prefixed with `https://` when sent) |
| `country_code` | 1 | no (e.g. `+970`) |
| `support_types` | 2 | yes (array or JSON string) |
| `support_types[]` | 2 | `direct_financial`, `in_kind`, `marketing_media`, `other` |
| `organization_bio` | 2 | yes (max 500) |
| `conditions_notes` | 3 | no |
| `additional_notes` | 3 | no |
| `attachment` | 3 | no (pdf, png, jpg; max 5MB) |

#### Success (201)

```json
{
  "message": "تم استلام طلب التعاون، سنتواصل معك خلال 3–5 أيام عمل.",
  "data": {
    "uuid": "abc12",
    "type": "sponsorship",
    "company_name": "شركة النجاح",
    "email": "example@gmail.com",
    "status": "pending"
  }
}
```

On admin **approve**, an email is sent to the request email.

---

## Strategic Partnership (`type = partnership`)

Frontend route: `/collaborate/partnership`

### Form config

```
GET /api/v1/pages/collaborate/partnership/form
```

### Submit

```
POST /api/v1/pages/collaborate/partnership
```

Use `multipart/form-data` when uploading `attachment`.

| Field | Step | Required |
|-------|------|----------|
| `company_name` | 1 | yes |
| `email` | 1 | yes |
| `phone` | 1 | yes |
| `website` | 1 | no |
| `country_code` | 1 | no |
| `partnership_types` | 2 | yes (array or JSON string) |
| `partnership_types[]` | 2 | `content_exchange`, `advertising_sponsorship`, `event_collaboration`, `other` |
| `partnership_goal` | 2 | yes (max 500) |
| `additional_notes` | 3 | no |
| `attachment` | 3 | no (pdf, png, jpg; max 5MB) |

#### Partnership payload (stored in `payload` JSON)

```json
{
  "partnership_types": ["content_exchange", "event_collaboration"],
  "partnership_goal": "نبذة عن المؤسسة وهدف الشراكة...",
  "additional_notes": null
}
```

---

## Creator collaborate (`type = creator`)

Frontend route: `/collaborate/creator`

Separate from creators page join: `POST /api/v1/pages/creators/join` → `creator_join_requests` (creates account on approve).

### Form config

```
GET /api/v1/pages/collaborate/creator/form
```

### Submit

```
POST /api/v1/pages/collaborate/creator
```

Use `multipart/form-data` when uploading `attachment` or `intro_video`.

| Field | Step | Required |
|-------|------|----------|
| `full_name` | 1 | yes (stored in `company_name` column) |
| `email` | 1 | yes |
| `phone` | 1 | yes |
| `country_code` | 1 | no |
| `content_types` | 2 | yes (array or JSON string; keys from settings) |
| `followers_count` | 2 | yes (integer) |
| `content_bio` | 2 | yes (max 5000) |
| `socials` | 3 | no — `[{"platform":"instagram","url":"https://..."}]` |
| `additional_notes` | 3 | no |
| `attachment` / `intro_video` | 3 | no (pdf, png, jpg, mp4, mov, webm; max 5MB) |
| `terms_accepted` | 3 | yes (`true` on submit) |

**Social platforms:** `instagram`, `facebook`, `twitter`, `linkedin`, `youtube`, `tiktok`, `telegram`, `other`

#### Creator payload (stored in `payload` JSON)

```json
{
  "full_name": "محمد أحمد",
  "content_types": ["culture_arts", "comedy"],
  "followers_count": 5000,
  "content_bio": "نبذة عن محتواك...",
  "socials": [{"platform": "instagram", "url": "https://instagram.com/handle"}],
  "additional_notes": null,
  "terms_accepted": true
}
```

---

## Other collaboration (`type = other`)

Frontend route: `/collaborate/other`

### Form config

```
GET /api/v1/pages/collaborate/other/form
```

### Submit

```
POST /api/v1/pages/collaborate/other
```

Use `multipart/form-data` when uploading `attachment`.

| Field | Step | Required |
|-------|------|----------|
| `name` | 1 | yes (stored in `company_name` column) |
| `email` | 1 | yes |
| `phone` | 1 | yes |
| `country_code` | 1 | no |
| `collaboration_idea` | 2 | yes (max 500) |
| `additional_notes` | 2 | no (max 500) |
| `attachment` | 2 | no (pdf, png, jpg; max 5MB) |

#### Other payload (stored in `payload` JSON)

```json
{
  "name": "محمد أحمد",
  "collaboration_idea": "نوع التعاون الذي أريده وكيف يمكن أن يفيد الطرفين...",
  "additional_notes": null
}
```

---

## Unified table design

```
collaboration_join_requests
├── type          → creator | sponsorship | partnership | other
├── company_name  → org name (sponsorship, partnership…)
├── email, phone, country_code, website
├── payload       → JSON type-specific fields
├── attachment    → optional file path
└── status        → pending | approved | rejected
```

Future types reuse the same table with different `payload` shapes.

## Architecture

```
POST /collaborate/other
    → CollaborationJoinRequestService::submitOther()
        → collaboration_join_requests (type = other)

POST /collaborate/creator
    → CollaborationJoinRequestService::submitCreator()
        → collaboration_join_requests (type = creator)

POST /collaborate/sponsorship
    → CollaborationJoinRequestService::submitSponsorship()
        → collaboration_join_requests (type = sponsorship)

POST /collaborate/partnership
    → CollaborationJoinRequestService::submitPartnership()
        → collaboration_join_requests (type = partnership)

Admin approve
    → CollaborationJoinAcceptedNotification → request email
```
