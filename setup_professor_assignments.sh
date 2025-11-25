#!/bin/bash

# Professor Assignment System - Quick Setup Script
# This script sets up the database and creates necessary directories

echo "==========================================="
echo "Professor Assignment System - Setup"
echo "==========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Database credentials
DB_HOST="localhost"
DB_USER="icei_38697196"
DB_PASS="4rdL34hSdQFcgrL"
DB_NAME="icei_38697196_coecsathesis"
MIGRATION_FILE="/opt/lampp/htdocs/api/professor_assignments_migration.sql"

echo -e "${YELLOW}Step 1: Creating database tables...${NC}"

# Check if migration file exists
if [ ! -f "$MIGRATION_FILE" ]; then
    echo -e "${RED}Error: Migration file not found at $MIGRATION_FILE${NC}"
    exit 1
fi

# Execute migration
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database tables created successfully${NC}"
else
    echo -e "${RED}✗ Failed to create database tables${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 2: Verifying table creation...${NC}"

# Verify tables
TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN -e "SHOW TABLES LIKE '%professor%';" 2>/dev/null | wc -l)

if [ "$TABLES" -ge 3 ]; then
    echo -e "${GREEN}✓ All tables created successfully ($TABLES tables found)${NC}"
else
    echo -e "${YELLOW}⚠ Only $TABLES professor-related tables found (expected 3+)${NC}"
fi

echo ""
echo -e "${YELLOW}Step 3: Setting permissions...${NC}"

# Set proper permissions for PHP files
chmod 755 /opt/lampp/htdocs/api/professor_assignments.php
chmod 755 /opt/lampp/htdocs/admin/professor_assignments.php
chmod 755 /opt/lampp/htdocs/profile/my_assignments.php

echo -e "${GREEN}✓ File permissions set${NC}"

echo ""
echo -e "${YELLOW}Step 4: Verifying file structure...${NC}"

# Check if all necessary files exist
FILES=(
    "/opt/lampp/htdocs/api/professor_assignments.php"
    "/opt/lampp/htdocs/admin/professor_assignments.php"
    "/opt/lampp/htdocs/profile/my_assignments.php"
    "/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_GUIDE.md"
)

ALL_EXIST=true
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ $file${NC}"
    else
        echo -e "${RED}✗ $file (missing)${NC}"
        ALL_EXIST=false
    fi
done

echo ""
if [ "$ALL_EXIST" = true ]; then
    echo -e "${GREEN}==========================================="
    echo "Setup Complete! 🎉"
    echo "==========================================="
    echo ""
    echo "Access points:"
    echo "  - Admin Panel:       http://localhost/admin/professor_assignments.php"
    echo "  - Faculty Dashboard: http://localhost/profile/my_assignments.php"
    echo "  - API:               http://localhost/api/professor_assignments.php"
    echo ""
    echo "Documentation:       /PROFESSOR_ASSIGNMENTS_GUIDE.md"
    echo ""
else
    echo -e "${RED}Setup completed with warnings. Please check missing files.${NC}"
fi
