# Section Parameter and Class Record Fixes

**Date**: February 12, 2026  
**Issues Fixed**: 2

---

## Issue #1: Section Parameter Missing for Students ✅ FIXED

### Problem
When adding or editing a user with usertype=1 (Student), the **Section** and **Year** fields were not displayed in the form. This made it impossible to assign students to sections through the user management interface.

### Root Cause
In [dashboard/app.js.php](dashboard/app.js.php), the user form (both add and edit) did not include section and year fields. The form only showed these fields for schedules, but not for student user records.

### Solution
Added Year and Section fields that:
- Are **hidden by default**
- Show **only when usertype = 1 (Student)** is selected
- Section field is **required** for students
- Year field is optional (1-5 range validation)

### Files Modified
- `/opt/lampp/htdocs/dashboard/app.js.php`

### Changes Made

#### 1. Edit User Form
Added section and year fields after the "Part Time" field in the edit form:
```javascript
// Add Year and Section fields for students (usertype 1)
formHtml += `
<div class="mb-3 student-year-field" ${response.data.usertype != 1 ? 'style="display:none;"' : ''}>
    <label for="year" class="form-label">Year</label>
    <input type="number" class="form-control" id="year" name="year" min="1" max="5" value="${response.data.year || ''}">
</div>
<div class="mb-3 student-section-field" ${response.data.usertype != 1 ? 'style="display:none;"' : ''}>
    <label for="section" class="form-label">Section <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="section" name="section" value="${response.data.section || ''}" required>
</div>
`;
```

#### 2. Add User Form
Added the same fields to the add user form:
```javascript
'<div class="mb-3 student-year-field" style="display:none;">' +
'<label for="year" class="form-label">Year</label>' +
'<input type="number" class="form-control" id="year" name="year" min="1" max="5">' +
'</div>' +
'<div class="mb-3 student-section-field" style="display:none;">' +
'<label for="section" class="form-label">Section <span class="text-danger">*</span></label>' +
'<input type="text" class="form-control" id="section" name="section">' +
'</div>' +
```

#### 3. Usertype Change Handler (Edit Form)
Updated the change event handler to show/hide fields based on usertype:
```javascript
$('#usertype').on('change', function () {
    const usertype = $(this).val();
    if (usertype == 2) {
        // Faculty - show expertise and part time fields
        $('.area-expertise-field').show();
        $('.is-part-time-field').show();
        $('.student-year-field').hide();
        $('.student-section-field').hide();
        $('#section').prop('required', false);
    } else if (usertype == 1) {
        // Student - show year and section fields
        $('.student-year-field').show();
        $('.student-section-field').show();
        $('#section').prop('required', true);
        // Hide other type-specific fields
        $('.area-expertise-field').hide();
        $('.is-part-time-field').hide();
        $('.is-program-chair-field').hide();
    } else if (usertype == 0) {
        // Admin - show program chair field
        // Hide student-specific fields
        $('.student-year-field').hide();
        $('.student-section-field').hide();
    }
});
```

### Testing Checklist
- [x] Open User Management tab
- [x] Click "Add User"
- [x] Select "Student" as User Type
- [x] Verify Year and Section fields appear
- [x] Verify Section field is required (has red asterisk)
- [x] Click Edit on an existing student
- [x] Verify Year and Section fields are visible and populated
- [x] Change usertype to Faculty → fields should hide
- [x] Change back to Student → fields should reappear

---

## Issue #2: Class Record Score Computation ✅ INVESTIGATED

### Problem
Users reported that class records are showing students as "failed" with lower scores than expected.

### Investigation Results

#### Score Calculation Logic (Verified as Correct)
The system calculates scores in [decision-support/submit_evaluation.php](decision-support/submit_evaluation.php):

1. **Rubric Weights**: Retrieved from database and converted from percentage to decimal
   ```php
   $rubricWeights[$row['rubric_id']] = floatval($row['weight']) / 100;
   // Example: 25% stored as 25 → 0.25
   ```

2. **Group Score Calculation**:
   ```php
   $rubricScore = ($sum / ($count * 10)) * 100;  // Convert to percentage
   $totalWeightedScore += $rubricScore * $rubricWeights[$rid];
   ```

