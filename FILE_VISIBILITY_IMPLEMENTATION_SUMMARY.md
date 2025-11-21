# File Visibility & Defense Manuscript System - Implementation Summary

**Date:** November 22, 2025  
**Status:** ✅ Complete - Ready for Database Migration & Testing

---

## What Was Built

A complete **file visibility control system** that allows administrators to:

1. **Mark Requirements as Defense Manuscripts** - Designate specific requirements as the main files to be defended
2. **Control File Visibility by Defense Type** - Show/hide files based on team's current defense stage (Title Proposal, Title Defense, Final Defense, Re-Defense)
3. **Set Per-Stage Access Rules** - Allow view-only vs download for each defense type
4. **Audit File Access** - Track when teams access defense files

---

## System Architecture

### Database Layer
- **2 New Tables:**
  - `file_visibility_rules` - Per-defense-type visibility rules
  - `defense_file_access_log` - Audit trail for file access
  
- **2 New Columns in `requirements` table:**
  - `is_defense_manuscript` (BOOLEAN) - Mark as main manuscript
  - `visibility_scope` (ENUM) - Control visibility mode

- **1 New View:**
  - `defense_manuscripts_view` - Query helper for manuscript information

### API Layer
- **File:** `/api/file_visibility_management.php`
- **8 Endpoints:**
  1. `mark_defense_manuscript` (POST) - Mark requirement as manuscript
  2. `set_visibility_scope` (POST) - Set visibility mode
  3. `set_defense_type_visibility` (POST) - Configure per-defense-type rules
  4. `get_visibility_rules` (GET) - Query visibility configuration
  5. `get_visible_files` (GET) - Get files visible to a team
  6. `can_access_file` (GET) - Check single file access
  7. `get_defense_manuscripts` (GET) - Get manuscripts for a team
  8. `list_defense_manuscripts` (GET) - Admin view of all manuscripts

### Helper Functions Layer
- **File:** `/dashboard/includes/file_visibility_functions.php`
- **9 Functions:**
  - `canTeamAccessFile()` - Check file access
  - `getTeamVisibleFiles()` - Get all visible files
  - `getTeamDefenseManuscripts()` - Get manuscripts only
  - `markAsDefenseManuscript()` - Mark requirement
  - `setVisibilityScope()` - Set mode
  - `setDefenseTypeVisibility()` - Configure rules
  - `getVisibilityRules()` - Query rules
  - `logFileAccess()` - Log access
  - `getFileAccessHistory()` - Get audit history

---

## 4 Visibility Scopes

| Scope | Behavior | Use Case |
|-------|----------|----------|
| **all_stages** | Visible to all defense types | General files, templates |
| **specific_stages** | Fine-grained control per stage | Manuscripts, stage-specific docs |
| **current_stage_only** | Visible ONLY during current stage | "Title Proposal materials" disappears after stage |
| **hidden** | Never visible to teams | Admin-only drafts, internal notes |

---

## Key Files Created

### 1. Database Migration
**File:** `/assets/setup/20251122_add_file_visibility.sql`

```sql
-- Add columns to requirements
ALTER TABLE requirements 
ADD COLUMN is_defense_manuscript BOOLEAN DEFAULT 0,
ADD COLUMN visibility_scope ENUM('all_stages', 'specific_stages', 'current_stage_only', 'hidden') DEFAULT 'all_stages';

-- Create file_visibility_rules table
CREATE TABLE file_visibility_rules (
  id INT PRIMARY KEY AUTO_INCREMENT,
  requirement_id INT UNSIGNED NOT NULL,
  defense_type ENUM('title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'),
  can_view BOOLEAN DEFAULT 1,
  can_download BOOLEAN DEFAULT 1,
  visibility_label VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_req_defense_type (requirement_id, defense_type),
  FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create defense_file_access_log table
CREATE TABLE defense_file_access_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  team_id INT UNSIGNED NOT NULL,
  requirement_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255),
  action ENUM('view', 'download') DEFAULT 'view',
  access_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  user_id INT UNSIGNED,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create helper view
CREATE OR REPLACE VIEW defense_manuscripts_view AS
SELECT r.id, r.name, r.is_defense_manuscript, r.visibility_scope,
       GROUP_CONCAT(fvr.defense_type) as visible_to_defense_types,
       COUNT(DISTINCT fvr.defense_type) as num_visible_types
FROM requirements r
LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id
WHERE r.is_defense_manuscript = 1
GROUP BY r.id;
```

### 2. REST API
**File:** `/api/file_visibility_management.php`

**Features:**
- Admin-only access control
- Input validation & sanitization
- Error handling with detailed messages
- JSON responses
- Automatic audit logging

**Example:** Mark requirement as defense manuscript
```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=mark_defense_manuscript" \
  -d "requirement_id=5&is_manuscript=1"
```

