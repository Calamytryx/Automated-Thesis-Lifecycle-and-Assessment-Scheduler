<?php
require '../assets/setup/db.inc.php';

// Check if defense_schedule_id is provided
if (!isset($_GET['defense_schedule_id']) || empty($_GET['defense_schedule_id'])) {
    echo json_encode(['error' => 'Defense schedule ID is required']);
    exit;
}

$defenseScheduleId = intval($_GET['defense_schedule_id']);

try {
    // Fetch the form assignment for this defense schedule
    $query = "SELECT embed_link FROM form_assignments WHERE defense_schedule_id = ? AND is_active = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$defenseScheduleId]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        echo json_encode(['error' => 'No form assigned to this defense schedule']);
        exit;
    }
    
    // Return the embed link
    echo json_encode(['embed_link' => $result['embed_link']]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 