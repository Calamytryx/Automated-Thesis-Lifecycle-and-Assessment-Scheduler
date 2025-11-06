<?php
session_start(); 

// Ensure errors are displayed for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include necessary setup files
require_once '../assets/setup/env.php'; 
include '../assets/setup/db.inc.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// --- Security Checks ---
// 1. Check if user is logged in
if (!isset($_SESSION['auth']) || !isset($_SESSION['id'])) { 
    $response['message'] = 'Unauthorized: Not logged in.'; 
    echo json_encode($response);
    exit;
}
// 2. Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}
// 3. Get filepath from POST data
$filepath = $_POST['filepath'] ?? null;
if (empty($filepath)) {
    $response['message'] = 'Filepath not provided.';
    echo json_encode($response);
    exit;
}

// --- File Path Validation & Deletion ---
define('UPLOAD_DIR_ROOT', realpath(__DIR__ . '/../uploads')); // Get absolute path to uploads dir

if (!UPLOAD_DIR_ROOT) {
     error_log("Upload directory root path could not be resolved.");
     $response['message'] = 'Server configuration error (upload path).';
     echo json_encode($response);
     exit;
}

// Construct the full absolute path to the file
$fullFilePath = UPLOAD_DIR_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filepath);
$resolvedPath = realpath($fullFilePath);

// **Crucial Security:** Ensure the resolved path is within the UPLOAD_DIR_ROOT
if ($resolvedPath === false || strpos($resolvedPath, UPLOAD_DIR_ROOT) !== 0) {
    error_log("Attempt to delete file outside upload directory. Provided: '$filepath', Resolved: '" . ($resolvedPath ?: 'false') . "'");
    $response['message'] = 'Invalid file path or file does not exist.';
    echo json_encode($response);
    exit;
}

// --- Perform Deletion ---
$pdo->beginTransaction();
try {
    // 1. Delete from Database first (using the relative path stored)
    $sql = "DELETE FROM uploaded_files WHERE filepath = :filepath";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':filepath', $filepath, PDO::PARAM_STR); // Use the original relative path
    $deletedRows = $stmt->execute();

    if (!$deletedRows) {
        // If DB delete fails (e.g., file wasn't in DB), maybe still allow file deletion? Or stop?
        // Let's stop for now to be safe. Could also just log a warning.
        throw new Exception("File record not found or could not be deleted from database.");
    }

    // 2. Delete from Filesystem
    if (file_exists($resolvedPath)) {
        if (!unlink($resolvedPath)) {
            throw new Exception("Failed to delete file from filesystem. Check permissions.");
        }
    } else {
        // File existed in DB but not on disk - log this inconsistency but proceed
        error_log("Inconsistency: File '$resolvedPath' found in DB but not on filesystem during delete.");
    }

    // If both succeed (or file didn't exist on disk but DB record deleted)
    $pdo->commit();
    $response['success'] = true;
    $response['message'] = 'File deleted successfully.';

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Deletion error: " . $e->getMessage());
    $response['message'] = $e->getMessage(); // Provide specific error from exception
}

echo json_encode($response);
exit;
