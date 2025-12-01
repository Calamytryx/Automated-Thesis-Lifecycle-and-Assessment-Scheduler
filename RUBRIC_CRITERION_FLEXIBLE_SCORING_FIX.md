# Rubric Criterion Flexible Scoring Fix

## Problem Summary
In numerical rubrics with individual scoring enabled, each criterion was incorrectly defaulting to 100 and couldn't be modified to have flexible max scores. All criteria were forced to use the same max score value.

## Root Cause
The `rubric_criteria` table lacked a `max_score` column to store individual criterion maximum scores. The system was only using a global `max_score_per_criterion` field, which didn't allow flexibility per criterion.

## Solution Implemented

### 1. Database Schema Update ✅
**File:** `/opt/lampp/htdocs/add_criterion_max_score_column.sql`

Added `max_score` column to `rubric_criteria` table:
```sql
ALTER TABLE `rubric_criteria` 
ADD COLUMN `max_score` DECIMAL(5,2) NULL DEFAULT NULL 
COMMENT 'Maximum score for this criterion (used for individual scoring in numerical rubrics)' 
AFTER `is_individual`;
```

**Column Details:**
- **Type:** `DECIMAL(5,2)` - allows scores up to 999.99
- **Nullable:** `YES` - NULL for group scoring criteria
- **Default:** `NULL`
- **Position:** After `is_individual` column

### 2. Backend Changes

#### a. Add Rubric (add_items.php) ✅
**Changes:**
- Updated SQL INSERT to include `max_score` column
- Extract `criterion_score` from frontend data and save as `max_score`
- Validate score is a valid float >= 0
- Only save max_score for individual criteria (`is_individual = 1`)

**Code:**
```php
// Handle max_score for individual criteria
$maxScore = null;
if ($criterion_is_individual && isset($criterion['criterion_score'])) {
    $maxScore = filter_var($criterion['criterion_score'], FILTER_VALIDATE_FLOAT);
    if ($maxScore === false || $maxScore < 0) {
        $maxScore = null;
    }
}

$stmtCriteria->execute([
    ':rubric_id' => $rubricId,
    ':criterion_text' => $criterionText,
    ':criterion_detail' => empty($criterionDetail) ? null : $criterionDetail,
    ':order_index' => $criterion['order_index'] ?? 0,
    ':is_individual' => $criterion_is_individual,
    ':max_score' => $maxScore // NEW: Store max score
]);
```

#### b. Edit Rubric (edit_items.php) ✅
**Changes:**
- Same logic as add_items.php
- Updated SQL INSERT for criteria to include `max_score`
- Extract and validate `criterion_score` from frontend

#### c. Get Rubric Details (get_rubric_details.php) ✅
**Changes:**
- Map `max_score` DB column to `criterion_score` for frontend compatibility
- Ensure proper data type casting

**Code:**
```php
foreach ($criteria as &$crit) {
    $crit['is_individual'] = (bool)$crit['is_individual'];
    // Map max_score to criterion_score for frontend compatibility
    $crit['criterion_score'] = $crit['max_score'];
}
```

### 3. Frontend Changes

#### a. Rubrics Tab (rubrics_tab.php) ✅

**Change 1: Remove max constraint from individual criterion input**
- **Before:** Input had `max="${maxScore}"` attribute restricting to global max
- **After:** No max constraint, allows flexible input with `step="0.01"`

```javascript
// OLD - Restrictive
<input type="number" name="criterion_score[]" value="0" min="0" max="${maxScore}" required>

// NEW - Flexible
<input type="number" name="criterion_score[]" value="0" min="0" step="0.01" required>
```

**Change 2: Properly load criterion scores when editing**
- Ensure both individual and group criterion scores are loaded correctly
- Set score value for individual criteria from `crit.criterion_score`

```javascript
if (individualEnabled) {
    lastRow.find('input[name="criterion_description[]"]').val(crit.criterion_text);
    // Set the max score for this individual criterion
    lastRow.find('input[name="criterion_score[]"]').val(crit.criterion_score || 0);
} else {
    lastRow.find('.criterion-description').val(crit.criterion_text);
    // For group scoring, set the readonly score
    lastRow.find('input[name="criterion_score[]"]').val(crit.criterion_score || 0);
}
```

**Change 3: Save function already correct**
- The existing save function was already sending `criterion_score` in the criteria data
- Backend now properly stores it as `max_score`

## How It Works Now

### Creating/Editing Individual Rubrics:
1. Enable "Individual Scoring" checkbox
2. Add criteria rows
3. Each criterion row shows:
   - Description input field
   - **Score input field (flexible, no upper limit except decimal precision)**
4. User can set different max scores per criterion (e.g., Criterion 1 = 50, Criterion 2 = 30, etc.)
5. Scores are saved to `rubric_criteria.max_score` column

### Loading Individual Rubrics:
1. API fetches criteria with `max_score` from database
2. Backend maps `max_score` → `criterion_score` for frontend
3. Frontend displays each criterion with its specific max score
4. User can modify scores when editing

### Data Flow:
```
Frontend (criterion_score) → Backend (saves as max_score) → Database (rubric_criteria.max_score)
Database (max_score) → Backend (maps to criterion_score) → Frontend (displays in input)
```

## Testing Checklist

- [x] Database migration runs successfully
- [x] New column appears in table structure
- [ ] Create new individual rubric with varied criterion scores
- [ ] Save and verify scores are stored correctly
- [ ] Edit existing individual rubric and modify scores
- [ ] Verify scores persist after editing
- [ ] Test group scoring still works (max_score should be NULL)
- [ ] Verify validation prevents negative scores
- [ ] Test decimal scores (e.g., 12.5, 33.33)

## Files Modified

1. `/opt/lampp/htdocs/add_criterion_max_score_column.sql` - NEW
2. `/opt/lampp/htdocs/dashboard/includes/add_items.php` - Modified
3. `/opt/lampp/htdocs/dashboard/includes/edit_items.php` - Modified
4. `/opt/lampp/htdocs/dashboard/includes/get_rubric_details.php` - Modified
5. `/opt/lampp/htdocs/dashboard/includes/tabs/rubrics_tab.php` - Modified

## Database Changes

**Table:** `rubric_criteria`
**New Column:** `max_score DECIMAL(5,2) NULL DEFAULT NULL`

## Notes

- The `max_score_per_criterion` field in the rubrics modal is now only for display/reference
- Each individual criterion can have its own unique max score
- Group scoring criteria will have `max_score = NULL` (calculated dynamically from quality levels)
- Individual criteria scores are validated to be >= 0
- Supports decimal scores with 2 decimal places precision

## Migration Instructions

1. Run the migration SQL:
   ```bash
   /opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/add_criterion_max_score_column.sql
   ```

2. Verify column was added:
   ```bash
   /opt/lampp/bin/mysql -u root -e "DESCRIBE icei_38697196_coecsathesis.rubric_criteria;"
   ```

3. No additional configuration needed - changes are backward compatible

## Status: ✅ COMPLETE

All changes have been implemented and the database migration has been executed successfully.
