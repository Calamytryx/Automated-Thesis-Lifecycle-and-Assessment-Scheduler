# Quick Fix Reference - Program Dropdown Filtering

## The Problem
Dropdown showing **ALL programs** instead of filtering by user role

## The Root Cause
**Chicken & Egg**: Function being used before file is included

```php
// ❌ BROKEN
if (function_exists('getVisiblePrograms')) {  // FALSE - function doesn't exist yet!
    require_once 'program_filter.php';        // Never executed
}
return getAllPrograms();  // Fallback - all programs!
```

## The Fix
**Move include to top**:

```php
// ✅ FIXED
require_once 'program_filter.php';            // Include FIRST
$visiblePrograms = getVisiblePrograms(...);   // Now it works!
```

## Changed Files
1. `/opt/lampp/htdocs/dashboard/includes/get_programs_grouped.php` - Moved include statement
2. `/opt/lampp/htdocs/dashboard/app.js.php` - Added debug logging

## How to Test
1. Press F12 in browser (Developer Tools)
2. Open Console tab
3. Add/Edit a team
4. Look for console messages showing program count
5. Check that count matches user role (not all programs!)

## Expected Results
- **Faculty**: 1 program (their assigned)
- **Program Chair**: ~8 programs (college only)
- **Super Admin**: 25+ programs (all)

## Verification
```bash
php -l /opt/lampp/htdocs/dashboard/includes/get_programs_grouped.php
# Output: No syntax errors detected ✓
```

---

**Status**: ✅ FIXED AND READY FOR TESTING

