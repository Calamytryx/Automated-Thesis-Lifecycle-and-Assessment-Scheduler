<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/auth_functions.php';
require_once __DIR__ . '/section_access.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'], $_SESSION['usertype'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = [];
}

$viewType = trim((string)($data['view_type'] ?? ''));
$college = trim((string)($data['college'] ?? ''));
$program = trim((string)($data['program'] ?? ''));
$section = trim((string)($data['section'] ?? ''));
$instructor = trim((string)($data['instructor'] ?? ''));
$exportType = trim((string)($data['export_type'] ?? 'table'));

if (!in_array($viewType, ['program', 'instructor'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid report view.']);
    exit;
}

if ($college === '') {
    echo json_encode(['success' => false, 'message' => 'College is required.']);
    exit;
}

if ($viewType === 'program' && ($program === '' || $section === '')) {
    echo json_encode(['success' => false, 'message' => 'Program and section are required for this report.']);
    exit;
}

if ($viewType === 'instructor' && $instructor === '') {
    echo json_encode(['success' => false, 'message' => 'Instructor is required for this report.']);
    exit;
}

$userId = (int) $_SESSION['id'];
$userType = (int) $_SESSION['usertype'];
$isSuperAdmin = ($userType === 0 && $userId === 0);
$isProgramChair = ($userType === 0 && $userId !== 0);

error_log(sprintf(
    'get_schedules_report.php start: user_id=%d user_type=%d view_type=%s export_type=%s college=%s',
    $userId,
    $userType,
    $viewType,
    $exportType,
    $college
));

try {
    $sql = "
        SELECT
            us.id,
            us.user_id,
            us.day_of_week,
            us.start_time,
            us.end_time,
            us.class_name,
            us.room,
            us.section,
            us.program,
            u.first_name,
            u.last_name,
            p.name AS program_name,
            p.specialization,
            p.college
        FROM user_schedules us
        LEFT JOIN users u ON us.user_id = u.id
        LEFT JOIN programs p ON us.program = p.id
        WHERE p.college = :college
    ";

    $params = [':college' => $college];

    if ($viewType === 'program') {
        $sql .= " AND us.program = :program AND us.section = :section ";
        $params[':program'] = $program;
        $params[':section'] = $section;
    } else {
        $sql .= " AND us.user_id = :instructor ";
        $params[':instructor'] = $instructor;
    }

    if ($isProgramChair) {
        $allowedCollege = get_user_college($pdo, $userId);
        if ($allowedCollege && strcasecmp($allowedCollege, $college) !== 0) {
            echo json_encode(['success' => false, 'message' => 'You do not have access to this college.']);
            exit;
        }
    }

    $sql .= " ORDER BY CASE LOWER(us.day_of_week) WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3 WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6 ELSE 99 END, us.start_time ASC, us.class_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log(sprintf(
        'get_schedules_report.php finish: export_type=%s rows=%d',
        $exportType,
        count($rows)
    ));

    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
} catch (Exception $e) {
    error_log('get_schedules_report.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error while fetching schedule report.']);
    exit;
}