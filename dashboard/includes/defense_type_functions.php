<?php
/**
 * Defense Type Detection & Management
 * 
 * Provides functions to determine the appropriate defense type for a team
 * based on their status, with support for admin overrides and persistent panelists.
 */

/**
 * Determine the defense type for a team
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @return string One of: 'title_proposal', 'title_defense', 'final_defense'
 */
function getTeamDefenseType($pdo, $teamId) {
    try {
        // 1. Check for active admin override
        $overrideStmt = $pdo->prepare("
            SELECT override_type FROM defense_type_overrides 
            WHERE team_id = ? AND active = 1 
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY created_at DESC LIMIT 1
        ");
        $overrideStmt->execute([$teamId]);
        if ($override = $overrideStmt->fetchColumn()) {
            return $override;
        }

        // 2. Check if team has approved titles
        $titleStmt = $pdo->prepare("
            SELECT COUNT(*) as approved_count FROM research_titles 
            WHERE team_id = ? AND approved_at IS NOT NULL
        ");
        $titleStmt->execute([$teamId]);
        $titleRow = $titleStmt->fetch(PDO::FETCH_ASSOC);
        $approvedTitles = $titleRow['approved_count'] ?? 0;

        // 3. No approved titles = title_proposal stage
        if ($approvedTitles === 0) {
            return 'title_proposal';
        }

        // 4. Check for 2 completed evaluations (final_defense indicator)
        $evalStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT epp.panelist_id) as evaluator_count
            FROM defense_schedules ds
            LEFT JOIN evaluation_per_panel epp ON ds.id = epp.defense_schedule_id
            WHERE ds.team_id = ? AND epp.created_at IS NOT NULL
            GROUP BY ds.id
        ");
        $evalStmt->execute([$teamId]);
        $evaluations = 0;
        while ($evalRow = $evalStmt->fetch(PDO::FETCH_ASSOC)) {
            $evaluations = max($evaluations, $evalRow['evaluator_count'] ?? 0);
        }

        if ($evaluations >= 2) {
            return 'final_defense';
        }

        // 5. Has approved title but < 2 evaluations = title_defense
        return 'title_defense';

    } catch (Exception $e) {
        error_log("Error determining defense type for team {$teamId}: " . $e->getMessage());
        return 'title_proposal'; // Default to title_proposal on error
    }
}

/**
 * Get or create persistent panelist assignments for a team's defense
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param string $defenseType Defense type
 * @param array|null $suggestedPanelists Optional array of panelist IDs to assign
 * @return array Array of panelist IDs ordered by position [primary, secondary, tertiary]
 */
function getPersistentPanelists($pdo, $teamId, $defenseType, $suggestedPanelists = null) {
    try {
        // Check for existing assignments
        $existingStmt = $pdo->prepare("
            SELECT panelist_id FROM team_panelists 
            WHERE team_id = ? AND defense_type = ? AND locked = 1
            ORDER BY panelist_position ASC
        ");
        $existingStmt->execute([$teamId, $defenseType]);
        $existing = $existingStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($existing)) {
            return $existing;
        }

        // No locked assignments found, return suggestions if provided
        return is_array($suggestedPanelists) ? $suggestedPanelists : [];

    } catch (Exception $e) {
        error_log("Error fetching persistent panelists for team {$teamId}: " . $e->getMessage());
        return [];
    }
}

/**
 * Lock panelist assignments for a team's defense stage
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param string $defenseType Defense type
 * @param array $panelistIds Array of panelist IDs in order [primary, secondary, tertiary]
 * @param int $userId User ID who is locking (usually admin)
 * @return bool Success/failure
 */
function lockPanelistAssignments($pdo, $teamId, $defenseType, $panelistIds, $userId) {
    try {
        $pdo->beginTransaction();

        // Clear any existing non-locked assignments
        $deleteStmt = $pdo->prepare("
            DELETE FROM team_panelists 
            WHERE team_id = ? AND defense_type = ? AND locked = 0
        ");
        $deleteStmt->execute([$teamId, $defenseType]);

        // Insert new locked assignments
        $insertStmt = $pdo->prepare("
            INSERT INTO team_panelists (team_id, defense_type, panelist_id, panelist_position, locked, created_by)
            VALUES (?, ?, ?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE 
                locked = 1, 
                admin_override = 1, 
                updated_at = NOW(),
                created_by = ?
        ");

        foreach ($panelistIds as $position => $panelistId) {
            $insertStmt->execute([$teamId, $defenseType, $panelistId, $position + 1, $userId, $userId]);
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error locking panelists for team {$teamId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Unlock panelist assignments to allow changes
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param string $defenseType Defense type
 * @return bool Success/failure
 */
function unlockPanelistAssignments($pdo, $teamId, $defenseType) {
    try {
        $stmt = $pdo->prepare("
            UPDATE team_panelists 
            SET locked = 0, admin_override = 0, updated_at = NOW()
            WHERE team_id = ? AND defense_type = ?
        ");
        return $stmt->execute([$teamId, $defenseType]);

    } catch (Exception $e) {
        error_log("Error unlocking panelists for team {$teamId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin override for defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param string $overrideType Defense type to force
 * @param int $userId Admin user ID
 * @param string|null $reason Reason for override
 * @param string|null $expiresAt Optional expiry date
 * @return bool Success/failure
 */
function setDefenseTypeOverride($pdo, $teamId, $overrideType, $userId, $reason = null, $expiresAt = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO defense_type_overrides (team_id, override_type, reason, created_by, active, expires_at)
            VALUES (?, ?, ?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE 
                active = 1, 
                updated_at = NOW()
        ");
        
        return $stmt->execute([$teamId, $overrideType, $reason, $userId, $expiresAt]);

    } catch (Exception $e) {
        error_log("Error setting defense type override for team {$teamId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove admin override for defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @return bool Success/failure
 */
function removeDefenseTypeOverride($pdo, $teamId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE defense_type_overrides 
            SET active = 0, updated_at = NOW()
            WHERE team_id = ? AND active = 1
        ");
        return $stmt->execute([$teamId]);

    } catch (Exception $e) {
        error_log("Error removing defense type override for team {$teamId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all requirement files submitted by a team for a requirement
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param int $requirementId Requirement ID
 * @return array Array of submitted files with details
 */
function getTeamRequirementSubmissions($pdo, $teamId, $requirementId) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM team_requirement_files 
            WHERE team_id = ? AND requirement_id = ? AND deleted_at IS NULL
            ORDER BY submission_number ASC, submitted_at DESC
        ");
        $stmt->execute([$teamId, $requirementId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error fetching requirement submissions for team {$teamId}, requirement {$requirementId}: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if a requirement allows multiple submissions
 * 
 * @param PDO $pdo Database connection
 * @param int $requirementId Requirement ID
 * @return array Requirement details with multi-submission info
 */
function getRequirementDetails($pdo, $requirementId) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, requirement_type, allow_multiple_submissions, max_submissions 
            FROM requirements WHERE id = ?
        ");
        $stmt->execute([$requirementId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    } catch (Exception $e) {
        error_log("Error fetching requirement details for requirement {$requirementId}: " . $e->getMessage());
        return [];
    }
}

/**
 * Combine multiple title proposal files into a single defense schedule reference
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param int $defenseScheduleId Defense schedule ID
 * @param array $requirementFileIds Array of team_requirement_files IDs to link
 * @return bool Success/failure
 */
function linkMultipleFilesToDefense($pdo, $defenseScheduleId, $requirementFileIds) {
    try {
        $fileIdsJson = json_encode(array_map('intval', $requirementFileIds));
        $stmt = $pdo->prepare("
            UPDATE defense_schedules 
            SET related_requirement_files = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$fileIdsJson, $defenseScheduleId]);

    } catch (Exception $e) {
        error_log("Error linking files to defense schedule {$defenseScheduleId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all files linked to a defense schedule (for multi-submission requirements)
 * 
 * @param PDO $pdo Database connection
 * @param int $defenseScheduleId Defense schedule ID
 * @return array Array of linked requirement files
 */
function getDefenseScheduleFiles($pdo, $defenseScheduleId) {
    try {
        $stmt = $pdo->prepare("
            SELECT ds.related_requirement_files 
            FROM defense_schedules ds 
            WHERE ds.id = ?
        ");
        $stmt->execute([$defenseScheduleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row || !$row['related_requirement_files']) {
            return [];
        }

        $fileIds = json_decode($row['related_requirement_files'], true) ?: [];
        if (empty($fileIds)) {
            return [];
        }

        // Fetch details for each file
        $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
        $fileStmt = $pdo->prepare("
            SELECT * FROM team_requirement_files 
            WHERE id IN ({$placeholders})
            ORDER BY submission_number ASC
        ");
        $fileStmt->execute($fileIds);
        return $fileStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error fetching files for defense schedule {$defenseScheduleId}: " . $e->getMessage());
        return [];
    }
}
