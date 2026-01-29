# Fix Missing Database Tables Error

## Error
```
SQLSTATE[42P01]: Undefined table: 7 ERROR:  relation "products" does not exist
```

## Solution

The database tables don't exist yet. You need to run migrations.

## Option 1: Update Dockerfile (Recommended)

I've updated your Dockerfile to automatically run migrations on startup. 

**Next steps:**
1. Commit and push the updated Dockerfile:
   ```bash
   git add backend/Dockerfile
   git commit -m "Add automatic migrations to startup script"
   git push origin main
   ```

2. Render will automatically rebuild and run migrations

## Option 2: Use Pre-Deploy Command

If you prefer to run migrations before the service starts:

1. Go to your backend service → **"Settings"**
2. Find **"Pre-Deploy Command"**
3. Set it to:
   ```bash
   php artisan migrate --force
   ```
4. Save and trigger a manual deploy

## Option 3: Use Render Shell (If Available)

1. Go to your backend service
2. Look for **"Shell"** or **"Console"** tab
3. Run:
   ```bash
   php artisan migrate --force
   ```

## What the Updated Dockerfile Does

The startup script now:
1. Checks for APP_KEY (generates if missing)
2. **Runs migrations automatically** (`php artisan migrate --force`)
3. Caches configuration for performance
4. Starts the Laravel server

## After Migrations Run

Once migrations complete:
- ✅ All tables will be created (products, categories, users, etc.)
- ✅ `/api/products` should work
- ✅ Other endpoints should work

## Verify It Worked

After the service restarts:
1. Check logs for "Running database migrations..."
2. Try `/api/products` again
3. Should return products (or empty array if no data)

---

**Quick Fix:** Just commit and push the updated Dockerfile, and Render will handle the rest!
