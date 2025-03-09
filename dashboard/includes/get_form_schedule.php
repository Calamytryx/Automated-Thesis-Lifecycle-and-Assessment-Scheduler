<?php
require '../../assets/setup/db.inc.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if form_id is provided
if (!isset($_GET['form_id']) || empty($_GET['form_id'])) {
    echo json_encode(['error' => 'Form ID is required']);
    exit;
}

$formId = intval($_GET['form_id']);

try {
    // Fetch the defense schedule ID for this form
    $query = "SELECT defense_schedule_id FROM form_assignments WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$formId]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        echo json_encode(['error' => 'Form assignment not found']);
        exit;
    }
    
    // Return the defense schedule ID as JSON
    echo json_encode(['defense_schedule_id' => $result['defense_schedule_id']]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 