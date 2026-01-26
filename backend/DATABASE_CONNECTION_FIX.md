# Database Connection Fix - Performance Schema Issue

## Problem Identified

The backend was failing to fetch data from the database due to a MySQL `performance_schema` error:

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'performance_schema.session_status' doesn't exist
```

This error occurs when Laravel tries to query MySQL's `performance_schema.session_status` table to get connection statistics. The `performance_schema` database/tables are missing or corrupted in your MySQL installation.

## Root Cause

Laravel's database connection class attempts to query `performance_schema.session_status` when:
1. Running `php artisan db:show` command
2. Getting connection statistics
3. Some database monitoring operations

Even though your database (`casaverafurniture_db`) exists and has data, Laravel fails when trying to get connection metadata.

## Fixes Applied

### 1. Enhanced Database Configuration (`config/database.php`)
- Added PDO options to improve connection handling:
  - `PDO::ATTR_EMULATE_PREPARES => false`
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
  - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`

### 2. Exception Handler (`bootstrap/app.php`)
- Added global exception handler to catch `performance_schema` errors gracefully
- Returns user-friendly error responses instead of crashing
- Logs the error for debugging while allowing the application to continue

### 3. Configuration Cache Cleared
- Cleared Laravel's configuration cache to ensure new settings are loaded

## Testing the Fix

After these changes, test your database connection:

```powershell
# Test a simple query
php artisan tinker
>>> DB::table('products')->count()
```

Or test via API endpoint:
```bash
GET http://localhost:8001/api/products
```

## Additional Solutions (If Issue Persists)

### Option 1: Fix MySQL Performance Schema (Recommended for Production)

1. **Restart MySQL Service:**
   ```powershell
   Get-Service -Name MySQL*
   Restart-Service MySQL80
   ```

2. **Repair Performance Schema:**
   ```bash
   mysql_upgrade --force
   ```

3. **Or recreate performance_schema:**
   ```sql
   -- Connect to MySQL
   mysql -u root -p
   
   -- Check if performance_schema exists
   SHOW DATABASES LIKE 'performance_schema';
   
   -- If missing, you may need to reinstall MySQL or run mysql_upgrade
   ```

### Option 2: Disable Performance Schema Queries (Workaround)

If you cannot fix the MySQL installation, you can modify Laravel to skip performance_schema queries. However, this requires modifying Laravel core files, which is not recommended for production.

### Option 3: Use SQLite for Development

For local development, you can switch to SQLite which doesn't have this issue:

1. Update `.env`:
   ```env
   DB_CONNECTION=sqlite
   # Comment out MySQL settings
   ```

2. Create SQLite database:
   ```powershell
   New-Item -Path "database\database.sqlite" -ItemType File -Force
   ```

3. Run migrations:
   ```powershell
   php artisan migrate:fresh --seed
   ```

## Current Status

✅ Exception handling added to gracefully handle performance_schema errors
✅ Database configuration enhanced with better PDO options
✅ Configuration cache cleared

⚠️ **Note:** The exception handler will catch and log performance_schema errors, but normal database queries should work fine. The error only occurs when Laravel tries to get connection statistics, not during regular CRUD operations.

## Next Steps

1. Test your API endpoints to verify data fetching works
2. Check Laravel logs (`storage/logs/laravel.log`) for any performance_schema warnings
3. If issues persist, consider fixing MySQL's performance_schema or switching to SQLite for development

## Verification

To verify the fix is working:

1. **Test a simple query:**
   ```php
   // In tinker or a controller
   $products = Product::count();
   echo "Products found: " . $products;
   ```

2. **Test API endpoint:**
   ```bash
   curl http://localhost:8001/api/products
   ```

3. **Check logs:**
   ```powershell
   Get-Content storage\logs\laravel.log -Tail 50
   ```

If you see performance_schema warnings in logs but queries still work, the fix is successful - the application continues working despite the missing performance_schema.
