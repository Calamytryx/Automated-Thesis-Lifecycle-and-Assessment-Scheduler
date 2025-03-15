<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rubric_id = $_POST['rubric_id'] ?? null;
    $is_active = $_POST['is_active'] ?? false;

    if (!$rubric_id) {
        echo json_encode(['success' => false, 'message' => 'Rubric ID is required']);
        exit;
    }

    try {
        // If setting this rubric as active, deactivate all other rubrics
        if ($is_active) {
            $stmt = $pdo->prepare("UPDATE rubrics SET is_active = 0");
            $stmt->execute();
        }

        // Update the current rubric's status
        $stmt = $pdo->prepare("UPDATE rubrics SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active ? 1 : 0, $rubric_id]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}  