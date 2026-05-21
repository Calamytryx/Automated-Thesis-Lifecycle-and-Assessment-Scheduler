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
    $key = (int) $panelistId;

    // Eager-load: the first lookup primes the shared per-request cache for ALL panelists
    // in one bulk query (loadPanelistPools), collapsing what used to be one query per
    // distinct panelist into a single round-trip. Subsequent lookups are pure cache hits.
    $cache =& panelistInfoCache($pdo);
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    // Fallback for any panelist not present in the bulk load (should be rare).
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
            WHERE id = ? AND usertype IN (0, 2) AND id != 0 AND deleted_at IS NULL
        ");
        $stmt->execute([$panelistId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($row) {
            $row['normalized_program'] = normalizeProgramName($row['program']);
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
 * Shared per-request panelist-info cache, eagerly primed by loadPanelistPools().
 *
 * Returns a reference to the static cache array so getPanelistInfo() and loadPanelistPools()
 * read/write the same store. Triggers the one-time bulk load on first access.
 *
 * @param PDO $pdo
 * @return array Reference to the id => info-row (or null) cache
 */
function &panelistInfoCache($pdo) {
    static $cache = [];
    static $primed = false;
    if (!$primed) {
        $primed = true; // set before priming to avoid re-entrancy from loadPanelistPools
        loadPanelistPools($pdo);
    }
    return $cache;
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

    // Panelists 1 & 2 must share AT LEAST a college. This holds regardless of employment type
    // OR external status: same-program faculty are eligible for these seats whether they are
    // internal, part-time, an external specialist, or the program chair. Identical normalized
    // programs pass; differing programs pass only when their colleges match (panelist-2
    // same-college conflict fallback).
    $p1 = $panelists[0];
    $p2 = $panelists[1];
    $p1prog = $p1['normalized_program'] ?? null;
    $p2prog = $p2['normalized_program'] ?? null;
    if ($p1prog && $p2prog && $p1prog === $p2prog) {
        // Same program — ideal case, nothing to relax.
    } else {
        $p1col = $p1['program_college'] ?? null;
        $p2col = $p2['program_college'] ?? null;
        if (!$p1col || !$p2col || $p1col !== $p2col) {
            error_log("Invalid: panelist1 and panelist2 are neither same program nor same college (" . ($p1prog ?: '-') . "/" . ($p1col ?: '-') . " vs " . ($p2prog ?: '-') . "/" . ($p2col ?: '-') . ")");
            return false;
        }
        error_log("panelist2 is same-college / different-program (conflict fallback) — allowed");
    }

    // Panelist 3 is the flexible External/Validator seat: any employment type, any program or
    // college is acceptable (the selection waterfall ranks the preference). Once slots 1 & 2
    // satisfy the program/college rule above, the combination is valid.
    return true;
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
 * Eagerly load all panelists in a single query: group IDs by employment type AND prime the
 * shared per-request info cache (program + college enrichment) so getPanelistInfo() never has
 * to query per panelist. This is the query-layer optimization for the panel-selection matrix.
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
    // Share the same store getPanelistInfo() reads from. panelistInfoCache() has already
    // flagged itself primed before calling us, so this returns the reference without recursing.
    $infoCache =& panelistInfoCache($pdo);
    try {
        // usertype 2 = faculty panelists, usertype 0 = program chairs (also eligible to serve
        // on panels). id 0 is the reserved system account and is excluded.
        $stmt = $pdo->query("
            SELECT id, usertype, is_parttime, is_external, first_name, last_name, username, program
            FROM users
            WHERE usertype IN (0, 2) AND id != 0 AND deleted_at IS NULL
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

            // Enrich and cache so getPanelistInfo($id) is a pure cache hit.
            $row['normalized_program'] = normalizeProgramName($row['program']);
            $row['program_college'] = getProgramCollege($pdo, $row['normalized_program']);
            $infoCache[$id] = $row;
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
 * Deterministic feasibility check: can a compliant 3-member panel be formed for this team?
 *
 * Mirrors the hard constraints of buildOptimalPanelistCombination() without the randomness,
 * so the pre-flight gate can decide up front whether a team's slots should be offered:
 *   - slots 1 & 2 require >= 2 available SAME-program internals (same-college fallback when
 *     no program name is supplied), and
 *   - slot 3 requires >= 1 additional available candidate (any tier of the waterfall).
 *
 * @param PDO $pdo Database connection
 * @param int|null $adviserId Adviser to exclude
 * @param int[] $exclude Other panelist IDs to exclude (e.g. locked elsewhere)
 * @param string|null $teamProgram Team program (raw or normalized)
 * @param string|null $teamCollege Team college
 * @return bool True if a compliant panel can be formed
 */
function canFormCompliantPanel($pdo, $adviserId = null, $exclude = [], $teamProgram = null, $teamCollege = null) {
    $excludeList = $exclude;
    if ($adviserId) {
        $excludeList[] = $adviserId;
    }
    $excludeList = array_unique($excludeList);

    $fullTime = getFullTimePanelists($pdo, $excludeList);
    $partTime = getPartTimePanelists($pdo, $excludeList);
    $external = getExternalPanelists($pdo, $excludeList);

    $teamProgramNorm = $teamProgram ? normalizeProgramName($teamProgram) : null;
    $teamCollegeResolved = $teamCollege ?: ($teamProgramNorm ? getProgramCollege($pdo, $teamProgramNorm) : null);

    // Slot 1 is ALWAYS a same-program internal. Slot 2 prefers a same-program internal but,
    // when a scheduling conflict leaves only one same-program panelist available, may fall
    // back to a same-COLLEGE (different program) internal. Feasibility therefore requires:
    //   - >= 1 same-program internal (slot 1), and
    //   - >= 2 same-college internals total (slots 1 & 2 combined; same-program counts here too).
    // Same-program EXTERNALS are eligible for slots 1 & 2 too, so include the external pool when
    // counting (mirrors buildOptimalPanelistCombination's slot-1/2 pool).
    $sameProgramSlot12 = [];
    $sameCollegeSlot12 = [];
    foreach (array_merge($fullTime, $partTime, $external) as $id) {
        $info = getPanelistInfo($pdo, $id);
        if (!$info) continue;
        if (panelistMatchesTeamProgram($info, $teamProgramNorm, $teamCollege)) {
            $sameProgramSlot12[$id] = true;
            $sameCollegeSlot12[$id] = true;
        } elseif ($teamCollegeResolved && ($info['program_college'] ?? null) === $teamCollegeResolved) {
            $sameCollegeSlot12[$id] = true;
        }
    }
    if (count($sameProgramSlot12) < 1 || count($sameCollegeSlot12) < 2) {
        return false;
    }

    // Slot 3: at least one more candidate (any tier) beyond the two internals.
    $allCandidates = array_unique(array_merge($fullTime, $partTime, $external));
    return count($allCandidates) >= 3;
}

/**
 * Order candidates for slot 3 (the External / Validator seat) by the selection waterfall.
 *
 * NOTE: Panelists 1 & 2 are ALWAYS internal subject-matter experts from the SAME program
 * and SAME college as the team. This function only ranks the 3rd seat.
 *
 * Waterfall (most preferred first):
 *   Tier 1: external specialist (is_external = 1) in the SAME program as the team
 *   Tier 2: any other panelist in the SAME program (is_external = 0)
 *   Tier 3: different program but SAME college
 *   Tier 4: entirely different college (last resort, lets the defense proceed)
 *
 * @param PDO $pdo Database connection
 * @param int[] $candidateIds All available panelist IDs to rank (full-time + part-time + external)
 * @param string|null $teamProgram Normalized team program name
 * @param string|null $teamCollege Team college
 * @param int[] $excludePanelistIds Panelist IDs already used / to skip (e.g. slots 1 & 2, adviser)
 * @return int[] Candidate IDs ordered by tier (Tier 1 first)
 */
function getExternalPanelistsWithPriority($pdo, $candidateIds = [], $teamProgram = null, $teamCollege = null, $excludePanelistIds = []) {
    if (empty($candidateIds)) {
        return [];
    }

    $exclude = array_flip(array_map('intval', $excludePanelistIds));

    $tier1 = []; // same program + external specialist
    $tier2 = []; // same program + non-external
    $tier3 = []; // same college, different program
    $tier4 = []; // different college (or unknown)

    foreach ($candidateIds as $id) {
        $id = (int) $id;
        if (isset($exclude[$id])) {
            continue;
        }
        $info = getPanelistInfo($pdo, $id);
        if (!$info) {
            continue;
        }

        $prog = $info['normalized_program'] ?? null;
        $coll = $info['program_college'] ?? null;
        $isExternal = ((int) ($info['is_external'] ?? 0)) === 1;

        if ($teamProgram && $prog && $prog === $teamProgram) {
            if ($isExternal) {
                $tier1[] = $id; // Priority 1
            } else {
                $tier2[] = $id; // Priority 2
            }
        } elseif ($teamCollege && $coll && $coll === $teamCollege) {
            $tier3[] = $id; // Priority 3
        } else {
            $tier4[] = $id; // Priority 4
        }
    }

    return array_values(array_merge($tier1, $tier2, $tier3, $tier4));
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
 * Panelist 1 is ALWAYS a same-program internal. Panelist 2 prefers the same program but,
 * on a scheduling conflict (no second same-program faculty available), falls back to a
 * same-college / different-program internal (never a different college).
 * Slot 3 (External/Validator seat) selection waterfall:
 * 1. Same-program external specialist (is_external = 1)
 * 2. Same-program internal (is_external = 0)
 * 3. Same college, different program
 * 4. Different college (last resort)
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

        // Normalized team program for the slot-3 waterfall (Tier 1/2 are same-program seats).
        $teamProgramNorm = $studentProgram ? normalizeProgramName($studentProgram) : null;

        // Full candidate pool for slot 3 (the External/Validator seat): every available
        // panelist regardless of employment type. Captured BEFORE the same-program filter
        // below so slot 3 can fall through the waterfall (same program -> same college ->
        // different college). Slots 1 & 2 are still locked to the same-program pool.
        $allSlot3Candidates = array_values(array_unique(array_merge($fullTime, $partTime, $external)));
        // Shuffle so the slot-3 pick varies WITHIN a waterfall tier. Without this the pool stays
        // in DB order (ORDER BY first_name) and getExternalPanelistsWithPriority preserves input
        // order per tier, so the alphabetically-first same-program external was always chosen
        // (e.g. only "Earl Saavedra" ever picked among several IT externals).
        shuffle($allSlot3Candidates);

        // If student/team program (or college) provided, force slots 1 & 2 to come from the
        // SAME program as the team. Matching is specialization-insensitive: the program name
        // is normalized first, so e.g. "Computer Science - Data Science" matches "Computer
        // Science". College is used only as a fallback when no program name is available.
        // This guarantees, for example, that an Information Technology faculty/program chair
        // is NOT eligible as panelist 1 or 2 for a Computer Science section (they may still
        // serve as the 3rd / external/cross-program panelist).
        $useStudentMatch = $studentProgram || $studentCollege;
        // Same-college / different-program fallback pools for panelist 2 ONLY. Panelist 1 is
        // always a same-program internal; panelist 2 prefers the same program but, when a
        // scheduling conflict (e.g. the only other same-program faculty is already booked and
        // thus excluded) leaves no second same-program panelist, falls back to a same-college /
        // different-program internal — never a different college.
        $fallbackFull = [];
        $fallbackPart = [];
        if ($useStudentMatch) {
            $studentProgram = $teamProgramNorm;
            $teamCollegeResolved = $studentCollege ?: ($teamProgramNorm ? getProgramCollege($pdo, $teamProgramNorm) : null);

            $matchingFull = [];
            foreach ($fullTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if (panelistMatchesTeamProgram($info, $studentProgram, $studentCollege)) {
                    $matchingFull[] = $id;
                } elseif ($teamCollegeResolved && ($info['program_college'] ?? null) === $teamCollegeResolved) {
                    $fallbackFull[] = $id;
                }
            }
            $matchingPart = [];
            foreach ($partTime as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if (panelistMatchesTeamProgram($info, $studentProgram, $studentCollege)) {
                    $matchingPart[] = $id;
                } elseif ($teamCollegeResolved && ($info['program_college'] ?? null) === $teamCollegeResolved) {
                    $fallbackPart[] = $id;
                }
            }
            // Same-program EXTERNALS are also eligible for slots 1 & 2 (treated as internal here)
            // so seats rotate when there are few internal faculty. They are appended AFTER the
            // internals (see $internalPool below) so internals are preferred and externals only
            // fill slots 1/2 when needed.
            $matchingExternal = [];
            foreach ($external as $id) {
                $info = getPanelistInfo($pdo, $id);
                if (!$info) continue;
                if (panelistMatchesTeamProgram($info, $studentProgram, $studentCollege)) {
                    $matchingExternal[] = $id;
                }
            }

            // Slot 1 ALWAYS needs a same-program panelist, so require at least one. Slot 2 may
            // use a second same-program panelist OR (on conflict) a same-college fallback, so the
            // same-program + same-college pools together must supply at least two candidates.
            $matchingInternals = array_values(array_unique(array_merge($matchingFull, $matchingPart)));
            $matchingCombined = array_values(array_unique(array_merge($matchingInternals, $matchingExternal)));
            $sameCollegeCount = count($matchingCombined) + count($fallbackFull) + count($fallbackPart);
            if (count($matchingCombined) < 1 || $sameCollegeCount < 2) {
                error_log("Not enough panelists for slots 1 & 2: need 1 same-program + 1 same-college (program=" . ($studentProgram ?: '-') . ", college=" . ($teamCollegeResolved ?: '-') . ")");
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

        // Slots 1 & 2 draw from ONE same-program pool, full-time and part-time treated equally
        // so panelist 1 isn't forced to be the lone full-timer (e.g. always "Amanda Menta").
        // Internals (incl. program chairs) come first; same-program externals are appended so
        // they only fill slots 1/2 when there aren't enough internals. Each segment is shuffled
        // independently to rotate within it.
        $internalPool = [];
        if ($useStudentMatch) {
            $internalsShuffled = $matchingInternals;
            shuffle($internalsShuffled);
            $externalsShuffled = $matchingExternal;
            shuffle($externalsShuffled);
            $internalPool = array_values(array_unique(array_merge($internalsShuffled, $externalsShuffled)));
        }
        $fallbackCombined = array_merge($fallbackFull, $fallbackPart);
        shuffle($fallbackCombined);

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
                // Slot 1 must have a same-program candidate available. Slot 3 uses the
                // external-priority pool and slot 2 may fall back to the same-college pool, so
                // only slot 1's empty pool is an immediate failure here. When matching on the
                // team program, slots 1 & 2 use the employment-agnostic $internalPool.
                $slot01Pool = $useStudentMatch ? $internalPool : $available[$type];
                if ($i === 0 && empty($slot01Pool)) { $valid = false; break; }

                $chosen = null;

                if ($i === 2) {
                    // Slot 3 is the External/Validator seat. Rank ALL available panelists by
                    // the waterfall: Tier 1 same-program external -> Tier 2 same-program
                    // internal -> Tier 3 same college different program -> Tier 4 different
                    // college. Use the team program (slots 1 & 2 share it) for tiering.
                    $prioritized = getExternalPanelistsWithPriority(
                        $pdo,
                        $allSlot3Candidates,
                        $teamProgramNorm,
                        $studentCollege,
                        $used
                    );

                    foreach ($prioritized as $candidate) {
                        if (!in_array($candidate, $used, true)) {
                            $chosen = $candidate;
                            break;
                        }
                    }
                } else {
                    // Slots 1 & 2 come from the same-program pool, full-time and part-time
                    // treated equally (any same-program faculty may be panelist 1 or 2).
                    foreach ($slot01Pool as $candidate) {
                        if (in_array($candidate, $used, true)) continue;
                        if ($useStudentMatch && !in_array($candidate, $matchingCombined, true)) continue;
                        $chosen = $candidate;
                        break;
                    }

                    // Conflict fallback for panelist 2 ONLY: no same-program candidate remains
                    // (e.g. the other same-program faculty is booked and excluded), so draw a
                    // same-college / different-program internal instead. Panelist 1 (i === 0) is
                    // never relaxed this way.
                    if ($chosen === null && $i === 1 && $useStudentMatch) {
                        foreach ($fallbackCombined as $candidate) {
                            if (in_array($candidate, $used, true)) continue;
                            $chosen = $candidate;
                            break;
                        }
                        if ($chosen !== null) {
                            error_log("panelist2 conflict fallback: using same-college / different-program candidate $chosen");
                        }
                    }
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