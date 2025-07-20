<?php
session_start();
require_once __DIR__ . '/notification_functions.php';
require_once __DIR__ . '/auth_functions.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['id'];

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Try to get from POST data as fallback
    $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 
                      (isset($_POST['id']) ? (int)$_POST['id'] : null);
    $mark_all = isset($_POST['mark_all']) ? (bool)$_POST['mark_all'] : false;
} else {
    $notification_id = isset($input['notification_id']) ? (int)$input['notification_id'] : 
                      (isset($input['id']) ? (int)$input['id'] : null);
    $mark_all = isset($input['mark_all']) ? (bool)$input['mark_all'] : false;
}

$success = false;

if ($mark_all) {
    // Mark all notifications as read
    $success = markAllAsRead($user_id);
} elseif ($notification_id) {
    // Mark specific notification as read
    $success = markAsRead($notification_id, $user_id);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Notification(s) marked as read' : 'Failed to mark notification(s) as read'
]);
?>
