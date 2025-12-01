`# Migration Status & Resolution Guide

## Current Situation

Your database already has some of the migration applied (partial state):
- ✅ `requirement_type` column exists in `requirements` table
- ❓ Other columns/tables may or may not exist

This is safe - we just need to complete the migration properly.

## Option 1: Use Idempotent Migration (RECOMMENDED) ✅

The new file handles everything safely:

```bash
mysql -u root -p coecsa_thesis < assets/setup/20251121_requirements_and_panelists_v2_idempotent.sql
```

**Features:**
- ✅ Checks if columns exist before adding them
- ✅ Uses `IF NOT EXISTS` for tables
- ✅ Won't throw errors if already applied
- ✅ Safe to run multiple times
- ✅ Idempotent (can be re-run without side effects)

---

## Option 2: Manual Completion (If you prefer checking step-by-step)

Run these queries one at a time to see what's already there:

### Step 1: Check what columns already exist
```sql
DESCRIBE requirements;
-- Look for: requirement_type, allow_multiple_submissions, max_submissions
```

### Step 2: Add missing columns if needed
```sql
-- Add if missing
ALTER TABLE `requirements` ADD COLUMN `allow_multiple_submissions` TINYINT(1) DEFAULT 0 AFTER `requirement_type`;
ALTER TABLE `requirements` ADD COLUMN `max_submissions` INT DEFAULT 1 AFTER `allow_multiple_submissions`;
```

### Step 3: Check if new tables exist
```sql
SHOW TABLES LIKE '%panelist%';
SHOW TABLES LIKE '%requirement_file%';
SHOW TABLES LIKE '%override%';
```

### Step 4: Create missing tables
```sql
-- Create if not exists
CREATE TABLE IF NOT EXISTS `team_panelists` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` INT(11) UNSIGNED NOT NULL,
  `defense_type` ENUM('title_proposal', 'title_defense', 'final_defense') NOT NULL,
  `panelist_id` INT(11) UNSIGNED NOT NULL,
  `panelist_position` INT(1) DEFAULT 1,
  `locked` TINYINT(1) DEFAULT 0,
  `admin_override` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT(11) UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_defense_panelist` (`team_id`, `defense_type`, `panelist_id`),
  KEY `idx_team_defense` (`team_id`, `defense_type`),
  KEY `idx_panelist` (`panelist_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`panelist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Similar for team_requirement_files and defense_type_overrides...
```

---

## Recommended Path Forward

### For Safety & Speed: Use Option 1 ⚡
```bash
# This is the safest approach
mysql -u root -p coecsa_thesis < assets/setup/20251121_requirements_and_panelists_v2_idempotent.sql
```

**Result:** All missing tables and columns will be added safely without errors.

---

## Verification

After running migration, verify everything is in place:

```sql
-- Check requirements columns
DESCRIBE requirements;
-- Should show: requirement_type, allow_multiple_submissions, max_submissions

-- Check tables exist
SHOW TABLES LIKE '%panelist%';
SHOW TABLES LIKE '%requirement_file%';
SHOW TABLES LIKE '%override%';

-- Check view exists
SHOW VIEWS LIKE 'team_defense_status';

-- Should see:
-- team_panelists ✓
-- team_requirement_files ✓
-- defense_type_overrides ✓
-- team_defense_status ✓
```

---

## Troubleshooting

### If you get "Duplicate column" errors
- The idempotent version handles this automatically
- Just use the v2_idempotent version

### If you get "Unknown column" errors
- Run the full idempotent migration again
- All columns will be created in correct order

### If you get "Foreign key constraint" errors
- Ensure `teams` and `users` tables exist (they should)
- Run migration without other transactions active

---

## Files Available

| File | Purpose |
|------|---------|
| `20251121_requirements_and_panelists.sql` | Original (may error on re-run) |
| `20251121_requirements_and_panelists_v2_idempotent.sql` | **USE THIS ONE** (safe to re-run) |

**Recommendation: Use v2_idempotent version** ✅

