<?php
session_start();
// load your DB constants and PDO
require_once '../assets/setup/env.php';
include '../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success'=>false,'files'=>[],'message'=>''];

$level = $_GET['level'] ?? '';
$id    = $_GET['id']    ?? '';

try {
    switch ($level) {
        case 'college':
            $sql = "SELECT filename, filepath FROM uploaded_files 
                    WHERE college_name = :id ORDER BY uploaded_at DESC";
            break;
        case 'program':
            $sql = "SELECT filename, filepath FROM uploaded_files 
                    WHERE program_id = :id ORDER BY uploaded_at DESC";
            break;
        case 'team':
            $sql = "SELECT filename, filepath FROM uploaded_files 
                    WHERE team_id = :id ORDER BY uploaded_at DESC";
            break;
        default:
            throw new Exception("Invalid level");
    }
    $stmt = $pdo->prepare($sql);
    // bind as int for numeric levels
    if ($level === 'college') {
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    } else {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    }
    $stmt->execute();
    $response['files'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
} catch(Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
