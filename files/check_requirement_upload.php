<?php
// Ensure errors are displayed for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary setup files
require_once '../assets/setup/env.php'; 
include '../assets/setup/db.inc.php'; 

// Define the IDs to check
$teamIdToCheck = 5;
$requirementIdToCheck = 5;

// Prepare response structure
header('Content-Type: application/json');
$response = [
    'team_id' => $teamIdToCheck,
    'requirement_id' => $requirementIdToCheck,
    'has_upload' => false, // Default to false
    'success' => false,
    'message' => ''
];

try {
    // Prepare the SQL query to count matching uploads
    // Assumes a 'requirement_id' column exists in 'uploaded_files'
    $sql = "SELECT COUNT(*) 
            FROM uploaded_files 
            WHERE team_id = :team_id 
              AND requirement_id = :requirement_id";
              
    $stmt = $pdo->prepare($sql);
    
    // Bind the parameters
    $stmt->bindParam(':team_id', $teamIdToCheck, PDO::PARAM_INT);
    $stmt->bindParam(':requirement_id', $requirementIdToCheck, PDO::PARAM_INT);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the count
    $uploadCount = $stmt->fetchColumn();
    
    // Set 'has_upload' based on the count
    if ($uploadCount > 0) {
        $response['has_upload'] = true;
    }
    
    $response['success'] = true;
    $response['message'] = $response['has_upload'] ? 'Upload found.' : 'No upload found.';

} catch (PDOException $e) {
    // Log the detailed error
    error_log("Database error checking requirement upload: " . $e->getMessage());
    $response['message'] = 'Database error occurred. Check server logs.';
    // Ensure success remains false
    $response['success'] = false; 
} catch (Exception $e) {
    // Catch any other general errors
    error_log("General error checking requirement upload: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred.';
    // Ensure success remains false
    $response['success'] = false; 
}

// Output the JSON response
echo json_encode($response);
exit;
