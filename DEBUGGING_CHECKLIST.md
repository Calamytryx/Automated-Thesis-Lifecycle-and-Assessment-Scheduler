# Complete Debugging Checklist - HTTP 400 Error

## Quick Diagnosis

If you're getting "HTTP 400 Bad Request", follow these steps IN ORDER:

---

## Step 1: Initialize Database Table ✅

**Action:** Open in browser:
```
https://localhost/init_section_professors.php
```

**Expected:** 
```
✅ Table created successfully!

Table structure:
  - id (int(11))
  - section (varchar(255))
  - professor_id (int(11))
  - status (varchar(50))
  - assigned_by (int(11))
  - assigned_at (timestamp)
```

**If you see error:** 
- Take screenshot
- Report the error message

---

## Step 2: Verify Database Data

**Action:** Open PhpMyAdmin and run these queries:

### Query 1: Check if sections exist
```sql
SELECT DISTINCT section FROM users 
WHERE section IS NOT NULL AND section != '' 
LIMIT 10;
```

**Expected:** Should return section names like "Section A", "Section B", etc.

**If EMPTY:** Go to Step 3a

**If NOT EMPTY:** Go to Step 2b

### Query 2: Check if faculty exist
```sql
SELECT id, first_name, last_name FROM users 
WHERE usertype = 2 
LIMIT 10;
```

**Expected:** Should return professor names

**If EMPTY:** Go to Step 3b

**If NOT EMPTY:** Go to Step 2c

### Query 3: Verify you're logged in as admin
```sql
SELECT id, username, usertype, first_name, last_name FROM users WHERE usertype = 0 LIMIT 1;
```

**Expected:** Should return your admin account

**If EMPTY:** You're not admin, can't assign

---

## Step 3: Fix Missing Data

### Step 3a: Add Sections to Users

```sql
-- Add section to first 5 users
UPDATE users SET section = 'Section A' LIMIT 1;
UPDATE users SET section = 'Section B' LIMIT 1;
UPDATE users SET section = 'Section C' LIMIT 1;
```

Then refresh dashboard and try again.

### Step 3b: Create Faculty Users

```sql
-- Make some users faculty (usertype = 2)
UPDATE users SET usertype = 2 LIMIT 3;
```

Then refresh dashboard and try again.

### Step 3c: Verify Table Structure

If sections and faculty exist, verify the table:

```sql
DESCRIBE section_professors;
```

Should show:
```
| Field        | Type         | Null | Key | Default           |
|--------------|--------------|------|-----|-------------------|
| id           | int(11)      | NO   | PRI | NULL              |
| section      | varchar(255) | NO   | MUL | NULL              |
| professor_id | int(11)      | NO   | MUL | NULL              |
| status       | varchar(50)  | YES  |     | active            |
| assigned_by  | int(11)      | YES  |     | NULL              |
| assigned_at  | timestamp    | YES  |     | CURRENT_TIMESTAMP |
```

If columns are wrong or missing, re-run:
```
https://localhost/init_section_professors.php
```

---

## Step 4: Test in Browser

**Action:** Open dashboard, go to "Manage" → "Professor Assignments"

**Check 1:** Do dropdowns have options?
- Section dropdown should have multiple options
- Professor dropdown should have multiple options

**If NO:** Data setup failed. Go back to Step 2-3

**If YES:** Continue to Check 2

**Check 2:** Open DevTools (F12) → Console tab

**Select section and professor, click Assign**

**Look for log messages:**

### Good Logs:
```
Assign attempt: {section: "Section A", profId: "5"}
Sending payload: {action: "assign_professor_to_section", section: "Section A", professor_id: 5}
Assign response: {success: true, message: "Professor assigned to section successfully"}
```

### Bad Logs:
```
Assign error details: {
  status: 400,
  statusText: "Bad Request",
  responseText: '{"success":false,"message":"..."}',
  error: "Bad Request"
}
```

**If you see bad logs:** Copy the `responseText` message and continue to Step 5

---

## Step 5: Debug From Error Message

### Error: "Professor already assigned to this section"
- **Cause:** That combination already exists
- **Fix:** Select different section or professor

### Error: "Invalid professor ID"
- **Cause:** Selected professor doesn't exist or isn't faculty
- **Fix:** Check database for faculty with usertype=2

### Error: "section/section_id and professor_id required. Got: section=NULL..."
- **Cause:** Section value is empty
- **Fix:** Make sure section dropdown has real value selected

### Error: "section/section_id and professor_id required. Got: ...professorId=NULL"
- **Cause:** Professor ID is empty
- **Fix:** Make sure professor dropdown has value selected

---

## Step 6: Check Admin Permissions

The API requires admin login (usertype=0). Verify:

1. You're logged in
2. Your user has usertype=0
3. Session is valid

In browser console:
```javascript
// This won't show admin status, but should have PHPSESSID cookie
console.log(document.cookie);
```

---

## Step 7: Network Debugging

If you still get 400 error:

1. Open DevTools (F12)
2. Go to **Network** tab
3. Try assigning professor again
4. Right-click the POST request to `/api/professor_assignments.php`
5. Click "Copy as cURL"
6. Paste and show us

---

## Summary Checklist

- [ ] Table created: `https://localhost/init_section_professors.php`
- [ ] Sections exist: `SELECT DISTINCT section FROM users WHERE section IS NOT NULL;`
- [ ] Faculty exist: `SELECT id, first_name, last_name FROM users WHERE usertype = 2;`
- [ ] Admin user exists: `SELECT id FROM users WHERE usertype = 0;`
- [ ] Dropdowns populate in browser
- [ ] Console shows "Assign attempt" log
- [ ] Console shows either success or specific error
- [ ] Report exact error message

---

## Report Template

If you still have issues, please provide:

```
1. Output from: https://localhost/init_section_professors.php
   [Copy-paste result here]

2. Database query results:
   SHOW TABLES LIKE 'section_professors';
   [Result here]
   
   SELECT COUNT(*) FROM users WHERE section IS NOT NULL;
   [Result here]
   
   SELECT COUNT(*) FROM users WHERE usertype = 2;
   [Result here]

3. Browser console error message:
   [Copy-paste full error here]

4. Network request details (from DevTools Network tab):
   - Status code: [e.g., 400]
   - Response body: [copy full response]
```

