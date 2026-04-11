
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
$requestedCollege = isset($_GET['college']) ? trim($_GET['college']) : '';

try {
    if ($isSuperAdmin) {
        if ($requestedCollege !== '') {
            $stmt = $pdo->prepare("SELECT id, name, specialization FROM programs WHERE college = ? ORDER BY name");
            $stmt->execute([$requestedCollege]);
        } else {
            $stmt = $pdo->query("SELECT id, name, specialization FROM programs ORDER BY name");
        }
    } elseif ($isProgramChair) {
        $userCollege = get_user_college($pdo, $userId);
        if (!$userCollege) {
            exit;
        }

        if ($requestedCollege !== '' && strcasecmp($requestedCollege, $userCollege) !== 0) {
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, name, specialization FROM programs WHERE college = ? ORDER BY name");
        $stmt->execute([$userCollege]);
    } else {
        exit;
    }

    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programs as $p) {
        $label = $p['name'] . ($p['specialization'] ? ' - ' . $p['specialization'] : '');
        echo '<option value="' . htmlspecialchars($p['id']) . '">' . htmlspecialchars($label) . '</option>';
    }

} catch (PDOException $e) {
    echo '<option value="">Error loading programs</option>';
    error_log($e->getMessage());
}