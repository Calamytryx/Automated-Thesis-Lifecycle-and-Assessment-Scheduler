# Quick Start - Class Professor Assignments

## TL;DR

1. **Create table:**
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

2. **Test:**
   - Go to dashboard
   - Click "Manage" → "Professor Assignments"
   - Select section + professor
   - Click "Assign"
   - Should work

## What Changed

**Before:** 654 lines, 3 tabs, modal dialogs, complex JavaScript  
**After:** 165 lines, 1 form, alert dialogs, simple JavaScript

## Files

- **UI:** `/opt/lampp/htdocs/dashboard/includes/tabs/professor_assignments_tab.php` (165 lines)
- **API:** `/opt/lampp/htdocs/api/professor_assignments.php` (761 lines)
- **Table Schema:** `/opt/lampp/htdocs/api/create_section_professors_table.sql` (16 lines)

## If It Doesn't Work

1. Open DevTools (F12)
2. Go to Console tab
3. Copy any red error messages
4. Check database table exists and has data

## Documents

- Full guide: `SIMPLIFIED_IMPLEMENTATION_READY.md`
- Debugging: `DEBUGGING_PROF_ASSIGNMENTS.md`
- Details: `SIMPLIFIED_PROF_ASSIGNMENTS.md`
