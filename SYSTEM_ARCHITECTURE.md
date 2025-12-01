# System Architecture: Dynamic Requirements & Persistent Panelists

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│  STUDENTS                                                    │
│  - Upload Requirements (1-3 files for title proposals)      │
│  - View Defense Type Status                                 │
│  - Submit for Evaluation                                    │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  UPLOAD HANDLER (home/includes/upload_file.php)             │
│  - Check requirement type                                   │
│  - Validate multi-submission flag                           │
│  - Insert into team_requirement_files or team_requirements  │
│  - Return submission_number                                 │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  DATABASE LAYER                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Table: requirements                                    │ │
│  │ - requirement_type (title_proposal, title_defense...) │ │
│  │ - allow_multiple_submissions (boolean)                │ │
│  │ - max_submissions (1-3)                               │ │
│  └────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Table: team_requirement_files                          │ │
│  │ - Individual submissions for multi-submission reqs     │ │
│  │ - submission_number (1, 2, 3)                         │ │
│  │ - status (pending, submitted, approved, rejected)     │ │
│  └────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Table: team_panelists                                  │ │
│  │ - Persistent assignments per team/defense_type        │ │
│  │ - locked (boolean) - prevents algorithm changes       │ │
│  │ - admin_override (boolean) - manually set by admin    │ │
│  └────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Table: defense_type_overrides                          │ │
│  │ - Admin exceptions for special cases                  │ │
│  │ - Can be temporary (expires_at) or permanent         │ │
│  └────────────────────────────────────────────────────────┘ │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  CORE LOGIC (dashboard/includes/defense_type_functions.php) │
│  - getTeamDefenseType()                                      │
│  - getPersistentPanelists()                                  │
│  - lockPanelistAssignments()                                 │
│  - setDefenseTypeOverride()                                  │
│  - getTeamRequirementSubmissions()                           │
│  - And 5+ more functions                                     │
└──────────────────┬──────────────────────────────────────────┘
                   │
    ┌──────────────┴──────────────┬──────────────┐
    │                             │              │
    ▼                             ▼              ▼
┌─────────────┐         ┌──────────────────┐  ┌────────────────┐
│  SCHEDULER  │         │  ADMIN API       │  │  REQUIREMENTS  │
│  run_sched  │         │  admin_overrides │  │  MANAGEMENT    │
│  .php       │         │  .php            │  │  add/edit_     │
│             │         │                  │  │  items.php     │
│ 1. Check    │         │ Endpoints:       │  │                │
│    persistent         │ - Get defense    │  │ - Set type     │
│    panelists          │   info           │  │ - Enable       │
│ 2. Lock     │         │ - Set override   │  │   multi-       │
│    panelists          │ - Lock panelists │  │   submission   │
│    after save         │ - Get            │  │ - Set max      │
│ 3. Handle              │   submissions    │  │   submissions  │
│    multi-file         │                  │  │                │
│    defense            │ Auth: Admin only │  │                │
│                       │                  │  │                │
└──────────────┘        └──────────────────┘  └────────────────┘
    │                             │                     │
    └──────────────┬──────────────┴─────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  EVALUATION SYSTEM                                           │
│  - decision-support/index.php                               │
│  - decision-support/submit_evaluation.php                   │
│  - Display defense_type-specific rubrics                    │
│  - Show all 3 proposals for multi-submission defenses       │
│  - Evaluators submit single evaluation for all proposals    │
└─────────────────────────────────────────────────────────────┘
```

## Data Flow: Student Submitting 3 Title Proposals

```
STUDENT INTERFACE
    │
    ├─ Upload Proposal 1 (PDF)
    │         │
    │         ▼
    │  home/includes/upload_file.php
    │  - Checks: requirement.allow_multiple_submissions = 1
    │  - Inserts into team_requirement_files (submission_number = 1)
    │  - Returns: { success: true, submission_number: 1, max_submissions: 3 }
    │         │
    │         ▼
    │  DATABASE: team_requirement_files
    │           [Record 1 - submission_number: 1, status: submitted]
    │
    ├─ Upload Proposal 2 (PDF)
    │         │
    │         ▼
    │  home/includes/upload_file.php
    │  - Checks: current count = 1, max = 3 ✓
    │  - Inserts into team_requirement_files (submission_number = 2)
    │  - Returns: { success: true, submission_number: 2, max_submissions: 3 }
    │         │
    │         ▼
    │  DATABASE: team_requirement_files
    │           [Record 2 - submission_number: 2, status: submitted]
    │
    ├─ Upload Proposal 3 (PDF)
    │         │
    │         ▼
    │  home/includes/upload_file.php
    │  - Checks: current count = 2, max = 3 ✓
    │  - Inserts into team_requirement_files (submission_number = 3)
    │  - Returns: { success: true, submission_number: 3, max_submissions: 3 }
    │         │
    │         ▼
    │  DATABASE: team_requirement_files
    │           [Record 3 - submission_number: 3, status: submitted]
    │
    └─ Try to Upload Proposal 4 (PDF)
              │
              ▼
       home/includes/upload_file.php
       - Checks: current count = 3, max = 3 ✗
       - Returns: { success: false, error: "Maximum submissions reached" }
