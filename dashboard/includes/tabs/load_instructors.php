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

$data = json_decode(file_get_contents("php://input"), true);
$selectedUserId = isset($data['user_id']) ? (int)$data['user_id'] : null;

try {
    if ($isSuperAdmin) {
        $stmt = $pdo->prepare(" 
            SELECT DISTINCT u.id, u.first_name, u.last_name
            FROM users u
            WHERE (
                u.usertype = 2
                OR (u.usertype = 0 AND u.id != 0)
            )
              AND u.first_name IS NOT NULL
              AND u.last_name IS NOT NULL
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute();
    } elseif ($isProgramChair) {
        $userCollege = get_user_college($pdo, $userId);
        if (!$userCollege) {
            exit;
        }

        $stmt = $pdo->prepare(" 
            SELECT DISTINCT u.id, u.first_name, u.last_name
            FROM users u
            LEFT JOIN programs p ON (
                u.program = p.name OR
                u.program = CONCAT(
                    p.name,
                    CASE
                        WHEN p.specialization IS NOT NULL AND p.specialization != ''
                        THEN CONCAT(' - ', p.specialization)
                        ELSE ''
                    END
                )
            )
            WHERE (
                u.usertype = 2
                OR (u.usertype = 0 AND u.id != 0)
            )
              AND u.first_name IS NOT NULL
              AND u.last_name IS NOT NULL
              AND p.college = :college
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute([':college' => $userCollege]);
    } else {
        exit;
    }

    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($instructors as $instructor) {
        $id = htmlspecialchars($instructor['id']);
        $name = htmlspecialchars($instructor['last_name'] . ', ' . $instructor['first_name']);
        $selected = ($selectedUserId === (int)$instructor['id']) ? ' selected' : '';
        echo "<option value=\"$id\"$selected>$name</option>";
    }

} catch (PDOException $e) {
    echo '<option value="">Error loading</option>';
    error_log("Instructor load error: " . $e->getMessage());
}
