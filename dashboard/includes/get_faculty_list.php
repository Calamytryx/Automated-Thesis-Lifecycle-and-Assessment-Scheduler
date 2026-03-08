<?php
/**
 * Returns a JSON list of faculty users for panelist selection.
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../assets/setup/db.inc.php';

try {
    if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], [0, 2])) {
        throw new Exception('Unauthorized');
    }

    $stmt = $pdo->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) AS full_name, program
        FROM users WHERE usertype = 2 OR (usertype = 0 AND id != 0)
        ORDER BY last_name, first_name
    ");
    $stmt->execute();
    $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'faculty' => $faculty]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
