<?php
require_once '../../assets/setup/env.php'; 
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'programs' => [], 'message' => ''];

try {
    // Fetch programs ordered for proper grouping
    $sql = "SELECT id, college, department, name, specialization 
            FROM programs 
            ORDER BY college, department, name, specialization";
    $stmt = $pdo->query($sql);
    $programsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format display names and structure data
    $formattedPrograms = [];
    foreach ($programsData as $program) {
        $displayName = $program['name'];
        if (!empty($program['specialization'])) {
            $displayName .= ' - ' . $program['specialization'];
        }
        $formattedPrograms[] = [
            'id' => $program['id'],
            'college' => $program['college'],
            'department' => $program['department'],
            'display_name' => $displayName
        ];
    }

    $response['success'] = true;
    $response['programs'] = $formattedPrograms;

} catch (PDOException $e) {
    error_log("Error fetching grouped programs: " . $e->getMessage());
    $response['message'] = 'Database error fetching programs.';
} catch (Exception $e) {
    error_log("General error fetching grouped programs: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred.';
}

echo json_encode($response);
exit;
