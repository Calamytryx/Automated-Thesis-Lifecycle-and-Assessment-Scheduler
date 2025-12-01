# Program-Specific Manuscript Requirements - Deployment Checklist

## Pre-Deployment Verification

- [ ] All files created successfully
  - [ ] `/assets/setup/20251122_program_manuscript_mapping.sql`
  - [ ] `/api/manuscript_requirements.php`
  - [ ] `/dashboard/includes/manuscript_requirements_functions.php`
  - [ ] `/dashboard/app.js.php` (modified)
  - [ ] `/decision-support/index.php` (modified)
  - [ ] `/home/includes/get_team_overview.php` (modified)

- [ ] Documentation created
  - [ ] `PROGRAM_MANUSCRIPT_REQUIREMENTS.md`
  - [ ] `PROGRAM_MANUSCRIPT_REQUIREMENTS_QUICK_START.md`
  - [ ] `IMPLEMENTATION_SUMMARY_PROGRAM_MANUSCRIPT_REQUIREMENTS.md`
  - [ ] `PROGRAM_MANUSCRIPT_REQUIREMENTS_INDEX.md`

---

## Phase 1: Database Setup

### Step 1.1: Backup Current Database
```bash
mysqldump -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis > /opt/lampp/htdocs/backups/icei_38697196_coecsathesis_backup_$(date +%Y%m%d_%H%M%S).sql
```
- [ ] Backup completed successfully
- [ ] Verify backup file exists and has content

### Step 1.2: Run Migration
```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_program_manuscript_mapping.sql
```
- [ ] Migration completed without errors
- [ ] Check: No "Table already exists" errors (safe to ignore if retrying)

### Step 1.3: Verify Database Tables
```bash
mysql icei_38697196_coecsathesis -e "SHOW TABLES LIKE '%manuscript%';"
```
- [ ] `program_manuscript_requirements` table exists
- [ ] `program_manuscript_view` view exists

### Step 1.4: Verify Database Columns
```bash
mysql icei_38697196_coecsathesis -e "DESC program_manuscript_requirements;"
```
- [ ] 11 columns present
- [ ] Proper data types (INT, VARCHAR, ENUM, BOOLEAN, TIMESTAMP)

### Step 1.5: Check Indexes
```bash
mysql icei_38697196_coecsathesis -e "SHOW INDEX FROM program_manuscript_requirements;"
```
- [ ] Primary key on `id`
- [ ] Indexes on (program_id, defense_type), (requirement_id, program_id), (is_required)
- [ ] Unique constraint on (requirement_id, program_id, defense_type)

---

## Phase 2: Code Deployment

### Step 2.1: Deploy API Endpoint
- [ ] Copy `/api/manuscript_requirements.php` to server
- [ ] Verify file permissions: `chmod 644 /opt/lampp/htdocs/api/manuscript_requirements.php`
- [ ] Test via browser: `http://localhost/api/manuscript_requirements.php?action=list_all_manuscripts`
- [ ] Should return JSON (even if no data yet)

### Step 2.2: Deploy Helper Functions
- [ ] Copy `/dashboard/includes/manuscript_requirements_functions.php` to server
- [ ] Verify file permissions: `chmod 644`
- [ ] Check: File is readable by web server

### Step 2.3: Deploy Dashboard Modifications
- [ ] Copy modified `/dashboard/app.js.php` to server
- [ ] Verify changes:
  - [ ] New form section for "is_defense_manuscript" checkbox
  - [ ] New functions: `loadProgramManuscriptConfig()`, `saveProgramManuscriptConfig()`
  - [ ] Program selection UI properly formatted

### Step 2.4: Deploy Decision Support Modifications
- [ ] Copy modified `/decision-support/index.php` to server
- [ ] Verify changes:
  - [ ] Helper functions loaded with `require_once`
  - [ ] `getTeamApplicableManuscripts()` called
  - [ ] PDF filename fetched from applicable manuscript

### Step 2.5: Deploy Home Tab Modifications
- [ ] Copy modified `/home/includes/get_team_overview.php` to server
- [ ] Verify changes:
  - [ ] Helper functions loaded with `require_once`
  - [ ] SQL query filters by program+defense_type
  - [ ] Only applicable requirements returned

---

## Phase 3: Configuration

