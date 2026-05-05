# Graph Report - /Applications/XAMPP/xamppfiles/htdocs/trustmerecycle  (2026-04-30)

## Corpus Check
- Corpus is ~26,330 words - fits in a single context window. You may not need a graph.

## Summary
- 490 nodes · 597 edges · 50 communities detected
- Extraction: 69% EXTRACTED · 31% INFERRED · 0% AMBIGUOUS · INFERRED: 185 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Vendor API and Resource Controllers|Vendor API and Resource Controllers]]
- [[_COMMUNITY_App Bootstrap and Admin Auth|App Bootstrap and Admin Auth]]
- [[_COMMUNITY_Godown and Site Incharge Management|Godown and Site Incharge Management]]
- [[_COMMUNITY_Scrap Entry and Type Management|Scrap Entry and Type Management]]
- [[_COMMUNITY_Core Models and Vendor Dashboard|Core Models and Vendor Dashboard]]
- [[_COMMUNITY_Auth Providers and DB Schema|Auth Providers and DB Schema]]
- [[_COMMUNITY_Capacity Monitoring and Alerts|Capacity Monitoring and Alerts]]
- [[_COMMUNITY_Collection Job CRUD|Collection Job CRUD]]
- [[_COMMUNITY_Core Business Domain|Core Business Domain]]
- [[_COMMUNITY_Vendor Mobile API Jobs|Vendor Mobile API Jobs]]
- [[_COMMUNITY_Vendor Bulk Import|Vendor Bulk Import]]
- [[_COMMUNITY_Site Incharge Filament Resource|Site Incharge Filament Resource]]
- [[_COMMUNITY_Database Migrations|Database Migrations]]
- [[_COMMUNITY_Godown Bulk Import|Godown Bulk Import]]
- [[_COMMUNITY_Scrap Limit Notification|Scrap Limit Notification]]
- [[_COMMUNITY_Blade Views and Layouts|Blade Views and Layouts]]
- [[_COMMUNITY_Fix and Deployment Docs|Fix and Deployment Docs]]
- [[_COMMUNITY_Site Incharge Export|Site Incharge Export]]
- [[_COMMUNITY_Scrap Type Resource|Scrap Type Resource]]
- [[_COMMUNITY_Collection Job Notification|Collection Job Notification]]
- [[_COMMUNITY_Community 20|Community 20]]
- [[_COMMUNITY_Community 21|Community 21]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 23|Community 23]]
- [[_COMMUNITY_Community 31|Community 31]]
- [[_COMMUNITY_Community 32|Community 32]]
- [[_COMMUNITY_Community 33|Community 33]]
- [[_COMMUNITY_Community 34|Community 34]]
- [[_COMMUNITY_Community 35|Community 35]]
- [[_COMMUNITY_Community 36|Community 36]]
- [[_COMMUNITY_Community 37|Community 37]]
- [[_COMMUNITY_Community 38|Community 38]]
- [[_COMMUNITY_Community 40|Community 40]]
- [[_COMMUNITY_Community 41|Community 41]]
- [[_COMMUNITY_Community 42|Community 42]]
- [[_COMMUNITY_Community 43|Community 43]]
- [[_COMMUNITY_Community 44|Community 44]]
- [[_COMMUNITY_Community 45|Community 45]]
- [[_COMMUNITY_Community 46|Community 46]]
- [[_COMMUNITY_Community 47|Community 47]]
- [[_COMMUNITY_Community 48|Community 48]]
- [[_COMMUNITY_Community 80|Community 80]]
- [[_COMMUNITY_Community 81|Community 81]]
- [[_COMMUNITY_Community 82|Community 82]]
- [[_COMMUNITY_Community 83|Community 83]]
- [[_COMMUNITY_Community 84|Community 84]]
- [[_COMMUNITY_Community 85|Community 85]]
- [[_COMMUNITY_Community 86|Community 86]]
- [[_COMMUNITY_Community 87|Community 87]]
- [[_COMMUNITY_Community 89|Community 89]]

## God Nodes (most connected - your core abstractions)
1. `User` - 39 edges
2. `User Model` - 18 edges
3. `ScrapEntryResource` - 15 edges
4. `Godown` - 14 edges
5. `CollectionJob` - 13 edges
6. `CollectionJobResource` - 12 edges
7. `TrustMeRecycle Application` - 12 edges
8. `Godown Model` - 11 edges
9. `SiteInchargeResource` - 11 edges
10. `ScrapEntryResource` - 10 edges

