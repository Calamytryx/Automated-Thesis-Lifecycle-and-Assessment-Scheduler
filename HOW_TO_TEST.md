# HOW TO TEST - Professor Assignment System

## Quick Test (2 minutes)

### Step 1: Open Dashboard
```
1. Open http://localhost/dashboard (or your server URL)
2. Log in as admin (usertype 0)
3. You should see sidebar with navigation
```

### Step 2: Find Professor Assignments Tab
```
1. Scroll down in sidebar to "Defense Management" section
2. Look for "👔 Professor Assignments" link
3. Click it
```

### Expected Result:
✓ Tab opens without errors
✓ You see two sub-tabs: "Team Assignments" and "History"
✓ No red error messages
✓ Teams table loads with data

---

## Detailed Test Checklist

### ✅ Part 1: Tab Loads Without Error

**What to do**:
1. Click the "Professor Assignments" tab
2. Open browser console (F12 → Console tab)
3. Wait 2 seconds for data to load

**Expected**:
- Table shows teams (or empty state saying "No team assignments found")
- No red error messages in console
- No "failed to load" message on page
- Loading state briefly appears then disappears

**If broken**:
- Red error message in table → API connection issue
- Console shows red errors → JavaScript error
- Blank table forever → API not responding

---

### ✅ Part 2: Assign Adviser Modal Works

**What to do**:
1. Click "Assign Adviser" button (blue button in Team Assignments tab)
2. Look at modal that appears

**Expected**:
- Single modal appears (NOT two modals)
- Modal has three dropdown fields:
  - "Select Team"
  - "Select Adviser (Professor)"
  - "Defense Type"
- Modal has "Assign" button at bottom
- Modal can be closed (X button or Cancel)

**If broken**:
- Two modals appear → Modal instance problem
- Modal appears blank → Modal HTML issue
- Dropdowns empty → API not returning data

---

### ✅ Part 3: Dropdowns Populate

**What to do**:
1. Click "Select Team" dropdown
2. Click "Select Adviser (Professor)" dropdown
3. Click "Defense Type" dropdown

**Expected**:
- Teams dropdown shows list of team names
- Adviser dropdown shows list of professor names and emails
- Defense Type shows: Title Proposal, Title Defense, Final Defense, Re-defense

**If broken**:
- Empty dropdowns → API queries failing
- Only one option → Data not fully loading

---

### ✅ Part 4: Assign Adviser

**What to do**:
1. Open "Assign Adviser" modal
2. Select any team from "Select Team" dropdown
3. Select any professor from "Select Adviser (Professor)" dropdown
4. Select "Title Proposal" from "Defense Type" dropdown
5. Click "Assign" button

**Expected**:
- Modal closes
- Green success notification appears: "Adviser assigned successfully"
- Table refreshes with new adviser showing in the team row
- New entry appears in History tab with current date

**If broken**:
- Modal stays open → JavaScript error
- Red error appears → API error or database issue
- Table doesn't refresh → Page not updating

---

### ✅ Part 5: Delete Adviser

**What to do**:
1. Find a team with an adviser assigned
2. Click the trash/delete icon in the Actions column
3. Click "OK" in the confirmation dialog

**Expected**:
- Green success notification: "Assignment removed"
- Table refreshes
- Team now shows "Unassigned" for adviser
- Entry disappears from History tab

**If broken**:
- Modal stays open → JavaScript error
- Red error appears → API error
- Adviser still there → Delete didn't work

---

### ✅ Part 6: View History

**What to do**:
1. Click the "History" sub-tab
2. Scroll down to see all entries

**Expected**:
- List of all adviser assignments ever made
- Shows: Team name, Adviser name, Assignment date
- Date format: Readable date (e.g., "12/15/2024")
- Sorted by most recent first

**If broken**:
- "Failed to load" error → API query problem
- Empty table → No data, but that's okay
- Dates missing → Data format issue

---

### ✅ Part 7: Search Teams

**What to do**:
1. Go back to "Team Assignments" tab
2. Type in the search box at top ("Search teams...")
3. Type part of a team name
4. See table filter in real-time

**Expected**:
- Table filters as you type
- Only matching teams show
- Clear search → All teams show again

**If broken**:
- Search doesn't filter → Client-side search not working
- (This is not critical, just nice-to-have)

