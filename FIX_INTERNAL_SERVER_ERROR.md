# Fix: "Load assignments error: Internal Server Error"

## Root Cause

The `section_professors` table either:
1. Doesn't exist in the database, OR
2. Has wrong schema (old schema with section_id instead of section column)

The API now returns empty data gracefully if the table doesn't exist, but assignments can't be saved without it.

---

## Solution - Create the Table

### Option 1: Automatic Setup (RECOMMENDED)

Run this PHP script from your browser or command line:

**From browser:**
```
https://localhost/init_section_professors.php
```

**From command line:**
```bash
cd /opt/lampp/htdocs
php init_section_professors.php
```

This will:
- Check if table exists
- Create it if missing
- Show success message

---

### Option 2: Manual SQL

**In PhpMyAdmin:**
1. Select your database
2. Go to "SQL" tab
3. Paste this:

```sql
CREATE TABLE IF NOT EXISTS `section_professors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section_professor` (`section`, `professor_id`),
  FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_professor_id` (`professor_id`),
  INDEX `idx_section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

4. Click "Go"

---

## After Creating Table

1. Go back to dashboard
2. Click "Manage" → "Professor Assignments"
3. Refresh page
4. Try assigning a professor again

---

## If Still Not Working

### Test 1: Check if section data exists
```sql
SELECT DISTINCT section FROM users WHERE section IS NOT NULL AND section != '';
```

If this returns no rows, users don't have sections assigned. Assign some sections to users first.

### Test 2: Check if faculty exist
```sql
SELECT id, first_name, last_name FROM users WHERE usertype = 2 LIMIT 5;
```

If this returns no rows, create some faculty users first.

### Test 3: Verify table was created
```sql
SHOW TABLES LIKE 'section_professors';
DESCRIBE section_professors;
```

Should show the table with these columns:
- id
- section
- professor_id
- status
- assigned_by
- assigned_at

### Test 4: Check browser console for errors
1. Open DevTools (F12)
2. Go to Console tab
3. Try assigning again
4. Look for any red error messages
5. Report the exact error

---

## Common Issues

### "Professor already assigned to this section"
- This is NORMAL - means the assignment already exists
- Try a different professor or section

### "Invalid professor ID"
- The selected professor doesn't exist or isn't faculty (usertype=2)
- Create more faculty users

### Table never created
- Run `php init_section_professors.php` to verify
- Check database name is correct in `/opt/lampp/htdocs/assets/setup/db.inc.php`

---

## Database Schema

The `section_professors` table structure:

| Column | Type | Purpose |
|--------|------|---------|
| `id` | int(11) | Primary key, auto-increment |
| `section` | varchar(255) | Section name (from users.section) |
| `professor_id` | int(11) | References users.id |
| `status` | varchar(50) | 'active' or other |
| `assigned_by` | int(11) | Admin who made assignment |
| `assigned_at` | timestamp | When assignment was made |

**Unique constraint:** (section + professor_id) - prevents duplicates

---

## Next Steps

1. **Create table** using Option 1 or 2 above
2. **Test assignment** in dashboard
3. **Check browser console** for any errors
4. **Report back** if issues persist with exact error message

