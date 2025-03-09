<?php
require_once '../assets/setup/db.inc.php';

// Function to get embedded form for a defense schedule
function getEmbeddedForm($scheduleId) {
    global $pdo;
    
    try {
        // Check if form_assignments table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'form_assignments'");
        if ($tableCheck->rowCount() == 0) {
            return null; // Table doesn't exist
        }
        
        // Query to get the embedded form
        $stmt = $pdo->prepare("
            SELECT embed_link 
            FROM form_assignments 
            WHERE defense_schedule_id = ? AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$scheduleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['embed_link'];
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Error fetching embedded form: " . $e->getMessage());
        return null;
    }
}
?> 