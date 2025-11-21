# Implementation Summary: Dynamic Requirements & Persistent Panelists

## What Has Been Implemented

### 1. ✅ Database Schema (Migration Ready)
**File:** `assets/setup/20251121_requirements_and_panelists.sql`

- ✅ Added `team_panelists` table - Persistent panelist assignments per team/defense_type
- ✅ Added `team_requirement_files` table - Support for multi-submission requirements
- ✅ Added `defense_type_overrides` table - Admin exceptions/special cases
- ✅ Modified `requirements` table - Added `requirement_type`, `allow_multiple_submissions`, `max_submissions`
- ✅ Modified `defense_schedules` table - Added `defense_type`, `related_requirement_files`, `admin_override_defense_type`
- ✅ Created `team_defense_status` view - Simplified defense type determination

### 2. ✅ Core Logic Functions
**File:** `dashboard/includes/defense_type_functions.php`

**Defense Type Detection:**
- `getTeamDefenseType()` - Auto-determines: title_proposal → title_defense → final_defense
- Checks admin overrides first
- Then evaluates team's title approval status
- Finally checks evaluation completion count

**Panelist Persistence:**
- `getPersistentPanelists()` - Retrieves locked panelists for a team/defense stage
- `lockPanelistAssignments()` - Locks panelists after scheduling (prevents algorithm changes)
- `unlockPanelistAssignments()` - Allows reassignment

**Admin Overrides:**
- `setDefenseTypeOverride()` - Force specific defense type for a team
- `removeDefenseTypeOverride()` - Remove override, return to auto-detection

**Multi-Submission Support:**
- `getTeamRequirementSubmissions()` - Fetch all submission files
- `getRequirementDetails()` - Check if requirement allows multiple submissions
- `linkMultipleFilesToDefense()` - Group multiple proposals into one defense
- `getDefenseScheduleFiles()` - Retrieve all files for a defense

### 3. ✅ Upload Handler Enhancement
**File:** `home/includes/upload_file.php`

- ✅ Imported defense_type_functions.php
- ✅ Checks requirement's `allow_multiple_submissions` flag
- ✅ For multi-submission requirements:
  - Inserts each file into `team_requirement_files` table
  - Tracks `submission_number` (1-3)
  - Enforces max submission limit
  - Returns `submission_number` and `max_submissions` in response
- ✅ For single-submission requirements:
  - Maintains original behavior (updates `team_requirements`)

### 4. ✅ Admin API Endpoints
**File:** `api/admin_overrides.php`

Complete REST API for admin operations:
- `get_team_defense_info` - Query team's current defense type & panelists
- `set_defense_type_override` - Force specific defense type
- `remove_defense_type_override` - Remove override
- `lock_panelists` - Lock panelist assignments
- `unlock_panelists` - Unlock for changes
- `get_team_requirement_submissions` - View all submitted files

All endpoints require admin authentication (usertype=0)

### 5. ✅ Requirements Management UI
**Files:** 
- `dashboard/includes/add_items.php`
- `dashboard/includes/edit_items.php`

When adding/editing requirements, now accepts:
- `requirement_type` - Select from: general, title_proposal, title_defense, final_defense
- `allow_multiple_submissions` - Toggle for multi-submission support
- `max_submissions` - Number 1-3 (enforced cap at 3)

## What Still Needs Integration

### 1. ⏳ Scheduler Integration
**File:** `dashboard/includes/run_scheduler.php`

**Required changes:**
1. Import `defense_type_functions.php` (already done ✅)
2. Modify `selectPanelists()` function:
   - Check for persistent panelists BEFORE algorithm
   - Use `getPersistentPanelists($pdo, $teamId, $defenseType)`
   - If locked panelists exist, return them immediately
   - Otherwise run algorithm as normal
3. Modify `saveScheduleToDatabase()` function:
   - After scheduling each team, call `lockPanelistAssignments()`
   - Determine `$defenseType` for each team using `getTeamDefenseType()`
   - For multi-submission requirements, populate `related_requirement_files` JSON
4. Determine defense_type for each scheduled defense and store in `defense_schedules.defense_type`

**Example implementation pattern:**
```php
// In selectPanelists()
$persistentPanelists = getPersistentPanelists($pdo, $teamId, $defenseType);
if (!empty($persistentPanelists)) {
    return $persistentPanelists;
}
// Otherwise proceed with algorithm

// In saveScheduleToDatabase()
foreach ($defenses as $defense) {
    $defenseType = getTeamDefenseType($pdo, $defense['team_id']);
    // Save to DB with defense_type
    // Then lock panelists
    lockPanelistAssignments($pdo, $defense['team_id'], $defenseType, 
        [$defense['panelist_ids'][0], $defense['panelist_ids'][1], $defense['panelist_ids'][2]], 
        $adminUserId);
}
```

### 2. ⏳ UI Components for Admin Dashboard

**For Requirements Tab:**
- Add form fields for `requirement_type`, `allow_multiple_submissions`, `max_submissions`
- Validation to ensure max_submissions ≤ 3

**For Team Management Section:**
- Display current defense type for each team
- Show override status if active
- Add button to set/remove override
- Show panelist lock status
- Buttons to lock/unlock panelists

