<?php
// Endpoint to link one or more team_requirement_files IDs to a defense schedule
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../assets/setup/db.inc.php';
require_once __DIR__ . '/../dashboard/includes/defense_type_functions.php';

$response = ['success' => false, 'error' => 'Invalid request'];

// Basic permission check - only team leader, adviser, or admin (usertype 2/3) allowed
if (!isset($_SESSION['id'])) {
    $response['error'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['id'];
$usertype = $_SESSION['usertype'] ?? null;
$teamRole = $_SESSION['team_role'] ?? null;

// Only allow if leader or adviser or admin (usertype 2 assumed staff/adviser)
if (!in_array($teamRole, ['leader', 'adviser']) && !in_array($usertype, [2, 3])) {
    $response['error'] = 'Permission denied';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

$schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);
$file_ids_raw = $_POST['file_ids'] ?? null;

if (!$schedule_id || !$file_ids_raw) {
    $response['error'] = 'Missing parameters';
    echo json_encode($response);
    exit;
}

// Normalize file ids to integer array
$fileIds = [];
if (is_array($file_ids_raw)) {
    foreach ($file_ids_raw as $fid) {
        $fid_i = filter_var($fid, FILTER_VALIDATE_INT);
        if ($fid_i) $fileIds[] = $fid_i;
    }
} else {
    $fid = filter_var($file_ids_raw, FILTER_VALIDATE_INT);
    if ($fid) $fileIds[] = $fid;
}

if (empty($fileIds)) {
    $response['error'] = 'No valid file ids provided';
    echo json_encode($response);
    exit;
}

try {
    $ok = linkMultipleFilesToDefense($pdo, $schedule_id, $fileIds);
    if ($ok) {
        $response['success'] = true;
        unset($response['error']);
    } else {
        $response['error'] = 'Failed to link files to schedule';
    }
} catch (Exception $e) {
    $response['error'] = 'Server error: ' . $e->getMessage();
}

echo json_encode($response);
exit;

?>
