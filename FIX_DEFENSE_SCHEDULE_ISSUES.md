# Fix Guide: Defense Schedule Issues

## Issues Identified

### 1. Missing Rubrics (IDs 8, 9, 10, 11)
**Problem:** Group ID 2 references rubrics that don't exist in the database.

**Symptoms:**
- Error message: "No active rubrics found for this evaluation group"
- Rubric IDs 8, 9, 10, 11 are referenced but missing

**Root Cause:** 
The `rubric_group_items` table has entries for rubrics 8-11, but these rubrics were never created in the `rubrics` table. The IDs jump from 6 to 14.

**Solution:**
Run the migration script to add missing rubrics:

```bash
mysql -u your_username -p icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/fix_missing_rubrics.sql
```

Or via phpMyAdmin:
1. Open phpMyAdmin
2. Select the database `icei_38697196_coecsathesis`
3. Go to "SQL" tab
4. Copy and paste the contents of `/opt/lampp/htdocs/assets/setup/fix_missing_rubrics.sql`
5. Click "Go"

---

### 2. Wrong File Displayed (Defense Type Mismatch)
**Problem:** Title proposal defenses show files meant for final defense, or vice versa.

**Symptoms:**
- PDF viewer shows incorrect manuscript
- Defense type is "title_proposal" but shows "final_defense" manuscript

**Root Causes:**
1. No files explicitly linked to the defense schedule
2. Fallback requirement lookup may use wrong defense type
3. `program_manuscript_requirements` table not configured for all programs

**Solutions:**

#### Solution A: Explicitly Link Files to Defense Schedule (RECOMMENDED)
When scheduling a defense, explicitly link the correct file:

1. In the defense scheduling interface, select the specific manuscript file
2. This stores the file reference in `defense_schedules.related_requirement_files`
3. The evaluation page will prioritize explicitly linked files

**Code already updated:** The `index.php` now checks for explicitly linked files first before falling back to requirement lookup.

#### Solution B: Configure Program Manuscript Requirements
Ensure all programs have manuscript requirements configured:

```sql
-- Check current configurations
SELECT 
    pmr.id,
    p.name as program_name,
    r.name as requirement_name,
    pmr.defense_type,
    pmr.visibility_to_panelist
FROM program_manuscript_requirements pmr
JOIN programs p ON pmr.program_id = p.id
JOIN requirements r ON pmr.requirement_id = r.id
WHERE pmr.visibility_to_panelist = 1;

-- Add missing configurations (example for program ID 81)
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, visibility_to_panelist)
VALUES
(46, 81, 'title_proposal', 1, 'before_defense', 1),
(47, 81, 'final_defense', 1, 'before_defense', 1);
```

---

## Verification Steps

### After Running the SQL Fix

1. **Verify Rubrics Exist:**
```sql
SELECT id, name, defense_type, is_active 
FROM rubrics 
WHERE id IN (8, 9, 10, 11, 12, 13) 
ORDER BY id;
```

Expected result: 6 rows showing all rubrics with `is_active = 1`

2. **Verify Group 2 Associations:**
```sql
SELECT 
    rgi.group_id, 
    rgi.rubric_id, 
    rgi.order_index, 
    rgi.weight, 
    r.name as rubric_name, 
    r.is_active
FROM rubric_group_items rgi
LEFT JOIN rubrics r ON rgi.rubric_id = r.id
WHERE rgi.group_id = 2
ORDER BY rgi.order_index;
```

Expected result: 4 rows with all rubrics having `is_active = 1`

3. **Test Defense Schedule:**
- Open a defense schedule with group_id = 2
- Verify rubrics display correctly
- Check that the correct PDF file is shown

### Verify File Display

1. **Check Defense Schedule Files:**
```sql
SELECT 
    ds.id as schedule_id,
    ds.team_id,
    ds.defense_type,
    ds.related_requirement_files,
    t.name as team_name
FROM defense_schedules ds
JOIN teams t ON ds.team_id = t.id
WHERE ds.id = YOUR_SCHEDULE_ID;
```

2. **Check Team Requirements:**
```sql
SELECT 
    tr.team_id,
    tr.requirement_id,
    r.name as requirement_name,
    tr.file_name,
    tr.status
FROM team_requirements tr
JOIN requirements r ON tr.requirement_id = r.id
WHERE tr.team_id = YOUR_TEAM_ID;
```

---

## Enhanced Error Messages

The system now provides detailed error messages when issues occur:

### Missing Rubrics Error
Shows:
- Evaluation group name and ID
- Expected rubric IDs
- Possible causes
- SQL commands to verify the issue
- Steps to fix

### Missing PDF Error
Shows:
- Defense type
- Team ID
- Requirement ID used
- Possible causes
- Steps to resolve

---

## Prevention

### For Admins Creating New Rubric Groups

1. **Always verify rubrics exist before creating groups:**
```sql
SELECT id, name, is_active FROM rubrics WHERE id IN (your_rubric_ids);
```

2. **Create rubrics first, then assign to groups**

3. **Test the evaluation page after creating new groups**

### For Admins Scheduling Defenses

1. **Always select the correct defense type**
2. **Explicitly link manuscript files when available**
3. **Verify manuscript requirements are configured for the program**

---

## Database Schema Reference

### Key Tables

**rubrics**
- Stores evaluation rubric definitions
- `is_active` must be 1 for rubrics to appear

**rubric_group_items**
- Links rubrics to evaluation groups
- References rubrics by `rubric_id`

**defense_schedules**
- `defense_type`: Type of defense (title_proposal, final_defense, etc.)
- `related_requirement_files`: JSON array of explicitly linked file IDs

**program_manuscript_requirements**
- Maps requirements to programs by defense type
- `visibility_to_panelist = 1` means file appears in evaluation

**team_requirement_files**
- Stores submitted manuscript files
- Multiple versions possible (submission_number)

---

## Contact

If issues persist after following this guide:
1. Check error logs: `/opt/lampp/logs/error_log`
2. Enable PHP error display for detailed debugging
3. Contact system administrator with error logs