---

### ✅ Part 8: Mobile Responsiveness

**What to do**:
1. Open DevTools (F12)
2. Click "Toggle device toolbar" (mobile icon)
3. Select iPhone/mobile device
4. Click "Professor Assignments" tab

**Expected**:
- Tab content reflows for mobile screen
- Table columns adjust for small screen
- Buttons still clickable
- Modal works on mobile

**If broken**:
- Text overlaps → CSS issue
- Buttons unreachable → Layout issue

---

## Troubleshooting

### Problem: "Failed to load assignments"

**Causes**:
1. API file not updated → Check `/opt/lampp/htdocs/api/professor_assignments.php`
2. Database connection issue → Check error logs
3. Session expired → Log out and back in

**Fix**:
1. Refresh page (Ctrl+F5 for hard refresh)
2. Check browser console (F12) for actual error message
3. Check server error logs

### Problem: "Two modals showing"

**Causes**:
1. Old tab file still being used
2. JavaScript error in modal management

**Fix**:
1. Hard refresh page (Ctrl+F5)
2. Check that file is: `/opt/lampp/htdocs/dashboard/includes/tabs/professor_assignments_tab.php` (448 lines)
3. Look for "Let me handle this" in console

### Problem: "Can't assign adviser"

**Causes**:
1. No teams in database
2. No professors in database (faculty with usertype=2)
3. API endpoint broken

**Fix**:
1. Check dropdown populated with options
2. Select valid team and professor
3. Check console for error message
4. Check server error logs

### Problem: "Search doesn't work"

**This is optional** - not required for core functionality.
Just use refresh button to reload table.

---

## Console Debugging

### How to check for errors:

1. Open Developer Tools: **F12**
2. Click **Console** tab (red X means errors)
3. Look for red text - that's the error
4. Red text should show what went wrong

### Common error messages:

| Error | Meaning | Fix |
|-------|---------|-----|
| "404: /api/professor_assignments.php" | API file missing | Check file exists |
| "Unexpected token '<'" | PHP error in API | Check PHP syntax |
| "Cannot read property 'show' of null" | Modal variable issue | Refresh page |
| "CORS error" | Cross-origin issue | Usually okay, not blocking |

---

## Database Verification

### Check if data saves correctly:

If you have database access:

```sql
-- See all advisers assigned
SELECT t.name, u.first_name, u.last_name, tm.created_at
FROM team_members tm
JOIN teams t ON tm.team_id = t.id
JOIN users u ON tm.user_id = u.id
WHERE tm.role = 'adviser'
ORDER BY tm.created_at DESC;
```

Expected: Shows teams with their assigned professors and dates

---

## Success Criteria

✅ **All of these should work**:

- [ ] Tab loads without errors
- [ ] Teams list displays
- [ ] Assign button opens single modal
- [ ] Dropdowns populate with data
- [ ] Can select team, adviser, defense type
- [ ] Can click Assign and save to database
- [ ] Table refreshes with new adviser
- [ ] Can delete adviser
- [ ] History shows assignments
- [ ] No JavaScript errors in console
- [ ] Works on desktop AND mobile

---

## Performance Notes

- First load: May take 2-3 seconds (loading data)
- Subsequent loads: Should be fast (<1 second)
- Assign/Delete: Instant feedback with notification
- Search: Real-time as you type

---

## Next Steps After Testing

If everything works:
✅ System is ready for users
✅ Faculty can use it
✅ Advisers assigned successfully

If something doesn't work:
1. Check the troubleshooting section above
2. Look at console errors (F12)
3. Check server error logs
4. Verify files were updated correctly

---

## Support Resources

**Documentation**:
- `PROFESSOR_ASSIGNMENTS_COMPLETE.md` - Full documentation
- `QUICK_SUMMARY_FIXES.md` - Quick reference
- `BEFORE_AND_AFTER.md` - What changed and why
- `PROFESSOR_ASSIGNMENTS_API_FIXES.md` - API details

**Test Script**:
```bash
bash /opt/lampp/htdocs/test_api_fixes.sh
```

---

**Status**: All systems ready ✅
**Ready to test in browser**: YES
**Database changes needed**: NO
**User training needed**: Minimal (very intuitive)
