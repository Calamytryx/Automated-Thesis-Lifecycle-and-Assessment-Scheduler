<?php
// Suppress errors and start output buffering
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Start output buffering

session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php'; // Adjust path as needed
require_once __DIR__ . '/../../dashboard/includes/defense_type_functions.php'; // Add defense type helper

// Set header to return JSON - Moved after potential output from included files
header('Content-Type: application/json');

$response = ['success' => false, 'error' => 'An unknown error occurred.'];
$uploadDir = __DIR__ . '/../../assets/uploads/submission/'; // Define upload directory

// Ensure upload directory exists and is writable
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        $response['error'] = 'Failed to create upload directory.';
        error_log("Upload Error: Failed to create directory: " . $uploadDir);
        ob_end_clean(); // Clean buffer before outputting JSON
        echo json_encode($response);
        exit;
    }
}
if (!is_writable($uploadDir)) {
    $response['error'] = 'Upload directory is not writable.';
    error_log("Upload Error: Directory not writable: " . $uploadDir);
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

// 1. Validate User Session and Role
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] != 1) { // Assuming usertype 1 is student
    $response['error'] = 'Unauthorized access.';
    error_log("Upload Error: Unauthorized access attempt. Session ID: " . ($_SESSION['id'] ?? 'None'));
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}
$userId = $_SESSION['id'];
// Ensure team_id is treated as an array and get the first element
$teamIdArray = isset($_SESSION['team_id']) ? (array)$_SESSION['team_id'] : [];
$teamId = $teamIdArray[0] ?? null;

