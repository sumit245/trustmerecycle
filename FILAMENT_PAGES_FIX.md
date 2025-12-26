# Filament Pages Registration Fix

## Issue
Error: `Call to a member function getPage() on string`

## Root Cause
Filament v3 expects `getPages()` to return `PageRegistration` objects, not class strings. The pages need to use the `route()` method to create proper registrations.

## Solution Applied
Updated all Filament resources to use the correct page registration format:

**Before (incorrect):**
```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListGodowns::class,
    ];
}
```

**After (correct):**
```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListGodowns::route('/'),
    ];
}
```

## Files Fixed
- `app/Filament/Resources/GodownResource.php`
- `app/Filament/Resources/ScrapTypeResource.php`
- `app/Filament/Resources/ScrapEntryResource.php`
- `app/Filament/Resources/CollectionJobResource.php`

## Next Steps
1. Clear caches:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Test the admin panel:
   - Visit: `http://127.0.0.1:8001/admin`
   - Should now load without errors

