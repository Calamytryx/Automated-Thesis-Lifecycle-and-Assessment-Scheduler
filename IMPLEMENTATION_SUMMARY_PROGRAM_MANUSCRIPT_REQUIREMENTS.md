# Program-Specific Manuscript Requirements - Implementation Complete

## Overview

The program-specific manuscript requirements system has been successfully implemented. This system allows different programs to have different manuscript requirements at different defense stages.

**Example:** Final Manuscript (requirement #5) is now required ONLY for WebDevelopment and Mobile programs at Final Defense stage. Other programs don't see it.

---

## What Was Built

### 1. Database Layer ✅
**File:** `/assets/setup/20251122_program_manuscript_mapping.sql`

Creates:
- `program_manuscript_requirements` table - Stores which programs need which manuscripts
- `program_manuscript_view` - View for easy querying
- Proper indexes and constraints for performance

**Key Features:**
- Unique constraint: (requirement_id, program_id, defense_type)
- Tracks: is_required, submission_stage, can_revise_after, visibility_to_panelist
- Full timestamps for audit trail

### 2. API Layer ✅
**File:** `/api/manuscript_requirements.php`

Provides 8 endpoints:
1. `add_manuscript_requirement` - Add manuscript for program+defense combo
2. `remove_manuscript_requirement` - Remove mapping
3. `get_requirement_manuscripts` - Get all programs for a manuscript
4. `get_program_defense_manuscripts` - Get manuscripts for program+stage
5. `get_team_manuscripts` - Get manuscripts applicable to team
6. `get_programs_for_manuscript` - List programs for UI configuration
7. `bulk_update_manuscripts` - Bulk configure multiple programs
8. `list_all_manuscripts` - Admin list of configurations

**All endpoints:**
- Require admin authentication (usertype=0)
- Return JSON responses
- Include full error handling and validation

### 3. Helper Functions Layer ✅
**File:** `/dashboard/includes/manuscript_requirements_functions.php`

Provides 6 functions:

**1. `getTeamApplicableManuscripts($pdo, $team_id)`**
- Gets all manuscripts applicable to team at current defense stage
- Returns: team info, program name, current defense type, applicable manuscripts
- Used by: Home tab, Decision Support, any page showing manuscripts

**2. `isManuscriptApplicableToTeam($pdo, $team_id, $requirement_id)`**
- Checks if specific manuscript applies to team
- Returns: is_applicable bool, submission_stage, visibility info
- Used by: Visibility/permission checks

**3. `getProgramManuscriptsByDefenseType($pdo, $program_id)`**
- Groups manuscripts by defense stage
- Returns: organized by title_proposal, title_defense, final_defense, re-defense
- Used by: Admin configuration UI

**4. `addManuscriptRequirement(...)`**
- Adds program+defense+manuscript mapping
- Validates inputs
- Returns: success status

**5. `removeManuscriptRequirement(...)`**
- Removes program+defense+manuscript mapping
- Returns: success status

**6. `getProgramManuscripts($pdo, $program_id)`**
- Gets all manuscripts for a program
- Returns: organized by program
- Used by: Admin reports

### 4. UI Integration Layer ✅

#### 4a. Requirements Tab Configuration UI
**File:** `/dashboard/app.js.php` (modified)

Added to edit form for requirements:
- Checkbox: "This is a defense manuscript"
- Configurable section with program checkboxes per defense type
- Grid layout organized by defense type
- Save button to persist configuration to database
- JavaScript functions to load/save configuration

**Functions Added:**
- `loadProgramManuscriptConfig(requirementId)` - Load programs
- `saveProgramManuscriptConfig(requirementId)` - Save selections to API

#### 4b. Decision Support Integration
**File:** `/decision-support/index.php` (modified)

Changed from hardcoded requirement_id=5 to:
- Calls `getTeamApplicableManuscripts()` helper
- Shows first applicable manuscript for team's program+stage
- Falls back gracefully if no applicable manuscript
- Improved logging for debugging

#### 4c. Home Tab Filtering
**File:** `/home/includes/get_team_overview.php` (modified)

Changed from showing all requirements to:
- Shows only non-manuscript requirements (is_defense_manuscript=0)
- Shows only applicable manuscript requirements for team's program+stage
- Filters based on current defense schedule
- Dynamically filters as team's defense stage changes

### 5. Documentation ✅

#### `PROGRAM_MANUSCRIPT_REQUIREMENTS.md`
- Comprehensive system overview
- Database schema documentation
- All 8 API endpoints with examples
- Helper function documentation
- Configuration examples
- SQL query reference
- Deployment steps
- Key concepts glossary

#### `PROGRAM_MANUSCRIPT_REQUIREMENTS_QUICK_START.md`
- 5-minute setup guide
- Step-by-step instructions
- Migration, mapping, and verification steps
- Common tasks (add, remove, bulk)
- Testing scenarios
- Troubleshooting guide

---

## How It Works

### Data Flow: Team Views Requirements

```
Team views Home page
    ↓
Home calls: get_team_overview.php?team_id=10
    ↓
get_team_overview.php loads helper functions
    ↓
Queries program_manuscript_requirements table
    ↓
Filters requirements WHERE:
  - is_defense_manuscript = 0 (non-manuscript reqs)
  - OR id IN (program_manuscript_requirements where program=WebDev AND stage=final_defense)
    ↓
Returns only applicable requirements
    ↓
Team sees only what they need for current stage
```

### Admin Configuration Flow

```
Admin edits "Final Manuscript" requirement
    ↓
Form shows: "Mark as defense manuscript" checkbox
    ↓
Admin checks it → Program selector appears
    ↓
Admin selects: WebDevelopment + Final Defense
                Mobile + Final Defense
    ↓
Admin clicks "Save Program Configuration"
    ↓
JavaScript calls: /api/manuscript_requirements.php?action=add_manuscript_requirement
    ↓
API validates and inserts into program_manuscript_requirements table
    ↓
Next time WebDev team at Final Defense views home:
    → Sees "Final Manuscript" requirement
    
Next time DataScience team at Final Defense views home:
    → Does NOT see "Final Manuscript" requirement
```

---

## Database Changes Required

Run the migration:

```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_program_manuscript_mapping.sql
```

This creates:
- `program_manuscript_requirements` table (11 columns)
- `program_manuscript_view` for easy querying
- Proper indexes on (program_id, defense_type), (requirement_id, program_id), (is_required)

---

## Configuration Steps

### Step 1: Mark Manuscript
```bash
mysql icei_38697196_coecsathesis -e "UPDATE requirements SET is_defense_manuscript=1 WHERE id=5;"
```

### Step 2: Get Program IDs
```bash
mysql icei_38697196_coecsathesis -e "SELECT id, name FROM programs;"
```

### Step 3: Configure via UI or API

**Via UI (Dashboard):**
1. Go to Dashboard → Requirements tab
2. Edit "Final Manuscript"
3. Check "This is a defense manuscript"
4. Select programs: WebDevelopment, Mobile
5. For each, check: Final Defense, Re-Defense
6. Click "Save Program Configuration"

**Via API:**
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=2&defense_type=final_defense&is_required=1"
```

### Step 4: Test Filtering

**For WebDev team at Final Defense:**
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=10"
```
→ Returns: "Final Manuscript" in list ✓

**For DataScience team at Final Defense:**
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=20"
```
→ Returns: Empty or without "Final Manuscript" ✓

---

## Files Modified

| File | Changes | Impact |
|------|---------|--------|
| `/dashboard/app.js.php` | Added requirements form section + JS functions | Enables admin UI for configuration |
| `/decision-support/index.php` | Changed to use helper function instead of hardcoded ID | Shows applicable manuscript only |
| `/home/includes/get_team_overview.php` | Added program-specific filtering to SQL query | Teams see only applicable requirements |

## Files Created

| File | Purpose |
|------|---------|
| `/assets/setup/20251122_program_manuscript_mapping.sql` | Database migration |
| `/api/manuscript_requirements.php` | REST API (8 endpoints) |
| `/dashboard/includes/manuscript_requirements_functions.php` | Helper functions (6 functions) |
| `PROGRAM_MANUSCRIPT_REQUIREMENTS.md` | Full documentation |
| `PROGRAM_MANUSCRIPT_REQUIREMENTS_QUICK_START.md` | Quick start guide |

---

## Key Features

✅ **Program-Specific** - Different programs, different requirements
✅ **Stage-Based** - Different at each defense stage (proposal, title, final, re-defense)
✅ **Database-Driven** - Configuration stored, not hardcoded
✅ **Automatic Filtering** - Teams see only what applies to them
✅ **Admin UI** - Easy configuration from dashboard
✅ **RESTful API** - Full API for programmatic access
✅ **Helper Functions** - Easy to use in any PHP page
✅ **Backward Compatible** - Non-manuscript requirements unaffected
✅ **Error Handling** - Comprehensive validation and logging
✅ **Documented** - Full API docs + quick start guide

---

## Testing Scenarios

### Scenario 1: Different Programs See Different Manuscripts
✓ WebDev team sees "Final Manuscript" at Final Defense
✓ DataScience team does NOT see "Final Manuscript" at Final Defense

### Scenario 2: Same Program at Different Stages
✓ WebDev at Title Proposal → Sees "Proposal"
✓ WebDev at Final Defense → Sees "Final Manuscript"

### Scenario 3: Re-Defense Support
✓ WebDev team in Re-Defense → Sees only re-defense manuscripts

### Scenario 4: Home Tab Filtering
✓ Team's requirement list filters dynamically as defense stage changes
✓ Only applicable manuscripts shown

### Scenario 5: Decision Support Page
✓ Shows only applicable manuscript for evaluation

---

## Troubleshooting

### No manuscripts returned for team
- Check: Is requirement marked as is_defense_manuscript?
- Check: Is mapping created for program+defense type?
- Check: Is team at correct defense stage?

### API returns error
- Check: Admin authentication required (usertype=0)
- Check: Valid requirement_id, program_id
- Check: Proper JSON in request

### Old hardcoded behavior expected
- System now filters dynamically
- Configure mappings in dashboard or via API
- Intentional change to support flexibility

---

## Next Steps (Optional)

1. **Advanced Features:**
   - Bulk import from CSV
   - Template configurations for program groups
   - Visibility rules (panelist vs team)
   - Conditional requirements (if X, then Y)

2. **Monitoring:**
   - Track requirement usage by program
   - Report on configuration gaps
   - Audit trail of changes

3. **Integration:**
   - Auto-create mappings on new requirement
   - Program templates with default mappings
   - Notification when new manuscripts added

---

## Summary

The system is **fully operational** and ready for production. All components have been implemented:
- ✅ Database schema
- ✅ REST API with 8 endpoints
- ✅ Helper functions for checking applicability
- ✅ Admin UI for configuration
- ✅ Integration in 3 key pages (requirements tab, decision-support, home)
- ✅ Comprehensive documentation

Teams now see only the manuscript requirements appropriate for their program at their current defense stage.

