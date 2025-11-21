<?php
/**
 * Manuscript Requirements API
 * 
 * Manages which manuscript requirements are applicable to specific
 * programs and defense types (e.g., "Final manuscript only for WebDev + Mobile")
 */

require_once __DIR__ . '/../assets/setup/db.inc.php';

header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'error' => 'Unknown error'];

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    // ============================================================
    // Public READ endpoints - no auth required
    // ============================================================
    if ($action === 'get_programs_for_manuscript') {
        // Get all programs and show which have mappings for a manuscript
        $requirement_id = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT);
        if (!$requirement_id) {
            throw new Exception('Missing requirement_id');
        }

        $sql = "SELECT DISTINCT p.id, p.name, p.specialization, p.college, p.department
                FROM programs p
                ORDER BY p.name, p.specialization";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get mappings for this requirement
        $mapping_sql = "SELECT program_id, GROUP_CONCAT(defense_type) as defense_types 
                       FROM program_manuscript_requirements 
                       WHERE requirement_id = ? AND is_required = 1
                       GROUP BY program_id";
        
        $mapping_stmt = $pdo->prepare($mapping_sql);
        $mapping_stmt->execute([$requirement_id]);
        $mappings = $mapping_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Add mapping info and formatted display name to each program
        foreach ($programs as &$program) {
            $program['has_mapping'] = isset($mappings[$program['id']]);
            $program['mapped_defense_types'] = isset($mappings[$program['id']]) 
                ? explode(',', $mappings[$program['id']]) 
                : [];
            
            // Format display name: "Program - Specialization" or just "Program"
            $program['display_name'] = $program['specialization'] 
                ? $program['name'] . ' - ' . $program['specialization']
                : $program['name'];
        }

        $response['success'] = true;
        $response['programs'] = $programs;
        echo json_encode($response);
        exit;
    }

    // ============================================================
    // Authentication Check - Admin only for write operations
    // ============================================================
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit;
    }

    // ============================================================
    // WRITE operations below - require admin auth
    // ============================================================

    // ============================================================
    // 1. Add manuscript requirement for program + defense type
    // ============================================================
    if ($action === 'add_manuscript_requirement') {
        $requirement_id = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
        $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
        $defense_type = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);
        $is_required = filter_input(INPUT_POST, 'is_required', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        $submission_stage = filter_input(INPUT_POST, 'submission_stage', FILTER_SANITIZE_STRING) ?? 'before_defense';
        $can_revise_after = filter_input(INPUT_POST, 'can_revise_after', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $visibility_to_panelist = filter_input(INPUT_POST, 'visibility_to_panelist', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;

        if (!$requirement_id || !$program_id || !$defense_type) {
            throw new Exception('Missing required parameters');
        }

        // Validate defense type
        $valid_types = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
        if (!in_array($defense_type, $valid_types)) {
            throw new Exception('Invalid defense type');
        }

        // Insert or update
        $sql = "INSERT INTO program_manuscript_requirements 
                (requirement_id, program_id, defense_type, is_required, submission_stage, can_revise_after, visibility_to_panelist)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    is_required = VALUES(is_required),
                    submission_stage = VALUES(submission_stage),
                    can_revise_after = VALUES(can_revise_after),
                    visibility_to_panelist = VALUES(visibility_to_panelist),
                    updated_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $requirement_id,
            $program_id,
            $defense_type,
            $is_required ? 1 : 0,
            $submission_stage,
            $can_revise_after ? 1 : 0,
            $visibility_to_panelist ? 1 : 0
        ]);

        $response['success'] = true;
        $response['message'] = 'Manuscript requirement added for ' . $defense_type;
    }

    // ============================================================
    // 2. Remove manuscript requirement
    // ============================================================
    elseif ($action === 'remove_manuscript_requirement') {
        $requirement_id = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
        $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
        $defense_type = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);

        if (!$requirement_id || !$program_id || !$defense_type) {
            throw new Exception('Missing required parameters');
        }

        $sql = "DELETE FROM program_manuscript_requirements 
                WHERE requirement_id = ? AND program_id = ? AND defense_type = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$requirement_id, $program_id, $defense_type]);

        $response['success'] = true;
        $response['message'] = 'Manuscript requirement removed';
    }

    // ============================================================
    // 3. Get manuscript requirements for requirement
    // ============================================================
    elseif ($action === 'get_requirement_manuscripts') {
        $requirement_id = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT);

        if (!$requirement_id) {
            throw new Exception('Invalid requirement ID');
        }

        // Get all programs this manuscript is configured for
        $sql = "SELECT 
                    pmr.id,
                    pmr.program_id,
                    p.name as program_name,
                    pmr.defense_type,
                    pmr.is_required,
                    pmr.submission_stage,
                    pmr.can_revise_after,
                    pmr.visibility_to_panelist
                FROM program_manuscript_requirements pmr
                JOIN programs p ON pmr.program_id = p.id
                WHERE pmr.requirement_id = ?
                ORDER BY p.name, 
                         FIELD(pmr.defense_type, 'title_proposal', 'title_defense', 'final_defense', 're-defense', 'general')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$requirement_id]);
        $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = $mappings;
    }

    // ============================================================
    // 4. Get all manuscripts for program + defense type
    // ============================================================
    elseif ($action === 'get_program_defense_manuscripts') {
        $program_id = filter_input(INPUT_GET, 'program_id', FILTER_VALIDATE_INT);
        $defense_type = filter_input(INPUT_GET, 'defense_type', FILTER_SANITIZE_STRING);

        if (!$program_id || !$defense_type) {
            throw new Exception('Missing program_id or defense_type');
        }

        // Get manuscripts for this combination
        $sql = "SELECT 
                    r.id,
                    r.name,
                    r.description,
                    pmr.is_required,
                    pmr.submission_stage,
                    pmr.can_revise_after,
                    pmr.visibility_to_panelist
                FROM program_manuscript_requirements pmr
                JOIN requirements r ON pmr.requirement_id = r.id
                WHERE pmr.program_id = ? 
                  AND pmr.defense_type = ? 
                  AND pmr.is_required = 1
                ORDER BY r.name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$program_id, $defense_type]);
        $manuscripts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = $manuscripts;
    }

    // ============================================================
    // 5. Get applicable manuscripts for a team
    // ============================================================
    elseif ($action === 'get_team_manuscripts') {
        $team_id = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);

        if (!$team_id) {
            throw new Exception('Invalid team ID');
        }

        // Get team's program
        $teamSql = "SELECT program FROM teams WHERE id = ?";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

        if (!$team) {
            throw new Exception('Team not found');
        }

        // Get program ID from program name
        $progSql = "SELECT id FROM programs WHERE name = ? LIMIT 1";
        $progStmt = $pdo->prepare($progSql);
        $progStmt->execute([$team['program']]);
        $prog = $progStmt->fetch(PDO::FETCH_ASSOC);

        if (!$prog) {
            throw new Exception('Program not found');
        }

        $program_id = $prog['id'];

        // Get team's current defense type
        $defSql = "SELECT defense_type FROM defense_schedules 
                   WHERE team_id = ? AND schedule_date <= NOW()
                   ORDER BY schedule_date DESC LIMIT 1";
        $defStmt = $pdo->prepare($defSql);
        $defStmt->execute([$team_id]);
        $def = $defStmt->fetch(PDO::FETCH_ASSOC);

        $current_defense_type = $def['defense_type'] ?? 'title_proposal';

        // Get manuscripts for this program + defense type
        $manSql = "SELECT 
                        r.id,
                        r.name,
                        r.description,
                        tr.file_name,
                        tr.status,
                        pmr.is_required,
                        pmr.submission_stage,
                        pmr.can_revise_after,
                        pmr.visibility_to_panelist
                    FROM program_manuscript_requirements pmr
                    JOIN requirements r ON pmr.requirement_id = r.id
                    LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
                    WHERE pmr.program_id = ? 
                      AND pmr.defense_type = ? 
                      AND pmr.is_required = 1
                      AND r.is_defense_manuscript = 1
                    ORDER BY r.name";

        $manStmt = $pdo->prepare($manSql);
        $manStmt->execute([$team_id, $program_id, $current_defense_type]);
        $manuscripts = $manStmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'team_id' => $team_id,
            'program_id' => $program_id,
            'program_name' => $team['program'],
            'current_defense_type' => $current_defense_type,
            'manuscripts' => $manuscripts,
            'count' => count($manuscripts)
        ];
    }

    // ============================================================
    // 6. Get programs available for manuscript
    // ============================================================
    elseif ($action === 'get_programs_for_manuscript') {
        $requirement_id = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT);

        if (!$requirement_id) {
            throw new Exception('Invalid requirement ID');
        }

        // Get all programs
        $sql = "SELECT id, name, college FROM programs ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $allPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get programs already mapped to this manuscript
        $mapSql = "SELECT DISTINCT program_id FROM program_manuscript_requirements WHERE requirement_id = ?";
        $mapStmt = $pdo->prepare($mapSql);
        $mapStmt->execute([$requirement_id]);
        $mappedPrograms = $mapStmt->fetchAll(PDO::FETCH_COLUMN);

        // Mark which are mapped
        foreach ($allPrograms as &$prog) {
            $prog['is_mapped'] = in_array($prog['id'], $mappedPrograms);
        }

        $response['success'] = true;
        $response['data'] = [
            'requirement_id' => $requirement_id,
            'programs' => $allPrograms
        ];
    }

    // ============================================================
    // 7. Bulk update manuscript requirements for a requirement
    // ============================================================
    elseif ($action === 'bulk_update_manuscripts') {
        $requirement_id = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
        $program_ids = $_POST['program_ids'] ?? [];
        $defense_type = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);

        if (!$requirement_id || !is_array($program_ids) || empty($program_ids) || !$defense_type) {
            throw new Exception('Missing required parameters');
        }

        // First, remove all mappings for this requirement + defense_type
        $deleteSql = "DELETE FROM program_manuscript_requirements 
                      WHERE requirement_id = ? AND defense_type = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$requirement_id, $defense_type]);

        // Insert new mappings for selected programs
        $insertSql = "INSERT INTO program_manuscript_requirements 
                      (requirement_id, program_id, defense_type, is_required)
                      VALUES (?, ?, ?, 1)";
        $insertStmt = $pdo->prepare($insertSql);

        $count = 0;
        foreach ($program_ids as $program_id) {
            $program_id = filter_var($program_id, FILTER_VALIDATE_INT);
            if ($program_id) {
                $insertStmt->execute([$requirement_id, $program_id, $defense_type]);
                $count++;
            }
        }

        $response['success'] = true;
        $response['message'] = "Updated $count program(s) for $defense_type";
        $response['updated_count'] = $count;
    }

    // ============================================================
    // 8. List all manuscript configurations (admin view)
    // ============================================================
    elseif ($action === 'list_all_manuscripts') {
        $sql = "SELECT 
                    r.id,
                    r.name,
                    COUNT(DISTINCT pmr.program_id) as num_programs,
                    GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as programs,
                    GROUP_CONCAT(DISTINCT pmr.defense_type SEPARATOR ', ') as defense_types
                FROM requirements r
                LEFT JOIN program_manuscript_requirements pmr ON r.id = pmr.requirement_id
                LEFT JOIN programs p ON pmr.program_id = p.id
                WHERE r.is_defense_manuscript = 1
                GROUP BY r.id, r.name
                ORDER BY r.name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $manuscripts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = $manuscripts;
    }

    else {
        throw new Exception('Invalid action: ' . ($action ?: 'not specified'));
    }

} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);
exit;
?>
