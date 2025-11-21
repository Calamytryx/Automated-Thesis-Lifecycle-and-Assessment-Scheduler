# Program-Specific Manuscript Requirements System

## Overview

This system allows you to configure which manuscript requirements are needed for **specific programs and defense types**.

**Example:**
- "Final Manuscript" (requirement #5) is required ONLY for:
  - **WebDevelopment** program at **Final Defense**
  - **Mobile** program at **Final Defense**
- Other programs don't need it, or need it at different stages

---

## Problem Solved

**Before:** All programs used the same manuscript requirements at the same stages
- Title Proposal: Thesis required for ALL programs
- Title Defense: Thesis required for ALL programs
- Final Defense: Thesis required for ALL programs

**Now:** Each program can have different manuscript requirements

**Example:**
```
Program          Title Proposal     Title Defense      Final Defense
─────────────────────────────────────────────────────────────────────
WebDevelopment   ✓ Proposal         ✓ System Design    ✓ Final Code + Docs
Mobile           ✓ Proposal         ✓ App Design       ✓ Final App
Data Science     ✓ Proposal         ✓ Analysis         ✗ (not needed)
```

---

## Database Schema

### `program_manuscript_requirements` Table

```sql
id (PK)
requirement_id (FK) -- which manuscript (e.g., requirement #5)
program_id (FK)     -- which program
defense_type (ENUM) -- which defense stage
is_required (BOOL)  -- is this manuscript required?
submission_stage    -- when: 'before_defense', 'at_defense', 'optional'
can_revise_after    -- can team revise after this stage?
visibility_to_panelist -- should panelists see it at defense?
```

### Unique Constraint
One entry per `(requirement_id, program_id, defense_type)` combination

---

## API Endpoints (8 total)

### 1. Add Manuscript Requirement
```bash
POST /api/manuscript_requirements.php?action=add_manuscript_requirement

Body:
{
  "requirement_id": 5,
  "program_id": 2,
  "defense_type": "final_defense",
  "is_required": true,
  "submission_stage": "before_defense",
  "can_revise_after": false,
  "visibility_to_panelist": true
}

# Make "Final Manuscript" required for WebDevelopment at Final Defense
```

### 2. Remove Manuscript Requirement
```bash
POST /api/manuscript_requirements.php?action=remove_manuscript_requirement

Body:
{
  "requirement_id": 5,
  "program_id": 2,
  "defense_type": "final_defense"
}
```

### 3. Get Requirement's Mappings
```bash
GET /api/manuscript_requirements.php?action=get_requirement_manuscripts&requirement_id=5

# Returns: All programs + defense types where this manuscript is required
```

### 4. Get Program's Manuscripts for Stage
```bash
GET /api/manuscript_requirements.php?action=get_program_defense_manuscripts&program_id=2&defense_type=final_defense

# Returns: All manuscripts required for WebDev at Final Defense
```

### 5. Get Team's Applicable Manuscripts
```bash
GET /api/manuscript_requirements.php?action=get_team_manuscripts&team_id=10

# Returns: Manuscripts applicable to Team 10 (based on their program + current defense stage)
```

### 6. Get Programs for Manuscript
```bash
GET /api/manuscript_requirements.php?action=get_programs_for_manuscript&requirement_id=5

# Returns: All programs + which have this manuscript configured
```

### 7. Bulk Update Manuscripts
```bash
POST /api/manuscript_requirements.php?action=bulk_update_manuscripts

Body:
{
  "requirement_id": 5,
  "defense_type": "final_defense",
  "program_ids": [2, 3, 4]  # WebDev, Mobile, etc
}

# Make requirement 5 required for WebDev, Mobile, etc at Final Defense
```

### 8. List All Manuscripts
```bash
GET /api/manuscript_requirements.php?action=list_all_manuscripts

# Returns: All manuscripts with program + defense type coverage
```

---

## Helper Functions

### Get Team's Applicable Manuscripts
```php
require_once __DIR__ . '/dashboard/includes/manuscript_requirements_functions.php';

$result = getTeamApplicableManuscripts($pdo, $team_id);
// Returns: ['manuscripts' => [...], 'current_defense_type' => '...', 'program_name' => '...']

foreach ($result['manuscripts'] as $manuscript) {
    echo $manuscript['name']; // "Final Manuscript", etc
}
```

### Check if Manuscript Applies to Team
```php
$applicable = isManuscriptApplicableToTeam($pdo, $team_id=10, $requirement_id=5);

if ($applicable['is_applicable']) {
    // Show manuscript to team
} else {
    // Hide it - not required for this program/stage
    echo $applicable['reason']; // "Not required for WebDev at Title Defense"
}
```

### Get All Manuscripts for a Program
```php
$manuscripts = getProgramManuscripts($pdo, $program_id=2);
// Returns all manuscripts configured for this program

foreach ($manuscripts as $manuscript) {
    echo $manuscript['name']; // "Final Manuscript", etc
    echo $manuscript['defense_types']; // "final_defense, re-defense"
}
```

---

## Configuration Examples

### Example 1: Final Manuscript for WebDev & Mobile Only

```bash
# Get requirement ID for "Final Manuscript"
mysql icei_38697196_coecsathesis -e "SELECT id FROM requirements WHERE name='Final Manuscript';" 
# Result: 5

# Get program IDs for WebDev and Mobile
mysql icei_38697196_coecsathesis -e "SELECT id FROM programs WHERE name IN ('WebDevelopment', 'Mobile');" 
# Result: 2, 3

# Add mapping for WebDev
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=2&defense_type=final_defense&is_required=1"

# Add mapping for Mobile
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=3&defense_type=final_defense&is_required=1"

# Result: Final Manuscript now required ONLY for WebDev and Mobile at Final Defense
# All other programs: won't see this requirement
```

### Example 2: Different Manuscript per Stage

```bash
# Requirement 1: Proposal (for Title Proposal only)
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=1&program_id=2&defense_type=title_proposal&is_required=1"

# Requirement 5: Final Manuscript (for Final Defense)
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=2&defense_type=final_defense&is_required=1"

# Result: 
#   - Title Proposal stage: Team sees Proposal requirement
#   - Title Defense stage: No manuscripts shown
#   - Final Defense stage: Team sees Final Manuscript requirement
```

### Example 3: Manuscript Not Required for a Program

```bash
# DataScience program doesn't need final manuscript
# Just don't add mapping for (requirement_id=5, program_id=4, defense_type=final_defense)

# Result: DataScience teams won't see "Final Manuscript" requirement
```

---

## Filter Team's Visible Manuscripts

### In Decision Support Page

```php
// Get applicable manuscripts for this team
require_once __DIR__ . '/dashboard/includes/manuscript_requirements_functions.php';

$result = getTeamApplicableManuscripts($pdo, $_SESSION['team_id'][0]);

if ($result['success']) {
    foreach ($result['manuscripts'] as $manuscript) {
        // Show only this manuscript
        echo "<div class='manuscript'>";
        echo $manuscript['name'];
        echo "</div>";
    }
} else {
    echo "No manuscripts required for your program at this stage";
}
```

### In Home Page Requirements Tab

```php
// Filter requirements to show only applicable manuscripts
require_once __DIR__ . '/dashboard/includes/manuscript_requirements_functions.php';

$result = getTeamApplicableManuscripts($pdo, $team_id);

// Get IDs of applicable manuscripts
$applicableIds = array_column($result['manuscripts'], 'id');

// Filter requirements list
$applicableRequirements = array_filter($requirements, function($req) use ($applicableIds) {
    return in_array($req['id'], $applicableIds);
});

// Show only applicable requirements
foreach ($applicableRequirements as $req) {
    // Render requirement...
}
```

---

## SQL Queries

### All Manuscripts for WebDev Program
```sql
SELECT 
    r.id,
    r.name,
    pmr.defense_type,
    pmr.is_required
FROM program_manuscript_requirements pmr
JOIN requirements r ON pmr.requirement_id = r.id
WHERE pmr.program_id = (SELECT id FROM programs WHERE name = 'WebDevelopment')
ORDER BY FIELD(pmr.defense_type, 'title_proposal', 'title_defense', 'final_defense', 're-defense');
```

### Manuscripts Visible to Team #10
```sql
SELECT 
    r.id,
    r.name,
    pmr.defense_type,
    tr.status,
    tr.file_name
FROM program_manuscript_requirements pmr
JOIN requirements r ON pmr.requirement_id = r.id
JOIN teams t ON pmr.program_id = (SELECT id FROM programs WHERE name = t.program)
LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = 10
WHERE t.id = 10 
  AND pmr.defense_type = (
    SELECT defense_type FROM defense_schedules 
    WHERE team_id = 10 AND schedule_date <= NOW()
    ORDER BY schedule_date DESC LIMIT 1
  )
  AND pmr.is_required = 1;
```

### Programs That Require Each Manuscript
```sql
SELECT 
    r.name as manuscript,
    GROUP_CONCAT(p.name SEPARATOR ', ') as required_for_programs,
    GROUP_CONCAT(pmr.defense_type SEPARATOR ', ') as at_defense_types
FROM program_manuscript_requirements pmr
JOIN requirements r ON pmr.requirement_id = r.id
JOIN programs p ON pmr.program_id = p.id
WHERE pmr.is_required = 1
GROUP BY r.id, r.name
ORDER BY r.name;
```

---

## Implementation Checklist

- [ ] Run database migration
- [ ] Verify `program_manuscript_requirements` table created
- [ ] Mark "Final Manuscript" as is_defense_manuscript = 1
- [ ] Add mappings for WebDev + Mobile + Final Defense
- [ ] Test: Get team manuscripts (should show only Final Manuscript for WebDev teams at Final Defense)
- [ ] Filter decision-support to show only applicable manuscripts
- [ ] Filter home page to show only applicable manuscripts
- [ ] Test with team from different program (should not see manuscript)
- [ ] Test during different defense stages (visibility changes)

---

## File Organization

| File | Purpose |
|------|---------|
| `/assets/setup/20251122_program_manuscript_mapping.sql` | Database migration |
| `/api/manuscript_requirements.php` | REST API (8 endpoints) |
| `/dashboard/includes/manuscript_requirements_functions.php` | Helper functions |

---

## Deployment Steps

### Step 1: Run Migration
```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_program_manuscript_mapping.sql
```

### Step 2: Verify Table
```bash
mysql icei_38697196_coecsathesis -e "SHOW TABLES LIKE 'program_manuscript_requirements';"
```

### Step 3: Mark Manuscript
```bash
mysql icei_38697196_coecsathesis -e "UPDATE requirements SET is_defense_manuscript=1 WHERE id=5;"
```

### Step 4: Add Mappings
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=2&defense_type=final_defense"
```

### Step 5: Test API
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=10"
```

---

## Key Concepts

| Term | Meaning |
|------|---------|
| **Manuscript** | Main file to be defended (thesis, project) |
| **Applicable** | Required for team's program at their current defense stage |
| **Program-Specific** | Different programs have different manuscript requirements |
| **Defense Type** | Current stage: title_proposal, title_defense, final_defense, re-defense |
| **Submission Stage** | When: before_defense, at_defense, optional |

---

## Example Query Results

### Get Team's Applicable Manuscripts
```json
{
  "success": true,
  "team_id": 10,
  "program_id": 2,
  "program_name": "WebDevelopment",
  "current_defense_type": "final_defense",
  "manuscripts": [
    {
      "id": 5,
      "name": "Final Manuscript",
      "description": "Final project code and documentation",
      "file_name": "final_submission.zip",
      "status": "submitted",
      "is_required": 1,
      "submission_stage": "before_defense",
      "can_revise_after": false,
      "visibility_to_panelist": true
    }
  ],
  "count": 1
}
```

### List All Manuscripts
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "name": "Final Manuscript",
      "num_programs": 2,
      "programs": "WebDevelopment, Mobile",
      "defense_types": "final_defense, re-defense"
    },
    {
      "id": 1,
      "name": "Thesis Proposal",
      "num_programs": 4,
      "programs": "WebDevelopment, Mobile, Data Science, AI",
      "defense_types": "title_proposal"
    }
  ]
}
```

