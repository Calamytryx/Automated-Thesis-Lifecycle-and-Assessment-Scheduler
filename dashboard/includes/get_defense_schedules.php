<?php
require '../../assets/setup/db.inc.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Fetch defense schedules with team names
    $query = "
        SELECT ds.id, ds.team_id, ds.schedule_date, ds.start_time, t.name as team_name
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        WHERE ds.schedule_date >= CURRENT_DATE()
        ORDER BY ds.schedule_date ASC, ds.start_time ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the number of schedules found
    error_log('Found ' . count($schedules) . ' defense schedules');
    
    // Return the schedules as JSON
    echo json_encode($schedules);
} catch (PDOException $e) {
    error_log('Database error in get_defense_schedules.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 