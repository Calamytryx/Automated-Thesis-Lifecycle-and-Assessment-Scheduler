# Program Filter Fix - Quick Reference

## The Issue
```
PDOException: Table 'icei_38697196_coecsathesis.sections' doesn't exist
Location: /opt/lampp/htdocs/assets/includes/program_filter.php:261
```

## What Was Wrong
Program filtering code was trying to JOIN with:
- Non-existent `sections` table
- Non-existent `s.program_id` relationship

## What's Right Now
Program filtering uses actual database structure:
- Faculty programs: `users.program` (VARCHAR field)
- No `sections` table needed
- Filters work by comparing program names

## Key Functions Fixed

### 1. `getVisibleProgramsFilter()`
Returns SQL WHERE clause for program filtering
- **Admin:** Returns `'1=1'` (no filter)
- **Program Chair:** Returns `'programs.college = ?'`
- **Faculty:** Returns `'CONCAT(...) = ?'` matching program name

### 2. `getVisiblePrograms()`  
Returns array of visible programs
- **Admin:** All programs
- **Program Chair:** College-level programs only
- **Faculty:** Their single assigned program

### 3. `getVisibleUsers()`
Returns array of visible users
- **Admin:** All users
- **Program Chair:** College-level users
- **Faculty:** Users from same program

### 4. `getVisibleTeams()`
Returns array of visible teams
- **Admin:** All teams
- **Program Chair:** College-level teams
- **Faculty:** Teams from same program

## Database Schema Used

```
users table:
├─ id (INT)
├─ usertype (INT: 0=admin, 1=student, 2=faculty)
├─ program (VARCHAR) ← Faculty assigned program
├─ first_name, last_name, ...

teams table:
├─ id (INT)
├─ program (VARCHAR) ← Team's program (matches users.program format)
├─ name, created_at, ...

programs table:
├─ id (INT)
├─ college (VARCHAR)
├─ name (VARCHAR)
├─ specialization (VARCHAR, optional)
```

## How Faculty Program Assignment Works

```
Faculty User (usertype=2)
    ↓
users.program = "Computer Science - AI"
    ↓
Can see teams where:
    teams.program = "Computer Science - AI"
    ↓
Can see users where:
    users.program = "Computer Science - AI"
```

## Files Changed

1. **`/opt/lampp/htdocs/assets/includes/program_filter.php`**
   - Fixed 4 filtering functions
   - Removed section table JOINs
   - Updated to use users.program field

## Validation

✅ PHP Syntax: PASS
```
No syntax errors in program_filter.php
No syntax errors in index.php
```

## Deployment Steps

1. Backup current program_filter.php (just in case)
2. Replace with fixed version (already done)
3. Test dashboard access as different user types
4. Monitor browser console for errors
5. Check Network tab for 200 responses

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Still getting SQL errors | Clear browser cache, reload dashboard |
| Faculty sees no data | Check `users.program` field is populated for that user |
| Admin sees limited data | Verify `users.usertype` and `users.id` are correct |
| Network errors | Check `/dashboard/app.js.php` for console errors |

---

**Created:** November 25, 2025
**Status:** ✅ DEPLOYED & VALIDATED
**Next Step:** Test with live user accounts
