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
            $stmt = $pdo->prepare("SELECT * FROM default_schedules WHERE id = ?");
        } else if ($table === 'user_schedules') {
            $stmt = $pdo->prepare("SELECT * FROM user_schedules WHERE id = ?");
        } else {
            $response['message'] = 'Invalid table specified';
            echo json_encode($response);
            exit;
        }
        
        $stmt->execute([$id]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($schedule) {
            $response['success'] = true;
            $response['data'] = $schedule;
        } else {
            $response['message'] = 'Schedule not found';
        }
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Get schedule details error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
