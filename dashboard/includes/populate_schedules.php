<?php
echo "Script is running!<br>";
require_once '../../assets/setup/db.inc.php';

function generateRandomSchedule($userId) {
    global $pdo;
    
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $classNames = ['Math', 'Science', 'English', 'History', 'Programming', 'Physics', 'Chemistry', 'Literature', 'Economics'];
    
    $scheduleCount = rand(3, 5); // 3 to 5 classes per week
    
    for ($i = 0; $i < $scheduleCount; $i++) {
        $day = $days[array_rand($days)];
        $startHour = rand(7, 19); // 7 AM to 7 PM
        $duration = rand(3, 5); // 1 to 5 hours
        $startTime = sprintf("%02d:00:00", $startHour);
        $endTime = sprintf("%02d:00:00", $startHour + $duration);
        $className = $classNames[array_rand($classNames)];
        
        $stmt = $pdo->prepare("INSERT INTO user_schedules (user_id, day_of_week, start_time, end_time, class_name) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt->execute([$userId, $day, $startTime, $endTime, $className])) {
            echo "Error inserting schedule for user $userId: " . implode(", ", $stmt->errorInfo()) . "<br>";
        } else {
            echo "Schedule inserted for user $userId<br>";
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT id, usertype FROM users WHERE usertype IN (1, 2)"); // 1 for students, 2 for staff
if (!$stmt) {
    echo "Error fetching users: " . implode(", ", $pdo->errorInfo()) . "<br>";
} else {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($users) . " users<br>";

    foreach ($users as $user) {
        generateRandomSchedule($user['id']);
    }

    echo "Schedules generation completed!";
}
