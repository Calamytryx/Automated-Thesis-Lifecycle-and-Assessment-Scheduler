<?php
require_once 'assets/setup/db.inc.php';

try {
    echo "<h2>Users Table Structure</h2>";
    $cols = $pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($cols as $col) {
        echo str_pad($col['Field'], 20) . " | " . str_pad($col['Type'], 25) . " | Null: " . $col['Null'] . " | Key: " . $col['Key'] . "\n";
    }
    echo "</pre>";
    
    echo "<h2>Check if users table has 'id' column as INT</h2>";
    $id_col = array_filter($cols, fn($c) => $c['Field'] === 'id');
    if ($id_col) {
        $id_col = array_pop($id_col);
        echo "✅ ID column: " . $id_col['Type'] . " (Key: " . $id_col['Key'] . ")\n";
    } else {
        echo "❌ No 'id' column found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
