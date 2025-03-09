<?php
require '../../assets/setup/db.inc.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if form_id is provided
if (!isset($_POST['form_id']) || empty($_POST['form_id'])) {
    echo json_encode(['error' => 'Form ID is required']);
    exit;
}

$formId = intval($_POST['form_id']);

try {
    // Delete the form assignment
    $query = "DELETE FROM form_assignments WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$formId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Form assignment not found']);
        exit;
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 