# File Visibility & Defense Manuscript Management System

## Overview

This system adds granular control over which files are visible to teams based on their current defense type (stage). You can now:

- **Mark requirements as "defense manuscripts"** - designate which files are the main thesis/project files to be defended
- **Control file visibility per defense type** - specify which defense stages (Title Proposal, Title Defense, Final Defense, Re-Defense) can see each file
- **Set access restrictions** - prevent certain files from being visible until a team reaches a specific defense stage
- **Audit file access** - track when teams access defense files

---

## Database Schema

### New Columns Added to `requirements` Table

```sql
-- Mark if this requirement produces the main defense manuscript
is_defense_manuscript BOOLEAN DEFAULT 0

-- Control which defense stages can see files from this requirement
visibility_scope ENUM('all_stages', 'specific_stages', 'current_stage_only', 'hidden') DEFAULT 'all_stages'
```

### New Tables Created

#### `file_visibility_rules`
Defines which files are visible to which defense types.

```
id                  INT (primary key)
requirement_id      INT (FK to requirements)
defense_type        ENUM (title_proposal, title_defense, final_defense, re-defense, general)
can_view            BOOLEAN (whether this type can view)
can_download        BOOLEAN (whether this type can download)
visibility_label    VARCHAR (optional label for UI)
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

**Unique Constraint:** `(requirement_id, defense_type)` - one rule per requirement per defense type

#### `defense_file_access_log`
Optional audit trail for file access.

```
id                  INT (primary key)
team_id             INT (FK to teams)
requirement_id      INT (FK to requirements)
file_name           VARCHAR
action              ENUM ('view', 'download')
access_timestamp    TIMESTAMP
user_id             INT (FK to users)
```

---

## Visibility Scopes

### 1. **all_stages** (Default)
- File is visible to all defense types
- Teams can access at any stage
- Example: "General Information" files

### 2. **specific_stages**
- Visibility controlled by `file_visibility_rules` table
- Each defense type has individual rules
- Most flexible: fine-grained control
- Example: Title Proposal manuscript only visible to Title Proposal stage

### 3. **current_stage_only**
- File visible ONLY during the team's current defense stage
- Not visible before or after
- Example: "See only during your current defense"

### 4. **hidden**
- Never visible to teams
- Only for admin use
- Example: "Draft materials"

---

## Configuration Examples

### Example 1: Mark File as Defense Manuscript
```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=mark_defense_manuscript" \
  -d "requirement_id=5&is_manuscript=1"
```

Result: Requirement #5 is marked as defense manuscript with blue icon in UI.

### Example 2: Set Specific Stage Visibility
```bash
# Requirement visible to Title Proposal stage
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=5&defense_type=title_proposal&can_view=1&can_download=1&visibility_label=Title Proposal Manuscript"

# Requirement visible to Title Defense onwards
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=6&defense_type=title_defense&can_view=1&can_download=1"
```

### Example 3: Set Scope to Current Stage Only
```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=7&visibility_scope=current_stage_only"
```

### Example 4: Hide Files from Teams
```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=8&visibility_scope=hidden"
```

---

## API Endpoints

### GET Endpoints

#### 1. Get Visibility Rules
```
GET /api/file_visibility_management.php?action=get_visibility_rules&requirement_id=5

Response:
{
  "success": true,
  "data": {
    "requirement": {
      "id": 5,
      "name": "Thesis Manuscript",
      "is_defense_manuscript": true,
      "visibility_scope": "specific_stages"
    },
    "rules": [
      {
        "defense_type": "title_proposal",
        "can_view": 1,
        "can_download": 1,
        "visibility_label": "Thesis Manuscript for Title Proposal"
      },
      {
        "defense_type": "title_defense",
        "can_view": 1,
        "can_download": 0,
        "visibility_label": "Read-only for Title Defense"
      }
    ]
  }
}
```

#### 2. Get Visible Files for Team
```
GET /api/file_visibility_management.php?action=get_visible_files&team_id=10

