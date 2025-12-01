<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

// Check if user is authenticated and is a student (leader)
if (!isset($_SESSION['auth']) || $_SESSION['usertype'] != 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirementId = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
    
    // Get team ID from session
    $teamIdArray = isset($_SESSION['team_id']) ? (array)$_SESSION['team_id'] : [];
    $teamId = $teamIdArray[0] ?? null;
    
    if (!$requirementId || !$teamId) {
        $response['message'] = 'Invalid requirement or team ID.';
        echo json_encode($response);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get current submission info - only allow removal if status is pending
        $checkStmt = $pdo->prepare("
            SELECT file_name, status 
            FROM team_requirements 
            WHERE team_id = ? AND requirement_id = ? AND status = 'pending'
        ");
        $checkStmt->execute([$teamId, $requirementId]);
        $submission = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$submission) {
            $response['message'] = 'No pending submission found to remove.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }
        
        $fileName = $submission['file_name'];
        $filePath = __DIR__ . '/../../assets/uploads/submission/' . $fileName;
        
        // Delete the file from filesystem
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                $response['message'] = 'Failed to delete the file from storage.';
                $pdo->rollBack();
                echo json_encode($response);
                exit;
            }
        }
        
        // Remove the record from database
        $deleteStmt = $pdo->prepare("
            DELETE FROM team_requirements 
            WHERE team_id = ? AND requirement_id = ? AND status = 'pending'
        ");
        $deleteStmt->execute([$teamId, $requirementId]);
        
        if ($deleteStmt->rowCount() > 0) {
            $pdo->commit();
            
            $response['success'] = true;
            $response['message'] = 'File removed successfully. You can now upload a new file.';
            
            error_log("File removed: Team ID {$teamId}, Requirement ID {$requirementId}, File: {$fileName} by User ID " . $_SESSION['id']);
        } else {
            $response['message'] = 'Failed to remove the submission record.';
            $pdo->rollBack();
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Remove file error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
