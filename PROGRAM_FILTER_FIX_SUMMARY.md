# Program Filter Fix - Database Schema Correction

## Problem

**Error:**
```
Fatal error: Uncaught PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'icei_38697196_coecsathesis.sections' doesn't exist in 
/opt/lampp/htdocs/assets/includes/program_filter.php:261
```

**Root Cause:** The `program_filter.php` file was attempting to join with a non-existent `sections` table. The database schema uses a different structure for faculty-to-program relationships.

---

## Database Schema - Actual Structure

### Faculty Program Assignment
Faculty members are assigned to programs through the `users` table:
- **Column:** `users.program` (VARCHAR)
- **Format:** Program name with optional specialization (e.g., "Computer Science - AI")
- **Purpose:** Identifies which program a faculty member teaches

### Faculty Section Assignment (Alternative)
For section-based filtering (if needed):
- **Table:** `section_professors`
- **Column:** `section` (VARCHAR or INT)
- **Purpose:** Stores section assignments for professors
- **Note:** This is different from a `sections` table - it's a mapping table

### No "sections" Table
The system does NOT have a separate `sections` table with relationships. Faculty filtering uses the `users.program` field directly.

---

## Changes Made

### File: `/opt/lampp/htdocs/assets/includes/program_filter.php`

#### 1. Fixed `getVisibleProgramsFilter()` Function
**Before:**
```php
// Tried to join non-existent tables
JOIN sections s ON s.program_id = p.id
JOIN section_professors sp ON sp.section_id = s.id
```

**After:**
```php
// Uses users.program field directly
SELECT DISTINCT program FROM users
WHERE id = :user_id AND usertype = 2
```

#### 2. Fixed `getVisiblePrograms()` Function
**Before:**
```php
// Faculty: Get assigned section programs
JOIN sections s ON s.program_id = p.id
JOIN section_professors sp ON sp.section_id = s.id
WHERE sp.professor_id = :user_id
```

**After:**
```php
// Faculty: Get their assigned program only
JOIN users u ON CONCAT(p.name, ...) = u.program
WHERE u.id = :user_id AND u.usertype = 2
LIMIT 1
```

#### 3. Fixed `getVisibleUsers()` Function
**Before:**
```php
// Faculty: Get assigned section users
JOIN sections s ON s.id = u.section_id
JOIN section_professors sp ON sp.section_id = s.id
WHERE sp.professor_id = :user_id
```

**After:**
```php
// Faculty: Get all users from same program
SELECT program FROM users WHERE id = :user_id
SELECT * FROM users WHERE program = :program
```

#### 4. Fixed `getVisibleTeams()` Function
**Before:**
```php
// Faculty: Get assigned section teams
JOIN programs p ON t.program = p.id  // Wrong: t.program is VARCHAR, not ID
JOIN sections s ON s.program_id = p.id
JOIN section_professors sp ON sp.section_id = s.id
WHERE sp.professor_id = :user_id
```

**After:**
```php
// Faculty: Get teams from same program
SELECT program FROM users WHERE id = :user_id
SELECT * FROM teams WHERE t.program = :program
```

---

## Access Control Rules (Now Corrected)

### Super Admin (usertype=0, id=0)
- ✅ Access: ALL programs, users, and teams
- Filter: None (1=1)

### Program Chair (usertype=0, id≠0)
- ✅ Access: Only programs from their college
- Filter: `programs.college = :admin_college`
- College determined by: `get_user_college()` function

### Faculty (usertype=2)
- ✅ Access: Only their own program + related users/teams
- Filter: `users.program = :faculty_program`
- Program determined by: `users.program` field

---

## Validation Results

✅ **PHP Syntax Check:**
```
No syntax errors detected in /opt/lampp/htdocs/assets/includes/program_filter.php
No syntax errors detected in /opt/lampp/htdocs/dashboard/index.php
```

---

## Files Modified

| File | Lines Changed | Purpose |
|------|---------------|---------|
| `/opt/lampp/htdocs/assets/includes/program_filter.php` | ~60 lines | Fixed all 4 filtering functions to use actual database schema |

---

## Testing Checklist

- [ ] Dashboard loads without PDOException error
- [ ] Admin sees all users/teams in dashboard
- [ ] Program Chair sees only college-level data
- [ ] Faculty sees only their program's users/teams
- [ ] User data properly filtered by program assignment

---

## How to Test

1. **Access Dashboard:**
   - Navigate to: `https://localhost/dashboard/index.php`

2. **Test as Different User Types:**
   - **Admin (id=0):** Should see all programs, users, teams
   - **Program Chair (id≠0):** Should see only college-level data
   - **Faculty (usertype=2):** Should see only their program data

3. **Verify in Browser Console:**
   - Open Developer Tools (F12)
   - Check for any JavaScript errors
   - Check Network tab for successful API calls

4. **Check Database:**
   ```sql
   -- Verify users.program field is populated for faculty
   SELECT id, first_name, last_name, usertype, program 
   FROM users 
   WHERE usertype = 2;
   ```

---

## Related Documentation

- **Previous:** `PROGRAM_FILTER_IMPLEMENTATION.md` - Original design
- **Previous:** `PROGRAM_FILTER_IMPLEMENTATION_COMPLETE.md` - Initial documentation
- **Current:** `PROGRAM_FILTER_FIX_SUMMARY.md` - This file

---

## Summary

The error was caused by incorrect SQL JOIN assumptions. The fix aligns `program_filter.php` with the actual database schema where:
- **Faculty programs** are stored in the `users.program` field
- **No `sections` table** exists (only `section_professors` for section assignments)
- **Filtering logic** now correctly uses field-based relationships instead of table relationships

All PHP code is syntax-valid and ready for production testing.
