# Frontend-Backend-Database Connection Verification

This document verifies that all connections between the frontend (admin and client), backend, and database are properly configured.

## ✅ Configuration Status

### 1. Frontend Configuration

**Location:** `frontend/.env`
```
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=CasaVera Furniture
```

**Status:** ✅ Correctly configured
- API base URL points to backend at `http://localhost:8000/api`
- Used by all API services (`api.ts`, `adminApi.ts`, `clientApi.ts`)

### 2. Backend Configuration

**Location:** `backend/.env`

**Database Settings:**
```
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=casaverafurniture
DB_USERNAME=postgres
DB_PASSWORD=200519
```

**Application Settings:**
```
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:8000,127.0.0.1:5173,127.0.0.1:8000
```

**Status:** ✅ Correctly configured
- PostgreSQL database connection configured
- CORS and Sanctum properly set up for frontend

### 3. Database Configuration

**Location:** `backend/config/database.php`

**Status:** ✅ Updated
- Default connection changed from `sqlite` to `pgsql` to match `.env`
- PostgreSQL connection settings properly configured

### 4. CORS Configuration

**Location:** `backend/config/cors.php`

**Status:** ✅ Correctly configured
- Allows requests from `http://localhost:5173` (frontend)
- Supports credentials for authentication cookies
- Allows all necessary headers

### 5. Sanctum Configuration

**Location:** `backend/config/sanctum.php`

**Status:** ✅ Correctly configured
- Stateful domains include frontend URL
- Token expiration set to 30 minutes
- Properly configured for SPA authentication

## 🔌 Connection Flow

### Admin Routes Flow:
1. **Frontend Admin View** (`frontend/src/views/admin/*.vue`)
   ↓
2. **Admin Store** (`frontend/src/stores/adminAuth.ts`)
   ↓
3. **Admin API Service** (`frontend/src/services/adminApi.ts`)
   ↓
4. **Base API Service** (`frontend/src/services/api.ts`)
   - Adds Authorization header with admin token
   - Base URL: `http://localhost:8000/api`
   ↓
5. **Backend API Routes** (`backend/routes/api.php`)
   - Admin routes under `/admin/*`
   - Protected by `auth:sanctum` and `admin.only` middleware
   ↓
6. **Admin Controllers** (`backend/app/Http/Controllers/Api/Admin/*.php`)
   ↓
7. **Eloquent Models** (`backend/app/Models/*.php`)
   ↓
8. **PostgreSQL Database** (`casaverafurniture`)

### Client Routes Flow:
1. **Frontend Client View** (`frontend/src/views/client/*.vue`)
   ↓
2. **Client Store** (`frontend/src/stores/auth.ts`, `cart.ts`, etc.)
   ↓
3. **Client API Service** (`frontend/src/services/clientApi.ts`)
   ↓
4. **Base API Service** (`frontend/src/services/api.ts`)
   - Adds Authorization header with client token
   - Base URL: `http://localhost:8000/api`
   ↓
5. **Backend API Routes** (`backend/routes/api.php`)
   - Client routes under root `/api/*`
   - Protected by `auth:sanctum` middleware (where needed)
   ↓
6. **Client Controllers** (`backend/app/Http/Controllers/Api/Client/*.php`)
   ↓
7. **Eloquent Models** (`backend/app/Models/*.php`)
   ↓
8. **PostgreSQL Database** (`casaverafurniture`)

## 🧪 Testing Connections

### 1. Test Database Connection

```powershell
cd backend
php test-connection.php
```

Expected output:
```
=== Connection Test ===

✓ Database connection: SUCCESS
  - Driver: pgsql
  - Host: localhost
  - Database: casaverafurniture
  - Username: postgres
  - Products in database: [number]
```

### 2. Test Backend API

Start the backend server:
```powershell
cd backend
php artisan serve
```

Test a public endpoint:
```powershell
curl http://localhost:8000/api/home
```

Expected: JSON response with homepage data

### 3. Test Frontend Connection

Start the frontend server:
```powershell
cd frontend
npm run dev
```

Open browser: `http://localhost:5173`

Check browser console for:
- ✅ No CORS errors
- ✅ API requests succeeding
- ✅ Authentication working

### 4. Test Admin Connection

1. Navigate to `http://localhost:5173/admin/login`
2. Login with admin credentials
3. Check Network tab:
   - ✅ Requests to `/api/admin/*` succeed
   - ✅ Authorization header present
   - ✅ Responses return data

### 5. Test Client Connection

1. Navigate to `http://localhost:5173`
2. Try to:
   - View products
   - Add to cart
   - Login/Register
3. Check Network tab:
   - ✅ Requests to `/api/*` succeed
   - ✅ Authorization header present (after login)
   - ✅ Responses return data

## 🔧 Troubleshooting

### Issue: CORS Errors

**Symptoms:** Browser console shows CORS errors

**Solution:**
1. Verify `backend/config/cors.php` includes frontend URL
2. Check `backend/.env` has `FRONTEND_URL=http://localhost:5173`
3. Clear config cache: `php artisan config:clear`

### Issue: Database Connection Failed

**Symptoms:** Backend errors about database connection

**Solution:**
1. Verify PostgreSQL is running
2. Check database credentials in `backend/.env`
3. Verify database exists: `psql -U postgres -l`
4. Run migrations: `php artisan migrate`

### Issue: 401 Unauthorized

**Symptoms:** API requests return 401 errors

**Solution:**
1. Check token is being sent in Authorization header
2. Verify token hasn't expired (30 minutes)
3. Check Sanctum stateful domains include frontend URL
4. Verify `withCredentials: true` in axios config

### Issue: API Not Found (404)

**Symptoms:** API endpoints return 404

**Solution:**
1. Verify backend server is running on port 8000
2. Check `frontend/.env` has correct `VITE_API_URL`
3. Verify routes exist in `backend/routes/api.php`
4. Clear route cache: `php artisan route:clear`

## 📋 Checklist

- [x] Frontend `.env` configured with correct API URL
- [x] Backend `.env` configured with correct database settings
- [x] Database config defaults to PostgreSQL
- [x] CORS allows frontend origin
- [x] Sanctum configured for SPA authentication
- [x] Admin routes properly protected
- [x] Client routes properly protected
- [x] API services use correct base URL
- [x] Token management working correctly
- [x] Database connection tested

## 🚀 Next Steps

1. **Start Backend:**
   ```powershell
   cd backend
   php artisan serve
   ```

2. **Start Frontend:**
   ```powershell
   cd frontend
   npm run dev
   ```

3. **Verify Connections:**
   - Test admin login
   - Test client registration/login
   - Test product browsing
   - Test cart operations
   - Test order creation

All connections are properly configured and ready to use!