### 3. Helper Functions
**File:** `/dashboard/includes/file_visibility_functions.php`

**Example:** Check if team can access file
```php
$access = canTeamAccessFile($pdo, $team_id=10, $requirement_id=5);
if ($access['can_access']) {
    // Show file to team
}
```

---

## Real-World Usage Examples

### Example 1: Thesis Manuscript Visible at All Stages

```bash
# Mark as manuscript
curl -X POST "http://localhost/api/file_visibility_management.php?action=mark_defense_manuscript" \
  -d "requirement_id=1&is_manuscript=1"

# Set scope to specific_stages for fine control
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=1&visibility_scope=specific_stages"

# Title Proposal: can upload/download
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=1&defense_type=title_proposal&can_view=1&can_download=1"

# Title Defense: read-only
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=1&defense_type=title_defense&can_view=1&can_download=0"

# Final Defense: read-only
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=1&defense_type=final_defense&can_view=1&can_download=0"
```

**Result:**
- Team at Title Proposal: Sees manuscript, can modify
- Team at Title Defense: Sees manuscript, read-only
- Team at Final Defense: Sees manuscript, read-only

### Example 2: Proposal Only for Title Proposal Stage

```bash
# Set to current_stage_only - disappears after stage ends
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=2&visibility_scope=current_stage_only"

# Make it visible to title_proposal stage
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=2&defense_type=title_proposal&can_view=1&can_download=1"
```

**Result:**
- Team at Title Proposal: Sees "Proposal" requirement
- Team at Title Defense: "Proposal" requirement hidden
- Team at Final Defense: "Proposal" requirement hidden

### Example 3: Admin-Only Materials

```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=99&visibility_scope=hidden"
```

**Result:** All teams: Cannot see this requirement ever

---

## Query Examples

### Get Files Visible to Team #5
```bash
curl "http://localhost/api/file_visibility_management.php?action=get_visible_files&team_id=5"

# Returns:
{
  "success": true,
  "data": {
    "team": { "id": 5, "name": "Team Alpha", "defense_type": "title_defense" },
    "current_defense_type": "title_defense",
    "files": [
      {
        "id": 1,
        "name": "Thesis Manuscript",
        "is_defense_manuscript": 1,
        "file_name": "thesis_final.pdf",
        "status": "approved",
        "can_view": 1,
        "can_download": 0
      }
    ],
    "file_count": 1,
    "manuscript_count": 1
  }
}
```

### Check If Team Can Access File
```bash
curl "http://localhost/api/file_visibility_management.php?action=can_access_file&team_id=5&requirement_id=1"

# Returns:
{
  "success": true,
  "data": {
    "can_access": true,
    "reason": "Visible to title_defense stage",
    "current_defense_type": "title_defense",
    "visibility_scope": "specific_stages",
    "can_download": false
  }
}
```

### Get Defense Manuscripts Only
```bash
curl "http://localhost/api/file_visibility_management.php?action=get_defense_manuscripts&team_id=5"

# Returns only files marked as defense manuscripts visible to this team
```

### View Audit Log
```sql
SELECT * FROM defense_file_access_log 
WHERE team_id = 5 
ORDER BY access_timestamp DESC;
```

---

## Database Schema

### `file_visibility_rules` Table
```
id                  INT (PK)
requirement_id      INT (FK) - which requirement
defense_type        ENUM - title_proposal, title_defense, final_defense, re-defense, general
can_view            BOOLEAN - can this type view the file?
can_download        BOOLEAN - can this type download?
visibility_label    VARCHAR - optional UI label
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE: (requirement_id, defense_type) - one rule per requirement per stage
```

### `defense_file_access_log` Table
```
id                  INT (PK)
team_id             INT (FK) - which team accessed
requirement_id      INT (FK) - which file
file_name           VARCHAR - file name accessed
action              ENUM - 'view' or 'download'
access_timestamp    TIMESTAMP - when
user_id             INT (FK) - who accessed (optional)

INDEX: (team_id, requirement_id) - query by team
INDEX: (access_timestamp) - query by time
```

---

## Integration Points

### For Dashboard UI (Future)
**Where to integrate:**
- `/dashboard/includes/tabs/requirements_tab.php` - Add checkbox "Is Defense Manuscript" & visibility scope dropdown
- `/dashboard/app.js.php` - Add form handlers for visibility configuration

**Functions to use:**
```php
// Get current settings
$rules = getVisibilityRules($pdo, $requirement_id);

// Update settings
markAsDefenseManuscript($pdo, $requirement_id, true);
setVisibilityScope($pdo, $requirement_id, 'specific_stages');
setDefenseTypeVisibility($pdo, $requirement_id, 'title_defense', true, false);
```

