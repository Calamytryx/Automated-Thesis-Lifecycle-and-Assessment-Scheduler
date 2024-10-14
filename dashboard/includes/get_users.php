<?php
require_once '../../assets/setup/db.inc.php';

$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
