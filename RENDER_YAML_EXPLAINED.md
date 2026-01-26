<!-- # render.yaml - Complete Explanation

This document explains every part of your `render.yaml` configuration file.

## 📋 File Overview

The `render.yaml` file defines 3 services that will be deployed on Render:
1. **PostgreSQL Database** - Your data storage
2. **Laravel Backend** - Your API server
3. **Vue Frontend** - Your web application

---

## 🗄️ Service 1: PostgreSQL Database

```yaml
- type: pspg                    # PostgreSQL managed database
  name: casaverafurniture-db    # Service name (used for references)
  plan: starter                  # Pricing plan ($7/month)
  databaseName: casaverafurniture
  databaseUser: casaverafurniture_user
  postgresMajorVersion: 16      # PostgreSQL version
```

### What This Does:
- Creates a managed PostgreSQL 16 database
- Automatically handles backups (7-day retention)
- Provides persistent storage (data never lost)
- Auto-configures connection credentials

### Key Points:
- **`type: pspg`** - Render's managed PostgreSQL service
- **`plan: starter`** - $7/month, includes 7-day backups
- **`postgresMajorVersion: 16`** - Uses PostgreSQL 16 (latest stable)

---

## 🚀 Service 2: Laravel Backend API

### Basic Configuration

```yaml
- type: web                     # Web service (not static)
  name: casaverafurniture-backend
  runtime: php                  # PHP runtime
  plan: starter                 # $7/month
  region: oregon                # US West Coast
  phpVersion: 8.2               # PHP 8.2 (required for Laravel 12)
  healthCheckPath: /up          # Health check endpoint
```

### Build Command

```yaml
buildCommand: |
  cd backend
  composer install --no-dev --optimize-autoloader
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
```

**What happens:**
1. Installs PHP dependencies (production only)
2. Optimizes autoloader for faster performance
3. Caches configuration files
4. Caches routes for faster routing
5. Caches views for faster rendering

### Start Command

```yaml
startCommand: |
  cd backend
  php artisan migrate --force
  php artisan optimize
  php artisan serve --host=0.0.0.0 --port=$PORT
```

**What happens:**
1. Runs database migrations automatically
2. Optimizes Laravel for production
3. Starts PHP development server on Render's port

### Environment Variables

#### Application Settings
```yaml
APP_NAME: CasaVeraFurniture
APP_ENV: production
APP_DEBUG: false              # Security: no debug info in production
APP_URL: (auto from service)  # Automatically set to backend URL
```

#### Frontend Connection
```yaml
FRONTEND_URL: (auto from frontend service)
SANCTUM_STATEFUL_DOMAINS: (auto from frontend service)
```
These auto-link to your frontend service for CORS and authentication.

#### Database Connection
```yaml
DB_CONNECTION: pgsql
DB_HOST: (auto from database service)
DB_PORT: (auto from database service)
DB_DATABASE: (auto from database service)
DB_USERNAME: (auto from database service)
DB_PASSWORD: (auto from database service)
```
All database credentials are automatically linked from the database service!

#### Laravel Settings
```yaml
SESSION_DRIVER: database       # Store sessions in database
CACHE_STORE: database          # Use database for caching
QUEUE_CONNECTION: database     # Use database for job queues
BROADCAST_DRIVER: log          # Log broadcasts (no real-time for now)
LOG_CHANNEL: stack
LOG_LEVEL: error               # Only log errors in production
```

#### Security
```yaml
APP_KEY: (auto-generated)      # Laravel encryption key
GOOGLE_CLIENT_ID: (manual)     # You need to set this
GOOGLE_CLIENT_SECRET: (manual) # You need to set this
GOOGLE_REDIRECT_URI: (auto)    # Auto-set to backend URL
```

---

## 🎨 Service 3: Vue Frontend

### Basic Configuration

```yaml
- type: web                    # Web service
  name: casaverafurniture-frontend
  runtime: node                # Node.js runtime
  plan: starter                # $7/month
  region: oregon               # Same region as backend (faster)
  NODE_VERSION: 20.19.0        # Node.js version
```

### Build Command

```yaml
buildCommand: |
  cd frontend
  npm ci                       # Clean install (faster, more reliable)
  npm run build                # Build Vue app for production
```

**What happens:**
1. Installs npm dependencies
2. Builds optimized production bundle
3. Creates `dist/` folder with static files

### Start Command

```yaml
startCommand: |
  cd frontend
  npx serve -s dist -l $PORT --single
```

**What happens:**
1. Serves the `dist/` folder
2. `-s` = silent mode
3. `-l $PORT` = listen on Render's port
4. `--single` = SPA mode (all routes → index.html)

### Environment Variables

```yaml
VITE_API_URL: (auto from backend service)/api
VITE_APP_NAME: CasaVera Furniture
```