```

## Data Flow: Scheduler Handling Persistent Panelists

```
ADMIN RUNS SCHEDULER
    │
    ▼
dashboard/includes/run_scheduler.php
    │
    ├─ For each team:
    │   │
    │   ├─ Step 1: Get team's defense type
    │   │  defense_type = getTeamDefenseType($pdo, $teamId)
    │   │  [Checks: override → approved_titles → evaluations]
    │   │
    │   ├─ Step 2: Select panelists
    │   │  selectPanelists($teamId, $defenseType)
    │   │   │
    │   │   ├─ Check for persistent assignment
    │   │   │  persistentPanelists = getPersistentPanelists(...)
    │   │   │  IF found and locked → RETURN immediately
    │   │   │
    │   │   └─ IF not locked → Run algorithm
    │   │      selectedPanelists = [271, 270, 272]
    │   │
    │   ├─ Step 3: Save to database
    │   │  saveScheduleToDatabase()
    │   │   │
    │   │   ├─ INSERT into defense_schedules
    │   │   │  - team_id: 5
    │   │   │  - panelist_id: 271
    │   │   │  - panelist_id2: 270
    │   │   │  - panelist_id3: 272
    │   │   │  - defense_type: 'title_defense'
    │   │   │  - related_requirement_files: [1, 2, 3] (if multi-submission)
    │   │   │
    │   │   └─ Lock panelists for future runs
    │   │      lockPanelistAssignments($teamId, 'title_defense', [271, 270, 272])
    │   │      → INSERT into team_panelists with locked = 1
    │   │
    │   └─ Result: Team 5 scheduled with locked panelists
    │
    ▼
