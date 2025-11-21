# Decision-Support Issues Fixed

**Date:** November 22, 2025  
**Issues Fixed:** 2 major issues  
**Status:** ✅ FIXED

---

## Issues Fixed

### Issue #1: Hardcoded "Final Manuscript" (requirement_id=5)

**Problem:**  
Decision-support was hardcoded to look for requirement_id=5 (Final Manuscript) FIRST, ignoring the dynamic program+defense_type filtering system. This meant that even if you configured a different requirement for a particular program/defense combination, decision-support would still try to use the "Final Manuscript" if it existed.

**Location:**  
File: `/opt/lampp/htdocs/decision-support/index.php`  
Lines: 103-116 (original code)

**Old Code:**
```php
// Try to find requirement_id = 5 (traditionally "Final Manuscript") first
foreach ($applicableManuscripts['manuscripts'] as $manuscript) {
    if ($manuscript['id'] == 5) {
        $selectedManuscript = $manuscript;
        break;
    }
}

// If not found, use the first applicable manuscript
if (!$selectedManuscript && !empty($applicableManuscripts['manuscripts'])) {
    $selectedManuscript = $applicableManuscripts['manuscripts'][0];
}
```

**New Code:**
```php
// Use the first applicable manuscript based on program+defense_type
// DO NOT hardcode requirement_id = 5
$selectedManuscript = null;

// Use the first applicable manuscript (already filtered by program+defense_type)
if (!empty($applicableManuscripts['manuscripts'])) {
    $selectedManuscript = $applicableManuscripts['manuscripts'][0];
    error_log("DS-Index: Selected manuscript requirement_id: {$selectedManuscript['id']} (using dynamic filtering, not hardcoded)");
}
```

**Impact:**
- ✅ Now uses the ACTUAL configured requirement for the program+defense_type
- ✅ Respects your configuration from the dashboard
- ✅ Works with ANY requirement, not just id=5
- ✅ Properly cascades to first applicable if multiple exist

---

### Issue #2: Can't Multi-Submit / Re-Edit Evaluations

**Problem:**  
Once an evaluator submitted their evaluation, the "Submit Evaluation" button disappeared with a message "You can no longer edit this evaluation." This prevented evaluators from updating their evaluations if they needed to make changes.

**Location:**  
File: `/opt/lampp/htdocs/decision-support/index.php`  
Lines: 801-810 (check) and 1055-1061 (button display)

**Old Code (Button was hidden):**
```php
<?php if (!empty($rubrics_in_group) && !$done_evaluating): ?>
    <div class="text-center mb-5">
        <button type="submit" class="btn btn-primary btn-lg">Submit Evaluation</button>
    </div>
<?php endif; ?>
```

**Old Message:**
```php
if ($done_evaluating) {
    echo "<div class='container mt-5'><div class='alert alert-info'>You can no longer edit this evaluation.</div></div>";
}
```

**New Code (Button always visible):**
```php
<?php if (!empty($rubrics_in_group)): ?>
    <div class="text-center mb-5">
        <button type="submit" class="btn btn-primary btn-lg">
            <?php echo $done_evaluating ? 'Update Evaluation' : 'Submit Evaluation'; ?>
        </button>
    </div>
<?php endif; ?>
```

**New Message (Helpful, not blocking):**
```php
if ($done_evaluating) {
    echo "<div class='container mt-5'><div class='alert alert-info'><i class='fas fa-info-circle'></i> You can update your previous evaluation by re-submitting below.</div></div>";
}
```

**How It Works:**
The backend (`submit_evaluation.php`) already handles both scenarios:
1. **Update existing:** If evaluation_per_panel rows exist for this evaluator+schedule, they get updated (lines 188-200)
2. **Create new:** If no rows exist, they're created (lines 207-230)

So the backend was READY for multi-submit, it was just the frontend blocking it!

**Impact:**
- ✅ Button now shows "Submit Evaluation" if first time
- ✅ Button shows "Update Evaluation" if already submitted
- ✅ Users can edit their evaluations as many times as needed
- ✅ Each submission updates the existing records
- ✅ More user-friendly message

---

## Files Modified

**1 file changed:**
- `/opt/lampp/htdocs/decision-support/index.php`
  - Lines 103-113: Removed hardcoded requirement_id=5 preference
  - Lines 801-810: Changed blocking message to helpful message
  - Lines 1055-1061: Made submit button always visible with conditional label

