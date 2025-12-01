# Quick Reference: Individual Criterion Flexible Scoring

## What Changed?
Individual rubric criteria can now have **custom maximum scores** instead of all defaulting to 100.

## Example
```
Before (All defaulted to 100):
✗ Criterion 1: Content        → 100
✗ Criterion 2: Presentation   → 100  
✗ Criterion 3: Q&A           → 100

After (Flexible scores):
✓ Criterion 1: Content        → 40
✓ Criterion 2: Presentation   → 35
✓ Criterion 3: Visual Aids    → 15
✓ Criterion 4: Q&A           → 10
                        Total: 100
```

## How to Use

### Creating New Individual Rubric:
1. Rubric Type: **Numerical**
2. ✓ Enable **Individual Scoring**
3. Add criteria and set custom scores for each
4. Save

### Editing Existing Rubric:
1. Click Edit on rubric
2. Modify criterion scores as needed
3. Save

## Database Details
- **Table:** `rubric_criteria`
- **Column:** `max_score DECIMAL(5,2) NULL`
- **Usage:** Stores max score for individual criteria only

## Files Modified
1. `dashboard/includes/add_items.php` - Save logic
2. `dashboard/includes/edit_items.php` - Update logic
3. `dashboard/includes/get_rubric_details.php` - Load logic
4. `dashboard/includes/tabs/rubrics_tab.php` - UI logic

## Migration
```bash
/opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/add_criterion_max_score_column.sql
```

## Status: ✅ COMPLETE
All changes implemented and migration executed successfully.
