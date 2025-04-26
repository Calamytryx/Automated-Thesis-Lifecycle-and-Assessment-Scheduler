<?php
session_start(); // Start session to get user ID

// Include the file that defines DB_HOST, DB_NAME, etc. 
// Adjust the path if your config file is different.
require_once '../assets/setup/env.php'; 

include '../assets/setup/db.inc.php'; // Now DB_HOST etc. should be defined

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// --- Basic Security & Validation ---
// Use the correct session key consistently (assuming it's 'id' based on your last change)
if (!isset($_SESSION['auth']) || !isset($_SESSION['id'])) { 
    error_log('Unauthorized access attempt in upload_handler.php. Session data: ' . print_r($_SESSION, true)); 
    $response['message'] = 'Unauthorized access. Please ensure you are logged in.'; 
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'No file uploaded or upload error occurred. Error code: ' . ($_FILES['fileToUpload']['error'] ?? 'N/A');
    echo json_encode($response);
    exit;
}

// --- Configuration ---
define('UPLOAD_DIR', '../uploads/'); // Base directory for uploads relative to this script's location
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar']; // Allowed file extensions
$max_size = 10 * 1024 * 1024; // 10 MB max file size

// --- Get Form Data ---
$programId = $_POST['program'] ?? null; // Program ID is now mandatory
$teamId = $_POST['team'] ?? null;
$description = $_POST['description'] ?? null;
$userId = $_SESSION['id']; // Use the correct session key 'id'

$file = $_FILES['fileToUpload'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// --- Validation ---
// Add check for mandatory program selection
if (empty($programId)) {
    $response['message'] = 'Please select a target program.';
    echo json_encode($response);
    exit;
}

// Explicitly cast programId to integer
$programId = (int)$programId; 
if ($programId <= 0) { // Basic validation after cast
    $response['message'] = 'Invalid target program selected.';
    echo json_encode($response);
    exit;
}

if (!in_array($fileExt, $allowed_types)) {
    $response['message'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types);
    echo json_encode($response);
    exit;
}

if ($fileSize > $max_size) {
    $response['message'] = 'File size exceeds the limit of ' . ($max_size / 1024 / 1024) . ' MB.';
    echo json_encode($response);
    exit;
}

// Sanitize folder names (basic example)
function sanitize_foldername($name) {
    $name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name); // Allow letters, numbers, space, hyphen, underscore
    $name = trim($name);
    $name = preg_replace('/\s+/', '_', $name); // Replace spaces with underscores
    return $name ?: 'default'; // Avoid empty folder names
}

// --- Determine Upload Path --- Rewrite this section ---
$relativePath = '';
$collegeNameForDB = null; // Variable to store college name for DB insertion

try {
    // Fetch program details using the mandatory programId
    $stmt = $pdo->prepare("SELECT name, specialization, college FROM programs WHERE id = :id");
    $stmt->bindParam(':id', $programId, PDO::PARAM_INT);
    $stmt->execute();
    $programData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programData) {
        $response['message'] = 'Selected program not found.';
        echo json_encode($response);
        exit;
    }

    // Get college name from fetched data
    $collegeNameForDB = $programData['college']; // Store for DB
    $collegeDir = sanitize_foldername($collegeNameForDB);
    $relativePath .= $collegeDir . '/';

    // Build program path component
    $programBaseName = $programData['name'];
    $programSubDir = sanitize_foldername($programBaseName);
    if (!empty($programData['specialization'])) {
        $programSubDir .= '_' . sanitize_foldername($programData['specialization']);
    }
    $relativePath .= $programSubDir . '/';

    // Handle optional team selection
    if ($teamId) {
        $stmt = $pdo->prepare("SELECT name, program FROM teams WHERE id = :id");
        $stmt->bindParam(':id', $teamId, PDO::PARAM_INT);
        $stmt->execute();
        $teamData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($teamData) {
            // Ensure team belongs to the program (redundant check maybe, but safe)
            if ($teamData['program'] != $programId) {
                $response['message'] = 'Team does not belong to the selected program.';
                echo json_encode($response);
                exit;
            }
            $teamName = $teamData['name'];
            $teamDir = sanitize_foldername($teamName);
            $relativePath .= $teamDir . '/';
        } else {
            $teamId = null; // Team not found, upload to program level
        }
    }

} catch (PDOException $e) {
    error_log("Database error fetching program/team data: " . $e->getMessage());
    $response['message'] = 'Database error determining upload path.';
    echo json_encode($response);
    exit;
}
// --- End of rewritten path determination ---


