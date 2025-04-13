<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Failed to fetch available rubrics.', 'data' => []];

try {
    // Fetch active rubrics (id, name, type)
    // You might add more filters later if needed (e.g., exclude rubrics already in ANY group)
    $stmt = $pdo->query("
        SELECT id, name, rubric_type
        FROM rubrics
        WHERE is_active = 1
        ORDER BY name ASC
    ");

    $rubrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['message'] = 'Available rubrics fetched successfully.';
    $response['data'] = $rubrics;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Error fetching available rubrics: " . $e->getMessage());
}

echo json_encode($response);
?>
