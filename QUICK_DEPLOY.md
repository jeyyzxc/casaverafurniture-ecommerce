# Quick Deploy to Render - 5 Minutes

## Fastest Way to Deploy

### Step 1: Push to Git (2 min)
```bash
git add .
git commit -m "Ready for Render deployment"
git push origin main
```

### Step 2: Deploy on Render (3 min)

1. **Go to Render Dashboard**
   - Visit: https://dashboard.render.com
   - Click "New +" → "Blueprint"

2. **Connect Repository**
   - Select your Git provider (GitHub/GitLab)
   - Choose your repository
   - Render will detect `render.yaml`

3. **Review & Deploy**
   - You'll see 3 services:
     - ✅ PostgreSQL Database
     - ✅ Backend API
     - ✅ Frontend
   - Click "Apply"
   - Wait for deployment (~5-10 minutes)

### Step 3: Configure (After Deployment)

1. **Get Your URLs**
   - Backend: `https://casaverafurniture-backend.onrender.com`
   - Frontend: `https://casaverafurniture-frontend.onrender.com`

2. **Set Environment Variables**

   **Backend → Environment:**
   - `GOOGLE_CLIENT_ID`: Your Google OAuth Client ID
   - `GOOGLE_CLIENT_SECRET`: Your Google OAuth Client Secret
   - `GOOGLE_REDIRECT_URI`: `https://casaverafurniture-backend.onrender.com/api/auth/google/callback`

   **Frontend → Environment:**
   - `VITE_API_URL`: Should auto-populate, verify it's correct

3. **Update Google OAuth**
   - Go to Google Cloud Console
   - Add redirect URI: `https://casaverafurniture-backend.onrender.com/api/auth/google/callback`

### Step 4: Test

1. **Backend Health:**
   ```
   https://casaverafurniture-backend.onrender.com/up
   ```

2. **Frontend:**
   ```
   https://casaverafurniture-frontend.onrender.com
   ```

## That's It! 🚀

Your app is now live. All connections are automatically configured:
- ✅ Frontend → Backend
- ✅ Backend → Database
- ✅ CORS configured
- ✅ Environment variables linked

## Need Help?

See `RENDER_DEPLOYMENT_GUIDE.md` for detailed instructions.
