# Scheduler Integration Code Snippets

This file shows exactly where and how to integrate the persistent panelist system into the scheduler.

## File: `dashboard/includes/run_scheduler.php`

### Change 1: Import defense_type_functions (ALREADY DONE ✅)

**Location:** Line 18-19 (just after other requires)

✅ **Already added:**
```php
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../includes/edit_functions.php';
require_once __DIR__ . '/../includes/defense_type_functions.php'; // ← ADDED
```

---

## Change 2: Modify `selectPanelists()` Function

**Location:** Around line 1262

**Current code:**
```php
function selectPanelists($panelistsByProgram, $allPanelists, $adviserId)
{
    global $pdo;
    $selectedPanelists = [];
    $teamData = null;
    
    // Find the team based on adviser
    foreach ($GLOBALS['teams'] as $team) {
        if ($team['adviser_id'] == $adviserId) {
            $teamData = $team;
            break;
        }
    }
    
    if (!$teamData) {
        // Fallback: randomly pick 3 panelists excluding the adviser
        $remaining = array_diff(array_keys($allPanelists), [$adviserId]);
        return array_slice($remaining, 0, 3);
    }
    
    // ... rest of algorithm ...
}
```

**Replace with:**
```php
function selectPanelists($panelistsByProgram, $allPanelists, $adviserId)
{
    global $pdo;
    $selectedPanelists = [];
    $teamData = null;
    
    // Find the team based on adviser
    foreach ($GLOBALS['teams'] as $team) {
        if ($team['adviser_id'] == $adviserId) {
            $teamData = $team;
            break;
        }
    }
    
    if (!$teamData) {
        // Fallback: randomly pick 3 panelists excluding the adviser
        $remaining = array_diff(array_keys($allPanelists), [$adviserId]);
        return array_slice($remaining, 0, 3);
    }
    
    // ========== NEW CODE START ==========
    // CHECK FOR PERSISTENT PANELISTS FIRST
    // Get the defense type for this team
    $teamId = $teamData['id'];
    $defenseType = getTeamDefenseType($pdo, $teamId);
    
    // Try to get persistent (locked) panelists
    $persistentPanelists = getPersistentPanelists($pdo, $teamId, $defenseType);
    
    if (!empty($persistentPanelists)) {
        // Use the locked panelists, don't run algorithm
        error_log("Using persistent panelists for team {$teamId}, defense type: {$defenseType}");
        return $persistentPanelists;
    }
    // ========== NEW CODE END ==========
    
    $teamProgram = (string)$teamData['program'];
    $teamDepartment = getDepartment($teamProgram);

    // Candidate 0: Panelist from the exact team program (same defense title)
    // ... rest of algorithm remains the same ...
}
```

---

## Change 3: Modify `saveScheduleToDatabase()` Function

**Location:** Around line 699

Find this section where defense_schedules is inserted:

**Current code:**
```php
$stmt = $pdo->prepare("
    INSERT INTO defense_schedules 
    (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, status, approval_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 'pending')
");

// ... loop through defenses ...

foreach ($defenses as $defense) {
    // Skip if this team was already scheduled
    if (in_array($defense['team_id'], $scheduledTeams)) {
        continue;
    }

    $scheduledTeams[] = $defense['team_id'];
    
    // ... date/time calculations ...
    
    $stmt->execute([
        $defense['team_id'],
        $defense['panelist_ids'][0],
        $defense['panelist_ids'][1],
        $defense['panelist_ids'][2],
        $date,
        $startTime->format('H:i:s'),
        $endTime->format('H:i:s'),
        $defense['room']
    ]);
    
    // CREATE DEFENSE SCHEDULE NOTIFICATIONS
    $scheduleId = $pdo->lastInsertId();
    
    // CREATE PANELIST APPROVAL RECORDS
    // ... rest of code ...
}
```

