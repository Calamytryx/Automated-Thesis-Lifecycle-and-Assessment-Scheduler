<?php

/**
 * Panelist Combination Validation and Selection Functions
 * 
 * Handles panelist assignment logic for defense schedule generation
 * with specific combinations and order of importance.
 * 
 * Allowed combinations (in order of importance):
 * 1. Full time, Part time, External
 * 2. Full time, Full time, External
 * 3. Full time, Full time, Part time
 * 4. Full time, Part time, Part time
 * 
 * External panelists (is_external=1) MUST be assigned as panelist_id3 (3rd position)
 * 
 * @package Defense Scheduling
 */

/**
 * Get panelist information including employment status and external status
 * 
 * @param PDO $pdo Database connection
 * @param int $panelistId Panelist user ID
 * @return array|null Panelist info with employment type and external status
 */
function getPanelistInfo($pdo, $panelistId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                usertype,
                is_parttime,
                is_external,
                first_name,
                last_name,
                username,
                program
            FROM users
            WHERE id = ? AND usertype = 2 AND deleted_at IS NULL
        ");
        $stmt->execute([$panelistId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log("getPanelistInfo error: " . $e->getMessage());
        return null;
    }
}

/**
 * Classify panelist as full-time (0), part-time (1), or external (2)
 * 
 * @param array $panelistInfo Panelist information from database
 * @return string 'full_time', 'part_time', or 'external'
 */
function classifyPanelistType($panelistInfo) {
    if (!is_array($panelistInfo)) {
        return null;
    }
    
    // External panelists take priority
    if ($panelistInfo['is_external'] == 1) {
        return 'external';
    }
    
    // Check employment status
    if ($panelistInfo['is_parttime'] == 1) {
        return 'part_time';
    }
    
    return 'full_time';
}

/**
 * Validate if a panelist combination is allowed
 * 
 * @param array $panelists Array of 3 panelist info arrays [panelist1, panelist2, panelist3]
 * @return bool True if combination is valid
 */
function isValidPanelistCombination($panelists) {
    if (!is_array($panelists) || count($panelists) != 3) {
        return false;
    }
    
    // Filter out null/empty panelists
    $validPanelists = array_filter($panelists, function($p) {
        return $p !== null && is_array($p);
    });
    
    if (count($validPanelists) < 3) {
        return false; // All 3 positions must be filled
    }
    
    // Classify each panelist
    $types = array_map('classifyPanelistType', $panelists);
    
    // Validate panelist_id3 (3rd position) must be external if external panelist is assigned
    if ($panelists[2]['is_external'] == 1) {
        // External must be in 3rd position
        if ($types[0] === 'external' || $types[1] === 'external') {
            error_log("Invalid combination: external panelist not in 3rd position");
            return false;
        }
    }
    
    // Validate allowed combinations
    $allowedCombinations = [
        ['full_time', 'part_time', 'external'],
        ['full_time', 'full_time', 'external'],
        ['full_time', 'full_time', 'part_time'],
        ['full_time', 'part_time', 'part_time']
    ];
    
    // Check if current combination matches any allowed combination
    foreach ($allowedCombinations as $allowed) {
        if ($types === $allowed) {
            return true;
        }
    }
    
    error_log("Invalid panelist combination: " . implode(", ", $types));
    return false;
}

/**
 * Get list of external panelists available for assignment
 * 
 * @param PDO $pdo Database connection
 * @param array $excludePanelistIds Panelist IDs to exclude from results
 * @return array List of external panelist IDs
 */
function getExternalPanelists($pdo, $excludePanelistIds = []) {
    try {
        $placeholders = '';
        $params = [];
        
        if (!empty($excludePanelistIds)) {
            $placeholders = ' AND id NOT IN (' . implode(',', array_fill(0, count($excludePanelistIds), '?')) . ')';
            $params = $excludePanelistIds;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, username
            FROM users
            WHERE usertype = 2 
            AND is_external = 1 
            AND deleted_at IS NULL
            $placeholders
            ORDER BY first_name, last_name
        ");
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($result, 'id') ?: [];
    } catch (Exception $e) {
        error_log("getExternalPanelists error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get full-time panelists available for assignment
 * 
 * @param PDO $pdo Database connection
 * @param array $excludePanelistIds Panelist IDs to exclude from results
 * @return array List of full-time panelist IDs
 */
function getFullTimePanelists($pdo, $excludePanelistIds = []) {
    try {
        $placeholders = '';
        $params = [];
        
        if (!empty($excludePanelistIds)) {
            $placeholders = ' AND id NOT IN (' . implode(',', array_fill(0, count($excludePanelistIds), '?')) . ')';
            $params = $excludePanelistIds;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, username
            FROM users
            WHERE usertype = 2 
            AND is_parttime = 0 
            AND is_external = 0
            AND deleted_at IS NULL
            $placeholders
            ORDER BY first_name, last_name
        ");
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($result, 'id') ?: [];
    } catch (Exception $e) {
        error_log("getFullTimePanelists error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get part-time panelists available for assignment
 * 
 * @param PDO $pdo Database connection
 * @param array $excludePanelistIds Panelist IDs to exclude from results
 * @return array List of part-time panelist IDs
 */
function getPartTimePanelists($pdo, $excludePanelistIds = []) {
    try {
        $placeholders = '';
        $params = [];
        
        if (!empty($excludePanelistIds)) {
            $placeholders = ' AND id NOT IN (' . implode(',', array_fill(0, count($excludePanelistIds), '?')) . ')';
            $params = $excludePanelistIds;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, username
            FROM users
            WHERE usertype = 2 
            AND is_parttime = 1 
            AND is_external = 0
            AND deleted_at IS NULL
            $placeholders
            ORDER BY first_name, last_name
        ");
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($result, 'id') ?: [];
    } catch (Exception $e) {
        error_log("getPartTimePanelists error: " . $e->getMessage());
        return [];
    }
}

/**
 * Build panelist combination using order of importance
 * 
 * Attempts combinations in this order:
 * 1. Full time, Part time, External
 * 2. Full time, Full time, External
 * 3. Full time, Full time, Part time
 * 4. Full time, Part time, Part time
 * 
 * @param PDO $pdo Database connection
 * @param int $adviserId Adviser ID (usually panelist_id)
 * @param array $preferredPanelists Pre-selected preferred panelists (optional)
 * @return array|null [panelist_id, panelist_id2, panelist_id3] or null if no valid combo found
 */
function buildOptimalPanelistCombination($pdo, $adviserId = null, $exclude = [])
{
    try {
        // Build full exclude list
        $excludeList = $exclude;
        if ($adviserId) {
            $excludeList[] = $adviserId;
        }
        $excludeList = array_unique($excludeList);

        // Get available panelists by type, excluding those in $excludeList
        $fullTime   = getFullTimePanelists($pdo, $excludeList);
        $partTime   = getPartTimePanelists($pdo, $excludeList);
        $external   = getExternalPanelists($pdo, $excludeList);

        // Shuffle each pool to get random selections (avoid always picking same IDs)
        shuffle($fullTime);
        shuffle($partTime);
        shuffle($external);

        // Defined allowed combinations (order: slot1, slot2, slot3)
        $patterns = [
            ['full_time', 'part_time', 'external'],
            ['full_time', 'full_time', 'external'],
            ['full_time', 'full_time', 'part_time'],
            ['full_time', 'part_time', 'part_time']
        ];

        foreach ($patterns as $pattern) {
            $selected = [null, null, null];
            $available = [
                'full_time' => $fullTime,
                'part_time' => $partTime,
                'external'  => $external,
            ];

            // Try to fill each position
            $valid = true;
            for ($i = 0; $i < 3; $i++) {
                $type = $pattern[$i];
                if (empty($available[$type])) {
                    $valid = false;
                    break;
                }
                // Take the first (random due to shuffle) available panelist of this type
                $selected[$i] = array_shift($available[$type]);
                // Remove chosen panelist from all pools to avoid reuse
                foreach ($available as $t => &$pool) {
                    $pool = array_diff($pool, [$selected[$i]]);
                }
            }

            if ($valid && !in_array(null, $selected, true)) {
                // Optional: final validation (should pass by construction)
                $infos = [
                    getPanelistInfo($pdo, $selected[0]),
                    getPanelistInfo($pdo, $selected[1]),
                    getPanelistInfo($pdo, $selected[2])
                ];
                if (isValidPanelistCombination($infos)) {
                    error_log("Valid combination built: " . implode(',', $selected));
                    return $selected;
                }
            }
        }

        error_log("Could not build any valid panelist combination with available panelists (adviser=$adviserId)");
        return null;
    } catch (Exception $e) {
        error_log("buildOptimalPanelistCombination error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get the best external panelist for assignment (3rd position)
 * 
 * @param PDO $pdo Database connection
 * @param array $excludePanelistIds Panelist IDs to exclude
 * @return int|null External panelist ID or null if none available
 */
function getPreferredExternalPanelist($pdo, $excludePanelistIds = []) {
    $externals = getExternalPanelists($pdo, $excludePanelistIds);
    return !empty($externals) ? reset($externals) : null;
}
