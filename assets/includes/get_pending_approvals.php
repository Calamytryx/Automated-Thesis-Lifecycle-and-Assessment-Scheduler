<?php
session_start();
require_once '../setup/db.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['id'];

// Debug: Log the user ID
error_log("DEBUG get_pending_approvals: User ID = " . $userId);

try {
    echo json_encode([
        'success' => true,
        'pendingApprovals' => [],
        'count' => 0,
        'message' => 'Panel approvals are no longer required. Assignments are auto-accepted.'
    ]);
    
} catch (Exception $e) {
    error_log("Error getting pending approvals: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error retrieving pending approvals'
    ]);
}
?>
