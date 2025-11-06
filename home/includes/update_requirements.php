<?php
header('Content-Type: application/json');
session_start();
require '../../assets/setup/db.inc.php';

if (!isset($_SESSION['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Function to interpolate SQL with parameters
function interpolateQuery($sql, $params) {
    $keys = [];
    $values = $params; 

    // Build a regular expression for each parameter
    foreach ($params as $key => $value) {
        if (is_string($key)) {
            $keys[] = '/:'.$key.'/';
        } else {
            $keys[] = '/[?]/';
        }
        
        if (is_string($value))
            $values[$key] = "'" . addslashes($value) . "'";
        else if (is_null($value))
            $values[$key] = 'NULL';
        else if (is_bool($value))
            $values[$key] = $value ? 'TRUE' : 'FALSE';
        else
            $values[$key] = $value;
    }

    return preg_replace($keys, $values, $sql, 1);
}

try {
    $postedData = $_POST;
    $postedFiles = $_FILES;
    $executedQueries = []; // Array to store all executed queries

    if (!isset($_POST['requirements']) || !is_array($_POST['requirements'])) {
        throw new Exception('Invalid requirements data');
    }
    
    foreach ($_POST['requirements'] as $requirementId) {
        $status = $_POST['status'][$requirementId];
        $feedback = $_POST['feedback'][$requirementId];
        $feedbackFile = $_FILES['feedbackFile']['name'][$requirementId] ?? '';

        // Get team name
        $teamSql = "SELECT name FROM teams WHERE id = ?";
        $teamStmt = $pdo->prepare($teamSql);
        $teamParams = [$_SESSION['team_id'][0]];
        $teamStmt->execute($teamParams);
        $executedQueries[] = interpolateQuery($teamSql, $teamParams);
        $teamName = $teamStmt->fetchColumn();

        // Get requirement name
        $reqSql = "SELECT name FROM requirements WHERE id = ?";
        $reqStmt = $pdo->prepare($reqSql);
        $reqParams = [$requirementId];
        $reqStmt->execute($reqParams);
        $executedQueries[] = interpolateQuery($reqSql, $reqParams);
        $requirementName = $reqStmt->fetchColumn();

        // Process file upload
        $newFileName = '';
        if (!empty($feedbackFile)) {
            $newFileName = 'feedback-' . $teamName . '-' . $requirementName . '-' . date('Ymd') . '.' . pathinfo($feedbackFile, PATHINFO_EXTENSION);
            $uploadDir = '../feedback/';
            $uploadFilePath = $uploadDir . basename($newFileName);
            move_uploaded_file($_FILES['feedbackFile']['tmp_name'][$requirementId], $uploadFilePath);
        }

        // Update team requirements
        $updateSql = "UPDATE team_requirements SET status = ?, feedback = ?, feedback_file = ? WHERE team_id = ? AND requirement_id = ?";
        $updateParams = [$status, $feedback, $newFileName, $_SESSION['team_id'][0], $requirementId];
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute($updateParams);
        $executedQueries[] = interpolateQuery($updateSql, $updateParams);
        
        // Send feedback notification to students if feedback was provided
        if (!empty($feedback) && $updateStmt->rowCount() > 0) {
            try {
                require_once dirname(__DIR__, 2) . '/assets/includes/notification_functions.php';
                createRequirementFeedbackNotifications($pdo, $_SESSION['team_id'][0], $requirementId, $feedback);
                error_log("Requirement feedback notification sent for Team ID " . $_SESSION['team_id'][0] . ", Req ID $requirementId");
            } catch (Exception $notifException) {
                // Don't fail the update if notification fails, just log it
                error_log("Failed to create requirement feedback notification: " . $notifException->getMessage());
            }
        }
    }

    echo json_encode([
        'success' => true,
        'postedData' => $postedData,
        'postedFiles' => $postedFiles,
        'executedQueries' => $executedQueries // All executed queries with parameters
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'executedQueries' => $executedQueries ?? []]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'executedQueries' => $executedQueries ?? []]);
}
?>