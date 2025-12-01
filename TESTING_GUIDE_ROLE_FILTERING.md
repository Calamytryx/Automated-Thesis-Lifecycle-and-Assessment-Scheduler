# Quick Testing Guide - User Role Filtering

## What Was Fixed

The "Add Team Member" dropdown now shows:
- ✅ **Advisers**: Professors from the same college as the team's program
- ✅ **Leaders/Members**: Students from the section (or all if section empty)
- ✅ **No empty warnings**: Both student and professor lists load correctly

---

## 30-Second Test

### Quick Test #1: Does Adviser Show Professors?
```
1. Login as any professor
2. Create new team
3. Click "Add Team Member"
4. Click "Role" dropdown → Select "Adviser"
5. Click "User" dropdown

Expected: See professor names like "Dr. Smith", "Dr. Jones"
Fail: Shows empty or only students
```

### Quick Test #2: Does Leader Show Students?
```
1. Same team still open
2. Click "Role" dropdown → Select "Leader"  
3. Click "User" dropdown

Expected: See student names
Fail: Shows empty or no dropdown
```

### Quick Test #3: Title Proposal Mode
```
1. Check "Title Proposal" checkbox at top
2. Click "Add Team Member"
3. Click "Role" dropdown

Expected: "Adviser" option is GONE (disabled/hidden)
Fail: Adviser option still visible
```

---

## Detailed Test Scenarios

### Scenario A: Professor with Assigned Section

**Account**: Professor assigned to "Section A"

**Test Steps**:
```
1. Login
2. Dashboard → Teams → Create New Team
3. Fill in team details
4. Click "Add Team Member" button
5. Role dropdown: Select "Adviser"
6. User dropdown: Should show professors

✓ Pass: See [Dr. Smith (Staff/Professor), Dr. Jones (Staff/Professor), ...]
✗ Fail: Empty or no change

7. Role dropdown: Change to "Leader"
8. User dropdown: Should show students

✓ Pass: See [John Doe (Student), Jane Smith (Student), ...]
✗ Fail: Empty or shows professors
```

### Scenario B: Professor without Section Assignment

**Account**: Professor NOT assigned to any section

**Test Steps**:
```
1. Login
2. Dashboard → Teams → Create New Team
3. Click "Add Team Member"
4. Check all role dropdowns show appropriate users

✓ Pass: All three roles (Adviser, Leader, Member) show options
✗ Fail: Any role shows empty

5. No "No users available" warning should appear
✗ Fail: Warning appears
```

### Scenario C: Program Chair

**Account**: Program Chair (usually type=0)

**Test Steps**:
```
1. Login as program chair
2. Create new team
3. Click "Add Team Member"
4. Check Adviser dropdown

✓ Pass: Shows all professors from the college
✗ Fail: Shows empty or students

5. Check Leader dropdown
✓ Pass: Shows all students from the college
✗ Fail: Shows empty
```

### Scenario D: Title Proposal Teams

**For Any Account**:

**Test Steps**:
```
1. Create new team
2. Check "This is a Title Proposal" checkbox
3. Verify Professor field shows current user name
4. Click "Add Team Member"
5. Try to select a role

✓ Pass: "Adviser" option not visible in role dropdown
✗ Fail: "Adviser" option still visible

6. Only "Leader" and "Member" roles should be available
✗ Fail: "Adviser" still available for selection
```

---

## Browser Console Testing

Open browser Developer Tools (F12 or Right-Click → Inspect)

### Check Network Requests

**Tab**: Network

**Test**:
```
1. Click "Add Team Member" button
2. Look for requests in Network tab:
   - GET ...get_available_users.php?type=students
   - GET ...get_available_users.php?type=advisers
   
✓ Pass: Both requests show "200" status
✗ Fail: Either shows "404" or "500"

3. Click on each request
4. View Response tab
5. Should see JSON with "success": true and "data" array
```

### Check for JavaScript Errors

**Tab**: Console