**Important:** `VITE_API_URL` is automatically linked to your backend service!

---

## 🔗 Automatic Service Linking

### How Services Connect

1. **Frontend → Backend:**
   ```
   VITE_API_URL automatically points to backend service URL
   ```

2. **Backend → Database:**
   ```
   All DB_* variables automatically linked from database service
   ```

3. **Backend → Frontend:**
   ```
   FRONTEND_URL and SANCTUM_STATEFUL_DOMAINS auto-linked
   ```

### The Magic of `fromService` and `fromDatabase`

```yaml
# Example: Auto-link database host
- key: DB_HOST
  fromDatabase:
    name: casaverafurniture-db
    property: host
```

This automatically gets the database host from the `casaverafurniture-db` service!

```yaml
# Example: Auto-link frontend URL
- key: FRONTEND_URL
  fromService:
    type: web
    name: casaverafurniture-frontend
    property: host
```

This automatically gets the frontend URL from the `casaverafurniture-frontend` service!

---

## 📊 Deployment Flow

When you deploy:

1. **Database Created First**
   - PostgreSQL service starts
   - Database and user created
   - Connection credentials generated

2. **Backend Deploys**
   - Builds PHP application
   - Gets database credentials automatically
   - Runs migrations
   - Starts API server

3. **Frontend Deploys**
   - Builds Vue application
   - Gets backend URL automatically
   - Starts web server

4. **Everything Connected**
   - Frontend can call backend API
   - Backend can query database
   - CORS configured automatically

---

## ⚙️ Configuration Options

### Plans Available

**Starter Plan ($7/month per service):**
- Good for: Development, small apps
- Includes: 7-day backups, basic resources
- Total: ~$21/month (3 services)

**Standard Plan ($20/month per service):**
- Good for: Production, medium traffic
- Includes: 30-day backups, more resources
- Total: ~$60/month (3 services)

### Regions Available

- `oregon` - US West Coast (fastest for US)
- `frankfurt` - Europe
- `singapore` - Asia Pacific
- `ohio` - US East Coast

**Recommendation:** Use same region for all services (faster communication)

### PHP Versions

- `8.2` - Required for Laravel 12 ✅ (current)
- `8.3` - Also supported
- `8.1` - Not supported by Laravel 12

### Node Versions

- `20.19.0` - Current LTS ✅ (current)
- `22.x` - Latest (also supported)
- `18.x` - Older LTS (also supported)

---

## 🔧 Customization Options

### Add Health Checks

Already included:
```yaml
healthCheckPath: /up
```

### Add Auto-Deploy

```yaml
autoDeploy: true  # Deploy on every git push
```

### Add Build Caching

```yaml
buildFilter: |
  # Only rebuild if these files change
  backend/composer.json
  backend/composer.lock
```

### Add Environment-Specific Settings

```yaml
envVars:
  - key: ENVIRONMENT
    value: production
  - key: FEATURE_FLAG_X
    value: true
```

---

## ✅ Verification Checklist

Before deploying, verify:

- [x] All service names are unique
- [x] Database credentials auto-linked
- [x] Frontend URL auto-linked to backend
- [x] PHP version matches Laravel requirements (8.2+)
- [x] Node version matches package.json (20.19.0)
- [x] Build commands are correct
- [x] Start commands are correct
- [x] Health check path exists (`/up`)
- [x] All required env vars are set

---

## 🚨 Common Issues & Fixes

### Issue: Build Fails

**Check:**
- PHP/Node version matches requirements
- Dependencies are in `composer.json` / `package.json`
- Build commands are correct

### Issue: Database Connection Fails

**Check:**
- Database service is created first
- `fromDatabase` references correct service name
- Database credentials are auto-populated

### Issue: Frontend Can't Reach Backend

**Check:**
- `VITE_API_URL` is set correctly
- Backend service is running
- CORS is configured in backend

### Issue: Migrations Fail

**Check:**
- Database is accessible
- Migrations are in `database/migrations/`
- `--force` flag is in start command

---

## 📚 Additional Resources

- **Render Docs**: https://render.com/docs
- **Laravel Docs**: https://laravel.com/docs
- **Vue Docs**: https://vuejs.org
- **PostgreSQL Docs**: https://www.postgresql.org/docs

---

## 🎯 Summary

Your `render.yaml` is **production-ready** and includes:

✅ **3 Services** - Database, Backend, Frontend  
✅ **Auto-Linking** - Services connect automatically  
✅ **Optimized Builds** - Production-ready configurations  
✅ **Health Checks** - Automatic monitoring  
✅ **Data Persistence** - Automatic backups  
✅ **Security** - Production settings enabled  

**Ready to deploy!** 🚀 -->
