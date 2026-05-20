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
    error_log("DEBUG: Entering getPanelistInfo() for ID: " . var_export($panelistId, true));
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
            error_log("DEBUG: getPanelistInfo() - Found user. Proceeding to normalize program: " . var_export($row['program'], true));
            $row['normalized_program'] = normalizeProgramName($row['program']);
            
            // Try to find the college for the normalized program from the programs table
            try {
                error_log("DEBUG: getPanelistInfo() - Fetching college for normalized program: " . var_export($row['normalized_program'], true));
                $pstmt = $pdo->prepare("SELECT college FROM programs WHERE name = ? LIMIT 1");
                $pstmt->execute([$row['normalized_program']]);
                $prog = $pstmt->fetch(PDO::FETCH_ASSOC);
                
                if ($prog) {
                    $row['program_college'] = $prog['college'];
                    error_log("DEBUG: getPanelistInfo() - Found college: " . var_export($row['program_college'], true));
                } else {
                    $row['program_college'] = null;
                    error_log("DEBUG: getPanelistInfo() - No matching college found in programs table.");
                }
            } catch (Exception $e) {
                error_log("DEBUG: getPanelistInfo() inner catch - Failed to fetch college. Error: " . $e->getMessage());
                $row['program_college'] = null;
            }
        } else {
            error_log("DEBUG: getPanelistInfo() - No user found or condition mismatched for ID: $panelistId");
        }
        
        error_log("DEBUG: Exiting getPanelistInfo() for ID: $panelistId. Returning payload data status: " . ($row ? "Success" : "Null"));
        return $row;
    } catch (Exception $e) {
        error_log("ERROR: getPanelistInfo error: " . $e->getMessage());
        return null;
    }
}

/**
 * Normalize a program string by removing specialization after a dash
 * e.g. "Bachelor of Science in Computer Science - Data Science" => "Bachelor of Science in Computer Science"
 */
function normalizeProgramName($program) {
    error_log("DEBUG: Entering normalizeProgramName() with payload: " . var_export($program, true));
    if (!is_string($program)) {
        error_log("DEBUG: normalizeProgramName() - Payload is not a string. Returning intact.");
        return $program;
    }
    $parts = preg_split('/\s*[-–—]\s*/u', $program);
    $result = trim($parts[0]);
    error_log("DEBUG: Exiting normalizeProgramName(). Result output: " . var_export($result, true));
    return $result;
}

/**
 * Classify panelist as full-time (0), part-time (1), or external (2)
 * 
 * @param array $panelistInfo Panelist information from database
 * @return string 'full_time', 'part_time', or 'external'
 */
function classifyPanelistType($panelistInfo) {
    error_log("DEBUG: Entering classifyPanelistType()");
    if (!is_array($panelistInfo)) {
        error_log("DEBUG: classifyPanelistType() - Info provided is not an array. Returning null.");
        return null;
    }
    
    // External panelists take priority
    if (isset($panelistInfo['is_external']) && $panelistInfo['is_external'] == 1) {
        error_log("DEBUG: classifyPanelistType() - Classified as 'external' for ID: " . ($panelistInfo['id'] ?? 'unknown'));
        return 'external';
    }
    
    // Check employment status
    if (isset($panelistInfo['is_parttime']) && $panelistInfo['is_parttime'] == 1) {
        error_log("DEBUG: classifyPanelistType() - Classified as 'part_time' for ID: " . ($panelistInfo['id'] ?? 'unknown'));
        return 'part_time';
    }
    
    error_log("DEBUG: classifyPanelistType() - Defaulting classification to 'full_time' for ID: " . ($panelistInfo['id'] ?? 'unknown'));
    return 'full_time';
}

/**
 * Validate if a panelist combination is allowed
 * 
 * @param array $panelists Array of 3 panelist info arrays [panelist1, panelist2, panelist3]
 * @return bool True if combination is valid
 */
