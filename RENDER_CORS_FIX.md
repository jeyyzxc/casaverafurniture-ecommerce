# Fix CORS Error - Complete Solution

## The Problem

Your frontend (`https://casaverafurniture-ecommerce2.onrender.com`) is trying to access your backend (`https://casaverafurniture-ecommerce-rufs.onrender.com`), but the backend isn't allowing it because `FRONTEND_URL` environment variable is not set.

## Solution: Set Environment Variables in Backend

### Step 1: Update Backend Environment Variables

1. Go to **Render Dashboard**
2. Click on your **backend service** (`casaverafurniture-ecommerce-rufs`)
3. Go to **"Environment"** tab
4. Add or update these variables:

```
FRONTEND_URL=https://casaverafurniture-ecommerce2.onrender.com
SANCTUM_STATEFUL_DOMAINS=casaverafurniture-ecommerce2.onrender.com
```

**Important Notes:**
- `FRONTEND_URL` should include `https://`
- `SANCTUM_STATEFUL_DOMAINS` should NOT include `https://` (just the domain)

### Step 2: Clear Config Cache

After updating environment variables, you need to clear the config cache:

**Option A: Using Render Shell (if available)**
1. Go to backend service
2. Click **"Shell"** or **"Console"**
3. Run:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

**Option B: Restart Service**
1. Go to backend service
2. Click **"Manual Deploy"** → **"Deploy latest commit"**
3. This will restart the service and clear caches

### Step 3: Verify CORS is Working

1. Open your frontend: `https://casaverafurniture-ecommerce2.onrender.com`
2. Open browser console (F12)
3. Check if CORS errors are gone
4. Try loading products - should work now

## Why This Happens

Laravel's CORS configuration reads from `env('FRONTEND_URL')`. If this isn't set:
- `array_filter()` removes the `null` value
- Only localhost origins remain in the allowed list
- Your production frontend gets blocked

## Alternative: Make CORS More Permissive (Not Recommended for Production)

If you want to allow all origins (for testing only), you can modify `config/cors.php`:

```php
'allowed_origins' => ['*'], // Allow all origins (NOT recommended for production)
```

But the proper solution is to set `FRONTEND_URL` correctly.

## Quick Fix Summary

1. ✅ Set `FRONTEND_URL=https://casaverafurniture-ecommerce2.onrender.com` in backend
2. ✅ Set `SANCTUM_STATEFUL_DOMAINS=casaverafurniture-ecommerce2.onrender.com` in backend
3. ✅ Clear config cache or restart service
4. ✅ Test frontend → backend connection

---

**After setting these variables, your CORS errors should be resolved!** ✅
