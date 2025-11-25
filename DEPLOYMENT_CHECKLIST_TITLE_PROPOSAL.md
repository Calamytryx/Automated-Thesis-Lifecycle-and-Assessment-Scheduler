# ✅ TITLE PROPOSAL FEATURE - DEPLOYMENT CHECKLIST

**Date**: November 24, 2025  
**Version**: 1.0 - Production Ready

---

## 🎯 PRE-DEPLOYMENT VERIFICATION

- [x] All PHP files syntax validated
  ```
  ✓ /dashboard/app.js.php
  ✓ /dashboard/includes/add_items.php
  ✓ /dashboard/includes/edit_items.php
  ✓ /dashboard/index.php
  ✓ /assets/includes/title_proposal_setup.php
  ```

- [x] Database connection verified
- [x] Migration function tested
- [x] Forms properly integrated
- [x] JavaScript handler functional
- [x] No conflicts with existing code
- [x] Backward compatibility confirmed

---

## 📦 DEPLOYMENT STEPS

### Step 1: Backup (5 minutes)
```bash
# Backup database
mysqldump thesis_assessment_system > backup_$(date +%Y%m%d).sql

# Backup code (optional)
tar -czf dashboard_backup_$(date +%Y%m%d).tar.gz /opt/lampp/htdocs/dashboard/
```

**Checklist**:
- [ ] Database backed up
- [ ] Files backed up (optional)
- [ ] Backups stored safely

---

### Step 2: Deploy Files (2 minutes)
Copy these files to your production server:

**Modified Files**:
```
[ ] /dashboard/app.js.php
[ ] /dashboard/includes/add_items.php
[ ] /dashboard/includes/edit_items.php
[ ] /dashboard/index.php
```

**New Support Files**:
```
[ ] /assets/includes/title_proposal_setup.php
[ ] /migrate_add_column.php
[ ] /check_title_proposal_column.php
[ ] /add_title_proposal_migration.sql (optional)
```

**Documentation Files** (optional, for reference):
```
[ ] /TITLE_PROPOSAL_QUICK_START.md
[ ] /TITLE_PROPOSAL_COMPLETE_SETUP.md
[ ] /DEPLOYMENT_READY_TITLE_PROPOSAL.md
[ ] /IMPLEMENTATION_SUMMARY_TITLE_PROPOSAL.md
```

**Checklist**:
- [ ] All modified files copied
- [ ] All support files copied
- [ ] File permissions verified (755 for PHP files)
- [ ] No files corrupted during transfer

---

### Step 3: Automatic Setup (< 1 minute)
Access the dashboard to trigger auto-migration:

```
1. Open browser: http://your-domain/dashboard/
2. Wait for page to load
3. Migration runs automatically in background
4. No user action needed
```

**Checklist**:
- [ ] Dashboard accessed successfully
- [ ] No errors in browser console
- [ ] Page loads normally
- [ ] No timeout errors

---

### Step 4: Verification (5 minutes)

#### Option A: Web-Based Verification (Recommended)
```
1. Open: http://your-domain/migrate_add_column.php
2. Look for: "✅ Column 'title_proposal' already exists"
3. Or: "✅ SUCCESS: Added 'title_proposal' column"
4. Scroll down to see full teams table structure
```

**Checklist**:
- [ ] Verification page loads
- [ ] Shows column exists or was created
- [ ] No error messages
- [ ] teams table structure displays correctly

#### Option B: Quick Check
```
1. Open: http://your-domain/check_title_proposal_column.php
2. Should display: "✅ Column 'title_proposal' EXISTS in teams table"
```

**Checklist**:
- [ ] Check page shows green checkmark
- [ ] Column confirmed to exist

---

### Step 5: Functional Testing (5 minutes)

#### Test 1: Add Team with Title Proposal
```
1. Go to: Dashboard → Teams Tab
2. Click: "Add Team" button
3. Verify: "Title Proposal" checkbox appears
4. Fill: Team details (Name, Title, Area, Program)
5. Check: The "Title Proposal" checkbox
6. Add: Team members
7. Verify: Adviser option NOT visible in role dropdown
8. Only: Leader and Member roles available
9. Click: "Save Team"
10. Verify: "Success" message appears
11. Check: Team appears in table
```

**Checklist**:
- [ ] Add form shows checkbox
- [ ] Checkbox is functional
- [ ] Adviser role hidden when checked
- [ ] Can add team with title_proposal=1
- [ ] Success message displays
- [ ] Team appears in table

#### Test 2: Add Team without Title Proposal
```
1. Go to: Dashboard → Teams Tab
2. Click: "Add Team" button
3. Fill: Team details (Name, Title, Area, Program)
4. Don't check: The "Title Proposal" checkbox
5. Add: Team members
6. Verify: ALL roles visible (Adviser, Leader, Member)
7. Select: "Adviser/Professor" role
8. Click: "Save Team"
9. Verify: "Success" message appears
```

**Checklist**:
- [ ] Can add team without title_proposal checked
- [ ] All roles visible (including Adviser)
- [ ] Can select Adviser role
- [ ] Success message displays
- [ ] Team created with title_proposal=0

