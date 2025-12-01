<?php

// --- DATABASE CONFIGURATION ---
$primaryHost   = 'localhost';
$fallbackHost  = 'sql302.iceiy.com';
$dbName        = 'icei_38697196_coecsathesis';
$dbUser        = 'icei_38697196';
$dbPass        = '4rdL34hSdQFcgrL';
$dbCharset     = 'utf8mb4';

$dsnPrimary   = "mysql:host={$primaryHost};dbname={$dbName};charset={$dbCharset}";
$dsnFallback  = "mysql:host={$fallbackHost};dbname={$dbName};charset={$dbCharset}";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- END DATABASE CONFIGURATION ---

// Attempt to connect using primary host, then fallback if needed
try {
    $pdo = new PDO($dsnPrimary, $dbUser, $dbPass, $options);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
    // echo "Connected to primary host ($primaryHost)";
} catch (PDOException $e1) {
    try {
        $pdo = new PDO($dsnFallback, $dbUser, $dbPass, $options);
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
        // echo "Connected to fallback host ($fallbackHost)";
    } catch (PDOException $e2) {
        die("Database connection failed: " . $e2->getMessage());
    }
}

// --- ENV VARIABLE FETCH FUNCTION ---
if (!function_exists('getEnvVariable')) {
    function getEnvVariable($key) {
        global $pdo;
        if (!isset($pdo)) {
            return null;
        }
        $stmt = $pdo->prepare("SELECT value FROM env_variables WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : null;
    }
}

// --- DEFINE CONSTANTS FROM DATABASE ---
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

// --- SPECIAL CASE: ALLOWED_INACTIVITY_TIME ---
if (!defined('ALLOWED_INACTIVITY_TIME')) {
    $inactivity_time = getEnvVariable('ALLOWED_INACTIVITY_TIME');
    define('ALLOWED_INACTIVITY_TIME', time() + (int)($inactivity_time ?? 3600));
}
