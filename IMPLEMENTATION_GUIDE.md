# Dynamic Requirements & Persistent Panelists Implementation Guide

## Overview

This implementation enables:
1. **Dynamic Defense Type Mapping** - Map requirements to specific defense types (title_proposal, title_defense, final_defense)
2. **Persistent Panelist Assignments** - Once assigned to a team for a defense stage, panelists remain consistent unless admin override
3. **Multiple File Submissions** - Teams can submit up to 3 files for title proposal requirements
4. **Admin Overrides** - Special handling for edge cases via override mechanisms
5. **Single Defense for Multiple Proposals** - Multiple title proposals grouped into one defense schedule

## Database Changes

### 1. Migration File
File: `/opt/lampp/htdocs/assets/setup/20251121_requirements_and_panelists.sql`

Run this migration to:
- Add columns to `requirements` table
- Create `team_panelists` table for persistent assignments
- Create `team_requirement_files` table for multi-submission support
- Create `defense_type_overrides` table for admin exceptions
- Add new columns to `defense_schedules` table
- Create `team_defense_status` view

**To apply migration:**
```bash
mysql -u root -p coecsa_thesis < /opt/lampp/htdocs/assets/setup/20251121_requirements_and_panelists.sql
```

### 2. New Tables

#### `team_panelists`
Tracks persistent panelist assignments across defense stages:
- `team_id` - FK to teams
- `defense_type` - Enum: title_proposal, title_defense, final_defense
- `panelist_id` - FK to users
- `panelist_position` - 1 (primary), 2 (secondary), 3 (tertiary)
- `locked` - Boolean: locked assignments cannot be changed by algorithm
- `admin_override` - Boolean: manually set by admin
- `created_by` - Admin user who locked the assignment

#### `team_requirement_files`
Individual file submissions for multi-submission requirements:
- `team_id` - FK to teams
- `requirement_id` - FK to requirements
- `file_name`, `original_file_name`, `file_path` - File tracking
- `submission_number` - 1, 2, or 3 for multi-submission requirements
- `status` - pending, submitted, approved, rejected
- `submitted_by` - FK to users
- `submitted_at` - Timestamp

#### `defense_type_overrides`
Admin exceptions for defense type determination:
- `team_id` - FK to teams
- `override_type` - Force team to be treated as this type
- `reason` - Why override is needed
- `active` - Boolean
- `expires_at` - Optional expiry for temporary overrides
- `created_by` - Admin user

### 3. Modified Columns

#### `requirements` Table
- `requirement_type` - Enum: title_proposal, title_defense, final_defense, general
- `allow_multiple_submissions` - Boolean (max 3 files allowed)
- `max_submissions` - Int (1-3, typically 3 for title proposals)

#### `defense_schedules` Table
- `defense_type` - Enum: tracks type of defense being scheduled
- `related_requirement_files` - JSON array of team_requirement_files IDs (for multi-submission)
- `admin_override_defense_type` - Boolean: whether type was manually set

## Code Changes

### 1. Helper Functions
File: `/opt/lampp/htdocs/dashboard/includes/defense_type_functions.php`

Core functions:
- `getTeamDefenseType()` - Determine team's current defense type
- `getPersistentPanelists()` - Get locked panelists for a team's defense stage
- `lockPanelistAssignments()` - Lock panelist assignments to prevent changes
- `unlockPanelistAssignments()` - Unlock for reassignment
- `setDefenseTypeOverride()` - Admin override for defense type
- `removeDefenseTypeOverride()` - Remove override
- `getTeamRequirementSubmissions()` - Get all files for a requirement
- `getRequirementDetails()` - Check if requirement allows multi-submission
- `linkMultipleFilesToDefense()` - Group multiple proposals into one defense
- `getDefenseScheduleFiles()` - Get all files linked to a defense

**Usage:**
```php
require_once __DIR__ . '/../dashboard/includes/defense_type_functions.php';

// Get team's current defense type (auto-determines based on status or override)
$defenseType = getTeamDefenseType($pdo, $teamId);

// Get persistent panelists for title defense (will return locked assignments if any)
$panelists = getPersistentPanelists($pdo, $teamId, 'title_defense', $suggestedPanelists);

// Lock panelist assignments after scheduling
lockPanelistAssignments($pdo, $teamId, 'title_defense', [271, 270, 272], $adminUserId);
```

### 2. Upload Handler
File: `/opt/lampp/htdocs/home/includes/upload_file.php`

**Changes:**
- Checks requirement's `allow_multiple_submissions` flag
- For multi-submission requirements:
  - Inserts into `team_requirement_files` table with submission_number
  - Allows up to `max_submissions` (typically 3)
  - Returns submission_number in response
- For single-submission requirements:
  - Uses original logic (updates `team_requirements`)