Response:
{
  "success": true,
  "data": {
    "team": {
      "id": 10,
      "name": "Team Alpha",
      "program": "Computer Science",
      "defense_type": "title_defense"
    },
    "current_defense_type": "title_defense",
    "files": [
      {
        "id": 5,
        "name": "Thesis Manuscript",
        "is_defense_manuscript": 1,
        "file_name": "thesis_manuscript_final.pdf",
        "status": "approved",
        "can_view": 1,
        "can_download": 0,
        "visibility_label": "Read-only for Title Defense"
      }
    ],
    "file_count": 1,
    "manuscript_count": 1
  }
}
```

#### 3. Check File Access
```
GET /api/file_visibility_management.php?action=can_access_file&team_id=10&requirement_id=5

Response:
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

#### 4. Get Defense Manuscripts Only
```
GET /api/file_visibility_management.php?action=get_defense_manuscripts&team_id=10

Response:
{
  "success": true,
  "data": {
    "team_id": 10,
    "current_defense_type": "title_defense",
    "manuscripts": [
      {
        "id": 5,
        "name": "Thesis Manuscript",
        "file_name": "thesis_final.pdf",
        "status": "approved"
      }
    ],
    "count": 1
  }
}
```

#### 5. List All Defense Manuscripts (Admin)
```
GET /api/file_visibility_management.php?action=list_defense_manuscripts

Response:
{
  "success": true,
  "data": {
    "manuscripts": [
      {
        "id": 5,
        "name": "Thesis Manuscript",
        "is_defense_manuscript": 1,
        "visibility_scope": "specific_stages",
        "num_visible_types": 2,
        "visible_types": "title_proposal, title_defense"
      }
    ],
    "total": 1
  }
}
```

### POST Endpoints

#### 1. Mark Defense Manuscript
```
POST /api/file_visibility_management.php?action=mark_defense_manuscript

Body:
{
  "requirement_id": 5,
  "is_manuscript": true
}
```

#### 2. Set Visibility Scope
```
POST /api/file_visibility_management.php?action=set_visibility_scope

Body:
{
  "requirement_id": 5,
  "visibility_scope": "specific_stages"
}
```

#### 3. Set Defense Type Visibility
```
POST /api/file_visibility_management.php?action=set_defense_type_visibility

Body:
{
  "requirement_id": 5,
  "defense_type": "title_proposal",
  "can_view": true,
  "can_download": true,
  "visibility_label": "Title Proposal Manuscript"
}
```

---

## Helper Functions

Available in `/dashboard/includes/file_visibility_functions.php`:

```php
// Check if team can access a file
canTeamAccessFile($pdo, $team_id, $requirement_id);
// Returns: ['can_access' => bool, 'reason' => string, 'can_download' => bool]

// Get all visible files for a team
getTeamVisibleFiles($pdo, $team_id);
// Returns: ['team' => array, 'files' => array, 'current_defense_type' => string]

// Get defense manuscripts only
getTeamDefenseManuscripts($pdo, $team_id);
// Returns: array of manuscript records

// Mark requirement as manuscript
markAsDefenseManuscript($pdo, $requirement_id, $is_manuscript);
// Returns: bool

// Set visibility scope
setVisibilityScope($pdo, $requirement_id, $scope);
// Returns: bool

// Set defense type visibility
setDefenseTypeVisibility($pdo, $requirement_id, $defense_type, $can_view, $can_download, $label);
// Returns: bool

// Get visibility rules
getVisibilityRules($pdo, $requirement_id);
// Returns: array of rules

// Log file access
logFileAccess($pdo, $team_id, $requirement_id, $file_name, $action, $user_id);
// Returns: bool

// Get file access history
getFileAccessHistory($pdo, $team_id, $limit);
// Returns: array of access logs
```

---

## Usage Workflow

### For Administrators

1. **Go to Dashboard → Defense Management → Requirements**
2. **Mark Defense Manuscripts:**
   - Open requirement edit form
   - Check "Is Defense Manuscript" checkbox
   - Save

