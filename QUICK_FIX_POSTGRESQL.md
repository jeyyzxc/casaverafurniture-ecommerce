# Quick Fix: PostgreSQL Not Running

## The Problem
All API requests are failing with **500 Internal Server Error** because PostgreSQL database server is not running.

## Quick Solution (2 minutes)

### Step 1: Start PostgreSQL Service

**Method A - Using Services (Easiest):**
1. Press `Win + R`
2. Type `services.msc` and press Enter
3. Find `postgresql-x64-16` (or similar)
4. Right-click → Start

**Method B - Using PowerShell (Run as Administrator):**
```powershell
Start-Service postgresql-x64-16
```

### Step 2: Verify It's Running

```powershell
Get-Service | Where-Object {$_.Name -like "*postgres*"}
```

Should show status: **Running**

### Step 3: Test the Fix

1. Refresh your browser at `http://localhost:5173`
2. Products should now load
3. No more 500 errors!

## That's It!

Once PostgreSQL is running, all the 500 errors will disappear and your application will work normally.

## Still Having Issues?

See `DATABASE_CONNECTION_FIX.md` for detailed troubleshooting.
