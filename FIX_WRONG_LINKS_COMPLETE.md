# Fix: Wrong Links and Files Being Called

## Problem Identified
When opening a defense schedule, the system was calling the wrong links and showing wrong files because:

1. **Home page was passing hardcoded `rubric_group_id`** from database
2. The SQL subquery only considered `program`, NOT `defense_type`
3. This caused mismatches like:
   - Title Proposal defense showing Final Defense rubrics
   - Title Defense showing wrong manuscript requirements
   - Wrong files being displayed regardless of actual defense type

## Root Cause
```sql
-- OLD PROBLEMATIC QUERY (in home/index.php and get_user_schedule.php)
SELECT rgi.group_id
FROM rubric_programs rp
JOIN rubric_group_items rgi ON rp.rubric_id = rgi.rubric_id
WHERE rp.program_name = t.program
LIMIT 1
```

This query:
- ❌ Ignored `defense_type` column (title_proposal, title_defense, final_defense, re_defense)
- ❌ Only matched on `program` (BSIT, BSCS, etc.)
- ❌ Could return ANY rubric group for the program, not the correct one for the defense type

## Solution Implemented

### 1. Made `group_id` Optional in Decision Support
**File:** `/opt/lampp/htdocs/decision-support/index.php`

The decision-support page already has auto-determination logic:
```php
// Lines 75-128: Auto-determine group_id if not provided
if (!$group_id) {
    $auto_query = "SELECT id FROM rubric_groups 
                   WHERE defense_type = ? 
                   AND (program_id = ? OR program_id IS NULL)
                   ORDER BY program_id DESC LIMIT 1";
    // Uses ACTUAL defense_type from defense_schedules table
}
```

### 2. Updated Home Page Link Generation
**File:** `/opt/lampp/htdocs/home/index.php`

#### Removed Rubric Group Subqueries
```php
// BEFORE: Lines 1440-1491 - Complex subquery
(
    SELECT rgi.group_id
    FROM rubric_programs rp
    JOIN rubric_group_items rgi ON rp.rubric_id = rgi.rubric_id
    WHERE rp.program_name = t.program
    LIMIT 1
) AS rubric_group_id

// AFTER: Clean query without rubric_group_id
SELECT
    ds.id AS schedule_id,
    ds.schedule_date,
    ds.start_time,
    ds.end_time,
    ds.room,
    t.name AS team_name,
    t.program AS team_program
FROM defense_schedules ds
JOIN teams t ON ds.team_id = t.id
```

#### Simplified Click Handler
```php
// BEFORE: Lines 1521-1527 - Required rubric_group_id
if ($rubric_group_id !== null) {
    $onclick_attr = 'onclick="redirectToDecisionSupport(' . $schedule_id . ', ' . $rubric_group_id . ')"';
} else {
    $item_class .= ' disabled';
    $disabled_message = 'Rubric group not configured';
}

// AFTER: Lines 1519-1521 - Only schedule_id needed
if ($_SESSION['usertype'] == 2) { // Faculty
    $onclick_attr = 'onclick="redirectToDecisionSupport(' . $schedule_id . ')"';
}
```

#### Updated JavaScript Function
```javascript
// BEFORE: Lines 2460-2469 - Required both parameters
function redirectToDecisionSupport(scheduleId, groupId) {
    if (!scheduleId || !groupId) {
        console.error('Missing scheduleId or groupId');
        return;
    }
    const url = `../decision-support/index.php?schedule_id=${scheduleId}&group_id=${groupId}`;
}

// AFTER: Lines 2456-2475 - groupId optional
function redirectToDecisionSupport(scheduleId, groupId = null) {
    if (!scheduleId) {
        console.error('Missing scheduleId');
        return;
    }
    
    let url = `../decision-support/index.php?schedule_id=${scheduleId}`;
    if (groupId) {
        url += `&group_id=${groupId}`;
    }
}
```

### 3. Cleaned Up get_user_schedule.php
**File:** `/opt/lampp/htdocs/home/includes/get_user_schedule.php`

```php
// BEFORE: Lines 75-91 - Had rubric_group_id subquery
SELECT 
    ds.id as defense_schedule_id,
    ...
    (SELECT rgi.group_id FROM ...) AS rubric_group_id
FROM defense_schedules ds

// AFTER: Lines 62-75 - Clean query
SELECT 
    ds.id as defense_schedule_id,
    ds.schedule_date as date,
    ds.start_time,
    ds.end_time,
    ds.room,
    ds.team_id,
    CONCAT('Defense with team: ', t.name) as description
FROM defense_schedules ds
```

## How It Works Now

