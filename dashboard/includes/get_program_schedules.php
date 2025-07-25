<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($_GET['program'])) {
            $whereConditions[] = "program = ?";
            $params[] = $_GET['program'];
        }
        
        if (!empty($_GET['year'])) {
            $whereConditions[] = "year = ?";
            $params[] = $_GET['year'];
        }
        
        if (!empty($_GET['section'])) {
            $whereConditions[] = "section = ?";
            $params[] = $_GET['section'];
        }
        
        $sql = "SELECT * FROM default_schedules";
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        $sql .= " ORDER BY day_of_week, start_time";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['schedules'] = $schedules;
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Get program schedules error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
