# Auth API (JWT)

Documentation for API authentication: register, login, me, refresh, logout, and forgot-password.

Base URL: `/api/v1/auth`  
Package: `php-open-source-saver/jwt-auth`  
Guard: `api` (`driver: jwt` in `config/auth.php`)

---

## Overview

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/v1/auth/register` | Public | Create account + return JWT |
| `POST` | `/api/v1/auth/login` | Public | Login + return JWT |
| `GET` | `/api/v1/auth/me` | Bearer | Current user |
| `POST` | `/api/v1/auth/refresh` | Bearer | New access token |
| `POST` | `/api/v1/auth/logout` | Bearer | Invalidate current token |
| `POST` | `/api/v1/auth/forgot-password` | Public | Send 6-digit reset code (expires in 60s) |
| `POST` | `/api/v1/auth/resend-code` | Public | Resend reset code |
| `POST` | `/api/v1/auth/verify-code` | Public | Verify email + code → `reset_token` |
| `POST` | `/api/v1/auth/reset-password` | Public | Set new password, then login |

Protected routes require header:

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

---

## Roles separation

| `users.type` | Spatie role | Permissions | Login |
|--------------|-------------|-------------|--------|
| `user` | `user` | Full **website/API** set (`WebsiteUserPermissions`) | API + website only — **blocked from Filament** |
| `content_creator` | `content_creator` | Same as user + creator profile extras (`ContentCreatorPermissions`) | API + website only — **blocked from Filament** |
| `admin` | `super_admin` / `admin` / `moderator` | Filament Shield resource permissions | `/admin` only — blocked from API auth |

`type` is the account kind (`admin`, `user`, `content_creator`). Roles add the permission set on top of that. Staff always have `type = admin`; their Filament role is still `super_admin`, `admin`, or `moderator`.

Website user permissions include browse (pages, content, team, creators, videos, courses…), engage (like, comment, join course), profile, donations, and payments.view.own.

A join-as-creator CTA (`POST /api/v1/pages/creators/join`) does **not** create a user immediately. When an admin **approves** the request, the existing user with that email is reused (or a new user is created), `type` is set to `content_creator`, the `content_creator` role is assigned, and a `creators` profile is filled from the request.

---

## Architecture

```
Api\AuthController
    → AuthService
        → UserRepository (UserRepositoryInterface)
            → User model (implements JWTSubject)
