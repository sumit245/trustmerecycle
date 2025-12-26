# Suppressing PDO Deprecation Warnings

## Issue
PHP 8.5+ shows deprecation warnings for `PDO::MYSQL_ATTR_SSL_CA` constant used in Laravel's vendor files. These warnings are harmless and will be fixed in future Laravel updates.

## Solutions

### Option 1: Set APP_DEBUG=false (Recommended for Production)
In your `.env` file:
```env
APP_DEBUG=false
```

This will hide deprecation warnings in production. Keep it `true` during development to see other important errors.

### Option 2: Suppress in index.php (Already Applied)
The `public/index.php` file has been updated to suppress deprecation warnings when `APP_DEBUG=false`.

### Option 3: Ignore in php.ini (System-wide)
Add to your `php.ini`:
```ini
error_reporting = E_ALL & ~E_DEPRECATED
```

## Note
These warnings don't affect functionality. They're just notifications that Laravel will need to update their code for PHP 8.5+ compatibility in future releases.

## Current Status
- Login page is working correctly
- Warnings are cosmetic only
- Application functionality is not affected

