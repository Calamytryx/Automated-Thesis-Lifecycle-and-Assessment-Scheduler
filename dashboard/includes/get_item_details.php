<?php

/**
 * This script retrieves item details from a specified table in the database.
 * 
 * Dependencies:
 * - Requires the database connection setup file located at '../../assets/setup/db.inc.php'.
 * 
 * Input:
 * - Expects a POST request with the following parameters:
 *   - 'table': The name of the table from which to retrieve the item details.
 *   - 'id': The ID of the item to retrieve.
 * 
 * Output:
 * - Returns a JSON response with the following structure:
 *   - success: A boolean indicating whether the operation was successful.
 *   - data: An associative array containing the item details (if successful).
 *   - message: A string containing an error message (if unsuccessful).
 * 
 * Functionality:
 * - Validates the 'table' parameter against a list of allowed tables.
 * - If the table is 'teams', retrieves additional details such as team members and research title.
 * - For other tables, retrieves the item details directly.
 * - Handles errors such as missing parameters, invalid table names, and item not found.
 * 
 * Example Usage:
 * - POST request with 'table' set to 'teams' and 'id' set to a valid team ID will return the team details along with members and research title.
 * - POST request with 'table' set to 'users' and 'id' set to a valid user ID will return the user details.
 */

require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $table = $_POST['table'];

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs', 'user_schedules', 'page_content'];

    if (!in_array($table, $allowedTables)) {
        $response['message'] = 'Invalid table';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $response['success'] = true;
                $response['data'] = $data;
                if ($table === 'programs') {
                    // Fetch all programs
                    $stmt = $pdo->query("SELECT * FROM programs");
                    $response['programs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else if ($table === 'users') {
                    // Fetch user roles
                    $stmt = $pdo->query("SELECT * FROM users");
                    $response['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else if ($table === 'thesis_topics') {
                    // Fetch thesis topics
                    $stmt = $pdo->query("SELECT id, title FROM thesis_topics");
                    $response['topics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else if ($table === 'defense_schedules') {
                    // Fetch teams
                    $stmt = $pdo->query("SELECT id, name FROM teams");
                    $response['teams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Fetch staff members filtered by current team (exclude adviser)
                    // Include faculty (usertype=2) and program chairs (usertype=0, id!=0) from same college
                    
                    // First get the team's college
                    $teamCollegeStmt = $pdo->prepare("
                        SELECT p.college 
                        FROM teams t
                        JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)
                        WHERE t.id = :team_id
                        LIMIT 1
                    ");
                    $teamCollegeStmt->execute(['team_id' => $data['team_id']]);
                    $teamCollege = $teamCollegeStmt->fetchColumn();
                    
                    // Build staff query - include faculty AND program chairs from same college
                    // Users don't have a college column, so we join to programs to get their college  
                    if ($teamCollege) {
                        $stmt = $pdo->prepare("
                            SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name 
                            FROM users u 
                            LEFT JOIN programs p2 ON u.program = CONCAT(p2.name, CASE WHEN p2.specialization IS NOT NULL AND p2.specialization != '' THEN CONCAT(' - ', p2.specialization) ELSE '' END)
                            WHERE (
                                u.usertype = 2 
                                OR (u.usertype = 0 AND u.id != 0 AND p2.college = :college)
                            )
                            AND u.id NOT IN (
                                SELECT user_id FROM team_members 
                                WHERE role = 'adviser' AND team_id = :team_id
                            )
                            ORDER BY name
                        ");
                        $stmt->execute(['college' => $teamCollege, 'team_id' => $data['team_id']]);
                    } else {
                        // Just include all faculty members
                        $stmt = $pdo->prepare("
                            SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name 
                            FROM users u 
                            WHERE u.usertype = 2
                            AND u.id NOT IN (
                                SELECT user_id FROM team_members 
                                WHERE role = 'adviser' AND team_id = :team_id
                            )
                            ORDER BY name
                        ");
                        $stmt->execute(['team_id' => $data['team_id']]);
                    }
                    
                    $response['staff'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Staff fetched for team {$data['team_id']}: " . count($response['staff']) . " members");

                    // Fetch current panelists for the defense schedule
                    // Build array of panelist IDs that are not null/empty
                    $panelistIds = [];
                    if (!empty($data['panelist_id'])) {
                        $panelistIds[] = $data['panelist_id'];
                    }
                    if (!empty($data['panelist_id2'])) {
                        $panelistIds[] = $data['panelist_id2'];
                    }
                    if (!empty($data['panelist_id3'])) {
                        $panelistIds[] = $data['panelist_id3'];
                    }

                    $currentPanelists = [];
                    if (!empty($panelistIds)) {
                        $placeholders = str_repeat('?,', count($panelistIds) - 1) . '?';
                        $stmt = $pdo->prepare("
                            SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
                            FROM users u
                            WHERE u.id IN ($placeholders)
                            ORDER BY FIELD(u.id, " . implode(',', array_fill(0, count($panelistIds), '?')) . ")
                        ");
                        $stmt->execute(array_merge($panelistIds, $panelistIds));
                        $currentPanelists = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    
                    $response['data']['panelists'] = $currentPanelists;

                    // Debugging: Log the fetched panelists
                    error_log("Fetched panelists: " . print_r($currentPanelists, true));
                } else if ($table === 'teams') {
                    // Fetch team members
                    $stmt = $pdo->prepare("
                        SELECT tm.user_id as id, CONCAT(u.first_name, ' ', u.last_name) as name, tm.role
                        FROM team_members tm
                        JOIN users u ON tm.user_id = u.id
                        WHERE tm.team_id = ?
                    ");
                    $stmt->execute([$id]);
                    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Fetch research title
                    $stmt = $pdo->prepare("SELECT title FROM research_titles WHERE team_id = ?");
                    $stmt->execute([$id]);
                    $researchTitle = $stmt->fetchColumn();

                    // Fetch program as text (already stored as text in teams table)
                    $response['data']['members'] = $members;
                    $response['data']['title'] = $researchTitle;
                    $response['data']['program_teams'] = $data['program']; // program is stored as text
                } else if ($table === 'research_titles') {
                    // Fetch teams data to populate the dropdown
                    $stmt = $pdo->query("SELECT id, name FROM teams");
                    $response['teams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else if ($table === 'rubrics') {
                    try {
                        // Get basic rubric info
                        $stmt = $pdo->prepare("SELECT * FROM rubrics WHERE id = ?");
                        $stmt->execute([$id]);
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($data) {
                            // Get quality criteria
                            $stmt = $pdo->prepare("SELECT * FROM rubric_quality_criteria WHERE rubric_id = ? ORDER BY quality_level");
                            $stmt->execute([$id]);
                            $data['quality_criteria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Get rubric rows
                            $stmt = $pdo->prepare("SELECT * FROM rubric_rows WHERE rubric_id = ? ORDER BY order_index");
                            $stmt->execute([$id]);
                            $data['rows'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $response['success'] = true;
                            $response['data'] = $data;
                        } else {
                            $response['message'] = 'Rubric not found';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Database error: ' . $e->getMessage();
                    }
                } else if($table === 'user_schedules') {
                    // Fetch user schedules
                    $stmt = $pdo->prepare("SELECT * FROM user_schedules WHERE id = ?");
                    $stmt->execute([$id]);
                    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($schedule) {
                        // Fetch user details
                        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                        $stmt->execute([$schedule['user_id']]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        $schedule['user_name'] = $user ? "{$user['first_name']} {$user['last_name']}" : 'Unknown User';

                        // Program is stored as text in user_schedules table
                        $schedule['program_name'] = $schedule['program'] ?: 'Unknown Program';

                        // There is no course_id column, so set course_name to 'No Course Assigned'
                        $schedule['course_name'] = 'No Course Assigned';

                        // Add schedule to response
                        $response['success'] = true;
                        $response['data'] = $schedule;
                    } else {
                        $response['message'] = 'Schedule not found';
                    }
                }   
            } else {
                $response['message'] = 'Item not found';
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