```

| Layer | Files |
|-------|--------|
| Controller | `app/Http/Controllers/Api/AuthController.php` |
| Requests | `app/Http/Requests/Api/Auth/LoginRequest.php`, `RegisterRequest.php` |
| Service | `app/Services/AuthService.php` |
| Repository | `app/Repositories/UserRepository.php` |
| Resource | `app/Http/Resources/UserResource.php` |
| Routes | `routes/api/v1.php` |
| Config | `config/jwt.php`, `config/auth.php` (`api` guard) |
| Env | `JWT_SECRET`, `JWT_TTL`, `JWT_REFRESH_TTL`, `JWT_ALGO` |

Binding in `AppServiceProvider`: `UserRepositoryInterface` → `UserRepository`

---

## Endpoints

### `POST /api/v1/auth/register`

**Body (JSON)**

| Field | Rules |
|-------|--------|
| `first_name` | required, string, max 100 |
| `last_name` | required, string, max 100 |
| `email` | required, email, unique |
| `phone` | optional, string, max 40 |
| `password` | required, min 8, confirmed |
| `password_confirmation` | required (must match password) |

```json
{
  "first_name": "أحمد",
  "last_name": "محمد",
  "email": "ahmed@example.com",
  "phone": "0599000000",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Success `201`**

```json
{
  "message": "تم إنشاء الحساب بنجاح.",
  "data": {
    "user": {
      "id": 1,
      "uuid": "xxxxx",
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "phone": "0599000000",
      "country_code": null,
      "avatar": null,
      "status": "active",
      "type": "user",
      "roles": ["user"],
      "permissions": ["api.access", "api.profile.view", "api.profile.update"],
      "is_content_creator": false,
      "created_at": "2026-08-04T12:00:00+00:00"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

`name` is built as `first_name + last_name`. Status is `active`. Role **`user`** is assigned automatically.

---

### `POST /api/v1/auth/login`

**Body (JSON)**

```json
{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

**Success `200`** — same shape as register (`user` + `access_token` + `token_type` + `expires_in`).

**Errors**

| Case | Result |
|------|--------|
| Wrong email/password | Validation error: `بيانات الدخول غير صحيحة.` |
| Status `banned` | Validation error: account banned message |
| Filament staff (admin roles) | Same as wrong credentials: `بيانات الدخول غير صحيحة.` |
| Missing website role (`user` / `content_creator`) | Same as wrong credentials: `بيانات الدخول غير صحيحة.` |
| Status `inactive` | Validation error: account inactive message |

---

### `GET /api/v1/auth/me`

Requires Bearer token. No body.

**Success `200`**

```json
{
  "data": {
    "user": { "...": "UserResource fields" }
  }
}
```

---

### `POST /api/v1/auth/refresh`

Requires Bearer token. Returns a new `access_token` (old one is invalidated by refresh flow).

**Success `200`** — token payload like login.

**Failure `401`**

```json
{
  "message": "تعذر تجديد التوكن.",
  "error": "..."
}
```

---

### `POST /api/v1/auth/logout`

Requires Bearer token. Invalidates the current JWT.

**Success `200`**

```json
{
  "message": "تم تسجيل الخروج بنجاح."
}
```

**Failure `401`**

```json
{
  "message": "تعذر تسجيل الخروج.",
  "error": "..."
}
```

---

### `POST /api/v1/auth/forgot-password`

Checks that the email exists, is valid, and belongs to a website user (`user` / `content_creator`). Sends a **6-digit** code that expires after **60 seconds**. The code is stored on **that email** only.

You can send/resend the code **5 times**. After the 5th send, wait **3 minutes**, then you get 5 new attempts. Forgot-password and resend share this limit.

```json
{ "email": "ahmed@example.com" }
```

**Success `200`**

```json
{
  "message": "تم إرسال رمز التحقق إلى بريدك الإلكتروني.",
  "data": {
    "expires_in": 60,
    "attempts_left": 4
  }
}
```

**Errors `422`:** invalid email format, email not found, inactive/admin account, mail send failure.

**Too many sends `429`:** after 5 attempts, wait 3 minutes.

```json
{
  "message": "لقد استنفدت المحاولات الخمس. يمكنك إعادة الإرسال بعد 3 دقيقة.",
  "errors": {
    "email": ["لقد استنفدت المحاولات الخمس. يمكنك إعادة الإرسال بعد 3 دقيقة."],
    "retry_after": ["180"]
  }
}
```

`retry_after` is seconds left until they can send again.

### `POST /api/v1/auth/resend-code`

Same body, response, and attempt limit as forgot-password. The **previous code expires immediately**; only the new code is valid. Restarts the 60-second timer.

### `POST /api/v1/auth/verify-code`

Checks the code against **that email only**. No `request_id`.

```json
{
  "email": "ahmed@example.com",
  "code": "123456"
}
```

**Success `200`** — store `reset_token` for the next page.

```json
{
  "message": "تم التحقق من الرمز بنجاح.",
  "data": {
    "reset_token": "…",
    "expires_in": 600
  }
}
```

**Errors `422`:** wrong code, expired code (after 1 minute).

### `POST /api/v1/auth/reset-password`

```json
{
  "reset_token": "…",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Success `200`**

```json
{ "message": "تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن." }
```

Then call `POST /api/v1/auth/login` with the new password. No JWT is issued here.

Frontend mapping:

| Page | Endpoint |
|------|----------|
| `/forgot-password` | `POST /auth/forgot-password` |
| `/code-verification` | `POST /auth/verify-code` + `POST /auth/resend-code` |
| `/set-new-password` | `POST /auth/reset-password` → redirect to login |

---

## Postman tips

| Tab | What to set |
|-----|-------------|
| Params | Empty for auth endpoints |
| Authorization | Login/Register/Forgot-password: No Auth. Me/Refresh/Logout: Bearer Token |
| Headers | `Accept: application/json` |
| Body | raw → JSON |

Example URL (XAMPP):

```text
http://127.0.0.1/sawt-platform/public/api/v1/auth/login
```

Or with `php artisan serve`:

```text
http://127.0.0.1:8000/api/v1/auth/login
```

---

## Notes

- Token TTL comes from `JWT_TTL` (minutes); `expires_in` in responses is in **seconds**.
- Web session login (`/login`) is separate from this JWT API.
- See also: [TEAM_API.md](./TEAM_API.md) for the team majors/members API.
