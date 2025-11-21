<?php
// Quick debug helper - check file presence and permissions for a submission PDF
header('Content-Type: application/json');

$fn = $_GET['file'] ?? null;
if (!$fn) {
    echo json_encode(['success' => false, 'error' => 'Missing file parameter. Use ?file=filename.pdf']);
    exit;
}

// Sanitize filename - allow only safe characters
$fn_safe = basename($fn);
$uploadDir = __DIR__ . '/../assets/uploads/submission/';
$path = realpath($uploadDir . $fn_safe);
$allowedBase = realpath($uploadDir);

if (!$path || strpos($path, $allowedBase) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid or unsafe filename', 'file' => $fn_safe]);
    exit;
}

$exists = file_exists($path);
$isReadable = is_readable($path);
$filesize = $exists ? filesize($path) : 0;
$filePerms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : null;

echo json_encode([
    'success' => true,
    'requested_file' => $fn_safe,
    'path' => $path,
    'exists' => $exists,
    'readable' => $isReadable,
    'filesize' => $filesize,
    'perms' => $filePerms,
    'upload_dir' => $uploadDir
]);

?>