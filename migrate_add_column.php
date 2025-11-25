<?php
/**
 * Quick Migration: Add title_proposal column to teams table
 * Access this file from your browser to run the migration
 * URL: http://localhost/dashboard/migrate_add_column.php
 */

require_once __DIR__ . '/assets/setup/db.inc.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - Title Proposal Column</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { color: green; padding: 10px; background: #e8f5e9; border: 1px solid #4caf50; }
        .error { color: red; padding: 10px; background: #ffebee; border: 1px solid #f44336; }
        .info { color: blue; padding: 10px; background: #e3f2fd; border: 1px solid #2196f3; }
        code { background: #f5f5f5; padding: 2px 5px; }
    </style>
</head>
<body>
    <h1>Database Migration - Add title_proposal Column</h1>

    <?php
    try {
        // Get database name
        $stmt = $pdo->query("SELECT DATABASE()");
        $dbName = $stmt->fetchColumn();
        
        echo "<div class='info'><strong>Database:</strong> " . htmlspecialchars($dbName) . "</div>";
        
        // Check if column exists
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'teams' AND COLUMN_NAME = 'title_proposal'
        ");
        $stmt->execute([$dbName]);
        $columnExists = $stmt->fetch();
        
        if ($columnExists) {
            echo "<div class='success'>";
            echo "<strong>✅ SUCCESS:</strong> Column <code>title_proposal</code> already exists in <code>teams</code> table!<br>";
            echo "No migration needed.";
            echo "</div>";
        } else {
            // Add the column
            $pdo->exec("ALTER TABLE teams ADD COLUMN title_proposal TINYINT NOT NULL DEFAULT 0 COMMENT 'Mark team as title proposal - professor role will be automatic'");
            
            // Verify it was added
            $stmt = $pdo->prepare("
                SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'teams' AND COLUMN_NAME = 'title_proposal'
            ");
            $stmt->execute([$dbName]);
            $column = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<div class='success'>";
            echo "<strong>✅ SUCCESS:</strong> Column <code>title_proposal</code> added to <code>teams</code> table!<br><br>";
            echo "<strong>Column Details:</strong><br>";
            echo "• Name: " . htmlspecialchars($column['COLUMN_NAME']) . "<br>";
            echo "• Type: " . htmlspecialchars($column['COLUMN_TYPE']) . "<br>";
            echo "• Nullable: " . ($column['IS_NULLABLE'] === 'YES' ? 'Yes' : 'No') . "<br>";
            echo "• Default: " . htmlspecialchars($column['COLUMN_DEFAULT']) . "<br>";
            echo "</div>";
        }
        
        // Show all teams table structure
        echo "<h2>Teams Table Structure</h2>";
        $stmt = $pdo->query("DESCRIBE teams");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>";
        echo "<strong>❌ ERROR:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<strong>❌ ERROR:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    ?>
</body>
</html>
