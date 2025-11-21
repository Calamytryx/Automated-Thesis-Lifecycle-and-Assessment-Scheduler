<?php
/**
 * Program-Specific Requirements Management Functions
 * 
 * Provides functions for managing which requirements apply to which defense types
 * on a per-program basis, allowing fine-grained control over requirement workflows.
 */

/**
 * Get all programs with their current requirement mappings for a specific defense type
 * 
 * @param PDO $pdo Database connection
 * @param string|null $defenseType Optional filter by defense type
 * @return array Array of programs with their mapped requirements
 */
function getProgramsWithRequirementMappings($pdo, $defenseType = null) {
    try {
        $query = "
            SELECT DISTINCT
                p.id,
                p.name,
                p.college,
                p.department,
                p.specialization,
                COUNT(DISTINCT prm.id) as total_mappings
            FROM programs p
            LEFT JOIN program_requirements_mapping prm ON p.id = prm.program_id
        ";
        
        if ($defenseType) {
            $query .= " WHERE prm.defense_type = ?";
        }
        
        $query .= " GROUP BY p.id, p.name ORDER BY p.name ASC";
        
        $stmt = $pdo->prepare($query);
        if ($defenseType) {
            $stmt->execute([$defenseType]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching programs with requirement mappings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all requirements mapped to a specific defense type for a program
 * 
 * @param PDO $pdo Database connection
 * @param int $programId Program ID
 * @param string $defenseType Defense type (title_proposal, title_defense, final_defense, re-defense, general)
 * @return array Array of requirements for this program and defense type
 */
function getRequirementsForDefenseType($pdo, $programId, $defenseType) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.name,
                r.description,
                r.due_date,
                r.requirement_type,
                r.allow_multiple_submissions,
                r.max_submissions,
                prm.id as mapping_id,
                prm.is_mandatory,
                prm.display_order,
                CASE WHEN prm.id IS NOT NULL THEN 1 ELSE 0 END as is_mapped
            FROM requirements r
            LEFT JOIN program_requirements_mapping prm 
                ON r.id = prm.requirement_id 
                AND prm.program_id = ? 
                AND prm.defense_type = ?
            ORDER BY prm.display_order ASC, r.name ASC
        ");
        $stmt->execute([$programId, $defenseType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching requirements for defense type: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all defense types and their current mappings for a program
 * 
 * @param PDO $pdo Database connection
 * @param int $programId Program ID
 * @return array Array with defense types and their requirement counts
 */
function getDefenseTypesForProgram($pdo, $programId) {
    try {
        $defenseTypes = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
        $result = [];
        
        foreach ($defenseTypes as $type) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM program_requirements_mapping 
                WHERE program_id = ? AND defense_type = ?
            ");
            $stmt->execute([$programId, $type]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $result[] = [
                'type' => $type,
                'label' => ucwords(str_replace('-', ' ', $type)),
                'count' => (int)$row['count']
            ];
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error fetching defense types for program: " . $e->getMessage());
        return [];
    }
}

/**
 * Add a requirement mapping for a program and defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $programId Program ID
 * @param string $defenseType Defense type
 * @param int $requirementId Requirement ID
 * @param int $userId Admin user ID
 * @param bool $isMandatory Whether requirement is mandatory
 * @param int $displayOrder Display order
 * @return bool Success/failure
 */
function addRequirementMapping($pdo, $programId, $defenseType, $requirementId, $userId, $isMandatory = true, $displayOrder = 0) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO program_requirements_mapping 
            (program_id, defense_type, requirement_id, is_mandatory, display_order, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                is_mandatory = ?,
                display_order = ?,
                updated_at = NOW()
        ");
        
        return $stmt->execute([
            $programId, 
            $defenseType, 
            $requirementId, 
            $isMandatory ? 1 : 0, 
            $displayOrder,
            $userId,
            // For ON DUPLICATE KEY UPDATE
            $isMandatory ? 1 : 0,
            $displayOrder
        ]);
    } catch (Exception $e) {
        error_log("Error adding requirement mapping: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove a requirement mapping for a program and defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $programId Program ID
 * @param string $defenseType Defense type
 * @param int $requirementId Requirement ID
 * @return bool Success/failure
 */
function removeRequirementMapping($pdo, $programId, $defenseType, $requirementId) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM program_requirements_mapping 
            WHERE program_id = ? AND defense_type = ? AND requirement_id = ?
        ");
        return $stmt->execute([$programId, $defenseType, $requirementId]);
    } catch (Exception $e) {
        error_log("Error removing requirement mapping: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all requirements currently not mapped for a program and defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $programId Program ID
 * @param string $defenseType Defense type
 * @return array Array of unmapped requirements
 */
function getUnmappedRequirements($pdo, $programId, $defenseType) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.id, r.name, r.description
            FROM requirements r
            WHERE r.id NOT IN (
                SELECT requirement_id FROM program_requirements_mapping 
                WHERE program_id = ? AND defense_type = ?
            )
            ORDER BY r.name ASC
        ");
        $stmt->execute([$programId, $defenseType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching unmapped requirements: " . $e->getMessage());
        return [];
    }
}

/**
 * Bulk update requirement mappings for a program and defense type
 * 
 * @param PDO $pdo Database connection
 * @param int $programId Program ID
 * @param string $defenseType Defense type
 * @param array $requirementIds Array of requirement IDs to map
 * @param int $userId Admin user ID
 * @return bool Success/failure
 */
function updateDefenseTypeRequirements($pdo, $programId, $defenseType, $requirementIds, $userId) {
    try {
        $pdo->beginTransaction();
        
        // Remove all existing mappings for this defense type
        $deleteStmt = $pdo->prepare("
            DELETE FROM program_requirements_mapping 
            WHERE program_id = ? AND defense_type = ?
        ");
        $deleteStmt->execute([$programId, $defenseType]);
        
        // Add new mappings
        $insertStmt = $pdo->prepare("
            INSERT INTO program_requirements_mapping 
            (program_id, defense_type, requirement_id, display_order, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($requirementIds as $order => $reqId) {
            $insertStmt->execute([$programId, $defenseType, $reqId, $order, $userId]);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating defense type requirements: " . $e->getMessage());
        return false;
    }
}

/**
 * Get requirements for a team based on their current defense stage and program
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param string $defenseType Current defense type
 * @return array Array of requirements applicable to this team and defense stage
 */
function getTeamRequirementsForDefenseType($pdo, $teamId, $defenseType) {
    try {
        // First get the team's program
        $teamStmt = $pdo->prepare("SELECT program FROM teams WHERE id = ?");
        $teamStmt->execute([$teamId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$team) {
            return [];
        }
        
        $programId = $team['program'];
        
        // Get requirements mapped for this program and defense type
        $reqStmt = $pdo->prepare("
            SELECT 
                r.id,
                r.name,
                r.description,
                r.due_date,
                prm.is_mandatory,
                prm.display_order
            FROM program_requirements_mapping prm
            JOIN requirements r ON prm.requirement_id = r.id
            WHERE prm.program_id = ? AND prm.defense_type = ?
            ORDER BY prm.display_order ASC, r.name ASC
        ");
        $reqStmt->execute([$programId, $defenseType]);
        
        $requirements = $reqStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no program-specific mappings, fall back to requirements with matching requirement_type
        if (empty($requirements)) {
            $fallbackStmt = $pdo->prepare("
                SELECT 
                    id,
                    name,
                    description,
                    due_date,
                    1 as is_mandatory,
                    0 as display_order
                FROM requirements 
                WHERE requirement_type = ? OR requirement_type = 'general'
                ORDER BY name ASC
            ");
            $fallbackStmt->execute([$defenseType]);
            $requirements = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $requirements;
    } catch (Exception $e) {
        error_log("Error fetching team requirements for defense type: " . $e->getMessage());
        return [];
    }
}

