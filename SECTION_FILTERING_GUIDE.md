# Section-Based Access Control - Complete Guide

## Overview
Professors assigned to a section can only see students and data from their assigned section.

## Database Setup

### 1. Create the section_professors table
Visit: `https://localhost/init_section_professors.php`

This creates the table with the following schema:
```sql
CREATE TABLE section_professors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section VARCHAR(255) NOT NULL,
  professor_id INT NOT NULL,
  status VARCHAR(50) DEFAULT 'active',
  assigned_by INT DEFAULT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## How Section Assignment Works

### Step 1: Set Up Test Data
1. Create students in each section via User Management:
   - Add Student A: section = "Section A"
   - Add Student B: section = "Section B"

2. Create teams with these students

3. Create faculty members (usertype = 2):
   - Add Professor 1: usertype = 2
   - Add Professor 2: usertype = 2

### Step 2: Assign Professors to Sections
1. Login as Admin
2. Go to **User Management → Professor Assignments**
3. Select **Section A** from dropdown
4. Select **Professor 1** from dropdown
5. Click **Assign**
6. Repeat for Professor 2 → Section B

### Step 3: Test Section Filtering
1. **Login as Professor 1**
   - Go to **Users** tab → Should only see **Student A**
   - Go to **Teams** tab → Should only see teams with **Student A**
   - Go to **Defense Schedules** → Should only see **Student A's** schedules

2. **Login as Professor 2**
   - Go to **Users** tab → Should only see **Student B**
   - Go to **Teams** tab → Should only see teams with **Student B**
   - Go to **Defense Schedules** → Should only see **Student B's** schedules

3. **Login as Admin**
   - Should see all students, teams, and schedules

## What Gets Filtered

### Users Table
- **Admin**: Sees all users in their college
- **Professor with section**: Sees only students from their assigned section
- **Professor without section**: Sees all students (backwards compatible)

### Teams Table
- **Admin**: Sees all teams in their college
- **Professor with section**: Sees only teams that have members from their assigned section
- **Professor without section**: Sees all teams

### Defense Schedules
- **Admin**: Sees all schedules
- **Professor with section**: Sees only schedules for students from their section
- **Professor without section**: Sees all schedules

### Evaluations
- **Admin**: Sees all evaluations
- **Professor with section**: Sees only evaluations for students from their section
- **Professor without section**: Sees all evaluations

## Code Components

### Section Access Helper Functions
File: `/opt/lampp/htdocs/dashboard/includes/section_access.php`

Key functions:
- `getProfessorSection($pdo, $professor_id)` - Returns assigned section string
- `canProfessorViewStudent($pdo, $professor_id, $student_id)` - Permission check
- `canProfessorViewTeam($pdo, $professor_id, $team_id)` - Permission check

### Dashboard Data API
File: `/opt/lampp/htdocs/dashboard/includes/tabs/get_table.php`

When fetching data, checks:
1. Is user type 2 (faculty)?
2. Do they have a section assignment?
3. If yes, add `AND users.section = :assigned_section` to WHERE clause

### Professor Assignments API
File: `/opt/lampp/htdocs/api/professor_assignments.php`

Handles:
- `list_sections` - Lists all sections from users table
- `list_professors` - Lists all faculty (usertype=2)
- `assign_professor_to_section` - Creates assignment
- `delete_section_assignment` - Removes assignment
- `list_section_professors` - Shows current assignments

## Troubleshooting

### Professor sees all data instead of filtered
**Solution**: 
1. Check if section_professors table exists: `https://localhost/test_scheduler.php`
2. Check if professor has a section assignment
3. Verify professor's usertype = 2

### Filter not working for specific table
**Solution**:
1. Check `get_table.php` includes `section_access.php`
2. Verify section name matches exactly (case-sensitive)
3. Check error log for SQL errors

### Students showing wrong section
**Solution**:
1. Verify students have section value in users.section column
2. Check section names are consistent across tables

## SQL Queries for Verification

```sql
-- Check section assignments
SELECT sp.id, sp.section, u.first_name, u.last_name 
FROM section_professors sp 
JOIN users u ON sp.professor_id = u.id;

-- Check students in Section A
SELECT id, first_name, last_name, section 
FROM users 
WHERE section = 'Section A' AND usertype = 1;

-- Check which teams have Section A students
SELECT DISTINCT t.id, t.name 
FROM teams t 
JOIN team_members tm ON t.id = tm.team_id 
JOIN users u ON tm.user_id = u.id 
WHERE u.section = 'Section A';
```

## Advanced: Add More Filters

To add section filtering to another table/view:

1. **In get_table.php:**
```php
// At the appropriate case block:
if ($currentUsertype === 2) {
    $assignedSection = getProfessorSection($pdo, $userId);
    if ($assignedSection) {
        $collegeRestrictionClause .= " AND your_student_table.section = :assigned_section";
        $params[':assigned_section'] = $assignedSection;
    }
}
```

2. **In custom API files:**
```php
require_once '../dashboard/includes/section_access.php';

// Then use:
$section = getProfessorSection($pdo, $userId);
if ($section) {
    $query .= " WHERE users.section = ?";
    $stmt->execute([$section]);
}
```

## Support

For issues or questions, check:
- `/opt/lampp/htdocs/dashboard/includes/section_access.php` - Helper functions documentation
- PHP error log - Check for SQL errors
- Browser console - Check for AJAX errors
