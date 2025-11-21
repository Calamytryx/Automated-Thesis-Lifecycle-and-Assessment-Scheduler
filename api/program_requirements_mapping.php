<?php
/**
 * Program Requirements Mapping API
 * 
 * Endpoints for managing which requirements apply to which defense types per program
 * 
 * Endpoints:
 * - GET /api/program_requirements_mapping.php?action=get_programs - Get all programs
 * - GET /api/program_requirements_mapping.php?action=get_requirements&program_id=X&defense_type=Y - Get requirements
 * - POST /api/program_requirements_mapping.php?action=add_mapping - Add requirement to defense type
 * - POST /api/program_requirements_mapping.php?action=remove_mapping - Remove requirement from defense type
 * - POST /api/program_requirements_mapping.php?action=update_defense_type_requirements - Bulk update
 */

session_start();
require_once __DIR__ . '/../assets/setup/db.inc.php';
require_once __DIR__ . '/../dashboard/includes/program_requirements_functions.php';
require_once __DIR__ . '/../assets/includes/security_functions.php';
require_once __DIR__ . '/../assets/includes/auth_functions.php';

header('Content-Type: application/json');

// Verify admin access
$userId = $_SESSION['id'] ?? 0;
$userType = $_SESSION['usertype'] ?? -1;

if ($userType != 0) { // Not admin
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Admin access required']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$response = ['success' => false, 'message' => 'Unknown action'];

try {
    switch ($action) {
        case 'get_programs':
            handleGetPrograms();
            break;
        
        case 'get_defense_types':
            handleGetDefenseTypes();
            break;
        
        case 'get_requirements':
            handleGetRequirements();
            break;
        
        case 'add_mapping':
            handleAddMapping();
            break;
        
        case 'remove_mapping':
            handleRemoveMapping();
            break;
        
        case 'update_defense_type_requirements':
            handleUpdateDefenseTypeRequirements();
            break;
        
        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
    error_log("Program Requirements API Error: " . $e->getMessage());
}

echo json_encode($response);

// ============================================================================
// Handler Functions
// ============================================================================

function handleGetPrograms() {
    global $pdo, $response;
    
    $defenseType = filter_input(INPUT_GET, 'defense_type', FILTER_SANITIZE_STRING);
    $programs = getProgramsWithRequirementMappings($pdo, $defenseType ?: null);
    
    $response = [
        'success' => true,
        'programs' => $programs,
        'count' => count($programs)
    ];
}

function handleGetDefenseTypes() {
    global $pdo, $response;
    
    $programId = filter_input(INPUT_GET, 'program_id', FILTER_VALIDATE_INT);
    if (!$programId) {
        $response = ['success' => false, 'error' => 'Invalid program ID'];
        return;
    }
    
    $defenseTypes = getDefenseTypesForProgram($pdo, $programId);
    
    $response = [
        'success' => true,
        'program_id' => $programId,
        'defense_types' => $defenseTypes
    ];
}

function handleGetRequirements() {
    global $pdo, $response;
    
    $programId = filter_input(INPUT_GET, 'program_id', FILTER_VALIDATE_INT);
    $defenseType = filter_input(INPUT_GET, 'defense_type', FILTER_SANITIZE_STRING);
    
    if (!$programId || !$defenseType) {
        $response = ['success' => false, 'error' => 'Missing program_id or defense_type'];
        return;
    }
    
    $validTypes = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
    if (!in_array($defenseType, $validTypes)) {
        $response = ['success' => false, 'error' => 'Invalid defense type'];
        return;
    }
    
    $requirements = getRequirementsForDefenseType($pdo, $programId, $defenseType);
    $unmapped = getUnmappedRequirements($pdo, $programId, $defenseType);
    
    $response = [
        'success' => true,
        'program_id' => $programId,
        'defense_type' => $defenseType,
        'requirements' => $requirements,
        'unmapped_count' => count($unmapped),
        'mapped_count' => count(array_filter($requirements, function($r) { return $r['is_mapped']; }))
    ];
}

function handleAddMapping() {
    global $pdo, $userId, $response;
    
    $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    $defenseType = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);
    $requirementId = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
    $isMandatory = filter_input(INPUT_POST, 'is_mandatory', FILTER_VALIDATE_BOOLEAN);
    
    if (!$programId || !$defenseType || !$requirementId) {
        $response = ['success' => false, 'error' => 'Missing required parameters'];
        return;
    }
    
    $validTypes = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
    if (!in_array($defenseType, $validTypes)) {
        $response = ['success' => false, 'error' => 'Invalid defense type'];
        return;
    }
    
    $result = addRequirementMapping($pdo, $programId, $defenseType, $requirementId, $userId, $isMandatory !== false);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Requirement {$requirementId} mapped to {$defenseType} for program {$programId}"
        ];
        error_log("Admin {$userId} added requirement mapping: Program {$programId}, Type {$defenseType}, Req {$requirementId}");
    } else {
        $response = ['success' => false, 'error' => 'Failed to add mapping'];
    }
}

function handleRemoveMapping() {
    global $pdo, $userId, $response;
    
    $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    $defenseType = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);
    $requirementId = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
    
    if (!$programId || !$defenseType || !$requirementId) {
        $response = ['success' => false, 'error' => 'Missing required parameters'];
        return;
    }
    
    $result = removeRequirementMapping($pdo, $programId, $defenseType, $requirementId);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Requirement {$requirementId} unmapped from {$defenseType} for program {$programId}"
        ];
        error_log("Admin {$userId} removed requirement mapping: Program {$programId}, Type {$defenseType}, Req {$requirementId}");
    } else {
        $response = ['success' => false, 'error' => 'Failed to remove mapping'];
    }
}

function handleUpdateDefenseTypeRequirements() {
    global $pdo, $userId, $response;
    
    $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    $defenseType = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);
    $requirementIds = json_decode($_POST['requirement_ids'] ?? '[]', true);
    
    if (!$programId || !$defenseType) {
        $response = ['success' => false, 'error' => 'Missing program_id or defense_type'];
        return;
    }
    
    if (!is_array($requirementIds)) {
        $response = ['success' => false, 'error' => 'Invalid requirement_ids format'];
        return;
    }
    
    // Validate all requirement IDs are integers
    foreach ($requirementIds as $id) {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            $response = ['success' => false, 'error' => 'Invalid requirement ID in list'];
            return;
        }
    }
    
    $validTypes = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
    if (!in_array($defenseType, $validTypes)) {
        $response = ['success' => false, 'error' => 'Invalid defense type'];
        return;
    }
    
    $result = updateDefenseTypeRequirements($pdo, $programId, $defenseType, $requirementIds, $userId);
    
    if ($result) {
        $response = [
            'success' => true,
            'message' => "Updated {$defenseType} requirements for program {$programId}",
            'mapped_count' => count($requirementIds)
        ];
        error_log("Admin {$userId} updated {$defenseType} requirements for program {$programId}: " . count($requirementIds) . " requirements");
    } else {
        $response = ['success' => false, 'error' => 'Failed to update requirements'];
    }
}

