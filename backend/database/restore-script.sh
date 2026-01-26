#!/bin/bash
# Database Restore Script for Render PostgreSQL
# Usage: ./restore-script.sh <backup-file.sql.gz>

# Configuration
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT}"
DB_NAME="${DB_DATABASE}"
DB_USER="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if backup file is provided
if [ -z "$1" ]; then
    echo -e "${RED}Error: Backup file not specified${NC}"
    echo "Usage: ./restore-script.sh <backup-file.sql.gz>"
    exit 1
fi

BACKUP_FILE="$1"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}Error: Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

# Check if required environment variables are set
if [ -z "$DB_HOST" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo -e "${RED}Error: Database connection variables not set${NC}"
    echo "Please set: DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    exit 1
fi

# Warning
echo -e "${RED}WARNING: This will overwrite the current database!${NC}"
echo -e "Database: $DB_NAME"
echo -e "Host: $DB_HOST"
echo -e "Backup file: $BACKUP_FILE"
echo ""
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo -e "${YELLOW}Restore cancelled${NC}"
    exit 0
fi

echo -e "${YELLOW}Starting database restore...${NC}"

# Decompress if needed
if [[ "$BACKUP_FILE" == *.gz ]]; then
    echo -e "${YELLOW}Decompressing backup...${NC}"
    gunzip -c "$BACKUP_FILE" | \
    export PGPASSWORD="$DB_PASSWORD" && \
    psql -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USER" -d "$DB_NAME" 2>&1
else
    export PGPASSWORD="$DB_PASSWORD"
    psql -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USER" -d "$DB_NAME" < "$BACKUP_FILE" 2>&1
fi

RESTORE_EXIT_CODE=$?
unset PGPASSWORD

# Check if restore was successful
if [ $RESTORE_EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✓ Database restored successfully!${NC}"
    exit 0
else
    echo -e "${RED}✗ Restore failed!${NC}"
    echo "Check the error messages above"
    exit 1
fi
