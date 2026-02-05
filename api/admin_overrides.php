<?php
/**
 * Admin endpoints for managing defense types, panelists, and overrides
 * 
 * Endpoints:
 * - GET /api/get_team_defense_info.php - Get defense type and panelist info for a team
 * - POST /api/set_defense_type_override.php - Set admin override for defense type
 * - POST /api/remove_defense_type_override.php - Remove defense type override
 * - POST /api/lock_panelists.php - Lock panelist assignments
 * - POST /api/unlock_panelists.php - Unlock panelist assignments
 * - GET /api/get_team_requirement_submissions.php - Get all submissions for a requirement
 */

session_start();
require_once __DIR__ . '/../assets/setup/db.inc.php';
require_once __DIR__ . '/../dashboard/includes/defense_type_functions.php';
require_once __DIR__ . '/../assets/includes/security_functions.php';
require_once __DIR__ . '/../assets/includes/auth_functions.php';

header('Content-Type: application/json');

// Verify admin access
$userId = $_SESSION['id'] ?? 0;
$userType = $_SESSION['usertype'] ?? -1;

if ($userType != 0) { // Not admin
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Admin access required']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$response = ['success' => false, 'message' => 'Unknown action'];

try {
    switch ($action) {
        case 'get_team_defense_info':
            handleGetTeamDefenseInfo();
            break;
        
        case 'set_defense_type_override':
            handleSetDefenseTypeOverride();
            break;
        
        case 'remove_defense_type_override':
            handleRemoveDefenseTypeOverride();
            break;
        
        case 'lock_panelists':
            handleLockPanelists();
            break;
        
        case 'unlock_panelists':
            handleUnlockPanelists();
            break;
        
        case 'set_locked_panelists':
            handleSetLockedPanelists();
            break;
        
        case 'get_team_requirement_submissions':
            handleGetTeamRequirementSubmissions();
            break;
        
        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
    error_log("Admin API Error: " . $e->getMessage());
}

echo json_encode($response);

// ============================================================================
// Handler Functions
// ============================================================================

function handleGetTeamDefenseInfo() {
    global $pdo, $response;
    
    $teamId = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);
    if (!$teamId) {
        $response = ['success' => false, 'error' => 'Invalid team ID'];
        return;
    }

    $defenseType = getTeamDefenseType($pdo, $teamId);
    $panelists = getPersistentPanelists($pdo, $teamId, $defenseType);
    
    // Get override info if any
    $overrideStmt = $pdo->prepare("
        SELECT * FROM defense_type_overrides 
        WHERE team_id = ? AND active = 1 
        LIMIT 1
    ");
    $overrideStmt->execute([$teamId]);
    $override = $overrideStmt->fetch(PDO::FETCH_ASSOC);

    // Get current scheduled panelists from defense_schedules (most recent schedule)
    $currentPanelistsStmt = $pdo->prepare("
        SELECT 
            ds.panelist_id, ds.panelist_id2, ds.panelist_id3, ds.defense_type,
            CONCAT(u1.first_name, ' ', u1.last_name) AS panelist1_name,
            CONCAT(u2.first_name, ' ', u2.last_name) AS panelist2_name,
            CONCAT(u3.first_name, ' ', u3.last_name) AS panelist3_name
        FROM defense_schedules ds
        LEFT JOIN users u1 ON ds.panelist_id = u1.id
        LEFT JOIN users u2 ON ds.panelist_id2 = u2.id
        LEFT JOIN users u3 ON ds.panelist_id3 = u3.id
        WHERE ds.team_id = ?
        ORDER BY ds.schedule_date DESC, ds.id DESC
        LIMIT 1
    ");
    $currentPanelistsStmt->execute([$teamId]);
    $scheduleRow = $currentPanelistsStmt->fetch(PDO::FETCH_ASSOC);
    
    $currentPanelists = [];
    if ($scheduleRow) {
        if ($scheduleRow['panelist_id'] && $scheduleRow['panelist1_name']) {
            $currentPanelists[] = ['id' => $scheduleRow['panelist_id'], 'name' => $scheduleRow['panelist1_name']];
        }
        if ($scheduleRow['panelist_id2'] && $scheduleRow['panelist2_name']) {
            $currentPanelists[] = ['id' => $scheduleRow['panelist_id2'], 'name' => $scheduleRow['panelist2_name']];
        }
        if ($scheduleRow['panelist_id3'] && $scheduleRow['panelist3_name']) {
            $currentPanelists[] = ['id' => $scheduleRow['panelist_id3'], 'name' => $scheduleRow['panelist3_name']];
        }
    }

    $response = [
        'success' => true,
        'team_id' => $teamId,
        'defense_type' => $defenseType,
        'panelists' => $panelists,
        'override' => $override,
        'current_panelists' => $currentPanelists
    ];
}

function handleSetDefenseTypeOverride() {
    global $pdo, $userId, $response;
    
    $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    $overrideType = filter_input(INPUT_POST, 'override_type', FILTER_SANITIZE_STRING);
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $expiresAt = filter_input(INPUT_POST, 'expires_at', FILTER_SANITIZE_STRING);

    if (!$teamId || !in_array($overrideType, ['title_proposal', 'title_defense', 'final_defense', 're-defense'])) {
        $response = ['success' => false, 'error' => 'Invalid parameters'];
        return;
    }

    $result = setDefenseTypeOverride($pdo, $teamId, $overrideType, $userId, $reason, $expiresAt ?: null);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Defense type override set to {$overrideType} for team {$teamId}"
        ];
        error_log("Admin {$userId} set defense type override for team {$teamId} to {$overrideType}");
    } else {
        $response = ['success' => false, 'error' => 'Failed to set override'];
    }
}

function handleRemoveDefenseTypeOverride() {
    global $pdo, $userId, $response;
    
    $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    if (!$teamId) {
        $response = ['success' => false, 'error' => 'Invalid team ID'];
        return;
    }

    $result = removeDefenseTypeOverride($pdo, $teamId);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Defense type override removed for team {$teamId}"
        ];
        error_log("Admin {$userId} removed defense type override for team {$teamId}");
    } else {
        $response = ['success' => false, 'error' => 'Failed to remove override'];
    }
}

