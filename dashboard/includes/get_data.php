<?php
session_start();
include '../../assets/setup/db.inc.php'; // Include your database connection

// Check if the request is an AJAX request
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = [];

    try {
        switch ($action) {
            case 'fetchUsers':
                $response = fetchAllUsers($pdo);
                break;
            case 'fetchThesisTopics':
                $response = fetchAllThesisTopics($pdo);
                break;
            case 'fetchResearchTitles':
                $response = fetchAllResearchTitles($pdo);
                break;
            case 'fetchRubrics':
                $response = fetchAllRubrics($pdo);
                break;
            case 'fetchTeams':
                $response = fetchAllTeams($pdo);
                break;
            case 'fetchRequirements':
                $response = fetchAllRequirements($pdo);
                break;
            case 'fetchEvaluations':
                $response = fetchAllEvaluations($pdo);
                break;
            case 'fetchEnvVariables':
                $response = fetchAllEnvVariables($pdo);
                break;
            default:
                $response = ['error' => 'Invalid action'];
                break;
        }
    } catch (Exception $e) {
        $response = ['error' => 'An error occurred: ' . $e->getMessage()];
    }

    echo json_encode($response);
}