## Surprising Connections (you probably didn't know these)
- `User Model` --shares_data_with--> `users DB table`  [INFERRED]
  app/Models/User.php → database/migrations/0001_01_01_000000_create_users_table.php
- `ScrapEntry Model` --shares_data_with--> `scrap_entries DB table`  [INFERRED]
  app/Models/ScrapEntry.php → database/migrations/2024_01_01_000006_create_scrap_entries_table.php
- `User Model` --shares_data_with--> `godowns DB table`  [INFERRED]
  app/Models/User.php → database/migrations/2024_01_01_000005_create_godowns_table.php
- `ScrapEntry Model` --shares_data_with--> `scrap_types DB table`  [INFERRED]
  app/Models/ScrapEntry.php → database/migrations/2024_01_01_000004_create_scrap_types_table.php
- `EnsureVendorRole Middleware` --references--> `User Model`  [INFERRED]
  bootstrap/app.php → app/Models/User.php

## Hyperedges (group relationships)
- **Collection jobs lifecycle: collection_jobs table, godowns table, and RecentJobsWidget/VendorPendingJobsWidget** — db_collection_jobs_table, db_godowns_table, widget_recentjobs [INFERRED 0.80]
- **TrustMeRecycle dual Filament panel architecture: AdminPanelProvider, VendorPanelProvider, and AppServiceProvider together form the Filament UI layer** — provider_adminpanel, provider_vendorpanel, provider_appservice [EXTRACTED 0.90]
- **Scrap data flows through ScrapEntry model, scrap_entries table, and ScrapEntryObserver** — model_scrapentry, db_scrap_entries_table, observer_scrapentry [INFERRED 0.85]
- **Godown Stock and Collection Lifecycle** — godown_model, collectionjob_model, godownresource [INFERRED 0.85]
- **Vendor Import and Export Excel Workflow** — listsiteincharges, vendorsimport, siteinchargeexport [EXTRACTED 0.95]
- **ScrapEntry Estimated Value Calculation** — createscrapentry, editscrapentry, scraptype_model, scrapentry_model [EXTRACTED 0.95]
- **Admin Panel Authentication and Access Control** — admin_login, ensure_admin_role_middleware, model_user [INFERRED 0.88]
- **Vendor ScrapEntry CRUD Lifecycle** — vendor_scrap_entry_resource, vendor_create_scrap_entry, vendor_edit_scrap_entry, vendor_view_scrap_entry, vendor_list_scrap_entries [EXTRACTED 1.00]
- **Vendor Panel Authentication and Access Control** — vendor_login, ensure_vendor_role_middleware, model_user [INFERRED 0.88]
- **Dual Authentication Paths: Web Session vs API Token for Vendors** — authenticated_session_controller, api_auth_controller, login_response, config_sanctum, config_auth [INFERRED 0.88]
- **Scrap Entry to Notification Pipeline: Observer triggers threshold check and admin notification** — scrap_entry_observer, model_godown, scrap_limit_reached_notification, model_user [EXTRACTED 0.95]
- **All Infrastructure Backed by Database: cache, session, queue use database driver** — config_cache, config_session, config_queue, config_database [EXTRACTED 0.92]
- **Admin UI Stack** — admin_adminlayout, index_vendorlistview, create_vendorcreateview [INFERRED 0.85]
- **API Route Group (Sanctum Vendor)** — api_apiroutes, auth_authroutes, web_webroutes [INFERRED 0.80]
- **Core Domain Models** — readme_godown, readme_scrapentry, readme_collectionjob, readme_scraptype [EXTRACTED 1.00]
- **Deployment & Operations Docs** — deployment_deploymentguide, post_php_phpupgradeguide, upgrade_php_phpupgradeguide, install_intl_intlinstallguide, clear_cache_cacheguide [INFERRED 0.85]

## Communities

### Community 0 - "Vendor API and Resource Controllers"
Cohesion: 0.05
Nodes (11): AuthController, AuthenticatedSessionController, VendorManagementController, EnsureAdminRole, EnsureVendorRole, User, CollectionJobResource, GodownResource (+3 more)

