# Quick Reference - Students List Fix

## Problem
Professor tries to add team members → Students dropdown empty

## Root Cause
Wrong API endpoint mode being called

## The Fix
**File**: `/opt/lampp/htdocs/dashboard/app.js.php`
**Line**: 4194

**Change**:
```diff
- url: 'includes/get_available_users.php?team_id=' + teamId + '&include_advisers=1'
+ url: 'includes/get_available_users.php?type=students&team_id=' + teamId + '&include_advisers=1'
```

**What changed**: Added `type=students&` parameter

## Why It Works
- `type=students` → Routes to section-based filtering
- Returns ALL students available to professor
- Students can be on multiple teams (no exclusion)
- Previous endpoint excluded students already on teams

## Testing
1. Login as professor
2. Add team
3. Click "Add Team Member"
4. Check: Students dropdown should be populated ✓

## Verification
```bash
# Check browser console F12 → Network tab
# Look for: get_available_users.php?type=students...
# Response should have: {"success": true, "data": [...]}
```

## Files Modified
- ✅ `dashboard/app.js.php` (line 4194)
- ✅ `get_available_users.php` (logging added)
- ✅ `section_access.php` (logging added)

