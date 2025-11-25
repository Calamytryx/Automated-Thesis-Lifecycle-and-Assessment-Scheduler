<?php
/**
 * Test Schedule Generation
 * Run this to test the scheduler with minimal data
 */

require_once 'assets/setup/db.inc.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Scheduler</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .log { background: #f5f5f5; padding: 10px; border-radius: 5px; margin: 10px 0; font-size: 12px; font-family: monospace; max-height: 400px; overflow-y: auto; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Defense Schedule Generator - Diagnostic Test</h1>
    
    <h2>Step 1: Check Database Connection</h2>
    <div class="log">
        <?php
        try {
            echo '<span class="success">✓ Database connected</span><br>';
            
            // Check if teams exist
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM teams");
            $row = $stmt->fetch();
            echo "Teams in database: " . $row['cnt'] . "<br>";
            
            // Check if users exist (for panelists)
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE usertype = 2");
            $row = $stmt->fetch();
            echo "Faculty (panelists) in database: " . $row['cnt'] . "<br>";
            
            if ($row['cnt'] == 0) {
                echo '<span class="error">⚠ WARNING: No faculty found! You need at least 3 faculty users with usertype=2</span><br>';
            }
            
        } catch (Exception $e) {
            echo '<span class="error">✗ Error: ' . $e->getMessage() . '</span>';
        }
        ?>
    </div>
    
    <h2>Step 2: Check PHP Error Log</h2>
    <div class="log">
        <?php
        $logFile = '/opt/lampp/htdocs/dashboard/includes/php_errors.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            // Show last 30 lines
            $lastLines = array_slice($lines, -30);
            foreach ($lastLines as $line) {
                if (strpos($line, 'SCHEDULER') !== false || strpos($line, 'ERROR') !== false) {
                    echo '<span class="error">' . htmlspecialchars($line) . '</span><br>';
                } else {
                    echo htmlspecialchars($line) . '<br>';
                }
            }
        } else {
            echo '<span class="info">No error log yet</span>';
        }
        ?>
    </div>
    
    <h2>Step 3: Test Minimal Schedule Generation</h2>
    <button onclick="testScheduler()">Generate Test Schedule</button>
    <div id="result" class="log" style="display:none;"></div>
    
    <script>
        function testScheduler() {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<span class="info">Sending request...</span>';
            
            const formData = new FormData();
            formData.append('rooms', ['Room 101', 'Room 102']);
            formData.append('timeDuration', 1);  // 1 hour per slot
            formData.append('timeSlots', ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00']);
            formData.append('days', '12-15-2025');  // One day
            formData.append('program', '');  // All programs
            
            fetch('dashboard/includes/run_scheduler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.innerHTML = '<span class="success">Response received:</span><br>' + 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                resultDiv.innerHTML = '<span class="error">Error: ' + error.message + '</span>';
            });
        }
        
        // Auto-refresh error log every 2 seconds while running
        setInterval(() => {
            location.reload();
        }, 2000);
    </script>
</body>
</html>