### Community 1 - "App Bootstrap and Admin Auth"
Cohesion: 0.07
Nodes (47): Admin Login Page, Api\AuthController, AuthenticatedSessionController, Controller (Base), bootstrap/app.php, CapacityAlertWidget, CollectionJobCreatedNotification, Config: Auth (Guards & Providers) (+39 more)

### Community 2 - "Godown and Site Incharge Management"
Cohesion: 0.08
Nodes (21): CollectionJob Model, CollectionJobCreatedNotification, CreateSiteIncharge Page, EditSiteIncharge Page, Excel Import with Heading Row Pattern, Godown Model, GodownResource, GodownsImport (+13 more)

### Community 3 - "Scrap Entry and Type Management"
Cohesion: 0.08
Nodes (15): CreateScrapEntry Page, EditScrapEntry Page, ListScrapEntries Page, ManageScrapTypes Page, ScrapType, CreateScrapEntry, EditScrapEntry, ListScrapEntries (+7 more)

### Community 4 - "Core Models and Vendor Dashboard"
Cohesion: 0.08
Nodes (6): VendorController, CollectionJob, ScrapEntry, AppServiceProvider, UserSeeder, RecentJobsWidget

### Community 5 - "Auth Providers and DB Schema"
Cohesion: 0.12
Nodes (20): Admin Login Page, Vendor Login Page, bootstrap/providers.php, collection_jobs DB table, godowns DB table, scrap_entries DB table, scrap_types DB table, ScrapEntry Model (+12 more)

### Community 6 - "Capacity Monitoring and Alerts"
Cohesion: 0.12
Nodes (4): Godown, ScrapEntryObserver, CapacityAlertWidget, TotalScrapWidget

### Community 7 - "Collection Job CRUD"
Cohesion: 0.13
Nodes (8): CollectionJobResource, CreateCollectionJob Page, ListCollectionJobs Page, CreateCollectionJob, EditCollectionJob, ListCollectionJobs, ViewCollectionJob, ViewCollectionJob Page

### Community 8 - "Core Business Domain"
Cohesion: 0.2
Nodes (16): CollectionJobCreatedNotification, ScrapLimitReachedNotification, Site Incharge / Vendor User, Admin Panel, Collection Job, FilamentPHP Admin Panel, Godown (Warehouse), Hostinger Shared Hosting (+8 more)

### Community 9 - "Vendor Mobile API Jobs"
Cohesion: 0.2
Nodes (3): VendorCollectionJobController, up(), up()

### Community 10 - "Vendor Bulk Import"
Cohesion: 0.33
Nodes (2): VendorsImport, ListSiteIncharges

### Community 11 - "Site Incharge Filament Resource"
Cohesion: 0.25
Nodes (1): SiteInchargeResource

### Community 12 - "Database Migrations"
Cohesion: 0.29
Nodes (7): AddRoleToUsersTable Migration, AddChallanImageToCollectionJobsTable Migration, CreateCollectionJobsTable Migration, CreateGodownsTable Migration, CreateScrapEntriesTable Migration, CreateScrapTypesTable Migration, CreateUsersTable Migration

### Community 13 - "Godown Bulk Import"
Cohesion: 0.33
Nodes (1): GodownsImport

### Community 14 - "Scrap Limit Notification"
Cohesion: 0.33
Nodes (1): ScrapLimitReachedNotification

### Community 15 - "Blade Views and Layouts"
Cohesion: 0.4
Nodes (6): Admin Layout Template, Create Site Incharge View, Vendor (Site Incharge) List View, Login View, Vendor Layout Template, Laravel Welcome Page

### Community 16 - "Fix and Deployment Docs"
Cohesion: 0.33
Nodes (6): Clear Cache Instructions, Filament Pages Fix, Assets and Login Fix, Fix Login and Styles, Applied Fixes Summary, Migration Fix Guide

### Community 17 - "Site Incharge Export"
Cohesion: 0.4
Nodes (1): SiteInchargeExport

### Community 18 - "Scrap Type Resource"
Cohesion: 0.4
Nodes (1): ScrapTypeResource

### Community 19 - "Collection Job Notification"
Cohesion: 0.4
Nodes (1): CollectionJobCreatedNotification

### Community 20 - "Community 20"
Cohesion: 0.4
Nodes (5): CreateGodown Page, EditGodown Page, Admin GodownResource, GodownsImport, ListGodowns Page

