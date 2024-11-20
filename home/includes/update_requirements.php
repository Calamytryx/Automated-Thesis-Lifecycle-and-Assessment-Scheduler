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
        $feedbackFile = $_FILES['feedbackFile'][$requirementId]['name'] ?? '';

        // Upload feedback file if present
        if (!empty($feedbackFile)) {
            $uploadDir = '../../feedback/';
            $uploadFilePath = $uploadDir . basename($feedbackFile);
            move_uploaded_file($_FILES['feedbackFile'][$requirementId]['tmp_name'], $uploadFilePath);
        }

        $stmt = $pdo->prepare("UPDATE team_requirements SET status = ?, feedback = ?, feedback_file = ? WHERE team_id = ? AND requirement_id = ?");
        $stmt->execute([$status, $feedback, $feedbackFile, $_SESSION['team_id'], $requirementId]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
