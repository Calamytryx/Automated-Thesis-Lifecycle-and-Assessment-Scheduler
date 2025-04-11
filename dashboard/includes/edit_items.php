<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    $id = $_POST['id'];
    
    // Remove table and id from $_POST so only fields remain
    unset($_POST['table'], $_POST['id']);
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }

    // Special handling for rubrics remains unchanged
    if ($table === 'rubrics') {
        $pdo->beginTransaction();
        try {
            $structure = isset($_POST['structure']) ? $_POST['structure'] : null;
            if ($structure) {
                $decoded = json_decode($structure);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid rubric structure: ' . json_last_error_msg());
                }
            }
            $stmt = $pdo->prepare("UPDATE rubrics SET name = :name, description = :description, structure = :structure WHERE id = :id");
            $stmt->execute([
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'structure' => $structure,
                'id' => $id
            ]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Rubric updated successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // NEW: For other tables, update using a generic handler.
    require_once __DIR__ . '/edit_functions.php';
    $result = handleEditSubmission($pdo, $table, $id, $_POST);
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item update failed']);
    }
}