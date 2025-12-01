<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

// Check if user is authenticated and is an adviser/admin
if (!isset($_SESSION['auth']) || !in_array($_SESSION['usertype'], [0, 2])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    $requirementId = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
    
    if (!$teamId || !$requirementId) {
        $response['message'] = 'Invalid team or requirement ID.';
        echo json_encode($response);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get current submission info
        $checkStmt = $pdo->prepare("
            SELECT file_name, status 
            FROM team_requirements 
            WHERE team_id = ? AND requirement_id = ? AND status != 'pending'
        ");
        $checkStmt->execute([$teamId, $requirementId]);
        $submission = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$submission) {
            $response['message'] = 'No submission found to revert.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }
        
        // Update status to pending and clear feedback
        $updateStmt = $pdo->prepare("
            UPDATE team_requirements 
            SET status = 'pending', feedback = NULL, feedback_file = NULL
            WHERE team_id = ? AND requirement_id = ?
        ");
        $updateStmt->execute([$teamId, $requirementId]);
        
        if ($updateStmt->rowCount() > 0) {
            // Get team and requirement names for notification
            $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
            $teamStmt->execute([$teamId]);
            $teamName = $teamStmt->fetchColumn();
            
            $reqStmt = $pdo->prepare("SELECT name FROM requirements WHERE id = ?");
            $reqStmt->execute([$requirementId]);
            $requirementName = $reqStmt->fetchColumn();
            
            // Create notification for team members
            try {
                require_once dirname(__DIR__, 2) . '/assets/includes/notification_functions.php';
                createRequirementRevertNotifications($pdo, $teamId, $requirementId, $requirementName);
            } catch (Exception $notifException) {
                // Don't fail the revert if notification fails, just log it
                error_log("Failed to create requirement revert notification: " . $notifException->getMessage());
            }
            
            $pdo->commit();
            
            $response['success'] = true;
            $response['message'] = "Submission for '{$requirementName}' has been reverted. The team can now resubmit.";
            $response['file_name'] = $submission['file_name']; // Return current file name for UI update
            
            error_log("Submission reverted: Team ID {$teamId}, Requirement ID {$requirementId} by User ID " . $_SESSION['id']);
        } else {
            $response['message'] = 'Failed to revert submission.';
            $pdo->rollBack();
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Revert submission error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
