<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../assets/setup/db.inc.php'; // Adjust path as needed

$response = ['success' => false, 'message' => 'Invalid request.'];
error_log("--- update_rubric_status.php ---"); // Log script start

// Check if required POST data is received
if (isset($_POST['rubric_id']) && isset($_POST['is_active'])) {
    $rubricId = filter_input(INPUT_POST, 'rubric_id', FILTER_VALIDATE_INT);
    // Validate is_active as 0 or 1
    $isActiveInput = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);

    error_log("Received rubric_id: " . print_r($_POST['rubric_id'], true) . ", is_active: " . print_r($_POST['is_active'], true)); // Log raw input
    error_log("Validated rubric_id: " . ($rubricId === false ? 'INVALID' : $rubricId) . ", Validated is_active: " . ($isActiveInput === false ? 'INVALID' : $isActiveInput)); // Log validated input

    // Check if validation passed
    if ($rubricId === false || $rubricId <= 0) {
        $response['message'] = 'Invalid Rubric ID provided.';
        error_log("Validation failed: Invalid Rubric ID.");
    } elseif ($isActiveInput === false) { // Check if validation failed (not 0 or 1)
        $response['message'] = 'Invalid status value provided. Must be 0 or 1.';
        error_log("Validation failed: Invalid status value.");
    } else {
        $isActive = $isActiveInput; // Assign validated value

        try {
            // Prepare the SQL statement
            $sql = "UPDATE rubrics SET is_active = :is_active WHERE id = :id";
            error_log("Preparing SQL: " . $sql); // Log SQL
            $stmt = $pdo->prepare($sql);

            // Bind parameters and execute
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT);
            $stmt->bindParam(':id', $rubricId, PDO::PARAM_INT);

            error_log("Executing update for ID: $rubricId with is_active: $isActive"); // Log before execution
            $executionSuccess = $stmt->execute();
            $rowCount = $stmt->rowCount();
            error_log("Execution result: " . ($executionSuccess ? 'Success' : 'Failure') . ", Rows affected: " . $rowCount); // Log after execution

            if ($executionSuccess) {
                if ($rowCount > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Rubric status updated successfully.';
                } else {
                    // Rubric ID might not exist, or status was already the same
                    $response['message'] = 'Rubric not found or status already set to the requested value.';
                    // Consider setting success to true here as well if no change isn't an error
                    // $response['success'] = true;
                    error_log("Update executed but no rows affected for ID: $rubricId. Might not exist or status unchanged.");
                }
            } else {
                $response['message'] = 'Database update failed.';
                error_log("PDO execute() returned false for rubric status update for ID: $rubricId. Error Info: " . print_r($stmt->errorInfo(), true));
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
            error_log("PDOException updating rubric status for ID $rubricId: " . $e->getMessage());
        } catch (Exception $e) {
            $response['message'] = 'An unexpected error occurred: ' . $e->getMessage();
            error_log("Exception updating rubric status for ID $rubricId: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Missing required parameters (rubric_id, is_active).';
    error_log("Missing POST parameters for update_rubric_status.php. Received: " . print_r($_POST, true));
}

error_log("Sending response: " . json_encode($response)); // Log response
// Send the JSON response
echo json_encode($response);
exit;
?>