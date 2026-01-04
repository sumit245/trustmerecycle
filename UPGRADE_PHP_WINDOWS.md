# Upgrading PHP for TrustMeRecycle Project on Windows

## Current Issues
- Project requires PHP ^8.2 but current version is 8.1.17
- Composer dependencies require PHP 8.2+
- [intl](file:///c%3A/Users/Munish/Desktop/trustmerecycle/config/app.php#L32-L32) extension has been enabled (this is now resolved)

## Solution: Upgrade PHP to 8.2+

### Option 1: Upgrade XAMPP (Recommended)

1. **Download latest XAMPP**:
   - Go to https://www.apachefriends.org/download.html
   - Download the latest version that includes PHP 8.2 or higher

2. **Backup your data**:
   ```bash
   # Backup your databases
   mysqldump -u root -p --all-databases > backup_all_databases.sql
   
   # Backup your project files if needed
   ```

3. **Install new XAMPP**:
   - Uninstall old XAMPP (optional - you can install alongside)
   - Install new XAMPP to a different directory if you want to keep the old one
   - Or install over the existing installation

4. **Verify PHP version**:
   ```bash
   php -v
   # Should show PHP 8.2 or higher
   ```

### Option 2: Manual PHP Upgrade

1. **Download PHP 8.2+**:
   - Go to https://windows.php.net/download/
   - Download the Thread Safe (TS) version for your architecture (x64)
   - Extract to a temporary folder

2. **Replace XAMPP PHP**:
   - Navigate to your XAMPP installation (usually C:\xampp)
   - Rename the existing `php` folder to `php_old` as backup
   - Copy the new PHP folder to XAMPP and rename it to `php`
   - Copy your `php.ini` file from the backup (`php_old`) to the new `php` folder
   - Make sure the [intl](file:///c%3A/Users/Munish/Desktop/trustmerecycle/config/app.php#L32-L32) extension is enabled in the new php.ini

3. **Update PATH** (if needed):
   - Make sure your system PATH points to the new PHP directory
   - Or use the full path to PHP when running commands

### Option 3: Use PHP Version Manager (PHP Manager for XAMPP)

1. **Install PHP Manager**:
   - Download PHP Manager for XAMPP from GitHub
   - This allows you to easily switch between PHP versions

2. **Install multiple PHP versions**:
   - Download different PHP versions and manage them through the manager

## Verification Steps

After upgrading PHP, verify everything works:

1. **Check PHP version**:
   ```bash
   php -v
   # Should show PHP 8.2 or higher
   ```

2. **Check required extensions**:
   ```bash
   php -m | findstr intl
   # Should show 'intl'
   ```

3. **Try composer install**:
   ```bash
   composer install
   # Should complete without PHP version errors
   ```

4. **Run Laravel commands**:
   ```bash
   php artisan --version
   php artisan config:clear
   ```

## Troubleshooting

### If you get Apache/MySQL conflicts after upgrading XAMPP:
- Stop all XAMPP services before installing new version
- Check that ports are not in use by other applications

### If composer still shows errors:
- Clear composer cache: `composer clear-cache`
- Try `composer install --ignore-platform-reqs` temporarily (not recommended for production)

### If Laravel doesn't work after PHP upgrade:
- Run `composer install` again
- Clear all caches: `php artisan config:clear`, `php artisan cache:clear`
- Check that all extensions required by Laravel are available

## Next Steps

Once you have PHP 8.2+ installed:

1. Run `composer install` in your project directory
2. Run `php artisan key:generate`
3. Run database migrations: `php artisan migrate`
4. Start the development server: `php artisan serve`

Remember: The [intl](file:///c%3A/Users/Munish/Desktop/trustmerecycle/config/app.php#L32-L32) extension has already been enabled in your php.ini file, so you don't need to do that again after upgrading.
