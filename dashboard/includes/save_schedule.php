<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    $scheduleType = $_POST['schedule_type'] ?? '';
    $id = $_POST['id'] ?? '';
    
    try {
        if ($table === 'default_schedules') {
            // Program schedule
            $program = $_POST['program'] ?? '';
            $year = $_POST['year'] ?? '';
            $section = $_POST['section'] ?? '';
            $className = $_POST['class_name'] ?? '';
            $building = $_POST['building'] ?? '';
            $room = $_POST['room'] ?? '';
            $dayOfWeek = $_POST['day_of_week'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            
            // Validation
            if (empty($program) || empty($year) || empty($section) || empty($className) || 
                empty($building) || empty($room) || empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
                $response['message'] = 'All fields are required for program schedule';
                echo json_encode($response);
                exit;
            }
            
            if ($id) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE default_schedules SET 
                    program = ?, year = ?, section = ?, class_name = ?, building = ?, room = ?, 
                    day_of_week = ?, start_time = ?, end_time = ? WHERE id = ?");
                $result = $stmt->execute([$program, $year, $section, $className, $building, $room, 
                    $dayOfWeek, $startTime, $endTime, $id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO default_schedules 
                    (program, year, section, class_name, building, room, day_of_week, start_time, end_time) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$program, $year, $section, $className, $building, $room, 
                    $dayOfWeek, $startTime, $endTime]);
            }
            
        } else if ($table === 'user_schedules') {
            // Faculty schedule - need to create user_schedules table structure
            $userId = $_POST['user_id'] ?? '';
            $scheduleTypeField = $_POST['schedule_type_field'] ?? '';
            $building = $_POST['building'] ?? '';
            $room = $_POST['room'] ?? '';
            $description = $_POST['description'] ?? '';
            $dayOfWeek = $_POST['day_of_week'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            
            // Validation
            if (empty($userId) || empty($scheduleTypeField) || empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
                $response['message'] = 'User ID, schedule type, day, start time, and end time are required';
                echo json_encode($response);
                exit;
            }
            
            // Create user_schedules table if it doesn't exist
            $createTableSQL = "CREATE TABLE IF NOT EXISTS user_schedules (
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
            
            if ($id) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE user_schedules SET 
                    user_id = ?, schedule_type = ?, day_of_week = ?, start_time = ?, end_time = ?, 
                    building = ?, room = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$userId, $scheduleTypeField, $dayOfWeek, $startTime, $endTime, 
                    $building, $room, $description, $id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO user_schedules 
                    (user_id, schedule_type, day_of_week, start_time, end_time, building, room, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$userId, $scheduleTypeField, $dayOfWeek, $startTime, $endTime, 
                    $building, $room, $description]);
            }
        } else {
            $response['message'] = 'Invalid table specified';
            echo json_encode($response);
            exit;
        }
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Schedule saved successfully';
        } else {
            $response['message'] = 'Failed to save schedule';
        }
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Schedule save error: ' . $e->getMessage());
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        error_log('Schedule save error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
