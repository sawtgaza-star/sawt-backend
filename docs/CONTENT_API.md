# Content Page API (محتوانا)

Content page chrome is edited in Filament **Settings → محتوانا**.  
Reels come from **Instagram** (same credentials as `GET /api/v1/reels`).

Header/footer: [LAYOUT_API.md](./LAYOUT_API.md).

## Filament

### Settings → «محتوانا»
| Section | Fields |
|---------|--------|
| Hero | `content_header_bg`, `content_hero_title_*`, `content_hero_desc_*` |
| Hero cards | repeater `content_hero_items` (**images only**) |
| Reels block | title, “see more”, `content_most_viewed_limit` (how many Instagram reels) |

### Settings → «ريلز إنستغرام»
Configure `instagram_user_id` + `instagram_access_token` (required for `reels.items`).

## API

```
GET /api/v1/pages/content
```

Public. No auth. No query filters.

### Response shape

```json
{
  "data": {
    "hero": {
      "image_url": "…",
      "title": { "ar": "…", "en": "…" },
      "description": {},
      "items": [
        { "image_url": "…", "sort_order": 0 }
      ]
    },
    "reels": {
      "title": { "ar": "…", "en": "…" },
      "view_more": { "ar": "…", "en": "…" },
            'status': "ok",
      "message": null,
      "items": [
        {
          "id": "…",
          "caption": "…",
          "thumbnail": "…",
          "video_url": "…",
          "permalink": "…",
          "username": "…",
          "likes": 0,
          "comments_count": 0,
          "views": null,
          "reach": null,
          "collaborators": [],
          "posted_at": "…",
          "sort_order": 0
        }
      ]
    }
  }
}
```

### `reels.status`

| Value | Meaning |
|-------|---------|
| `ok` | Instagram reels returned |
| `empty` | Credentials OK but no reels matched |
| `missing_credentials` | Instagram user id / token not set in Settings |
| `token_expired` | Access token expired — paste a new long-lived token in **Settings → ريلز إنستغرام** |
| `api_error` | Graph API error (see `reels.message`) |

### Notes

- Count limited by Settings `content_most_viewed_limit` (default 6).
- Standalone list: `GET /api/v1/reels?limit=12`.
- If `status` is `token_expired` on local **and** production, both environments need a fresh Instagram access token saved in Settings (or `.env`).
