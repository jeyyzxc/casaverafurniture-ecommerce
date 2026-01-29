<!-- # Deploy Backend Docker Service to Render

## Step-by-Step Guide

### Prerequisites
- ✅ You already have a PostgreSQL database on Render
- ✅ Your code is pushed to GitHub/GitLab
- ✅ You have a Render account

### Step 1: Create New Web Service

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +"** → **"Web Service"**
3. Connect your Git repository:
   - Select your Git provider (GitHub/GitLab)
   - Choose the repository: `casaverafurniture-ecommerce`
   - Click **"Connect"**

### Step 2: Configure the Service

#### Basic Settings:
- **Name**: `casaverafurniture-backend` (or any name you prefer)
- **Region**: Choose `Oregon` (or closest to your users)
- **Branch**: `main` (or your default branch)
- **Root Directory**: Leave empty (we'll set this in Docker context)

#### Runtime Settings:
- **Environment**: Select **"Docker"**
- **Dockerfile Path**: `backend/Dockerfile`
- **Docker Context**: `backend`

### Step 3: Configure Build & Start Commands

**Where to find these in Render Dashboard:**
After selecting "Docker" as the environment, you'll see these fields in the service creation form:

#### Build Command:
- **Location**: In the service creation form, look for "Build Command" field
- **Action**: Leave this **EMPTY** - Docker handles the build automatically
- Docker will use your Dockerfile to build the image

#### Start Command:
- **Location**: In the service creation form, look for "Start Command" field  
- **Action**: Leave this **EMPTY** - Your Dockerfile's CMD handles this
- The Dockerfile already has: `CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}`

#### Pre-Deploy Command (Optional but Recommended):
- **Location**: In the service creation form, look for "Pre-Deploy Command" or "Advanced" section
- **Action**: Paste this command:
```bash
php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize
```

**Note**: If you don't see a "Pre-Deploy Command" field, you can:
1. Skip it for now (migrations can be run manually later)
2. Or add these commands to your Dockerfile's CMD (but pre-deploy is better)
3. Or run migrations manually after first deployment via Render's shell/console

### Step 4: Set Environment Variables

Go to the **"Environment"** tab and add these variables:

#### Required Database Variables:
```
DB_CONNECTION=pgsql
DB_HOST=<your-postgres-host>
DB_PORT=<your-postgres-port>
DB_DATABASE=<your-database-name>
DB_USERNAME=<your-database-username>
DB_PASSWORD=<your-database-password>
```

**To get your database credentials:**
1. Go to your PostgreSQL service in Render Dashboard
2. Click on the database service
3. Go to **"Info"** tab
4. Copy the values from **"Internal Database URL"** or use the individual fields:
   - **Host**: Found in connection info
   - **Port**: Usually `5432`
   - **Database**: Your database name
   - **User**: Your database user
   - **Password**: Click "Reveal" to see password

#### Required App Variables:
```
APP_NAME=CasaVeraFurniture
APP_ENV=production
APP_DEBUG=false
APP_URL=https://casaverafurniture-backend.onrender.com
```

**Note**: `APP_URL` will be set automatically after first deployment. You can update it then.

#### Generate APP_KEY:
1. In the Environment tab, click **"Add Environment Variable"**
2. Key: `APP_KEY`
3. Value: Click **"Generate"** or run locally:
   ```bash
   php artisan key:generate --show
   ```
4. Copy the generated key and paste it

#### Frontend/CORS Variables:
```
FRONTEND_URL=https://your-frontend-url.onrender.com
SANCTUM_STATEFUL_DOMAINS=your-frontend-url.onrender.com
```

**Note**: Set these after you deploy the frontend. For now, you can use a placeholder.

#### Session & Cache Variables:
```
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_DRIVER=log
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Step 5: Advanced Settings (Optional)

#### Health Check Path:
- **Health Check Path**: `/up`
- This endpoint is already configured in your Laravel app

#### Auto-Deploy:
- Enable **"Auto-Deploy"** if you want automatic deployments on git push

#### Plan:
- Choose **"Starter"** plan ($7/month) or **"Free"** (if available, with limitations)

### Step 6: Deploy

1. Click **"Create Web Service"**
2. Render will:
   - Build your Docker image
   - Install dependencies
   - Run your pre-deploy commands (migrations, caching)
   - Start your service

### Step 7: Monitor Deployment

1. Watch the **"Logs"** tab for build progress
2. Wait for the build to complete (usually 5-10 minutes)
3. Check for any errors in the logs

### Step 8: Verify Deployment

Once deployed, test your service:

1. **Health Check**: 
   ```
   https://your-service-name.onrender.com/up
   ```
   Should return: `{"status":"ok"}`

2. **API Test**:
   ```
   https://your-service-name.onrender.com/api/products
   ```
   Should return JSON data

### Troubleshooting

#### Build Fails:
- Check logs for specific errors
- Verify Dockerfile path is correct: `backend/Dockerfile`
- Verify Docker context is set to: `backend`

#### Database Connection Fails:
- Double-check database credentials
- Ensure database is in the same region
- Check if database allows connections from your service

#### Migrations Fail:
- Check database credentials are correct
- Verify database user has proper permissions
- Check logs for specific migration errors

#### Service Won't Start:
- Check logs for PHP errors
- Verify `APP_KEY` is set
- Check if port is correctly configured (Render provides `$PORT`)

### Next Steps

After backend is deployed:
1. Note your backend URL
2. Deploy frontend service
3. Update `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` in backend environment
4. Update frontend `VITE_API_URL` to point to your backend

---

## Quick Reference: Environment Variables Checklist

Copy this checklist and fill in your values:

```
✅ DB_CONNECTION=pgsql
✅ DB_HOST=________________
✅ DB_PORT=________________
✅ DB_DATABASE=________________
✅ DB_USERNAME=________________
✅ DB_PASSWORD=________________
✅ APP_NAME=CasaVeraFurniture
✅ APP_ENV=production
✅ APP_DEBUG=false
✅ APP_KEY=________________ (generate this)
✅ APP_URL=________________ (update after deployment)
✅ FRONTEND_URL=________________ (update after frontend deploy)
✅ SANCTUM_STATEFUL_DOMAINS=________________ (update after frontend deploy)
✅ SESSION_DRIVER=database
✅ CACHE_STORE=database
✅ QUEUE_CONNECTION=database
✅ BROADCAST_DRIVER=log
✅ LOG_CHANNEL=stack
✅ LOG_LEVEL=error
``` -->
