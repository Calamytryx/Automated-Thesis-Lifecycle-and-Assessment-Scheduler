<?php
/**
 * Initialize section_professors table
 * Run this once to set up the database table
 */

require_once 'assets/setup/db.inc.php';

try {
    // Check if table exists
    $stmt = $pdo->prepare("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'section_professors'
    ");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        // Check what schema it has
        $cols = $pdo->query("DESCRIBE section_professors")->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ Table exists with columns: ";
        foreach ($cols as $col) {
            echo $col['Field'] . ", ";
        }
        exit;
    }

    // Create the table with new schema (string section instead of section_id)
    echo "Creating section_professors table...\n";
    
    // First, disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `section_professors` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `section` varchar(255) NOT NULL,
      `professor_id` int(11) NOT NULL,
      `status` varchar(50) DEFAULT 'active',
      `assigned_by` int(11) DEFAULT NULL,
      `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_section_professor` (`section`, `professor_id`),
      INDEX `idx_professor_id` (`professor_id`),
      INDEX `idx_section` (`section`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "✅ Table created successfully!\n";
    echo "\nTable structure:\n";
    $cols = $pdo->query("DESCRIBE section_professors")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
