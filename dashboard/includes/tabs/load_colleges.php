<?php
require_once '../../../assets/setup/db.inc.php';
require_once '../../../assets/includes/auth_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html');

if (!isset($_SESSION['id'], $_SESSION['usertype'])) {
    exit;
}

$userId = (int) $_SESSION['id'];
$userType = (int) $_SESSION['usertype'];
$isSuperAdmin = ($userType === 0 && $userId === 0);
$isProgramChair = ($userType === 0 && $userId !== 0);

try {
    if ($isSuperAdmin) {
        $stmt = $pdo->query("SELECT DISTINCT college FROM programs WHERE college IS NOT NULL AND college != '' ORDER BY college");
    } elseif ($isProgramChair) {
        $userCollege = get_user_college($pdo, $userId);
        if (!$userCollege) {
            exit;
        }

        $stmt = $pdo->prepare("SELECT DISTINCT college FROM programs WHERE college = ? AND college IS NOT NULL AND college != '' ORDER BY college");
        $stmt->execute([$userCollege]);
    } else {
        exit;
    }

    $colleges = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($colleges as $c) {
        echo '<option value="' . htmlspecialchars($c) . '">' . htmlspecialchars($c) . '</option>';
    }

} catch (PDOException $e) {
    echo '<option value="">Error loading colleges</option>';
    error_log($e->getMessage());
}
