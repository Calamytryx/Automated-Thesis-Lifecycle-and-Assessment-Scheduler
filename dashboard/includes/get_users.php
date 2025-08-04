
<?php
/**
 * Fetches a list of users from the database and returns it as a JSON-encoded array.
 *
 * This script connects to the database using the PDO instance from the included
 * db.inc.php file, prepares a SQL statement to select the id, first_name, and
 * last_name columns from the users table, executes the statement, and fetches
 * all the results as an associative array. The results are then encoded in JSON
 * format and output.
 *
 * @file /c:/xampp/htdocs/dashboard/includes/get_users.php
 *
 * @requires ../../assets/setup/db.inc.php
 *
 * @return void Outputs a JSON-encoded array of users.
 */
require_once '../../assets/setup/db.inc.php';

$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
