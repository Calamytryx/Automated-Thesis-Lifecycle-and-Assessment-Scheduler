<?php
/**
 * Test Script for Professor Assignments System
 * 
 * This script verifies that all components of the professor assignments system are properly deployed.
 * 
 * Usage: 
 *   - Via browser: http://localhost/api/test_professor_assignments.php?test=all
 *   - Via CLI: php /opt/lampp/htdocs/api/test_professor_assignments.php
 */

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Professor Assignments System - Verification</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }";
echo ".test-group { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".test { padding: 10px; margin: 8px 0; border-left: 4px solid #ccc; }";
echo ".pass { border-left-color: #28a745; background: #f1f7f2; }";
echo ".fail { border-left-color: #dc3545; background: #f8d7da; }";
echo ".warning { border-left-color: #ffc107; background: #fff3cd; }";
echo ".icon { margin-right: 8px; font-weight: bold; }";
echo ".pass .icon { color: #28a745; }";
echo ".fail .icon { color: #dc3545; }";
echo ".warning .icon { color: #ffc107; }";
echo "code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New'; }";
echo ".summary { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #007bff; }";
echo "table { width: 100%; border-collapse: collapse; margin-top: 10px; }";
echo "td, th { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #f9f9f9; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Professor Assignments System - Verification Report</h1>";

$tests_passed = 0;
$tests_failed = 0;
$tests_warning = 0;

// ============================================================================
// TEST 1: File Structure
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>📁 Test 1: File Structure</h2>";

$files_to_check = [
    '/opt/lampp/htdocs/api/professor_assignments.php' => 'Backend API',
    '/opt/lampp/htdocs/api/professor_assignments_migration.sql' => 'Database Migration',
    '/opt/lampp/htdocs/admin/professor_assignments.php' => 'Admin Interface',
    '/opt/lampp/htdocs/profile/my_assignments.php' => 'Faculty Dashboard',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_GUIDE.md' => 'Technical Guide',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_QUICKSTART.md' => 'Quick Start',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_SUMMARY.md' => 'System Summary',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_VISUAL.md' => 'Visual Reference',
];

$file_check = [];
foreach ($files_to_check as $filepath => $description) {
    if (file_exists($filepath)) {
        $size = filesize($filepath);
        $size_kb = round($size / 1024, 2);
        echo "<div class='test pass'>";
        echo "<span class='icon'>✓</span>";
        echo "<strong>$description</strong><br>";
        echo "<code>$filepath</code> ({$size_kb} KB)";
        echo "</div>";
        $tests_passed++;
        $file_check[$filepath] = true;
    } else {
        echo "<div class='test fail'>";
        echo "<span class='icon'>✗</span>";
        echo "<strong>$description</strong><br>";
        echo "<code>$filepath</code> - NOT FOUND";
        echo "</div>";
        $tests_failed++;
        $file_check[$filepath] = false;
    }
}

echo "</div>";

// ============================================================================
// TEST 2: PHP File Syntax
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>🔧 Test 2: PHP File Syntax</h2>";

$php_files = [
    '/opt/lampp/htdocs/api/professor_assignments.php',
    '/opt/lampp/htdocs/admin/professor_assignments.php',
    '/opt/lampp/htdocs/profile/my_assignments.php',
];

foreach ($php_files as $filepath) {
    if (file_exists($filepath)) {
        $output = null;
        $return_var = null;
        exec("php -l " . escapeshellarg($filepath) . " 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<div class='test pass'>";
            echo "<span class='icon'>✓</span>";
            echo "<strong>" . basename($filepath) . "</strong> - Valid PHP Syntax";
            echo "</div>";
            $tests_passed++;
        } else {
            echo "<div class='test fail'>";
            echo "<span class='icon'>✗</span>";
            echo "<strong>" . basename($filepath) . "</strong> - Syntax Error<br>";
            echo "<code>" . implode("<br>", $output) . "</code>";
            echo "</div>";
            $tests_failed++;
        }
    }
}

echo "</div>";

// ============================================================================
// TEST 3: API Endpoints Documentation
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>⚙️ Test 3: API Endpoints</h2>";

if (isset($file_check['/opt/lampp/htdocs/api/professor_assignments.php']) && 
    $file_check['/opt/lampp/htdocs/api/professor_assignments.php']) {
    
    $api_content = file_get_contents('/opt/lampp/htdocs/api/professor_assignments.php');
    
    $required_endpoints = [
        'list_pending',
        'list_section_professors',
        'assign_professor_to_section',
        'assign_professor_to_team',
        'accept_assignment',
        'reject_assignment',
        'get_assignment_status',
        'list_team_professors',
    ];
    
    $endpoints_found = 0;
    echo "<table>";
    echo "<tr><th>Endpoint</th><th>Status</th></tr>";
    
    foreach ($required_endpoints as $endpoint) {
        if (strpos($api_content, "'" . $endpoint . "'") !== false || 
            strpos($api_content, '"' . $endpoint . '"') !== false ||
            strpos($api_content, "function " . $endpoint) !== false) {
            echo "<tr>";
            echo "<td><code>$endpoint</code></td>";
            echo "<td style='color: #28a745;'>✓ Found</td>";
            echo "</tr>";
            $endpoints_found++;
        } else {
            echo "<tr>";
            echo "<td><code>$endpoint</code></td>";
            echo "<td style='color: #dc3545;'>✗ Missing</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    if ($endpoints_found === count($required_endpoints)) {
        echo "<div class='test pass' style='margin-top: 10px;'>";
        echo "<span class='icon'>✓</span>";
        echo "<strong>All $endpoints_found required endpoints found</strong>";
        echo "</div>";
        $tests_passed++;
    } else {
        echo "<div class='test warning' style='margin-top: 10px;'>";
        echo "<span class='icon'>⚠</span>";
        echo "<strong>Only $endpoints_found of " . count($required_endpoints) . " endpoints found</strong>";
        echo "</div>";
        $tests_warning++;
    }
}

echo "</div>";

// ============================================================================
// TEST 4: Required Functions
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>🛠️ Test 4: Required Functions</h2>";

if (isset($file_check['/opt/lampp/htdocs/api/professor_assignments.php']) && 
    $file_check['/opt/lampp/htdocs/api/professor_assignments.php']) {
    
    $api_content = file_get_contents('/opt/lampp/htdocs/api/professor_assignments.php');
    
    $required_functions = [
        'session_start' => 'Session management',
        'require_once' => 'Database connection',
        'json_encode' => 'JSON responses',
        'http_response_code' => 'HTTP status codes',
        'prepared statements' => 'SQL security (prepare/execute)',
    ];
    
    foreach ($required_functions as $function => $description) {
        $check_string = $function;
        if ($function === 'prepared statements') {
            $check_string = 'prepare';
        }
        
        if (strpos($api_content, $check_string) !== false) {
            echo "<div class='test pass'>";
            echo "<span class='icon'>✓</span>";
            echo "<strong>$description</strong> - Implemented";
            echo "</div>";
            $tests_passed++;
        } else {
            echo "<div class='test fail'>";
            echo "<span class='icon'>✗</span>";
            echo "<strong>$description</strong> - Not found";
            echo "</div>";
            $tests_failed++;
        }
    }
}

echo "</div>";

// ============================================================================
// TEST 5: Database Schema Readiness
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>💾 Test 5: Database Schema Migration</h2>";

if (isset($file_check['/opt/lampp/htdocs/api/professor_assignments_migration.sql']) && 
    $file_check['/opt/lampp/htdocs/api/professor_assignments_migration.sql']) {
    
    $migration_content = file_get_contents('/opt/lampp/htdocs/api/professor_assignments_migration.sql');
    
    $required_tables = [
        'section_professors',
        'team_professor_assignments',
        'professor_assignment_history',
    ];
    
    echo "<h3>Expected Tables:</h3>";
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Status</th></tr>";
    
    foreach ($required_tables as $table) {
        if (strpos($migration_content, "CREATE TABLE" . (strpos($migration_content, "IF NOT EXISTS") ? " IF NOT EXISTS" : "")) !== false &&
            strpos($migration_content, "`$table`") !== false) {
            echo "<tr>";
            echo "<td><code>$table</code></td>";
            echo "<td style='color: #28a745;'>✓ Defined in migration</td>";
            echo "</tr>";
            $tests_passed++;
        } else {
            echo "<tr>";
            echo "<td><code>$table</code></td>";
            echo "<td style='color: #ffc107;'>⚠ Check migration file</td>";
            echo "</tr>";
            $tests_warning++;
        }
    }
    echo "</table>";
    
    // Check for key constraints
    echo "<h3>Database Features:</h3>";
    $features = [
        'FOREIGN KEY' => 'Foreign key constraints',
        'UNIQUE' => 'Unique constraints',
        'INDEX' => 'Performance indexes',
        'utf8mb4' => 'UTF-8 collation',
    ];
    
    foreach ($features as $feature => $description) {
        if (strpos($migration_content, $feature) !== false) {
            echo "<div class='test pass'>";
            echo "<span class='icon'>✓</span>";
            echo "<strong>$description</strong>";
            echo "</div>";
            $tests_passed++;
        } else {
            echo "<div class='test warning'>";
            echo "<span class='icon'>⚠</span>";
            echo "<strong>$description</strong> - Not found";
            echo "</div>";
            $tests_warning++;
        }
    }
}

echo "</div>";

// ============================================================================
// TEST 6: Documentation Quality
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>📖 Test 6: Documentation</h2>";

$docs = [
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_GUIDE.md' => 'Technical Guide',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_QUICKSTART.md' => 'Quick Start',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_SUMMARY.md' => 'System Summary',
    '/opt/lampp/htdocs/PROFESSOR_ASSIGNMENTS_VISUAL.md' => 'Visual Reference',
];

foreach ($docs as $filepath => $title) {
    if (file_exists($filepath)) {
        $content = file_get_contents($filepath);
        $lines = substr_count($content, "\n");
        $words = str_word_count($content);
        
        echo "<div class='test pass'>";
        echo "<span class='icon'>✓</span>";
        echo "<strong>$title</strong><br>";
        echo "$lines lines, ~$words words";
        echo "</div>";
        $tests_passed++;
    }
}

echo "</div>";

// ============================================================================
// TEST 7: Access Control Implementation
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>🔒 Test 7: Security - Access Control</h2>";

$security_checks = [
    'Permission checks in API' => [
        'file' => '/opt/lampp/htdocs/api/professor_assignments.php',
        'search' => 'usertype'
    ],
    'Admin interface permission' => [
        'file' => '/opt/lampp/htdocs/admin/professor_assignments.php',
        'search' => 'usertype'
    ],
    'Faculty interface permission' => [
        'file' => '/opt/lampp/htdocs/profile/my_assignments.php',
        'search' => 'usertype'
    ],
];

foreach ($security_checks as $description => $check) {
    if (file_exists($check['file'])) {
        $content = file_get_contents($check['file']);
        if (strpos($content, $check['search']) !== false) {
            echo "<div class='test pass'>";
            echo "<span class='icon'>✓</span>";
            echo "<strong>$description</strong>";
            echo "</div>";
            $tests_passed++;
        } else {
            echo "<div class='test warning'>";
            echo "<span class='icon'>⚠</span>";
            echo "<strong>$description</strong> - Not verified";
            echo "</div>";
            $tests_warning++;
        }
    }
}

// Check for prepared statements
$api_file = '/opt/lampp/htdocs/api/professor_assignments.php';
if (file_exists($api_file)) {
    $content = file_get_contents($api_file);
    if (strpos($content, 'prepare') !== false && strpos($content, 'execute') !== false) {
        echo "<div class='test pass'>";
        echo "<span class='icon'>✓</span>";
        echo "<strong>SQL Injection Prevention</strong> - Prepared statements used";
        echo "</div>";
        $tests_passed++;
    } else {
        echo "<div class='test fail'>";
        echo "<span class='icon'>✗</span>";
        echo "<strong>SQL Injection Prevention</strong> - Prepared statements not found";
        echo "</div>";
        $tests_failed++;
    }
}

echo "</div>";

// ============================================================================
// SUMMARY
// ============================================================================
echo "<div class='summary'>";
echo "<h2>📊 Summary</h2>";

$total_tests = $tests_passed + $tests_failed + $tests_warning;

echo "<table>";
echo "<tr>";
echo "<td>✓ Tests Passed:</td>";
echo "<td><strong style='color: #28a745;'>$tests_passed</strong></td>";
echo "</tr>";
echo "<tr>";
echo "<td>✗ Tests Failed:</td>";
echo "<td><strong style='color: #dc3545;'>$tests_failed</strong></td>";
echo "</tr>";
echo "<tr>";
echo "<td>⚠ Warnings:</td>";
echo "<td><strong style='color: #ffc107;'>$tests_warning</strong></td>";
echo "</tr>";
echo "<tr>";
echo "<td><strong>Total Tests:</strong></td>";
echo "<td><strong>$total_tests</strong></td>";
echo "</tr>";
echo "</table>";

$pass_percentage = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100) : 0;

echo "<p>";
if ($tests_failed === 0 && $tests_warning <= 1) {
    echo "✅ <strong>SYSTEM READY FOR DEPLOYMENT</strong><br>";
    echo "All critical components are in place. ";
    echo "Run the database migration to complete setup.";
} elseif ($tests_failed === 0) {
    echo "⚠️ <strong>SYSTEM MOSTLY READY</strong><br>";
    echo "Minor warnings exist but the system should function. Review warnings above.";
} else {
    echo "❌ <strong>SYSTEM HAS ISSUES</strong><br>";
    echo "Please review the failed tests above and correct before deployment.";
}
echo "</p>";

echo "<p style='margin-top: 20px; font-size: 0.9em; color: #666;'>";
echo "Pass Rate: <strong>$pass_percentage%</strong> ($tests_passed/$total_tests tests passed)";
echo "</p>";

echo "</div>";

// ============================================================================
// NEXT STEPS
// ============================================================================
echo "<div class='test-group'>";
echo "<h2>🚀 Next Steps</h2>";

echo "<h3>1. Database Setup</h3>";
echo "<pre>mysql -h sql302.iceiy.com -u icei_38697196 -p'[password]' icei_38697196_coecsathesis &lt; /opt/lampp/htdocs/api/professor_assignments_migration.sql</pre>";

echo "<h3>2. Verify Database Tables</h3>";
echo "<pre>mysql -h sql302.iceiy.com -u icei_38697196 -p'[password]' icei_38697196_coecsathesis -e \"SHOW TABLES LIKE 'professor%';\"</pre>";

echo "<h3>3. Access the Interfaces</h3>";
echo "<ul>";
echo "<li><a href='/admin/professor_assignments.php' target='_blank'>Admin Panel</a> (for admin users)</li>";
echo "<li><a href='/profile/my_assignments.php' target='_blank'>Faculty Dashboard</a> (for faculty users)</li>";
echo "</ul>";

echo "<h3>4. Review Documentation</h3>";
echo "<ul>";
echo "<li><strong><a href='/PROFESSOR_ASSIGNMENTS_QUICKSTART.md' target='_blank'>Quick Start</a></strong> - Start here (5 min read)</li>";
echo "<li><a href='/PROFESSOR_ASSIGNMENTS_GUIDE.md' target='_blank'>Technical Guide</a> - Complete reference</li>";
echo "<li><a href='/PROFESSOR_ASSIGNMENTS_SUMMARY.md' target='_blank'>System Summary</a> - Architecture overview</li>";
echo "<li><a href='/PROFESSOR_ASSIGNMENTS_VISUAL.md' target='_blank'>Visual Reference</a> - Diagrams and flowcharts</li>";
echo "</ul>";

echo "</div>";

echo "</body>";
echo "</html>";
?>
