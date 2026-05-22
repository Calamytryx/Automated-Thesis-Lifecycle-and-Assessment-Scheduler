<?php
// Test script to run scheduler with diagnostics
require_once __DIR__ . '/dashboard/includes/header.php';
require_once __DIR__ . '/dashboard/includes/run_scheduler.php';

// Create a minimal PDO connection if not already set
if (empty($pdo)) {
    // Use existing connection mechanism
    require_once __DIR__ . '/dashboard/config.php';
}

// Fetch test data
$teams = [];
$panelists = [];
$rooms = [];

try {
    $teamStmt = $pdo->query("SELECT id, name, program, area_of_expertise, adviser_id, defense_type FROM teams LIMIT 2");
    $teams = $teamStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $panelistStmt = $pdo->query("SELECT id, first_name, last_name FROM users WHERE usertype = 2 LIMIT 3");
    $panelists = $panelistStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $roomStmt = $pdo->query("SELECT DISTINCT room FROM defense_schedules WHERE room IS NOT NULL LIMIT 2");
    $rooms = array_column($roomStmt->fetchAll(PDO::FETCH_ASSOC), 'room');
    
    if (empty($rooms)) {
        $rooms = ['Defense Room 1', 'Defense Room 2'];
    }
    
    error_log("TEST: Found " . count($teams) . " teams, " . count($panelists) . " panelists, " . count($rooms) . " rooms");
    
    // Load user schedules
    error_log("TEST: Loading user schedules...");
    $userSchedules = fetchUserSchedules($pdo);
    error_log("TEST: Loaded schedules for " . count($userSchedules) . " users");
    
    // Log sample of what we have
    $sampleCount = 0;
    foreach ($userSchedules as $userId => $schedules) {
        foreach ($schedules as $sch) {
            error_log(sprintf(
                "TEST SCHEDULE: user=%d class=%s room=%s day=%d time=%s-%s",
                $userId,
                $sch['class_name'] ?? 'UNKNOWN',
                $sch['room'] ?? 'NO_ROOM',
                $sch['day_of_week'] ?? -1,
                $sch['start_time'] ?? '?',
                $sch['end_time'] ?? '?'
            ));
            $sampleCount++;
            if ($sampleCount >= 10) break 2;
        }
    }
    
    // Build occupancy map
    error_log("TEST: Building room occupancy map...");
    $occupancyMap = buildRoomOccupancyMap($userSchedules);
    error_log("TEST: Occupancy map built");
    
} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
}

echo "Diagnostics written to error log";
?>