---

## Testing Instructions

### Test #1: Verify Dynamic Requirement Selection

**Setup:**
1. Go to Dashboard → Requirements tab
2. Edit a requirement (e.g., "Final Manuscript", id=5)
3. In Program Configuration:
   - Select "Final Defense"
   - Configure ONLY for specific programs (e.g., only CS programs)
4. Create a team with a different program (e.g., Business)

**Test:**
1. Go to Decision-Support for the Business team's Final Defense
2. Check browser console (F12 → Console)
3. Should see log: "DS-Index: Selected manuscript requirement_id: X (using dynamic filtering, not hardcoded)"
4. The displayed manuscript should NOT be id=5 (or should be empty if no manuscript configured for Business+Final Defense)

**Expected Result:** ✅ Uses the configured requirement, not hardcoded id=5

---

### Test #2: Verify Multi-Submit / Re-edit

**Setup:**
1. Log in as an evaluator
2. Go to a defense schedule → click to evaluate
3. Fill out the rubrics and comments
4. Click "Submit Evaluation"
5. See success message and page reloads

**Test - First Submission:**
1. Submit button shows: "Submit Evaluation"
2. Fill form and submit
3. See success: "Evaluation submitted successfully"

**Test - Re-edit/Update:**
1. Go BACK to the same defense schedule
2. Button NOW shows: "Update Evaluation" (not hidden!)
3. Message says: "You can update your previous evaluation by re-submitting below."
4. Change scores or comments
5. Click "Update Evaluation"
6. See success: "Evaluation submitted successfully"
7. Verify changes are saved by reloading page

**Expected Result:** ✅ Can submit and update multiple times

---

## Technical Details

### How Dynamic Filtering Works
```
1. Decision-support page loads
2. Gets team_id from defense_schedule
3. Calls getTeamApplicableManuscripts($pdo, $team_id)
4. This function returns manuscripts for team's program+current defense_type
5. We now take the FIRST result (not looking for id=5)
6. Uses that requirement for the form
```

### How Multi-Submit Works
```
1. User submits evaluation
2. Backend checks: Does evaluation_per_panel row exist?
3. If YES → UPDATE existing row with new scores/comments
4. If NO → INSERT new row
4. Both scenarios result in successful submission
5. User can repeat this process as many times as needed
```

---

## Before vs After

### Before Fix #1
❌ Always tried to use requirement_id=5 first  
❌ Ignored your program-specific configuration  
❌ Showed wrong manuscript for some teams  
❌ Logging showed hardcoded preference

### After Fix #1
✅ Uses first applicable manuscript from filtered list  
✅ Respects your program+defense_type configuration  
✅ Shows correct manuscript based on team's setup  
✅ Logging confirms dynamic filtering

### Before Fix #2
❌ Submit button hidden after first submission  
❌ Message: "You can no longer edit this evaluation"  
❌ Users couldn't update their evaluations  
❌ Had to ask admin to delete and resubmit

### After Fix #2
✅ Button always visible  
✅ Label changes: "Submit" → "Update"  
✅ Helpful message about updating  
✅ Users can edit anytime

---

## Backward Compatibility

✅ **Fully backward compatible**
- All existing evaluations still work
- Existing data not modified
- No database changes required
- No API changes
- Previous configurations still work

---

## Deployment

**Files to Upload:**
- `/opt/lampp/htdocs/decision-support/index.php` (MODIFIED)

**No other changes needed.**

Changes take effect immediately upon upload.

---

## Summary

**Issue #1 - Hardcoded requirement_id=5:**  
Removed the priority logic that always preferred requirement_id=5. Now uses the first applicable manuscript from the dynamically filtered list based on program+defense_type.

**Issue #2 - Can't multi-submit:**  
Removed the check that hid the submit button after first evaluation. Button now always shows with intelligent label ("Submit" vs "Update"), allowing evaluators to edit their evaluations as needed.

Both fixes leverage existing functionality that was already implemented but either blocked by the frontend or not being used.

---

**Status:** ✅ **FIXED AND READY**

---

Generated: November 22, 2025  
Files Modified: 1  
Lines Changed: ~30  
Testing: Ready  
Deployment: Ready
