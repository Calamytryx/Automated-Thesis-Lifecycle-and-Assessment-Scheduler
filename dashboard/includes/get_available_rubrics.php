<?php
// filepath: c:\xampp\htdocs\atlas\dashboard\includes\get_available_rubrics.php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Failed to load rubrics.', 'data' => []];

try {
    // Fetch active rubrics (id, name, type)
    // You might want to add more filters if needed (e.g., based on user permissions)
    $stmt = $pdo->query("SELECT id, name, rubric_type FROM rubrics WHERE is_active = 1 ORDER BY name ASC");
    $rubrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rubrics) {
        $response['success'] = true;
        $response['message'] = 'Available rubrics loaded successfully.';
        $response['data'] = $rubrics;
    } else {
        $response['success'] = true; // Still success, just no data
        $response['message'] = 'No active rubrics found.';
    }

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Error fetching available rubrics: " . $e->getMessage());
}

echo json_encode($response);
?>