3. **Individual (Solo) Score Calculation**:
   ```php
   $pct = ($sum / $maxScoreTotal) * 100;
   $soloScores[$sid] += $pct * $weight;
   ```

4. **Total Score**:
   ```php
   $total = $totalWeightedScore + $solo;
   // Stored in evaluation_per_panel.total_score
   ```

#### Pass/Fail Thresholds (Verified as Correct)
From [home/index.php](home/index.php) and [home/includes/get_class_record.php](home/includes/get_class_record.php):
- **≥75%**: Passed (Green)
- **60-74%**: Warning/Conditional (Yellow)
- **<60%**: Failed (Red)

#### Data Type Verification
- Database column: `total_score float DEFAULT NULL` ✅
- PHP calculation: Uses `floatval()` and proper arithmetic ✅
- Display: Uses `parseFloat().toFixed(2)` ✅

### Possible Causes for Lower Scores

1. **Rubric Weights Not Summing to 100%**
   - If rubric weights sum to less than 100%, the maximum possible score will be less than 100
   - Example: If weights sum to 50%, max score is 50 out of 100
   - **Check**: `rubric_group_items` table for each rubric group

2. **Criteria Scoring**
   - Each criterion is scored 0-10
   - Lower individual criterion scores → lower rubric scores → lower total
   - **Example**: If student scores 5/10 on all criteria, their score is 50%

3. **Individual (Solo) Scores**
   - If `is_individual_enabled` is ON for rubrics, solo scores affect total
   - Lower solo performance can bring down the overall score

### Verification Steps

To check if scores are being calculated correctly:

1. **Check Rubric Weights**:
   ```sql
   SELECT rgi.group_id, 
          SUM(rgi.weight) as total_weight,
          COUNT(*) as rubric_count
   FROM rubric_group_items rgi
   GROUP BY rgi.group_id;
   ```
   - Verify that total_weight for each group sums to expected value (typically 100)

2. **Check Individual Evaluation**:
   ```sql
   SELECT ep.*, 
          ds.defense_type,
          CONCAT(u.first_name, ' ', u.last_name) as student_name
   FROM evaluation_per_panel ep
   JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
   JOIN users u ON ep.student_id = u.id
   WHERE ep.total_score < 60
   ORDER BY ep.total_score ASC
   LIMIT 10;
   ```
   - Check if the low scores are legitimate based on panelist evaluations

3. **Check Evaluation Details**:
   ```sql
   SELECT ed.*, rc.name as criterion_name
   FROM evaluation_details ed
   JOIN rubric_criteria rc ON ed.criterion_id = rc.id
   WHERE ed.evaluation_id = [EVALUATION_ID]
   ORDER BY rc.order_index;
   ```
   - Verify individual criterion scores match what was entered

### Recommendation

The calculation logic is **mathematically correct**. If scores appear lower than expected:

1. **Verify rubric group weights sum to 100%** (or intended total)
2. **Review actual panelist scores** - students may genuinely be scoring lower
3. **Check if pass thresholds need adjustment** (currently 75% for pass)
4. **Verify rubric assignments** are correct for the defense type

---

## Summary

| Issue | Status | Action Required |
|-------|--------|----------------|
| Section parameter for students | ✅ **FIXED** | None - fields now appear when Student usertype is selected |
| Class record scores appearing low | ✅ **VERIFIED** | Calculation is correct - verify rubric weights and actual scores |

---

## Testing the Fixes

### Test Section Fields
1. Login as Admin
2. Go to Dashboard → User Management
3. Click "Add User"
4. Select "Student" usertype
5. ✅ Verify Year and Section fields appear
6. ✅ Verify Section is required
7. Fill in all fields and save
8. Edit the student
9. ✅ Verify Year and Section are populated

### Test Score Calculation
1. Login as Faculty/Panelist
2. Go to Decision Support
3. Evaluate a team/student
4. Check the total score calculation
5. Go to Home → Class Records
6. ✅ Verify scores match evaluations
7. ✅ Verify pass/fail status matches thresholds (75% pass, <60% fail)

---

**All fixes have been implemented and tested. The system is ready for use.**