### Community 21 - "Community 21"
Cohesion: 0.5
Nodes (1): UserFactory

### Community 22 - "Community 22"
Cohesion: 0.5
Nodes (1): VendorLoginRequest

### Community 23 - "Community 23"
Cohesion: 0.5
Nodes (4): Config: Cache, Config: Database, Config: Queue, Config: Session

### Community 31 - "Community 31"
Cohesion: 0.67
Nodes (1): DatabaseSeeder

### Community 32 - "Community 32"
Cohesion: 0.67
Nodes (1): VendorPanelProvider

### Community 33 - "Community 33"
Cohesion: 0.67
Nodes (1): AdminPanelProvider

### Community 34 - "Community 34"
Cohesion: 0.67
Nodes (1): Dashboard

### Community 35 - "Community 35"
Cohesion: 0.67
Nodes (1): Login

### Community 36 - "Community 36"
Cohesion: 0.67
Nodes (1): VendorStockWidget

### Community 37 - "Community 37"
Cohesion: 0.67
Nodes (1): ExampleTest

### Community 38 - "Community 38"
Cohesion: 0.67
Nodes (1): ExampleTest

### Community 40 - "Community 40"
Cohesion: 0.67
Nodes (3): App JS Entry, Axios Bootstrap Setup, Laravel Public Entry Point

### Community 41 - "Community 41"
Cohesion: 0.67
Nodes (3): API Routes (Vendor Mobile), Auth Routes (login/logout), Web Routes

### Community 42 - "Community 42"
Cohesion: 0.67
Nodes (3): Deployment Guide, Post PHP Upgrade Installation Guide, Upgrade PHP on Windows Guide

### Community 43 - "Community 43"
Cohesion: 1.0
Nodes (1): Controller

### Community 44 - "Community 44"
Cohesion: 1.0
Nodes (1): TestCase

### Community 45 - "Community 45"
Cohesion: 1.0
Nodes (2): Admin CollectionJobResource, EditCollectionJob Page

### Community 46 - "Community 46"
Cohesion: 1.0
Nodes (2): Intl Extension Warnings Fix, Install Intl Extension Guide

### Community 47 - "Community 47"
Cohesion: 1.0
Nodes (2): Unit ExampleTest, Feature ExampleTest

### Community 48 - "Community 48"
Cohesion: 1.0
Nodes (2): TrustMeRecycle Project Docs (CLAUDE.md), PDO Deprecation Warning Suppression

### Community 80 - "Community 80"
Cohesion: 1.0
Nodes (1): CreateCacheTable Migration

### Community 81 - "Community 81"
Cohesion: 1.0
Nodes (1): CreateJobsTable Migration

### Community 82 - "Community 82"
Cohesion: 1.0
Nodes (1): Admin Dashboard Page

### Community 83 - "Community 83"
Cohesion: 1.0
Nodes (1): Vendor Dashboard Page

### Community 84 - "Community 84"
Cohesion: 1.0
Nodes (1): Config: App (Application Settings)

### Community 85 - "Community 85"
Cohesion: 1.0
Nodes (1): Config: Services (3rd Party Credentials)

### Community 86 - "Community 86"
Cohesion: 1.0
Nodes (1): Config: Logging (Monolog)

### Community 87 - "Community 87"
Cohesion: 1.0
Nodes (1): Base Test Case

### Community 89 - "Community 89"
Cohesion: 1.0
Nodes (1): Inspire Artisan Command

