<?php
header('Content-Type: application/json');
try {
    require_once '../../assets/setup/db.inc.php';

    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user id', 'count' => 0]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT t.id) as total FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.role = 'adviser'");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = isset($row['total']) ? intval($row['total']) : 0;

    echo json_encode(['success' => true, 'count' => $count]);
} catch (Exception $e) {
    error_log('get_adviser_team_count error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error', 'count' => 0]);
}

?>
