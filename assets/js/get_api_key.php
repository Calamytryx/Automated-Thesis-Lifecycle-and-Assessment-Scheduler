<?php
session_start();

if (empty($_SERVER['HTTP_X_REQUESTED_FETCH']) || $_SERVER['HTTP_X_REQUESTED_FETCH'] !== 'true') {
    http_response_code(403);
    exit('Forbidden');
}


// Only allow authorized requests here!
header('Content-Type: application/json');
echo json_encode(['apiKey' => 'AIzaSyBSE1RdMjnZA7w83hBJW9EwF4fpuRdgp_c']);
?>