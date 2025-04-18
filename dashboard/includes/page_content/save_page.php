<?php
/**
 * Save page content
 * 
 * This file handles saving new page content or updating existing page content
 */

session_start();
// Fix the include path to properly reference the database connection file
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

// Get the raw POST data
$json_data = file_get_contents('php://input');

// Make sure we're dealing with clean JSON data
header('Content-Type: application/json');

// Try to decode the JSON data 
$data = json_decode($json_data, true);

if (!$data) {
    // Log the exact error for debugging
    $json_error = json_last_error_msg();
    error_log("JSON Decode Error: " . $json_error . " - Raw data: " . substr($json_data, 0, 200) . "...");
    
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . $json_error]);
    exit;
}

// Validate required fields
if (!isset($data['title']) || empty(trim($data['title'])) ||
    !isset($data['slug']) || empty(trim($data['slug'])) ||
    !isset($data['content']) || empty(trim($data['content'])) ||
    !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Title, slug, content, and status are required']);
    exit;
}

// Sanitize and prepare data
$page_id = isset($data['page_id']) && !empty($data['page_id']) ? filter_var($data['page_id'], FILTER_SANITIZE_NUMBER_INT) : null;
$title = trim($data['title']);
$slug = trim(preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $data['slug']))));
$content = $data['content']; // HTML content
$status = in_array($data['status'], ['draft', 'published']) ? $data['status'] : 'draft';
$user_id = $_SESSION['id'];

try {
    // Check if slug already exists (for different page)
    $slug_check_query = "SELECT id FROM page_content WHERE slug = :slug" . ($page_id ? " AND id != :id" : "");
    $slug_check_stmt = $pdo->prepare($slug_check_query);
    $slug_check_stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    if ($page_id) {
        $slug_check_stmt->bindParam(':id', $page_id, PDO::PARAM_INT);
    }
    $slug_check_stmt->execute();
    
    if ($slug_check_stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'A page with this slug already exists']);
        exit;
    }
    
    if ($page_id) {
        // Update existing page
        $query = "UPDATE page_content SET 
                    title = :title, 
                    slug = :slug, 
                    content = :content, 
                    status = :status, 
                    updated_by = :updated_by
                  WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $page_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':updated_by', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Page updated successfully', 'id' => $page_id]);
    } else {
        // Insert new page
        $query = "INSERT INTO page_content (title, slug, content, status, created_by, updated_by) 
                  VALUES (:title, :slug, :content, :status, :created_by, :updated_by)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':created_by', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':updated_by', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $new_page_id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Page created successfully', 'id' => $new_page_id]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in save_page.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}