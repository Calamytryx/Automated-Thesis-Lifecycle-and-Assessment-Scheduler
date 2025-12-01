<?php
// CRITICAL: Start session FIRST before accessing $_SESSION
session_start();

require_once '../../assets/setup/db.inc.php';
require_once '../../assets/includes/program_filter.php'; // Include FIRST so functions are available

header('Content-Type: application/json');
$response = ['success' => false, 'programs' => [], 'message' => ''];

try {
    // Get user session data with explicit checks
    if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
        error_log("get_programs_grouped.php - ERROR: Session variables not set! SESSION dump: " . json_encode($_SESSION));
        throw new Exception("Session not properly initialized");
    }
    
    $userId = $_SESSION['id'];
    $usertype = $_SESSION['usertype'];
    
    error_log("get_programs_grouped.php - Filtering for userId: $userId, usertype: $usertype");
    
    // Use program_filter to get visible programs for this user
    $visiblePrograms = getVisiblePrograms($pdo, $userId, $usertype);
    error_log("get_programs_grouped.php - program_filter returned " . count($visiblePrograms) . " programs");
    
    // Fallback: If no programs from filter, get all programs
    if (empty($visiblePrograms)) {
        error_log("get_programs_grouped.php - No programs from filter, using all programs as fallback");
        $stmt = $pdo->query("SELECT id, college, department, name, specialization 
                            FROM programs 
                            ORDER BY college, department, name, specialization");
        $visiblePrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("get_programs_grouped.php - Fallback query returned " . count($visiblePrograms) . " programs");
    }
    
    // Format display names and structure data
    $formattedPrograms = [];
    foreach ($visiblePrograms as $program) {
        $displayName = $program['name'];
        if (!empty($program['specialization'])) {
            $displayName .= ' - ' . $program['specialization'];
        }
        $formattedPrograms[] = [
            'id' => $program['id'],
            'college' => $program['college'],
            'department' => $program['department'] ?? '',
            'display_name' => $displayName
        ];
    }

    $response['success'] = true;
    $response['programs'] = $formattedPrograms;
    error_log("get_programs_grouped.php - Returning " . count($formattedPrograms) . " programs to client");

} catch (PDOException $e) {
    error_log("Error fetching grouped programs (PDO): " . $e->getMessage());
    $response['message'] = 'Database error fetching programs.';
} catch (Exception $e) {
    error_log("General error fetching grouped programs: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred.';
}

echo json_encode($response);
exit;