### Flow Diagram
```
User clicks defense schedule in home/index.php
    ↓
redirectToDecisionSupport(schedule_id)  [no group_id!]
    ↓
../decision-support/index.php?schedule_id=123
    ↓
Decision-support reads defense_schedules table:
    - Gets defense_type (title_proposal, title_defense, etc.)
    - Gets team_id → program_id
    ↓
Auto-determines correct rubric_group:
    SELECT id FROM rubric_groups 
    WHERE defense_type = 'title_proposal' 
    AND (program_id = 1 OR program_id IS NULL)
    ↓
Loads correct rubrics AND manuscript files for that defense type
    ✅ Title Proposal → Title Proposal rubrics + requirements
    ✅ Title Defense → Title Defense rubrics + requirements
    ✅ Final Defense → Final Defense rubrics + requirements
```

## Files Modified

1. ✅ `/opt/lampp/htdocs/home/index.php`
   - Removed `rubric_group_id` from SQL queries (2 places: student and faculty)
   - Removed `$rubric_group_id = $schedule['rubric_group_id'];` assignment
   - Simplified onclick handler to only pass `schedule_id`
   - Updated `redirectToDecisionSupport()` to accept optional `groupId`

2. ✅ `/opt/lampp/htdocs/home/includes/get_user_schedule.php`
   - Removed `rubric_group_id` subquery from faculty schedule query

## Testing Checklist

### Prerequisites
- [ ] Database migration applied: `fix_rubric_groups_defense_type.sql`
- [ ] `rubric_groups` table has `defense_type` and `program_id` columns
- [ ] Defense schedules have correct `defense_type` values

### Test Cases

#### Test 1: Title Proposal Defense
1. Log in as faculty/panelist
2. Click on a defense schedule with `defense_type = 'title_proposal'`
3. **Expected:**
   - URL: `decision-support/index.php?schedule_id=X` (NO group_id parameter)
   - Page shows "Title Proposal" rubrics
   - Manuscript files are from title proposal requirements
   - Error log shows: "DS-Index: Auto-determined group_id=Y for defense_type='title_proposal'"

#### Test 2: Title Defense
1. Click defense schedule with `defense_type = 'title_defense'`
2. **Expected:**
   - Shows Title Defense rubrics (different from title proposal)
   - Shows correct manuscript requirements for title defense

#### Test 3: Final Defense
1. Click defense schedule with `defense_type = 'final_defense'`
2. **Expected:**
   - Shows Final Defense rubrics
   - Shows final manuscript requirements (chapter 1-5)

#### Test 4: Re-Defense
1. Click defense schedule with `defense_type = 're_defense'`
2. **Expected:**
   - Shows re-defense rubrics
   - Shows appropriate manuscript files

### Verification Steps

1. **Check URL Parameter:**
   ```
   Open browser console
   Click defense schedule
   Verify URL does NOT have &group_id= parameter
   ```

2. **Check Auto-Determination:**
   ```bash
   # Check PHP error log
   tail -f /opt/lampp/logs/php_error_log
   
   # Look for:
   DS-Index: Auto-determined group_id=X for defense_type='title_proposal', program_id=1
   ```

3. **Check Rubric Display:**
   ```
   Title Proposal should show:
   - Understanding of the Problem (rubric_id 8)
   - Clarity of Objectives (rubric_id 9)
   - Presentation Skills (rubric_id 10)
   - Q&A and Defense (rubric_id 11)
   
   Final Defense should show different rubrics
   ```

4. **Check File Display:**
   ```
   Title Proposal defense:
   - Should show Chapter 1-3 or Title Proposal Document
   
   Final Defense:
   - Should show Chapter 1-5, Abstract, etc.
   ```

## Benefits

✅ **Correct Content:** Each defense type shows its correct rubrics and files
✅ **Automatic:** No manual group_id selection needed
✅ **Maintainable:** Single source of truth (rubric_groups table with defense_type)
✅ **Flexible:** Works for all programs (BSIT, BSCS, etc.)
✅ **Error-Free:** No more "wrong rubrics" or "wrong files" issues

## Related Documentation

- `FIX_REQUIREMENTS_AND_RUBRICS_COMPLETE.md` - Auto-determination implementation
- `assets/setup/fix_rubric_groups_defense_type.sql` - Database migration
- `DEPLOYMENT_CHECKLIST_TITLE_PROPOSAL.md` - System requirements

## Status

🟢 **COMPLETE** - All code changes implemented and ready for testing

## Next Steps

1. Apply database migration: `fix_rubric_groups_defense_type.sql`
2. Test with actual defense schedules
3. Verify correct rubrics appear for each defense type
4. Verify correct manuscript files appear
5. Monitor error logs for any issues
