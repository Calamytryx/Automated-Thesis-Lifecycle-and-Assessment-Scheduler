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

// Get unread count
$count = getUnreadCount($user_id);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'count' => $count
]);
?>
