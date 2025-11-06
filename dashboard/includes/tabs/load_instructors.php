<?php
require_once '../../../assets/setup/db.inc.php';
header('Content-Type: text/html');

$data = json_decode(file_get_contents("php://input"), true);
$selectedUserId = isset($data['user_id']) ? (int)$data['user_id'] : null;

try {
    $stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE usertype = 2 ORDER BY name");
    $stmt->execute();
    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($instructors as $instructor) {
        $id = htmlspecialchars($instructor['id']);
        $name = htmlspecialchars($instructor['name']);
        $selected = ($selectedUserId === (int)$instructor['id']) ? ' selected' : '';
        echo "<option value=\"$id\"$selected>$name</option>";
    }

} catch (PDOException $e) {
    echo '<option value="">Error loading</option>';
    error_log("Instructor load error: " . $e->getMessage());
}
