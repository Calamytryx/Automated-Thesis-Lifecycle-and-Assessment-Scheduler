<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS name 
                              FROM users WHERE usertype = 2 ORDER BY first_name, last_name");
        $stmt->execute();
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['faculty'] = $faculty;
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Get faculty error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
