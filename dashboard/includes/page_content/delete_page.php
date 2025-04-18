<?php
/**
 * Delete page content
 * 
 * This file handles deleting a page content by ID
 */

session_start();
require_once '../../../assets/setup/db.inc.php';

// Check if the user is authorized
if (!isset($_SESSION['auth']) || $_SESSION['usertype'] != 0) { 
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['page_id']) || empty($data['page_id'])) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Page ID is required']);
    exit;
}

$page_id = filter_var($data['page_id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Check if the page exists
    $check_query = "SELECT id FROM page_content WHERE id = :id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':id', $page_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        header('HTTP/1.0 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Page not found']);
        exit;
    }
    
    // Delete the page
    $delete_query = "DELETE FROM page_content WHERE id = :id";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->bindParam(':id', $page_id, PDO::PARAM_INT);
    $delete_stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Page deleted successfully']);
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}