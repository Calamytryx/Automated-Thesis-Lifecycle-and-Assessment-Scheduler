<?php
/**
 * This script fetches requirements along with the current user's status, submitted_at, and feedback.
 * 
 * JSON Response:
 * - On success: { "success": true, "requirements": [ 
 *     { 
 *         "id": int, 
 *         "name": string, 
 *         "description": string, 
 *         "due_date": string,
 *         "status": string,
 *         "submitted_at": string,
 *         "feedback": string 
 *     }, 
 *     ... 
 * ] }
 * - On failure: { "success": false, "error": string }
 */
header('Content-Type: application/json');
session_start();

// Include database connection
require '../../assets/setup/db.inc.php';

// Check if user is authenticated
if (!isset($_SESSION['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['id'];

try {
    // Fetch all requirements
    $stmt = $pdo->prepare("SELECT id, name, description, due_date FROM requirements ORDER BY due_date ASC, name ASC");
    $stmt->execute();
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch user-specific requirement statuses
    $userReqStmt = $pdo->prepare("SELECT requirement_id, status, submitted_at, feedback FROM user_requirements WHERE user_id = ?");
    $userReqStmt->execute([$user_id]);
    $userRequirements = $userReqStmt->fetchAll(PDO::FETCH_ASSOC);

    // Map user requirements for easy access
    $userReqMap = [];
    foreach ($userRequirements as $userReq) {
        $userReqMap[$userReq['requirement_id']] = [
            'status' => $userReq['status'],
            'submitted_at' => $userReq['submitted_at'],
            'feedback' => $userReq['feedback']
        ];
    }

    // Combine requirements with user-specific data
    foreach ($requirements as &$req) {
        if (isset($userReqMap[$req['id']])) {
            $req['status'] = $userReqMap[$req['id']]['status'];
            $req['submitted_at'] = $userReqMap[$req['id']]['submitted_at'];
            $req['feedback'] = $userReqMap[$req['id']]['feedback'];
        } else {
            $req['status'] = 'pending';
            $req['submitted_at'] = null;
            $req['feedback'] = '';
        }
    }

    echo json_encode(['success' => true, 'requirements' => $requirements]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>