# TrustMeRecycle - Deployment Guide for Hostinger Shared Hosting

This guide will help you deploy the TrustMeRecycle Laravel application to Hostinger shared hosting.

## Prerequisites

- Hostinger shared hosting account with cPanel access
- MySQL database created via phpMyAdmin
- FTP/SFTP access or cPanel File Manager
- SSH access (optional, but recommended)

## Step 1: Database Setup

1. Log in to your Hostinger cPanel
2. Open phpMyAdmin
3. Create a new MySQL database (e.g., `trustmerecycle_db`)
4. Create a database user and assign it to the database
5. Note down the database credentials:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

## Step 2: Upload Files

### Option A: Using cPanel File Manager

1. Log in to cPanel
2. Navigate to File Manager
3. Go to `public_html` (or your domain's root directory)
4. Upload all project files (except `vendor` and `node_modules` if present)
5. Extract if uploaded as ZIP

### Option B: Using FTP/SFTP

1. Connect to your hosting via FTP/SFTP client (FileZilla, WinSCP, etc.)
2. Upload all project files to `public_html` directory
3. Ensure file permissions are set correctly (folders: 755, files: 644)

## Step 3: Configure Environment

1. In cPanel File Manager, navigate to your project root
2. Locate `.env.example` file
3. Copy it to `.env`
4. Edit `.env` file with your database credentials:

```env
APP_NAME="TrustMeRecycle"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

5. Generate application key (via SSH or cPanel Terminal):
   ```bash
   php artisan key:generate
   ```

## Step 4: Set File Permissions

Set the following permissions via cPanel File Manager or SSH:

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 644 .env
```

Or via cPanel File Manager:
- `storage/` folder: 755
- `bootstrap/cache/` folder: 755
- `.env` file: 644

## Step 5: Create Storage Symlink

### Via SSH (Recommended):

```bash
cd /home/username/public_html
php artisan storage:link
```

### Via cPanel:

1. Go to cPanel Terminal (if available)
2. Navigate to your project directory
3. Run: `php artisan storage:link`

### Manual Method (if SSH not available):

1. In cPanel File Manager, go to `public/` directory
2. Create a symbolic link named `storage` pointing to `../storage/app/public`
   - Or create a folder `public/storage` and copy contents from `storage/app/public`

## Step 6: Run Migrations and Seeders

### Via SSH:

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Via cPanel Terminal:

Same commands as above.

### Via phpMyAdmin (Alternative):

If SSH is not available, you can import the database schema manually:
1. Export your local database
2. Import it via phpMyAdmin

## Step 7: Configure .htaccess for Subdirectory (If Needed)

If your Laravel app is installed in a subdirectory (e.g., `public_html/recycler/`):

1. Edit `public/.htaccess`
2. Uncomment and update the `RewriteBase` line:
   ```apache
   RewriteBase /recycler/public/
   ```

3. Update `APP_URL` in `.env`:
   ```env
   APP_URL=https://yourdomain.com/recycler/public
   ```

## Step 8: Point Document Root (If Installed in Root)

If installed directly in `public_html`:

1. In cPanel, go to **Domains** → **Your Domain** → **Document Root**
2. Set document root to: `public_html/public`
3. Save changes

## Step 9: Verify Installation

1. Visit your domain: `https://yourdomain.com`
2. You should see the welcome page or login page
3. Test login with seeded credentials:
   - **Admin**: `admin@trustmerecycle.com` / `password`
   - **Vendor 1**: `vendor1@trustmerecycle.com` / `password`
   - **Vendor 2**: `vendor2@trustmerecycle.com` / `password`

## Step 10: Admin Panel Access

1. Navigate to: `https://yourdomain.com/admin`
2. Login with admin credentials
3. You should see the Filament admin panel

## Troubleshooting

### Issue: 500 Internal Server Error

**Solutions:**
- Check file permissions (storage and bootstrap/cache must be writable)
- Verify `.env` file exists and has correct permissions
- Check error logs in cPanel → Error Logs
- Ensure `APP_DEBUG=true` temporarily to see detailed errors

### Issue: Storage Images Not Loading

**Solutions:**
- Verify storage symlink exists: `public/storage` → `storage/app/public`
- Check `storage/app/public/proofs/` directory exists and is writable
- Verify `APP_URL` in `.env` matches your domain

### Issue: Database Connection Error

**Solutions:**
- Verify database credentials in `.env`
- Check database host (try `localhost` or `127.0.0.1`)
- Ensure database user has proper permissions
- Check if database exists in phpMyAdmin

### Issue: Filament Panel Not Loading

**Solutions:**
- Ensure Filament provider is registered in `bootstrap/providers.php`
- Clear config cache: `php artisan config:clear`
- Clear route cache: `php artisan route:clear`
- Clear view cache: `php artisan view:clear`

### Issue: Permission Denied Errors

**Solutions:**
- Set correct permissions:
  ```bash
  find storage -type f -exec chmod 644 {} \;
  find storage -type d -exec chmod 755 {} \;
  find bootstrap/cache -type f -exec chmod 644 {} \;
  find bootstrap/cache -type d -exec chmod 755 {} \;
  ```

## Post-Deployment Checklist

- [ ] Database migrations run successfully
- [ ] Seeders executed (admin and vendors created)
- [ ] Storage symlink created
- [ ] File permissions set correctly
- [ ] `.env` file configured with production values
- [ ] `APP_DEBUG=false` in production
- [ ] Admin panel accessible at `/admin`
- [ ] Vendor portal accessible at `/vendor/dashboard`
- [ ] File uploads working (test collection proof upload)
- [ ] Notifications working (test threshold alert)

## Security Recommendations

1. **Change Default Passwords**: Immediately change default passwords after first login
2. **Enable HTTPS**: Ensure SSL certificate is installed and active
3. **Hide .env File**: Ensure `.env` is not publicly accessible
4. **Regular Backups**: Set up automated database backups via cPanel
5. **Update Dependencies**: Regularly update Laravel and packages via Composer

## Maintenance Commands

Via SSH or cPanel Terminal:

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration (for better performance)
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

## Support

For issues specific to:
- **Laravel**: Check Laravel documentation
- **Filament**: Check Filament documentation
- **Hostinger**: Contact Hostinger support

---

**Note**: This application requires PHP 8.2+ and MySQL 5.7+. Ensure your Hostinger hosting plan supports these requirements.

