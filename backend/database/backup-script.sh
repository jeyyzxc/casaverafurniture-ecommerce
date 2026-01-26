#!/bin/bash
# Database Backup Script for Render PostgreSQL
# This script can be run manually or scheduled to create local backups

# Configuration
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT}"
DB_NAME="${DB_DATABASE}"
DB_USER="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"

# Backup settings
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup_$DATE.sql"
RETENTION_DAYS=30

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting database backup...${NC}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Check if required environment variables are set
if [ -z "$DB_HOST" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo -e "${RED}Error: Database connection variables not set${NC}"
    echo "Please set: DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    exit 1
fi

# Create backup using pg_dump
echo -e "${YELLOW}Creating backup: $BACKUP_FILE${NC}"

export PGPASSWORD="$DB_PASSWORD"
pg_dump -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USER" -d "$DB_NAME" \
    --no-owner \
    --no-privileges \
    --clean \
    --if-exists \
    > "$BACKUP_FILE" 2>&1

BACKUP_EXIT_CODE=$?
unset PGPASSWORD

# Check if backup was successful
if [ $BACKUP_EXIT_CODE -eq 0 ]; then
    # Compress backup
    echo -e "${YELLOW}Compressing backup...${NC}"
    gzip "$BACKUP_FILE"
    BACKUP_FILE="${BACKUP_FILE}.gz"
    
    # Get file size
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    
    echo -e "${GREEN}✓ Backup created successfully!${NC}"
    echo -e "  File: $BACKUP_FILE"
    echo -e "  Size: $FILE_SIZE"
    
    # Clean up old backups
    echo -e "${YELLOW}Cleaning up old backups (keeping last $RETENTION_DAYS days)...${NC}"
    find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete
    
    echo -e "${GREEN}✓ Backup process completed!${NC}"
    exit 0
else
    echo -e "${RED}✗ Backup failed!${NC}"
    echo "Check the error messages above"
    rm -f "$BACKUP_FILE"
    exit 1
fi
