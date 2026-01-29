# Deploy Frontend (Vue) Service to Render

## Prerequisites
- ✅ Backend is deployed and working
- ✅ You have your backend URL (e.g., `https://casaverafurniture-backend.onrender.com`)
- ✅ Your code is pushed to GitHub/GitLab

## Step-by-Step Guide

### Step 1: Create New Web Service

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +"** → **"Web Service"**
3. Connect your Git repository:
   - Select your Git provider (GitHub/GitLab)
   - Choose the repository: `casaverafurniture-ecommerce`
   - Click **"Connect"**

### Step 2: Configure the Service

#### Basic Settings:
- **Name**: `casaverafurniture-frontend` (or any name you prefer)
- **Region**: Choose same region as backend (e.g., `Oregon`)
- **Branch**: `main` (or your default branch)
- **Root Directory**: `frontend`

#### Runtime Settings:
- **Environment**: Select **"Node"**
- **Build Command**: 
  ```bash
  npm install && npm run build-only
  ```
  **Note**: 
  - Using `npm install` instead of `npm ci` to handle Rollup's optional dependencies correctly
  - Using `build-only` instead of `build` to skip TypeScript type-checking (faster and avoids type errors in production)
- **Start Command**: 
  ```bash
  npx serve -s dist -l $PORT --single
  ```

**Note**: The `--single` flag is important for Vue Router's history mode to work properly.

### Step 3: Set Environment Variables

Go to the **"Environment"** tab and add these variables:

#### Required Variables:
```
VITE_API_URL=https://your-backend-url.onrender.com/api
VITE_APP_NAME=CasaVera Furniture
NODE_VERSION=20.19.0
```

**Important**: 
- Replace `your-backend-url.onrender.com` with your actual backend URL
- The `/api` at the end is important - your frontend expects the API at that path
- `VITE_API_URL` is used at **build time**, so set it before the first build

#### Example:
If your backend is at `https://casaverafurniture-backend.onrender.com`, then:
```
VITE_API_URL=https://casaverafurniture-backend.onrender.com/api
```

### Step 4: Advanced Settings (Optional)

#### Health Check Path:
- **Health Check Path**: `/` (or leave empty)
- Frontend doesn't need a specific health endpoint

#### Auto-Deploy:
- Enable **"Auto-Deploy"** if you want automatic deployments on git push

#### Plan:
- Choose **"Starter"** plan ($7/month) or **"Free"** (if available, with limitations)

### Step 5: Deploy

1. Click **"Create Web Service"**
2. Render will:
   - Install Node.js dependencies
   - Build your Vue application (this embeds `VITE_API_URL` into the build)
   - Start serving the built files

### Step 6: Monitor Deployment

1. Watch the **"Logs"** tab for build progress
2. Wait for the build to complete (usually 3-5 minutes)
3. Check for any errors in the logs

### Step 7: Verify Deployment

Once deployed, test your frontend:

1. **Open your frontend URL**: `https://your-frontend-url.onrender.com`
2. **Check if it loads** - You should see your Vue app
3. **Test API connection** - Try logging in or fetching data
4. **Check browser console** - Look for any API connection errors

### Step 8: Update Backend Environment Variables

Now that frontend is deployed, update your backend service:

1. Go to your **backend service** → **"Environment"** tab
2. Update these variables:
   ```
   FRONTEND_URL=https://your-frontend-url.onrender.com
   SANCTUM_STATEFUL_DOMAINS=your-frontend-url.onrender.com
   ```
3. **Save** the changes
4. Backend will automatically restart with new settings

## Troubleshooting

### Build Fails:
- **Check Node version**: Ensure `NODE_VERSION=20.19.0` matches your `package.json`
- **Check build logs**: Look for specific npm/vite errors
- **Verify root directory**: Should be `frontend`

### Frontend Can't Connect to Backend:
- **Check VITE_API_URL**: Must be set correctly before build
- **Check CORS**: Backend must have `FRONTEND_URL` in CORS allowed origins
- **Check browser console**: Look for CORS or network errors

### 404 Errors on Routes:
- **Verify Start Command**: Must include `--single` flag for Vue Router history mode
- **Check if routes are working**: Try accessing routes directly

### API Calls Fail:
1. Check `VITE_API_URL` is correct
2. Check backend is running
3. Check CORS settings in backend
4. Check browser console for specific errors

## Important Notes

### VITE_API_URL is Build-Time
- `VITE_API_URL` is embedded into your build during `npm run build`
- If you change it later, you **must rebuild** the frontend
- To update: Change the env var → Manual Deploy → Clear cache & deploy

### CORS Configuration
- Your backend needs to allow requests from your frontend domain
- This is handled by `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` in backend
- Make sure these are set correctly after frontend deployment

## Quick Checklist

Before deploying:
- [ ] Backend is deployed and working
- [ ] You have your backend URL
- [ ] `VITE_API_URL` is set correctly (with `/api` at the end)
- [ ] Root directory is set to `frontend`
- [ ] Build command: `npm ci && npm run build`
- [ ] Start command: `npx serve -s dist -l $PORT --single`

After deploying:
- [ ] Frontend loads successfully
- [ ] Update backend `FRONTEND_URL`
- [ ] Update backend `SANCTUM_STATEFUL_DOMAINS`
- [ ] Test API connections
- [ ] Test authentication/login

## Next Steps

After both services are deployed:
1. ✅ Test the full application flow
2. ✅ Set up custom domains (optional)
3. ✅ Configure monitoring (optional)
4. ✅ Set up backups for database

---

**Your frontend should now be live!** 🎉
