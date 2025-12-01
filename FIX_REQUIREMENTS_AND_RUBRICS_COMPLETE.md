# Complete Fix: Defense Support System - Requirements & Rubrics

## Issues Identified

### 1. Wrong Requirements Shown
**Problem:** All defenses show final_defense requirements instead of the correct requirement based on defense type (title_proposal, title_defense, final_defense, re-defense).

**Root Cause:**
- System falls back to requirement_id=5 when no match found
- `program_manuscript_requirements` table not properly configured for all program + defense_type combinations

### 2. Wrong Rubrics Shown
**Problem:** Rubric groups don't automatically match defense type and program.

**Root Cause:**
- `rubric_groups` table lacks `defense_type` and `program_id` columns
- `group_id` is manually passed in URL instead of auto-determined
- No automatic mapping between defense schedule → correct rubric group

---

## Solutions Implemented

### Part 1: Fix Rubric Groups Structure

**File:** `/opt/lampp/htdocs/assets/setup/fix_rubric_groups_defense_type.sql`

**Changes:**
1. Add `defense_type` and `program_id` columns to `rubric_groups`
2. Update existing groups with correct defense types
3. Create function `get_rubric_group_for_defense()` for automatic selection

**Apply this:**
```bash
mysql -u root -p icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/fix_rubric_groups_defense_type.sql
```

### Part 2: Auto-Determine Rubric Group

**File:** `/opt/lampp/htdocs/decision-support/index.php`

**Changes:**
1. Made `group_id` parameter optional in URL
2. Added auto-detection logic:
   - Reads defense_type from schedule
   - Reads program from team
   - Finds matching rubric_group automatically
3. Falls back gracefully if no exact match

**Benefits:**
- URL can now be: `decision-support/index.php?schedule_id=123` (no group_id needed!)
- System automatically picks correct rubrics for defense type
- Still supports manual override: `decision-support/index.php?schedule_id=123&group_id=5`

### Part 3: Fix Missing Rubrics (Already Created)

**File:** `/opt/lampp/htdocs/assets/setup/fix_missing_rubrics.sql`

Adds rubrics 8-13 for Title Proposal and Title Defense evaluations.

---

## Installation Steps

### Step 1: Run Database Migrations

```bash
# Fix rubric groups structure
mysql -u root -p icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/fix_rubric_groups_defense_type.sql

# Add missing rubrics (if not already done)
mysql -u root -p icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/fix_missing_rubrics.sql
```

### Step 2: Configure Program Manuscript Requirements

For EACH program and defense type combination, add entries to `program_manuscript_requirements`:

```sql
-- Example: Configure for program 79 (BSIT) and program 80 (BSCS)

-- Title Proposal requirements
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, visibility_to_panelist, created_at)
VALUES
(46, 79, 'title_proposal', 1, 'before_defense', 1, NOW()),
(46, 80, 'title_proposal', 1, 'before_defense', 1, NOW());

-- Title Defense requirements  
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, visibility_to_panelist, created_at)
VALUES
(47, 79, 'title_defense', 1, 'before_defense', 1, NOW()),
(47, 80, 'title_defense', 1, 'before_defense', 1, NOW());

-- Final Defense requirements
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, visibility_to_panelist, created_at)
VALUES
(48, 79, 'final_defense', 1, 'before_defense', 1, NOW()),
(48, 80, 'final_defense', 1, 'before_defense', 1, NOW());

-- Re-Defense requirements
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, visibility_to_panelist, created_at)
VALUES
(49, 79, 're-defense', 1, 'before_defense', 1, NOW()),
(49, 80, 're-defense', 1, 'before_defense', 1, NOW());
```

**Important:** Replace requirement IDs (46, 47, 48, 49) with your actual requirement IDs from the `requirements` table.

### Step 3: Verify Current Requirements

```sql
-- Check what requirements exist
SELECT id, name, requirement_type, is_visible 
FROM requirements 
WHERE requirement_type IN ('title_proposal', 'title_defense', 'final_defense', 're-defense')
ORDER BY requirement_type;

-- Check what programs exist
SELECT id, name FROM programs ORDER BY name;
```

### Step 4: Update Rubric Groups (If Needed)

If you want specific rubric groups for specific programs:

