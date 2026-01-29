# Fixing 505 Error on Render

## Common Causes of 505 Error

1. **Missing APP_KEY** - Laravel requires this
2. **Database connection failing** - Server crashes on startup
3. **Missing environment variables**
4. **Port configuration issue**

## Step-by-Step Fix

### Step 1: Check Render Logs

1. Go to your service in Render Dashboard
2. Click on **"Logs"** tab
3. Look for error messages - this will tell you exactly what's wrong

### Step 2: Verify Environment Variables

Go to your service → **"Environment"** tab and ensure these are set:

#### Critical Variables:
```
APP_KEY=<must be set - generate if missing>
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-service-name.onrender.com
```

#### Database Variables:
```
DB_CONNECTION=pgsql
DB_HOST=<your-postgres-host>
DB_PORT=5432
DB_DATABASE=<your-database-name>
DB_USERNAME=<your-database-user>
DB_PASSWORD=<your-database-password>
```

### Step 3: Generate APP_KEY (if missing)

**Option A: Generate in Render Dashboard**
1. Go to Environment tab
2. Add variable: `APP_KEY`
3. Click "Generate" button (if available)

**Option B: Generate locally and copy**
```bash
cd backend
php artisan key:generate --show
```
Copy the output and paste it as `APP_KEY` value in Render.

### Step 4: Check Database Connection

Make sure your database credentials are correct:
1. Go to your PostgreSQL service in Render
2. Copy the connection details
3. Verify they match your environment variables

### Step 5: Check Service Logs for Specific Errors

Common errors you might see:

**Error: "No application encryption key has been specified"**
- Solution: Set `APP_KEY` environment variable

**Error: "SQLSTATE[HY000] [2002] Connection refused"**
- Solution: Check database host, port, and credentials

**Error: "Port already in use"**
- Solution: This shouldn't happen, but check if PORT env var is set

**Error: "Class not found" or "Autoload error"**
- Solution: Rebuild the service (clear cache and redeploy)

### Step 6: Test Database Connection Manually

If you have shell access, you can test:
```bash
php artisan tinker
DB::connection()->getPdo();
```

### Step 7: Try Manual Deploy with Cache Clear

1. In Render Dashboard, go to your service
2. Click **"Manual Deploy"**
3. Select **"Clear build cache & deploy"**
4. This forces a fresh build

## Quick Checklist

- [ ] APP_KEY is set and valid
- [ ] Database credentials are correct
- [ ] DB_CONNECTION=pgsql (not mysql)
- [ ] All required environment variables are set
- [ ] Service logs show no fatal errors
- [ ] Database service is running and accessible

## If Still Not Working

1. **Check the exact error in logs** - Share it for specific help
2. **Try accessing the health endpoint**: `https://your-url.onrender.com/up`
3. **Check if service is actually running** - Look for "Live" status
4. **Verify the PORT environment variable** - Render sets this automatically

## Next Steps After Fix

Once the service is working:
1. Test health endpoint: `/up`
2. Test API endpoint: `/api/products`
3. Run migrations if needed
4. Deploy frontend and connect it
