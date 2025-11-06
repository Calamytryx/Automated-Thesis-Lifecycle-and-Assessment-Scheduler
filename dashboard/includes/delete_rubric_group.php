<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request or missing ID.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($group_id) {
        $pdo->beginTransaction();
        try {
            // Delete the group. Associated items should be deleted by CASCADE constraint.
            $stmt = $pdo->prepare("DELETE FROM rubric_groups WHERE id = ?");
            $stmt->execute([$group_id]);

            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Rubric group deleted successfully.';
                error_log("Deleted rubric group ID: " . $group_id);
            } else {
                $pdo->rollBack(); // Rollback if no rows were affected (group didn't exist)
                $response['message'] = 'Rubric group not found or already deleted.';
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $response['message'] = 'Database error deleting rubric group: ' . $e->getMessage();
            error_log("Error deleting rubric group (ID: $group_id): " . $e->getMessage());
        }
    }
}

echo json_encode($response);
?>
