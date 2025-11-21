<?php
/**
 * File Visibility Helper Functions
 * 
 * Provides utility functions for managing and checking file visibility
 * based on defense types and team status
 */

/**
 * Check if team can access a file based on defense type and visibility rules
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @param int $requirement_id Requirement ID (file source)
 * @return array ['can_access' => bool, 'reason' => string, 'can_download' => bool]
 */
function canTeamAccessFile($pdo, $team_id, $requirement_id) {
    try {
        // Get team's current defense type
        $teamSql = "SELECT defense_type FROM defense_schedules 
                    WHERE team_id = ? 
                    AND schedule_date <= NOW()
                    ORDER BY schedule_date DESC 
                    LIMIT 1";
        $teamStmt = $pdo->prepare($teamSql);
        $teamStmt->execute([$team_id]);
        $result = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        $currentDefenseType = $result['defense_type'] ?? 'title_proposal';

        // Get visibility rules
        $visSql = "SELECT r.visibility_scope, fvr.can_view, fvr.can_download, fvr.defense_type
                   FROM requirements r
                   LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id AND fvr.defense_type = ?
                   WHERE r.id = ?";
        $visStmt = $pdo->prepare($visSql);
        $visStmt->execute([$currentDefenseType, $requirement_id]);
        $visibility = $visStmt->fetch(PDO::FETCH_ASSOC);

        if (!$visibility) {
            return ['can_access' => false, 'reason' => 'Requirement not found', 'can_download' => false];
        }

        $can_access = false;
        $reason = '';
        $can_download = false;

        if ($visibility['visibility_scope'] === 'hidden') {
            $reason = 'File is hidden from all teams';
        } elseif ($visibility['visibility_scope'] === 'all_stages') {
            $can_access = true;
            $can_download = $visibility['can_download'] ?? true;
            $reason = 'Visible to all defense stages';
        } elseif ($visibility['visibility_scope'] === 'specific_stages' && $visibility['can_view']) {
            $can_access = true;
            $can_download = $visibility['can_download'] ?? true;
            $reason = 'Visible to ' . $currentDefenseType . ' stage';
        } elseif ($visibility['visibility_scope'] === 'current_stage_only' && $visibility['defense_type'] === $currentDefenseType) {
            $can_access = true;
            $can_download = $visibility['can_download'] ?? true;
            $reason = 'Visible only during ' . $currentDefenseType . ' stage';
        }

        return [
            'can_access' => $can_access,
            'reason' => $reason,
            'can_download' => $can_download,
            'current_defense_type' => $currentDefenseType
        ];

    } catch (Exception $e) {
        return ['can_access' => false, 'reason' => 'Error checking access: ' . $e->getMessage(), 'can_download' => false];
    }
}

/**
 * Get all files visible to a team based on their current defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @return array Array of visible file records
 */
