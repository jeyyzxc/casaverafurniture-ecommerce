# Post-Deployment Checklist - Final Steps

## ✅ What's Done
- ✅ Backend deployed and working
- ✅ Frontend deployed and working
- ✅ Database connected
- ✅ Migrations completed

## Next Steps

### Step 1: Connect Frontend to Backend

#### Update Backend Environment Variables

1. Go to your **backend service** in Render Dashboard
2. Go to **"Environment"** tab
3. Update these variables with your frontend URL:

```
FRONTEND_URL=https://your-frontend-url.onrender.com
SANCTUM_STATEFUL_DOMAINS=your-frontend-url.onrender.com
```

**Example:**
If your frontend is at `https://casaverafurniture-frontend.onrender.com`, set:
```
FRONTEND_URL=https://casaverafurniture-frontend.onrender.com
SANCTUM_STATEFUL_DOMAINS=casaverafurniture-frontend.onrender.com
```

4. **Save** - Backend will automatically restart

#### Verify Frontend API URL

1. Go to your **frontend service** in Render Dashboard
2. Go to **"Environment"** tab
3. Verify `VITE_API_URL` is set correctly:
   ```
   VITE_API_URL=https://your-backend-url.onrender.com/api
   ```

**Note:** If you need to change `VITE_API_URL`, you must rebuild the frontend (it's a build-time variable).

### Step 2: Test the Application

#### Test Frontend
1. Open your frontend URL: `https://your-frontend-url.onrender.com`
2. Check if it loads correctly
3. Open browser console (F12) - check for any API errors

#### Test API Connection
1. Try to load products (should show empty list or products)
2. Try to access admin panel (if you have one)
3. Test login/authentication

#### Test Backend Health
- Health check: `https://your-backend-url.onrender.com/up`
- API test: `https://your-backend-url.onrender.com/api/products`

### Step 3: Seed Database (Optional but Recommended)

If you have seeders, run them to add initial data:

**Option A: Using Render Shell (if available)**
1. Go to backend service
2. Click **"Shell"** or **"Console"**
3. Run:
   ```bash
   php artisan db:seed
   ```

**Option B: Add to Pre-Deploy Command**
1. Go to backend service → **"Settings"**
2. Find **"Pre-Deploy Command"**
3. Add seeding:
   ```bash
   php artisan migrate --force && php artisan db:seed --force
   ```
4. Trigger manual deploy

**Option C: Create Admin User**
If you have a seeder for admin users, run it. Otherwise, you can create one manually via tinker or a custom command.

### Step 4: Create Admin Account

If you need to create an admin account:

**Option A: Using Artisan Command (if you have one)**
```bash
php artisan admin:create
```

**Option B: Using Tinker**
1. Go to backend service → **"Shell"** (if available)
2. Run:
   ```bash
   php artisan tinker
   ```
3. Then:
   ```php
   $admin = \App\Models\Admin::create([
       'first_name' => 'Admin',
       'last_name' => 'User',
       'email' => 'admin@casavera.com',
       'password' => bcrypt('your-secure-password'),
       'role_id' => 1, // Adjust based on your roles
   ]);
   ```

**Option C: Using Database Directly**
- Connect to your PostgreSQL database
- Insert admin record manually

### Step 5: Verify CORS Configuration

Your backend should automatically allow your frontend domain. Verify:

1. Try making an API request from frontend
2. Check browser console for CORS errors
3. If CORS errors appear, check backend `config/cors.php`

### Step 6: Test Full User Flow

1. **Browse Products** - Check if products load
2. **Add to Cart** - Test cart functionality
3. **Checkout** - Test checkout process
4. **Admin Login** - Test admin panel access
5. **Add Products** - Test adding products through admin

### Step 7: Configure Custom Domains (Optional)

If you have custom domains:

1. Go to each service → **"Settings"** → **"Custom Domains"**
2. Add your domain
3. Update DNS records as instructed
4. Update environment variables with new domains

### Step 8: Set Up Monitoring (Optional)

1. Enable Render's built-in monitoring
2. Set up error alerts
3. Monitor service health

## Troubleshooting

### Frontend Can't Connect to Backend

**Symptoms:**
- CORS errors in browser console
- API requests failing
- 401/403 errors

**Fix:**
1. Verify `VITE_API_URL` in frontend environment
2. Verify `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` in backend
3. Check backend CORS configuration
4. Rebuild frontend if `VITE_API_URL` was changed

### Authentication Not Working

**Symptoms:**
- Can't login
- Tokens not being saved
- Session issues

**Fix:**
1. Check `SANCTUM_STATEFUL_DOMAINS` includes frontend domain
2. Verify `SESSION_DRIVER=database` in backend
3. Check if sessions table exists (should be created by migrations)
4. Verify cookies are being set (check browser DevTools)

### Products Not Showing

**Symptoms:**
- Empty product list
- API returns empty array

**Fix:**
1. This is normal if database is empty
2. Add products through admin panel
3. Or run seeders to add sample data

## Quick Verification Checklist

- [ ] Frontend loads without errors
- [ ] Backend API responds correctly
- [ ] Frontend can call backend API (check browser console)
- [ ] No CORS errors
- [ ] Admin login works (if applicable)
- [ ] Products can be viewed (even if empty)
- [ ] Database has necessary tables
- [ ] Environment variables are set correctly

## Summary

Your application is now **fully deployed**! 🎉

**Current Status:**
- ✅ Backend: Live and working
- ✅ Frontend: Live and working  
- ✅ Database: Connected and migrated
- ⏳ Next: Connect them together and test

**Priority Actions:**
1. Update backend `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS`
2. Verify frontend `VITE_API_URL` is correct
3. Test the full application flow
4. Add initial data (seeders or manually)

---

**Congratulations on deploying your application!** 🚀
