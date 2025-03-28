<?php
session_start();
header('Content-Type: application/json');

require_once '../../assets/setup/db.inc.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for the regular file upload (leader or member submission)
    if (isset($_FILES['file']) && isset($_POST['document_name']) && isset($_POST['requirement_id'])) {
        $file = $_FILES['file'];
        $documentName = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['document_name']);
        $requirementId = (int)$_POST['requirement_id']; // Capture the requirement ID
        
        // Validate file type
        $fileType = mime_content_type($file['tmp_name']);
        if ($fileType !== 'application/pdf') {
            echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed.']);
            exit;
        }

        // Get user ID from session
        if (!isset($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
            exit;
        }
        $userId = $_SESSION['id'];

        try {
            // Get team_id from team_members
            $stmt = $pdo->prepare("SELECT team_id FROM team_members WHERE user_id = ?");
            $stmt->execute([$userId]);
            $teamMember = $stmt->fetch();
            if (!$teamMember) {
                throw new Exception('Team not found for user.');
            }
            $teamId = $teamMember['team_id'];

            // Get team name from teams
            $stmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
            $stmt->execute([$teamId]);
            $team = $stmt->fetch();
            if (!$team) {
                throw new Exception('Team not found.');
            }
            $teamName = preg_replace('/[^a-zA-Z0-9-_]/', '', $team['name']);

            // Generate filename for the regular file
            $date = date('Ymd');
            $newFileName = "{$teamName}-{$documentName}-{$date}.pdf";

            // Ensure submission directory exists
            $targetDir = '../../assets/uploads/submission/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Move uploaded file
            $targetPath = $targetDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {

                // Insert the regular file details into the team_requirements table
                $stmt = $pdo->prepare("
                    INSERT INTO `team_requirements` (
                        `team_id`, 
                        `requirement_id`, 
                        `status`, 
                        `submitted_at`, 
                        `feedback`, 
                        `file_name`
                    ) VALUES (?, ?, 'submitted', NOW(), '', ?)
                ");
                $stmt->execute([$teamId, $requirementId, $newFileName]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    // Check for the feedback file upload (adviser submits feedback)
    elseif (isset($_FILES['file']) && isset($_POST['document_name']) && isset($_POST['requirement_id']) && isset($_POST['feedback'])) {
        $file = $_FILES['file'];
        $documentName = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['document_name']);
        $requirementId = (int)$_POST['requirement_id']; // Capture the requirement ID
        $feedback = $_POST['feedback']; // Capture feedback

        // Validate file type
        $fileType = mime_content_type($file['tmp_name']);
        if ($fileType !== 'application/pdf') {
            echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed.']);
            exit;
        }

        // Get user ID from session
        if (!isset($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
            exit;
        }
        $userId = $_SESSION['id'];

        try {
            // Get team_id from team_members
            $stmt = $pdo->prepare("SELECT team_id FROM team_members WHERE user_id = ?");
            $stmt->execute([$userId]);
            $teamMember = $stmt->fetch();
            if (!$teamMember) {
                throw new Exception('Team not found for user.');
            }
            $teamId = $teamMember['team_id'];

            // Generate filename for the feedback file
            $newFileName = "{$documentName}-feedback-" . time() . ".pdf";
            $targetDir = __DIR__ . '/../feedback/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Move the uploaded feedback file
            $targetPath = $targetDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {

                // Insert the feedback file name into the database
                $stmt = $pdo->prepare("UPDATE team_requirements SET feedback_file = ?, feedback = ? WHERE id = ?");
                $stmt->execute([$newFileName, $feedback, $requirementId]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No file, document name, or requirement ID provided.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
header('location: ../');
?>
