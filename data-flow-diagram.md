# Recycler Application - Data Flow Diagram

```mermaid
flowchart TD
    %% User Roles
    Admin[Admin User]
    SiteIncharge[Site Incharge/Vendor User]
    
    %% Core Entities
    UserModel[(User Model)]
    ScrapTypeModel[(ScrapType Model)]
    GodownModel[(Godown Model)]
    ScrapEntryModel[(ScrapEntry Model)]
    CollectionJobModel[(CollectionJob Model)]
    
    %% Filament Resources
    SiteInchargeResource[SiteInchargeResource<br/>Filament Admin Panel]
    ScrapTypeResource[ScrapTypeResource<br/>Filament Admin Panel]
    GodownResource[GodownResource<br/>Filament Admin Panel]
    ScrapEntryResource[ScrapEntryResource<br/>Filament Admin Panel]
    CollectionJobResource[CollectionJobResource<br/>Filament Admin Panel]
    
    %% Observers & Notifications
    ScrapEntryObserver[ScrapEntryObserver]
    ScrapLimitNotification[ScrapLimitReachedNotification]
    CollectionJobNotification[CollectionJobCreatedNotification]
    
    %% Database
    Database[(Database)]
    
    %% Admin Flow - User Management
    Admin -->|Creates| SiteInchargeResource
    SiteInchargeResource -->|Saves| UserModel
    UserModel -->|Stores| Database
    
    %% Admin Flow - Scrap Type Management
    Admin -->|Creates/Manages| ScrapTypeResource
    ScrapTypeResource -->|Saves| ScrapTypeModel
    ScrapTypeModel -->|Stores| Database
    
    %% Admin/Site Incharge Flow - Godown Management
    Admin -->|Creates| GodownResource
    SiteIncharge -->|Views Own| GodownResource
    GodownResource -->|Assigns to Site Incharge| GodownModel
    GodownModel -->|Stores| Database
    GodownModel -->|Belongs To| UserModel
    
    %% Site Incharge Flow - Scrap Entry Creation
    SiteIncharge -->|Creates| ScrapEntryResource
    ScrapEntryResource -->|Selects Godown & ScrapType| ScrapEntryModel
    ScrapEntryModel -->|Stores| Database
    ScrapEntryModel -->|Belongs To| GodownModel
    ScrapEntryModel -->|Belongs To| ScrapTypeModel
    
    %% Observer Flow - Automatic Stock Update
    ScrapEntryModel -->|Triggers Event| ScrapEntryObserver
    ScrapEntryObserver -->|Updates Stock| GodownModel
    ScrapEntryObserver -->|Checks Threshold| GodownModel
    
    %% Notification Flow - Capacity Limit Reached
    GodownModel -->|Threshold Reached?| ScrapLimitNotification
    ScrapLimitNotification -->|Notifies| Admin
    ScrapLimitNotification -->|Stores| Database
    
    %% Admin Flow - Collection Job Creation
    Admin -->|Views Godown List| GodownResource
    GodownResource -->|Dispatch Truck Action| CollectionJobModel
    CollectionJobModel -->|Status: truck_dispatched| Database
    CollectionJobModel -->|Belongs To| GodownModel
    
    %% Notification Flow - Truck Dispatched
    CollectionJobModel -->|Triggers| CollectionJobNotification
    CollectionJobNotification -->|Notifies| SiteIncharge
    CollectionJobNotification -->|Stores| Database
    
    %% Site Incharge Flow - Collection Job Completion
    SiteIncharge -->|Views Own Jobs| CollectionJobResource
    CollectionJobResource -->|Updates Status| CollectionJobModel
    CollectionJobModel -->|Status: completed| Database
    CollectionJobModel -->|Stores Proof Images| Database
    
    %% Data Relationships
    UserModel -.->|hasMany| GodownModel
    GodownModel -.->|hasMany| ScrapEntryModel
    GodownModel -.->|hasMany| CollectionJobModel
    ScrapTypeModel -.->|hasMany| ScrapEntryModel
    
    %% Styling
    classDef adminClass fill:#e1f5ff,stroke:#01579b,stroke-width:2px
    classDef siteInchargeClass fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef modelClass fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    classDef resourceClass fill:#e8f5e9,stroke:#1b5e20,stroke-width:2px
    classDef observerClass fill:#fff9c4,stroke:#f57f17,stroke-width:2px
    classDef notificationClass fill:#fce4ec,stroke:#880e4f,stroke-width:2px
    classDef databaseClass fill:#eceff1,stroke:#263238,stroke-width:3px
    
    class Admin adminClass
    class SiteIncharge siteInchargeClass
    class UserModel,ScrapTypeModel,GodownModel,ScrapEntryModel,CollectionJobModel modelClass
    class SiteInchargeResource,ScrapTypeResource,GodownResource,ScrapEntryResource,CollectionJobResource resourceClass
    class ScrapEntryObserver observerClass
    class ScrapLimitNotification,CollectionJobNotification notificationClass
    class Database databaseClass
```

## Data Flow Summary

### 1. **User & Setup Flow**
- **Admin** creates **Site Incharge** users via `SiteInchargeResource`
- **Admin** manages **Scrap Types** (with unit prices) via `ScrapTypeResource`
- **Admin** or **Site Incharge** creates **Godowns** (sites) assigned to Site Incharge

### 2. **Scrap Entry Flow**
- **Site Incharge** creates **Scrap Entries** via `ScrapEntryResource`
- Each entry links to a **Godown** and **Scrap Type**
- `ScrapEntryObserver` automatically:
  - Updates **Godown** `current_stock_mt`
  - Checks if capacity threshold is reached
  - Sends `ScrapLimitReachedNotification` to **Admin** if threshold reached

### 3. **Collection Job Flow**
- **Admin** views **Godowns** and dispatches trucks via "Dispatch Truck" action
- Creates **CollectionJob** with status `truck_dispatched`
- Sends `CollectionJobCreatedNotification` to **Site Incharge**
- **Site Incharge** views and updates collection job:
  - Uploads collection proof image
  - Uploads challan image
  - Updates collected amount
  - Marks job as `completed`

### 4. **Key Relationships**
- **User** (Site Incharge) → hasMany → **Godown**
- **Godown** → hasMany → **ScrapEntry**
- **Godown** → hasMany → **CollectionJob**
- **ScrapType** → hasMany → **ScrapEntry**

### 5. **Notifications**
- **ScrapLimitReachedNotification**: Sent to Admin when godown reaches capacity
- **CollectionJobCreatedNotification**: Sent to Site Incharge when truck is dispatched