function isValidPanelistCombination($panelists) {
    error_log("DEBUG: Entering isValidPanelistCombination()");
    if (!is_array($panelists) || count($panelists) != 3) {
        error_log("DEBUG: isValidPanelistCombination() - Invalid configuration structure or counts != 3. Returning false.");
        return false;
    }
    
    // Filter out null/empty panelists
    $validPanelists = array_filter($panelists, function($p) {
        return $p !== null && is_array($p);
    });
    error_log("DEBUG: isValidPanelistCombination() - Count of non-empty panelist payloads: " . count($validPanelists));
    
    if (count($validPanelists) < 3) {
        error_log("DEBUG: isValidPanelistCombination() - Fewer than 3 active profiles. Returning false.");
        return false; // All 3 positions must be filled
    }
    
    // Classify each panelist
    $types = array_map('classifyPanelistType', $panelists);
    error_log("DEBUG: isValidPanelistCombination() - Mapping complete. Combination patterns identified: " . implode(', ', $types));

    // Enforce that non-external panelist positions 1 and 2 come from same normalized program (or same college if program missing)
    $p1 = $panelists[0];
    $p2 = $panelists[1];
    if ($types[0] !== 'external' && $types[1] !== 'external') {
        error_log("DEBUG: isValidPanelistCombination() - Testing slot 1 & slot 2 affinity parameters.");
        $p1prog = $p1['normalized_program'] ?? null;
        $p2prog = $p2['normalized_program'] ?? null;
        
        if ($p1prog && $p2prog) {
            error_log("DEBUG: isValidPanelistCombination() - Testing program equality: '$p1prog' vs '$p2prog'");
            if ($p1prog !== $p2prog) {
                error_log("INVALID: panelist1 and panelist2 programs differ (" . ($p1prog ?: '-') . " vs " . ($p2prog ?: '-') . ")");
                return false;
            }
        } else {
            error_log("DEBUG: isValidPanelistCombination() - Missing programmatic strings. Falling back to track college alignment.");
            $p1col = $p1['program_college'] ?? null;
            $p2col = $p2['program_college'] ?? null;
            error_log("DEBUG: isValidPanelistCombination() - Testing college equality: '$p1col' vs '$p2col'");
            if ($p1col && $p2col && $p1col !== $p2col) {
                error_log("INVALID: panelist1 and panelist2 colleges differ (" . ($p1col ?: '-') . " vs " . ($p2col ?: '-') . ")");
                return false;
            }
        }
    }

    // Ensure panelist 3 is external OR from a different college than panelist1
    $p3 = $panelists[2];
    if (($p3['is_external'] ?? 0) != 1) {
        error_log("DEBUG: isValidPanelistCombination() - Slot 3 panelist is internal. Checking tracking metrics against slot 1.");
        $p3col = $p3['program_college'] ?? null;
        $p1col = $p1['program_college'] ?? null;
        if ($p3col && $p1col && $p3col === $p1col) {
            error_log("INVALID: panelist3 is not external and is from same college as panelist1 ($p3col)");
            return false;
        }
    }
    
    // Validate panelist_id3 (3rd position) must be external if external panelist is assigned
    if (isset($panelists[2]['is_external']) && $panelists[2]['is_external'] == 1) {
        error_log("DEBUG: isValidPanelistCombination() - Slot 3 verified as External. Checking slot 1 & 2 for leakage.");
        if ($types[0] === 'external' || $types[1] === 'external') {
            error_log("INVALID combination: external panelist mixed into 1st or 2nd assignment slot position");
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
    foreach ($allowedCombinations as $index => $allowed) {
        if ($types === $allowed) {
            error_log("DEBUG: isValidPanelistCombination() - Successful match discovered on index structure #$index (" . implode(', ', $allowed) . ")");
            return true;
        }
    }
    
    error_log("INVALID panelist combination schema blueprint: " . implode(", ", $types));
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
    error_log("DEBUG: Entering getExternalPanelists(). Exclusion list: " . implode(',', $excludePanelistIds));
    try {
        $placeholders = '';
        $params = [];
        
        if (!empty($excludePanelistIds)) {
            $placeholders = ' AND id NOT IN (' . implode(',', array_fill(0, count($excludePanelistIds), '?')) . ')';
            $params = $excludePanelistIds;
            error_log("DEBUG: getExternalPanelists() - Applied parameters for exclusions placeholder string: $placeholders");
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
        $ids = array_column($result, 'id') ?: [];
        error_log("DEBUG: Exiting getExternalPanelists(). Retreived IDs: " . implode(',', $ids));
        return $ids;
    } catch (Exception $e) {
        error_log("ERROR: getExternalPanelists error: " . $e->getMessage());
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
    error_log("DEBUG: Entering getFullTimePanelists(). Exclusion list: " . implode(',', $excludePanelistIds));
    try {
        $placeholders = '';
        $params = [];
        
        if (!empty($excludePanelistIds)) {
            $placeholders = ' AND id NOT IN (' . implode(',', array_fill(0, count($excludePanelistIds), '?')) . ')';
            $params = $excludePanelistIds;
            error_log("DEBUG: getFullTimePanelists() - Applied parameters for exclusions placeholder string: $placeholders");
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
        $ids = array_column($result, 'id') ?: [];
        error_log("DEBUG: Exiting getFullTimePanelists(). Retreived IDs: " . implode(',', $ids));
        return $ids;
    } catch (Exception $e) {
        error_log("ERROR: getFullTimePanelists error: " . $e->getMessage());
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
    error_log("DEBUG: Entering getPartTimePanelists(). Exclusion list: " . implode(',', $excludePanelistIds));
    try {
        $placeholders = '';
        $params = [];
        
        if (!empty($excludePanelistIds)) {
            $placeholders = ' AND id NOT IN (' . implode(',', array_fill(0, count($excludePanelistIds), '?')) . ')';
            $params = $excludePanelistIds;
            error_log("DEBUG: getPartTimePanelists() - Applied parameters for exclusions placeholder string: $placeholders");
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
        $ids = array_column($result, 'id') ?: [];
        error_log("DEBUG: Exiting getPartTimePanelists(). Retreived IDs: " . implode(',', $ids));
        return $ids;
    } catch (Exception $e) {
        error_log("ERROR: getPartTimePanelists error: " . $e->getMessage());
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
 * @param array $exclude Panelist IDs to filter out globally
 * @param string $studentProgram Program context matching constraints
 * @param string $studentCollege College context matching constraints
 * @return array|null [panelist_id, panelist_id2, panelist_id3] or null if no valid combo found
 */
function buildOptimalPanelistCombination($pdo, $adviserId = null, $exclude = [], $studentProgram = null, $studentCollege = null)
{
    error_log("DEBUG: Entering buildOptimalPanelistCombination(). Adviser: " . var_export($adviserId, true) . ", studentProgram: " . var_export($studentProgram, true) . ", studentCollege: " . var_export($studentCollege, true));
    try {
        // Build full exclude list
        $excludeList = $exclude;
        if ($adviserId) {
            $excludeList[] = $adviserId;
        }
        $excludeList = array_unique($excludeList);
        error_log("DEBUG: buildOptimalPanelistCombination() - Aggregated structural exceptions array list: " . implode(',', $excludeList));

        // Get available panelists by type, excluding those in $excludeList
        $fullTime   = getFullTimePanelists($pdo, $excludeList);
        $partTime   = getPartTimePanelists($pdo, $excludeList);
        $external   = getExternalPanelists($pdo, $excludeList);

        // If student program/college provided, force slots 1&2 to come from same program+college
        $useStudentMatch = $studentProgram || $studentCollege;
        $matchingCombined = [];
        
        if ($useStudentMatch) {
            error_log("DEBUG: buildOptimalPanelistCombination() - Evaluating constraints via Student affinity metrics mapping.");
            $studentProgram = $studentProgram ? normalizeProgramName($studentProgram) : null;
            
            // Build matching pools where BOTH normalized_program and program_college match student
            $matchingFull = [];
            foreach ($fullTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) {
                    error_log("DEBUG: buildOptimalPanelistCombination() - Missing tracking metrics for Full-time ID: $id. Skipping.");
                    continue;
                }
                if ($studentProgram && $info['normalized_program'] === $studentProgram &&
                    $studentCollege && $info['program_college'] === $studentCollege) {
                    $matchingFull[] = $id;
                }
            }
            error_log("DEBUG: buildOptimalPanelistCombination() - Valid Full-time profiles matching student profiles: " . implode(',', $matchingFull));

            $matchingPart = [];
            foreach ($partTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) {
                    error_log("DEBUG: buildOptimalPanelistCombination() - Missing tracking metrics for Part-time ID: $id. Skipping.");
                    continue;
                }
                if ($studentProgram && $info['normalized_program'] === $studentProgram &&
                    $studentCollege && $info['program_college'] === $studentCollege) {
                    $matchingPart[] = $id;
                }
            }
            error_log("DEBUG: buildOptimalPanelistCombination() - Valid Part-time profiles matching student profiles: " . implode(',', $matchingPart));

            // Combined matching pool must have at least two panelists (slots 1 & 2)
            $matchingCombined = array_values(array_unique(array_merge($matchingFull, $matchingPart)));
            error_log("DEBUG: buildOptimalPanelistCombination() - Combined processing target metrics pool: " . implode(',', $matchingCombined));
            
            if (count($matchingCombined) < 2) {
                error_log("ERROR: Not enough matching panelists in same program+college to fill slots 1 and 2");
                return null;
            }

            // Replace original pools but keep full/part distinctions for role selection
            $fullTime = $matchingFull;
            $partTime = $matchingPart;
            // Keep external pool unchanged for slot 3
        }

        // Shuffle each pool to get random selections (avoid always picking same IDs)
        error_log("DEBUG: buildOptimalPanelistCombination() - Executing dataset randomization shuffles.");
        shuffle($fullTime);
        shuffle($partTime);
        shuffle($external);

        // Defined allowed combinations (order: slot1, slot2, slot3)
        $randVal = mt_rand(1, 4);
        error_log("DEBUG: buildOptimalPanelistCombination() - Random design layout key generated: $randVal");
        
        $patterns = $randVal === 1 ? [
            ['full_time', 'part_time', 'external'],
        ] : [
            ['full_time', 'full_time', 'external'],
            ['full_time', 'full_time', 'part_time'],
            ['full_time', 'part_time', 'external'],
            ['full_time', 'part_time', 'part_time'],
        ];

        foreach ($patterns as $pIdx => $pattern) {
            error_log("DEBUG: buildOptimalPanelistCombination() - Evaluating pattern blueprint index #$pIdx: " . implode(', ', $pattern));
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
                error_log("DEBUG: buildOptimalPanelistCombination() - Processing Slot $i configuration target type: '$type'");
                
                if (empty($available[$type])) { 
                    error_log("DEBUG: buildOptimalPanelistCombination() - Pool configuration type '$type' is empty. Abandoning this pattern.");
                    $valid = false; 
                    break; 
                }

                $chosen = null;
                foreach ($available[$type] as $candidate) {
                    if (in_array($candidate, $used, true)) {
                        continue;
                    }
                    if ($useStudentMatch && ($i === 0 || $i === 1)) {
                        if (!in_array($candidate, $matchingCombined, true)) {
                            error_log("DEBUG: buildOptimalPanelistCombination() - Candidate ID $candidate failed affinity inclusion parameter tracking filter for slot $i.");
                            continue;
                        }
                    }
                    $chosen = $candidate;
                    error_log("DEBUG: buildOptimalPanelistCombination() - Candidate ID $chosen tentatively selected for Slot $i.");
                    break;
                }
                
                if ($chosen === null) { 
                    error_log("DEBUG: buildOptimalPanelistCombination() - No acceptable candidate found for position array slot $i. Pattern broken.");
                    $valid = false; 
                    break; 
                }

                $selected[$i] = $chosen;
                $used[] = $chosen;
                
                // remove chosen from all pools
                foreach ($available as $t => &$pool) {
                    $pool = array_values(array_diff($pool, [$chosen]));
                }
            }

            if ($valid && !in_array(null, $selected, true)) {
                error_log("DEBUG: buildOptimalPanelistCombination() - Candidate collection successful: " . implode(',', $selected) . ". Initiating validation constraints matching.");
                // Optional: final validation (should pass by construction)
                $infos = [
                    getPanelistInfo($pdo, $selected[0]),
                    getPanelistInfo($pdo, $selected[1]),
                    getPanelistInfo($pdo, $selected[2])
                ];
                if (isValidPanelistCombination($infos)) {
                    error_log("SUCCESS: Valid combination successfully built: " . implode(',', $selected));
                    return $selected;
                } else {
                    error_log("DEBUG: buildOptimalPanelistCombination() - Final structural validation logic returned failure for combination: " . implode(',', $selected));
                }
            }
        }

        error_log("ERROR: Could not build any valid panelist combination with available panelists (adviser=$adviserId)");
        return null;
    } catch (Exception $e) {
        error_log("ERROR: buildOptimalPanelistCombination error: " . $e->getMessage());
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
    error_log("DEBUG: Entering getPreferredExternalPanelist(). Exclusions: " . implode(',', $excludePanelistIds));
    $externals = getExternalPanelists($pdo, $excludePanelistIds);
    
    if (!empty($externals)) {
        $chosen = reset($externals);
        error_log("DEBUG: Exiting getPreferredExternalPanelist(). Top queue priority panelist selection: $chosen");
        return $chosen;
    }
    
    error_log("DEBUG: Exiting getPreferredExternalPanelist(). No structural profile match found. Returning null.");
    return null;
}