### Step 3.1: Mark Requirements as Defense Manuscripts
```bash
# First, check which requirements should be manuscripts
mysql icei_38697196_coecsathesis -e "SELECT id, name, requirement_type FROM requirements WHERE requirement_type IN ('title_proposal', 'title_defense', 'final_defense', 're-defense');"

# Mark "Final Manuscript" as defense manuscript (typical: requirement_id = 5)
mysql icei_38697196_coecsathesis -e "UPDATE requirements SET is_defense_manuscript=1 WHERE id=5;"
```
- [ ] Identified defense manuscripts in system
- [ ] Marked with `is_defense_manuscript=1`

### Step 3.2: Get Program IDs
```bash
mysql icei_38697196_coecsathesis -e "SELECT id, name FROM programs ORDER BY name;"
```
- [ ] List of all programs obtained
- [ ] Program IDs noted for configuration

### Step 3.3: Configure First Manuscript (Via UI)
```
1. Go to Dashboard → Requirements tab
2. Find and edit "Final Manuscript"
3. Check: "This is a defense manuscript"
4. Program selector appears
5. Select: WebDevelopment, Mobile (example)
6. For each, select: Final Defense, Re-Defense
7. Click: "Save Program Configuration"
```
- [ ] Requirements tab loads without errors
- [ ] Checkbox appears and toggles section visibility
- [ ] Programs list loads successfully
- [ ] Save button functions properly
- [ ] Database receives configuration

### Step 3.4: Verify Configuration (Via API)
```bash
curl "http://localhost/api/manuscript_requirements.php?action=list_all_manuscripts"
```
- [ ] Returns JSON with configured manuscripts
- [ ] Shows program and defense type mappings

---

## Phase 4: Testing

### Test 4.1: Check Team-Specific Filtering
```bash
# Test WebDev team at Final Defense
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=10"
# Should show: "Final Manuscript" (if team 10 is WebDev at Final Defense)

# Test DataScience team at Final Defense  
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=20"
# Should NOT show: "Final Manuscript" (if team 20 is DataScience)
```
- [ ] WebDev team sees applicable manuscripts
- [ ] DataScience team doesn't see non-applicable manuscripts
- [ ] API returns proper JSON response

### Test 4.2: Check Home Tab Filtering
```
1. Log in as WebDev student
2. Go to Home page
3. Check: Requirement checklist shows only applicable manuscripts
4. Verify: "Final Manuscript" visible
5. Repeat as DataScience student
6. Verify: "Final Manuscript" NOT visible
```
- [ ] Home page loads without errors
- [ ] Requirements filtered correctly
- [ ] Different teams see different requirements

### Test 4.3: Check Decision Support Filtering
```
1. Log in as panelist
2. Open Defense Evaluation page for WebDev team at Final Defense
3. Check: PDF view shows applicable manuscript
4. Open Defense Evaluation for DataScience team at Final Defense
5. Check: PDF section shows appropriate message (if no applicable manuscript)
```
- [ ] Decision Support loads without errors
- [ ] Shows applicable manuscript for team's program+stage
- [ ] Handles missing manuscript gracefully

### Test 4.4: Check Admin Configuration UI
```
1. Log in as admin
2. Go to Dashboard → Requirements tab
3. Edit a defense manuscript requirement
4. Check: Program selector appears/disappears with checkbox
5. Select programs and defense types
6. Save configuration
7. Verify: Checkbox marks show correct selection
```
- [ ] Configuration UI appears correctly
- [ ] Checkboxes save and persist
- [ ] Database receives updates

### Test 4.5: Check Defense Stage Transitions
```
1. Team currently at Title Proposal stage
2. View home requirements
3. See only "Title Proposal" requirements
4. Update defense schedule to Title Defense
5. Refresh home page
6. See different requirements for new stage
```
- [ ] Filtering changes as defense stage changes
- [ ] No browser cache issues
- [ ] Database queries reflect current stage

---

## Phase 5: Documentation Deployment

### Step 5.1: Publish Documentation
- [ ] `PROGRAM_MANUSCRIPT_REQUIREMENTS.md` placed in root
- [ ] `PROGRAM_MANUSCRIPT_REQUIREMENTS_QUICK_START.md` placed in root
- [ ] `IMPLEMENTATION_SUMMARY_PROGRAM_MANUSCRIPT_REQUIREMENTS.md` placed in root
- [ ] `PROGRAM_MANUSCRIPT_REQUIREMENTS_INDEX.md` placed in root

### Step 5.2: Update Main README
- [ ] Add link to new documentation
- [ ] Update feature list to mention program-specific requirements
- [ ] Add quick start link

