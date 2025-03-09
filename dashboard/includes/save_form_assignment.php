<?php
require '../../assets/setup/db.inc.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if required fields are provided
if (!isset($_POST['embed_link']) || empty($_POST['embed_link']) || 
    !isset($_POST['defense_schedule_id']) || empty($_POST['defense_schedule_id'])) {
    echo json_encode(['error' => 'Embed link and defense schedule ID are required']);
    exit;
}

$embedLink = $_POST['embed_link'];
$defenseScheduleId = intval($_POST['defense_schedule_id']);
$formId = isset($_POST['form_id']) && !empty($_POST['form_id']) ? intval($_POST['form_id']) : null;

try {
    // Check if a form is already assigned to this defense schedule
    $checkQuery = "SELECT id FROM form_assignments WHERE defense_schedule_id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$defenseScheduleId]);
    $existingForm = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingForm && (!$formId || $existingForm['id'] != $formId)) {
        echo json_encode(['error' => 'A form is already assigned to this defense schedule. Please edit the existing form instead.']);
        exit;
    }
    
    if ($formId) {
        // Update existing form assignment
        $query = "UPDATE form_assignments SET embed_link = ?, defense_schedule_id = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$embedLink, $defenseScheduleId, $formId]);
    } else {
        // Insert new form assignment
        $query = "INSERT INTO form_assignments (defense_schedule_id, embed_link, is_active) VALUES (?, ?, 1)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$defenseScheduleId, $embedLink]);
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 