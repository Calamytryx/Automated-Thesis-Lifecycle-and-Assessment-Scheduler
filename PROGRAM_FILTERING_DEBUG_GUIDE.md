# Program Filtering Debug Guide

## Issue
Program dropdown showing full program list instead of filtering by user role.

## Root Cause Analysis

### Problem 1: Incorrect Include Statement (FIXED ✅)
**File**: `/opt/lampp/htdocs/dashboard/includes/get_programs_grouped.php`

**Issue**: The `require_once` for `program_filter.php` was INSIDE the `if` block:
```php
// ❌ WRONG - Chicken and egg problem!
if (function_exists('getVisiblePrograms')) {
    require_once '../../assets/includes/program_filter.php';  // Too late!
    $visiblePrograms = getVisiblePrograms(...);
}
```

**Why it failed**: The function `getVisiblePrograms()` cannot exist unless the file is included first. By putting the require inside the condition, the function never gets defined, the condition fails, and we fall back to showing all programs.

**Solution**: Include the file FIRST, then use the function:
```php
// ✅ CORRECT - Include happens first
require_once '../../assets/includes/program_filter.php';

// Now the function exists and can be used
if (function_exists('getVisiblePrograms')) {
    $visiblePrograms = getVisiblePrograms(...);
}
```

### Problem 2: Session Variables Not Initialized
**Added**: Session variable validation with detailed error logging

**What to check**:
- When AJAX calls the endpoint, does it include the session cookie?
- Are `$_SESSION['id']` and `$_SESSION['usertype']` present?

**Debug Approach**: Check browser console and `/opt/lampp/logs/php_error.log`

## How to Test

### 1. Check Console Logging
**File**: `/opt/lampp/htdocs/dashboard/app.js.php` (lines ~198)

Open browser Developer Tools (F12) → Console tab → Try adding a team:
- Should see: "populateProgramDropdown called with selected: null"
- Should see: "Current user type: X Current user ID: Y"
- Should see: "Program API response: {...}"

The response should show the number of programs returned:
- Super Admin (id=0, usertype=0): Should see ALL programs
- Program Chair (id≠0, usertype=0): Should see COLLEGE programs only
- Faculty (usertype=2): Should see THEIR PROGRAM only

### 2. Check PHP Error Logs
```bash
tail -50 /opt/lampp/logs/php_error.log | grep get_programs_grouped
```

Look for messages like:
```
get_programs_grouped.php - Filtering for userId: 1, usertype: 2
get_programs_grouped.php - program_filter returned 1 programs
```

If it shows:
```
get_programs_grouped.php - ERROR: Session not properly initialized!
```

Then session cookies aren't being sent with the AJAX request.

### 3. Test Different User Roles

**Super Admin** (typically id=0, usertype=0):
- ✅ Should see ALL programs in dropdown
- ✅ Should see filtered correctly (all)

**Program Chair** (id≠0, usertype=0):
- ✅ Should see only programs from their college
- ❓ If not working, check `get_user_college()` in `auth_functions.php`

**Faculty** (usertype=2):
- ✅ Should see only their assigned program
- ❓ If not working, check their `users.program` field in database

### 4. Manual AJAX Test
Open browser console and run:
```javascript
$.ajax({
    url: 'dashboard/includes/get_programs_grouped.php',
    method: 'GET',
    dataType: 'json',
    success: function(resp) {
        console.log('Returned programs:', resp.programs.length);
        console.log('First program:', resp.programs[0]);
    }
});
```

This will show:
- How many programs are being returned
- Their college/name values

## Implementation Summary

### Files Modified

1. **`/opt/lampp/htdocs/dashboard/includes/get_programs_grouped.php`**
   - ✅ Moved `require_once program_filter.php` to top of file
   - ✅ Added explicit session variable checks
   - ✅ Added comprehensive error logging
   - ✅ Graceful fallback to all programs if filter returns empty

2. **`/opt/lampp/htdocs/dashboard/app.js.php`**
   - ✅ Added console logging to `populateProgramDropdown()` function
   - ✅ Logs current user type/ID for debugging
   - ✅ Logs API response for inspection

### Key Logic Flow

```
User opens Add/Edit Team Modal
    ↓
populateProgramDropdown() is called (JavaScript)
    ↓
Makes AJAX request to get_programs_grouped.php
    ↓
PHP code includes program_filter.php (now at top)
    ↓
Calls getVisiblePrograms($pdo, $userId, $usertype)
    ↓
Based on usertype:
  - Admin (0, 0):     Returns ALL programs
  - Chair (0, ≠0):    Returns college programs only
  - Faculty (2):      Returns their assigned program only
    ↓
If result is empty: Falls back to ALL programs
    ↓
Formats data with display names (name + specialization)
    ↓
Returns JSON with grouped programs
    ↓
JavaScript populates dropdown grouped by college
```

## Expected Behavior After Fix

| User Type | Programs Visible | Grouped By |
|-----------|-----------------|-----------|
| Super Admin | All | College |
| Program Chair | College only | College |
| Faculty | Their program | College |

## Troubleshooting Checklist

- [ ] PHP syntax validation passed? `php -l /opt/lampp/htdocs/dashboard/includes/get_programs_grouped.php`
- [ ] Browser console shows API response with correct count?
- [ ] PHP error log shows correct userId/usertype?
- [ ] Session cookie is being sent with AJAX request?
- [ ] `program_filter.php` is properly included?
- [ ] Faculty member has `program` field set in database?
- [ ] Program Chair has college assigned via `get_user_college()`?

## Next Steps If Still Not Working

1. **Check if filtering logic is correct**:
   - Debug `getVisiblePrograms()` directly in a test file
   - Verify the SQL queries are working

2. **Check session persistence**:
   - Verify AJAX is sending cookies with the request
   - May need to add: `xhrFields: {withCredentials: true}` to AJAX call

3. **Check database data**:
   - Verify faculty members have program field populated
   - Verify program names match exactly
   - Verify program chairs have college field set

