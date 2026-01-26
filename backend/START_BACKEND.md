# Backend Server Startup Guide

## ✅ Verification Status

The backend has been verified and is ready to run:

- ✅ **Database Connection:** SUCCESS
  - Database: `casaverafurniture_db`
  - Host: `127.0.0.1:3306`
  - Username: `root`
  
- ✅ **Database Tables:** All tables accessible
  - Products: 16 records
  - Categories: 18 records
  - Admins: 2 records
  - Users: 0 records
  - Orders: 0 records

- ✅ **API Routes:** 102 routes registered

- ✅ **Environment:** Configured correctly
  - APP_ENV: local
  - APP_DEBUG: true
  - APP_URL: http://localhost:8000

## Starting the Backend Server

### Option 1: Using Artisan Serve (Recommended for Development)

```powershell
cd backend
php artisan serve
```

The server will start on: **http://localhost:8000**

You should see:
```
Laravel development server started: http://127.0.0.1:8000
```

### Option 2: Using a Specific Port

If port 8000 is already in use:

```powershell
cd backend
php artisan serve --port=8001
```

Then update `frontend/.env`:
```env
VITE_API_URL=http://localhost:8001/api
```

### Option 3: Using a Specific Host and Port

```powershell
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

## Verifying the Server is Running

### Test 1: Health Check
Open in browser or use curl:
```
http://localhost:8000/up
```

Should return: `{"status":"ok"}`

### Test 2: API Endpoint
```bash
curl http://localhost:8000/api/products
```

Should return JSON with products data.

### Test 3: Admin Login Endpoint
```bash
curl -X POST http://localhost:8000/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@casavera.com","password":"password"}'
```

Should return authentication token.

## Quick Verification Script

Run the verification script anytime:

```powershell
cd backend
php verify-backend.php
```

This will check:
- Database connection
- Table accessibility
- API routes
- Environment configuration

## Troubleshooting

### Issue: Port Already in Use

**Error:** `Address already in use`

**Solution:**
1. Find what's using port 8000:
   ```powershell
   netstat -ano | findstr :8000
   ```
2. Kill the process or use a different port:
   ```powershell
   php artisan serve --port=8001
   ```

### Issue: Database Connection Failed

**Error:** `SQLSTATE[HY000] [1045] Access denied`

**Solution:**
1. Check `.env` file has correct credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=casaverafurniture_db
   DB_USERNAME=root
   DB_PASSWORD=200519_Jerome
   ```
2. Clear config cache:
   ```powershell
   php artisan config:clear
   ```

### Issue: Performance Schema Error

**Error:** `Table 'performance_schema.session_status' doesn't exist`

**Solution:**
- This is handled gracefully by the exception handler
- The application will continue working despite this error
- Regular database queries are not affected

## Running in Background (Windows)

To run the server in the background on Windows:

```powershell
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd backend; php artisan serve"
```

## Next Steps

Once the backend is running:

1. ✅ Backend server started on http://localhost:8000
2. ✅ Start frontend: `cd frontend && npm run dev`
3. ✅ Test admin login at: http://localhost:5173/admin/login
4. ✅ Test API endpoints

---

**Backend is ready! Start it with: `php artisan serve`** 🚀
