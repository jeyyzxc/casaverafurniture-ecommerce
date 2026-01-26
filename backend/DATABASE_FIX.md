# Database Connection Fix Guide

## Issue
MySQL connection error: `Table 'performance_schema.session_status' doesn't exist`

## Solutions

### Option 1: Fix MySQL Performance Schema (Recommended for Production)
1. **Restart MySQL Service:**
   ```powershell
   # Check if MySQL is running
   Get-Service -Name MySQL*
   
   # Restart MySQL (adjust service name as needed)
   Restart-Service MySQL80
   # OR
   Restart-Service MySQL
   ```

2. **Repair Performance Schema:**
   ```sql
   -- Connect to MySQL as root
   mysql -u root -p
   
   -- Recreate performance_schema
   mysql_upgrade --force
   ```

3. **Verify Database Exists:**
   ```sql
   SHOW DATABASES;
   -- Should see 'casaverafurniture' in the list
   ```

### Option 2: Switch to SQLite (Easier for Development)
1. **Update `.env` file:**
   ```env
   DB_CONNECTION=sqlite
   # Comment out or remove MySQL settings:
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=casaverafurniture
   # DB_USERNAME=root
   # DB_PASSWORD=
   ```

2. **Create SQLite database file:**
   ```powershell
   cd backend
   New-Item -Path "database\database.sqlite" -ItemType File -Force
   ```

3. **Run migrations:**
   ```powershell
   php artisan migrate:fresh --seed
   ```

4. **Clear config cache:**
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   ```

### Option 3: Use Different MySQL Database
If the current database is corrupted, create a new one:

```sql
CREATE DATABASE casaverafurniture_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then update `.env`:
```env
DB_DATABASE=casaverafurniture_new
```

Then run migrations:
```powershell
php artisan migrate:fresh --seed
```

## Quick Fix (SQLite - Recommended for Development)

The easiest solution is to switch to SQLite for local development:

1. Update `backend/.env`:
   - Change `DB_CONNECTION=mysql` to `DB_CONNECTION=sqlite`
   - Comment out MySQL settings

2. Create database file:
   ```powershell
   cd backend\database
   New-Item database.sqlite -ItemType File
   ```

3. Run migrations:
   ```powershell
   cd backend
   php artisan migrate:fresh --seed
   ```

4. Clear cache:
   ```powershell
   php artisan config:clear
   ```
