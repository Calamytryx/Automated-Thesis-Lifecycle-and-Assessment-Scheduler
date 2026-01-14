# Specialization System Fixes - Complete

## Issues Fixed

### 1. ✅ Teams Not Showing for Research Professors (usertype=2)
**Problem:** Teams with assignments weren't displaying for usertype 2 users
**Solution:** Modified the `getMyTeams()` query to use a subquery for GROUP_CONCAT instead of LEFT JOIN to avoid grouping issues

**File:** `dashboard/includes/specialization_assignment_api.php`
**Changes:**
- Changed GROUP_CONCAT to use a correlated subquery
- Ensures team members are properly displayed even when teams come from professor assignments

### 2. ✅ Foreign Key Constraint Error
**Problem:** `SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row`
**Solution:** Added validation to check if specialization_id exists before attempting insertion

**File:** `dashboard/includes/specialization_assignment_api.php`
**Changes in `assignToUser()` and `assignToTeam()`:**
```php
// Validate that specialization exists
$checkStmt = $pdo->prepare("SELECT id FROM specialization_pool WHERE id = ?");
$checkStmt->execute([$specializationId]);
if (!$checkStmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Invalid specialization ID']);
    return;
}
```

### 3. ✅ Allow Multiple Specializations per Team/User
**Problem:** The system was using ON DUPLICATE KEY UPDATE which prevented multiple specializations
**Solution:** 
- Removed ON DUPLICATE KEY UPDATE logic
- Added duplicate check to give proper error message
- Now allows multiple unique specializations per team/user

**Changes:**
```php
// Check if already assigned
$existsStmt = $pdo->prepare("
    SELECT id FROM team_specializations 
    WHERE team_id = ? AND specialization_id = ?
");
$existsStmt->execute([$teamId, $specializationId]);
if ($existsStmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'This specialization is already assigned to this team']);
    return;
}

// Insert new assignment (allows multiple different specializations)
$stmt = $pdo->prepare("
    INSERT INTO team_specializations 
    (team_id, specialization_id, assigned_by, notes) 
    VALUES (?, ?, ?, ?)
");
```

### 4. ✅ Integrate Specialization into Genetic Algorithm
**Problem:** The scheduler wasn't considering team specializations when assigning panelists
**Solution:** Added specialization matching to both panelist selection and fitness calculation

**File:** `dashboard/includes/run_scheduler.php`

**New Functions Added:**
```php
// Get team specializations
function getTeamSpecializations($pdo, $teamId) {
    // Returns array of specialization names for a team
}

// Get user/panelist specializations
function getUserSpecializations($pdo, $userId) {
    // Returns array of specialization names for a user
}

// Calculate match score
function calculateSpecializationMatch($teamSpecializations, $panelistSpecializations) {
    $matchCount = count(array_intersect($teamSpecializations, $panelistSpecializations));
    return $matchCount * 10; // 10 points per matching specialization
}
```

**Modified Functions:**

1. **`selectPanelists()`** - Now scores panelists based on:
   - Same program: +100 points
   - Same department: +50 points
   - **Specialization match: +10 points per matching specialization** ⭐ NEW
   - Sorts by total score to pick best matches

2. **`calculateDefenseFitness()`** - Now includes:
   - Existing expertise matching
   - **Specialization matching bonus** ⭐ NEW
   - Better fitness scores for teams with matching panelist specializations

## How It Works Now

### Specialization Assignment
1. **Admins (usertype=0):**
   - Can assign multiple specializations to teams
   - Can assign multiple specializations to users (admins/faculty only)
   - Assignments are college-restricted for program chairs

2. **Research Professors (usertype=2):**
   - Can assign multiple specializations to their teams
   - Cannot assign to individual users
   - Only see teams they advise or are assigned to as panelists

### Defense Scheduling Algorithm
When the genetic algorithm runs:

1. **Panelist Selection:**
   - Fetches team specializations from `team_specializations` table
   - Fetches panelist specializations from `user_specializations` table
   - Scores each panelist based on program, department, AND specialization matches
   - Selects panelists with highest compatibility scores

2. **Fitness Calculation:**
   - Calculates expertise similarity (existing)
   - **Adds bonus for specialization matches (NEW)**
   - Higher fitness = better schedule quality
   - Schedules with matching specializations are preferred

3. **Scoring System:**
   - Each matching specialization: +10 fitness points
   - Example: Team with "AI, Machine Learning" gets bonus if panelist has either

## Benefits

✅ **Better Panel Assignments:** Panelists are now matched based on specific specializations
✅ **Multiple Specializations:** Teams can have multiple focus areas (e.g., "AI" + "Web Development")
✅ **Flexible System:** Easy to add/remove specializations per team or user
✅ **Improved Schedule Quality:** Genetic algorithm produces better matches
✅ **Transparent Matching:** Clear scoring system shows why panelists were selected

## Testing Checklist

- [ ] Assign multiple specializations to a team
- [ ] Assign multiple specializations to a user (faculty)
- [ ] Verify usertype 2 can see their teams
- [ ] Run defense scheduler and check if specializations influence panelist selection
- [ ] Check that duplicate specialization assignments show proper error message
- [ ] Verify invalid specialization IDs are rejected

## Database Structure

Tables remain unchanged - the UNIQUE constraints ensure no duplicate team+specialization combinations:
- `team_specializations`: (team_id, specialization_id) UNIQUE
- `user_specializations`: (user_id, specialization_id) UNIQUE

This means:
- ✅ Team can have multiple DIFFERENT specializations
- ✅ User can have multiple DIFFERENT specializations
- ❌ Cannot assign the SAME specialization twice to same team/user
