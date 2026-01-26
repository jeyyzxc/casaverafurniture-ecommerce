# Database Setup Instructions

## Issue Found

The MySQL authentication is now working (password is correct), but the database `casaverafurniture_db` doesn't exist yet.

## Solution: Create the Database

You have two options:

### Option 1: Using MySQL Workbench (Easiest)

1. Open MySQL Workbench
2. Connect to your MySQL server (you're already connected)
3. In the SQL Editor, run:
   ```sql
   CREATE DATABASE IF NOT EXISTS casaverafurniture_db 
   CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;
   ```
4. Click the Execute button (⚡)
5. Verify it was created:
   ```sql
   SHOW DATABASES LIKE 'casaverafurniture_db';
   ```

### Option 2: Using Command Line

```powershell
# Connect to MySQL
mysql -u root -p
# Enter password: 200519_Jerome

# Create database
CREATE DATABASE IF NOT EXISTS casaverafurniture_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

# Exit MySQL
EXIT;
```

### Option 3: Check if Database Exists with Different Name

If you already have the database with a different name, you can either:

1. **Rename it** (if it has the same structure):
   ```sql
   RENAME DATABASE old_name TO casaverafurniture_db;
   ```

2. **Or update `.env`** to use the existing database name:
   ```env
   DB_DATABASE=your_existing_database_name
   ```

## After Creating the Database

Once the database is created, run migrations:

```powershell
cd backend
php artisan migrate
```

If you already have tables in the database, you might need to:

```powershell
# Check migration status
php artisan migrate:status

# Or if tables exist but migrations table doesn't:
php artisan migrate:install
```

## Verify Database Connection

Test the connection:

```powershell
php artisan migrate:status
```

Should show your migrations without errors.

## Next Steps

1. Create the database using one of the methods above
2. Run migrations: `php artisan migrate`
3. Seed data (if needed): `php artisan db:seed`
4. Test API endpoints
