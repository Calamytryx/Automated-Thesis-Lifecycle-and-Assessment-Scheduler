<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $table = $_POST['table'] ?? '';
    
    if (empty($id) || empty($table)) {
        $response['message'] = 'ID and table are required';
        echo json_encode($response);
        exit;
    }
    
    try {
        if ($table === 'default_schedules') {
            $stmt = $pdo->prepare("DELETE FROM default_schedules WHERE id = ?");
        } else if ($table === 'user_schedules') {
            $stmt = $pdo->prepare("DELETE FROM user_schedules WHERE id = ?");
        } else {
            $response['message'] = 'Invalid table specified';
            echo json_encode($response);
            exit;
        }
        
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Schedule deleted successfully';
        } else {
            $response['message'] = 'Failed to delete schedule';
        }
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Delete schedule error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
