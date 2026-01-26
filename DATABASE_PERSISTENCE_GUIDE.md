# Database Persistence & Backup Guide

## ✅ Data Persistence on Render

**Good News:** Your data is **automatically persisted** on Render! Even if PostgreSQL is stopped or restarted, all your data remains safe and accessible.

### How Render Ensures Data Persistence

1. **Automatic Data Storage**
   - Render PostgreSQL databases use persistent storage
   - Data is stored on durable disk volumes
   - Data survives service restarts, updates, and deployments

2. **Automatic Backups**
   - **Starter Plan**: 7 days of automatic backups
   - **Standard Plan**: 30 days of automatic backups
   - Backups are taken automatically every day
   - No manual intervention required

3. **High Availability**
   - Database service is managed by Render
   - Automatic failover and recovery
   - 99.95% uptime SLA on paid plans

## 🔄 What Happens When PostgreSQL Stops?

### Scenario 1: Service Restart
- ✅ **Data is preserved** - All data remains intact
- ✅ **Automatic recovery** - Service restarts automatically
- ✅ **No data loss** - Transactions are safely stored

### Scenario 2: Planned Maintenance
- ✅ **Zero downtime** - Render handles maintenance seamlessly
- ✅ **Data protected** - All data is backed up before maintenance
- ✅ **Automatic restoration** - Service resumes with all data

### Scenario 3: Unexpected Failure
- ✅ **Automatic recovery** - Render restores from latest backup
- ✅ **Data integrity** - Transactions are preserved
- ✅ **Minimal downtime** - Usually less than 5 minutes

## 📦 Backup Information

### Automatic Backups

**Backup Schedule:**
- Daily automatic backups
- Backups run during low-traffic hours
- No performance impact during backup

**Backup Retention:**
- **Starter Plan**: 7 days
- **Standard Plan**: 30 days
- **Pro Plan**: 30 days (with point-in-time recovery)

**Backup Location:**
- Stored securely in Render's infrastructure
- Encrypted at rest
- Geographically distributed

### Accessing Backups

1. **Via Render Dashboard:**
   - Go to your database service
   - Click "Backups" tab
   - View and download backups

2. **Restore from Backup:**
   - Select a backup from the list
   - Click "Restore"
   - Database will be restored to that point in time

## 🔧 Manual Backup Options

### Option 1: Using Render Dashboard (Easiest)

1. Go to Render Dashboard
2. Select your database service (`casaverafurniture-db`)
3. Click "Backups" tab
4. Click "Create Backup" for on-demand backup
5. Download backup file if needed

### Option 2: Using pg_dump (Command Line)

If you need to create a manual backup:

```bash
# Connect to your Render database
pg_dump -h <database-host> -U <database-user> -d casaverafurniture > backup.sql

# Or with password prompt
PGPASSWORD=<password> pg_dump -h <database-host> -U <database-user> -d casaverafurniture > backup.sql
```

**Get connection details:**
- Go to Render Dashboard → Database service
- Click "Info" tab
- Copy connection string

### Option 3: Using Laravel Artisan

Create a backup command in your Laravel app:

```php
// In app/Console/Commands/BackupDatabase.php
php artisan db:backup
```

## 🔄 Restore from Backup

### Via Render Dashboard

1. Go to database service → "Backups" tab
2. Select the backup you want to restore
3. Click "Restore"
4. Confirm restoration
5. Database will be restored (this may take a few minutes)

### Via Command Line

```bash
# Restore from SQL file
psql -h <database-host> -U <database-user> -d casaverafurniture < backup.sql
```

## 🛡️ Data Protection Best Practices

### 1. Regular Backup Verification

**Monthly:**
- [ ] Verify backups are being created
- [ ] Test restore process (on a test database)
- [ ] Check backup file sizes (should be consistent)

### 2. Monitor Database Health

**Weekly:**
- [ ] Check database service status
- [ ] Review error logs
- [ ] Monitor disk usage

### 3. Document Critical Data

**Keep Records Of:**
- Important customer data
- Order numbers and references
- Admin account credentials
- Configuration settings

### 4. Upgrade Plan for Better Backup Retention

**Consider upgrading if:**
- You need more than 7 days of backups
- You need point-in-time recovery
- You need faster restore times

## 📊 Backup Status Monitoring

### Check Backup Status

1. **Render Dashboard:**
   - Database service → "Backups" tab
   - See last backup time
   - View backup history

2. **Email Notifications:**
   - Enable email alerts in Render
   - Get notified of backup failures
   - Get notified of restore completions

## 🚨 Disaster Recovery Plan

### If Data is Lost or Corrupted

1. **Immediate Actions:**
   - Stop accepting new orders (if applicable)
   - Document what data is missing
   - Contact Render support if needed

2. **Restore Process:**
   - Go to Render Dashboard
   - Select database service
   - Go to "Backups" tab
   - Select most recent good backup
   - Click "Restore"
   - Wait for restoration (5-15 minutes)

3. **Post-Restore:**
   - Verify data integrity
   - Test application functionality
   - Re-enter any data created after backup
   - Document the incident

## 💾 Local Backup Strategy (Optional)

For extra safety, you can create local backups:

### Automated Local Backups Script

Create a cron job or scheduled task:

```bash
#!/bin/bash
# backup-database.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
DB_HOST="your-database-host"
DB_USER="your-database-user"
DB_NAME="casaverafurniture"

# Create backup
pg_dump -h $DB_HOST -U $DB_USER -d $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

### Schedule with Cron

```bash
# Run daily at 2 AM
0 2 * * * /path/to/backup-database.sh
```

## 📈 Backup Monitoring

### Set Up Alerts

1. **Render Dashboard:**
   - Go to database service
   - Enable email notifications
   - Set up alerts for backup failures

2. **Custom Monitoring:**
   - Check backup status via API
   - Set up monitoring dashboard
   - Alert on backup age > 24 hours

## ✅ Verification Checklist

Use this checklist to ensure your backups are working:

- [ ] Automatic backups are enabled (default on Render)
- [ ] Last backup was within 24 hours
- [ ] Backup file size is reasonable (not 0 bytes)
- [ ] You know how to restore from backup
- [ ] You've tested restore process (on test database)
- [ ] Backup retention period meets your needs
- [ ] You have access to backup files
- [ ] Team knows backup/restore procedures

## 🔗 Additional Resources

- **Render Database Docs**: https://render.com/docs/databases
- **PostgreSQL Backup Docs**: https://www.postgresql.org/docs/current/backup.html
- **Render Support**: support@render.com

## 📝 Important Notes

1. **Data is Always Persistent**: Render databases use persistent storage. Your data won't be lost if the service restarts.

2. **Automatic Backups**: Backups happen automatically. You don't need to do anything.

3. **Backup Retention**: Starter plan keeps 7 days of backups. Consider upgrading if you need longer retention.

4. **Restore Time**: Restoring from backup typically takes 5-15 minutes depending on database size.

5. **No Downtime**: Backups don't cause downtime. They run in the background.

---

## 🎯 Summary

**Your data is safe!** Render automatically:
- ✅ Persists all data to durable storage
- ✅ Creates daily backups
- ✅ Retains backups for 7+ days
- ✅ Provides easy restore options
- ✅ Handles failures automatically

**You don't need to worry about data loss** - Render handles it all for you!
