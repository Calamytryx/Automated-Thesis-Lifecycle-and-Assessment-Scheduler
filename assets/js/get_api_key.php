<?php
session_start();

if (empty($_SERVER['HTTP_X_REQUESTED_FETCH']) || $_SERVER['HTTP_X_REQUESTED_FETCH'] !== 'true') {
    http_response_code(403);
    exit('Forbidden');
}

// Multiple API keys for load balancing and token distribution
$apiKeys = [
    'AIzaSyBSE1RdMjnZA7w83hBJW9EwF4fpuRdgp_c',
    'AIzaSyBOZITEf87HFxtnCMpmk6Z4msjnCcxBemw',
    'AIzaSyDCGJ6G9f_LzBch31F9HjWC6kc6uq4p38Q',
    'AIzaSyAEp1T7g7_YroE--BXl645aDoceWIJBqNI'
];

// Rotate through API keys (round-robin)
if (!isset($_SESSION['current_api_key_index'])) {
    $_SESSION['current_api_key_index'] = 0;
}

$currentIndex = $_SESSION['current_api_key_index'];
$selectedKey = $apiKeys[$currentIndex];

// Move to next key for next request
$_SESSION['current_api_key_index'] = ($currentIndex + 1) % count($apiKeys);

header('Content-Type: application/json');
echo json_encode([
    'apiKey' => $selectedKey,
    'keyIndex' => $currentIndex,
    'totalKeys' => count($apiKeys)
]);
?>