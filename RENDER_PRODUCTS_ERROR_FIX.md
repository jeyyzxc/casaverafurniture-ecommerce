# Fix "Failed to load products" Error

## Issue
The `/api/products` endpoint returns:
```json
{"success": false, "message": "Failed to load products", "error": null}
```

## Most Likely Causes

### 1. Database Migrations Not Run
The database tables might not exist yet.

**Solution:**
1. Go to your backend service in Render Dashboard
2. Click **"Shell"** or **"Console"** tab (if available)
3. Or use **"Manual Deploy"** → **"Run Command"**
4. Run:
   ```bash
   php artisan migrate --force
   ```

**Alternative:** If you have Pre-Deploy Command set, it should run migrations automatically. Check if it's configured.

### 2. Database Connection Issue
The database credentials might be incorrect.

**Check:**
1. Go to backend service → **"Environment"** tab
2. Verify all database variables are set:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=<your-postgres-host>
   DB_PORT=5432
   DB_DATABASE=<your-database-name>
   DB_USERNAME=<your-database-user>
   DB_PASSWORD=<your-database-password>
   ```

### 3. Enable Debug Mode Temporarily
To see the actual error message:

1. Go to backend service → **"Environment"** tab
2. Change:
   ```
   APP_DEBUG=true
   ```
3. Save and wait for service to restart
4. Try `/api/products` again - you'll see the actual error
5. **Important:** Change it back to `false` after fixing

### 4. Check Render Logs
The actual error is logged. Check:

1. Go to backend service → **"Logs"** tab
2. Look for recent errors
3. Search for "Products index failed" or database errors
4. The log will show the real error message

## Step-by-Step Fix

### Step 1: Check Logs First
1. Open backend service → **"Logs"**
2. Look for error messages around the time you tried `/api/products`
3. Share the error if you need help

### Step 2: Run Migrations
If tables don't exist:

**Option A: Using Render Shell (if available)**
1. Go to backend service
2. Click **"Shell"** or **"Console"**
3. Run: `php artisan migrate --force`

**Option B: Using Pre-Deploy Command**
1. Go to backend service → **"Settings"**
2. Find **"Pre-Deploy Command"**
3. Make sure it includes:
   ```bash
   php artisan migrate --force
   ```
4. Trigger a manual deploy

**Option C: Update Dockerfile**
Add migrations to startup (less ideal, but works):
- We can modify the Dockerfile to run migrations on startup

### Step 3: Verify Database Connection
Test if backend can connect to database:

1. Enable `APP_DEBUG=true` temporarily
2. Try accessing `/api/products` again
3. Check the error message
4. If it's a database connection error, fix credentials

### Step 4: Check Database Tables
If you have database access, verify tables exist:
- `products` table should exist
- `categories` table should exist
- Other related tables

## Quick Diagnostic Commands

If you have shell access, run these:

```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check if products table exists
php artisan tinker
Schema::hasTable('products');

# Count products
php artisan tinker
\App\Models\Product::count();
```

## Most Common Fix

**90% of the time, it's missing migrations:**

1. Go to Render Dashboard → Your backend service
2. Look for **"Shell"**, **"Console"**, or **"Run Command"** option
3. Run: `php artisan migrate --force`
4. Try `/api/products` again

If you don't have shell access, we can add migrations to the Dockerfile startup script.

## After Fixing

Once products load:
1. Change `APP_DEBUG` back to `false`
2. Test other endpoints
3. Deploy frontend and connect it

---

**Next Step:** Check your Render logs first to see the actual error!