### Step 5.3: Admin Training Materials
- [ ] Create screenshot guide (optional)
- [ ] Document common configuration scenarios
- [ ] Prepare FAQ document

---

## Phase 6: Post-Deployment Verification

### Step 6.1: Check Error Logs
```bash
tail -100 /opt/lampp/logs/php_error.log | grep -i manuscript
tail -100 /opt/lampp/logs/apache_error.log | grep -i manuscript
```
- [ ] No PHP errors related to manuscript functionality
- [ ] No Apache errors related to new endpoints

### Step 6.2: Monitor Database Performance
```bash
mysql icei_38697196_coecsathesis -e "SHOW STATUS LIKE 'Slow_queries';"
```
- [ ] No slow queries introduced
- [ ] Check: Indexes are being used properly

### Step 6.3: Check User Feedback
- [ ] Collect feedback from admins
- [ ] Collect feedback from teams
- [ ] Address any immediate issues

### Step 6.4: Verify Integration
- [ ] Dashboard shows new configuration UI
- [ ] Decision Support filters correctly
- [ ] Home tab shows appropriate requirements
- [ ] API endpoints respond properly

---

## Rollback Plan (If Needed)

### If Critical Issues Found

#### Option 1: Database Rollback
```bash
mysql icei_38697196_coecsathesis < /opt/lampp/htdocs/backups/icei_38697196_coecsathesis_backup_[TIMESTAMP].sql
```

#### Option 2: Code Rollback
- Restore previous versions of modified files from version control
- Clear browser cache
- Restart web server

#### Option 3: Disable Feature
- Comment out helper function includes in modified files
- Revert to hardcoded requirement_id queries
- Minimal impact approach

---

## Completion Checklist

### Deployment Completed
- [ ] All files deployed
- [ ] Database migration completed
- [ ] Code modifications working
- [ ] Initial configuration done
- [ ] All tests passed
- [ ] No errors in logs
- [ ] Documentation in place
- [ ] Admin training completed
- [ ] Users briefed

### Go-Live Readiness
- [ ] System backup confirmed
- [ ] Rollback plan tested
- [ ] Support team notified
- [ ] Monitoring set up
- [ ] Documentation accessible
- [ ] Admin access verified
- [ ] User access verified

### Post-Launch Monitoring (First Week)
- [ ] Daily log review
- [ ] User issue tracking
- [ ] Performance monitoring
- [ ] Database backup verification
- [ ] Configuration validation

---

## Support Contacts

| Role | Contact | Notes |
|------|---------|-------|
| System Admin | [Name] | Database & server access |
| Developer | [Name] | Code issues & bugs |
| Admin User | [Name] | Configuration & training |
| Support | [Name] | User issues & tickets |

---

## Quick Reference Commands

**Check Migration Status:**
```bash
mysql icei_38697196_coecsathesis -e "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema='icei_38697196_coecsathesis' AND table_name='program_manuscript_requirements';"
```

**Verify API:**
```bash
curl -s "http://localhost/api/manuscript_requirements.php?action=list_all_manuscripts" | jq .
```

**Check Current Configurations:**
```bash
mysql icei_38697196_coecsathesis -e "SELECT r.name, p.name as program, pmr.defense_type, pmr.is_required FROM program_manuscript_requirements pmr JOIN requirements r ON pmr.requirement_id=r.id JOIN programs p ON pmr.program_id=p.id ORDER BY r.name, p.name;"
```

**Count Teams by Program:**
```bash
mysql icei_38697196_coecsathesis -e "SELECT program, COUNT(*) as team_count FROM teams GROUP BY program;"
```

---

## Issues Encountered & Resolution

### Issue 1: Migration fails with "Table already exists"
**Status:** ✅ Expected (safe to ignore if retrying)
**Resolution:** Drop table first if needed to start fresh

### Issue 2: API returns 404
**Status:** To be verified
**Resolution:** Check file path, server configuration

### Issue 3: Helper functions not found
**Status:** To be verified
**Resolution:** Verify `require_once` paths are correct

### Issue 4: Requirements showing old way
**Status:** To be verified
**Resolution:** Clear browser cache, verify modified files deployed

---

## Sign-Off

**Deployed By:** ___________________________  
**Date:** ___________________________  
**Verified By:** ___________________________  
**Date:** ___________________________  
**Approved By:** ___________________________  
**Date:** ___________________________  

---

**System Status:** ✅ Ready for Deployment

