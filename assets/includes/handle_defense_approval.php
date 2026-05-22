<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'success' => false,
    'message' => 'Panel approval is no longer required. Panelist assignments are auto-accepted.'
]);
