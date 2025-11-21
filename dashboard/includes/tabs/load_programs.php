
<?php
require_once '../../../assets/setup/db.inc.php';
header('Content-Type: text/html');
try {
    $stmt = $pdo->query("SELECT id, name, specialization FROM programs ORDER BY name");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programs as $p) {
        $label = $p['name'] . ($p['specialization'] ? ' - ' . $p['specialization'] : '');
        echo '<option value="' . htmlspecialchars($p['id']) . '">' . htmlspecialchars($label) . '</option>';
    }

} catch (PDOException $e) {
    echo '<option value="">Error loading programs</option>';
    error_log($e->getMessage());
}