if (!$teamId || !filter_var($teamId, FILTER_VALIDATE_INT) || $teamId <= 0) { // Check if teamId is null, not int, or not positive
    $response['error'] = 'User is not associated with a valid team.';
    error_log("Upload Error: User ID {$userId} has invalid team ID in session: " . print_r($teamId, true));
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

// *** NEW: Verify Team ID exists in the database ***
try {
    $teamCheckSql = "SELECT id FROM teams WHERE id = :team_id";
    $stmtTeamCheck = $pdo->prepare($teamCheckSql);
    $stmtTeamCheck->execute([':team_id' => $teamId]);
    if ($stmtTeamCheck->fetch() === false) {
        // Refine the error message for the user
        $response['error'] = 'Your associated team (ID: ' . htmlspecialchars($teamId) . ') could not be found in the system. Please try logging out and logging back in. If the issue continues, contact an administrator.';
        error_log("Upload Error: Team ID {$teamId} from session for User ID {$userId} not found in teams table.");
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
} catch (PDOException $e) {
    $response['error'] = 'Database error during team validation.';
    error_log("Upload DB Error: PDOException during team validation for Team ID {$teamId}. Error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode($response);
    exit;
}
// *** END NEW ***

if (!$teamId) {
    $response['error'] = 'User is not associated with a team.';
    error_log("Upload Error: User ID {$userId} has no team ID in session.");
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

// 2. Validate Input Parameters
$requirementId = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
$documentName = filter_input(INPUT_POST, 'document_name', FILTER_SANITIZE_STRING); // Use FILTER_SANITIZE_STRING or appropriate filter

if (!$requirementId || !$documentName) {
    $response['error'] = 'Missing or invalid requirement information.';
    error_log("Upload Error: Missing requirement_id or document_name. POST: " . print_r($_POST, true));
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

// 3. Validate File Upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE directive specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $errorCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $response['error'] = $uploadErrors[$errorCode] ?? 'Unknown file upload error.';
    error_log("Upload Error: File upload failed. Error code: {$errorCode}. Message: {$response['error']}");
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

$fileTmpPath = $_FILES['file']['tmp_name'];
$fileName = $_FILES['file']['name'];
$fileSize = $_FILES['file']['size'];
$fileType = $_FILES['file']['type'];
$fileNameCmps = explode(".", $fileName);
$fileExtension = strtolower(end($fileNameCmps));

// Sanitize filename and create a unique name
$safeFileNameBase = preg_replace('/[^A-Za-z0-9.\-_]/', '_', pathinfo($fileName, PATHINFO_FILENAME)); // Sanitize original filename base
$uniqueFileName = $teamId . '_' . $requirementId . '_' . time() . '_' . $safeFileNameBase . '.' . $fileExtension;
$destPath = $uploadDir . $uniqueFileName;

// Allowed file types (example: allow PDF and DOCX)
$allowedExtensions = ['pdf', 'docx', 'doc'];
if (!in_array($fileExtension, $allowedExtensions)) {
    $response['error'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions);
    error_log("Upload Error: Invalid file type '{$fileExtension}' for file '{$fileName}'.");
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

// 4. Move Uploaded File
if (!move_uploaded_file($fileTmpPath, $destPath)) {
    $response['error'] = 'Failed to move uploaded file.';
    error_log("Upload Error: Failed to move '{$fileTmpPath}' to '{$destPath}'. Check permissions and destination path.");
    ob_end_clean(); // Clean buffer before outputting JSON
    echo json_encode($response);
    exit;
}

// 5. Update Database with multi-submission support
try {
    // Get requirement details to check if multiple submissions are allowed
    $reqDetails = getRequirementDetails($pdo, $requirementId);
    $allowMultipleSubmissions = $reqDetails['allow_multiple_submissions'] ?? 0;
    $maxSubmissions = $reqDetails['max_submissions'] ?? 1;

    // Check existing team_requirements record
    $checkSql = "SELECT id, status FROM team_requirements WHERE team_id = :team_id AND requirement_id = :requirement_id";
    $stmtCheck = $pdo->prepare($checkSql);
    $stmtCheck->execute([':team_id' => $teamId, ':requirement_id' => $requirementId]);
    $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    // For multi-submission requirements, check how many files already submitted
    if ($allowMultipleSubmissions) {
        $countSql = "SELECT COUNT(*) as file_count FROM team_requirement_files 
                     WHERE team_id = :team_id AND requirement_id = :requirement_id AND deleted_at IS NULL";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute([':team_id' => $teamId, ':requirement_id' => $requirementId]);
        $countRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $currentFileCount = $countRow['file_count'] ?? 0;

        // Check if limit exceeded
        if ($currentFileCount >= $maxSubmissions) {
            if (file_exists($destPath)) {
                unlink($destPath);
            }
            $response['error'] = "Maximum number of submissions ({$maxSubmissions}) already reached for this requirement.";
            error_log("Upload Error: File limit exceeded. Team ID {$teamId}, Req ID {$requirementId}, Current: {$currentFileCount}, Max: {$maxSubmissions}");
            ob_end_clean();
            echo json_encode($response);
            exit;
        }

        // Insert into team_requirement_files for multi-submission requirement
        $insertFilesSql = "INSERT INTO team_requirement_files 
                          (team_id, requirement_id, file_name, original_file_name, file_path, file_size, submission_number, status, submitted_by, submitted_at)
                          VALUES (:team_id, :requirement_id, :file_name, :original_file_name, :file_path, :file_size, :submission_number, 'submitted', :submitted_by, NOW())";
        $stmtInsertFiles = $pdo->prepare($insertFilesSql);
        $stmtInsertFiles->execute([
            ':team_id' => $teamId,
            ':requirement_id' => $requirementId,
            ':file_name' => $uniqueFileName,
            ':original_file_name' => $fileName,
            ':file_path' => $destPath,
            ':file_size' => $fileSize,
            ':submission_number' => $currentFileCount + 1,
            ':submitted_by' => $userId
        ]);

        // Also update team_requirements to mark as submitted
        if ($existingRecord) {
            $updateMainSql = "UPDATE team_requirements 
                             SET status = 'submitted', submitted_at = NOW(), file_name = :file_name
                             WHERE id = :id";
            $stmtUpdateMain = $pdo->prepare($updateMainSql);
            $stmtUpdateMain->execute([
                ':file_name' => $uniqueFileName,
                ':id' => $existingRecord['id']
            ]);
        } else {
            $insertMainSql = "INSERT INTO team_requirements (team_id, requirement_id, file_name, status, submitted_at)
                             VALUES (:team_id, :requirement_id, :file_name, 'submitted', NOW())";
            $stmtInsertMain = $pdo->prepare($insertMainSql);
            $stmtInsertMain->execute([
                ':team_id' => $teamId,
                ':requirement_id' => $requirementId,
                ':file_name' => $uniqueFileName
            ]);
        }

        $response['success'] = true;
        $response['submission_number'] = $currentFileCount + 1;
        $response['max_submissions'] = $maxSubmissions;
        unset($response['error']);
        error_log("Upload Success (Multi): File #{$response['submission_number']}/{$maxSubmissions} '{$uniqueFileName}' uploaded for Team ID {$teamId}, Req ID {$requirementId}.");

    } else {
        // Single submission mode (original logic)
        // Security check: Only allow uploads if status is pending or no record exists
        if ($existingRecord && $existingRecord['status'] !== 'pending') {
            // Delete the uploaded file since we won't process it
            if (file_exists($destPath)) {
                unlink($destPath);
            }
            $response['error'] = 'File upload not allowed. Requirement status does not permit new submissions.';
            error_log("Upload Security Block: Team ID {$teamId}, Req ID {$requirementId} - Status: {$existingRecord['status']}");
            ob_end_clean();
            echo json_encode($response);
            exit;
        }

        if ($existingRecord) {
            // Update existing record (only if status was pending)
            $sql = "UPDATE team_requirements
                    SET file_name = :file_name, status = 'submitted', submitted_at = NOW(), feedback = NULL, feedback_file = NULL
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':file_name' => $uniqueFileName,
                ':id' => $existingRecord['id']
            ]);
        } else {
            // Insert new record
            $sql = "INSERT INTO team_requirements (team_id, requirement_id, file_name, status, submitted_at)
                    VALUES (:team_id, :requirement_id, :file_name, 'submitted', NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':team_id' => $teamId,
                ':requirement_id' => $requirementId,
                ':file_name' => $uniqueFileName
            ]);
        }

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            unset($response['error']);
            error_log("Upload Success: File '{$uniqueFileName}' uploaded for Team ID {$teamId}, Req ID {$requirementId}. DB updated.");
        } else {
            if (file_exists($destPath)) {
                 $response['success'] = true;
                 unset($response['error']);
                 error_log("Upload Warning: File '{$uniqueFileName}' uploaded, but DB record was not inserted/updated (or data was identical). Team ID {$teamId}, Req ID {$requirementId}.");
            } else {
                 $response['error'] = 'Database record not updated and file move failed.';
                 error_log("Upload Error: File '{$uniqueFileName}' DB record not updated AND file move failed. Team ID {$teamId}, Req ID {$requirementId}.");
            }
        }
    }

    // Send requirement submission notification to advisers
    if ($response['success']) {
        try {
            require_once dirname(__DIR__, 2) . '/assets/includes/notification_functions.php';
            createRequirementSubmissionNotifications($pdo, $teamId, $requirementId, $uniqueFileName);
            error_log("Requirement submission notification sent for Team ID {$teamId}, Req ID {$requirementId}");
        } catch (Exception $notifException) {
            // Don't fail the upload if notification fails, just log it
            error_log("Failed to create requirement submission notification: " . $notifException->getMessage());
        }
    }

} catch (PDOException $e) {
    // Provide more specific error message
    $response['error'] = 'Database error: ' . $e->getMessage();
    // Attempt to delete the moved file if DB update fails
    if (file_exists($destPath)) {
        unlink($destPath);
    }
    // Log the specific PDO error
    error_log("Upload DB Error: PDOException for Team ID {$teamId}, Req ID {$requirementId}. File '{$uniqueFileName}' deleted. Error: " . $e->getMessage());
} catch (Exception $e) {
    $response['error'] = 'An unexpected error occurred during database update.';
     if (file_exists($destPath)) {
        unlink($destPath);
    }
    error_log("Upload General Error: Exception for Team ID {$teamId}, Req ID {$requirementId}. File '{$uniqueFileName}' deleted. Error: " . $e->getMessage());
}

// Clean (erase) the output buffer and turn off output buffering
ob_end_clean();

// Ensure only JSON is output
echo json_encode($response);
exit;
?>
