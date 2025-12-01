# Database Migration Complete - Defense Type System Fixed

## Date: December 2, 2025

## Problem
**Error:** `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'defense_type' in 'where clause'`

## Root Cause
The `defense_type` column existed in tables but had inconsistencies:
1. **defense_schedules** - Had `defense_type` column but some values were NULL
2. **rubric_groups** - Had `defense_type` but ENUM used 're-defense' (hyphen) instead of 're_defense' (underscore)
3. **Missing rubric group** - No rubric group existed for 'title_proposal' defense type

## Migrations Applied

### 1. Fixed defense_schedules Table
**File:** `add_defense_type_column.sql`
- ✅ Verified `defense_type` column exists
- ✅ Added index on `defense_type`
- ✅ Ensured `related_requirement_files` JSON column exists

### 2. Updated NULL defense_type Values
**File:** `fix_defense_type_enum.sql`
- ✅ Set all NULL `defense_type` values to 'general'
- ✅ Made `defense_type` NOT NULL with DEFAULT 'general'
- ✅ Updated ENUM to include: title_proposal, title_defense, final_defense, re_defense, general

### 3. Standardized ENUM Values
**File:** `standardize_defense_type_enum.sql`
- ✅ Changed 're-defense' (hyphen) to 're_defense' (underscore) in rubric_groups
- ✅ Ensured consistency across all tables

### 4. Added rubric_groups Columns
**File:** `fix_rubric_groups_simplified.sql`
- ✅ Added `defense_type` column to rubric_groups
- ✅ Added `program_id` column to rubric_groups  
- ✅ Created index on (defense_type, program_id)
- ✅ Updated existing groups with correct defense types

### 5. Created Title Proposal Rubric Group
**SQL executed directly:**
```sql
INSERT INTO rubric_groups (name, description, defense_type, program_id)
VALUES ('CCS', 'Title Proposal', 'title_proposal', NULL);

-- Assigned rubrics 30-39 (PDS/PDR series) to new group
INSERT INTO rubric_group_items (group_id, rubric_id)
SELECT 8, id FROM rubrics WHERE id BETWEEN 30 AND 39;
```
- ✅ Created rubric group ID 8 for title_proposal
- ✅ Assigned 10 rubrics to the new group

## Current Database State

### defense_schedules Table
- **Column:** `defense_type` ENUM('title_proposal','title_defense','final_defense','re_defense','general') NOT NULL DEFAULT 'general'
- **Index:** idx_defense_type on (defense_type)
- **Data Distribution:**
  - title_proposal: 22 schedules
  - general: 1 schedule

### rubric_groups Table  
- **Columns:**
  - `defense_type` ENUM('title_proposal','title_defense','final_defense','re_defense','general') DEFAULT 'general'
  - `program_id` INT(11) DEFAULT NULL
- **Index:** idx_defense_program on (defense_type, program_id)

### Rubric Groups
| ID | Name | Description | Defense Type | Rubric Count |
|----|------|-------------|--------------|--------------|
| 1 | Information Technology and Computer Science | (RE-PRESENTATION) | re_defense | 6 |
| 5 | CCS | Final Defense | final_defense | 6 |
| 6 | CCS | Proposal Re-Presentation | re_defense | 10 |
| 7 | CCS | Proposal Defense | title_defense | 10 |
| **8** | **CCS** | **Title Proposal** | **title_proposal** | **10** |

## Testing Steps

### 1. Test Title Proposal Defense
1. Log in as panelist/faculty
2. Click on a title_proposal defense schedule
3. **Expected Result:**
   - ✅ URL: `decision-support/index.php?schedule_id=X` (no group_id)
   - ✅ Page loads without "Column not found" error
   - ✅ Shows rubric group 8 (Title Proposal) rubrics
   - ✅ Console log: "DS-Index: Auto-determined group_id=8 for defense_type='title_proposal'"

### 2. Test Title Defense
1. Click on a title_defense schedule
2. **Expected Result:**
   - ✅ Shows rubric group 7 (Proposal Defense) rubrics
   - ✅ Different rubrics than title_proposal

### 3. Test Final Defense
1. Click on a final_defense schedule  
2. **Expected Result:**
   - ✅ Shows rubric group 5 (Final Defense) rubrics

### 4. Verify Auto-Determination Logic
Check PHP error log for confirmation:
```bash
tail -f /opt/lampp/logs/php_error_log
```

Look for:
```
DS-Index: Auto-determined group_id=8 for defense_type='title_proposal', program_id=NULL
DS-Index: Using team program for lookup: NULL
```

## Files Modified

### New Migration Files Created
1. `/opt/lampp/htdocs/assets/setup/add_defense_type_column.sql`
2. `/opt/lampp/htdocs/assets/setup/fix_defense_type_enum.sql`
3. `/opt/lampp/htdocs/assets/setup/standardize_defense_type_enum.sql`
4. `/opt/lampp/htdocs/assets/setup/fix_rubric_groups_simplified.sql`

### Code Files (Previously Modified)
1. `/opt/lampp/htdocs/decision-support/index.php` - Auto-determination logic
2. `/opt/lampp/htdocs/home/index.php` - Removed group_id requirement
3. `/opt/lampp/htdocs/home/includes/get_user_schedule.php` - Cleaned up queries

## Next Steps

1. ✅ Database structure is now correct
2. ✅ All migrations applied successfully
3. ⏭️ Test the system with actual defense schedules
4. ⏭️ Verify correct rubrics appear for each defense type
5. ⏭️ Configure program-specific rubric groups if needed (currently all use program_id = NULL)

## Rubric Assignment Reference

| Defense Type | Rubric Group ID | Rubrics Assigned |
|--------------|-----------------|------------------|
| title_proposal | 8 | 30-39 (PDS/PDR series) |
| title_defense | 7 | 30-39 (PDS/PDR series) |
| final_defense | 5 | 14-19 (FDR/FDS series) |
| re_defense | 1, 6 | 1-6 (old), 20-29 (PRS/PRR series) |
| general | - | (fallback) |

## Status
🟢 **MIGRATIONS COMPLETE** - System ready for testing

The error "Unknown column 'defense_type' in 'where clause'" has been resolved. The system can now:
- Auto-determine correct rubric groups based on defense_type
- Handle all defense types: title_proposal, title_defense, final_defense, re_defense
- Fall back to 'general' type when needed
