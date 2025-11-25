# Quick Fix Reference - Session Management

## Problem
Programs dropdown: "Error loading programs"
Students list: Empty
Add Team: Fails

## Root Cause
Missing `session_start()` in AJAX endpoint files

## The Fix
Added this line to 5 files:
```php
session_start();  // Line must go FIRST!
```

## Files Fixed
1. ✅ `get_programs_grouped.php` - Line 3
2. ✅ `get_available_users.php` - Line 14
3. ✅ `add_items.php` - Line 3
4. ✅ `edit_items.php` - Line 3
5. ✅ `bulk_add_teams.php` - Line 3

## Verification
```bash
grep -n "session_start" /opt/lampp/htdocs/dashboard/includes/{get_programs_grouped,get_available_users,add_items,edit_items,bulk_add_teams}.php
```

All 5 should show `session_start()` ✓

## What It Does
Enables access to `$_SESSION` variables in AJAX requests:
- ✅ Programs dropdown filters by role
- ✅ Students list loads correctly
- ✅ Add team works
- ✅ Edit team works
- ✅ Bulk operations work

## Key Rule
```php
<?php
// MUST be first line in every PHP file that uses $_SESSION
session_start();

// Then other code
require_once 'db.inc.php';
```

## Status
✅ Fixed and validated
✅ Ready for testing
✅ All syntax passes