**Response includes:**
```json
{
  "success": true,
  "submission_number": 2,
  "max_submissions": 3
}
```

### 3. Admin API Endpoints
File: `/opt/lampp/htdocs/api/admin_overrides.php`

Available endpoints (all require admin access):

#### GET `/api/admin_overrides.php?action=get_team_defense_info`
Parameters: `team_id`
Returns: Team's defense type, persistent panelists, active overrides

#### POST `/api/admin_overrides.php?action=set_defense_type_override`
Parameters: `team_id`, `override_type` (title_proposal|title_defense|final_defense), `reason`, `expires_at`
Effect: Force team to be treated as specific defense type

#### POST `/api/admin_overrides.php?action=remove_defense_type_override`
Parameters: `team_id`
Effect: Remove override, return to auto-detection

#### POST `/api/admin_overrides.php?action=lock_panelists`
Parameters: `team_id`, `defense_type`, `panelist_ids` (JSON array)
Effect: Lock panelist assignments to prevent algorithm changes

#### POST `/api/admin_overrides.php?action=unlock_panelists`
Parameters: `team_id`, `defense_type`
Effect: Unlock panelists for reassignment

#### GET `/api/admin_overrides.php?action=get_team_requirement_submissions`
Parameters: `team_id`, `requirement_id`
Returns: All submitted files for a team's requirement

### 4. Requirements Management
Files: 
- `/opt/lampp/htdocs/dashboard/includes/add_items.php`
- `/opt/lampp/htdocs/dashboard/includes/edit_items.php`

**Changes:**
- Added fields when adding/editing requirements:
  - `requirement_type` - Select: title_proposal, title_defense, final_defense, general
  - `allow_multiple_submissions` - Checkbox
  - `max_submissions` - Number input (1-3, default 1)

### 5. Scheduler Integration
File: `/opt/lampp/htdocs/dashboard/includes/run_scheduler.php`

**Pending implementation:**
- Import defense_type_functions.php
- Modify `selectPanelists()` function to:
  - Check for persistent (locked) panelists first
  - Use getPersistentPanelists() before algorithm assignment
  - Only suggest new panelists if no locked assignments exist
- Modify `saveScheduleToDatabase()` to:
  - Determine defense_type for each team
  - Call lockPanelistAssignments() after scheduling
  - Store related_requirement_files for multi-submission requirements

## How It Works

### Defense Type Determination

The system automatically determines a team's defense type:

1. **Check Admin Override** - If active override exists, use that type
2. **Check Approved Titles** - Query research_titles for approved_at
   - No approved titles → `title_proposal`
   - Has approved titles → Continue to step 3
3. **Check Evaluations** - Query for completed evaluations
   - ≥ 2 completed → `final_defense`
   - < 2 → `title_defense`

### Panelist Persistence

When panelists are assigned to a team's defense:

1. **Check for Locked Assignment** - Query team_panelists with locked=1
   - If found, use those panelists (respect across all future schedules)
2. **Algorithm Assignment** - If no locked assignment:
   - Generate schedule with algorithm
   - After saving, lock panelists via lockPanelistAssignments()
3. **Admin Override** - Admin can:
   - Unlock assignments for reassignment
   - Force specific panelists via lock

### Multiple Submissions

For title proposal submissions (up to 3):

1. **Check Requirement Settings** - allowMultipleSubmissions = 1, max_submissions = 3
2. **Upload Handler** - Inserts into team_requirement_files with submission_number
3. **Defense Scheduling** - All 3 proposals linked to single defense:
   - related_requirement_files = [file_id_1, file_id_2, file_id_3]
   - Evaluators review all proposals in one defense

## UI Integration Points

### Admin Dashboard - Requirements Tab

Add fields to requirement form:
```html
<div class="form-group">
  <label>Defense Type</label>
  <select name="requirement_type">
    <option value="general">General</option>
    <option value="title_proposal">Title Proposal</option>
    <option value="title_defense">Title Defense</option>
    <option value="final_defense">Final Defense</option>
  </select>
</div>

<div class="form-group">
  <label>
    <input type="checkbox" name="allow_multiple_submissions"> 
    Allow Multiple Submissions
  </label>
</div>

<div class="form-group">
  <label>Max Submissions (1-3)</label>
  <input type="number" name="max_submissions" min="1" max="3" value="1">
</div>
```

### Admin Dashboard - Team Management

Add override section:
```html
<div class="override-panel">
  <h5>Defense Type Override</h5>
  <p>Current Type: <span id="current-type"></span></p>
  <select id="override-type">
    <option value="">None</option>
    <option value="title_proposal">Title Proposal</option>
    <option value="title_defense">Title Defense</option>
    <option value="final_defense">Final Defense</option>
  </select>
  <button onclick="setOverride()">Set Override</button>
  
  <h5>Panelist Lock Status</h5>
  <div id="panelist-lock-info"></div>
  <button onclick="lockPanelists()">Lock Current Panelists</button>
  <button onclick="unlockPanelists()">Unlock for Changes</button>
</div>
```

