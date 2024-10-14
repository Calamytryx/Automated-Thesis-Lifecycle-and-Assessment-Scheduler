<?php
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
                $sql = "UPDATE `team_members` SET `role` = ? WHERE `id` = ? AND `team_id` = ?";
                $stmt = $pdo->prepare($sql);
            
                foreach ($members as $index => $member) {
                    if (isset($member['id']) && isset($member['role'])) {
                        $stmt->execute([$member['role'], $member['id'], $id]);
                        error_log("Updated member. ID: {$member['id']}, Role: {$member['role']}, Team ID: $id. Affected rows: " . $stmt->rowCount());
                    } else {
                        error_log("Skipped member update. Member data: " . print_r($member, true));
                    }
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