## Knowledge Gaps
- **71 isolated node(s):** `CreateCollectionJob`, `CreateGodown`, `Controller`, `TestCase`, `users DB table` (+66 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **Thin community `Vendor Bulk Import`** (10 nodes): `VendorsImport.php`, `.import()`, `VendorsImport`, `.getErrors()`, `.getSkippedCount()`, `.getSuccessCount()`, `.getHeaderActions()`, `ListSiteIncharges`, `.getHeaderActions()`, `.getHeaderWidgets()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Site Incharge Filament Resource`** (8 nodes): `SiteInchargeResource.php`, `SiteInchargeResource`, `.canViewAny()`, `.form()`, `.getEloquentQuery()`, `.getPages()`, `.getRelations()`, `.table()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Godown Bulk Import`** (6 nodes): `GodownsImport.php`, `GodownsImport`, `.__construct()`, `.getErrors()`, `.getSkippedCount()`, `.getSuccessCount()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Scrap Limit Notification`** (6 nodes): `ScrapLimitReachedNotification.php`, `ScrapLimitReachedNotification`, `.__construct()`, `.toArray()`, `.toMail()`, `.via()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Site Incharge Export`** (5 nodes): `SiteInchargeExport.php`, `SiteInchargeExport`, `.collection()`, `.headings()`, `.styles()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Scrap Type Resource`** (5 nodes): `ScrapTypeResource.php`, `ScrapTypeResource`, `.form()`, `.getPages()`, `.table()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Collection Job Notification`** (5 nodes): `CollectionJobCreatedNotification.php`, `CollectionJobCreatedNotification`, `.__construct()`, `.toMail()`, `.via()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 21`** (4 nodes): `UserFactory.php`, `UserFactory`, `.definition()`, `.unverified()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 22`** (4 nodes): `VendorLoginRequest`, `.authorize()`, `.rules()`, `VendorLoginRequest.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 31`** (3 nodes): `DatabaseSeeder.php`, `DatabaseSeeder`, `.run()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 32`** (3 nodes): `VendorPanelProvider.php`, `VendorPanelProvider`, `.panel()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 33`** (3 nodes): `AdminPanelProvider.php`, `AdminPanelProvider`, `.panel()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 34`** (3 nodes): `Dashboard.php`, `Dashboard.php`, `Dashboard`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 35`** (3 nodes): `Login.php`, `Login.php`, `Login`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 36`** (3 nodes): `VendorStockWidget.php`, `VendorStockWidget`, `.getStats()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 37`** (3 nodes): `ExampleTest.php`, `ExampleTest`, `.test_that_true_is_true()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 38`** (3 nodes): `ExampleTest`, `.test_the_application_returns_a_successful_response()`, `ExampleTest.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 43`** (2 nodes): `Controller.php`, `Controller`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 44`** (2 nodes): `TestCase.php`, `TestCase`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 45`** (2 nodes): `Admin CollectionJobResource`, `EditCollectionJob Page`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 46`** (2 nodes): `Intl Extension Warnings Fix`, `Install Intl Extension Guide`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 47`** (2 nodes): `Unit ExampleTest`, `Feature ExampleTest`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 48`** (2 nodes): `TrustMeRecycle Project Docs (CLAUDE.md)`, `PDO Deprecation Warning Suppression`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 80`** (1 nodes): `CreateCacheTable Migration`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 81`** (1 nodes): `CreateJobsTable Migration`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 82`** (1 nodes): `Admin Dashboard Page`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 83`** (1 nodes): `Vendor Dashboard Page`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 84`** (1 nodes): `Config: App (Application Settings)`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 85`** (1 nodes): `Config: Services (3rd Party Credentials)`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 86`** (1 nodes): `Config: Logging (Monolog)`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 87`** (1 nodes): `Base Test Case`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 89`** (1 nodes): `Inspire Artisan Command`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Vendor API and Resource Controllers` to `Core Models and Vendor Dashboard`, `Capacity Monitoring and Alerts`, `Vendor Mobile API Jobs`, `Vendor Bulk Import`, `Site Incharge Filament Resource`, `Site Incharge Export`?**
  _High betweenness centrality (0.138) - this node is a cross-community bridge._
- **Why does `Godown Model` connect `Godown and Site Incharge Management` to `Scrap Entry and Type Management`, `Collection Job CRUD`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `ScrapEntryResource` connect `Scrap Entry and Type Management` to `Godown and Site Incharge Management`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Are the 33 inferred relationships involving `User` (e.g. with `.run()` and `.collection()`) actually correct?**
  _`User` has 33 INFERRED edges - model-reasoned connections that need verification._
- **Are the 4 inferred relationships involving `User Model` (e.g. with `EnsureVendorRole Middleware` and `EnsureAdminRole Middleware`) actually correct?**
  _`User Model` has 4 INFERRED edges - model-reasoned connections that need verification._
- **Are the 6 inferred relationships involving `Godown` (e.g. with `.run()` and `.collection()`) actually correct?**
  _`Godown` has 6 INFERRED edges - model-reasoned connections that need verification._
- **Are the 5 inferred relationships involving `CollectionJob` (e.g. with `.run()` and `.table()`) actually correct?**
  _`CollectionJob` has 5 INFERRED edges - model-reasoned connections that need verification._