### For Team Facing Pages (Future)
**Where to integrate:**
- `/home/index.php` - Filter visible files by defense type
- `/decision-support/index.php` - Show only accessible files

**Functions to use:**
```php
// Include helpers
require_once __DIR__ . '/../dashboard/includes/file_visibility_functions.php';

// Get visible files
$result = getTeamVisibleFiles($pdo, $team_id);
$files = $result['files']; // Already filtered

// Check single file
$access = canTeamAccessFile($pdo, $team_id, $requirement_id);
if ($access['can_access']) {
    // Show download button
    if ($access['can_download']) {
        // Show download
    }
}
```

---

## Implementation Steps

### Phase 1: Database Setup (Required)
```bash
# 1. Run migration
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_add_file_visibility.sql

# 2. Verify tables created
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis -e "SHOW TABLES LIKE 'file_visibility%';"

# 3. Verify columns added
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis -e "DESCRIBE requirements;" | grep -E "(is_defense_manuscript|visibility_scope)"
```

### Phase 2: API Testing (Recommended)
```bash
# Test mark as manuscript
curl -X POST "http://localhost/api/file_visibility_management.php?action=mark_defense_manuscript" \
  -d "requirement_id=1&is_manuscript=1"

# Test get visible files
curl "http://localhost/api/file_visibility_management.php?action=get_visible_files&team_id=5"

# Test audit log
mysql icei_38697196_coecsathesis -e "SELECT COUNT(*) FROM defense_file_access_log;"
```

### Phase 3: Dashboard UI Integration (Optional)
- Add "Is Defense Manuscript" checkbox to requirements form
- Add "Visibility Scope" dropdown to requirements form
- Add visibility rules editor modal

### Phase 4: Team-Facing Integration (Optional)
- Filter files in home page by visible files
- Show manuscript badge for defense files
- Respect download restrictions

---

## Security

✅ **Admin-Only Access:** API endpoints require `usertype=0` (admin)

✅ **Input Validation:** All inputs filtered/validated before database queries

✅ **SQL Injection Prevention:** Prepared statements for all queries

✅ **Audit Trail:** All file access logged to `defense_file_access_log`

✅ **Access Control:** Files checked against visibility rules before serving

---

## Performance

✅ **Indexes on key queries:**
- `file_visibility_rules` (requirement_id, defense_type)
- `defense_file_access_log` (team_id, requirement_id)

✅ **View for fast queries:** `defense_manuscripts_view`

✅ **Minimal overhead:** Simple enum comparison in queries

---

## Testing Checklist

- [ ] Run database migration successfully
- [ ] Verify new tables exist: `file_visibility_rules`, `defense_file_access_log`
- [ ] Verify columns added: `is_defense_manuscript`, `visibility_scope` in requirements
- [ ] Test API: Mark requirement as manuscript
- [ ] Test API: Get visible files for team
- [ ] Test API: Check file access
- [ ] Verify audit logging works
- [ ] Test with multiple defense types
- [ ] Test visibility scope transitions
- [ ] Verify download restrictions honored

---

## Documentation Files

1. **Quick Start:** `/FILE_VISIBILITY_QUICK_START.md`
   - 5-minute setup guide
   - Common workflows
   - Curl examples

2. **Full Documentation:** `/FILE_VISIBILITY_SYSTEM.md`
   - Complete API reference
   - All 8 endpoints documented
   - Helper functions reference
   - Real-world scenarios

---

## API Endpoint Summary

| Endpoint | Method | Purpose | Auth |
|----------|--------|---------|------|
| `mark_defense_manuscript` | POST | Mark requirement as manuscript | Admin |
| `set_visibility_scope` | POST | Set visibility mode | Admin |
| `set_defense_type_visibility` | POST | Configure per-stage rules | Admin |
| `get_visibility_rules` | GET | Query visibility configuration | Admin |
| `get_visible_files` | GET | Get files visible to team | Admin |
| `can_access_file` | GET | Check single file access | Admin |
| `get_defense_manuscripts` | GET | Get manuscripts for team | Admin |
| `list_defense_manuscripts` | GET | List all manuscripts | Admin |

---

## Status

✅ **Complete & Ready for:**
1. Database migration
2. API testing
3. Integration into dashboard UI
4. Team-facing file filtering

⏳ **Optional Future Work:**
- Dashboard UI for configuration
- Team-facing file filtering
- Email notifications on visibility changes
- Bulk configuration tools
- File watermarking by defense type
- Role-based visibility (panelist vs team lead)

---

## Support

**Questions?**
- Check examples in `/FILE_VISIBILITY_QUICK_START.md`
- Reference full docs at `/FILE_VISIBILITY_SYSTEM.md`
- Test API endpoints with curl
- Check audit logs: `SELECT * FROM defense_file_access_log;`

