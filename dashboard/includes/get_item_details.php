<?php
/**
 * This script retrieves item details from a specified table in the database.
 * 
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
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];

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

                if ($table === 'defense_schedules') {
                    // Fetch teams
                    $stmt = $pdo->query("SELECT id, name FROM teams");
                    $response['teams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Fetch all staff members
                    $stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE usertype = 2");
                    $response['staff'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Fetch current panelists for the defense schedule
                    $stmt = $pdo->prepare("
                        SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
                        FROM users u
                        WHERE u.id IN (:panelist_id, :panelist_id2, :panelist_id3)
                    ");
                    $stmt->execute([
                        'panelist_id' => $data['panelist_id'],
                        'panelist_id2' => $data['panelist_id2'],
                        'panelist_id3' => $data['panelist_id3']
                    ]);
                    $currentPanelists = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $response['data']['panelists'] = $currentPanelists;

                    // Debugging: Log the fetched panelists
                    error_log("Fetched panelists: " . print_r($currentPanelists, true));
                }
                else if ($table === 'teams') {
                    $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
                    $stmt->execute([$id]);
                    $team = $stmt->fetch(PDO::FETCH_ASSOC);
            
                    if ($team) {
                        // Fetch team members
                        $memberStmt = $pdo->prepare("
                            SELECT tm.user_id as id, CONCAT(u.first_name, ' ', u.last_name) as name, tm.role
                            FROM team_members tm
                            JOIN users u ON tm.user_id = u.id
                            WHERE tm.team_id = ?
                        ");
                        $memberStmt->execute([$id]);
                        $members = $memberStmt->fetchAll(PDO::FETCH_ASSOC);
            
                        // Fetch research title
                        $titleStmt = $pdo->prepare("SELECT title FROM research_titles WHERE team_id = ?");
                        $titleStmt->execute([$id]);
                        $researchTitle = $titleStmt->fetchColumn();
            
                        $team['members'] = $members;
                        $team['title'] = $researchTitle;
            
                        echo json_encode(['success' => true, 'data' => $team]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Team not found']);
                    }
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
                    $stmt->execute([$id]);
                    $item = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($item) {
                        // Add table to the response
                        $item['table'] = $table;
                        echo json_encode(['success' => true, 'data' => $item]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Item not found']);
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