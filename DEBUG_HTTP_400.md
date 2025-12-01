# HTTP 400 Bad Request - "section/section_id and professor_id required"

## The Error

When trying to assign a professor, you get:
- HTTP 400 Bad Request
- "section/section_id and professor_id required"

## What This Means

The API received your request but one of these is missing/empty:
- `section` - The section name (required)
- `professor_id` - The professor ID (required)

## How to Debug

### Step 1: Check Browser Console

1. Open DevTools (F12)
2. Go to **Console** tab
3. Try assigning professor again
4. Look for log messages like:

```
Assign attempt: {section: "Section A", profId: "5"}
Sending payload: {action: "assign_professor_to_section", section: "Section A", professor_id: 5}
Assign response: {...}
```

OR error like:

```
Assign error details: {
  status: 400,
  statusText: "Bad Request",
  responseText: '{"success":false,"message":"..."}',
  error: "Bad Request"
}
```

### Step 2: Verify Dropdowns Have Values

In browser console, type:
```javascript
console.log({
  section: $('#sectionSelect').val(),
  professor: $('#profSelect').val()
});
```

**Expected output:**
```javascript
{
  section: "Section A",           // NOT empty
  professor: "5"                  // NOT empty, a number as string
}
```

**If either is empty**, the problem is the dropdowns aren't populated.

### Step 3: Check Dropdowns Are Populated

In browser console:
```javascript
console.log({
  sections: $('#sectionSelect').find('option').length,
  professors: $('#profSelect').find('option').length
});
```

**Expected:** Both numbers > 1 (first option is "Select a...")

**If 1 or less:** Dropdowns not populated, see next section.

### Step 4: Check Database Data

If dropdowns are empty, it means no sections or professors exist:

```sql
-- Check sections
SELECT DISTINCT section FROM users 
WHERE section IS NOT NULL AND section != '' 
LIMIT 10;

-- Check professors
SELECT id, first_name, last_name FROM users 
WHERE usertype = 2 
LIMIT 10;
```

**If either returns no rows:** You need to set up data first.

---

## Common Causes & Fixes

### Cause 1: Section Dropdown Empty

**Problem:** No sections to select from

**Fix:**
```sql
-- Add sections to some users
UPDATE users SET section = 'Section A' WHERE id = 1;
UPDATE users SET section = 'Section B' WHERE id = 2;
UPDATE users SET section = 'Section C' WHERE id = 3;
```

Then refresh dashboard.

### Cause 2: Professor Dropdown Empty

**Problem:** No faculty users exist

**Fix:** Check if faculty exist:
```sql
SELECT id, first_name, last_name FROM users WHERE usertype = 2;
```

If no results:
```sql
-- Update some users to be faculty (usertype = 2)
UPDATE users SET usertype = 2 WHERE id IN (5, 6, 7);
```

Then refresh dashboard.

### Cause 3: Dropdowns Show Options But Still Get Error

**Problem:** Maybe professor_id is being sent as string instead of number

**Fix:** Look in browser console for the payload being sent. It should show:
```javascript
{
  action: "assign_professor_to_section",
  section: "Section A",
  professor_id: 5    // <-- should be NUMBER, not "5"
}
```

If it shows `professor_id: "5"` (string), there's a JavaScript issue.

---

## Check API Error Log

The API now logs detailed error info. Check PHP error log:

```bash
tail -20 /opt/lampp/logs/php_error.log
```

Or check if table exists:

```sql
SHOW TABLES LIKE 'section_professors';
```

If table doesn't exist:
```bash
php /opt/lampp/htdocs/init_section_professors.php
```

---

## Step-by-Step Test

1. **Ensure table exists:**
   ```bash
   php /opt/lampp/htdocs/init_section_professors.php
   ```

2. **Add test data:**
   ```sql
   -- Add sections to users
   UPDATE users SET section = 'Test Section 1' LIMIT 1;
   UPDATE users SET section = 'Test Section 2' LIMIT 1;
   
   -- Add faculty
   UPDATE users SET usertype = 2 LIMIT 1;
   ```

3. **Refresh dashboard**

4. **Open DevTools (F12)**

5. **Select section and professor from dropdowns**

6. **Click Assign button**

7. **Check console logs** - should show:
   ```
   Assign attempt: {section: "...", profId: "..."}
   Sending payload: {...}
   ```

8. **Check response** - should show either success or specific error

9. **Report exact error** from console if it fails

---

## Expected Success Flow

1. ✅ Dropdowns populate with options
2. ✅ Select section from dropdown
3. ✅ Select professor from dropdown
4. ✅ Click "Assign" button
5. ✅ Console shows payload being sent
6. ✅ Alert says "Professor assigned successfully"
7. ✅ Form resets (dropdowns clear)
8. ✅ Table refreshes and shows new assignment

---

## If All Else Fails

1. Make sure you ran: `php init_section_professors.php`
2. Verify table exists: `SHOW TABLES LIKE 'section_professors';`
3. Check users have data:
   ```sql
   SELECT COUNT(*) FROM users WHERE section IS NOT NULL;
   SELECT COUNT(*) FROM users WHERE usertype = 2;
   ```
4. Check admin logged in with usertype=0:
   ```sql
   SELECT id, username, usertype FROM users WHERE id = [your_user_id];
   ```
5. Report exact console error messages

