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
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($row) {
            $row['normalized_program'] = normalizeProgramName($row['program']);
            // Try to find the college for the normalized program from the programs table
            try {
                $pstmt = $pdo->prepare("SELECT college FROM programs WHERE name = ? LIMIT 1");
                $pstmt->execute([$row['normalized_program']]);
                $prog = $pstmt->fetch(PDO::FETCH_ASSOC);
                $row['program_college'] = $prog ? $prog['college'] : null;
            } catch (Exception $e) {
                $row['program_college'] = null;
            }
        }
        return $row;
    } catch (Exception $e) {
        error_log("getPanelistInfo error: " . $e->getMessage());
        return null;
    }
}

/**
 * Normalize a program string by removing specialization after a dash
 * e.g. "Bachelor of Science in Computer Science - Data Science" => "Bachelor of Science in Computer Science"
 */
function normalizeProgramName($program) {
    if (!is_string($program)) {
        return $program;
    }
    $parts = preg_split('/\s*[-–—]\s*/u', $program);
    return trim($parts[0]);
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

    // Enforce that non-external panelist positions 1 and 2 come from same normalized program (or same college if program missing)
    $p1 = $panelists[0];
    $p2 = $panelists[1];
    if ($types[0] !== 'external' && $types[1] !== 'external') {
        $p1prog = $p1['normalized_program'] ?? null;
        $p2prog = $p2['normalized_program'] ?? null;
        if ($p1prog && $p2prog) {
            if ($p1prog !== $p2prog) {
                error_log("Invalid: panelist1 and panelist2 programs differ (" . ($p1prog ?: '-') . " vs " . ($p2prog ?: '-') . ")");
                return false;
            }
        } else {
            $p1col = $p1['program_college'] ?? null;
            $p2col = $p2['program_college'] ?? null;
            if ($p1col && $p2col && $p1col !== $p2col) {
                error_log("Invalid: panelist1 and panelist2 colleges differ (" . ($p1col ?: '-') . " vs " . ($p2col ?: '-') . ")");
                return false;
            }
        }
    }

    // Ensure panelist 3 is external OR from a different college than panelist1
    $p3 = $panelists[2];
    if (($p3['is_external'] ?? 0) != 1) {
        $p3col = $p3['program_college'] ?? null;
        $p1col = $p1['program_college'] ?? null;
        if ($p3col && $p1col && $p3col === $p1col) {
            error_log("Invalid: panelist3 is not external and is from same college as panelist1");
            return false;
        }
    }
    
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
function buildOptimalPanelistCombination($pdo, $adviserId = null, $exclude = [], $studentProgram = null, $studentCollege = null)
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

        // If student program/college provided, force slots 1&2 to come from same program+college
        $useStudentMatch = $studentProgram || $studentCollege;
        if ($useStudentMatch) {
            $studentProgram = $studentProgram ? normalizeProgramName($studentProgram) : null;
            // Build matching pools where BOTH normalized_program and program_college match student
            $matchingFull = [];
            foreach ($fullTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if ($studentProgram && $info['normalized_program'] === $studentProgram &&
                    $studentCollege && $info['program_college'] === $studentCollege) {
                    $matchingFull[] = $id;
                }
            }
            $matchingPart = [];
            foreach ($partTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if ($studentProgram && $info['normalized_program'] === $studentProgram &&
                    $studentCollege && $info['program_college'] === $studentCollege) {
                    $matchingPart[] = $id;
                }
            }

            // Combined matching pool must have at least two panelists (slots 1 & 2)
            $matchingCombined = array_values(array_unique(array_merge($matchingFull, $matchingPart)));
            if (count($matchingCombined) < 2) {
                error_log("Not enough matching panelists in same program+college to fill slots 1 and 2");
                return null;
            }

            // Replace original pools but keep full/part distinctions for role selection
            $fullTime = $matchingFull;
            $partTime = $matchingPart;
            // Keep external pool unchanged for slot 3
        }

        // Shuffle each pool to get random selections (avoid always picking same IDs)
        shuffle($fullTime);
        shuffle($partTime);
        shuffle($external);

        // Defined allowed combinations (order: slot1, slot2, slot3)
$patterns = mt_rand(1, 4) === 1 ? [
            ['full_time', 'part_time', 'external'],
        ] : [
            ['full_time', 'full_time', 'external'],
            ['full_time', 'full_time', 'part_time'],
            ['full_time', 'part_time', 'external'],
            ['full_time', 'part_time', 'part_time'],
        ];

        foreach ($patterns as $pattern) {
            $selected = [null, null, null];
            $available = [
                'full_time' => $fullTime,
                'part_time' => $partTime,
                'external'  => $external,
            ];

            // Try to fill each position, forcing slots 0 and 1 from matchingCombined when required
            $valid = true;
            $used = [];
            for ($i = 0; $i < 3; $i++) {
                $type = $pattern[$i];
                if (empty($available[$type])) { $valid = false; break; }

                $chosen = null;
                foreach ($available[$type] as $candidate) {
                    if (in_array($candidate, $used, true)) continue;
                    if ($useStudentMatch && ($i === 0 || $i === 1)) {
                        if (!in_array($candidate, $matchingCombined, true)) continue;
                    }
                    $chosen = $candidate;
                    break;
                }
                if ($chosen === null) { $valid = false; break; }

                $selected[$i] = $chosen;
                $used[] = $chosen;
                // remove chosen from all pools
                foreach ($available as $t => &$pool) {
                    $pool = array_values(array_diff($pool, [$chosen]));
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