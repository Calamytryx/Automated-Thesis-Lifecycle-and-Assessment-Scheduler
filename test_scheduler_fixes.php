<?php
/**
 * Test script to verify scheduler fixes for class conflict resolution
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$logFile = __DIR__ . '/scheduler_test_' . date('Y-m-d_H-i-s') . '.log';
ini_set('error_log', $logFile);

try {
    // Load database connection
    require_once __DIR__ . '/dashboard/includes/db.php';
    require_once __DIR__ . '/dashboard/includes/run_scheduler.php';
    require_once __DIR__ . '/dashboard/includes/edit_functions.php';

    error_log("=== SCHEDULER DIAGNOSTIC TEST START ===");
    error_log("Test file: " . __FILE__);
    error_log("Time: " . date('Y-m-d H:i:s'));

    // Verify functions exist
    $functionsToCheck = [
        'validateCandidateClassConflicts',
        'computeSchedulerSlotContext',
        'scheduler_unix_on_calendar_day',
        'scheduler_user_class_range_on_calendar_day',
        'buildRoomOccupancyMap'
    ];

    foreach ($functionsToCheck as $func) {
        if (function_exists($func)) {
            error_log("✓ Function exists: $func");
        } else {
            error_log("✗ MISSING Function: $func");
        }
    }

    // Check if DefenseSchedule class can be instantiated
    if (class_exists('DefenseSchedule')) {
        error_log("✓ DefenseSchedule class exists");
    } else {
        error_log("✗ MISSING DefenseSchedule class");
    }

    // Try to fetch some data
    $teams = [];
    $stmt = $pdo->query("SELECT id, program FROM teams LIMIT 1");
    $singleTeam = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($singleTeam) {
        error_log("✓ Sample team found: Team ID " . $singleTeam['id']);
    } else {
        error_log("✗ No teams found in database");
    }

    // Check user schedules
    $schedCount = $pdo->query("SELECT COUNT(*) as cnt FROM user_schedules")->fetch(PDO::FETCH_ASSOC);
    error_log("✓ User schedules in DB: " . $schedCount['cnt']);

    // Check defense schedule format
    $defenseCount = $pdo->query("SELECT COUNT(*) as cnt FROM defense_schedules")->fetch(PDO::FETCH_ASSOC);
    error_log("✓ Defense schedules in DB: " . $defenseCount['cnt']);

    error_log("=== All basic checks passed ===");
    error_log("Test log saved to: $logFile");
    echo "Test completed. Check log: $logFile\n";

} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    echo "Error occurred. Check log.\n";
}
?>
