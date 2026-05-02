<?php
require_once '../../../assets/setup/db.inc.php'; // Adjust path as needed
require_once '../../../assets/includes/auth_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['id'], $_SESSION['usertype'])) {
    echo json_encode([]);
    exit;
}

$userId = (int) $_SESSION['id'];
$userType = (int) $_SESSION['usertype'];
$isSuperAdmin = ($userType === 0 && $userId === 0);
$isProgramChair = ($userType === 0 && $userId !== 0);

$programId = $_GET['program_id'] ?? '';

// Ensure that program_id is a valid number and not empty
if (!preg_match('/^\d+$/', $programId)) {
    echo json_encode([]);
    exit;
}

try {
    // 1. Get program details
    $stmt = $pdo->prepare("SELECT name, specialization, college FROM programs WHERE id = ?");
    $stmt->execute([$programId]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if program exists
    if (!$program) {
        echo json_encode([]);
        exit;
    }

    // 2. Authorization: Program Chair can only access sections for programs in their own college.
    if ($isProgramChair) {
        $userCollege = get_user_college($pdo, $userId);
        if (!$userCollege || strcasecmp((string) $program['college'], $userCollege) !== 0) {
            echo json_encode([]);
            exit;
        }
    } elseif (!$isSuperAdmin) {
        echo json_encode([]);
        exit;
    }

    // 3. Build the full program string (name and specialization)
    $fullProgram = trim($program['name']);
    if (isset($program['specialization']) && strlen(trim($program['specialization'])) > 0) {
        $fullProgram .= ' - ' . trim($program['specialization']);
    }

    // 4. Fetch sections that match the full program
$stmt = $pdo->prepare("
    SELECT DISTINCT section
    FROM users
    WHERE REPLACE(TRIM(program), '  ', ' ') = :program
      AND section IS NOT NULL AND section != ''
    ORDER BY section
");
$stmt->execute(['program' => $fullProgram]);
$sections = $stmt->fetchAll(PDO::FETCH_COLUMN);


    // Return sections as a JSON array
    echo json_encode($sections);

} catch (PDOException $e) {
    // Log the error and return an empty array
    error_log("DB error: " . $e->getMessage());
    echo json_encode([]);
}