function handleLockPanelists() {
    global $pdo, $userId, $response;
    
    $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    $defenseType = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);
    $panelistIds = json_decode($_POST['panelist_ids'] ?? '[]', true);

    if (!$teamId || !in_array($defenseType, ['title_proposal', 'title_defense', 'final_defense', 're-defense'])) {
        $response = ['success' => false, 'error' => 'Invalid parameters'];
        return;
    }

    // Validate panelist IDs
    foreach ($panelistIds as $id) {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            $response = ['success' => false, 'error' => 'Invalid panelist ID'];
            return;
        }
    }

    $result = lockPanelistAssignments($pdo, $teamId, $defenseType, $panelistIds, $userId);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Panelists locked for team {$teamId}, {$defenseType}"
        ];
        error_log("Admin {$userId} locked panelists for team {$teamId}, {$defenseType}");
    } else {
        $response = ['success' => false, 'error' => 'Failed to lock panelists'];
    }
}

function handleUnlockPanelists() {
    global $pdo, $userId, $response;
    
    $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    $defenseType = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);

    if (!$teamId || !in_array($defenseType, ['title_proposal', 'title_defense', 'final_defense', 're-defense'])) {
        $response = ['success' => false, 'error' => 'Invalid parameters'];
        return;
    }

    $result = unlockPanelistAssignments($pdo, $teamId, $defenseType);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Panelists unlocked for team {$teamId}, {$defenseType}"
        ];
        error_log("Admin {$userId} unlocked panelists for team {$teamId}, {$defenseType}");
    } else {
        $response = ['success' => false, 'error' => 'Failed to unlock panelists'];
    }
}

function handleGetTeamRequirementSubmissions() {
    global $pdo, $response;
    
    $teamId = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);
    $requirementId = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT);

    if (!$teamId || !$requirementId) {
        $response = ['success' => false, 'error' => 'Invalid parameters'];
        return;
    }

    $submissions = getTeamRequirementSubmissions($pdo, $teamId, $requirementId);
    
    $response = [
        'success' => true,
        'team_id' => $teamId,
        'requirement_id' => $requirementId,
        'submissions' => $submissions,
        'count' => count($submissions)
    ];
}

function handleSetLockedPanelists() {
    global $pdo, $userId, $response;
    
    $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    $locked1 = filter_input(INPUT_POST, 'locked_panelist1', FILTER_VALIDATE_INT) ?: null;
    $locked2 = filter_input(INPUT_POST, 'locked_panelist2', FILTER_VALIDATE_INT) ?: null;
    $locked3 = filter_input(INPUT_POST, 'locked_panelist3', FILTER_VALIDATE_INT) ?: null;

    if (!$teamId) {
        $response = ['success' => false, 'error' => 'Invalid team ID'];
        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE teams 
            SET locked_panelist1 = ?, locked_panelist2 = ?, locked_panelist3 = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$locked1, $locked2, $locked3, $teamId]);
        
        if ($result) {
            $response = [
                'success' => true,
                'message' => "Locked panelists updated for team {$teamId}"
            ];
            error_log("Admin {$userId} updated locked panelists for team {$teamId}");
        } else {
            $response = ['success' => false, 'error' => 'Failed to update locked panelists'];
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'error' => $e->getMessage()];
    }
}