**For Evaluation Forms:**
- When displaying multiple title proposals, show all 3 files
- Ensure evaluators can review all proposals for single defense

### 3. ⏳ Evaluation System Updates
**Files:** `decision-support/index.php`, `decision-support/submit_evaluation.php`

- Display linked requirement files from `related_requirement_files`
- When defense_type is shown, use it to display appropriate rubric
- Ensure all 3 proposals are available for review in one defense session

## Database Migration Instructions

### Prerequisites
- MySQL CLI access to your database
- Backup of database (recommended)

### Steps

1. **Apply the migration:**
```bash
cd /opt/lampp/htdocs/assets/setup
mysql -u root -p coecsa_thesis < 20251121_requirements_and_panelists.sql
```

2. **Verify migration success:**
```sql
-- Check new tables exist
SHOW TABLES LIKE '%panelist%';
SHOW TABLES LIKE '%requirement_file%';
SHOW TABLES LIKE '%override%';

-- Check new columns exist
DESCRIBE requirements;
DESCRIBE defense_schedules;

-- Check view exists
SHOW VIEWS LIKE 'team_defense_status';
```

3. **Set up sample requirements (optional):**
```sql
-- Mark existing requirements with their types
UPDATE requirements SET requirement_type = 'title_proposal' WHERE name LIKE '%title%' LIMIT 1;
UPDATE requirements SET requirement_type = 'final_defense' WHERE name LIKE '%manuscript%' LIMIT 1;
UPDATE requirements SET requirement_type = 'title_defense' WHERE name LIKE '%capstone%' LIMIT 1;

-- Enable multi-submission for title proposal
UPDATE requirements SET allow_multiple_submissions = 1, max_submissions = 3 
WHERE requirement_type = 'title_proposal' LIMIT 1;
```

## How to Test the Implementation

### Test 1: Multiple Title Submissions
1. Create requirement with:
   - `requirement_type = 'title_proposal'`
   - `allow_multiple_submissions = 1`
   - `max_submissions = 3`
2. Have student upload 3 different title PDFs
3. Verify in DB: Each file appears in `team_requirement_files` with submission_number 1, 2, 3
4. Try uploading 4th file - should be blocked

### Test 2: Persistent Panelists
1. Run scheduler to create first defense schedule
2. Check `team_panelists` table - should have entries with `locked = 0` initially
3. Admin locks panelists via API call: `POST /api/admin_overrides.php?action=lock_panelists`
4. Run scheduler again
5. Verify same panelists are reused (check selectPanelists logic)

### Test 3: Defense Type Override
1. Create a team with no approved titles (should be title_proposal)
2. Admin sets override to final_defense: `POST /api/admin_overrides.php?action=set_defense_type_override`
3. Query `getTeamDefenseType()` - should return final_defense
4. Admin removes override
5. Query again - should return title_proposal

### Test 4: Multi-File Defense Grouping
1. Create defense schedule for team with 3 title proposals
2. In `saveScheduleToDatabase()`, populate `related_requirement_files` JSON
3. Query `getDefenseScheduleFiles()` - should return all 3 files
4. In evaluation UI, display all 3 files for review

## Files Created
- ✅ `/opt/lampp/htdocs/assets/setup/20251121_requirements_and_panelists.sql` - Database migration
- ✅ `/opt/lampp/htdocs/dashboard/includes/defense_type_functions.php` - Core logic functions
- ✅ `/opt/lampp/htdocs/api/admin_overrides.php` - Admin API endpoints
- ✅ `/opt/lampp/htdocs/IMPLEMENTATION_GUIDE.md` - Complete implementation documentation

## Files Modified
- ✅ `/opt/lampp/htdocs/home/includes/upload_file.php` - Multi-submission support
- ✅ `/opt/lampp/htdocs/dashboard/includes/add_items.php` - Requirement type fields
- ✅ `/opt/lampp/htdocs/dashboard/includes/edit_items.php` - Requirement type fields
- ✅ `/opt/lampp/htdocs/dashboard/includes/run_scheduler.php` - Added import (needs further integration)

## Next Steps

1. **Apply database migration**
   - Run the SQL file in MySQL
   - Verify all tables and columns exist

2. **Integrate scheduler logic**
   - Implement persistent panelist checking in `selectPanelists()`
   - Implement panelist locking in `saveScheduleToDatabase()`
   - Set defense_type for each scheduled defense

3. **Build admin UI**
   - Add requirement type fields to admin requirements form
   - Add defense override section to team management
   - Add panelist lock controls

4. **Test end-to-end**
   - Student uploads 3 title proposals
   - Admin schedules defenses
   - Verify panelists stay same in re-scheduling
   - Verify evaluators see all 3 proposals

5. **Update evaluation system**
   - Display multiple proposals in one defense
   - Ensure rubric selection matches defense_type

## Key Benefits

✅ **Flexibility**: Admins can customize which requirements apply to which defense stages
✅ **Consistency**: Panelists won't change unexpectedly between title defense and final defense
✅ **Efficiency**: Multiple title proposals handled in one defense session
✅ **Control**: Admin overrides for special cases (extended timelines, medical leaves, etc.)
✅ **Traceability**: Audit trail of who locked panelists, when, and why

