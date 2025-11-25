<?php
/**
 * Migration script to add title_proposal column to teams table
 * Run this from the browser or command line to add the column if it doesn't exist
 */

session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php';

// Check if user is authenticated and is admin
// Note: Can be run from CLI without authentication
$isWebRequest = php_sapi_name() !== 'cli';
if ($isWebRequest && (!isset($_SESSION['id']) || $_SESSION['usertype'] !== 0)) {
    http_response_code(403);
    die('Access denied. Admin only.');
}

try {
    // Get database name
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    
    // Check if column exists
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'teams' AND COLUMN_NAME = 'title_proposal'
    ");
    $stmt->execute([$dbName]);
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add the column
        $pdo->exec("ALTER TABLE teams ADD COLUMN title_proposal TINYINT NOT NULL DEFAULT 0 COMMENT 'Mark team as title proposal - professor role will be automatic'");
        echo json_encode([
            'success' => true,
            'message' => 'Column title_proposal added successfully to teams table'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Column title_proposal already exists'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
