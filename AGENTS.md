# TrustMeRecycle — Codex Project

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


<claude-mem-context>
# Memory Context

# [trustmerecycle] recent context, 2026-05-07 10:24am GMT+5:30

Legend: 🎯session 🔴bugfix 🟣feature 🔄refactor ✅change 🔵discovery ⚖️decision 🚨security_alert 🔐security_note
Format: ID TIME TYPE TITLE
Fetch details: get_observations([IDs]) | Search: mem-search skill

Stats: 34 obs (8,476t read) | 193,389t work | 96% savings

### May 5, 2026
120 10:49p ✅ Refactored job status API import in useJobs hook
121 " ✅ Disabled debug mode in environment configuration
122 10:50p 🟣 Enhanced pickUp function to require collection proof data
123 10:52p ✅ Implemented pickUp callback to pass collection proof to API
124 10:53p ✅ Replaced simple confirmation with CollectionJobModal for job completion
132 11:28p 🟣 CompleteJobModal component for pickup job completion
S26 Session cleared and reset (May 5 at 11:28 PM)
S25 Prioritize and fix issues from UX audit report for TrustMeRecycle mobile app (May 5 at 11:28 PM)
S31 Use computer to see the bug in emulator via npm run android and fix it (May 5 at 11:38 PM)
### May 6, 2026
152 12:05p 🔵 React Native Android Build Fails Due to Missing Java Runtime
S32 Debug and fix a bug in React Native Android emulator for trustmerecycle app by running npm run android and examining logs (May 6 at 12:06 PM)
153 12:16p 🔵 JAVA_HOME environment variable not configured
154 12:17p 🔵 Android build initiated with JAVA_HOME configured
S33 Debug and fix React Native Android emulator rendering bug in trustmerecycle app by identifying code issues and applying corrections (May 6 at 12:22 PM)
S34 Debug Android app failures using adb stacktrace analysis to identify root cause of crashes (May 6 at 12:24 PM)
155 2:06p 🔵 Ripgrep not available for log filtering
156 " ✅ ADB logcat captured for crash analysis
157 2:07p ✅ Fresh app restart and crash reproduction for clean logcat
S35 Should gorhom/bottom-sheet be used given current native module mismatches? Decision made to skip it for now in favor of stabilization. (May 6 at 2:07 PM)
### May 7, 2026
299 9:59a 🔵 Laravel dev server port binding denied on 0.0.0.0:8000
300 10:00a ✅ Admin panel Laravel server started on port 8000
301 " ✅ Mobile app React Native dev server started on port 8081
302 " ✅ Mobile app building and deploying to Android emulator
303 " ✅ Mobile app APK built and installed on Android emulator
304 10:01a 🔵 Metro bundler processing JavaScript for running app on emulator
305 " 🔵 Mobile app successfully running and focused on Android emulator
S60 Run admin panel and mobile app both locally on the development machine (May 7 at 10:01 AM)
306 10:22a 🔵 Vendor Login Network and Configuration Mismatch
307 " 🔵 Backend MySQL Connection Failure in Laravel
308 " 🔵 IP Address Mismatch Between Dev Machine and Mobile App Configuration
309 " 🔵 Vendor User Accounts Exist in Database
310 " 🔵 Backend Vendor API Authentication Architecture
311 10:23a 🔵 Vendor Login Credentials Mismatch: Documentation vs Database
312 " 🔵 Laravel Development Server Not Running; Node Dev Server on Port 8081
313 " ✅ Mobile App API Endpoint Updated for Android Emulator
314 " ✅ Laravel Development Server Started on Port 8000
315 " 🔵 Vendor Login API Successfully Authenticated
316 " ✅ Android Emulator Port Forwarding Configured
317 " 🔵 Mobile App Successfully Launched After Fixes
318 10:24a 🔵 Vendor Login Screen Rendered Successfully with Pre-filled Credentials
319 " 🔵 Vendor Login Request Completed Without API Errors
320 " 🔵 Vendor Login Successfully Authenticated; Jobs Endpoint Called

Access 193k tokens of past work via get_observations([IDs]) or mem-search skill.
</claude-mem-context>