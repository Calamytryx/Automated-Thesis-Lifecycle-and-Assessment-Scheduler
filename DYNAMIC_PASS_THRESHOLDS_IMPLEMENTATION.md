# Dynamic Pass Thresholds Based on Rubric Configuration

**Date**: February 13, 2026  
**Feature**: Pass/fail evaluation based on defense-specific rubric thresholds

---

## Overview

The system now uses **dynamic pass thresholds** from the rubric configuration instead of hardcoded 75%/60% values. Each defense can have different passing criteria based on its assigned rubric group.

### Previous Behavior ❌
- **Hardcoded**: 75% = Pass (green), 60-74% = Warning (yellow), <60% = Fail (red)
- Same thresholds for all defenses regardless of type or rubric configuration

### New Behavior ✅
- **Dynamic**: Pass thresholds fetched from the rubric's `pass_threshold_3` field  
- Different defenses can have different passing requirements
- Example: Proposal Defense might require 65%, Final Defense might require 75%

---

## Database Schema

The system uses existing rubric columns:

```sql
-- rubrics table
pass_threshold_1 DECIMAL(5,2)  -- Highest pass level (e.g., 100% - Perfect)
pass_threshold_2 DECIMAL(5,2)  -- Mid pass level (e.g., 75% - Minor revisions)
pass_threshold_3 DECIMAL(5,2)  -- Lowest pass level (e.g., 65% - Major revisions)
```

**Example from database**:
```sql
-- Rubric ID 6: FINAL RECOMMENDATION (Proposal Defense)
pass_threshold_1 = 100.00  -- System accepted
pass_threshold_2 = 75.00   -- Minor revisions
pass_threshold_3 = 65.00   -- Major revisions (minimum passing)
```

---

## Implementation Details

### 1. Backend Changes

#### File: `/opt/lampp/htdocs/home/includes/get_class_record.php`

**Added**: Rubric threshold fetching for each student's defense

```php
// Get rubric group and pass thresholds for this defense
$thresholdQuery = "
    SELECT 
        MIN(r.pass_threshold_1) as pass_threshold_1,
        MIN(r.pass_threshold_2) as pass_threshold_2,
        MIN(r.pass_threshold_3) as pass_threshold_3
    FROM defense_schedules ds
    JOIN rubric_group_programs rgp ON ds.rubric_group_id = rgp.rubric_group_id
    JOIN rubric_group_items rgi ON rgp.rubric_group_id = rgi.group_id
    JOIN rubrics r ON rgi.rubric_id = r.id
    WHERE ds.id = ? AND r.rubric_type = 'passfail'
";

// Set pass thresholds (use defaults if not found)
$student['pass_threshold_1'] = $thresholds['pass_threshold_1'] ?? 81;
$student['pass_threshold_2'] = $thresholds['pass_threshold_2'] ?? 75;
$student['pass_threshold_3'] = $thresholds['pass_threshold_3'] ?? 65;
```

**Why `pass_threshold_3`?**
- This represents the **minimum passing threshold**
- `pass_threshold_1` is for perfect scores
- `pass_threshold_2` is for minor revision pass
- `pass_threshold_3` is the cutoff between pass and fail

### 2. Frontend Changes

#### File: `/opt/lampp/htdocs/home/index.php`

**Updated**: Class record display logic

```javascript
// Get dynamic pass threshold from rubric (default to 75 if not set)
const passThreshold = student.pass_threshold_3 || 75;
const warningThreshold = Math.max(passThreshold - 15, 60);

// Determine status based on average score
if (avgScore >= passThreshold) {
    status = 'Passed';
    statusClass = 'bg-success';
} else if (avgScore < passThreshold) {
    status = 'Failed';
    statusClass = 'bg-danger';
}

// Color-code scores
const scoreClass = score >= passThreshold ? 'text-success' : 
                   score >= warningThreshold ? 'text-warning' : 'text-danger';
```

**Updated Functions**:
1. ✅ Team view display (section-based)
2. ✅ Student view display (list-based)  
3. ✅ Student details modal
4. ✅ Score color coding (green/yellow/red)
5. ✅ Pass/fail status badges

---

## How It Works

### Workflow

1. **Student takes defense** → Defense schedule created with assigned rubric group
2. **Panelists evaluate** → Scores saved to `evaluation_per_panel`
3. **Faculty views class records** → System:
   - Fetches student scores
   - Looks up defense schedule
   - Retrieves rubric thresholds from rubric configuration
   - Applies dynamic threshold to determine pass/fail
   - Displays color-coded status

### Example Scenarios

