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
    // Per-request cache: getPanelistInfo is called repeatedly (once per candidate
    // panelist while building combinations, plus during final validation). Without
    // caching this ran 2 DB queries on every call and was the main scheduler hotspot.
    static $cache = [];

    $key = (int) $panelistId;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

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
            // Resolve the college for the normalized program (memoized per program name
            // so repeated panelists from the same program don't re-query).
            $row['program_college'] = getProgramCollege($pdo, $row['normalized_program']);
        }
        $cache[$key] = $row;
        return $row;
    } catch (Exception $e) {
        error_log("getPanelistInfo error: " . $e->getMessage());
        $cache[$key] = null;
        return null;
    }
}

/**
 * Resolve (and memoize) the college for a normalized program name.
 *
 * @param PDO $pdo
 * @param string|null $normalizedProgram
 * @return string|null
 */
function getProgramCollege($pdo, $normalizedProgram) {
    static $collegeCache = [];

    if (!is_string($normalizedProgram) || $normalizedProgram === '') {
        return null;
    }
    if (array_key_exists($normalizedProgram, $collegeCache)) {
        return $collegeCache[$normalizedProgram];
    }

    try {
        $pstmt = $pdo->prepare("SELECT college FROM programs WHERE name = ? LIMIT 1");
        $pstmt->execute([$normalizedProgram]);
        $prog = $pstmt->fetch(PDO::FETCH_ASSOC);
        $collegeCache[$normalizedProgram] = $prog ? $prog['college'] : null;
    } catch (Exception $e) {
        $collegeCache[$normalizedProgram] = null;
    }

    return $collegeCache[$normalizedProgram];
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
 * Does a panelist belong to the team's program (for eligibility as panelist 1 or 2)?
 *
 * Program comparison is specialization-insensitive: both sides are already normalized
 * (specialization after a dash stripped), so "Computer Science - Data Science" counts as
 * the same program as "Computer Science". College is only consulted as a fallback when the
 * team has no program name to match against.
 *
 * @param array $info Panelist info from getPanelistInfo()
 * @param string|null $teamProgram Normalized team program name
 * @param string|null $teamCollege Team college (fallback)
 * @return bool
 */
function panelistMatchesTeamProgram($info, $teamProgram, $teamCollege) {
    if (!is_array($info)) {
        return false;
    }
    if (!empty($teamProgram)) {
        return isset($info['normalized_program']) && $info['normalized_program'] === $teamProgram;
    }
    if (!empty($teamCollege)) {
        return isset($info['program_college']) && $info['program_college'] === $teamCollege;
    }
    return false;
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

    // Panelist 3 is the "external"/cross-program slot. It may be:
    //   - a truly external panelist (is_external = 1), or
    //   - a faculty member from a DIFFERENT program than the team/panelist 1
    //     (e.g. an Information Technology faculty serving on a Computer Science panel), or
    //   - a same-program part-time panelist (per the allowed employment combinations).
    // Comparison is specialization-insensitive (normalized program names). We no longer
    // reject a cross-program 3rd panelist just because they share a college with panelist 1.
    $p3 = $panelists[2];
    if (($p3['is_external'] ?? 0) != 1) {
        $p3prog = $p3['normalized_program'] ?? null;
        $p1prog = $p1['normalized_program'] ?? null;
        if ($p3prog && $p1prog && $p3prog !== $p1prog) {
            error_log("panelist3 is a cross-program reviewer ({$p3prog} vs {$p1prog}) — allowed");
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
    return filterPanelistPool(loadPanelistPools($pdo)['external'], $excludePanelistIds);
}

/**
 * Load all panelist IDs grouped by type in a single query and memoize for the request.
 *
 * Previously getExternal/FullTime/PartTimePanelists each ran their own query on every
 * call (and were called per team). Now we fetch once and filter exclusions in PHP.
 *
 * @param PDO $pdo
 * @return array{full_time:int[], part_time:int[], external:int[]}
 */
function loadPanelistPools($pdo) {
    static $pools = null;
    if ($pools !== null) {
        return $pools;
    }

    $pools = ['full_time' => [], 'part_time' => [], 'external' => []];
    try {
        $stmt = $pdo->query("
            SELECT id, is_parttime, is_external
            FROM users
            WHERE usertype = 2 AND deleted_at IS NULL
            ORDER BY first_name, last_name
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = (int) $row['id'];
            if ((int) $row['is_external'] === 1) {
                $pools['external'][] = $id;
            } elseif ((int) $row['is_parttime'] === 1) {
                $pools['part_time'][] = $id;
            } else {
                $pools['full_time'][] = $id;
            }
        }
    } catch (Exception $e) {
        error_log("loadPanelistPools error: " . $e->getMessage());
    }

    return $pools;
}

/**
 * Return pool IDs with the excluded IDs removed.
 *
 * @param int[] $pool
 * @param int[] $excludePanelistIds
 * @return int[]
 */
function filterPanelistPool(array $pool, array $excludePanelistIds) {
    if (empty($excludePanelistIds)) {
        return array_values($pool);
    }
    $exclude = array_flip(array_map('intval', $excludePanelistIds));
    return array_values(array_filter($pool, function ($id) use ($exclude) {
        return !isset($exclude[(int) $id]);
    }));
}

/**
 * Get full-time panelists available for assignment
 * 
 * @param PDO $pdo Database connection
 * @param array $excludePanelistIds Panelist IDs to exclude from results
 * @return array List of full-time panelist IDs
 */
function getFullTimePanelists($pdo, $excludePanelistIds = []) {
    return filterPanelistPool(loadPanelistPools($pdo)['full_time'], $excludePanelistIds);
}

/**
 * Get part-time panelists available for assignment
 * 
 * @param PDO $pdo Database connection
 * @param array $excludePanelistIds Panelist IDs to exclude from results
 * @return array List of part-time panelist IDs
 */
function getPartTimePanelists($pdo, $excludePanelistIds = []) {
    return filterPanelistPool(loadPanelistPools($pdo)['part_time'], $excludePanelistIds);
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

        // If student/team program (or college) provided, force slots 1 & 2 to come from the
        // SAME program as the team. Matching is specialization-insensitive: the program name
        // is normalized first, so e.g. "Computer Science - Data Science" matches "Computer
        // Science". College is used only as a fallback when no program name is available.
        // This guarantees, for example, that an Information Technology faculty/program chair
        // is NOT eligible as panelist 1 or 2 for a Computer Science section (they may still
        // serve as the 3rd / external/cross-program panelist).
        $useStudentMatch = $studentProgram || $studentCollege;
        if ($useStudentMatch) {
            $studentProgram = $studentProgram ? normalizeProgramName($studentProgram) : null;

            $matchingFull = [];
            foreach ($fullTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if (panelistMatchesTeamProgram($info, $studentProgram, $studentCollege)) {
                    $matchingFull[] = $id;
                }
            }
            $matchingPart = [];
            foreach ($partTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if (panelistMatchesTeamProgram($info, $studentProgram, $studentCollege)) {
                    $matchingPart[] = $id;
                }
            }

            // Combined matching pool must have at least two panelists (slots 1 & 2)
            $matchingCombined = array_values(array_unique(array_merge($matchingFull, $matchingPart)));
            if (count($matchingCombined) < 2) {
                error_log("Not enough same-program panelists to fill slots 1 and 2 (program=" . ($studentProgram ?: '-') . ", college=" . ($studentCollege ?: '-') . ")");
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
        // Prefer the external 3rd slot most of the time, but still keep a small
        // chance of the other valid employment mixes.
        $patterns = mt_rand(1, 100) <= 75 ? [
            ['full_time', 'full_time', 'external'],
        ] : [
            ['full_time', 'part_time', 'external'],
            ['full_time', 'full_time', 'part_time'],
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