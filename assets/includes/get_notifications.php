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

// Get parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$type = isset($_GET['type']) ? $_GET['type'] : 'unread'; // 'unread' or 'recent'

if ($type === 'unread') {
    $notifications = getUnreadNotifications($user_id, $limit);
} else {
    // Get recent notifications (both read and unread)
    $result = getAllNotifications($user_id, 1, $limit);
    $notifications = $result['notifications'];
}

// Format notifications for display
$formatted_notifications = [];
foreach ($notifications as $notification) {
    $formatted_notifications[] = [
        'id' => $notification['id'],
        'reference_type' => $notification['type'], // Map type to reference_type for frontend
        'title' => $notification['title'],
        'message' => $notification['message'],
        'reference_id' => $notification['related_id'], // Map related_id to reference_id for frontend
        'is_read' => isset($notification['is_read']) ? (bool)$notification['is_read'] : false,
        'created_at' => $notification['created_at'],
        'time_ago' => formatNotificationTime($notification['created_at']),
        'icon' => getNotificationIcon($notification['type']), // Use type column
        'color' => getNotificationColor($notification['type']) // Use type column
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'notifications' => $formatted_notifications,
    'count' => count($formatted_notifications)
]);
?>
