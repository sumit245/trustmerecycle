# Install PHP intl Extension

## Problem
The error "The 'intl' PHP extension is required to use the [format] method" occurs because Laravel's `Number::format()` method (used by Filament) requires the `intl` PHP extension.

## Solution: Install intl Extension

### For macOS (using Homebrew)

1. **Check your PHP version:**
   ```bash
   php -v
   ```

2. **Install intl extension:**
   
   If you're using Homebrew PHP:
   ```bash
   brew install php-intl
   ```
   
   Or if you have a specific PHP version:
   ```bash
   brew install php@8.2-intl  # Replace 8.2 with your PHP version
   ```

3. **Enable the extension:**
   
   Find your PHP ini file:
   ```bash
   php --ini
   ```
   
   Edit the php.ini file and uncomment or add:
   ```ini
   extension=intl
   ```
   
   Or create/edit `/usr/local/etc/php/8.2/conf.d/ext-intl.ini` (adjust version):
   ```ini
   extension=intl.so
   ```

4. **Verify installation:**
   ```bash
   php -m | grep intl
   ```
   
   Should output: `intl`

5. **Restart your PHP server:**
   ```bash
   # Stop your current server (Ctrl+C)
   # Then restart:
   php artisan serve
   ```

### Alternative: Using PECL

If Homebrew doesn't work:

```bash
pecl install intl
```

Then add to php.ini:
```ini
extension=intl.so
```

### For Shared Hosting (Hostinger)

If you're deploying to Hostinger shared hosting:

1. **Check if intl is available:**
   - Log into cPanel
   - Go to "Select PHP Version" or "PHP Configuration"
   - Look for "intl" in the list of extensions
   - Enable it if available

2. **If not available:**
   - Contact Hostinger support to enable the intl extension
   - Or use a VPS/dedicated server where you have more control

### Verify After Installation

Run this command to verify:
```bash
php -r "echo extension_loaded('intl') ? 'intl is loaded' : 'intl is NOT loaded';"
```

Should output: `intl is loaded`

## After Installation

1. **Clear Laravel caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

2. **Restart your development server:**
   ```bash
   php artisan serve
   ```

3. **Test the admin panel:**
   - Visit `http://127.0.0.1:8001/admin/login`
   - Login should work without the intl error

## Troubleshooting

### If intl still doesn't load:

1. **Check PHP configuration:**
   ```bash
   php -i | grep "Configuration File"
   php -i | grep "extension_dir"
   ```

2. **Verify extension file exists:**
   ```bash
   find /usr -name "intl.so" 2>/dev/null
   ```

3. **Check for errors:**
   ```bash
   php -m 2>&1 | grep -i intl
   ```

### If you can't install intl:

As a temporary workaround, you could modify Filament resources to avoid using `Number::format()`, but this is not recommended. It's better to install the extension.












