<?php
/**
 * This script fetches the user's defense schedules and general schedules from the database.
 * 
 * 
 * It starts a session and enables error reporting. It then connects to the database and retrieves
 * the schedules based on the user type (student or staff) stored in the session.
 * 
 * The script performs the following operations:
 * 
 * 1. Starts a session and enables error reporting.
 * 2. Connects to the database using a required setup file.
 * 3. Retrieves the user ID and user type from the session.
 * 4. Initializes empty arrays for defense schedules and user schedules.
 * 5. Fetches defense schedules:
 *    - If the user is a student, it fetches their defense schedules including panelist usernames.
 *    - If the user is a staff member, it fetches defense schedules where they are a panelist.
 * 6. Fetches user schedules:
 *    - Retrieves the user's general schedules (e.g., classes) from the database.
 * 7. Combines the defense schedules and user schedules into a response array.
 * 8. Returns the response as a JSON object.
 * 9. Handles any database errors by logging them and returning an error response.
 * 
 * @throws PDOException If there is a database error.
 * 
 * @return void Outputs a JSON encoded response with the user's schedules or an error message.
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../assets/setup/db.inc.php';

$user_id = $_SESSION['id'];
$user_type = $_SESSION['usertype']; // Assuming you store user type in session

try {
    $defense_schedules = [];
    $user_schedules = [];

    if ($user_type == 1) {
        // Fetch defense schedules using team_id
        $team_stmt = $pdo->prepare("SELECT team_id FROM team_members WHERE user_id = ?");
        $team_stmt->execute([$user_id]);
        $team_id = $team_stmt->fetchColumn();

        if ($team_id) {
            $defense_stmt = $pdo->prepare("
                SELECT 
                    ds.id as defense_schedule_id, -- Added defense schedule ID
                    ds.schedule_date as date,
                    ds.start_time,
                    ds.end_time,
                    ds.room,
                    ds.team_id, -- Include team_id here
                    t.name as team_name,
                    CONCAT('Defense with team: ', 
                           t.name
                    ) as description
                FROM defense_schedules ds -- Added alias ds
                JOIN teams t ON ds.team_id = t.id
                WHERE ds.team_id = ? AND ds.approval_status = 'approved'
                ORDER BY date, start_time
            ");
            $defense_stmt->execute([$team_id]);
            $defense_schedules = $defense_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif ($user_type == 2 || $user_type == 0) {
        // Fetch defense schedules where the user is a panelist
        // Note: rubric_group_id is no longer needed - decision-support auto-determines it
        $defense_stmt = $pdo->prepare("
            SELECT 
                ds.id as defense_schedule_id,
                ds.schedule_date as date,
                ds.start_time,
                ds.end_time,
                ds.room,
                ds.team_id,
                ds.defense_status,
                CASE 
                    WHEN ds.schedule_date < CURDATE() THEN 1
                    WHEN ds.schedule_date = CURDATE() AND ds.end_time < CURTIME() THEN 1
                    ELSE 0
                END as defense_is_past,
                (SELECT COUNT(*) FROM evaluation_per_panel epp WHERE epp.defense_schedule_id = ds.id AND epp.evaluator_id = ?) as has_evaluated,
                t.name as team_name,
                CONCAT('Defense with team: ', t.name) as description
            FROM defense_schedules ds
            JOIN teams t ON ds.team_id = t.id
            WHERE (ds.panelist_id = ? OR ds.panelist_id2 = ? OR ds.panelist_id3 = ?)
            AND ds.approval_status = 'approved'
            ORDER BY ds.schedule_date, ds.start_time
        ");
        $defense_stmt->execute([$user_id, $user_id, $user_id, $user_id]);
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
?>
