# Post-PHP Upgrade Installation Steps

After you have successfully upgraded PHP to version 8.2 or higher, follow these steps to complete the TrustMeRecycle installation:

## 1. Verify Your PHP Installation

```bash
php -v
# Should show PHP version 8.2 or higher

php -m | findstr intl
# Should show 'intl' is loaded
```

## 2. Run the Environment Check Script

```bash
php check_environment.php
# Should show both PHP version and extensions are OK
```

## 3. Install Project Dependencies

```bash
composer install
```

## 4. Set Up Environment

```bash
# Copy example environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

## 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Run seeders (if needed)
php artisan db:seed
```

## 6. Create Storage Link (if needed)

```bash
php artisan storage:link
```

## 7. Start the Development Server

```bash
php artisan serve
```

## 8. Access the Application

- Home: http://127.0.0.1:8000
- Admin Panel: http://127.0.0.1:8000/admin
- Vendor Portal: http://127.0.0.1:8000/vendor/dashboard

## Default Login Credentials

**Admin:**
- Email: `admin@trustmerecycle.com`
- Password: `password`

**Vendor 1:**
- Email: `vendor1@trustmerecycle.com`
- Password: `password`

**Vendor 2:**
- Email: `vendor2@trustmerecycle.com`
- Password: `password`

## Troubleshooting

If you still encounter issues after PHP upgrade:

1. **Clear composer cache:**
   ```bash
   composer clear-cache
   ```

2. **Clear Laravel caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Try installing with no dev dependencies first:**
   ```bash
   composer install --no-dev
   ```

4. **Check file permissions** (especially on Windows with XAMPP):
   - Make sure the `storage` and `bootstrap/cache` directories are writable
