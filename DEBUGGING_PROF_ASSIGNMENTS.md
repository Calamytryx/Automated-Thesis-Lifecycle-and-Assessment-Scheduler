# Debugging Guide - Class Professor Assignments

## If Nothing Loads

**Symptoms:** Page shows "Loading..." forever, or table stays empty

**Causes:**
1. API endpoints not accessible
2. Database connection issue
3. User not logged in as admin
4. PHP session not working

**How to fix:**
1. Open **DevTools** (F12) → **Network** tab
2. Look for XHR/Fetch requests to `/api/professor_assignments.php`
3. Check the **Response** of failed requests
4. Look for error messages

---

## If Dropdowns are Empty

**Symptoms:** Select boxes show "Select a section..." and "Select a professor..." but no options

**Causes:**
1. Database has no sections in `users.section` column
2. No faculty users exist (no usertype=2)
3. API returning empty data

**How to check:**
```sql
-- Run in MySQL/PhpMyAdmin

-- Check sections
SELECT DISTINCT section FROM users WHERE section IS NOT NULL AND section != '';

-- Check professors
SELECT id, first_name, last_name FROM users WHERE usertype = 2;

-- If both return results, issue is with API
```

**Database structure check:**
- `users` table must have `section` column (VARCHAR)
- `users` table must have `usertype` column
- Some users must have `usertype = 2` (faculty)
- Some users must have non-null `section` value

---

## If Assignments Table Shows "Failed to load assignments"

**Symptoms:** Red error message in table

**Causes:**
1. `section_professors` table doesn't exist
2. Table has different column names
3. Permission issue

**How to check:**
```sql
-- Run in MySQL/PhpMyAdmin

-- Check if table exists
SHOW TABLES LIKE 'section_professors';

-- Check table structure
DESCRIBE section_professors;

-- Should show columns:
-- id (INT, PRIMARY KEY)
-- section (VARCHAR)
-- professor_id (INT)
-- status (VARCHAR)
-- assigned_by (INT)
-- assigned_at (DATETIME)
```

**If columns are different:**
Edit `/opt/lampp/htdocs/api/professor_assignments.php`:
- Find `assignProfessorToSection()` function (around line 195)
- Update column names in INSERT statement
- Find `listSectionProfessors()` function (around line 110)
- Update column names in SELECT statement

---

## If "Assign" Button Doesn't Work

**Symptoms:** Click Assign → Nothing happens, or error alert

**Causes:**
1. Both dropdowns not filled
2. API endpoint error
3. Duplicate assignment already exists

**How to check:**
1. Make sure BOTH dropdowns have values selected
2. Open **DevTools** → **Network** tab
3. Click Assign
4. Look for POST request to `/api/professor_assignments.php`
5. Check **Response** tab for error message

**Common response errors:**
```
"Professor already assigned to this section"
→ That section+professor combo already exists

"Invalid professor ID"
→ Selected professor doesn't exist or usertype != 2

"section and professor_id required"
→ Form isn't sending data correctly
```

---

## If "Delete" Button Doesn't Work

**Symptoms:** Click delete → confirm → nothing happens

**Causes:**
1. Wrong assignment ID being sent
2. API delete endpoint error
3. Database permission issue

**How to check:**
1. Open **DevTools** → **Network** tab
2. Click a delete button
3. Check the POST request body - should show assignment ID
4. Check Response for errors

---

## If You See JavaScript Errors in Console

**Common errors:**

### "Cannot read property 'data' of undefined"
- API response format is wrong
- Add debug line: `console.log(response);` in loadAssignments()

### "$ is not defined"
- jQuery not loaded
- Check that jQuery is included in the page

### "Unexpected token" in JSON
- API is returning HTML instead of JSON
- Usually means PHP error occurred
- Check `/api/professor_assignments.php` for syntax errors

---

## How to Add Debug Logging

**Edit the JavaScript in `professor_assignments_tab.php`:**

```javascript
function loadSections() {
    $.ajax({
        url: '/api/professor_assignments.php',
        type: 'GET',
        data: { action: 'list_sections' },
        dataType: 'json',
        success: function(response) {
            console.log('Sections response:', response); // ADD THIS LINE
            if (response.success && response.data) {
                const select = $('#sectionSelect');
                select.find('option:not(:first)').remove();
                response.data.forEach(section => {
                    select.append(`<option value="${section}">${section}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Load sections error:', error, xhr); // ADD xhr HERE
        }
    });
}
```

**Then in DevTools Console, you'll see:**
- What data came back from API
- What errors occurred
- Full AJAX error details

---

## Database Table Creation

**If `section_professors` table doesn't exist, create it:**

```sql
CREATE TABLE section_professors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(255) NOT NULL,
    professor_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    assigned_by INT,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(section),
    INDEX(professor_id),
    FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## Quick Test Queries

**Run these in PhpMyAdmin to verify setup:**

```sql
-- Check if user is logged in and is admin (usertype=0)
SELECT id, username, usertype FROM users WHERE id = YOUR_ADMIN_ID;

-- Count sections
SELECT COUNT(DISTINCT section) as total_sections FROM users;

-- Count professors
SELECT COUNT(*) as total_professors FROM users WHERE usertype = 2;

-- Check assignments table
SELECT * FROM section_professors;

-- Test the data structure
SELECT 
    sp.id,
    sp.section,
    u.first_name,
    u.last_name,
    u.email
FROM section_professors sp
JOIN users u ON sp.professor_id = u.id;
```

---

## Step-by-Step Troubleshooting

1. **Check if page loads at all**
   - Go to dashboard
   - Click Professor Assignments tab
   - Page should display with empty table and dropdowns
   
2. **If page doesn't show**
   - Check browser console (F12)
   - Look for 404 or PHP errors

3. **If page shows but nothing populates**
   - Open DevTools Network tab
   - Reload page
   - Look for failed XHR requests
   - Check response content

4. **If dropdowns are empty**
   - Run the database check queries above
   - Make sure users have sections and professors exist

5. **If Assign doesn't work**
   - Make sure section_professors table exists
   - Check Network tab for POST errors
   - Read the error message in response

6. **If nothing works**
   - Check PHP error log: `/opt/lampp/logs/php_error.log`
   - Check MySQL error log
   - Check browser console for ALL errors

---

## Critical Files

| File | Purpose |
|------|---------|
| `/opt/lampp/htdocs/dashboard/includes/tabs/professor_assignments_tab.php` | UI + JavaScript |
| `/opt/lampp/htdocs/api/professor_assignments.php` | API endpoints |
| Database `users` table | Section and professor data |
| Database `section_professors` table | Assignments storage |

---

## Getting More Help

**Check these logs:**
- Browser DevTools Console (F12)
- Browser Network tab (XHR/Fetch requests)
- `/opt/lampp/logs/php_error.log`
- `/opt/lampp/logs/mysql_error.log`
- MySQL error output in PhpMyAdmin

**Common resolution:** Most issues are either:
- Missing data in database
- Wrong column names
- User not logged in as admin
- section_professors table missing

