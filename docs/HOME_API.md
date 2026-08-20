# Home Page API (Settings)

Homepage content is stored in the **`settings`** table (`group = home`) and edited in Filament **Settings → الصفحة الرئيسية**.

Creators and team **cards** are loaded from the Creators / Team Members resources (active records). Header and footer are separate: [LAYOUT_API.md](./LAYOUT_API.md).

## Filament

**الإدارة → الإعدادات → تاب «الصفحة الرئيسية»**

| Section | Fields |
|---------|--------|
| 1) Hero | `home_hero_slides` (repeater), trust line, support/collaborate button labels |
| 2) Stats strip | `home_stat_team`, `home_stat_stories`, `home_stat_views`, `home_stat_videos`, `home_stat_followers` |
| 3) Who we are | one section image (`home_hero_image`), titles, description, features with icon images, CTA labels (no URLs) |
| 4) Latest news | titles + `home_news_items` repeater (no URLs) |
| 5) Creators teaser | copy + `home_creators_limit` (items from Creators) |
| 6) Platform sections | titles + `home_platform_sections` repeater (no URLs) |
| 7) Partners | titles + `home_partners` logos (no URLs) |
| 8) Stories | title + description + stat badge above the form; story **image cards** (`home_stories_items`: image, badge, title, description) — no placeholder / no subtitle / no reel URLs |
| 9) Team teaser | copy + `home_team_limit` (items from Team Members) |
| 10) Join CTA | banner: background image, title, description, button label — no URL |
| 11) Reviews / Instagram reels | section title + description + toggle; when on, API returns **latest 3 reels** (no manual video fields) |

Button / “view all” **URLs are not editable** in the dashboard — the front app owns routing.

Save the Settings page once to persist any new keys.

## API

```
GET /api/v1/pages/home
```

Public. No auth.

### Response shape (summary)

```json
{
  "data": {
    "hero": {
      "trust": { "ar": "…", "en": "…" },
      "buttons": {
        "support": { "label": {} },
        "collaborate": { "label": {} }
      },
      "slides": [
        {
          "image_url": "…",
          "title": { "ar": "منصة صوت", "en": "…" },
          "subtitle": { "ar": "…", "en": "…" },
          "sort_order": 0
        }
      ]
    },
    "stats": {
      "items": [
        { "key": "followers", "value": "+10", "label": { "ar": "متابع", "en": "Followers" } }
      ]
    },
    "who_we_are": {
      "section_title": {},
      "section_subtitle": {},
      "image_url": "…",
      "title": {},
      "lead": {},
      "description": {},
      "features": [{ "icon_url": null, "title": {}, "sort_order": 0 }],
      "cta": { "label": {} }
    },
    "news": {
      "title": {},
      "subtitle": {},
      "view_all": { "label": {} },
      "items": [{ "image_url": null, "title": {}, "excerpt": {}, "date": null, "sort_order": 0 }]
    },
    "creators": {
      "title": {},
      "description": {},
      "view_all": { "label": {} },
      "items": [{ "uuid": "…", "name": "…", "followers_count": 0 }]
    },
    "platform_sections": {
      "title": {},
      "subtitle": {},
      "items": [{ "image_url": null, "title": {}, "description": {}, "stats": [{}, {}], "cta": { "label": {} }, "sort_order": 0 }]
    },
    "partners": {
      "title": {},
      "subtitle": {},
      "items": [{ "name": "", "logo_url": "…", "sort_order": 0 }]
    },
    "stories": {
      "title": {},
      "description": {},
      "badge": {},
      "items": [
        {
          "image_url": "…",
          "badge": { "ar": "قصة نجاح", "en": "Success story" },
          "title": { "ar": "سمير", "en": "Samir" },
          "description": { "ar": "…", "en": "…" },
          "sort_order": 0
        }
      ]
    },
    "team": {
      "title": {},
      "subtitle": {},
      "profile_cta": {},
      "items": [{ "uuid": "…", "name": {}, "photo_url": "…" }]
    },
    "join_cta": {
      "image_url": "…",
      "title": { "ar": "انضم إلينا كصانع محتوى", "en": "…" },
      "description": {},
      "button": { "label": {} }
    },
    "reviews": {
      "title": {},
      "description": {},
      "reels_enabled": true,
      "reels_status": "ok",
      "reels": [
        {
          "id": "…",
          "caption": "…",
          "thumbnail": "…",
          "video_url": "…",
          "permalink": "…",
          "username": "sawtgaza",
          "likes": 0,
          "comments_count": 0,
          "views": 1234,
          "reach": 890,
          "collaborators": [
            { "id": "…", "username": "partner", "invite_status": "Accepted" }
          ],
          "posted_at": "…",
          "sort_order": 0
        }
      ],
      "comments": { "count": 0, "items": [] }
    }
  }
}
```

### Notes

- When home reel mode is on and Instagram Business ID + token are set (Settings → «ريلز إنستغرام»), `reviews.reels` has up to **3 latest** reels; comments come from the newest reel.
- Each reel includes `views` / `reach` from Graph `/{media-id}/insights?metric=views,reach` (null if insights permission is missing).
- Each reel includes `collaborators` from Graph `/{media-id}/collaborators` (`username`, `invite_status`: Accepted|Pending). Empty if none or API denies the edge.
- `reviews.reels_status`: `ok` | `empty` | `missing_credentials` | `disabled` — explains why `reels` may be `[]`.
- Locale: every `*_ar` / `*_en` setting is exposed as `{ "ar": "…", "en": "…" }` via `i18n`.
