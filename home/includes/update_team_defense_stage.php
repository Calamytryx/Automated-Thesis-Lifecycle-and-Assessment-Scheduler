<?php
session_start();
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

// Authentication check
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

// Only faculty can update defense stages
if ($_SESSION['usertype'] != 2 && $_SESSION['usertype'] != 0) {
    echo json_encode(['success' => false, 'message' => 'Permission denied.']);
    exit;
}

$userId = $_SESSION['id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$teamId = intval($input['team_id'] ?? 0);
$defenseType = $input['defense_type'] ?? '';

// Validate inputs
if ($teamId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid team ID.']);
    exit;
}

$validTypes = ['title_proposal', 'title_defense', 'final_defense', 're-defense'];
if (!in_array($defenseType, $validTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid defense type.']);
    exit;
}

try {
    // Verify user is the adviser of this team (unless admin)
    if ($_SESSION['usertype'] == 2) {
        $checkStmt = $pdo->prepare("
            SELECT 1 FROM team_members 
            WHERE team_id = ? AND user_id = ? AND role = 'adviser'
        ");
        $checkStmt->execute([$teamId, $userId]);
        if (!$checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You are not the adviser of this team.']);
            exit;
        }
    }
    
    // Check if there's an existing active override
    $existingStmt = $pdo->prepare("
        SELECT id FROM defense_type_overrides 
        WHERE team_id = ? AND active = 1
    ");
    $existingStmt->execute([$teamId]);
    $existing = $existingStmt->fetch();
    
    if ($existing) {
        // Update existing override
        $updateStmt = $pdo->prepare("
            UPDATE defense_type_overrides 
            SET override_type = ?, updated_at = NOW()
            WHERE team_id = ? AND active = 1
        ");
        $updateStmt->execute([$defenseType, $teamId]);
    } else {
        // Create new override
        $insertStmt = $pdo->prepare("
            INSERT INTO defense_type_overrides (team_id, override_type, reason, active, created_by, created_at, updated_at)
            VALUES (?, ?, 'Updated by adviser', 1, ?, NOW(), NOW())
        ");
        $insertStmt->execute([$teamId, $defenseType, $userId]);
    }
    
    error_log("UPDATE_TEAM_DEFENSE_STAGE: team_id={$teamId}, defense_type={$defenseType}, by_user={$userId}");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Defense stage updated successfully.',
        'new_stage' => $defenseType
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in update_team_defense_stage.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.'
    ]);
} catch (Exception $e) {
    error_log('Error in update_team_defense_stage.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>
