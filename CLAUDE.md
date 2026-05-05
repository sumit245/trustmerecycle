# TrustMeRecycle — Claude Project

## What This Is
Laravel 11 + Filament 3 scrap recycling management app (India).
Two Filament panels: Admin (`/admin`) + Vendor (`/vendor`).
Mobile API: Sanctum token auth at `/api/vendor/*`.

## Key Models
| Model | Purpose |
|-------|---------|
| `User` | Roles: `admin`, `vendor` (site incharge) |
| `CollectionJob` | Pickup jobs assigned to vendors |
| `ScrapEntry` | Scrap logged per job |
| `ScrapType` | Categories of scrap material |
| `Godown` | Vendor storage sites |

## Architecture
```
app/
  Filament/
    Resources/          ← Admin panel CRUD (SiteIncharge, ScrapEntry, etc.)
    Vendor/Resources/   ← Vendor panel CRUD
    Pages/              ← Dashboard, Auth/Login per panel
    Widgets/            ← CapacityAlert, RecentJobs, TotalScrap
  Http/
    Controllers/Api/    ← Mobile API (AuthController, VendorCollectionJobController)
    Middleware/         ← EnsureAdminRole, EnsureVendorRole
  Models/               ← User, CollectionJob, ScrapEntry, ScrapType, Godown
  Observers/            ← ScrapEntryObserver (triggers notifications)
  Notifications/        ← ScrapLimitReached, CollectionJobCreated

routes/
  api.php               ← Sanctum-protected /api/vendor/* endpoints
  web.php               ← Includes auth.php, vendor management
  auth.php              ← Login/logout (AuthenticatedSessionController)

docs/api/
  openapi.yaml          ← Full OpenAPI 3.0 spec (all Vendor Mobile API endpoints)
  index.html            ← Swagger UI viewer (open in browser)
```

## Stack
- PHP 8.x / Laravel 11
- Filament 3 (admin + vendor panels)
- Laravel Sanctum (mobile API auth)
- MySQL (`recycle` database)
- XAMPP local development
- DataTables (admin list views)
- Tailwind CSS + Alpine.js

## Common Commands
```bash
php artisan migrate
php artisan db:seed
php artisan test
php artisan filament:optimize-clear
php artisan cache:clear && php artisan config:clear && php artisan view:clear
composer install
```

## Dev Environment
- XAMPP on macOS
- DB: `recycle`, user: `root`, password: (none)
- URL: `http://localhost/trustmerecycle/public`
- Artisan: `php artisan` from project root
- **API Docs (Swagger UI):** `http://localhost/trustmerecycle/docs/api/index.html`
- **OpenAPI Spec:** `docs/api/openapi.yaml`

## API Endpoints (Vendor Mobile — all under `/api`)
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/vendor/login` | None | Authenticate vendor, get Sanctum Bearer token |
| `POST` | `/vendor/logout` | Bearer | Revoke current token |
| `GET`  | `/vendor/me` | Bearer | Authenticated vendor profile |
| `GET`  | `/vendor/jobs` | Bearer | Paginated list of collection jobs (20/page) |
| `GET`  | `/vendor/jobs/{id}` | Bearer | Single collection job details |
| `POST` | `/vendor/jobs/{id}/complete` | Bearer | Mark dispatched job complete + upload proof image |

- Token ability required: `vendor` (scoped via Sanctum)
- `complete` endpoint accepts `multipart/form-data` with `collected_amount_mt` + `proof_image` (max 5 MB)

## Important Notes
- `public/js/filament/` = minified vendor bundles — DO NOT read these files
- `vendor/` = Composer packages — ignore completely
- Two separate auth flows: web session (Filament) + Sanctum token (mobile API)
- `User::isAdmin()` drives role-based routing in `routes/web.php`
- ScrapEntry capacity limits trigger `ScrapLimitReachedNotification` via observer

## Graph (graphify)
- `graphify-out/graph.json` — knowledge graph of codebase
- `graphify-out/GRAPH_REPORT.md` — god nodes + community structure
- Core business community: `CollectionJob`, `ScrapEntry`, `User`, `Godown`
- Run `/graphify` to rebuild after major changes