function getTeamVisibleFiles($pdo, $team_id) {
    try {
        // Get team's current defense type
        $teamSql = "SELECT t.id, t.name, ds.defense_type
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

        if (!$team) {
            return [];
        }

        $currentDefenseType = $team['defense_type'] ?? 'title_proposal';

        // Get visible files
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

        return [
            'team' => $team,
            'current_defense_type' => $currentDefenseType,
            'files' => $files
        ];

    } catch (Exception $e) {
        error_log('Error in getTeamVisibleFiles: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get defense manuscripts for a team
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @return array Array of defense manuscript records
 */
function getTeamDefenseManuscripts($pdo, $team_id) {
    try {
        // Get team's current defense type
        $teamSql = "SELECT ds.defense_type
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
        $result = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        $currentDefenseType = $result['defense_type'] ?? 'title_proposal';

        // Get defense manuscripts
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
        
        return $manStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log('Error in getTeamDefenseManuscripts: ' . $e->getMessage());
        return [];
    }
}

/**
 * Mark a requirement as a defense manuscript
 * 
 * @param PDO $pdo Database connection
 * @param int $requirement_id Requirement ID
 * @param bool $is_manuscript Whether to mark as manuscript
 * @return bool Success status
 */
function markAsDefenseManuscript($pdo, $requirement_id, $is_manuscript = true) {
    try {
        $sql = "UPDATE requirements SET is_defense_manuscript = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$is_manuscript ? 1 : 0, $requirement_id]);
    } catch (Exception $e) {
        error_log('Error in markAsDefenseManuscript: ' . $e->getMessage());
        return false;
    }
}

/**
 * Set visibility scope for a requirement
 * 
 * @param PDO $pdo Database connection
 * @param int $requirement_id Requirement ID
 * @param string $scope Visibility scope (all_stages, specific_stages, current_stage_only, hidden)
 * @return bool Success status
 */
function setVisibilityScope($pdo, $requirement_id, $scope) {
    try {
        $valid_scopes = ['all_stages', 'specific_stages', 'current_stage_only', 'hidden'];
        
        if (!in_array($scope, $valid_scopes)) {
            throw new Exception('Invalid scope: ' . $scope);
        }

        $sql = "UPDATE requirements SET visibility_scope = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$scope, $requirement_id]);
    } catch (Exception $e) {
        error_log('Error in setVisibilityScope: ' . $e->getMessage());
        return false;
    }
}

/**
 * Set visibility rule for a specific defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $requirement_id Requirement ID
 * @param string $defense_type Defense type
 * @param bool $can_view Whether this type can view the file
 * @param bool $can_download Whether this type can download
 * @param string $label Optional visibility label
 * @return bool Success status
 */
function setDefenseTypeVisibility($pdo, $requirement_id, $defense_type, $can_view, $can_download, $label = null) {
    try {
        $valid_types = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
        
        if (!in_array($defense_type, $valid_types)) {
            throw new Exception('Invalid defense type: ' . $defense_type);
        }

        $sql = "INSERT INTO file_visibility_rules (requirement_id, defense_type, can_view, can_download, visibility_label)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    can_view = VALUES(can_view),
                    can_download = VALUES(can_download),
                    visibility_label = VALUES(visibility_label),
                    updated_at = NOW()";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $requirement_id,
            $defense_type,
            $can_view ? 1 : 0,
            $can_download ? 1 : 0,
            $label
        ]);
    } catch (Exception $e) {
        error_log('Error in setDefenseTypeVisibility: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get visibility rules for a requirement
 * 
 * @param PDO $pdo Database connection
 * @param int $requirement_id Requirement ID
 * @return array Array of visibility rules
 */
function getVisibilityRules($pdo, $requirement_id) {
    try {
        $sql = "SELECT defense_type, can_view, can_download, visibility_label 
                FROM file_visibility_rules 
                WHERE requirement_id = ? 
                ORDER BY FIELD(defense_type, 'title_proposal', 'title_defense', 'final_defense', 're-defense', 'general')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$requirement_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error in getVisibilityRules: ' . $e->getMessage());
        return [];
    }
}

/**
 * Log file access for audit trail
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @param int $requirement_id Requirement ID
 * @param string $file_name File name
 * @param string $action 'view' or 'download'
 * @param int $user_id User ID (optional)
 * @return bool Success status
 */
function logFileAccess($pdo, $team_id, $requirement_id, $file_name, $action = 'view', $user_id = null) {
    try {
        $sql = "INSERT INTO defense_file_access_log (team_id, requirement_id, file_name, action, user_id) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$team_id, $requirement_id, $file_name, $action, $user_id]);
    } catch (Exception $e) {
        error_log('Error in logFileAccess: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get file access history for a team
 * 
 * @param PDO $pdo Database connection
 * @param int $team_id Team ID
 * @param int $limit Number of records to return
 * @return array Array of access log records
 */
function getFileAccessHistory($pdo, $team_id, $limit = 50) {
    try {
        $sql = "SELECT 
                    dfal.id,
                    dfal.requirement_id,
                    dfal.file_name,
                    dfal.action,
                    dfal.access_timestamp,
                    r.name as requirement_name,
                    u.first_name,
                    u.last_name
                FROM defense_file_access_log dfal
                LEFT JOIN requirements r ON dfal.requirement_id = r.id
                LEFT JOIN users u ON dfal.user_id = u.id
                WHERE dfal.team_id = ?
                ORDER BY dfal.access_timestamp DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$team_id, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error in getFileAccessHistory: ' . $e->getMessage());
        return [];
    }
}
?>
