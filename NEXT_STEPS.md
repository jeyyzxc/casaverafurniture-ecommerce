# 🚀 Next Steps to Deploy to Render

## Step 1: Commit Your Changes to Git

Make sure all your files are committed:

```bash
# Check what files have changed
git status

# Add all files (including render.yaml)
git add .

# Commit with a descriptive message
git commit -m "Add Render deployment configuration with PostgreSQL 18"

# Push to your repository
git push origin main
```

**Important:** Make sure `render.yaml` is in the root directory and committed!

---

## Step 2: Create Render Account (If You Don't Have One)

1. Go to https://render.com
2. Click "Get Started for Free"
3. Sign up with GitHub/GitLab (recommended) or email
4. Verify your email if needed

---

## Step 3: Deploy on Render (5-10 minutes)

### 3.1 Create Blueprint

1. **Go to Render Dashboard**
   - Visit: https://dashboard.render.com
   - Click the **"New +"** button (top right)
   - Select **"Blueprint"**

2. **Connect Your Repository**
   - Choose your Git provider (GitHub/GitLab/Bitbucket)
   - Authorize Render to access your repositories
   - Select your `casaverafurniture` repository
   - Select branch: `main` (or `master`)

3. **Review Services**
   - Render will automatically detect `render.yaml`
   - You'll see 3 services:
     - ✅ **PostgreSQL Database** (`casaverafurniture-db`)
     - ✅ **Backend API** (`casaverafurniture-backend`)
     - ✅ **Frontend** (`casaverafurniture-frontend`)
   - Review the configuration
   - **Click "Apply"** to start deployment

4. **Wait for Deployment**
   - Database will be created first (~2 minutes)
   - Backend will build and deploy (~5 minutes)
   - Frontend will build and deploy (~3 minutes)
   - **Total: ~10 minutes**

---

## Step 4: Configure Environment Variables (After Deployment)

Once services are "Live", configure these:

### Backend Environment Variables

1. Go to **Backend Service** → **Environment** tab
2. Add these variables (if not auto-set):

   ```
   GOOGLE_CLIENT_ID=your-google-client-id
   GOOGLE_CLIENT_SECRET=your-google-client-secret
   ```

3. Verify these are auto-set (should be already):
   - ✅ `APP_KEY` (auto-generated)
   - ✅ `APP_URL` (auto-set)
   - ✅ `FRONTEND_URL` (auto-set)
   - ✅ All `DB_*` variables (auto-linked from database)

### Frontend Environment Variables

1. Go to **Frontend Service** → **Environment** tab
2. Verify:
   - ✅ `VITE_API_URL` (should auto-populate with backend URL)

**Note:** If you change frontend env vars, you need to **rebuild** the service!

---

## Step 5: Update Google OAuth (If Using)

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Navigate to **APIs & Services** → **Credentials**
3. Edit your OAuth 2.0 Client
4. Add authorized redirect URI:
   ```
   https://casaverafurniture-backend.onrender.com/api/auth/google/callback
   ```
   (Replace with your actual backend URL)
5. Save changes

---

## Step 6: Verify Deployment

### Test Backend

1. **Health Check:**
   ```
   https://your-backend-url.onrender.com/up
   ```
   Should return: `{"status":"ok"}`

2. **API Test:**
   ```
   https://your-backend-url.onrender.com/api/home
   ```
   Should return JSON data

### Test Frontend

1. **Open Frontend:**
   ```
   https://your-frontend-url.onrender.com
   ```
   Should load your homepage

2. **Check Browser Console:**
   - No CORS errors
   - API calls succeed
   - Products load correctly

### Test Database

1. **Check Backend Logs:**
   - Go to Backend Service → **Logs** tab
   - Look for: "Database connection: SUCCESS"
   - Look for: "Migrations completed"

2. **Test API Endpoints:**
   - Try creating a product (if admin)
   - Try adding to cart
   - Verify data persists

---

## Step 7: Create Admin User (If Needed)

If you need to create an admin user:

1. **Option 1: Use Seeder**
   - Add seeder to create admin
   - Run: `php artisan db:seed --class=AdminSeeder`
   - (You can do this via Render Shell)

2. **Option 2: Manual Creation**
   - Use Render Shell to access backend
   - Run: `php artisan tinker`
   - Create admin user manually

---

## ✅ Deployment Complete Checklist

- [ ] All code pushed to Git
- [ ] Services deployed on Render
- [ ] All services show "Live" status
- [ ] Database connection successful
- [ ] Migrations ran successfully
- [ ] Environment variables configured
- [ ] Google OAuth updated (if using)
- [ ] Backend health check passes
- [ ] Frontend loads correctly
- [ ] API endpoints work
- [ ] No errors in logs

---

## 🆘 Troubleshooting

### Issue: Build Failed

**Check:**
- Service logs for specific errors
- PHP/Node version matches requirements
- Dependencies are in composer.json/package.json

### Issue: Database Connection Failed

**Check:**
- Database service is running
- Backend logs for connection errors
- Environment variables are set correctly

### Issue: Frontend Can't Reach Backend

**Check:**
- `VITE_API_URL` is set correctly
- Backend service is running
- CORS is configured (should be automatic)

### Issue: 500 Errors

**Check:**
- Backend logs for specific errors
- Database migrations completed
- Environment variables are set

---

## 📞 Need Help?

1. **Check Logs:**
   - Render Dashboard → Service → Logs tab

2. **Check Documentation:**
   - `RENDER_DEPLOYMENT_GUIDE.md` - Detailed guide
   - `QUICK_DEPLOY.md` - Quick reference

3. **Render Support:**
   - Email: support@render.com
   - Docs: https://render.com/docs

---

## 🎯 Quick Command Reference

```bash
# Check Git status
git status

# Commit changes
git add .
git commit -m "Ready for deployment"
git push origin main

# After deployment, check backend health
curl https://your-backend.onrender.com/up

# Test API
curl https://your-backend.onrender.com/api/home
```

---

**You're ready to deploy! Start with Step 1 above.** 🚀