3. **Configure Visibility:**
   - Set "Visibility Scope" to one of:
     - `all_stages` - visible to all defense stages
     - `specific_stages` - configure per defense type (recommended)
     - `current_stage_only` - only visible during current defense
     - `hidden` - hidden from all teams

4. **Set Per-Defense-Type Rules:**
   - For each defense type that should see the file:
     - Enable "Can View" checkbox
     - Enable "Can Download" (optional) checkbox
     - Add custom label (optional)

### For Teams

1. **Go to Home → Requirement Checker**
2. **View Visible Files:**
   - Only files applicable to current defense stage show
   - Defense manuscripts marked with special indicator
   - Download/view permissions honored

3. **View Defense Manuscripts:**
   - Special "Defense Manuscripts" section
   - Shows only files marked as main manuscript
   - Respects visibility scope

---

## Real-World Scenarios

### Scenario 1: Thesis Submitted Once, Reviewed at Each Stage
```
Requirement: "Thesis Manuscript"
- is_defense_manuscript: TRUE
- visibility_scope: specific_stages

Rules:
- title_proposal: can_view=1, can_download=1 (submit here)
- title_defense: can_view=1, can_download=0 (read-only during defense)
- final_defense: can_view=1, can_download=0 (read-only during defense)
- re-defense: can_view=1, can_download=0 (if needed)
```

### Scenario 2: Proposal Only for First Stage
```
Requirement: "Project Proposal"
- is_defense_manuscript: FALSE
- visibility_scope: current_stage_only

Rules:
- title_proposal: can_view=1, can_download=1
- title_defense: can_view=0, can_download=0 (hidden after)
- final_defense: can_view=0, can_download=0
```

### Scenario 3: Progressive Manuscript Development
```
Requirement: "Final Thesis"
- is_defense_manuscript: TRUE
- visibility_scope: specific_stages

Rules:
- title_proposal: can_view=0 (not yet submitted)
- title_defense: can_view=1, can_download=0 (read-only)
- final_defense: can_view=1, can_download=1 (download for edits)
- re-defense: can_view=1, can_download=1 (allow revisions)
```

### Scenario 4: Admin-Only Draft Materials
```
Requirement: "Internal Review Notes"
- is_defense_manuscript: FALSE
- visibility_scope: hidden

Rules: (all set to 0)
```

---

## Audit & Logging

All file access is logged to `defense_file_access_log`:

```
SELECT * FROM defense_file_access_log 
WHERE team_id = 10 
ORDER BY access_timestamp DESC;
```

Logs show:
- Which team accessed which file
- When they accessed it (view vs download)
- Which user initiated the access
- Timestamp

---

## Database Migration

Run the migration to enable this feature:

```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_add_file_visibility.sql
```

Or apply manually:

```sql
-- Add columns to requirements
ALTER TABLE requirements 
ADD COLUMN is_defense_manuscript BOOLEAN DEFAULT 0 AFTER requirement_type,
ADD COLUMN visibility_scope ENUM('all_stages', 'specific_stages', 'current_stage_only', 'hidden') DEFAULT 'all_stages' AFTER is_defense_manuscript;

-- Create tables and view (see migration file)
```

---

## Implementation Checklist

- [ ] Run database migration
- [ ] Verify new columns in requirements table
- [ ] Verify new tables created (file_visibility_rules, defense_file_access_log)
- [ ] Test API endpoints with admin account
- [ ] Mark first defense manuscript
- [ ] Set visibility rules for all defense types
- [ ] Test with team account - verify correct files show
- [ ] Check audit logs for file access
- [ ] Integrate UI components into dashboard

---

## Future Enhancements

Possible additions:
- Watermark files based on defense type
- Automated visibility transitions as teams progress
- Bulk visibility rule configuration
- File version tracking per defense stage
- Email notifications when files become visible
- Export file access reports
- Role-based visibility (panelist vs team leader)

