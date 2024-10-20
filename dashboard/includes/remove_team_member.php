<?php
/**
 * This script handles the removal of a team member from a team.
 * 
 * 
 * It expects a POST request with the following parameters:
 * - user_id: The ID of the user to be removed from the team.
 * - team_id: The ID of the team from which the user will be removed.
 * 
 * The script performs the following actions:
 * 1. Checks if the request method is POST.
 * 2. Validates the presence of user_id and team_id in the POST data.
 * 3. Initiates a database transaction.
 * 4. Disables foreign key checks.
 * 5. Executes a DELETE query to remove the user from the team.
 * 6. Enables foreign key checks.
 * 7. Commits the transaction if the DELETE query is successful.
 * 8. Rolls back the transaction and returns an error message if any step fails.
 * 
 * The response is returned as a JSON object with the following structure:
 * - success: A boolean indicating whether the operation was successful.
 * - message: A string containing a success or error message.
 * 
 * Dependencies:
 * - Requires the database connection setup file located at /../../assets/setup/db.inc.php.
 * 
 * Example response:
 * {
 *   "success": true,
 *   "message": "Team member removed successfully"
 * }
 * 
 * Error response example:
 * {
 *   "success": false,
 *   "message": "Database error: <error_message>"
 * }
 */
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $team_id = $_POST['team_id'] ?? '';

    if (!empty($user_id) && !empty($team_id)) {
        try {
            $pdo->beginTransaction();

            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            $sql = "DELETE FROM team_members WHERE user_id = ? AND team_id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$user_id, $team_id]);

            // Enable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            if ($result) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Team member removed successfully';
            } else {
                throw new Exception('Failed to remove team member');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing user_id or team_id';
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);