<?php

// --- DATABASE CONFIGURATION ---
// Change these variables to update your database connection.
$dbHost    = 'localhost';
$dbName    = 'icei_38697196_coecsathesis';
$dbUser    = 'icei_38697196';
$dbPass    = '4rdL34hSdQFcgrL';
$dbCharset = 'utf8mb4';

// Build the DSN string dynamically.
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

// --- END DATABASE CONFIGURATION ---

// Function to get environment variables from database
if (!function_exists('getEnvVariable')) {
    function getEnvVariable($key) {
        global $pdo;
        if (!isset($pdo)) {
            return null; // Return null if database connection is not established
        }
        $stmt = $pdo->prepare("SELECT value FROM env_variables WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : null;
    }
}

// Establish database connection
try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Define constants using database values
$constants = [
    'APP_NAME', 'APP_ORGANIZATION', 'APP_OWNER', 'APP_DESCRIPTION',
    'DB_DATABASE', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PORT',
    'MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_PORT'
];

foreach ($constants as $constant) {
    if (!defined($constant)) {
        define($constant, getEnvVariable($constant));
    }
}

// Special case for ALLOWED_INACTIVITY_TIME
if (!defined('ALLOWED_INACTIVITY_TIME')) {
    $inactivity_time = getEnvVariable('ALLOWED_INACTIVITY_TIME');
    define('ALLOWED_INACTIVITY_TIME', time() + (int)($inactivity_time ?? 3600));
}
