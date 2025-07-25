<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Check if user_schedules table exists, create if not
        $checkTableSQL = "SHOW TABLES LIKE 'user_schedules'";
        $result = $pdo->query($checkTableSQL);
        
        if ($result->rowCount() == 0) {
            // Create table if it doesn't exist
            $createTableSQL = "CREATE TABLE user_schedules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                schedule_type VARCHAR(50) NOT NULL,
                day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                building VARCHAR(100),
                room VARCHAR(50),
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $pdo->exec($createTableSQL);
        }
        
        $sql = "SELECT us.*, CONCAT(u.first_name, ' ', u.last_name) AS faculty_name 
                FROM user_schedules us 
                JOIN users u ON us.user_id = u.id 
                WHERE u.usertype = 2";
        
        $params = [];
        
        // Apply faculty filter
        if (!empty($_GET['faculty_id'])) {
            $sql .= " AND us.user_id = ?";
            $params[] = $_GET['faculty_id'];
        }
        
        $sql .= " ORDER BY us.day_of_week, us.start_time";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['schedules'] = $schedules;
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Get faculty schedules error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
