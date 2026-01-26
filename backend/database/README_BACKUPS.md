# Database Backup Scripts

These scripts provide additional backup options beyond Render's automatic backups.

## ⚠️ Important Note

**Render automatically backs up your database daily!** These scripts are optional and provide:
- Manual backup capability
- Local backup storage
- Custom backup scheduling

## Scripts

### 1. `backup-script.sh` - Create Database Backup

Creates a compressed SQL dump of your database.

**Usage:**
```bash
# Set environment variables first
export DB_HOST="your-database-host"
export DB_PORT="5432"
export DB_DATABASE="casaverafurniture"
export DB_USERNAME="your-username"
export DB_PASSWORD="your-password"

# Make script executable
chmod +x backup-script.sh

# Run backup
./backup-script.sh
```

**What it does:**
- Creates SQL dump of database
- Compresses the backup file
- Stores in `./backups/` directory
- Cleans up backups older than 30 days
- Shows backup file size and location

### 2. `restore-script.sh` - Restore Database from Backup

Restores database from a backup file.

**Usage:**
```bash
# Set environment variables first
export DB_HOST="your-database-host"
export DB_PORT="5432"
export DB_DATABASE="casaverafurniture"
export DB_USERNAME="your-username"
export DB_PASSWORD="your-password"

# Make script executable
chmod +x restore-script.sh

# Restore from backup
./restore-script.sh backups/backup_20240126_120000.sql.gz
```

**⚠️ Warning:** This will overwrite your current database!

## Prerequisites

- `pg_dump` and `psql` installed (PostgreSQL client tools)
- Database connection credentials
- Write permissions for backup directory

## Scheduling Backups

### Using Cron (Linux/Mac)

```bash
# Edit crontab
crontab -e

# Add this line to run daily at 2 AM
0 2 * * * cd /path/to/backend/database && ./backup-script.sh
```

### Using Task Scheduler (Windows)

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger (daily at 2 AM)
4. Set action: Run `backup-script.sh`
5. Set working directory to script location

## Backup File Format

Backups are stored as:
```
backups/backup_YYYYMMDD_HHMMSS.sql.gz
```

Example:
```
backups/backup_20240126_143022.sql.gz
```

## Restore Process

1. **Stop your application** (recommended)
2. Run restore script with backup file
3. Wait for restore to complete
4. Verify data integrity
5. Restart your application

## Best Practices

1. **Test Restores**: Periodically test restoring from backups
2. **Store Offsite**: Copy backups to cloud storage (S3, Dropbox, etc.)
3. **Verify Backups**: Check backup file sizes (should not be 0 bytes)
4. **Document**: Keep notes of when backups were created
5. **Monitor**: Set up alerts if backups fail

## Troubleshooting

### Error: pg_dump not found
**Solution:** Install PostgreSQL client tools
```bash
# Ubuntu/Debian
sudo apt-get install postgresql-client

# Mac
brew install postgresql

# Windows
Download from: https://www.postgresql.org/download/windows/
```

### Error: Permission denied
**Solution:** Make scripts executable
```bash
chmod +x backup-script.sh restore-script.sh
```

### Error: Connection refused
**Solution:** Check database credentials and network access
- Verify DB_HOST, DB_PORT are correct
- Check firewall rules
- Verify database is accessible from your location

## Alternative: Use Render Dashboard

**Easier Option:** Use Render's built-in backup system:
1. Go to Render Dashboard
2. Select database service
3. Click "Backups" tab
4. Create, download, or restore backups

This is recommended for most users as it's simpler and doesn't require additional setup.