NEXT SCHEDULE RUN (2 weeks later)
    │
    ├─ For Team 5 again:
    │   selectPanelists(team_id=5, defense_type=final_defense)
    │   │
    │   ├─ Check persistent: getPersistentPanelists(...)
    │   │  Query: SELECT * FROM team_panelists 
    │   │          WHERE team_id=5 AND defense_type=title_defense AND locked=1
    │   │  Result: [271, 270, 272] ← SAME PANELISTS!
    │   │
    │   └─ RETURN [271, 270, 272]
    │      (Algorithm doesn't run, panelists unchanged)
    │
    ▼
RESULT: Same panelists for Team 5 across both defenses ✓
```

## Data Flow: Admin Setting Defense Override

```
ADMIN DASHBOARD
    │
    ▼
Admin clicks "Set Override"
    │
    ├─ Team: 15
    ├─ Override Type: "final_defense"
    ├─ Reason: "Medical extension - 6 month delay"
    ├─ Expires: null (permanent)
    │
    ▼
POST /api/admin_overrides.php?action=set_defense_type_override
    │
    ├─ Validate: admin access ✓
    │
    ├─ setDefenseTypeOverride(...)
    │   │
    │   └─ INSERT into defense_type_overrides
    │      {
    │        team_id: 15,
    │        override_type: 'final_defense',
    │        reason: 'Medical extension - 6 month delay',
    │        active: 1,
    │        expires_at: null,
    │        created_by: 1 (admin_id)
    │      }
    │
    ▼
NEXT TIME SYSTEM CHECKS TEAM 15's DEFENSE TYPE:
    │
    └─ getTeamDefenseType(team_id=15)
        │
        ├─ Query defense_type_overrides
        │  WHERE team_id=15 AND active=1 AND expires_at IS NULL
        │
        └─ Result: 'final_defense' (OVERRIDE ACTIVE)
           (Even though team has no approved title or evaluations)
```

## Defense Type Auto-Detection Algorithm

```
getTeamDefenseType($pdo, $teamId)
    │
    ├─ STEP 1: Check for active override
    │  Query: SELECT override_type FROM defense_type_overrides 
    │          WHERE team_id = ? AND active = 1 
    │          AND (expires_at IS NULL OR expires_at > NOW())
    │  IF found: RETURN $override_type
    │
    └─ STEP 2: Check approved titles
       Query: SELECT COUNT(*) FROM research_titles 
              WHERE team_id = ? AND approved_at IS NOT NULL
       │
       ├─ IF count = 0:
       │  RETURN 'title_proposal'
       │  (Team has no approved title)
       │
       └─ IF count > 0: Continue to Step 3
    
    ├─ STEP 3: Check completed evaluations
    │  Query: SELECT COUNT(DISTINCT panelist_id) FROM evaluation_per_panel
    │          WHERE defense_schedule_id IN (
    │            SELECT id FROM defense_schedules WHERE team_id = ?
    │          )
    │
    │  ├─ IF count ≥ 2:
    │  │  RETURN 'final_defense'
    │  │  (Has title + 2+ evaluations)
    │  │
    │  └─ IF count < 2:
    │     RETURN 'title_defense'
    │     (Has title but not yet 2 evaluations)
    │
    └─ STEP 4: Default fallback
       RETURN 'title_proposal'
       (Shouldn't reach here in normal operation)
```

## Panelist Assignment Workflow

```
1. INITIAL ASSIGNMENT (First defense scheduling)
   ├─ Algorithm selects: Panelist 1, 2, 3
   └─ saveScheduleToDatabase() calls:
      lockPanelistAssignments($teamId, $defenseType, [...], $adminId)
      │
      └─ Status: locked = 0, admin_override = 0
         (Algorithmically assigned, not yet locked)

2. ADMIN LOCKS PANELISTS
   ├─ Admin opens team details
   ├─ Sees current panelists: [271, 270, 272]
   └─ Clicks "Lock Panelists"
      │
      └─ Updates team_panelists SET locked = 1
         Status: locked = 1, admin_override = 1 if manually set

3. NEXT SCHEDULER RUN
   ├─ selectPanelists() checks:
   │  getPersistentPanelists(...) → finds locked row
   │  │
   │  └─ RETURNS [271, 270, 272] immediately
   │
   └─ Algorithm SKIPPED for this team
      Panelists unchanged ✓

4. ADMIN WANTS TO CHANGE
   ├─ Clicks "Unlock Panelists"
   └─ Updates team_panelists SET locked = 0
      │
      └─ Next scheduler run: Algorithm can reassign
         Panelists may change

5. ADMIN MANUALLY ASSIGNS
   ├─ Admin selects: Primary: 305, Secondary: 310, Tertiary: 315
   └─ Calls lockPanelistAssignments() with new IDs
      │
      └─ Replaces records in team_panelists
         Status: locked = 1, admin_override = 1 (manually set)
         Next scheduler: These panelists used ✓
```

## Database Query Patterns

### Pattern 1: Determine Current Defense Type
```sql
-- Get team's defense type (with override precedence)
SELECT COALESCE(
  (SELECT override_type FROM defense_type_overrides 
   WHERE team_id = ? AND active = 1 
   AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1),
  CASE 
    WHEN (SELECT COUNT(*) FROM research_titles 
          WHERE team_id = ? AND approved_at IS NOT NULL) = 0 
    THEN 'title_proposal'
    WHEN (SELECT COUNT(DISTINCT epp.panelist_id) FROM evaluation_per_panel epp
          JOIN defense_schedules ds ON epp.defense_schedule_id = ds.id
          WHERE ds.team_id = ? AND epp.created_at IS NOT NULL) >= 2 
    THEN 'final_defense'
    ELSE 'title_defense'
  END
) as defense_type;
```

### Pattern 2: Get Team's Submission Files
```sql
-- Get all submission files for a team's requirement
SELECT * FROM team_requirement_files 
WHERE team_id = ? 
  AND requirement_id = ? 
  AND deleted_at IS NULL
ORDER BY submission_number ASC, submitted_at DESC;
```

### Pattern 3: Get Persistent Panelists
```sql
-- Check if team has locked panelists for a defense stage
SELECT panelist_id FROM team_panelists 
WHERE team_id = ? 
  AND defense_type = ? 
  AND locked = 1
ORDER BY panelist_position ASC;
```

### Pattern 4: Find Teams Needing Override Check
```sql
-- Find all teams with active overrides
SELECT t.id, t.name, dto.override_type, dto.reason, dto.created_by
FROM teams t
LEFT JOIN defense_type_overrides dto ON t.id = dto.team_id 
WHERE dto.active = 1 
  AND (dto.expires_at IS NULL OR dto.expires_at > NOW())
ORDER BY t.id;
```

## Integration Points

### With Evaluation System
```
When evaluator opens defense evaluation:

1. Query defense_schedules WHERE id = ?
   - Get defense_type
   - Get related_requirement_files (if JSON not null)

2. IF related_requirement_files has values:
   - Parse JSON array: [1, 2, 3]
   - Fetch from team_requirement_files WHERE id IN (1, 2, 3)
   - Display all 3 proposals in tabs/carousel
   - Evaluator sees all proposals in one session

3. Use defense_type to determine:
   - Which rubric to apply
   - What criteria are evaluated
```

### With Requirements Tab
```
When student views requirements:

1. Query requirements WHERE requirement_type IN ('title_proposal', 'title_defense', 'final_defense')

2. Filter by student's current defense_type:
   defense_type = getTeamDefenseType($pdo, $studentTeamId)
   
3. Display only relevant requirements:
   - Title proposal students see: title_proposal requirements
   - Title defense students see: title_defense requirements
   - Final defense students see: final_defense requirements

4. For multi-submission requirements:
   - Show submission counter: "2 of 3"
   - List each submission with status
```

## Security & Validation

### Admin-Only Endpoints
All `/api/admin_overrides.php` endpoints check:
```php
if ($userType != 0) { // 0 = admin
    exit with error
}
```

### Input Validation
- Team ID: Must be valid int, exists in database
- Panelist IDs: Must be valid int array, exist in users table
- Defense Type: Must be one of enum values
- Max Submissions: Capped at 3 in database
- Override Expiry: Validated as future datetime

### Audit Trail
Actions logged:
- defense_type_overrides.created_by - Who set override
- defense_type_overrides.created_at - When set
- team_panelists.created_by - Who locked panelists
- team_requirement_files.submitted_by - Who submitted file

