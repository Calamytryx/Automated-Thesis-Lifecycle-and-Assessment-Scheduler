<?php
// Include database connection
include('../../assets/setup/db.inc.php');

try {
    // Separate queries for teams and users
    $stmt_teams = $pdo->query("SELECT * FROM teams");
    $teams_data = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_users = $pdo->prepare("SELECT * FROM users WHERE usertype = 2");
    $stmt_users->execute();
    $users_data = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
    
    $data = [
        'teams' => $teams_data,
        'users' => $users_data
    ];
    
    if ($data) {
        $response['success'] = true;
        $response['data'] = $data;

        if (true) {
            // Fetch teams
            $stmt = $pdo->query("SELECT id, name FROM teams");
            $response['teams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch all staff members
            $stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE usertype = 2");
            $response['staff'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }
} catch (PDOException $e) {
    // Handle error
    $response = [
        'error' => 'Database error: ' . $e->getMessage()
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>