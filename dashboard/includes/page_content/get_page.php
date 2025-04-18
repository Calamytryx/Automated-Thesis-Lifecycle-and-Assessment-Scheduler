<?php
/**
 * Get page content details
 * 
 * This file handles getting page content details for a specific page ID
 */

session_start();
require_once '../../../assets/setup/db.inc.php';

// Check if the user is authorized
if (!isset($_SESSION['auth']) || $_SESSION['usertype'] != 0) {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if the page ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Page ID is required']);
    exit;
}

$page_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT); 

try {
    // Get the page content details
    $query = "SELECT * FROM page_content WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $page_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        header('HTTP/1.0 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Page not found']);
        exit;
    }
    
    // Return the page content details
    echo json_encode(['success' => true, 'data' => $page]);
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}