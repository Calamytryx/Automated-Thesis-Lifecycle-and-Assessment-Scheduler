<?php
require_once '../../../assets/setup/db.inc.php';
header('Content-Type: text/html');
try {
    $stmt = $pdo->query("SELECT DISTINCT college FROM programs WHERE college IS NOT NULL AND college != '' ORDER BY college");
    $colleges = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($colleges as $c) {
        echo '<option value="' . htmlspecialchars($c) . '">' . htmlspecialchars($c) . '</option>';
    }

} catch (PDOException $e) {
    echo '<option value="">Error loading colleges</option>';
    error_log($e->getMessage());
}
