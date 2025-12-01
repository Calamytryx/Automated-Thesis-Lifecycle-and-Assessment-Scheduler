# 🔐 Permission Refinement - Quick Reference

## The Three Permission Rules (Carefully Segregated)

### Rule 1: Student Selection for Team Members
**Filtered by**: Section Assignment  
**Scope**: Professor's assigned section(s) ONLY  
**Function**: `getAvailableStudentsForProfessor()`  
**API**: `get_available_users.php?type=students`

✅ Professor assigned to Section A → Can select Section A students  
✅ Professor assigned to Sections A & B → Can select from A or B  
❌ Professor assigned to Section A → Cannot select Section B students  

---

### Rule 2: Adviser Selection for Teams
**Filtered by**: College (NOT Section)  
**Scope**: Professors from same college as team's program  
**Function**: `getAvailableAdvisersForTeam()`  
**API**: `get_available_users.php?type=advisers&team_program=PROGRAM_NAME`

✅ Team in College A, Section 1 → Adviser from College A, Section 1 ✓  
✅ Team in College A, Section 1 → Adviser from College A, Section 3 ✓  
❌ Team in College A, Section 1 → Adviser from College B ✗  

---

### Rule 3: Team Creation Validation
**Validated against**: Professor's assigned section(s)  
**Function**: `canProfessorCreateTeam()`  
**Enforcement**: `add_items.php` and `bulk_add_teams.php`

✅ Prof A (Section A) creates team with Section A students → Allowed  
✅ Prof B (Sections B, C) creates team with Section C students → Allowed  
❌ Prof A (Section A) creates team with Section B students → Blocked  

---

## Implementation at a Glance

### Database
```sql
-- One professor can have MULTIPLE section assignments
SELECT * FROM section_professors;
-- Example:
-- professor_id | section
-- 5            | Section A
-- 5            | Section B
-- 5            | Section C
```

### Functions
```php
// Get all sections for a professor (returns array)
$sections = getProfessorSections($pdo, $professor_id);  // ['Section A', 'Section B']

// Validate team creation with members
$result = canProfessorCreateTeam($pdo, $professor_id, $memberIds);
if (!$result['canCreate']) {
    // Return error: $result['message']
}

// Get students for dropdown
$students = getAvailableStudentsForProfessor($pdo, $professor_id);

// Get advisers for dropdown
$advisers = getAvailableAdvisersForTeam($pdo, $team_program);
```

### API Calls
```javascript
// For team members (section-filtered)
fetch('includes/get_available_users.php?type=students')
  .then(r => r.json())
  .then(data => {
    // data.data contains [{ id, first_name, last_name, section }, ...]
  });

// For advisers (college-filtered)
fetch('includes/get_available_users.php?type=advisers&team_program=Program+Name')
  .then(r => r.json())
  .then(data => {
    // data.data contains [{ id, first_name, last_name }, ...]
  });
```

---

## Key Differences (Segregation)

| Aspect | Team Members | Advisers |
|--------|--------------|----------|
| **Filter Type** | Section | College |
| **Multiple Allowed** | ✅ Yes | ✅ Yes |
| **Scope** | Only assigned section(s) | All in same college |
| **Includes Self** | ✅ Yes (if in section) | ✅ Yes |
| **Cross-Section** | ❌ No | ✅ Yes (same college) |
| **Cross-College** | ❌ No | ❌ No |

---

## Testing Quick Checks

### ✓ Can Professor See Students?
If in dropdown for adding members → ✅ Success

### ✓ Can Professor Create Team with Them?
If no error on creation → ✅ Success

### ✓ Can Professor Become Adviser?
If button appears on team → ✅ Success

### ✓ Can Professor See All Teams?
If teams table shows teams from other sections → ✅ Success

### ✓ Can Different Professor See Students from Different Section?
If Prof A (Section A) cannot see Section B students in dropdown → ✅ Success

---

## Troubleshooting

### Problem: Students from other sections appearing in dropdown
**Check**: `get_available_users.php` is called with `type=students`  
**Check**: `getAvailableStudentsForProfessor()` using `getProfessorSections()`  
**Check**: Section data in `section_professors` table

### Problem: Advisers from wrong college appearing
**Check**: `get_available_users.php` is called with `type=advisers&team_program=X`  
**Check**: `team_program` parameter being passed correctly  
**Check**: Program-to-college mapping in `programs` table

### Problem: Professor can create teams with wrong section students
**Check**: `canProfessorCreateTeam()` being called before creation  
**Check**: Permission check in `add_items.php` (line 423-461)  
**Check**: Same check in `bulk_add_teams.php` (lines 60-78)

---

## Files to Monitor

- `/opt/lampp/htdocs/dashboard/includes/section_access.php` → Permission logic
- `/opt/lampp/htdocs/dashboard/includes/get_available_users.php` → API endpoint
- `/opt/lampp/htdocs/dashboard/includes/add_items.php` → Creation validation
- `/opt/lampp/htdocs/dashboard/includes/bulk_add_teams.php` → Bulk creation validation
- `/opt/lampp/htdocs/dashboard/app.js.php` → Frontend calls

---

## Error Messages Users Will See

### Team Creation Blocked
```
"You can only create teams with students from your assigned section(s): Section A, Section B"
```

### Adviser Not Found
```
"You can only assign advisers from professors in the same college."
```

---

**Status**: 🟢 Production Ready