#### Test 3: Edit Team to Enable Title Proposal
```
1. Go to: Dashboard → Teams Tab
2. Click: Edit button on an existing team
3. Verify: "Title Proposal" checkbox visible and unchecked
4. Check: The "Title Proposal" checkbox
5. Verify: Adviser option disappears from role dropdowns
6. Click: "Save Changes"
7. Verify: "Success" message appears
8. Edit: Same team again
9. Verify: Checkbox is now CHECKED
```

**Checklist**:
- [ ] Edit form shows checkbox
- [ ] Checkbox reflects current state
- [ ] Adviser hidden when checked
- [ ] Changes persist after save
- [ ] Success message displays

#### Test 4: Database Verification
```
MySQL Command:
SELECT id, name, title_proposal FROM teams LIMIT 5;

Expected Results:
- title_proposal column exists
- Some rows show 0, some show 1
- No NULL values (should be 0 default)
```

**Checklist**:
- [ ] Can run SELECT query
- [ ] title_proposal column exists
- [ ] Data looks correct
- [ ] No NULL values

---

## ⚠️ TROUBLESHOOTING

### Issue: "Column not found: 1054 Unknown column"

**Solution**:
```
1. Access: http://your-domain/migrate_add_column.php
2. This will create the missing column
3. Or run SQL manually:
   ALTER TABLE teams ADD COLUMN title_proposal TINYINT DEFAULT 0;
4. Refresh dashboard and try again
```

- [ ] Column created
- [ ] Error resolved
- [ ] Feature working

### Issue: Checkbox not visible in Add form

**Solution**:
```
1. Clear browser cache: Ctrl+Shift+Del (or Cmd+Shift+Del on Mac)
2. Hard refresh: Ctrl+F5 (or Cmd+Shift+R on Mac)
3. Check browser console: F12 → Console tab
4. Verify file was deployed: /dashboard/app.js.php exists
```

- [ ] Cache cleared
- [ ] Page refreshed
- [ ] Checkbox now visible

### Issue: Adviser option doesn't hide when checked

**Solution**:
```
1. Open browser console: F12
2. Check for JavaScript errors
3. Verify form element IDs:
   - Checkbox: #title_proposal
   - Container: #teamMembers
   - Selects: .role-select
4. Check file: /dashboard/app.js.php line 647
```

- [ ] Console checked for errors
- [ ] No errors found
- [ ] Element IDs verified
- [ ] Handler function exists

### Issue: Migration script shows error

**Solution**:
```
1. Check database connection
2. Verify database user permissions
3. Check error logs: /var/log/mysql/error.log
4. Try manual SQL in phpMyAdmin
5. Contact your database administrator
```

- [ ] Database connection verified
- [ ] Permissions checked
- [ ] Error logs reviewed

---

## 📊 ROLLBACK PLAN

If you need to revert the deployment:

### Rollback Steps
```
1. Restore backup database:
   mysql thesis_assessment_system < backup_YYYYMMDD.sql

2. Restore backup code (if backed up):
   tar -xzf dashboard_backup_YYYYMMDD.tar.gz

3. Remove new files:
   rm /migrate_add_column.php
   rm /check_title_proposal_column.php
   rm /assets/includes/title_proposal_setup.php
   rm /add_title_proposal_migration.sql
```

### Verification After Rollback
```
1. Access dashboard: http://your-domain/dashboard/
2. Test team operations
3. Verify no errors
4. Check database: teams table structure
```

**Checklist** (only if needed):
- [ ] Backup restored
- [ ] Code restored
- [ ] New files removed
- [ ] Dashboard working
- [ ] No errors

---

## ✅ POST-DEPLOYMENT CHECKLIST

- [ ] All verification tests passed
- [ ] No error messages in logs
- [ ] Dashboard functioning normally
- [ ] Teams can be added with/without title_proposal
- [ ] Adviser role visibility works correctly
- [ ] Database column verified
- [ ] Documentation files in place
- [ ] Support tools accessible
- [ ] No performance issues observed
- [ ] Users can access the feature

---

## 📝 SIGN-OFF

**Deployment Performed By**: ___________________  
**Date**: ___________________  
**Time**: ___________________  

**Verification By**: ___________________  
**Date**: ___________________  
**Time**: ___________________  

**Status**: 
- [ ] **PASSED** - All tests successful, feature live
- [ ] **FAILED** - Issues found, see troubleshooting
- [ ] **PARTIAL** - Some issues, feature limited

**Comments**: 
```
_________________________________________________________________

_________________________________________________________________

_________________________________________________________________
```

---

## 📞 SUPPORT CONTACTS

**Technical Issues**: Check `/TITLE_PROPOSAL_COMPLETE_SETUP.md` → Troubleshooting

**Quick Verification**: Access `/migrate_add_column.php`

**Documentation**: 
- Quick Start: `/TITLE_PROPOSAL_QUICK_START.md`
- Detailed Guide: `/TITLE_PROPOSAL_COMPLETE_SETUP.md`
- Implementation: `/IMPLEMENTATION_SUMMARY_TITLE_PROPOSAL.md`

---

## 🎉 DEPLOYMENT COMPLETE!

Once you've completed all steps and tests:

✅ **Feature is LIVE**  
✅ **Users can now use Title Proposal feature**  
✅ **Support documentation is available**  

---

**Next Steps**:
1. Inform users about the new feature
2. Share documentation if needed
3. Monitor for any issues
4. Keep backup files for 7+ days

---

**Version**: 1.0  
**Last Updated**: November 24, 2025  
**Status**: ✅ READY FOR DEPLOYMENT
