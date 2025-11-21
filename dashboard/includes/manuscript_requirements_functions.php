<?php
/**
 * Manuscript Requirements Helper Functions
 * 
 * Check which manuscripts are applicable to teams based on program + defense type
 */

/**
 * Get all manuscripts applicable to a team
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @return array ['team_id', 'program_id', 'program_name', 'current_defense_type', 'manuscripts' => [], 'count' => int]
 */
function getTeamApplicableManuscripts($pdo, $team_id) {
    try {
        // Get team's program
        $teamSql = "SELECT program FROM teams WHERE id = ?";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

        if (!$team) {
            throw new Exception('Team not found');
        }

        error_log("getTeamApplicableManuscripts DEBUG: Team program = '{$team['program']}'");

        // Try exact match first
        $progSql = "SELECT id FROM programs WHERE name = ? LIMIT 1";
        $progStmt = $pdo->prepare($progSql);
        $progStmt->execute([$team['program']]);
        $prog = $progStmt->fetch(PDO::FETCH_ASSOC);

        // If no exact match, try partial match (team program contains program name)
        if (!$prog) {
            error_log("getTeamApplicableManuscripts DEBUG: Exact match failed, trying partial match");
            $progSql = "SELECT id, name FROM programs WHERE ? LIKE CONCAT('%', name, '%') LIMIT 1";
            $progStmt = $pdo->prepare($progSql);
            $progStmt->execute([$team['program']]);
            $prog = $progStmt->fetch(PDO::FETCH_ASSOC);
        }

        // If still no match, try using team.program as program_id directly (if it's numeric)
        if (!$prog && is_numeric($team['program'])) {
            error_log("getTeamApplicableManuscripts DEBUG: Program field is numeric, using as ID");
            $progSql = "SELECT id, name FROM programs WHERE id = ? LIMIT 1";
            $progStmt = $pdo->prepare($progSql);
            $progStmt->execute([(int)$team['program']]);
            $prog = $progStmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$prog) {
            // Log all available programs for debugging
            $allProgsSql = "SELECT id, name FROM programs LIMIT 20";
            $allProgsStmt = $pdo->query($allProgsSql);
            $allProgs = $allProgsStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("getTeamApplicableManuscripts DEBUG: Available programs: " . json_encode($allProgs));
            throw new Exception("Program not found: '{$team['program']}'");
        }

        $program_id = $prog['id'];
        $program_name = $prog['name'] ?? $team['program'];
        error_log("getTeamApplicableManuscripts DEBUG: Found program ID = {$program_id}, Name = '{$program_name}'");

        // Get team's current defense type
        // First try to get the most recent defense schedule (regardless of date)
        // This allows future defenses to work too
        $defSql = "SELECT defense_type FROM defense_schedules 
                   WHERE team_id = ?
                   ORDER BY schedule_date DESC LIMIT 1";
        $defStmt = $pdo->prepare($defSql);
        $defStmt->execute([$team_id]);
        $def = $defStmt->fetch(PDO::FETCH_ASSOC);

        $current_defense_type = $def['defense_type'] ?? 'title_proposal';
        error_log("getTeamApplicableManuscripts DEBUG: Current defense type = '{$current_defense_type}'");

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

        error_log("getTeamApplicableManuscripts DEBUG: Found " . count($manuscripts) . " manuscripts for program_id={$program_id}, defense_type='{$current_defense_type}'");

        return [
            'success' => true,
            'team_id' => $team_id,
            'program_id' => $program_id,
            'program_name' => $program_name,
            'current_defense_type' => $current_defense_type,
            'manuscripts' => $manuscripts,
            'count' => count($manuscripts)
        ];

    } catch (Exception $e) {
        error_log('Error in getTeamApplicableManuscripts: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'manuscripts' => []
        ];
    }
}

/**
 * Check if specific manuscript is required for team
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @param int $requirement_id Requirement (manuscript) ID
 * @return array ['is_applicable' => bool, 'is_required' => bool, 'submission_stage' => string, ...]
 */
