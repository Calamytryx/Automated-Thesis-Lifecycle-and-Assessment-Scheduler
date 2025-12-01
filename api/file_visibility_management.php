<?php
/**
 * File Visibility Management API
 * 
 * Handles:
 * - Marking requirements as defense manuscripts
 * - Configuring file visibility per defense type
 * - Querying visible files for teams based on their defense type
 * - Access control and logging
 */

require_once __DIR__ . '/../assets/setup/db.inc.php';
require_once __DIR__ . '/../dashboard/includes/defense_type_functions.php';

header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'error' => 'Unknown error'];

// ============================================================
// Authentication Check
// ============================================================
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit;
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    // ============================================================
    // 1. Mark requirement as defense manuscript
    // ============================================================
    if ($action === 'mark_defense_manuscript') {
        $requirement_id = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
        $is_manuscript = filter_input(INPUT_POST, 'is_manuscript', FILTER_VALIDATE_BOOLEAN);

        if (!$requirement_id) {
            throw new Exception('Invalid requirement ID');
        }

        $sql = "UPDATE requirements SET is_defense_manuscript = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$is_manuscript ? 1 : 0, $requirement_id]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Requirement marked as ' . ($is_manuscript ? 'defense manuscript' : 'regular requirement');
        } else {
            throw new Exception('Failed to update requirement');
        }
    }

    // ============================================================
    // 2. Set visibility scope for requirement
    // ============================================================
    elseif ($action === 'set_visibility_scope') {
        $requirement_id = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
        $visibility_scope = filter_input(INPUT_POST, 'visibility_scope', FILTER_SANITIZE_STRING);

        if (!$requirement_id) {
            throw new Exception('Invalid requirement ID');
        }

        $valid_scopes = ['all_stages', 'specific_stages', 'current_stage_only', 'hidden'];
        if (!in_array($visibility_scope, $valid_scopes)) {
            throw new Exception('Invalid visibility scope');
        }

        $sql = "UPDATE requirements SET visibility_scope = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$visibility_scope, $requirement_id]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Visibility scope updated to: ' . $visibility_scope;
            $response['visibility_scope'] = $visibility_scope;
        } else {
            throw new Exception('Failed to update visibility scope');
        }
    }

    // ============================================================
    // 3. Set defense type visibility rules
    // ============================================================
    elseif ($action === 'set_defense_type_visibility') {
        $requirement_id = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
        $defense_type = filter_input(INPUT_POST, 'defense_type', FILTER_SANITIZE_STRING);
        $can_view = filter_input(INPUT_POST, 'can_view', FILTER_VALIDATE_BOOLEAN);
        $can_download = filter_input(INPUT_POST, 'can_download', FILTER_VALIDATE_BOOLEAN);
        $visibility_label = filter_input(INPUT_POST, 'visibility_label', FILTER_SANITIZE_STRING);

        if (!$requirement_id || !$defense_type) {
            throw new Exception('Invalid requirement ID or defense type');
        }

        $valid_types = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
        if (!in_array($defense_type, $valid_types)) {
            throw new Exception('Invalid defense type');
        }

        // Use INSERT ... ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO file_visibility_rules (requirement_id, defense_type, can_view, can_download, visibility_label)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    can_view = VALUES(can_view),
                    can_download = VALUES(can_download),
                    visibility_label = VALUES(visibility_label),
                    updated_at = NOW()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $requirement_id,
            $defense_type,
            $can_view ? 1 : 0,
            $can_download ? 1 : 0,
            $visibility_label
        ]);

        $response['success'] = true;
        $response['message'] = 'Visibility rule set for ' . $defense_type;
    }

    // ============================================================
    // 4. Get visibility rules for a requirement
    // ============================================================
    elseif ($action === 'get_visibility_rules') {
        $requirement_id = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT);

        if (!$requirement_id) {
            throw new Exception('Invalid requirement ID');
        }

        // Get requirement details
        $reqSql = "SELECT id, name, is_defense_manuscript, visibility_scope FROM requirements WHERE id = ?";
        $reqStmt = $pdo->prepare($reqSql);
        $reqStmt->execute([$requirement_id]);
        $requirement = $reqStmt->fetch(PDO::FETCH_ASSOC);

        if (!$requirement) {
            throw new Exception('Requirement not found');
        }

        // Get visibility rules
        $rulesSql = "SELECT defense_type, can_view, can_download, visibility_label 
                     FROM file_visibility_rules 
                     WHERE requirement_id = ? 
                     ORDER BY FIELD(defense_type, 'title_proposal', 'title_defense', 'final_defense', 're-defense', 'general')";
        $rulesStmt = $pdo->prepare($rulesSql);
        $rulesStmt->execute([$requirement_id]);
        $rules = $rulesStmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'requirement' => $requirement,
            'rules' => $rules
        ];
    }

    // ============================================================
    // 5. Get visible files for team (check file access)
    // ============================================================
    elseif ($action === 'get_visible_files') {
        $team_id = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);

        if (!$team_id) {
            throw new Exception('Invalid team ID');
        }

        // Get team's current defense type
        $teamSql = "SELECT t.id, t.name, t.program, ds.defense_type
                    FROM teams t
                    LEFT JOIN defense_schedules ds ON t.id = ds.team_id 
                        AND ds.defense_type = (
                            SELECT defense_type FROM defense_schedules 
                            WHERE team_id = t.id 
                            AND schedule_date <= NOW()
                            ORDER BY schedule_date DESC 
                            LIMIT 1
                        )
                    WHERE t.id = ?
                    LIMIT 1";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

        if (!$team) {
            throw new Exception('Team not found');
        }

        $currentDefenseType = $team['defense_type'] ?? 'title_proposal'; // Default if no schedule

        // Get visible files based on current defense type
        $filesSql = "SELECT 
                        r.id,
                        r.name,
                        r.is_defense_manuscript,
                        r.visibility_scope,
                        tr.file_name,
                        tr.status,
                        tr.submitted_at,
                        fvr.can_view,
                        fvr.can_download,
                        fvr.visibility_label
                    FROM requirements r
                    LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
                    LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id AND fvr.defense_type = ?
                    WHERE 
                        -- Visibility conditions
                        (r.visibility_scope = 'all_stages' 
                         OR (r.visibility_scope = 'specific_stages' AND fvr.can_view = 1)
                         OR (r.visibility_scope = 'current_stage_only' AND fvr.defense_type = ?)
                        )
                        AND r.visibility_scope != 'hidden'
                        AND tr.file_name IS NOT NULL
                        AND tr.status IN ('submitted', 'approved')
                    ORDER BY r.is_defense_manuscript DESC, r.name ASC";
        
        $filesStmt = $pdo->prepare($filesSql);
        $filesStmt->execute([$team_id, $currentDefenseType, $currentDefenseType]);
        $files = $filesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Log access
        foreach ($files as $file) {
            $logSql = "INSERT INTO defense_file_access_log (team_id, requirement_id, file_name, action, user_id) 
                       VALUES (?, ?, ?, 'view', ?)";
            $logStmt = $pdo->prepare($logSql);
            $logStmt->execute([$team_id, $file['id'], $file['file_name'], $_SESSION['id']]);
        }

        $response['success'] = true;
        $response['data'] = [
            'team' => $team,
            'current_defense_type' => $currentDefenseType,
            'files' => $files,
            'file_count' => count($files),
            'manuscript_count' => count(array_filter($files, fn($f) => $f['is_defense_manuscript']))
        ];
    }

    // ============================================================
    // 6. Check if team can access specific file
    // ============================================================
    elseif ($action === 'can_access_file') {
        $team_id = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);
        $requirement_id = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT);

        if (!$team_id || !$requirement_id) {
            throw new Exception('Invalid team ID or requirement ID');
        }

        // Get current defense type
        $teamSql = "SELECT defense_type FROM defense_schedules 
                    WHERE team_id = ? 
                    AND schedule_date <= NOW()
                    ORDER BY schedule_date DESC 
                    LIMIT 1";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $result = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        $currentDefenseType = $result['defense_type'] ?? 'title_proposal';

        // Check visibility
        $visSql = "SELECT r.visibility_scope, fvr.can_view, fvr.can_download
                   FROM requirements r
                   LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id AND fvr.defense_type = ?
                   WHERE r.id = ?";
        $visStmt = $pdo->prepare($visSql);
        $visStmt->execute([$currentDefenseType, $requirement_id]);
        $visibility = $visStmt->fetch(PDO::FETCH_ASSOC);

        if (!$visibility) {
            throw new Exception('Requirement not found');
        }

        $can_access = false;
        $reason = '';

        if ($visibility['visibility_scope'] === 'hidden') {
            $reason = 'File is hidden from all teams';
        } elseif ($visibility['visibility_scope'] === 'all_stages') {
            $can_access = true;
            $reason = 'Visible to all defense stages';
        } elseif ($visibility['visibility_scope'] === 'specific_stages' && $visibility['can_view']) {
            $can_access = true;
            $reason = 'Visible to ' . $currentDefenseType . ' stage';
        } elseif ($visibility['visibility_scope'] === 'current_stage_only' && $visibility['defense_type'] === $currentDefenseType) {
            $can_access = true;
            $reason = 'Visible only during ' . $currentDefenseType . ' stage';
        }

        if ($can_access) {
            $logSql = "INSERT INTO defense_file_access_log (team_id, requirement_id, file_name, action, user_id) 
                       VALUES (?, ?, ?, 'download', ?)";
            $logStmt = $pdo->prepare($logSql);
            $logStmt->execute([$team_id, $requirement_id, '', $_SESSION['id']]);
        }

        $response['success'] = true;
        $response['data'] = [
            'can_access' => $can_access,
            'reason' => $reason,
            'current_defense_type' => $currentDefenseType,
            'visibility_scope' => $visibility['visibility_scope'],
            'can_download' => $can_access && $visibility['can_download']
        ];
    }

    // ============================================================
    // 7. Get defense manuscripts only
    // ============================================================
    elseif ($action === 'get_defense_manuscripts') {
        $team_id = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);

        if (!$team_id) {
            throw new Exception('Invalid team ID');
        }

        // Get team's current defense type
        $teamSql = "SELECT t.id, ds.defense_type
                    FROM teams t
                    LEFT JOIN defense_schedules ds ON t.id = ds.team_id 
                        AND ds.defense_type = (
                            SELECT defense_type FROM defense_schedules 
                            WHERE team_id = t.id 
                            AND schedule_date <= NOW()
                            ORDER BY schedule_date DESC 
                            LIMIT 1
                        )
                    WHERE t.id = ?";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

        $currentDefenseType = $team['defense_type'] ?? 'title_proposal';

        // Get defense manuscripts visible to this team
        $manSql = "SELECT 
                        r.id,
                        r.name,
                        r.visibility_scope,
                        tr.file_name,
                        tr.status,
                        tr.submitted_at,
                        fvr.visibility_label,
                        fvr.can_view,
                        fvr.can_download
                    FROM requirements r
                    LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
                    LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id AND fvr.defense_type = ?
                    WHERE 
                        r.is_defense_manuscript = 1
                        AND r.visibility_scope != 'hidden'
                        AND (r.visibility_scope = 'all_stages' 
                             OR (r.visibility_scope = 'specific_stages' AND fvr.can_view = 1)
                             OR (r.visibility_scope = 'current_stage_only' AND fvr.defense_type = ?)
                        )
                        AND tr.file_name IS NOT NULL
                        AND tr.status IN ('submitted', 'approved')
                    ORDER BY tr.submitted_at DESC";
        
        $manStmt = $pdo->prepare($manSql);
        $manStmt->execute([$team_id, $currentDefenseType, $currentDefenseType]);
        $manuscripts = $manStmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'team_id' => $team_id,
            'current_defense_type' => $currentDefenseType,
            'manuscripts' => $manuscripts,
            'count' => count($manuscripts)
        ];
    }

    // ============================================================
    // 8. Get all defense manuscripts (admin view)
    // ============================================================
    elseif ($action === 'list_defense_manuscripts') {
        $sql = "SELECT 
                    r.id,
                    r.name,
                    r.description,
                    r.is_defense_manuscript,
                    r.visibility_scope,
                    COUNT(DISTINCT fvr.defense_type) as num_visible_types,
                    GROUP_CONCAT(DISTINCT fvr.defense_type SEPARATOR ', ') as visible_types
                FROM requirements r
                LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id AND fvr.can_view = 1
                WHERE r.is_defense_manuscript = 1
                GROUP BY r.id, r.name, r.description, r.is_defense_manuscript, r.visibility_scope
                ORDER BY r.name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $manuscripts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'manuscripts' => $manuscripts,
            'total' => count($manuscripts)
        ];
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