## Example Usage

### Students Uploading 3 Title Proposals

```javascript
// Frontend: Upload multiple files
async function submitTitleProposal(files) {
  for (let i = 0; i < files.length; i++) {
    const formData = new FormData();
    formData.append('file', files[i]);
    formData.append('requirement_id', 41); // Title Proposal requirement
    formData.append('document_name', `Title Proposal ${i + 1}`);
    
    const response = await fetch('/home/includes/upload_file.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    console.log(`Upload ${result.submission_number}/${result.max_submissions} complete`);
  }
}
```

### Admin Handling Edge Case

```php
// Backend: Force team to final_defense even without 2 evaluations
require_once 'dashboard/includes/defense_type_functions.php';

setDefenseTypeOverride(
  $pdo, 
  $teamId = 15,
  $overrideType = 'final_defense',
  $userId = $adminId,
  $reason = 'Special case: extended timeline per department',
  $expiresAt = null // Permanent override
);

// Lock panelists so they don't change in next scheduler run
lockPanelistAssignments($pdo, 15, 'final_defense', [271, 270, 272], $adminId);
```

### Scheduler Using Persistent Panelists

```php
// In run_scheduler.php selectPanelists()
$teamId = $teamData['id'];
$defenseType = getTeamDefenseType($pdo, $teamId);

// Check for persistent assignments first
$persistentPanelists = getPersistentPanelists($pdo, $teamId, $defenseType);

if (!empty($persistentPanelists)) {
  // Use locked panelists
  return $persistentPanelists;
}

// Otherwise, run algorithm as before
$selectedPanelists = selectPanelistsByAlgorithm(...);

// After saving defense schedule, lock these panelists
lockPanelistAssignments($pdo, $teamId, $defenseType, $selectedPanelists, $adminUserId);
```

## Testing Checklist

- [ ] Run migration without errors
- [ ] Create requirement with defense_type = 'title_proposal', allow_multiple_submissions = 1
- [ ] Student uploads 3 title proposals, each tracked as submission_number 1, 2, 3
- [ ] Attempt 4th upload blocked with "Maximum submissions reached"
- [ ] Admin sets defense type override for a team
- [ ] Schedule generated, panelists locked
- [ ] Verify next scheduler run uses locked panelists
- [ ] Admin unlocks panelists, next run allows changes
- [ ] Multiple proposals appear in single defense schedule
- [ ] Evaluation form displays all 3 proposals

## Rollback Instructions

If needed to revert changes:

```sql
-- Remove override
DELETE FROM defense_type_overrides;
DROP TABLE IF EXISTS defense_type_overrides;

-- Remove persistent panelists
DELETE FROM team_panelists;
DROP TABLE IF EXISTS team_panelists;

-- Remove multi-submission files
DELETE FROM team_requirement_files;
DROP TABLE IF EXISTS team_requirement_files;

-- Remove columns from existing tables
ALTER TABLE requirements DROP COLUMN requirement_type;
ALTER TABLE requirements DROP COLUMN allow_multiple_submissions;
ALTER TABLE requirements DROP COLUMN max_submissions;

ALTER TABLE defense_schedules DROP COLUMN defense_type;
ALTER TABLE defense_schedules DROP COLUMN related_requirement_files;
ALTER TABLE defense_schedules DROP COLUMN admin_override_defense_type;
ALTER TABLE defense_schedules DROP KEY idx_defense_type;

DROP VIEW IF EXISTS team_defense_status;
```

## Support & Debugging

### Check Team's Current Defense Type
```php
$defenseType = getTeamDefenseType($pdo, $teamId);
echo "Team $teamId is in $defenseType stage";
```

### View Team's Persistent Panelists
```php
$stmt = $pdo->prepare("
  SELECT panelist_id, panelist_position, locked, admin_override 
  FROM team_panelists 
  WHERE team_id = ? AND defense_type = ?
");
$stmt->execute([$teamId, 'title_defense']);
$panelists = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Check Submitted Files for Requirement
```php
$submissions = getTeamRequirementSubmissions($pdo, $teamId, $requirementId);
foreach ($submissions as $file) {
  echo "Submission {$file['submission_number']}: {$file['original_file_name']} ({$file['status']})";
}
```

### View Active Admin Overrides
```php
$stmt = $pdo->prepare("
  SELECT * FROM defense_type_overrides 
  WHERE active = 1 AND (expires_at IS NULL OR expires_at > NOW())
");
$stmt->execute();
$overrides = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