**Replace with:**
```php
$stmt = $pdo->prepare("
    INSERT INTO defense_schedules 
    (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 'pending')
");

// Get current user ID (usually session or passed as parameter)
// For now, assume it's available as $userId or use 0 for system
$adminUserId = $_SESSION['id'] ?? 0;

// ... loop through defenses ...

foreach ($defenses as $defense) {
    // Skip if this team was already scheduled
    if (in_array($defense['team_id'], $scheduledTeams)) {
        continue;
    }

    $scheduledTeams[] = $defense['team_id'];
    
    // ... date/time calculations ...
    
    // ========== NEW CODE START ==========
    // Determine defense type for this team
    $defenseType = getTeamDefenseType($pdo, $defense['team_id']);
    
    // For multi-submission requirements, get all related files
    $relatedFiles = [];
    try {
        $fileStmt = $pdo->prepare("
            SELECT trf.id FROM team_requirement_files trf
            JOIN requirements r ON trf.requirement_id = r.id
            WHERE trf.team_id = ? 
            AND r.requirement_type = ?
            AND trf.deleted_at IS NULL
            ORDER BY trf.submission_number ASC
        ");
        $fileStmt->execute([$defense['team_id'], $defenseType]);
        $relatedFiles = $fileStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error fetching related files for team {$defense['team_id']}: " . $e->getMessage());
    }
    
    $relatedFilesJson = !empty($relatedFiles) ? json_encode($relatedFiles) : null;
    // ========== NEW CODE END ==========
    
    $stmt->execute([
        $defense['team_id'],
        $defense['panelist_ids'][0],
        $defense['panelist_ids'][1],
        $defense['panelist_ids'][2],
        $date,
        $startTime->format('H:i:s'),
        $endTime->format('H:i:s'),
        $defense['room'],
        $defenseType  // ← ADD THIS PARAMETER
    ]);
    
    // CREATE DEFENSE SCHEDULE NOTIFICATIONS
    $scheduleId = $pdo->lastInsertId();
    
    // ========== NEW CODE START ==========
    // Save related requirement files for multi-submission defenses
    if (!empty($relatedFilesJson)) {
        $updateStmt = $pdo->prepare("
            UPDATE defense_schedules 
            SET related_requirement_files = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$relatedFilesJson, $scheduleId]);
    }
    
    // LOCK PANELIST ASSIGNMENTS TO PREVENT FUTURE ALGORITHM CHANGES
    $panelistIds = [
        $defense['panelist_ids'][0],
        $defense['panelist_ids'][1],
        $defense['panelist_ids'][2]
    ];
    // Remove any null/empty values
    $panelistIds = array_filter($panelistIds);
    
    if (!empty($panelistIds)) {
        $lockResult = lockPanelistAssignments(
            $pdo, 
            $defense['team_id'], 
            $defenseType, 
            $panelistIds, 
            $adminUserId
        );
        if ($lockResult) {
            error_log("Locked panelists for team {$defense['team_id']}, defense_type: {$defenseType}");
        } else {
            error_log("Failed to lock panelists for team {$defense['team_id']}");
        }
    }
    // ========== NEW CODE END ==========
    
    // CREATE PANELIST APPROVAL RECORDS
    $approvalStmt = $pdo->prepare("
        INSERT INTO panelist_approvals (defense_schedule_id, panelist_id) 
        VALUES (?, ?)
    ");
    
    // Create approval record for each panelist
    foreach ([$defense['panelist_ids'][0], $defense['panelist_ids'][1], $defense['panelist_ids'][2]] as $panelistId) {
        if ($panelistId && $panelistId !== '') {
            $approvalStmt->execute([$scheduleId, $panelistId]);
        }
    }
    // ... rest of code ...
}
```

---

## Change 4: Update INSERT Statement Prepare

**Location:** Around line 726-729

**OLD:**
```php
$stmt = $pdo->prepare("
    INSERT INTO defense_schedules 
    (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, status, approval_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 'pending')
");
```

**NEW:**
```php
$stmt = $pdo->prepare("
    INSERT INTO defense_schedules 
    (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 'pending')
");
```

---

## Summary of Changes