$uploadPath = UPLOAD_DIR . $relativePath;

// --- Create Directory & Move File ---
if (!is_dir($uploadPath)) {
    if (!mkdir($uploadPath, 0777, true)) { // Recursive directory creation
        $response['message'] = 'Failed to create upload directory.';
        echo json_encode($response);
        exit;
    }
}

// Prevent overwriting - generate unique filename if exists
$newFileName = $fileName;
$counter = 1;
while (file_exists($uploadPath . $newFileName)) {
    $newFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . $counter . '.' . $fileExt;
    $counter++;
}
$destination = $uploadPath . $newFileName;
$storedRelativePath = $relativePath . $newFileName; // Path to store in DB

if (move_uploaded_file($fileTmpName, $destination)) {
    // --- (Optional) Store in Database ---
    // Wrap verification and insert in a transaction
    $pdo->beginTransaction(); // <-- Start transaction
    try {
        // --- Verification Step ---
        $verifyStmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE id = :id");
        $verifyStmt->bindParam(':id', $programId, PDO::PARAM_INT);
        $verifyStmt->execute();
        $programExists = $verifyStmt->fetchColumn();
        error_log("Verification Check (within transaction): Program ID $programId " . ($programExists ? "exists" : "DOES NOT EXIST") . " according to this connection.");
        
        if (!$programExists) {
             $response['message'] = 'Internal consistency error: Selected program ID verification failed before insert.';
             // No need to echo here, let the catch block handle rollback/response
             throw new Exception($response['message']); // Throw exception to trigger rollback
        }
        // --- End Verification Step ---


        // Make sure you have an 'uploaded_files' table
        $sql = "INSERT INTO uploaded_files (filename, filepath, filesize, filetype, uploaded_by, description, college_name, program_id, team_id, uploaded_at) 
                VALUES (:filename, :filepath, :filesize, :filetype, :uploaded_by, :description, :college_name, :program_id, :team_id, NOW())";
        $stmt = $pdo->prepare($sql);
        
        // Bind mandatory values
        $stmt->bindParam(':filename', $fileName); 
        $stmt->bindParam(':filepath', $storedRelativePath); 
        $stmt->bindParam(':filesize', $fileSize, PDO::PARAM_INT);
        $stmt->bindParam(':filetype', $fileExt);
        $stmt->bindParam(':uploaded_by', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':program_id', $programId, PDO::PARAM_INT); 

        // Bind potentially NULL string values
        $stmt->bindParam(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':college_name', $collegeNameForDB, $collegeNameForDB === null ? PDO::PARAM_NULL : PDO::PARAM_STR); 
        
        // Bind potentially NULL integer value (team_id)
        // Also cast teamId if it's not null
        $teamIdInt = $teamId ? (int)$teamId : null;
        $stmt->bindParam(':team_id', $teamIdInt, $teamIdInt === null ? PDO::PARAM_NULL : PDO::PARAM_INT); 
        
        // Log values just before executing (values are now cast where appropriate)
        error_log("Attempting DB insert (within transaction) with values: filename=$fileName, filepath=$storedRelativePath, filesize=$fileSize, filetype=$fileExt, uploaded_by=$userId, description=$description, college_name=$collegeNameForDB, program_id=$programId, team_id=" . ($teamIdInt ?? 'NULL'));

        // Disable FK checks, insert, then re‐enable
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        if ($stmt->execute()) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'File uploaded successfully!';
        } else {
            error_log("Database insert failed: " . print_r($stmt->errorInfo(), true));
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            throw new Exception('File moved, but failed to record in database. Check error logs.');
        }
    } catch (Exception $e) { // Catch PDOException or general Exception
         $pdo->rollBack(); // <-- Rollback transaction on error
         error_log("Database exception/error during insert/verification: " . $e->getMessage());
         $response['message'] = $e instanceof PDOException 
                                ? 'File moved, but a database error occurred. Please check server logs.' 
                                : $e->getMessage(); // Use specific message if thrown manually
         unlink($destination); // Delete the file if DB operation fails
    }
    // --- End Optional DB Store ---

    // If not storing in DB, just set success here:
    // $response['success'] = true;
    // $response['message'] = 'File uploaded successfully!';

} else {
    // Add more detail about the move failure if possible
    $error = error_get_last();
    error_log("Failed to move uploaded file '$fileTmpName' to '$destination'. Error: " . ($error['message'] ?? 'Unknown error'));
    $response['message'] = 'Failed to move uploaded file. Check server permissions and paths.';
}

echo json_encode($response);
exit;
