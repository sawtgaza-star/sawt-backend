# About Page API (Settings)

About page content is stored in the **`settings`** table (`group = about`) and edited in Filament **Settings → من نحن**.

Header and footer are separate Settings tabs and are **not** returned by this API.

## Filament

**الإدارة → الإعدادات → تاب «من نحن»**

| Section | Fields |
|---------|--------|
| Hero | `about_header_bg`, `about_hero_title_*`, `about_hero_desc_*` |
| Intro | `about_intro_image`, `about_header_*`, `about_intro_*` |
| Values | `about_core_values_title_*`, `about_core_values_subtitle_*`, repeater `about_core_values` (icon, titles, descriptions) |
| Platform | `about_platform_image`, `about_platform_question_*`, `about_platform_desc_*` |
| Story | `about_story_title_*`, `about_story_subtitle_*`, repeater `about_story_cards` |
| Join CTA | `about_join_bg`, `about_join_title_*`, `about_join_desc_*`, `about_join_button_text_*`, `about_join_button_url` |

Save the Settings page once to persist any new keys (values title, join button, etc.).

## API

```
GET /api/v1/pages/about
```

Public. No auth.

### Response shape

```json
{
  "data": {
    "hero": {
      "image_url": "…",
      "title": { "ar": "…", "en": "…" },
      "description": { "ar": "…", "en": "…" }
    },
    "intro": {
      "image_url": "…",
      "title": { "ar": "من نحن", "en": "…" },
      "body": { "ar": "…", "en": "…" }
    },
    "values": {
      "title": { "ar": "…", "en": "…" },
      "subtitle": { "ar": "…", "en": "…" },
      "items": [
        {
          "icon_url": null,
          "title": { "ar": "المصداقية", "en": "Credibility" },
          "description": { "ar": "…", "en": "…" },
          "sort_order": 0
        }
      ]
    },
    "platform": { "image_url": "…", "title": {}, "description": {} },
    "story": {
      "title": {},
      "subtitle": {},
      "cards": [{ "icon_url": null, "title": {}, "description": {}, "sort_order": 0 }]
    },
    "join": {
      "image_url": "…",
      "title": {},
      "description": {},
      "button_text": {},
      "button_url": "/donate"
    }
  }
}
```

## Architecture

```
Api\AboutController
    → AboutService
        → AboutRepository (AboutRepositoryInterface)
            → SettingRepository

Api\TeamController
    → TeamService
        → TeamRepository + SettingRepository
```
