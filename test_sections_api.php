<?php
session_start();
require_once 'assets/setup/db.inc.php';

echo "<h2>Testing Section Listing API</h2>";

// Show session info
echo "<h3>Current Session:</h3>";
echo "<pre>";
echo "User ID: " . ($_SESSION['id'] ?? 'NOT SET') . "\n";
echo "Usertype: " . ($_SESSION['usertype'] ?? 'NOT SET') . "\n";
echo "Program: " . ($_SESSION['program'] ?? 'NOT SET') . "\n";
echo "College: " . ($_SESSION['college'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Test getting college
if (isset($_SESSION['id']) && $_SESSION['usertype'] == 0 && $_SESSION['id'] != 0) {
    require_once 'assets/includes/auth_functions.php';
    $userCollege = get_user_college($pdo, $_SESSION['id']);
    echo "<h3>Program Chair College:</h3>";
    echo "<pre>College: " . ($userCollege ?? 'NULL') . "</pre>";
    
    // Test the query
    echo "<h3>Testing Section Query:</h3>";
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.section, COUNT(*) as student_count
        FROM users u
        LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
        WHERE u.section IS NOT NULL 
          AND u.section != ''
          AND p.college = ?
        GROUP BY u.section
        ORDER BY u.section ASC
    ");
    $stmt->execute([$userCollege]);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Section</th><th>Student Count</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['section']}</td><td>{$row['student_count']}</td></tr>";
    }
    echo "</table>";
    
    // Test without join
    echo "<h3>All Sections (No College Filter):</h3>";
    $stmt = $pdo->query("
        SELECT DISTINCT section, COUNT(*) as count
        FROM users
        WHERE section IS NOT NULL AND section != ''
        GROUP BY section
        ORDER BY section
    ");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Section</th><th>Count</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['section']}</td><td>{$row['count']}</td></tr>";
    }
    echo "</table>";
    
    // Test programs table
    echo "<h3>Programs in Your College:</h3>";
    $stmt = $pdo->prepare("SELECT id, name, specialization, college FROM programs WHERE college = ?");
    $stmt->execute([$userCollege]);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Specialization</th><th>College</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['specialization']}</td><td>{$row['college']}</td></tr>";
    }
    echo "</table>";
    
} else {
    echo "<p>You are not logged in as a Program Chair (usertype=0, id!=0)</p>";
    echo "<p>Please login as a Program Chair to test this feature.</p>";
}

// Test direct API call
echo "<h3>Testing Direct API Call:</h3>";
echo "<p><a href='/api/professor_assignments.php?action=list_sections' target='_blank'>Click here to test API directly</a></p>";

echo "<h3>Test Loading Sections via AJAX:</h3>";
?>
<div id="result"></div>
<button onclick="testAPI()">Test API Call</button>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function testAPI() {
    console.log('Testing API call...');
    $.ajax({
        url: '/api/professor_assignments.php',
        type: 'GET',
        data: { action: 'list_sections' },
        dataType: 'json',
        success: function(response) {
            console.log('Success:', response);
            document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            document.getElementById('result').innerHTML = '<pre style="color:red;">ERROR: ' + error + '\n\nResponse:\n' + xhr.responseText + '</pre>';
        }
    });
}
</script>
