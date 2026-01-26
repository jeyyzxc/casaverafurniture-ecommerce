# Database Connection Fix - PostgreSQL Not Running

## Problem

All API endpoints are returning **500 Internal Server Error** because PostgreSQL is not running.

**Error Message:**
```
SQLSTATE[08006] [7] could not connect to server: Connection refused
Is the server running on host "localhost" and accepting TCP/IP connections on port 5432?
```

## Solution: Start PostgreSQL Server

### Option 1: Start PostgreSQL Service (Windows)

1. **Open Services:**
   - Press `Win + R`
   - Type `services.msc` and press Enter

2. **Find PostgreSQL Service:**
   - Look for a service named:
     - `postgresql-x64-XX` (where XX is version number)
     - `PostgreSQL`
     - `postgresql-x64-16` (or similar version)

3. **Start the Service:**
   - Right-click on the PostgreSQL service
   - Click "Start"
   - Wait for it to start (Status should change to "Running")

### Option 2: Start via Command Line (PowerShell as Administrator)

```powershell
# Check if PostgreSQL service exists
Get-Service | Where-Object {$_.Name -like "*postgres*"}

# Start PostgreSQL service (replace with actual service name)
Start-Service postgresql-x64-16
# OR
Start-Service PostgreSQL
```

### Option 3: Start via pg_ctl (if installed)

```powershell
# Navigate to PostgreSQL bin directory (adjust path as needed)
cd "C:\Program Files\PostgreSQL\16\bin"

# Start PostgreSQL server
.\pg_ctl.exe -D "C:\Program Files\PostgreSQL\16\data" start
```

## Verify PostgreSQL is Running

### Check Service Status

```powershell
Get-Service | Where-Object {$_.Name -like "*postgres*"}
```

The status should show "Running".

### Test Connection

```powershell
# Test connection using psql (if installed)
psql -U postgres -h localhost -p 5432 -d postgres

# Or test from Laravel
cd backend
php artisan tinker
>>> DB::connection()->getPdo();
```

If connection succeeds, you should see a PDO object.

## After Starting PostgreSQL

1. **Verify Database Exists:**
   ```powershell
   cd backend
   php artisan migrate:status
   ```

2. **Run Migrations (if needed):**
   ```powershell
   php artisan migrate
   ```

3. **Test API Endpoints:**
   - Open browser: `http://localhost:5173`
   - Check if products load correctly
   - Check browser console for errors

## Troubleshooting

### Issue: Service won't start

**Possible causes:**
- PostgreSQL installation is corrupted
- Port 5432 is already in use
- Data directory permissions issue

**Solutions:**
1. Check if port 5432 is in use:
   ```powershell
   netstat -ano | findstr :5432
   ```

2. Check PostgreSQL logs:
   - Usually in `C:\Program Files\PostgreSQL\16\data\log\`
   - Or check Windows Event Viewer

3. Reinstall PostgreSQL if necessary

### Issue: Can't find PostgreSQL service

**Possible causes:**
- PostgreSQL not installed
- Service name is different

**Solutions:**
1. Check if PostgreSQL is installed:
   ```powershell
   Get-Command psql -ErrorAction SilentlyContinue
   ```

2. Install PostgreSQL if missing:
   - Download from: https://www.postgresql.org/download/windows/
   - Use default port 5432
   - Remember the password you set (should match `.env` file)

### Issue: Wrong password

**Error:** `password authentication failed`

**Solution:**
1. Check `backend/.env` file:
   ```
   DB_PASSWORD=200519
   ```

2. If password is different, either:
   - Update `.env` with correct password
   - Reset PostgreSQL password

## Quick Start Checklist

- [ ] PostgreSQL service is running
- [ ] Database `casaverafurniture` exists
- [ ] Migrations have been run
- [ ] Backend server is running (`php artisan serve`)
- [ ] Frontend server is running (`npm run dev`)
- [ ] API endpoints return 200 instead of 500

## Alternative: Use SQLite for Development

If you can't get PostgreSQL running, you can temporarily switch to SQLite:

1. **Update `backend/.env`:**
   ```env
   DB_CONNECTION=sqlite
   # Comment out PostgreSQL settings:
   # DB_HOST=localhost
   # DB_PORT=5432
   # DB_DATABASE=casaverafurniture
   # DB_USERNAME=postgres
   # DB_PASSWORD=200519
   ```

2. **Create SQLite database:**
   ```powershell
   cd backend
   New-Item -Path "database\database.sqlite" -ItemType File -Force
   ```

3. **Run migrations:**
   ```powershell
   php artisan migrate
   ```

4. **Clear config cache:**
   ```powershell
   php artisan config:clear
   ```

**Note:** SQLite is fine for development but PostgreSQL is recommended for production.

## Next Steps

Once PostgreSQL is running:

1. ✅ Start PostgreSQL service
2. ✅ Verify connection works
3. ✅ Run migrations if needed
4. ✅ Test API endpoints
5. ✅ Verify frontend loads data correctly

The 500 errors should disappear once PostgreSQL is running!
