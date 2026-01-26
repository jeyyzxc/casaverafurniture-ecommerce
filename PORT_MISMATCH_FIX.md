# Port Mismatch Fix

## Issue Found

Your PostgreSQL connection dialog shows **port 5433**, but your backend `.env` file was configured for **port 5432**. This mismatch would cause connection failures.

## Fix Applied

✅ Updated `backend/.env` to use port **5433** to match your PostgreSQL configuration.

## Next Steps

1. **Clear Laravel config cache:**
   ```powershell
   cd backend
   php artisan config:clear
   ```

2. **Test the connection:**
   ```powershell
   php artisan migrate:status
   ```

3. **If PostgreSQL is running, the connection should now work!**

## Verify Your PostgreSQL Setup

Based on your connection dialog:
- ✅ Host: `localhost`
- ✅ Port: `5433` (now matches backend)
- ✅ Database: `casaverafurniture`
- ✅ Username: `postgres`
- ⚠️ Password: Make sure "Save password?" is enabled and password is saved, or ensure your backend `.env` has the correct password

## Important Notes

1. **Password:** If your PostgreSQL `postgres` user requires a password, make sure:
   - The password is saved in your database tool (enable "Save password?")
   - The password in `backend/.env` matches: `DB_PASSWORD=200519`

2. **Database Name:** The red underline under "casaverafurniture" in your dialog might just be a spell-checker warning. As long as the database exists, it should be fine.

3. **Start PostgreSQL:** Make sure the PostgreSQL service is running before testing!

## Testing

After updating the port, refresh your frontend and check if the 500 errors are resolved.