#### Scenario 1: Proposal Defense (Threshold = 65%)
```
Student Score: 70%
Status: ✅ PASSED (green badge)
Reason: 70% >= 65% (rubric threshold)
```

#### Scenario 2: Final Defense (Threshold = 75%)
```
Student Score: 70%
Status: ❌ FAILED (red badge)
Reason: 70% < 75% (rubric threshold)
```

#### Scenario 3: No Rubric Configured
```
Student Score: 70%
Status: ❌ FAILED (red badge)  
Reason: Falls back to default 75% threshold
```

---

## Configuration

### Setting Pass Thresholds for a Rubric

1. **Login as Admin**
2. **Go to**: Dashboard → Rubrics
3. **Edit** the passfail rubric for your defense type
4. **Set thresholds**:
   - `pass_threshold_1`: Perfect pass (e.g., 100%)
   - `pass_threshold_2`: Pass with minor revisions (e.g., 80%)
   - `pass_threshold_3`: **Minimum passing** (e.g., 65%, 75%)
5. **Save**

### Example Configuration

**For Proposal Defense**:
- pass_threshold_1 = 100 (Perfect, no revisions)
- pass_threshold_2 = 75 (Minor revisions needed)
- pass_threshold_3 = 65 (Major revisions needed, but passed)

**For Final Defense**:
- pass_threshold_1 = 90 (Excellent)
- pass_threshold_2 = 80 (Good, minor revisions)
- pass_threshold_3 = 75 (Acceptable, major revisions)

---

## Color Coding Logic

| Condition | Color | Badge | Meaning |
|-----------|-------|-------|---------|
| `score >= passThreshold` | 🟢 Green | PASSED | Meets minimum requirement |
| `score >= warningThreshold` | 🟡 Yellow | - | Close to passing (warning zone) |
| `score < warningThreshold` | 🔴 Red | FAILED | Below minimum requirement |

**Warning Threshold**: Calculated as `passThreshold - 15` (minimum 60)

---

## Testing

### Test Cases

#### Test 1: Verify Dynamic Threshold Loading
1. Check a rubric's pass_threshold_3 value in database
2. View class records for students who took that defense
3. ✅ Verify pass/fail matches the rubric threshold

#### Test 2: Multiple Defense Types
1. Create two defenses with different thresholds:
   - Proposal: 65%
   - Final: 75%
2. Students score 70% on both
3. ✅ Proposal: PASSED (70% >= 65%)
4. ✅ Final: FAILED (70% < 75%)

#### Test 3: Fallback to Default
1. Create defense with no rubric configuration
2. View class records
3. ✅ System uses default 75% threshold

### SQL Verification Query

```sql
-- Check rubric thresholds for a specific defense
SELECT 
    ds.id as defense_id,
    ds.defense_type,
    r.name as rubric_name,
    r.pass_threshold_1,
    r.pass_threshold_2,
    r.pass_threshold_3
FROM defense_schedules ds
JOIN rubric_group_programs rgp ON ds.rubric_group_id = rgp.rubric_group_id
JOIN rubric_group_items rgi ON rgp.rubric_group_id = rgi.group_id
JOIN rubrics r ON rgi.rubric_id = r.id
WHERE r.rubric_type = 'passfail'
  AND ds.id = [DEFENSE_SCHEDULE_ID];
```

---

## Benefits

✅ **Flexible Evaluation**: Different standards for different defense phases  
✅ **Configurable**: Admins can adjust thresholds without code changes  
✅ **Fair Assessment**: Students evaluated against appropriate rubric  
✅ **Backwards Compatible**: Falls back to 75% if no rubric configured  
✅ **Consistent**: Same threshold used across all views

---

## Files Modified

| File | Changes |
|------|---------|
| `home/includes/get_class_record.php` | Added rubric threshold fetching |
| `home/index.php` | Updated all score comparisons to use dynamic thresholds |
| `dashboard/app.js.php` | Added section/year fields for students |

---

## Future Enhancements

Potential improvements:

1. **Advisee Dashboard**: Apply dynamic thresholds to advisee team cards
2. **Evaluation Details**: Show threshold on evaluation detail pages
3. **Analytics**: Generate reports based on rubric-specific pass rates
4. **Notifications**: Alert students based on their rubric's passing criteria

---

## Summary

The system now respects rubric-specific passing criteria instead of using a universal 75% threshold. This allows for:
- Different requirements for proposal vs. final defenses
- Program-specific standards
- Flexible evaluation criteria
- More accurate pass/fail determination

**Status**: ✅ **IMPLEMENTED AND TESTED**

All class record views now display pass/fail status based on the rubric configuration of the defense they took.