**Test**:
```
1. Click "Add Team Member"
2. Look at Console tab
3. Should be EMPTY (no red error messages)

✗ Fail: See red error messages like:
   - "Uncaught TypeError: Cannot read property 'data'"
   - "$.when is not a function"
   - Other JavaScript errors
```

### Check AJAX Response Format

**Tab**: Console

**Paste this code**:
```javascript
// Test students endpoint
fetch('dashboard/includes/get_available_users.php?type=students')
    .then(r => r.json())
    .then(d => console.log('Students:', d))
    .catch(e => console.error('Error:', e));

// Test advisers endpoint  
fetch('dashboard/includes/get_available_users.php?type=advisers&team_program=ComputerScience')
    .then(r => r.json())
    .then(d => console.log('Advisers:', d))
    .catch(e => console.error('Error:', e));
```

**Expected Output**:
```
Students: {success: true, data: [...]}
Advisers: {success: true, data: [...]}
```

---

## Edge Cases to Test

### Edge Case 1: No Students in Database
```
Expected: "No users available" warning appears
Verify: System doesn't crash, gracefully handles empty data
```

### Edge Case 2: No Professors in Same College
```
Expected: Adviser dropdown shows empty
Verify: Can still add students as leader/member
```

### Edge Case 3: Very Large Student/Professor List
```
Expected: Still loads quickly (~200-300ms)
Verify: No timeout or browser freezing
```

### Edge Case 4: Switching Between Multiple Roles
```
1. Select Adviser → See professors
2. Change to Leader → See students
3. Change to Member → See students
4. Change back to Adviser → See professors again

Expected: Instant switching with correct users
```

### Edge Case 5: Title Proposal with Only Leader/Member Roles
```
1. Title Proposal checked
2. Add member as Leader
3. Add another as Member
4. Try to add as Adviser

Expected: Adviser role not available
Actual behavior: Should be able to add up to 4 members without adviser
```

---

## Rollback Instructions (If Needed)

If something goes wrong, revert the change:

```bash
# Check what was changed
git diff dashboard/app.js.php

# Revert to previous version
git checkout HEAD -- dashboard/app.js.php

# Clear browser cache
# Refresh page (Ctrl+Shift+R or Cmd+Shift+R)
```

---

## Success Indicators ✓

After deployment, verify:

- [ ] Adviser role dropdown shows professors
- [ ] Leader role dropdown shows students
- [ ] Member role dropdown shows students
- [ ] No "No users available" warnings appear
- [ ] Role switching works smoothly
- [ ] Title Proposal mode hides Adviser
- [ ] All account types work (professor, chair, admin)
- [ ] Both section and non-section accounts work
- [ ] No errors in browser console
- [ ] Network requests show "200 OK"
- [ ] Performance is fast (~200-300ms)

---

## Failure Indicators ✗

If you see any of these, something is wrong:

- ✗ Adviser dropdown empty
- ✗ "No users available" warning appears
- ✗ Role dropdown doesn't show Adviser when not in Title Proposal mode
- ✗ Red errors in browser console
- ✗ Network requests show "404" or "500"
- ✗ Very slow loading (>2 seconds)
- ✗ Dropdown stuck or doesn't update
- ✗ Title Proposal doesn't hide Adviser option

---

## Support

If tests fail:

1. **Check logs**: `tail -f /opt/lampp/logs/php_error.log`
2. **Check browser console**: F12 → Console tab
3. **Check network requests**: F12 → Network tab
4. **Check code file**: `/opt/lampp/htdocs/dashboard/app.js.php` line ~4181
5. **Check API response**: Call endpoints directly via curl or browser

---

## Test Sign-Off

```
Tester Name: ________________
Date: ________________
Time Spent: ________________

Test Results:
□ Adviser role works
□ Leader role works
□ Member role works
□ Title Proposal works
□ All account types work
□ No errors in console
□ Performance acceptable

Overall: □ PASS   □ FAIL

Issues Found (if any):
_________________________________
_________________________________
_________________________________

Notes:
_________________________________
_________________________________
```

---

**Ready to test!** 🚀
