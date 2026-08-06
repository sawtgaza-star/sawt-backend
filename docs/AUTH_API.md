# Auth API (JWT)

Documentation for API authentication: register, login, me, refresh, logout.

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

Protected routes require header:

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

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
      "created_at": "2026-08-04T12:00:00+00:00"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

`name` is built as `first_name + last_name`. User status is set to `active`.

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

## Postman tips

| Tab | What to set |
|-----|-------------|
| Params | Empty for auth endpoints |
| Authorization | Login/Register: No Auth. Me/Refresh/Logout: Bearer Token |
| Headers | `Accept: application/json` |
| Body | raw → JSON (login / register only) |

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
