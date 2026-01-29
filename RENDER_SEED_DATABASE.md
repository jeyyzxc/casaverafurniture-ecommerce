<!-- # Seed Database on Render - Complete Guide

## Available Seeders

Your project has these seeders:
- ✅ **RolesAndPermissionsSeeder** - Creates admin roles
- ✅ **AdminSeeder** - Creates default admin accounts
- ✅ **PaymentMethodsSeeder** - Sets up payment methods (GCash, Bank Transfer, COD)
- ✅ **ShippingZonesSeeder** - Creates shipping zones
- ✅ **CategoriesSeeder** - Creates product categories
- ✅ **SiteSettingsSeeder** - Sets up site settings
- ✅ **ProductsSeeder** - Creates sample furniture products

## Method 1: Using Render Shell (Recommended)

### Step 1: Access Render Shell

1. Go to **Render Dashboard**
2. Click on your **backend service**
3. Look for **"Shell"** or **"Console"** tab
4. Click to open a terminal

### Step 2: Run Seeders

Once in the shell, run:

```bash
php artisan db:seed --force
```

The `--force` flag is required in production to prevent confirmation prompts.

### Step 3: Verify

After seeding, verify data was created:

```bash
php artisan tinker
```

Then in tinker:
```php
\App\Models\Product::count();
\App\Models\Category::count();
\App\Models\Admin::count();
```

Type `exit` to leave tinker.

## Method 2: Add to Pre-Deploy Command (Automatic)

If you want seeders to run automatically on every deploy:

1. Go to your **backend service** → **"Settings"**
2. Find **"Pre-Deploy Command"**
3. Update it to:
   ```bash
   php artisan migrate --force && php artisan db:seed --force
   ```
4. Save and trigger a manual deploy

**Note:** This will re-seed on every deploy, which might duplicate data. Use with caution.

## Method 3: One-Time Seed via Pre-Deploy (Recommended for First Time)

For a one-time seed:

1. Go to backend service → **"Settings"**
2. Find **"Pre-Deploy Command"**
3. Temporarily set it to:
   ```bash
   php artisan migrate --force && php artisan db:seed --force
   ```
4. Trigger **"Manual Deploy"**
5. After seeding completes, remove `&& php artisan db:seed --force` from Pre-Deploy Command
6. This way it only runs once

## What Gets Created

### Admin Accounts
- **Email:** `admin@casavera.com`
- **Password:** `password`
- **Role:** Super Admin

- **Email:** `test.admin@casavera.com`
- **Password:** `Test@123!`
- **Role:** Admin

### Data Created
- ✅ Roles and permissions
- ✅ Payment methods (GCash, Bank Transfer, COD)
- ✅ Shipping zones
- ✅ Product categories (Living Room, Bedroom, etc.)
- ✅ Site settings
- ✅ Sample furniture products

## Verify Seeding Worked

After seeding:

1. **Check Products:**
   - Go to your frontend
   - Products should now appear
   - Or check API: `https://your-backend-url.onrender.com/api/products`

2. **Check Admin Login:**
   - Go to admin panel
   - Login with: `admin@casavera.com` / `password`

3. **Check Categories:**
   - API: `https://your-backend-url.onrender.com/api/categories`

## Troubleshooting

### Error: "Class not found"
- Make sure you're in the correct directory
- Run: `php artisan db:seed --force` from the backend root

### Error: "Foreign key constraint"
- Seeders run in order (DatabaseSeeder handles this)
- Make sure migrations ran first

### Data Already Exists
- If you run seeders multiple times, you might get duplicate data
- To reset: `php artisan migrate:fresh --seed --force` (⚠️ This deletes all data!)

### Can't Access Shell
- Some Render plans don't have Shell access
- Use Method 2 or 3 instead (Pre-Deploy Command)

## Quick Command Reference

```bash
# Seed all data
php artisan db:seed --force

# Seed specific seeder
php artisan db:seed --class=ProductsSeeder --force

# Reset and seed (deletes all data first!)
php artisan migrate:fresh --seed --force

# Check data counts
php artisan tinker
# Then: \App\Models\Product::count();
```

---

**Recommended:** Use Method 1 (Render Shell) for the first seed, then remove it from Pre-Deploy Command to avoid re-seeding on every deploy. -->
