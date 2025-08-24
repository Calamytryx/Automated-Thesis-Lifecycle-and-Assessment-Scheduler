<?php
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$progressId = $_GET['id'] ?? null;

if (!$progressId) {
    echo json_encode(['status' => 'error', 'message' => 'No progress ID provided']);
    exit;
}

try {
    // Check progress from database
    $stmt = $pdo->prepare("SELECT status, message, percentage, created_at FROM schedule_progress WHERE id = ?");
    $stmt->execute([$progressId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$progress) {
        echo json_encode(['status' => 'error', 'message' => 'Progress not found']);
        exit;
    }
    
    // Clean up old progress records (older than 1 hour)
    $cleanupStmt = $pdo->prepare("DELETE FROM schedule_progress WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $cleanupStmt->execute();
    
    echo json_encode([
        'status' => $progress['status'],
        'message' => $progress['message'],
        'percentage' => $progress['percentage']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
