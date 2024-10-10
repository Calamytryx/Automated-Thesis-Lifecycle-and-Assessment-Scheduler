<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../assets/setup/db.inc.php';

$user_id = $_SESSION['id'];
$user_type = $_SESSION['usertype']; // Assuming you store user type in session

try {
    $defense_schedules = [];
    $user_schedules = [];

    // Fetch defense schedules
    if ($user_type == 1) { // Student
        $defense_stmt = $pdo->prepare("
            SELECT 
                schedule_date as date,
                start_time,
                end_time,
                room,
                CONCAT('Defense with panelists: ', 
                       (SELECT username FROM users WHERE id = panelist_id), ', ',
                       (SELECT username FROM users WHERE id = panelist_id2), ', ',
                       (SELECT username FROM users WHERE id = panelist_id3)
                ) as description
            FROM defense_schedules 
            WHERE student_id = ?
            ORDER BY date, start_time
        ");
        $defense_stmt->execute([$user_id]);
        $defense_schedules = $defense_stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($user_type == 2) { // Staff
        $defense_stmt = $pdo->prepare("
            SELECT 
                schedule_date as date,
                start_time,
                end_time,
                room,
                CONCAT('Defense for student: ', 
                       (SELECT username FROM users WHERE id = student_id)
                ) as description
            FROM defense_schedules 
            WHERE panelist_id = ? OR panelist_id2 = ? OR panelist_id3 = ?
            ORDER BY date, start_time
        ");
        $defense_stmt->execute([$user_id, $user_id, $user_id]);
        $defense_schedules = $defense_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch user schedules
    $user_stmt = $pdo->prepare("
        SELECT 
            day_of_week as date,
            start_time,
            end_time,
            'N/A' as room,
            class_name as description
        FROM user_schedules 
        WHERE user_id = ?
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time
    ");
    $user_stmt->execute([$user_id]);
    $user_schedules = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'defense_schedules' => $defense_schedules,
        'user_schedules' => $user_schedules
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $response = [
        'success' => false,
        'error' => "Database error: " . $e->getMessage()
    ];
    echo json_encode($response);
}
