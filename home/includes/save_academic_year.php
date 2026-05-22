<?php
session_start();
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

// Only faculty/program chair (usertype 2 or 0) can save academic year
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype']) || !in_array((int)$_SESSION['usertype'], [0, 2], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$facultyId = intval($_SESSION['id']);
$rawInput = isset($_POST['academic_year']) ? trim($_POST['academic_year']) : '';

// Validate: not empty
if (empty($rawInput)) {
    echo json_encode(['success' => false, 'message' => 'Academic year is required']);
    exit;
}

// Validate: max length (e.g. "2025-2026, 1st Semester" = ~25 chars)
if (mb_strlen($rawInput) > 50) {
    echo json_encode(['success' => false, 'message' => 'Academic year value is too long']);
    exit;
}

// Validate: must match expected pattern like "2025-2026, 1st Semester"
if (!preg_match('/^\d{4}-\d{4},\s*(1st Semester|2nd Semester|Summer)$/', $rawInput)) {
    echo json_encode(['success' => false, 'message' => 'Invalid academic year format']);
    exit;
}

// Sanitize for storage
$academicYear = htmlspecialchars($rawInput, ENT_QUOTES, 'UTF-8');

try {
    // Update academic_year for all active section_professors records of this faculty
    $stmt = $pdo->prepare("
        UPDATE section_professors 
        SET academic_year = ? 
        WHERE professor_id = ? AND status = 'active'
    ");
    $stmt->execute([$academicYear, $facultyId]);

    // Verify at least one active assignment exists for this professor
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM section_professors 
        WHERE professor_id = ? AND status = 'active'
    ");
    $checkStmt->execute([$facultyId]);
    $activeCount = $checkStmt->fetchColumn();

    if ($activeCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Academic year saved successfully',
            'academic_year' => $academicYear
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No active section assignments found'
        ]);
    }
} catch (Exception $e) {
    error_log("Error saving academic year: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save academic year'
    ]);
}
?>
