<?php
require '../../assets/setup/db.inc.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Fetch assigned forms with team names and schedule details
    $query = "
        SELECT f.id, f.defense_schedule_id, f.embed_link, f.is_active, 
               ds.schedule_date, ds.start_time, t.name as team_name
        FROM form_assignments f
        JOIN defense_schedules ds ON f.defense_schedule_id = ds.id
        JOIN teams t ON ds.team_id = t.id
        ORDER BY ds.schedule_date DESC, ds.start_time ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the forms as JSON
    echo json_encode($forms);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 