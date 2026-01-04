# TrustMeRecycle - Scrap Recycling Management System

A comprehensive Laravel 11 application for managing scrap recycling operations with vendor portals and admin dashboard.

## **Features**

- **Admin Panel (FilamentPHP)**: Complete CRUD operations for managing godowns, scrap entries, collection jobs, and scrap types
- **Vendor Portal**: Mobile-first interface for vendors to log scrap entries and manage collection jobs
- **Threshold Alerts**: Automatic notifications when godowns reach capacity limits
- **Collection Workflow**: Streamlined process for dispatching trucks and collecting scrap
- **Financial Tracking**: Visual charts showing estimated value trends over time
- **Role-Based Access**: Separate interfaces


   ```

5. Configure database in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=trustmerecycle
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Run migrations and seeders:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. Create storage symlink:
   ```bash
   php artisan storage:link
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

9. Access the application:
   - Home: http://localhost:8000
   - Admin Panel: http://localhost:8000/admin
   - Vendor Portal: http://localhost:8000/vendor/dashboard

### Default Login Credentials

**Admin:**
- Email: `admin@trustmerecycle.com`
- Password: `password`

**Vendor 1:**
- Email: `vendor1@trustmerecycle.com`
- Password: `password`

**Vendor 2:**
- Email: `vendor2@trustmerecycle.com`
- Password: `password`

## Project Structure

```
app/
├── Filament/
│   ├── Resources/          # Filament admin resources
│   ├── Widgets/            # Dashboard widgets
│   └── Pages/              # Custom Filament pages
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           # Authentication controllers
│   │   └── VendorController.php
│   └── Middleware/         # Role-based middleware
├── Models/                 # Eloquent models
├── Notifications/          # Laravel notifications
└── Observers/              # Model observers

database/
├── migrations/             # Database migrations
└── seeders/                # Database seeders

resources/
└── views/
    ├── auth/               # Authentication views
    ├── layouts/            # Layout templates
    └── vendor/             # Vendor portal views

routes/
├── web.php                # Web routes
└── auth.php               # Authentication routes
```

## Key Features Explained

### Threshold Logic

When a vendor adds a scrap entry:
1. The `ScrapEntryObserver` automatically updates the godown's stock
2. If stock reaches or exceeds capacity limit, a notification is sent to all admin users
3. Admin can then dispatch a collection truck from the Filament panel

### Collection Workflow

1. **Admin dispatches truck**: Creates a `CollectionJob` with truck details
2. **Vendor receives notification**: Sees a prominent "Truck Arrived? Mark Collected" card
3. **Vendor uploads proof**: Uploads image and enters collected amount
4. **Stock reduced**: Godown stock is automatically reduced by collected amount

### Vendor Dashboard

- **Stock Card**: Visual progress bar showing current stock vs capacity (turns red when >80%)
- **Add Scrap Form**: Simple form to log daily scrap entries
- **Financial Chart**: 30-day trend of estimated scrap value
- **Collection Jobs**: Action cards for pending/dispatched jobs

### Admin Panel Features

- **Godown Management**: View all godowns with capacity status filters
- **Dispatch Truck Action**: One-click truck dispatch with driver/vehicle details
- **Scrap Type Management**: CRUD for configurable scrap types
- **Dashboard Widgets**: 
  - Total scrap stock across all godowns
  - Capacity alerts (godowns at 80%+)
  - Recent collection jobs

## Database Schema

- **users**: Extended with `role`, `phone`, `profile_photo`
- **scrap_types**: Configurable scrap types with unit prices
- **godowns**: Warehouse information with capacity tracking
- **scrap_entries**: Daily scrap logs with estimated values
- **collection_jobs**: Collection workflow management

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed Hostinger shared hosting deployment instructions.

## Security Notes

- Change default passwords immediately after installation
- Ensure `.env` file is not publicly accessible
- Use HTTPS in production
- Regularly update dependencies

## License

This project is proprietary software for TrustMeRecycle business operations.

## Support

For deployment issues, refer to [DEPLOYMENT.md](DEPLOYMENT.md). For development questions, consult Laravel and FilamentPHP documentation.
