# Backend Repair Report

## 1. Problems found

- Flutter used `phone`, `username`, `code`, `new_password`, `search`, `unit`, `quantity`, `brand`, `price_entry_id`, and `note`; the backend primarily validated older names.
- Auth routes were duplicated and protected by Sanctum. Refresh was incorrectly protected by an unexpired access token and returned nested tokens.
- Price status was hard-coded as `pending`; brand was mandatory; product statistics included unapproved prices.
- Reports had no persisted status or timestamps and lacked the Flutter PATCH contract.
- Store and location APIs required internal IDs and leaked backend-shaped resources.
- Product prices, price vote/moderation, store verification, and several pagination metadata responses were missing.

## 2. Database migrations added

- `database/migrations/2026_09_05_000001_repair_flutter_contract.php`

It adds `products.default_unit_id`, `prices.status`, `prices.reviewed_by`, `prices.reviewed_at`, price indexes, and `reports.status` plus timestamps. The migration is additive and has a rollback.

## 3. Schema before/after

- Prices now persist `pending`, `approved`, or `rejected`, reviewer, and review time. `brand_id` remains nullable through the existing schema.
- Reports now persist `pending`, `reviewed`, or `resolved` with timestamps.
- Products can persist a nullable default unit.
- The existing official-price history tables were retained.

## 4. Authentication after repair

- `tymon/jwt-auth` `^1.0.2` is used with Laravel's `api` JWT guard.
- Access tokens carry `token_type=access`; refresh tokens carry `token_type=refresh` and are rotated/invalidated on refresh.
- Protected routes use `auth:api`, `access`, and `active` middleware.
- Refresh is public to the access guard and accepts `{ "refresh_token": "..." }`.
- Login, registration, OTP verification, and admin login return `data.access_token`, `data.refresh_token`, and `data.user`.
- Sanctum was removed from Composer and the user model because JWT is now the sole API authentication mechanism.

## 5. Endpoint changes

Added or repaired:

- `GET /api/v1/products/{product}/prices`
- `POST /api/v1/prices/{price}/vote`
- `PATCH /api/v1/prices/{price}/approve`
- `PATCH /api/v1/prices/{price}/reject`
- `PATCH /api/v1/reports/{report}`
- `PATCH /api/v1/stores/{store}/verify`
- `PUT /api/v1/auth/reset-password`
- `PUT /api/v1/auth/change-password`

Removed: duplicate direct `/api/v1/login`, `/register`, `/admin/login`, OTP, reset, profile, and password routes. The canonical contract is under `/api/v1/auth/*`.

## 6. Request and response mappings

- `phone` -> `users.phone_number`
- `username` -> email or phone lookup for admin login
- `sector`/`area` -> existing sector/location records
- `unit` -> `units.name` and `unit_id`
- `brand` -> optional `brands.name` and nullable `brand_id`
- `quantity` -> `prices.amount`
- `price_entry_id`/`note` -> `price_id`/`description`
- Resources expose Flutter keys such as `phone`, `role`, `area`, `sector`, `quantity`, `reported_at`, and `updated_at`.
- Paginated endpoints expose `current_page`, `last_page`, `per_page`, and `total` beside `data`.

## 7. Security fixes

- Refresh tokens cannot authenticate protected endpoints as access tokens.
- Refresh rotation invalidates the submitted refresh token.
- Price moderation and store/report administration require admin role middleware.
- Vote writes use `updateOrCreate`, enforcing one active vote per user and price.
- Passwords, OTP hashes, and auth tokens are not included in resources.
- OTPs remain hashed, expire, are single-use, and have an attempt limit.

## 8. Tests and commands

Updated `tests/Feature/PriceApiTest.php` to use the Flutter payloads and JWT flow. Verified:

- OTP request/verification response shape
- phone login and JWT-protected price submission
- optional brand mapping path
- admin login and admin user authorization
- full Laravel suite: **5 passed**
- PHP syntax checks for touched controllers, middleware, model, and migration: passed
- route listing: passed; canonical auth and required product/price/report/store routes are present

`flutter analyze` could not run because the Flutter SDK is not installed or available on PATH.

## 9. PostgreSQL verification and remaining blockers

The local `.env` points to a remote Neon PostgreSQL database. A `migrate:fresh --seed` attempt did run against that configured target, dropped its schema, and then failed inside the remote transaction while creating the initial users unique constraint; the first PostgreSQL error was obscured by the aborted transaction wrapper. Do not reuse that database without restoring/recreating it. PostgreSQL acceptance is therefore **not verified** and must be run against a disposable local/test database, not the current remote `.env`.

## 10. Endpoint matrix

| METHOD | ROUTE | AUTH | ROLE | FLUTTER CALLER | STATUS |
|---|---|---|---|---|---|
| POST | `/api/v1/auth/login` | No | - | AuthService.login | repaired |
| POST | `/api/v1/auth/register` | No | - | AuthService.register | repaired |
| POST | `/api/v1/auth/admin/login` | No | - | AuthService.adminLogin | repaired |
| POST | `/api/v1/auth/refresh` | Refresh token body | - | ApiClient interceptor | repaired |
| GET | `/api/v1/products` | No | - | ProductService | repaired |
| GET | `/api/v1/products/{id}/prices` | No | - | ProductService | added |
| POST | `/api/v1/prices` | JWT access | user | PriceService | repaired |
| POST | `/api/v1/prices/{id}/vote` | JWT access | user | PriceService | added |
| PATCH | `/api/v1/prices/{id}/approve` | JWT access | admin | PriceService | added |
| PATCH | `/api/v1/prices/{id}/reject` | JWT access | admin | PriceService | added |
| POST | `/api/v1/reports` | JWT access | user | ReportService | repaired |
| PATCH | `/api/v1/reports/{id}` | JWT access | admin | ReportService | added |
| POST | `/api/v1/official-prices` | JWT access | admin | CatalogService | repaired |
| GET | `/api/v1/official-prices/{id}/history` | JWT access | admin | CatalogService | repaired |
| POST | `/api/v1/stores` | JWT access | user | StoreService | repaired |
| PATCH | `/api/v1/stores/{id}/verify` | JWT access | admin | StoreService | added |

## 11. Local verification commands

```powershell
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan jwt:secret
# Configure a disposable PostgreSQL database in .env.
php artisan config:clear
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
```

Flutter verification, when the SDK is installed:

```powershell
cd flutter_app
flutter pub get
flutter analyze
flutter test
```