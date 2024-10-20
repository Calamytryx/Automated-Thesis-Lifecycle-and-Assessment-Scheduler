
<?php
/**
 * This script handles updating items in the database based on POST data.
 * 
 * 
 * Dependencies:
 * - Requires database connection setup from db.inc.php.
 * - Requires edit functions from edit_functions.php.
 * 
 * Functionality:
 * - Enables error reporting for debugging.
 * - Logs all received POST data.
 * - Processes POST requests to update items in specified tables.
 * - Supports updating 'teams' table with nested updates for team members and research titles.
 * - Supports updating other tables with dynamic field updates.
 * - Uses transactions to ensure data integrity.
 * - Provides JSON response indicating success or failure.
 * 
 * POST Parameters:
 * - table: The name of the table to update.
 * - id: The ID of the item to update.
 * - Additional parameters depend on the table being updated.
 * 
 * Response:
 * - JSON object with 'success' (boolean) and 'message' (string) fields.
 * 
 * Error Handling:
 * - Rolls back transaction on failure.
 * - Logs detailed error messages for debugging.
 */
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/edit_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

// Log all POST data
error_log("Received POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';

    error_log("Table: $table, ID: $id");

    if (!empty($table) && !empty($id)) {
        try {
            $pdo->beginTransaction();

            if ($table === 'teams') {
                // Handle team update
                $name = $_POST['name'] ?? '';
                $title = $_POST['title'] ?? '';
                $members = json_decode($_POST['members'] ?? '[]', true);
            
                error_log("Updating team: Name=$name, Title=$title, Members=" . print_r($members, true));
            
                // Update team name
                $sql = "UPDATE `teams` SET `name` = ? WHERE `id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $id]);
                error_log("Team name updated. Affected rows: " . $stmt->rowCount());
            
                // Update research title
                $sql = "UPDATE `research_titles` SET `title` = ? WHERE `team_id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $id]);
                error_log("Research title updated. Affected rows: " . $stmt->rowCount());
            
                // Update team members
                $currentMembers = $pdo->query("SELECT user_id FROM team_members WHERE team_id = $id")->fetchAll(PDO::FETCH_COLUMN);
                $newMembers = array_column($members, 'id');

                // Remove members not in the new list
                $membersToRemove = array_diff($currentMembers, $newMembers);
                if (!empty($membersToRemove)) {
                    $sql = "DELETE FROM team_members WHERE team_id = ? AND user_id IN (" . implode(',', array_fill(0, count($membersToRemove), '?')) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array_merge([$id], $membersToRemove));
                    error_log("Removed members: " . implode(', ', $membersToRemove));
                }

                // Add new members and update roles
                $sql = "INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE role = VALUES(role)";
                $stmt = $pdo->prepare($sql);

                foreach ($members as $member) {
                    $stmt->execute([$id, $member['id'], $member['role']]);
                    error_log("Updated/Added member. User ID: {$member['id']}, Role: {$member['role']}, Team ID: $id. Affected rows: " . $stmt->rowCount());
                }

                $result = true; // Assume success if no exception is thrown
            } else {
                // Handle other tables as before
                $updateData = [];
                $params = [];
                foreach ($_POST as $key => $value) {
                    if ($key !== 'table' && $key !== 'id') {
                        if ($table === 'research_titles' && $key === 'approved') {
                            $updateData[] = "approved_at = ?";
                            $params[] = $value ? date('Y-m-d H:i:s') : null;
                        } else {
                            $updateData[] = "`$key` = ?";
                            $params[] = $value;
                        }
                    }
                }

                if ($table === 'research_titles' && !isset($_POST['approved'])) {
                    $updateData[] = "approved_at = ?";
                    $params[] = null;
                }

                $params[] = $id;

                $sql = "UPDATE `$table` SET " . implode(', ', $updateData) . " WHERE id = ?";
                error_log("SQL Query: $sql");
                error_log("Parameters: " . print_r($params, true));

                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($params);
            }

            if ($result) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Item updated successfully';
            } else {
                $pdo->rollBack();
                $response['message'] = 'Failed to update item';
                error_log("PDO Error Info: " . print_r($stmt->errorInfo(), true));
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $response['message'] = 'Database error: ' . $e->getMessage();
            error_log("PDO Exception: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Missing table or id';
        error_log("Missing table or id. Table: '$table', ID: '$id'");
    }
} else {
    $response['message'] = 'Invalid request method';
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
}

header('Content-Type: application/json');
echo json_encode($response);
