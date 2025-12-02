# Pass/Fail Rubric Weight Integration Fix

**Date:** January 2025  
**Issue:** Pass/fail rubrics were using fixed 100-point scoring instead of utilizing the weight module from rubric_group_items table

## Problem Description

When pass/fail rubrics were integrated into the total score calculation, they were assigned a fixed value:
- **Pass** = 100 points
- **Fail** = 0 points
- **Max possible** = 100 points per pass/fail rubric

However, the system already had a **weight-based scoring module** in the `rubric_group_items` table where each rubric in a group has a configurable weight (percentage). This weight was:
- ✅ **Fetched** from the database
- ✅ **Displayed** in the UI as a badge
- ❌ **NOT used** in total score calculations

## Solution Implemented

### 1. Added Weight Data Attribute to Rubric Cards

**File:** `decision-support/index.php`  
**Line:** ~1223

```php
<div class="card mb-4 rubric-card" 
     data-rubric-id="<?php echo $rubric_id; ?>" 
     data-rubric-type="<?php echo $rubric['rubric_type']; ?>" 
     data-weight="<?php echo isset($rubric['weight']) && $rubric['weight'] !== null ? $rubric['weight'] : 0; ?>">
```

**Purpose:** Pass the weight value from PHP to JavaScript via a data attribute for client-side calculation.

### 2. Updated Total Score Calculation Function

**File:** `decision-support/index.php`  
**Function:** `updateTotalScore()`  
**Lines:** ~1567-1620

**Before:**
```javascript
// Pass/fail rubrics: Pass = 100 points, Fail = 0 points
evaluationForm.querySelectorAll('.rubric-card[data-rubric-type="passfail"]').forEach(card => {
    const rubricId = card.dataset.rubricId;
    const selectedOption = evaluationForm.querySelector(`input[name="selected_option[${rubricId}]"]:checked`);
    
    if (selectedOption) {
        const optionValue = parseInt(selectedOption.value);
        totalScore += (optionValue > 0) ? 100 : 0;
    }
    maxPossibleScore += 100; // Fixed 100 points
});
```

**After:**
```javascript
// Pass/fail rubrics: Pass = weight%, Fail = 0%
evaluationForm.querySelectorAll('.rubric-card[data-rubric-type="passfail"]').forEach(card => {
    const rubricId = card.dataset.rubricId;
    const weight = parseFloat(card.dataset.weight) || 0; // Get weight from data attribute
    const selectedOption = evaluationForm.querySelector(`input[name="selected_option[${rubricId}]"]:checked`);
    
    if (selectedOption) {
        const optionValue = parseInt(selectedOption.value);
        totalScore += (optionValue > 0) ? weight : 0; // Use weight instead of 100
    }
    maxPossibleScore += weight; // Use weight instead of fixed 100
});
```

## How It Works Now

### Database Structure
```
rubric_groups
├── id
├── name
└── description

rubric_group_items
├── id
├── group_id (FK to rubric_groups)
├── rubric_id (FK to rubrics)
├── order_index
└── weight (DECIMAL 5,2) ← This is the key field
```

### Example Calculation

**Scenario:** Evaluation group with 3 rubrics
1. **Numerical Rubric** - Weight: 60% (max 60 points)
2. **Pass/Fail Rubric** - Weight: 20% (pass=20, fail=0)
3. **Numerical Rubric** - Weight: 20% (max 20 points)

**Before Fix:**
- Max Possible Score = 60 + **100** + 20 = **180** ❌ (exceeds 100%)
- If student scores: 50/60 + Pass + 15/20 = 50 + **100** + 15 = **165/180 = 91.7%** ❌

**After Fix:**
- Max Possible Score = 60 + **20** + 20 = **100** ✅
- If student scores: 50/60 + Pass + 15/20 = 50 + **20** + 15 = **85/100 = 85%** ✅

## Benefits

1. ✅ **Accurate Percentage Calculation** - Total always sums to 100% when weights are properly configured
2. ✅ **Flexible Weighting** - Pass/fail rubrics can have any weight (e.g., 5%, 20%, 50%)
3. ✅ **Consistent with System Design** - Uses existing rubric_group_items.weight module
4. ✅ **Fair Evaluation** - Pass/fail rubrics contribute proportionally to their importance

## Testing Checklist

- [ ] Verify pass/fail rubrics display weight badge in card header
- [ ] Check total score calculation includes pass/fail weight correctly
- [ ] Confirm max possible score = sum of all rubric weights (should be ~100)
- [ ] Test with mixed rubrics (numerical + pass/fail)
- [ ] Verify percentage calculation is accurate
- [ ] Test with different weight configurations (5%, 10%, 20%, etc.)
- [ ] Ensure "Pass" selection adds weight to total score
- [ ] Ensure "Fail" selection adds 0 to total score
- [ ] Verify real-time updates when pass/fail options change

## Database Query Reference

To view rubric weights for a group:
```sql
SELECT 
    rg.id AS group_id,
    rg.name AS group_name,
    r.id AS rubric_id,
    r.name AS rubric_name,
    r.rubric_type,
    rgi.weight,
    rgi.order_index
FROM rubric_groups rg
JOIN rubric_group_items rgi ON rg.id = rgi.group_id
JOIN rubrics r ON rgi.rubric_id = r.id
WHERE rg.id = 1
ORDER BY rgi.order_index;
```

## Related Files

- `decision-support/index.php` - Main evaluation form with total score calculation
- Database tables:
  - `rubric_groups` - Evaluation group definitions
  - `rubric_group_items` - Maps rubrics to groups with weight
  - `rubrics` - Individual rubric definitions

## Notes

- Numerical rubrics still use their configured max scores (not affected by this change)
- Yes/No rubrics are not included in total score calculation (as before)
- Weight can be NULL - in that case, it defaults to 0 in calculations
- Total score display shows both earned score and max possible score (e.g., "85.00 / 100.00")

---
**Status:** ✅ Complete  
**Validated:** No syntax errors, ready for testing
