<?php
// Basic security check - ensure user is logged in if necessary
// session_start(); // Uncomment if using sessions
// if (!isset($_SESSION['auth'])) {
//     header('Content-Type: application/json');
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

// Include the file that defines DB_HOST, DB_NAME, etc. 
// Adjust the path if your config file is different.
require_once '../assets/setup/env.php'; 

include '../assets/setup/db.inc.php'; // Now DB_HOST etc. should be defined

header('Content-Type: application/json');

$level = $_GET['level'] ?? null;
$parentId = $_GET['parent_id'] ?? null;

$response = ['success' => false, 'locations' => []];

try {
    // Change 'college' level to fetch all programs grouped by college
    if ($level === 'program_locations') { 
        // Get all programs with necessary details, ordered for grouping
        $sql = "SELECT id, name, specialization, college 
                FROM programs 
                ORDER BY college, name, specialization";
        $stmt = $pdo->query($sql);
        $response['locations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
    } 
    // Remove the old 'program' level case as it's handled by 'program_locations' now
    // elseif ($level === 'program' && $parentId !== null) { ... } 
    elseif ($level === 'team' && $parentId !== null) {
        // Get teams for a specific program ID (this remains the same)
        $sql = "SELECT id, name FROM teams WHERE program = :program_id ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':program_id', $parentId, PDO::PARAM_INT);
        $stmt->execute();
        $response['locations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
    } else {
        $response['message'] = 'Invalid level or missing parent ID.';
    }
} catch (PDOException $e) {
    // Log error properly in a real application
    $response['message'] = 'Database error: ' . $e->getMessage(); 
}

echo json_encode($response);
exit;
