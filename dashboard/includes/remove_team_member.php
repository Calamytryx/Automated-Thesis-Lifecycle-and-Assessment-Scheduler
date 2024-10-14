<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $team_id = $_POST['team_id'] ?? '';

    if (!empty($user_id) && !empty($team_id)) {
        try {
            $pdo->beginTransaction();

            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            $sql = "DELETE FROM team_members WHERE user_id = ? AND team_id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$user_id, $team_id]);

            // Enable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            if ($result) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Team member removed successfully';
            } else {
                throw new Exception('Failed to remove team member');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing user_id or team_id';
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);