```sql
-- Example: Make group 5 specifically for BSIT final defense
UPDATE rubric_groups 
SET program_id = 79, defense_type = 'final_defense' 
WHERE id = 5;

-- Make group 7 for all programs doing proposal defense
UPDATE rubric_groups 
SET program_id = NULL, defense_type = 'title_defense' 
WHERE id = 7;
```

---

## Verification

### Test 1: Check Rubric Groups
```sql
SELECT id, name, defense_type, program_id 
FROM rubric_groups 
ORDER BY defense_type, program_id;
```

Expected: Each group has appropriate defense_type.

### Test 2: Check Program Manuscript Requirements
```sql
SELECT 
    pmr.id,
    p.name as program_name,
    r.name as requirement_name,
    pmr.defense_type,
    pmr.visibility_to_panelist
FROM program_manuscript_requirements pmr
JOIN programs p ON pmr.program_id = p.id
JOIN requirements r ON pmr.requirement_id = r.id
WHERE pmr.visibility_to_panelist = 1
ORDER BY p.name, pmr.defense_type;
```

Expected: Entries for all program + defense_type combinations you need.

### Test 3: Test Defense Page

1. Open a defense schedule: `/decision-support/index.php?schedule_id=YOUR_SCHEDULE_ID`
2. Check browser console for:
   - Auto-determined group_id
   - Correct defense_type
   - Correct requirement_id
3. Verify:
   - Correct rubrics appear
   - Correct manuscript PDF loads

---

## Troubleshooting

### Issue: "No rubric group found"

**Fix:** Add a general fallback group:
```sql
INSERT INTO rubric_groups (name, description, defense_type, program_id) 
VALUES ('General Evaluation', 'Fallback for all defenses', 'general', NULL);
```

### Issue: Wrong PDF file shown

**Solutions:**

1. **Check defense_type in schedule:**
```sql
SELECT id, team_id, defense_type FROM defense_schedules WHERE id = YOUR_SCHEDULE_ID;
```

2. **Manually link correct file:**
```sql
-- Get team's submitted files
SELECT id, file_name, requirement_id FROM team_requirement_files WHERE team_id = YOUR_TEAM_ID;

-- Link file to defense schedule
UPDATE defense_schedules 
SET related_requirement_files = '[123]'  -- Replace 123 with actual file ID
WHERE id = YOUR_SCHEDULE_ID;
```

3. **Check program manuscript requirements:**
```sql
SELECT * FROM program_manuscript_requirements 
WHERE program_id = YOUR_PROGRAM_ID 
  AND defense_type = 'YOUR_DEFENSE_TYPE';
```

### Issue: Rubrics still not showing

**Check:**
```sql
-- Are rubrics active?
SELECT id, name, is_active FROM rubrics WHERE id IN (8,9,10,11);

-- Are they linked to the group?
SELECT rgi.*, r.name 
FROM rubric_group_items rgi
JOIN rubrics r ON rgi.rubric_id = r.id
WHERE rgi.group_id = YOUR_GROUP_ID;
```

---

## Complete Database Schema Reference

### Key Tables

**rubric_groups** (UPDATED)
- `id`: Primary key
- `name`: Group name
- `description`: Optional description
- `defense_type`: NEW - title_proposal, title_defense, final_defense, re-defense, general
- `program_id`: NEW - NULL for all programs, or specific program ID
- Indexes on (defense_type, program_id)

**program_manuscript_requirements**
- Maps which requirement appears for which program + defense_type
- `visibility_to_panelist = 1` means file shows in evaluation

**defense_schedules**
- `defense_type`: Type of defense
- `related_requirement_files`: JSON array of explicitly linked file IDs (highest priority)

**team_requirement_files**
- Stores all submitted manuscript versions
- Fallback when no explicit link exists

---

## Migration Checklist

- [ ] Run `fix_rubric_groups_defense_type.sql`
- [ ] Run `fix_missing_rubrics.sql` (if not done)
- [ ] Configure `program_manuscript_requirements` for all programs
- [ ] Update existing `rubric_groups` with defense_type
- [ ] Test defense evaluation page
- [ ] Verify correct PDFs load
- [ ] Verify correct rubrics appear
- [ ] Document which requirement IDs map to which defense types

---

## Contact

For issues:
1. Check error logs: `/opt/lampp/logs/error_log`
2. Look for "DS-Index" entries
3. Review this guide's troubleshooting section