| Change | Location | Impact | Status |
|--------|----------|--------|--------|
| Import functions | Line ~18 | Enables defense type functions | ✅ DONE |
| Check persistent panelists | selectPanelists() ~1262 | Skip algorithm if locked | 📝 TODO |
| Get defense type | saveScheduleToDatabase() ~730 | Determine defense type | 📝 TODO |
| Get related files | saveScheduleToDatabase() ~740 | Populate related_requirement_files | 📝 TODO |
| Lock panelists | saveScheduleToDatabase() ~770 | Prevent future changes | 📝 TODO |
| Update SQL INSERT | saveScheduleToDatabase() ~728 | Include defense_type column | 📝 TODO |

---

## Testing the Integration

After making these changes, test with:

### Test 1: Scheduler Prefers Persistent Panelists
```bash
1. Run scheduler first time → Creates schedule with panelists A, B, C
2. Check team_panelists table → Should have locked entries
3. Run scheduler second time → Same team gets same panelists A, B, C
4. Verify logs show: "Using persistent panelists for team X"
```

### Test 2: Defense Type Tracking
```bash
1. Create a team with no approved titles
2. Run scheduler
3. Check defense_schedules → defense_type should be 'title_proposal'
4. Approve a title
5. Run scheduler again
6. Check defense_schedules → new schedule should have defense_type = 'title_defense'
```

### Test 3: Multi-File Linking
```bash
1. Create requirement with allow_multiple_submissions = 1
2. Have team upload 3 files
3. Run scheduler
4. Check defense_schedules.related_requirement_files
5. Should contain JSON array of the 3 file IDs: [1, 2, 3]
```

---

## Error Handling

Add error handling for the new code:

```php
// When getting defense type
try {
    $defenseType = getTeamDefenseType($pdo, $defense['team_id']);
} catch (Exception $e) {
    error_log("Error getting defense type for team {$defense['team_id']}: " . $e->getMessage());
    $defenseType = 'title_proposal'; // Default fallback
}

// When locking panelists
try {
    $lockResult = lockPanelistAssignments(...);
    if (!$lockResult) {
        error_log("Warning: Failed to lock panelists for team {$defense['team_id']}");
    }
} catch (Exception $e) {
    error_log("Error locking panelists: " . $e->getMessage());
    // Continue anyway - don't fail the entire schedule
}
```

---

## Logging

Add detailed logging to track panelist assignments:

```php
// When using persistent panelists
error_log("PANELIST_PERSIST: Team {$teamId}, Type: {$defenseType}, Panelists: " . json_encode($persistentPanelists));

// When locking new panelists
error_log("PANELIST_LOCK: Team {$defense['team_id']}, Type: {$defenseType}, Panelists: " . json_encode($panelistIds) . ", Admin: {$adminUserId}");

// When using algorithm (no lock)
error_log("PANELIST_ALGO: Team {$defense['team_id']}, Type: {$defenseType}, Panelists: " . json_encode($selectedPanelists));
```

---

## Database Verification

After integration, verify with these queries:

```sql
-- Check if panelists were locked
SELECT team_id, defense_type, panelist_id, locked, admin_override, created_at 
FROM team_panelists 
ORDER BY created_at DESC LIMIT 10;

-- Check if defense_type was stored
SELECT id, team_id, defense_type, related_requirement_files, schedule_date 
FROM defense_schedules 
ORDER BY created_at DESC LIMIT 10;

-- Check if related files were linked
SELECT id, team_id, defense_type, 
       JSON_LENGTH(related_requirement_files) as file_count,
       related_requirement_files
FROM defense_schedules 
WHERE related_requirement_files IS NOT NULL 
LIMIT 10;
```

---

## Migration Path

1. ✅ Apply database migration
2. 📝 Update selectPanelists() to check persistent panelists
3. 📝 Update saveScheduleToDatabase() to lock panelists
4. 📝 Update saveScheduleToDatabase() to populate related_requirement_files
5. 📝 Test with pilot team data
6. 📝 Deploy to production

