# 🚀 Quick Reference - Section-Based Access Control

## One-Minute Setup

```
1. Visit: https://localhost/init_section_professors.php
   → Creates database table ✓

2. Add students with sections:
   - Student A: section = "Section A"
   - Student B: section = "Section B"

3. Admin → Professor Assignments:
   - Select "Section A" + "Professor 1" → Assign
   - Select "Section B" + "Professor 2" → Assign

4. Login as Professor 1:
   → Only sees Student A and Section A data ✓
```

## What Gets Filtered

| View | Admin Sees | Prof (Section A) | Prof (No Section) |
|------|-----------|-----------------|-------------------|
| **Users** | All users | Section A only | All users |
| **Teams** | All teams | Section A teams | All teams |
| **Schedules** | All | Section A | All |
| **Evaluations** | All | Section A | All |

## Database Query

```sql
-- Check who's assigned to which section
SELECT section, CONCAT(u.first_name, ' ', u.last_name) as professor
FROM section_professors sp
JOIN users u ON sp.professor_id = u.id;
```

## File Locations

| File | Purpose |
|------|---------|
| `init_section_professors.php` | Create table |
| `section_access.php` | Helper functions |
| `get_table.php` | Dashboard filtering |
| `professor_assignments.php` | API |

## Code Example

```php
// In any dashboard view:
require_once 'dashboard/includes/section_access.php';

$section = getProfessorSection($pdo, $_SESSION['id']);
if ($section) {
    echo "You can only see $section students";
}
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Prof sees all data | Check if section_professors table exists |
| Wrong students shown | Verify student section = assignment section |
| SQL errors | Check error log: `/opt/lampp/htdocs/dashboard/includes/php_errors.log` |

## Key Points

✅ Professors (usertype=2) with section assignment → Filtered data  
✅ Professors without section → See everything (backwards compatible)  
✅ Admins (usertype=0) → Always see everything  
✅ Students (usertype=1) → Not affected  

---

**That's it! You're done! 🎉**
