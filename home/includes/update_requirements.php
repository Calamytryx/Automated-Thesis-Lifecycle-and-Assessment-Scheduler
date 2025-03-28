<?php
header('Content-Type: application/json');
session_start();
require '../../assets/setup/db.inc.php';

if (!isset($_SESSION['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    if (!isset($_POST['requirements']) || !is_array($_POST['requirements'])) {
        throw new Exception('Invalid requirements data');
    }
    foreach ($_POST['requirements'] as $requirementId) {
        $status = $_POST['status'][$requirementId];
        $feedback = $_POST['feedback'][$requirementId];
        $feedbackFile = $_FILES['feedbackFile']['name'][$requirementId] ?? '';

        // Upload feedback file if present
        if (!empty($feedbackFile)) {
            // Get team name and requirement name
            $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
            $teamStmt->execute([$_SESSION['team_id'][0]]);
            $teamName = $teamStmt->fetchColumn();

            $reqStmt = $pdo->prepare("SELECT name FROM requirements WHERE id = ?");
            $reqStmt->execute([$requirementId]);
            $requirementName = $reqStmt->fetchColumn();

            // Generate new file name
            $newFileName = 'feedback-' . $teamName . '-' . $requirementName . '-' . date('Ymd') . '.' . pathinfo($feedbackFile, PATHINFO_EXTENSION);
            $uploadDir = '../feedback/';
            $uploadFilePath = $uploadDir . basename($newFileName);
            move_uploaded_file($_FILES['feedbackFile']['tmp_name'][$requirementId], $uploadFilePath);
        }

        $stmt = $pdo->prepare("UPDATE team_requirements SET status = ?, feedback = ?, feedback_file = ? WHERE team_id = ? AND requirement_id = ?");
        $stmt->execute([$status, $feedback, $newFileName ?? '', $_SESSION['team_id'][0], $requirementId]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
