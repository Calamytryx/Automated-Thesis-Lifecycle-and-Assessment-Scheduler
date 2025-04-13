<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $rubrics_json = $_POST['rubrics'] ?? '[]';

    if (empty($name)) {
        $response['message'] = 'Group name is required.';
        echo json_encode($response);
        exit;
    }

    $rubrics = json_decode($rubrics_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($rubrics)) {
        $response['message'] = 'Invalid rubrics data format.';
        echo json_encode($response);
        exit;
    }

    $pdo->beginTransaction();
    try {
        // 1. Insert or Update `rubric_groups` table
        if ($group_id) {
            // Update existing group
            $groupStmt = $pdo->prepare("UPDATE rubric_groups SET name = :name, description = :description, updated_at = NOW() WHERE id = :id");
            $groupStmt->execute([':name' => $name, ':description' => $description, ':id' => $group_id]);
            $current_group_id = $group_id;
            error_log("Updated rubric group ID: " . $current_group_id);
        } else {
            // Insert new group
            $groupStmt = $pdo->prepare("INSERT INTO rubric_groups (name, description, created_at, updated_at) VALUES (:name, :description, NOW(), NOW())");
            $groupStmt->execute([':name' => $name, ':description' => $description]);
            $current_group_id = $pdo->lastInsertId();
            error_log("Inserted new rubric group ID: " . $current_group_id);
        }

        if (!$current_group_id) {
            throw new Exception("Failed to create or update rubric group.");
        }

        // 2. Clear existing `rubric_group_items` for this group
        $clearStmt = $pdo->prepare("DELETE FROM rubric_group_items WHERE group_id = ?");
        $clearStmt->execute([$current_group_id]);
        error_log("Cleared existing items for group ID: " . $current_group_id);

        // 3. Insert new `rubric_group_items`
        if (!empty($rubrics)) {
            $itemStmt = $pdo->prepare("
                INSERT INTO rubric_group_items
                (group_id, rubric_id, order_index, weight, created_at, updated_at)
                VALUES (:group_id, :rubric_id, :order_index, :weight, NOW(), NOW())
            ");

            foreach ($rubrics as $item) {
                // Validate item data
                $rubric_id_val = filter_var($item['rubric_id'], FILTER_VALIDATE_INT);
                $order_index_val = filter_var($item['order_index'], FILTER_VALIDATE_INT);
                // Allow null or valid float for weight
                $weight_val = isset($item['weight']) ? filter_var($item['weight'], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;

                if ($rubric_id_val === false || $order_index_val === false) {
                     error_log("Skipping invalid rubric item data: " . print_r($item, true));
                     continue; // Skip invalid entries
                }

                $itemStmt->execute([
                    ':group_id' => $current_group_id,
                    ':rubric_id' => $rubric_id_val,
                    ':order_index' => $order_index_val,
                    ':weight' => $weight_val // Can be null
                ]);
                 error_log("Inserted item: Group=$current_group_id, Rubric=$rubric_id_val, Order=$order_index_val, Weight=$weight_val");
            }
        }

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Rubric group saved successfully.';
        error_log("=== END SAVE RUBRIC GROUP - SUCCESS ===");

    } catch (Exception $e) {
        $pdo->rollBack();
        $response['message'] = 'Error saving rubric group: ' . $e->getMessage();
        error_log("Error saving rubric group, transaction rolled back: " . $e->getMessage());
        error_log("=== END SAVE RUBRIC GROUP - ERROR ===");
    }
}

echo json_encode($response);
?>