function isManuscriptApplicableToTeam($pdo, $team_id, $requirement_id) {
    try {
        // Get team's program
        $teamSql = "SELECT program FROM teams WHERE id = ?";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

        if (!$team) {
            return ['is_applicable' => false, 'reason' => 'Team not found'];
        }

        // Get program ID
        $progSql = "SELECT id FROM programs WHERE name = ? LIMIT 1";
        $progStmt = $pdo->prepare($progSql);
        $progStmt->execute([$team['program']]);
        $prog = $progStmt->fetch(PDO::FETCH_ASSOC);

        if (!$prog) {
            return ['is_applicable' => false, 'reason' => 'Program not found'];
        }

        // Get current defense type
        // First try to get the most recent defense schedule (regardless of date)
        // This allows future defenses to work too
        $defSql = "SELECT defense_type FROM defense_schedules 
                   WHERE team_id = ?
                   ORDER BY schedule_date DESC LIMIT 1";
        $defStmt = $pdo->prepare($defSql);
        $defStmt->execute([$team_id]);
        $def = $defStmt->fetch(PDO::FETCH_ASSOC);

        $current_defense_type = $def['defense_type'] ?? 'title_proposal';

        // Check if manuscript is configured for this program + defense type
        $manSql = "SELECT * FROM program_manuscript_requirements 
                   WHERE requirement_id = ? 
                   AND program_id = ? 
                   AND defense_type = ?
                   AND is_required = 1";

        $manStmt = $pdo->prepare($manSql);
        $manStmt->execute([$requirement_id, $prog['id'], $current_defense_type]);
        $manuscript = $manStmt->fetch(PDO::FETCH_ASSOC);

        if (!$manuscript) {
            return [
                'is_applicable' => false,
                'reason' => 'Not required for this program/stage combination',
                'program_id' => $prog['id'],
                'program_name' => $team['program'],
                'defense_type' => $current_defense_type
            ];
        }

        return [
            'is_applicable' => true,
            'is_required' => true,
            'submission_stage' => $manuscript['submission_stage'],
            'can_revise_after' => $manuscript['can_revise_after'],
            'visibility_to_panelist' => $manuscript['visibility_to_panelist'],
            'program_id' => $prog['id'],
            'program_name' => $team['program'],
            'defense_type' => $current_defense_type
        ];

    } catch (Exception $e) {
        error_log('Error in isManuscriptApplicableToTeam: ' . $e->getMessage());
        return ['is_applicable' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Get manuscripts for all defense types of a program
 * Useful for bulk configuration view
 * 
 * @param PDO $pdo Database connection
 * @param int $program_id Program ID
 * @return array Organized by defense type with manuscript counts
 */
function getProgramManuscriptsByDefenseType($pdo, $program_id) {
    try {
        $sql = "SELECT 
                    pmr.defense_type,
                    COUNT(*) as count,
                    CAST(GROUP_CONCAT(r.name SEPARATOR ', ') AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci) as manuscripts
                FROM program_manuscript_requirements pmr
                JOIN requirements r ON pmr.requirement_id = r.id
                WHERE pmr.program_id = ? AND pmr.is_required = 1
                GROUP BY pmr.defense_type
                ORDER BY FIELD(pmr.defense_type, 'title_proposal', 'title_defense', 'final_defense', 're-defense', 'general')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$program_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'program_id' => $program_id,
            'by_defense_type' => $results
        ];

    } catch (Exception $e) {
        error_log('Error in getProgramManuscriptsByDefenseType: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Add manuscript requirement for program + defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $requirement_id Manuscript requirement ID
 * @param int $program_id Program ID
 * @param string $defense_type Defense type
 * @param array $options ['is_required', 'submission_stage', 'can_revise_after', 'visibility_to_panelist']
 * @return bool
 */
function addManuscriptRequirement($pdo, $requirement_id, $program_id, $defense_type, $options = []) {
    try {
        $is_required = $options['is_required'] ?? true;
        $submission_stage = $options['submission_stage'] ?? 'before_defense';
        $can_revise_after = $options['can_revise_after'] ?? false;
        $visibility_to_panelist = $options['visibility_to_panelist'] ?? true;

        $sql = "INSERT INTO program_manuscript_requirements 
                (requirement_id, program_id, defense_type, is_required, submission_stage, can_revise_after, visibility_to_panelist)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    is_required = VALUES(is_required),
                    submission_stage = VALUES(submission_stage),
                    can_revise_after = VALUES(can_revise_after),
                    visibility_to_panelist = VALUES(visibility_to_panelist)";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $requirement_id,
            $program_id,
            $defense_type,
            $is_required ? 1 : 0,
            $submission_stage,
            $can_revise_after ? 1 : 0,
            $visibility_to_panelist ? 1 : 0
        ]);

    } catch (Exception $e) {
        error_log('Error in addManuscriptRequirement: ' . $e->getMessage());
        return false;
    }
}

/**
 * Remove manuscript requirement for program + defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $requirement_id Manuscript requirement ID
 * @param int $program_id Program ID
 * @param string $defense_type Defense type
 * @return bool
 */
function removeManuscriptRequirement($pdo, $requirement_id, $program_id, $defense_type) {
    try {
        $sql = "DELETE FROM program_manuscript_requirements 
                WHERE requirement_id = ? AND program_id = ? AND defense_type = ?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$requirement_id, $program_id, $defense_type]);

    } catch (Exception $e) {
        error_log('Error in removeManuscriptRequirement: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all manuscripts configured for a program
 * 
 * @param PDO $pdo Database connection
 * @param int $program_id Program ID
 * @return array List of manuscripts with all defense types
 */
function getProgramManuscripts($pdo, $program_id) {
    try {
        $sql = "SELECT 
                    r.id,
                    r.name,
                    r.description,
                    CAST(GROUP_CONCAT(DISTINCT pmr.defense_type SEPARATOR ', ') AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci) as defense_types,
                    COUNT(DISTINCT pmr.defense_type) as num_types
                FROM program_manuscript_requirements pmr
                JOIN requirements r ON pmr.requirement_id = r.id
                WHERE pmr.program_id = ? AND pmr.is_required = 1
                GROUP BY r.id, r.name, r.description
                ORDER BY r.name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$program_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log('Error in getProgramManuscripts: ' . $e->getMessage());
        return [];
    }
}
?>
