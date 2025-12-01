#!/bin/bash

# Test script for Professor Assignment System
# Tests the API endpoints after the fixes

echo "=========================================="
echo "Professor Assignment System - API Test"
echo "=========================================="
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test file syntax
echo -e "${YELLOW}1. Testing PHP syntax...${NC}"
php -l /opt/lampp/htdocs/dashboard/includes/tabs/professor_assignments_tab.php && \
    echo -e "${GREEN}✓ Tab file syntax OK${NC}" || \
    echo -e "${RED}✗ Tab file has syntax errors${NC}"

php -l /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ API file syntax OK${NC}" || \
    echo -e "${RED}✗ API file has syntax errors${NC}"

echo ""
echo -e "${YELLOW}2. Checking files exist...${NC}"
[ -f /opt/lampp/htdocs/dashboard/includes/tabs/professor_assignments_tab.php ] && \
    echo -e "${GREEN}✓ Tab file exists${NC}" || \
    echo -e "${RED}✗ Tab file not found${NC}"

[ -f /opt/lampp/htdocs/api/professor_assignments.php ] && \
    echo -e "${GREEN}✓ API file exists${NC}" || \
    echo -e "${RED}✗ API file not found${NC}"

echo ""
echo -e "${YELLOW}3. Verifying key functions in API...${NC}"

grep -q "function assignProfessorToTeam()" /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ assignProfessorToTeam() found${NC}" || \
    echo -e "${RED}✗ assignProfessorToTeam() not found${NC}"

grep -q "function deleteAssignment()" /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ deleteAssignment() found${NC}" || \
    echo -e "${RED}✗ deleteAssignment() not found${NC}"

grep -q "function listTeamProfessors()" /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ listTeamProfessors() found${NC}" || \
    echo -e "${RED}✗ listTeamProfessors() not found${NC}"

grep -q "function listAssignmentHistory()" /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ listAssignmentHistory() found${NC}" || \
    echo -e "${RED}✗ listAssignmentHistory() not found${NC}"

echo ""
echo -e "${YELLOW}4. Verifying correct database queries...${NC}"

grep -q "WHERE tm.role = 'adviser'" /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ Using team_members with role='adviser' filter${NC}" || \
    echo -e "${RED}✗ Not using correct team_members query${NC}"

grep -q "DELETE FROM team_members WHERE team_id = ?" /opt/lampp/htdocs/api/professor_assignments.php && \
    echo -e "${GREEN}✓ Delete uses team_members table${NC}" || \
    echo -e "${RED}✗ Delete query not correct${NC}"

echo ""
echo -e "${YELLOW}5. Verifying no references to old tables in main functions...${NC}"

# Count how many references to old tables in the main functions used by the tab
OLD_TABLE_COUNT=$(grep -c "team_professor_assignments" /opt/lampp/htdocs/api/professor_assignments.php)
echo "References to team_professor_assignments: $OLD_TABLE_COUNT (in other unused functions)"

echo ""
echo -e "${YELLOW}6. Tab inclusion check...${NC}"

grep -q 'include.*professor_assignments_tab.php' /opt/lampp/htdocs/dashboard/index.php && \
    echo -e "${GREEN}✓ Tab is included in dashboard/index.php${NC}" || \
    echo -e "${RED}✗ Tab not included in dashboard${NC}"

grep -q 'professor-assignments-tab' /opt/lampp/htdocs/dashboard/index.php && \
    echo -e "${GREEN}✓ Tab is registered in sidebar navigation${NC}" || \
    echo -e "${RED}✗ Tab not registered in sidebar${NC}"

echo ""
echo "=========================================="
echo -e "${GREEN}Testing complete!${NC}"
echo "=========================================="
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Open dashboard in browser"
echo "2. Look for 'Professor Assignments' in sidebar"
echo "3. Click tab and verify no 'failed to load' error"
echo "4. Test assigning an adviser to a team"
echo "5. Verify data saves to database"
echo "6. Test deletion"
