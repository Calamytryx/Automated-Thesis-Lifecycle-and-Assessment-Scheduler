<?php

session_start(); // Required for access control

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
ob_start(); // Buffer any stray output to protect JSON response

// Increase PHP timeout for long-running scheduler
set_time_limit(600); // 10 minutes
error_log("=== SCHEDULER START === Execution time limit set to 600 seconds");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

// Release the PHP session lock early so inactivity/session polls do not block
// behind this long-running request.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// If the POST submission contains 'startTime', ignore this submission.
if (isset($_POST['startTime'])) {
    error_log("Ignoring submission with startTime. Expected submission without startTime.");
    echo json_encode(['success' => false, 'message' => 'Ignoring unintended submission']);
    exit;
}

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../assets/setup/db.inc.php';
    require_once __DIR__ . '/../includes/edit_functions.php';
    require_once __DIR__ . '/../includes/defense_type_functions.php'; // Add defense type helper

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection error');
    }

    // Progress tracking function
    function updateProgress($pdo, $progressId, $status, $message, $percentage = null) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO schedule_progress (id, status, message, percentage) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                message = VALUES(message), 
                percentage = VALUES(percentage),
                updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$progressId, $status, $message, $percentage]);
        } catch (Exception $e) {
            error_log("Progress update failed: " . $e->getMessage());
        }
    }

    function normalizeValidationMode($mode)
    {
        $mode = strtolower(trim((string)$mode));
        $allowedModes = ['strict', 'soft', 'hybrid'];
        return in_array($mode, $allowedModes, true) ? $mode : 'hybrid';
    }

    function parseTimeRange($day, $timeSlot, $duration)
    {
        $r = scheduler_defense_range_on_day(trim((string) $day), trim((string) $timeSlot), $duration);
        if ($r === null) {
            return null;
        }

        $dayNorm = scheduler_calendar_day_from_raw((string) $day);
        $dStr = $dayNorm ?? trim((string) $day);

        return [
            'day' => $dStr,
            'start' => $r['start'],
            'end' => $r['end'],
            'day_of_week' => date('w', strtotime($dStr)),
        ];
    }

    function slotRangesOverlap($startA, $endA, $startB, $endB)
    {
        return ($startA < $endB) && ($endA > $startB);
    }

    function schedulerClassRowAllowsOverlap(array $scheduleRow): bool
    {
        $allowOverlap = (int) ($scheduleRow['allow_overlap'] ?? 0);
        $isResearchClass = (int) ($scheduleRow['is_research_class'] ?? 0);
        return $allowOverlap === 1 || $isResearchClass === 1;
    }

    function schedulerClassRowDescriptor(array $scheduleRow): string
    {
        $className = trim((string) ($scheduleRow['class_name'] ?? 'Class'));
        $room = trim((string) ($scheduleRow['room'] ?? ''));
        $start = trim((string) ($scheduleRow['start_time'] ?? ''));
        $end = trim((string) ($scheduleRow['end_time'] ?? ''));
        $parts = [$className !== '' ? $className : 'Class'];
        if ($start !== '' || $end !== '') {
            $parts[] = trim($start . '-' . $end, '-');
        }
        if ($room !== '') {
            $parts[] = 'room ' . $room;
        }
        if (schedulerClassRowAllowsOverlap($scheduleRow)) {
            $parts[] = '(overlap-exempt)';
        }

        return implode(', ', $parts);
    }

    function userSchedulesHasOverlapExceptionColumns($pdo)
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [
            'allow_overlap' => false,
            'is_research_class' => false,
        ];

        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM user_schedules WHERE Field IN ('allow_overlap','is_research_class')");
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $cache['allow_overlap'] = in_array('allow_overlap', $cols, true);
            $cache['is_research_class'] = in_array('is_research_class', $cols, true);
        } catch (Exception $e) {
            error_log('userSchedulesHasOverlapExceptionColumns: ' . $e->getMessage());
        }

        return $cache;
    }

    function fetchExistingDefenseSchedules($pdo)
    {
        $stmt = $pdo->query("SELECT team_id, room, schedule_date AS day, start_time, end_time FROM defense_schedules WHERE status = 'scheduled'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function buildRoomOccupancyMap($userSchedules)
    {
        $roomMap = [];

        foreach ($userSchedules as $scheduleList) {
            foreach ($scheduleList as $schedule) {
                if (schedulerClassRowAllowsOverlap($schedule)) {
                    continue;
                }

                if (empty($schedule['room'])) {
                    continue;
                }

                $room = $schedule['room'];
                $day = $schedule['day_of_week'];
                $rawStart = trim((string)($schedule['start_time'] ?? ''));
                $rawEnd = trim((string)($schedule['end_time'] ?? ''));
                if ($rawStart === '' || $rawEnd === '') {
                    continue;
                }

                // Avoid adding duplicate identical scheduling blocks for the same room/day.
                // Program/section templates may add the same class for many users; dedupe here.
                $entry = [
                    'start_time' => $rawStart,
                    'end_time' => $rawEnd,
                    'class_name' => $schedule['class_name'] ?? null,
                ];

                $roomMap[$room][$day] = $roomMap[$room][$day] ?? [];
                $isDup = false;
                foreach ($roomMap[$room][$day] as $existing) {
                    if (isset($existing['start_time'], $existing['end_time']) && $existing['start_time'] === $entry['start_time'] && $existing['end_time'] === $entry['end_time'] && ((string)($existing['class_name'] ?? '') === (string)($entry['class_name'] ?? ''))) {
                        $isDup = true;
                        break;
                    }
                }

                if (!$isDup) {
                    $roomMap[$room][$day][] = $entry;

                    // Diagnostic logging: if multiple identical time blocks appear
                    // for the same room/day, log details to help track duplication sources.
                    if (count($roomMap[$room][$day]) > 1) {
                        static $dupLogged = [];
                        $key = $room . '|' . $day . '|' . $entry['start_time'] . '|' . $entry['end_time'] . '|' . (string)($entry['class_name'] ?? '');
                        if (empty($dupLogged[$key])) {
                            $dupLogged[$key] = true;
                            error_log(sprintf(
                                "Duplicate room occupancy detected: room=%s day=%s start=%s end=%s class=%s current_count=%d",
                                $room,
                                $day,
                                $entry['start_time'],
                                $entry['end_time'],
                                $entry['class_name'] ?? '',
                                count($roomMap[$room][$day])
                            ));
                        }
                    }
                }
            }
        }

        // Diagnostic: log the final occupancy map structure
        error_log("=== ROOM OCCUPANCY MAP SUMMARY ===");
        foreach ($roomMap as $room => $dayMap) {
            foreach ($dayMap as $day => $entries) {
                foreach ($entries as $entry) {
                    error_log(sprintf(
                        "Room Occupancy: room=%s day=%d start=%s end=%s class=%s",
                        $room,
                        $day,
                        $entry['start_time'],
                        $entry['end_time'],
                        $entry['class_name'] ?? 'UNKNOWN'
                    ));
                }
            }
        }
        error_log("=== END OCCUPANCY MAP ===");

        return $roomMap;
    }

    /**
     * Augment occupancy map with schedule rows for specified teams.
     * Fetches section template and personal schedules directly from DB (like preview overlay does)
     * to ensure all applicable classes are included in conflict checking.
     */
    function augmentOccupancyMapWithTeamSchedules($pdo, &$roomMap, $teamIds)
    {
        if (empty($teamIds)) {
            return;
        }

        error_log("AUGMENTING OCCUPANCY MAP for teams: " . implode(', ', $teamIds));

        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

        // Fetch section template classes
        $sqlSection = "
            SELECT DISTINCT
                us.day_of_week,
                us.start_time,
                us.end_time,
                us.room,
                us.class_name,
                COALESCE(us.allow_overlap, 0) AS allow_overlap,
                COALESCE(us.is_research_class, 0) AS is_research_class
            FROM team_members tm
            JOIN users u_student ON u_student.id = tm.user_id AND u_student.usertype = 1
            LEFT JOIN programs p_student ON (
                u_student.program = p_student.name OR
                u_student.program = CONCAT(
                    p_student.name,
                    CASE
                        WHEN p_student.specialization IS NOT NULL AND p_student.specialization != ''
                        THEN CONCAT(' - ', p_student.specialization)
                        ELSE ''
                    END
                )
            )
            INNER JOIN user_schedules us ON us.program = p_student.id
                AND TRIM(us.section) = TRIM(u_student.section)
            WHERE tm.team_id IN ($placeholders)
              AND u_student.section IS NOT NULL
              AND TRIM(u_student.section) <> ''
              AND p_student.id IS NOT NULL
            LIMIT 2000
        ";

        try {
            $stmt = $pdo->prepare($sqlSection);
            $stmt->execute($teamIds);
            $sectionRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("AUGMENT: Found " . count($sectionRows) . " section template rows");
        } catch (Exception $e) {
            error_log("AUGMENT section classes error: " . $e->getMessage());
            $sectionRows = [];
        }

        // Fetch personal schedules
        $sqlPersonal = "
            SELECT DISTINCT
                us.day_of_week,
                us.start_time,
                us.end_time,
                us.room,
                us.class_name,
                COALESCE(us.allow_overlap, 0) AS allow_overlap,
                COALESCE(us.is_research_class, 0) AS is_research_class
            FROM team_members tm
            JOIN users u_student ON u_student.id = tm.user_id AND u_student.usertype = 1
            INNER JOIN user_schedules us ON us.user_id = u_student.id
            WHERE tm.team_id IN ($placeholders)
            LIMIT 2000
        ";

        try {
            $stmt = $pdo->prepare($sqlPersonal);
            $stmt->execute($teamIds);
            $personalRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("AUGMENT: Found " . count($personalRows) . " personal schedule rows");
        } catch (Exception $e) {
            error_log("AUGMENT personal classes error: " . $e->getMessage());
            $personalRows = [];
        }

        $allRows = array_merge($sectionRows, $personalRows);

        // Add to occupancy map
        foreach ($allRows as $row) {
            if (schedulerClassRowAllowsOverlap($row)) {
                continue;
            }

            if (empty($row['room'])) {
                continue;
            }

            $dowNorm = normalize_user_schedule_day_to_week_int($row['day_of_week'] ?? '');
            if ($dowNorm === null) {
                continue;
            }

            $room = $row['room'];
            $day = $dowNorm;
            $rawStart = trim((string)($row['start_time'] ?? ''));
            $rawEnd = trim((string)($row['end_time'] ?? ''));
            if ($rawStart === '' || $rawEnd === '') {
                continue;
            }

            $entry = [
                'start_time' => $rawStart,
                'end_time' => $rawEnd,
                'class_name' => $row['class_name'] ?? null,
            ];

            $roomMap[$room] = $roomMap[$room] ?? [];
            $roomMap[$room][$day] = $roomMap[$room][$day] ?? [];

            // Check for duplicates
            $isDup = false;
            foreach ($roomMap[$room][$day] as $existing) {
                if (isset($existing['start'], $existing['end']) && 
                    $existing['start'] === $entry['start'] && 
                    $existing['end'] === $entry['end'] && 
                    ((string)($existing['class_name'] ?? '') === (string)($entry['class_name'] ?? ''))) {
                    $isDup = true;
                    break;
                }
            }

            if (!$isDup) {
                $roomMap[$room][$day][] = $entry;
                error_log(sprintf(
                    "AUGMENT ADDED: room=%s day=%d time=%s-%s class=%s",
                    $room,
                    $day,
                    date('H:i', $start),
                    date('H:i', $end),
                    $row['class_name'] ?? 'UNKNOWN'
                ));
            }
        }

        error_log("AUGMENT COMPLETE: occupancy map now has " . count($roomMap) . " rooms");
    }

    function validateCandidateSlot($pdo, $defense, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration)
    {
        $conflicts = [];
        $dayValue = is_array($defense['day'] ?? null) ? ($defense['day'][0] ?? '') : ($defense['day'] ?? '');
        $timeValue = is_array($defense['time_slot'] ?? null) ? ($defense['time_slot'][0] ?? '') : ($defense['time_slot'] ?? '');
        $roomValue = is_array($defense['room'] ?? null) ? ($defense['room'][0] ?? '') : ($defense['room'] ?? '');
        $panelistIds = $defense['panelist_ids'] ?? [];
        if (!is_array($panelistIds)) {
            $panelistIds = [$panelistIds];
        }

        $range = parseTimeRange($dayValue, $timeValue, $duration);

        if ($range === null) {
            return ['Invalid day or time slot format'];
        }

        $defenseStart = $range['start'];
        $defenseEnd = $range['end'];
        $defenseDay = $range['day'];
        $defenseDayOfWeek = $range['day_of_week'];
        $defenseWindow = date('H:i', $defenseStart) . '-' . date('H:i', $defenseEnd);

        // Debug logging for room check
        if ($roomValue !== '') {
            if (isset($roomOccupancyMap[$roomValue][$defenseDayOfWeek])) {
                error_log(sprintf(
                    "Room occupancy check: room=%s day=%d time=%s has %d entries",
                    $roomValue,
                    $defenseDayOfWeek,
                    $defenseWindow,
                    count($roomOccupancyMap[$roomValue][$defenseDayOfWeek])
                ));
            } else {
                error_log(sprintf(
                    "Room occupancy check: room=%s day=%d time=%s - NO ENTRIES IN MAP",
                    $roomValue,
                    $defenseDayOfWeek,
                    $defenseWindow
                ));
            }
        }

        if ($roomValue !== '' && isset($roomOccupancyMap[$roomValue][$defenseDayOfWeek])) {
            foreach ($roomOccupancyMap[$roomValue][$defenseDayOfWeek] as $occupied) {
                $occStart = scheduler_unix_on_calendar_day($defenseDay, $occupied['start_time'] ?? '');
                $occEnd = scheduler_unix_on_calendar_day($defenseDay, $occupied['end_time'] ?? '');
                if ($occStart === false || $occEnd === false) {
                    continue;
                }
                if (slotRangesOverlap($defenseStart, $defenseEnd, $occStart, $occEnd)) {
                    $classStart = date('H:i', $occStart);
                    $classEnd = date('H:i', $occEnd);
                    $className = trim((string) ($occupied['class_name'] ?? 'Class'));
                    $conflicts[] = "Room schedule conflict: room {$roomValue} occupied by {$className} ({$classStart}-{$classEnd}) on {$defenseDay}, candidate {$defenseWindow}";
                    break;
                }
            }
        }

        foreach ($panelistIds as $panelistId) {
            if (is_array($panelistId)) {
                continue;
            }
            if (!isset($userSchedules[$panelistId])) {
                continue;
            }

            foreach ($userSchedules[$panelistId] as $schedule) {
                if (schedulerClassRowAllowsOverlap($schedule)) {
                    continue;
                }

                if ((int)$schedule['day_of_week'] !== (int)$defenseDayOfWeek) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($defenseDay, $schedule);
                if ($sch === null) {
                    continue;
                }

                if (slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end'])) {
                    $conflicts[] = "Panelist schedule conflict: panelist {$panelistId}, {$defenseDay} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        $teamMembers = getTeamMembers($pdo, $defense['team_id'], 'array');
        foreach ($teamMembers as $member) {
            $memberId = $member['id'];
            if (!isset($userSchedules[$memberId])) {
                error_log(sprintf(
                    "Team member %d (ID=%s) has NO schedules loaded",
                    $memberId,
                    $memberId
                ));
                continue;
            }

            error_log(sprintf(
                "Team member %d has %d schedules, checking against defense %s day=%d",
                $memberId,
                count($userSchedules[$memberId]),
                $defenseWindow,
                $defenseDayOfWeek
            ));

            foreach ($userSchedules[$memberId] as $schedule) {
                if (schedulerClassRowAllowsOverlap($schedule)) {
                    continue;
                }

                if ((int)$schedule['day_of_week'] !== (int)$defenseDayOfWeek) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($defenseDay, $schedule);
                if ($sch === null) {
                    continue;
                }

                if (slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end'])) {
                    $conflicts[] = "Student schedule conflict: member {$memberId}, {$defenseDay} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        foreach ($existingSchedules as $existing) {
            if ($existing['day'] !== $defenseDay) {
                continue;
            }

            $existingStart = scheduler_unix_on_calendar_day($defenseDay, $existing['start_time'] ?? '');
            $existingEnd = scheduler_unix_on_calendar_day($defenseDay, $existing['end_time'] ?? '');
            if ($existingStart === false || $existingEnd === false) {
                continue;
            }

            if (slotRangesOverlap($defenseStart, $defenseEnd, $existingStart, $existingEnd) && $existing['room'] === $roomValue) {
                $existWindow = date('H:i', $existingStart) . '-' . date('H:i', $existingEnd);
                $conflicts[] = "Existing defense occupancy: room {$roomValue}, {$defenseDay} {$defenseWindow}, clashes with team {$existing['team_id']} at {$existWindow}";
            }
        }

        return array_values(array_unique($conflicts));
    }

    function validateCandidateClassConflicts($pdo, $defense, $userSchedules, $duration)
    {
        $conflicts = [];
        $dayValue = is_array($defense['day'] ?? null) ? ($defense['day'][0] ?? '') : ($defense['day'] ?? '');
        $timeValue = is_array($defense['time_slot'] ?? null) ? ($defense['time_slot'][0] ?? '') : ($defense['time_slot'] ?? '');
        $panelistIds = $defense['panelist_ids'] ?? [];
        if (!is_array($panelistIds)) {
            $panelistIds = [$panelistIds];
        }

        $range = parseTimeRange($dayValue, $timeValue, $duration);
        if ($range === null) {
            return ['Invalid day or time slot format'];
        }

        $defenseStart = $range['start'];
        $defenseEnd = $range['end'];
        $defenseDayOfWeek = $range['day_of_week'];
        $defenseWindow = date('H:i', $defenseStart) . '-' . date('H:i', $defenseEnd);

        foreach ($panelistIds as $panelistId) {
            if (is_array($panelistId)) {
                continue;
            }
            if (!isset($userSchedules[$panelistId])) {
                continue;
            }

            foreach ($userSchedules[$panelistId] as $schedule) {
                if (schedulerClassRowAllowsOverlap($schedule)) {
                    continue;
                }
                if ((int) $schedule['day_of_week'] !== (int) $defenseDayOfWeek) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($range['day'], $schedule);
                if ($sch === null) {
                    continue;
                }

                if (slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end'])) {
                    $conflicts[] = "Panelist class conflict: panelist {$panelistId}, {$dayValue} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        $teamMembers = getTeamMembers($pdo, $defense['team_id'], 'array');
        static $loggedTeams = [];
        if (!in_array($defense['team_id'], $loggedTeams)) {
            $loggedTeams[] = $defense['team_id'];
            error_log(sprintf(
                "CLASS CONFLICT CHECK: Team %d, time=%s day=%d, found %d members",
                $defense['team_id'],
                $defenseWindow,
                $defenseDayOfWeek,
                count($teamMembers)
            ));
        }

        foreach ($teamMembers as $member) {
            $memberId = $member['id'];
            if (!isset($userSchedules[$memberId])) {
                static $missedMembers = [];
                if (!in_array($memberId, $missedMembers)) {
                    $missedMembers[] = $memberId;
                    error_log(sprintf(
                        "CLASS CONFLICT: Team member %d has NO schedules loaded (team_id=%d)",
                        $memberId,
                        $defense['team_id']
                    ));
                }
                continue;
            }

            static $memberScheds = [];
            if (!in_array($memberId, $memberScheds)) {
                $memberScheds[] = $memberId;
                error_log(sprintf(
                    "CLASS CONFLICT: Team member %d has %d schedules loaded",
                    $memberId,
                    count($userSchedules[$memberId])
                ));
                // Log first few schedules for debugging
                $count = 0;
                foreach ($userSchedules[$memberId] as $s) {
                    error_log(sprintf(
                        "  Schedule: %s day %d %s-%s room %s allow_overlap=%d is_research=%d",
                        $s['class_name'] ?? 'UNKNOWN',
                        $s['day_of_week'] ?? -1,
                        $s['start_time'],
                        $s['end_time'],
                        $s['room'] ?? 'N/A',
                        $s['allow_overlap'] ?? 0,
                        $s['is_research_class'] ?? 0
                    ));
                    $count++;
                    if ($count >= 3) break;
                }
            }

            foreach ($userSchedules[$memberId] as $schedule) {
                if (schedulerClassRowAllowsOverlap($schedule)) {
                    continue;
                }
                if ((int) $schedule['day_of_week'] !== (int) $defenseDayOfWeek) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($range['day'], $schedule);
                if ($sch === null) {
                    continue;
                }

                if (slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end'])) {
                    error_log(sprintf(
                        "CLASS CONFLICT DETECTED: member %d class %s overlaps defense %s day %d",
                        $memberId,
                        $schedule['class_name'] ?? 'UNKNOWN',
                        $defenseWindow,
                        $defenseDayOfWeek
                    ));
                    $conflicts[] = "Student class conflict: member {$memberId}, {$dayValue} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        return array_values(array_unique($conflicts));
    }

    function buildPreGACandidatePool($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, $duration, $progressId = null, $validationMode = 'hybrid')
    {
        $candidatePool = [];
        $issues = [];
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);

        foreach ($teams as $team) {
            $tid = (int) $team['id'];
            $teamPanelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($teamPanelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            $teamCandidates = [];
            foreach ($days as $day) {
                foreach ($timeSlots as $timeSlot) {
                    foreach ($rooms as $room) {
                        $defense = [
                            'team_id' => $team['id'],
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];

                        $conflicts = validateCandidateSlot($pdo, $defense, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration);
                        if (empty($conflicts)) {
                            $teamCandidates[] = $defense;
                        } else {
                            $issues[$team['id']][] = [
                                'candidate' => $defense,
                                'conflicts' => $conflicts,
                            ];
                        }
                    }
                }
            }

            $candidatePool[$team['id']] = [
                'team' => $team,
                'candidates' => $teamCandidates,
                'panelists' => $selectedPanelists,
            ];
        }

        $summary = [
            'teams' => count($teams),
            'validCandidates' => array_sum(array_map(function ($entry) {
                return count($entry['candidates']);
            }, $candidatePool)),
            'unresolvedTeams' => array_values(array_filter(array_map(function ($entry) {
                return empty($entry['candidates']) ? $entry['team']['id'] : null;
            }, $candidatePool))),
        ];

        return [
            'candidatePool' => $candidatePool,
            'issues' => $issues,
            'summary' => $summary,
            'blocked' => !empty($summary['unresolvedTeams']) && $validationMode === 'strict',
            'existingSchedules' => $existingSchedules,
        ];
    }

    /**
     * Per team and calendar day: start times that pass validateCandidateSlot for at least one room
     * (student classes, panelist teaching loads, venue timetable, DB defenses — same checks as GA).
     */
    function computeSchedulerSlotContext($pdo, $teams, $panelists, $rooms, $timeSlotsFlat, $days, $userSchedules, $duration): array
    {
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);
        $slotsByTeamDay = [];
        $eligibleDaysByTeam = [];

        // Diagnostic: show what schedules we have loaded
        error_log("=== COMPUTE SLOT CONTEXT START ===");
        error_log("Total users with schedules: " . count($userSchedules));
        $totalScheds = 0;
        foreach ($userSchedules as $uid => $scheds) {
            $totalScheds += count($scheds);
        }
        error_log("Total schedule entries across all users: " . $totalScheds);

        foreach ($teams as $team) {
            $tid = (int) $team['id'];
            $slotsByTeamDay[$tid] = [];
            $teamPanelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($teamPanelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            foreach ($days as $day) {
                $good = [];
                foreach ($timeSlotsFlat as $timeSlot) {
                    $classConflicts = validateCandidateClassConflicts($pdo, [
                        'team_id' => $tid,
                        'panelist_ids' => $selectedPanelists,
                        'time_slot' => $timeSlot,
                        'day' => $day,
                        'defense_type' => $team['defense_type'] ?? 'title_proposal',
                    ], $userSchedules, $duration);
                    if (!empty($classConflicts)) {
                        error_log("Slot REJECTED (class conflict): team=$tid day=$day time=$timeSlot");
                        continue;
                    }

                    $hasRoom = false;
                    foreach ($rooms as $room) {
                        $probe = [
                            'team_id' => $tid,
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];
                        if (empty(validateCandidateSlot($pdo, $probe, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration))) {
                            $hasRoom = true;
                            break;
                        }
                    }
                    if ($hasRoom) {
                        error_log("Slot ADDED to pool: team=$tid day=$day time=$timeSlot");
                        $good[] = $timeSlot;
                    } else {
                        error_log("Slot REJECTED (no room): team=$tid day=$day time=$timeSlot");
                    }
                }
                $slotsByTeamDay[$tid][$day] = array_values(array_unique($good));
                error_log(sprintf(
                    "Final slots for team %d day %s: %d valid times",
                    $tid,
                    $day,
                    count($slotsByTeamDay[$tid][$day])
                ));
            }

            $eligibleDaysByTeam[$tid] = [];
            foreach ($days as $day) {
                if (!empty($slotsByTeamDay[$tid][$day])) {
                    $eligibleDaysByTeam[$tid][] = $day;
                }
            }
        }

        $schedulerDays = [];
        foreach ($days as $d) {
            $usableThisDay = false;
            foreach ($teams as $team) {
                if (!empty($slotsByTeamDay[(int) $team['id']][$d])) {
                    $usableThisDay = true;
                    break;
                }
            }
            if ($usableThisDay) {
                $schedulerDays[] = $d;
            }
        }

        $daysWithNoSlots = [];
        foreach ($days as $d) {
            if (!in_array($d, $schedulerDays, true)) {
                $daysWithNoSlots[] = $d;
            }
        }

        error_log("=== COMPUTE SLOT CONTEXT END ===");

        return [
            'slotsByTeamDay' => $slotsByTeamDay,
            'eligibleDaysByTeam' => $eligibleDaysByTeam,
            'schedulerDays' => $schedulerDays,
            'daysWithNoSlots' => $daysWithNoSlots,
        ];
    }

    /**
     * Count of distinct (team, day, start time, room) placements that pass the same validation as the GA candidate pool.
     */
    function countFeasibleDefensePlacements($pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $userSchedules, $duration): int
    {
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);
        $n = 0;
        foreach ($teams as $team) {
            $tid = (int) $team['id'];
            $teamPanelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($teamPanelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            foreach (($eligibleDaysByTeam[$tid] ?? []) as $day) {
                foreach (scheduler_slots_for_team_day($slotsByTeamDay, $tid, $day) as $timeSlot) {
                    foreach ($rooms as $room) {
                        $defense = [
                            'team_id' => $team['id'],
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];
                        if (empty(validateCandidateSlot($pdo, $defense, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration))) {
                            $n++;
                        }
                    }
                }
            }
        }

        return $n;
    }

    /**
     * Final gate: preview JSON rows must pass the same validateCandidateSlot checks as GA (members, panelists, room timetable).
     * Catches second-pass synthesis and any drift vs class schedules.
     *

    function buildBlockedTimeSlotMap($pdo, $teams, $panelists, $rooms, $days, $timeSlots, $userSchedules, $duration)
    {
        $blockedByDay = [];
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);
        $selectedPanelistsByTeam = [];

        foreach ($teams as $team) {
            $selectedPanelistsByTeam[(int) $team['id']] = selectPanelists(
                fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists),
                $panelists,
                $team['adviser_id'],
                $team['id']
            );
        }

        foreach ($days as $day) {
            $dayBlocked = [];

            foreach ($timeSlots as $timeSlot) {
                $isUsable = false;
                $reasonCounts = [];
                $sampleReasons = [];

                foreach ($teams as $team) {
                    $teamId = (int) $team['id'];
                    $selectedPanelists = $selectedPanelistsByTeam[$teamId] ?? [];

                    foreach ($rooms as $room) {
                        $candidate = [
                            'team_id' => $teamId,
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];

                        $classConflicts = validateCandidateClassConflicts($pdo, $candidate, $userSchedules, $duration);
                        if (!empty($classConflicts)) {
                            foreach ($classConflicts as $conflict) {
                                $reasonCounts[$conflict] = ($reasonCounts[$conflict] ?? 0) + 1;
                            }
                            if (count($sampleReasons) < 5) {
                                foreach ($classConflicts as $conflict) {
                                    if (!in_array($conflict, $sampleReasons, true)) {
                                        $sampleReasons[] = $conflict;
                                    }
                                    if (count($sampleReasons) >= 5) {
                                        break;
                                    }
                                }
                            }
                            continue;
                        }

                        $conflicts = validateCandidateSlot($pdo, $candidate, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration);
                        if (empty($conflicts)) {
                            $isUsable = true;
                            break 2;
                        }

                        foreach ($conflicts as $conflict) {
                            $reasonCounts[$conflict] = ($reasonCounts[$conflict] ?? 0) + 1;
                        }
                        if (count($sampleReasons) < 5) {
                            foreach ($conflicts as $conflict) {
                                if (!in_array($conflict, $sampleReasons, true)) {
                                    $sampleReasons[] = $conflict;
                                }
                                if (count($sampleReasons) >= 5) {
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!$isUsable) {
                    arsort($reasonCounts);
                    $topReasons = [];
                    foreach (array_slice($reasonCounts, 0, 5, true) as $reason => $count) {
                        $topReasons[] = ($count > 1 ? "[x{$count}] " : '') . $reason;
                    }
                    $dayBlocked[] = [
                        'time_slot' => $timeSlot,
                        'reasons' => !empty($topReasons)
                            ? $topReasons
                            : array_values(array_slice(array_unique($sampleReasons), 0, 3)),
                        'conflict_breakdown' => array_slice($reasonCounts, 0, 10, true),
                    ];
                }
            }

            if (!empty($dayBlocked)) {
                $blockedByDay[$day] = $dayBlocked;
            }
        }

        return $blockedByDay;
    }

    /**
     * @return list<string> Human-readable failures (empty = OK)
     */
    function validatePreviewScheduleRowsAgainstClasses($pdo, array $previewRows, $userSchedules, $duration): array
    {
        $existing = fetchExistingDefenseSchedules($pdo);
        $roomMap = buildRoomOccupancyMap($userSchedules);
        
        // Extract team IDs from preview rows
        $teamIds = [];
        foreach ($previewRows as $row) {
            if (!empty($row['team_id'])) {
                $teamIds[] = (int) $row['team_id'];
            }
        }
        $teamIds = array_values(array_unique($teamIds));
        
        // Augment occupancy map with all team schedules (fetches from DB like preview overlay does)
        augmentOccupancyMapWithTeamSchedules($pdo, $roomMap, $teamIds);
        
        $failures = [];

        foreach ($previewRows as $row) {
            if (empty($row['team_id']) || empty($row['schedule_date']) || $row['start_time'] === null || $row['start_time'] === '') {
                continue;
            }
            $ts = trim((string) $row['start_time']);
            if (strlen($ts) >= 8) {
                $ts = substr($ts, 0, 5);
            }

            $p1 = isset($row['panelist_id']) ? (int) $row['panelist_id'] : 0;
            $p2 = isset($row['panelist_id2']) ? (int) $row['panelist_id2'] : 0;
            $p3 = isset($row['panelist_id3']) ? (int) $row['panelist_id3'] : 0;
            $panelIds = array_values(array_filter([$p1, $p2, $p3], function ($id) {
                return $id > 0;
            }));

            $normalizedDay = scheduler_calendar_day_from_raw($row['schedule_date']);
            if ($normalizedDay === null) {
                error_log(sprintf(
                    "PREVIEW ROW DATE NORMALIZATION FAILED: team=%s raw_date=%s",
                    $row['team_id'] ?? '(unknown)',
                    $row['schedule_date'] ?? '(empty)'
                ));
            }

            $defense = [
                'team_id' => (int) $row['team_id'],
                'panelist_ids' => $panelIds,
                'room' => $row['room'] ?? '',
                'time_slot' => $ts,
                'day' => $normalizedDay ?? $row['schedule_date'],
                'defense_type' => $row['defense_type'] ?? 'title_proposal',
            ];

            if (count($panelIds) < 3) {
                $failures[] = 'Team ' . $defense['team_id'] . ': preview row has fewer than 3 panelists.';

                continue;
            }

            // Only check room/venue conflicts. Skip class conflicts since GA already validated against pre-filtered slots.
            $roomErrs = [];
            $defenseDay = $normalizedDay ?? $row['schedule_date'];
            $ts = trim((string) $row['start_time']);
            if (strlen($ts) >= 8) {
                $ts = substr($ts, 0, 5);
            }
            $range = parseTimeRange($defenseDay, $ts, $duration);
            if ($range !== null) {
                $defenseStart = $range['start'];
                $defenseEnd = $range['end'];
                $defenseDayOfWeek = $range['day_of_week'];
                $roomValue = $defense['room'] ?? '';
                
                // Check room occupancy
                if ($roomValue !== '' && isset($roomMap[$roomValue][$defenseDayOfWeek])) {
                    foreach ($roomMap[$roomValue][$defenseDayOfWeek] as $occupied) {
                        $occStart = scheduler_unix_on_calendar_day($defenseDay, $occupied['start_time'] ?? '');
                        $occEnd = scheduler_unix_on_calendar_day($defenseDay, $occupied['end_time'] ?? '');
                        if ($occStart === false || $occEnd === false) {
                            continue;
                        }
                        if (slotRangesOverlap($defenseStart, $defenseEnd, $occStart, $occEnd)) {
                            $roomErrs[] = "Room schedule conflict: room {$roomValue} occupied by " . schedulerClassRowDescriptor($occupied);
                            break;
                        }
                    }
                }
                
                // Check existing defense occupancy
                foreach ($existing as $existingDef) {
                    if ($existingDef['day'] !== $defenseDay) {
                        continue;
                    }
                    $existingStart = scheduler_unix_on_calendar_day($defenseDay, $existingDef['start_time'] ?? '');
                    $existingEnd = scheduler_unix_on_calendar_day($defenseDay, $existingDef['end_time'] ?? '');
                    if ($existingStart === false || $existingEnd === false) {
                        continue;
                    }
                    if (slotRangesOverlap($defenseStart, $defenseEnd, $existingStart, $existingEnd) && $existingDef['room'] === $roomValue) {
                        $roomErrs[] = "Room already scheduled: team {$existingDef['team_id']} at {$defenseDay}";
                        break;
                    }
                }
            }
            
            if (!empty($roomErrs)) {
                $failures[] = 'Team ' . $defense['team_id'] . ': ' . implode('; ', array_slice($roomErrs, 0, 3));
            }
        }

        return $failures;
    }

    function describeValidationIssues($issues, $pdo)
    {
        if (empty($issues)) {
            return [];
        }

        $teamNames = getTeamNames($pdo, array_keys($issues));
        $messages = [];

        foreach ($issues as $teamId => $entries) {
            $examples = [];
            foreach (array_slice($entries, 0, 3) as $entry) {
                $examples[] = implode('; ', $entry['conflicts']);
            }

            $messages[] = sprintf(
                'Group %s could not be scheduled: %s',
                $teamNames[$teamId] ?? ('Team ' . $teamId),
                implode(' | ', $examples)
            );
        }

        return $messages;
    }

    function summarizeValidationIssues($issues)
    {
        $summary = [
            'teams' => count($issues),
            'room' => 0,
            'panelist' => 0,
            'member' => 0,
            'invalid' => 0,
        ];

        foreach ($issues as $entries) {
            foreach ($entries as $entry) {
                foreach ($entry['conflicts'] as $conflict) {
                    $lower = strtolower($conflict);
                    if (strpos($lower, 'student schedule conflict') !== false) {
                        $summary['member']++;
                    } elseif (strpos($lower, 'panelist schedule conflict') !== false || strpos($lower, 'faculty') !== false) {
                        $summary['panelist']++;
                    } elseif (strpos($lower, 'room schedule conflict') !== false) {
                        $summary['room']++;
                    } elseif (strpos($lower, 'existing defense occupancy') !== false || strpos($lower, 'already has a defense') !== false) {
                        $summary['room']++;
                    } elseif (strpos($lower, 'time window violation') !== false || strpos($lower, 'past 8:30') !== false) {
                        $summary['invalid']++;
                    } elseif (strpos($lower, 'room') !== false) {
                        $summary['room']++;
                    } elseif (strpos($lower, 'panelist') !== false) {
                        $summary['panelist']++;
                    } elseif (strpos($lower, 'team member') !== false || strpos($lower, 'member') !== false) {
                        $summary['member']++;
                    } else {
                        $summary['invalid']++;
                    }
                }
            }
        }

        return $summary;
    }

    function buildValidationFailurePayload($pdo, $validationMode, $validationSummary, $validationIssues)
    {
        $issueMessages = describeValidationIssues($validationIssues, $pdo);
        $summaryCounts = summarizeValidationIssues($validationIssues);

        return [
            'validationMode' => $validationMode,
            'validationSummary' => $validationSummary,
            'validationCounts' => $summaryCounts,
            'validationIssues' => $issueMessages,
            'suggestions' => [
                'adjust room availability',
                'add more rooms',
                'modify section times',
                'reduce blocked schedules',
            ],
        ];
    }

    function chooseCandidateFromPool($candidatePool, $teamId)
    {
        if (!isset($candidatePool[$teamId]['candidates']) || empty($candidatePool[$teamId]['candidates'])) {
            return null;
        }

        return $candidatePool[$teamId]['candidates'][array_rand($candidatePool[$teamId]['candidates'])];
    }

    /**
     * Get accessible sections for current user based on role
     * - Admin (id=0): All sections
     * - Program Chair (usertype=0, id!=0): All sections in their college
     * - Faculty (usertype=2): Only their assigned sections
     */
    function getAccessibleSections($pdo, $userId, $usertype) {
        try {
            // Admin (id=0): All sections
            if ($userId === 0 && $usertype === 0) {
                $stmt = $pdo->prepare("SELECT DISTINCT section FROM users WHERE section IS NOT NULL ORDER BY section");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Program Chair (usertype=0, id!=0): All sections in their college
            if ($usertype === 0 && $userId !== 0) {
                require_once __DIR__ . '/../../assets/includes/auth_functions.php';
                $userCollege = get_user_college($pdo, $userId);
                
                if (!$userCollege) {
                    return [];
                }

                $stmt = $pdo->prepare("
                    SELECT DISTINCT u.section FROM users u
                    LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                    WHERE u.section IS NOT NULL AND p.college = :college
                    ORDER BY u.section
                ");
                $stmt->execute([':college' => $userCollege]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Faculty (usertype=2): Only their assigned sections
            if ($usertype === 2) {
                require_once __DIR__ . '/../../dashboard/includes/section_access.php';
                return getProfessorSections($pdo, $userId);
            }

            return [];
        } catch (Exception $e) {
            error_log("Error getting accessible sections: " . $e->getMessage());
            return [];
        }
    }

    // Main execution
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $estimateOnly = isset($_POST['estimate_slots']) && ($_POST['estimate_slots'] === 'true' || $_POST['estimate_slots'] === '1');

        if ($estimateOnly) {
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                throw new Exception('Database connection error');
            }
            if (!validateInputs()) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo json_encode([
                    'success' => false,
                    'feasible_placement_count' => null,
                    'message' => 'Rooms, defense days, time slot list, and duration are required to estimate availability.',
                ]);
                exit;
            }

            // Section filter (mirror main handler; no overwrite/upgrade dialogs)
            $selectedSections = [];
            if (isset($_POST['section']) && !empty($_POST['section'])) {
                if (is_array($_POST['section'])) {
                    $selectedSections = array_filter($_POST['section'], static function ($s) {
                        return !empty(trim($s));
                    });
                } else {
                    $selectedSections = [trim($_POST['section'])];
                }
            } elseif (isset($_POST['selectedSection']) && !empty($_POST['selectedSection'])) {
                if (is_array($_POST['selectedSection'])) {
                    $selectedSections = array_filter($_POST['selectedSection'], static function ($s) {
                        return !empty(trim($s));
                    });
                } else {
                    $selectedSections = [trim($_POST['selectedSection'])];
                }
            }

            $currentUserId = $_SESSION['id'] ?? 0;
            $currentUsertype = $_SESSION['usertype'] ?? -1;
            $accessibleSections = getAccessibleSections($pdo, $currentUserId, $currentUsertype);
            if (!empty($selectedSections)) {
                $invalidSections = array_diff($selectedSections, $accessibleSections);
                if (!empty($invalidSections)) {
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    echo json_encode(['success' => false, 'message' => 'You do not have access to one or more selected sections.']);
                    exit;
                }
            } else {
                $selectedSections = $accessibleSections;
            }

            $duration = floatval($_POST['timeDuration']);
            if (!is_numeric($duration) || $duration <= 0) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo json_encode(['success' => false, 'message' => 'Invalid duration.', 'feasible_placement_count' => null]);
                exit;
            }
            $GLOBALS['timeDuration'] = $duration;

            $teams = fetchTeams($pdo, $selectedSections);
            $panelists = fetchPanelists($pdo);
            $teams = array_values($teams);

            $rooms = $_POST['rooms'];
            $days = $_POST['days'];
            $timeSlotsRaw = isset($_POST['timeSlots']) && is_array($_POST['timeSlots']) ? $_POST['timeSlots'] : [];
            $timeSlots = filter_time_slots_respecting_latest_end(array_map('trim', $timeSlotsRaw), $duration);

            if (empty($teams) || empty($panelists)) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo json_encode([
                    'success' => true,
                    'feasible_placement_count' => 0,
                    'teams_considered' => 0,
                    'message' => 'No teams or panelists loaded for this section filter.',
                ]);
                exit;
            }

            if (empty($timeSlots)) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo json_encode([
                    'success' => true,
                    'feasible_placement_count' => 0,
                    'teams_considered' => count($teams),
                    'message' => 'No start times fit the 8:30 PM finish rule for this defense length.',
                ]);
                exit;
            }

            $userSchedules = fetchUserSchedules($pdo);
            $slotContext = computeSchedulerSlotContext($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, $duration);
            $feasible = 0;
            $theoretical = 0;
            $blockedTimeSlots = [];

            if (!empty($slotContext['schedulerDays'])) {
                foreach ($teams as $t) {
                    $tid = (int) $t['id'];
                    foreach (($slotContext['eligibleDaysByTeam'][$tid] ?? []) as $d) {
                        $cnt = count(scheduler_slots_for_team_day($slotContext['slotsByTeamDay'], $tid, $d));
                        $theoretical += $cnt * count($rooms);
                    }
                }
                $feasible = countFeasibleDefensePlacements(
                    $pdo,
                    $teams,
                    $panelists,
                    $rooms,
                    $slotContext['slotsByTeamDay'],
                    $slotContext['eligibleDaysByTeam'],
                    $userSchedules,
                    $duration
                );
            }

            if ($feasible < count($teams)) {
                $blockedTimeSlots = buildBlockedTimeSlotMap($pdo, $teams, $panelists, $rooms, $days, $timeSlots, $userSchedules, $duration);
            }

            while (ob_get_level()) {
                ob_end_clean();
            }
            $teamCount = count($teams);
            $estimateWarnings = [];
            if ($feasible <= 0) {
                $estimateWarnings[] = 'Zero conflict-free placements: generation cannot satisfy class, faculty, and room rules.';
                $estimateWarnings[] = 'Remove the blocked start times below or widen dates/rooms before generating.';
            }
            if ($feasible > 0 && $feasible < $teamCount) {
                $estimateWarnings[] = 'Fewer valid placements (' . $feasible . ') than teams (' . $teamCount . ') — optimizer may fail or omit teams unless you widen rooms/dates.';
            }
            if (!empty($slotContext['daysWithNoSlots'] ?? [])) {
                $estimateWarnings[] = 'Some selected dates have no feasible start times for any team: ' . implode(', ', array_slice($slotContext['daysWithNoSlots'], 0, 5));
            }

            echo json_encode([
                'success' => true,
                'estimate_slots' => true,
                'feasible_placement_count' => $feasible,
                'theoretical_max_checked' => $theoretical,
                'teams_considered' => $teamCount,
                'candidate_days_kept' => count($slotContext['schedulerDays'] ?? []),
                'requested_day_count' => is_array($days) ? count($days) : 0,
                'allowed_start_slots' => count($timeSlots),
                'rooms_count' => is_array($rooms) ? count($rooms) : 0,
                'days_omitted_no_feasible_starts' => $slotContext['daysWithNoSlots'] ?? [],
                'blocked_time_slots_by_day' => $blockedTimeSlots,
                'estimate_warnings' => $estimateWarnings,
                'placements_below_team_count' => $feasible > 0 && $feasible < $teamCount,
                'message' => $feasible > 0
                    ? "After loading section and faculty classes, {$feasible} conflict-free (team × day × time × room) placement(s) match your settings."
                    : 'No conflict-free placements found for current rooms, dates, and hours once class schedules are applied.',
            ]);
            exit;
        }

        // Generate unique progress ID for tracking
        $progressId = uniqid('sched_', true);
        updateProgress($pdo, $progressId, 'running', 'Validating inputs...', 5);

        // Validate required inputs
        if (!validateInputs()) {
            updateProgress($pdo, $progressId, 'error', 'Please check all required fields are filled correctly', null);
            throw new Exception("Please check all required fields are filled correctly");
        }

        updateProgress($pdo, $progressId, 'running', 'Loading team data...', 10);

        // Get the selected section(s) for filtering
        $selectedSections = [];
        if (isset($_POST['section']) && !empty($_POST['section'])) {
            if (is_array($_POST['section'])) {
                $selectedSections = array_filter($_POST['section'], function($s) { return !empty(trim($s)); });
            } else {
                $selectedSections = [trim($_POST['section'])];
            }
        } elseif (isset($_POST['selectedSection']) && !empty($_POST['selectedSection'])) {
            if (is_array($_POST['selectedSection'])) {
                $selectedSections = array_filter($_POST['selectedSection'], function($s) { return !empty(trim($s)); });
            } else {
                $selectedSections = [trim($_POST['selectedSection'])];
            }
        }

        // 🔐 APPLY ACCESS CONTROL: Get current user's accessible sections
        $currentUserId = $_SESSION['id'] ?? 0;
        $currentUsertype = $_SESSION['usertype'] ?? -1;
        $accessibleSections = getAccessibleSections($pdo, $currentUserId, $currentUsertype);

        // If user selected specific sections, validate they have access
        if (!empty($selectedSections)) {
            // Check if all selected sections are in accessible sections
            $invalidSections = array_diff($selectedSections, $accessibleSections);
            if (!empty($invalidSections)) {
                updateProgress($pdo, $progressId, 'error', 'You do not have access to one or more selected sections', null);
                if (ob_get_level()) ob_end_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'You do not have access to one or more selected sections'
                ]);
                exit;
            }
        } else {
            // If no section selected, use all accessible sections
            $selectedSections = $accessibleSections;
        }

        error_log("Scheduler Access Control: userId=$currentUserId, usertype=$currentUsertype, accessibleSections=" . implode(',', $accessibleSections) . ", selectedSections=" . implode(',', $selectedSections));

        updateProgress($pdo, $progressId, 'running', 'Checking for existing schedules...', 15);

        // Check for teams that already have schedules
        $scheduledTeams = checkExistingSchedules($pdo, $selectedSections);
        error_log("SCHEDULER: checkExistingSchedules returned " . count($scheduledTeams) . " teams");
        error_log("SCHEDULER: scheduledTeams data: " . json_encode($scheduledTeams));
        
        // Separate schedules into past and future
        $currentDate = date('Y-m-d');
        $pastSchedules = [];
        $futureSchedules = [];
        $upcomingDefenses = []; // Defenses that haven't started yet (pending status)
        $teamsNeedingGradeCheck = [];
        $teamsToAutoProgress = [];
        
        error_log("SCHEDULER: Current date for comparison: $currentDate");
        error_log("SCHEDULER: Starting to process " . count($scheduledTeams) . " scheduled teams");
        
        foreach ($scheduledTeams as $teamId => $schedule) {
            $scheduleDate = $schedule['defense_date'] ?? 'NO_DATE';
            $defenseStatus = $schedule['defense_status'] ?? 'pending';
            error_log("SCHEDULER: Processing Team $teamId - defense_date: $scheduleDate, defense_status: $defenseStatus");
            
            // Normalize schedule date to Y-m-d format
            if ($scheduleDate !== 'NO_DATE') {
                $parsedDate = parseDate($scheduleDate);
                if ($parsedDate) {
                    $scheduleDate = $parsedDate->format('Y-m-d');
                    error_log("SCHEDULER: Team $teamId normalized date: $scheduleDate");
                } else {
                    error_log("SCHEDULER: Team $teamId - FAILED to parse date: " . $schedule['defense_date']);
                }
            }
            
            if ($scheduleDate < $currentDate) {
                // Past schedule
                $pastSchedules[$teamId] = $schedule;
                error_log("SCHEDULER: Team $teamId marked as PAST schedule ($scheduleDate < $currentDate)");
                
                // Check if defense has been evaluated (passed/failed)
                if (in_array($defenseStatus, ['passed', 'failed'])) {
                    // Need to check if team has all grades before progression
                    $teamsNeedingGradeCheck[$teamId] = [
                        'old_schedule' => $schedule,
                        'status' => $defenseStatus
                    ];
                    error_log("SCHEDULER: Team $teamId needs grade check for progression (status: $defenseStatus)");
                } else {
                    error_log("SCHEDULER: Team $teamId has past schedule but status is '$defenseStatus' (not passed/failed) - skipping progression");
                }
            } else {
                // Future schedule
                $futureSchedules[$teamId] = $schedule;
                error_log("SCHEDULER: Team $teamId marked as FUTURE schedule ($scheduleDate >= $currentDate)");
                
                // Only ask for override if defense is still pending (hasn't started)
                if ($defenseStatus === 'pending') {
                    $upcomingDefenses[$teamId] = $schedule;
                    error_log("SCHEDULER: Team $teamId has UPCOMING defense (pending)");
                } else {
                    error_log("SCHEDULER: Team $teamId has future schedule but status is '$defenseStatus' (not pending) - will not ask for override");
                }
            }
        }
        
        error_log("SCHEDULER: Finished processing teams loop");
        
        error_log("SCHEDULER: Summary - Past: " . count($pastSchedules) . ", Future: " . count($futureSchedules) . ", Upcoming/Pending: " . count($upcomingDefenses) . ", Needs grade check: " . count($teamsNeedingGradeCheck));
        error_log("SCHEDULER: POST confirm_upgrade = " . (isset($_POST['confirm_upgrade']) ? $_POST['confirm_upgrade'] : 'NOT SET'));
        error_log("SCHEDULER: POST confirm_overwrite = " . (isset($_POST['confirm_overwrite']) ? $_POST['confirm_overwrite'] : 'NOT SET'));
        
        // Check if we need to ask for defense type upgrade confirmation
        if (!empty($teamsNeedingGradeCheck) && (!isset($_POST['confirm_upgrade']) || $_POST['confirm_upgrade'] !== 'true')) {
            error_log("SCHEDULER: Need to check grades for " . count($teamsNeedingGradeCheck) . " teams");
            
            // Verify all teams have complete grades
            $teamsReadyForUpgrade = [];
            $teamsMissingGrades = [];
            
            foreach ($teamsNeedingGradeCheck as $teamId => $data) {
                if (teamHasCompleteGrades($pdo, $teamId, $data['old_schedule']['id'])) {
                    $teamsReadyForUpgrade[$teamId] = $data;
                } else {
                    $teamsMissingGrades[$teamId] = $data;
                }
            }
            
            error_log("SCHEDULER: Teams ready for upgrade: " . count($teamsReadyForUpgrade) . ", Missing grades: " . count($teamsMissingGrades));
            
            if (!empty($teamsReadyForUpgrade)) {
                // Ask for confirmation to upgrade defense types
                updateProgress($pdo, $progressId, 'info', 'Defense type upgrades require confirmation', null);
                $upgradeInfo = [];
                foreach ($teamsReadyForUpgrade as $teamId => $data) {
                    $currentType = $data['old_schedule']['defense_type'] ?? 'title_proposal';
                    $status = $data['status'];
                    $newType = '';
                    if ($status === 'passed') {
                        switch ($currentType) {
                            case 'title_proposal': $newType = 'title_defense'; break;
                            case 'title_defense': $newType = 'final_defense'; break;
                            case 'final_defense': $newType = '(completed)'; break;
                        }
                    } else {
                        $newType = 're_defense';
                    }
                    
                    $teamNames = getTeamNames($pdo, [$teamId]);
                    $upgradeInfo[] = [
                        'team_id' => $teamId,
                        'team_name' => $teamNames[$teamId] ?? "Team $teamId",
                        'current_type' => ucwords(str_replace('_', ' ', $currentType)),
                        'new_type' => ucwords(str_replace('_', ' ', $newType)),
                        'status' => $status
                    ];
                }
                
                error_log("SCHEDULER: Asking for upgrade confirmation for " . count($upgradeInfo) . " teams");
                if (ob_get_level()) ob_end_clean();
                echo json_encode([
                    'success' => false,
                    'requireUpgradeConfirmation' => true,
                    'message' => 'Some teams have completed defenses and are ready for progression. Proceed with defense type upgrades?',
                    'upgradeInfo' => $upgradeInfo,
                    'missingGrades' => !empty($teamsMissingGrades) ? array_keys($teamsMissingGrades) : []
                ]);
                exit;
            }
            
            // If all teams missing grades, skip progression
            if (empty($teamsReadyForUpgrade)) {
                error_log("SCHEDULER: No teams ready for upgrade (all missing grades) - proceeding without upgrade");
                $teamsToAutoProgress = [];
            }
        } elseif (!empty($teamsNeedingGradeCheck) && isset($_POST['confirm_upgrade']) && $_POST['confirm_upgrade'] === 'true') {
            error_log("SCHEDULER: Upgrade confirmed, processing teams with complete grades");
            // Upgrade confirmed, filter teams with complete grades
            foreach ($teamsNeedingGradeCheck as $teamId => $data) {
                if (teamHasCompleteGrades($pdo, $teamId, $data['old_schedule']['id'])) {
                    $teamsToAutoProgress[$teamId] = $data;
                }
            }
        } else {
            error_log("SCHEDULER: No teams needing grade check or already processed");
        }
        
        // If there are upcoming defenses (pending) and overwrite confirmation is not received
        if (!empty($upcomingDefenses) && (!isset($_POST['confirm_overwrite']) || $_POST['confirm_overwrite'] !== 'true')) {
            error_log("SCHEDULER: Asking for overwrite confirmation for " . count($upcomingDefenses) . " upcoming defenses");
            updateProgress($pdo, $progressId, 'error', 'Confirmation required for overwriting upcoming defenses', null);
            $teamNames = getTeamNames($pdo, array_keys($upcomingDefenses));
            if (ob_get_level()) ob_end_clean();
            echo json_encode([
                'success' => false,
                'requireConfirmation' => true,
                'message' => 'The following teams have UPCOMING defenses that have not started yet. Do you want to overwrite them?',
                'scheduledTeams' => $teamNames
            ]);
            exit;
        } else {
            error_log("SCHEDULER: No upcoming defenses to confirm or confirmation received");
        }
        
        // Process automatic defense type progression for past schedules
        if (!empty($teamsToAutoProgress)) {
            updateProgress($pdo, $progressId, 'running', 'Processing defense progressions...', 18);
            $progressedTeams = handleDefenseProgression($pdo, $teamsToAutoProgress);
            error_log("SCHEDULER: Auto-progressed " . count($progressedTeams) . " teams based on past defense results");
        }
        
        // Remove ONLY upcoming/pending future schedules (past schedules and completed future schedules are kept)
        if (!empty($upcomingDefenses)) {
            updateProgress($pdo, $progressId, 'running', 'Removing upcoming schedules...', 20);
            removeExistingSchedules($pdo, array_keys($upcomingDefenses));
            error_log("SCHEDULER: Removed " . count($upcomingDefenses) . " upcoming schedules for re-scheduling");
        }

        // NOW fetch teams AFTER processing progressions and removing schedules
        updateProgress($pdo, $progressId, 'running', 'Loading teams and panelists...', 22);

        $teams = fetchTeams($pdo, $selectedSections);
        $panelists = fetchPanelists($pdo);
        $requestedTeamIds = parseSchedulerTeamIdList($_POST['unresolved_team_ids'] ?? []);
        
        // Filter out teams that should NOT be scheduled:
        // 1. Teams with future pending schedules that were NOT confirmed for overwrite
        // 2. Teams that have completed final_defense and passed
        $teamsWithFuturePending = [];
        if (!isset($_POST['confirm_overwrite']) || $_POST['confirm_overwrite'] !== 'true') {
            // Get teams that still have future pending schedules
            $currentDate = date('Y-m-d');
            $placeholders = implode(',', array_fill(0, count($selectedSections ?: ['ALL']), '?'));
            $sectionFilter = !empty($selectedSections) ? 
                "AND EXISTS (SELECT 1 FROM team_members tm2 JOIN users u ON tm2.user_id = u.id WHERE tm2.team_id = ds.team_id AND u.section IN ($placeholders))" : "";
            
            $checkStmt = $pdo->prepare("
                SELECT DISTINCT ds.team_id 
                FROM defense_schedules ds 
                WHERE ds.status = 'scheduled' 
                AND ds.schedule_date >= ?
                AND ds.defense_status = 'pending'
                $sectionFilter
            ");
            $params = [$currentDate];
            if (!empty($selectedSections)) {
                $params = array_merge($params, $selectedSections);
            }
            $checkStmt->execute($params);
            $teamsWithFuturePending = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("SCHEDULER: Teams with future pending schedules (NOT overwriting): " . implode(', ', $teamsWithFuturePending));
        }
        
        // Filter teams to schedule
        $originalTeamCount = count($teams);
        $teams = array_filter($teams, function($team) use ($teamsWithFuturePending) {
            // Exclude teams with future pending schedules
            return !in_array($team['id'], $teamsWithFuturePending);
        });
        if (!empty($requestedTeamIds)) {
            $teams = array_filter($teams, function ($team) use ($requestedTeamIds) {
                return in_array((int) ($team['id'] ?? 0), $requestedTeamIds, true);
            });
        }
        $teams = array_values($teams); // Re-index array
        $requestedScopeTeamIds = array_values(array_unique(array_map(static function ($team) {
            return (int) ($team['id'] ?? 0);
        }, $teams)));
        
        error_log("SCHEDULER: After filtering - " . count($teams) . " teams to schedule (excluded " . ($originalTeamCount - count($teams)) . " with future pending schedules)");
        
        $duration = $_POST['timeDuration'];
        // Convert to a proper number
        if (!is_numeric($duration) || floatval($duration) <= 0) {
            updateProgress($pdo, $progressId, 'error', 'Invalid duration. It must be a positive number.', null);
            throw new Exception("Invalid duration. It must be a positive number.");
        }
        $duration = floatval($duration);
        // FIX: Set duration to a global variable so functions use it instead of $_POST
        $GLOBALS['timeDuration'] = $duration;
        $validationMode = normalizeValidationMode($_POST['validationMode'] ?? $_POST['schedulingMode'] ?? 'hybrid');
        $GLOBALS['validationMode'] = $validationMode;

        $rooms = $_POST['rooms'];
        $days = $_POST['days'];
        $timeSlotsRaw = isset($_POST['timeSlots']) && is_array($_POST['timeSlots']) ? $_POST['timeSlots'] : [];

        updateProgress($pdo, $progressId, 'running', 'Normalizing time slots to respect latest finish (8:30 PM ceiling for this defense length)…', 24);

        $timeSlots = filter_time_slots_respecting_latest_end(array_map('trim', $timeSlotsRaw), $duration);

        if (empty($timeSlots)) {
            $msgEmpty = 'No valid start slots remain: each option would run past the 8:30 PM limit with defense duration '
                . $duration . ' h. Reduce duration or end your working-window range earlier.';
            updateProgress($pdo, $progressId, 'error', $msgEmpty, null);
            if (ob_get_level()) {
                ob_end_clean();
            }
            echo json_encode([
                'success' => false,
                'message' => $msgEmpty,
                'suggestions' => [
                    'Shorten defense duration',
                    'Start the last permissible slot earlier (e.g. last start = 8:30 PM minus duration)',
                    'Add gaps so automatic slot builder stops before over-long blocks',
                ],
            ]);
            exit;
        }

        $_POST['timeSlots'] = $timeSlots;

        updateProgress($pdo, $progressId, 'running', 'Loading section and faculty schedules (user_schedules)…', 24);

        $userSchedules = fetchUserSchedules($pdo);
        $GLOBALS['schedulerUserSchedulesSnapshot'] = $userSchedules;

        updateProgress($pdo, $progressId, 'running', 'Computing conflict-free time slots per defense day (classes, faculty, venues, existing defenses)…', 25);

        $slotContext = computeSchedulerSlotContext($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, $duration);
        $slotsByTeamDay = $slotContext['slotsByTeamDay'];
        $eligibleDaysByTeam = $slotContext['eligibleDaysByTeam'];
        $schedulerDays = $slotContext['schedulerDays'];
        $GLOBALS['schedulerSlotsByTeamDay'] = $slotsByTeamDay;
        $GLOBALS['schedulerEligibleDaysByTeam'] = $eligibleDaysByTeam;
        $GLOBALS['schedulerDays'] = $schedulerDays;

        if (empty($schedulerDays)) {
            $msgNoDay = 'None of the selected defense dates have any feasible start times for any team after excluding class overlaps, faculty loads, venue conflicts, and existing defenses. Broaden dates/rooms, shorten duration, or resolve timetable clashes.';
            updateProgress($pdo, $progressId, 'error', $msgNoDay, null);
            if (ob_get_level()) {
                ob_end_clean();
            }
            echo json_encode([
                'success' => false,
                'message' => $msgNoDay,
                'daysWithNoFeasibleSlots' => $slotContext['daysWithNoSlots'],
            ]);
            exit;
        }

        if (!empty($slotContext['daysWithNoSlots'])) {
            error_log(
                'SCHEDULER: Dates with zero feasible slots for any team on that day (omitted from pooled days): '
                . implode(', ', $slotContext['daysWithNoSlots'])
            );
            updateProgress(
                $pdo,
                $progressId,
                'running',
                count($slotContext['daysWithNoSlots']) . ' date(s) omitted (no candidate had a free slot on that day); ' . count($schedulerDays) . ' day(s) still in rotation',
                26
            );
        }

        updateProgress($pdo, $progressId, 'running', 'Validating classroom room bookings and constructing conflict-free GA candidate pools…', 26);
        $preValidation = buildPreGACandidatePool(
            $pdo,
            $teams,
            $panelists,
            $rooms,
            $slotsByTeamDay,
            $eligibleDaysByTeam,
            $userSchedules,
            $duration,
            $progressId,
            $validationMode
        );

        $validCandidatePool = $preValidation['candidatePool'];
        $validationIssues = $preValidation['issues'];
        $validationSummary = $preValidation['summary'];
        $unresolvedFromPreValidation = array_values(array_unique(array_map('intval', $validationSummary['unresolvedTeams'] ?? [])));

        updateProgress(
            $pdo,
            $progressId,
            'running',
            'Valid candidate pool ready: ' . $validationSummary['validCandidates'] . ' conflict-free candidates found',
            30
        );

        if (!empty($validationSummary['unresolvedTeams'])) {
            $unresolvedNames = getTeamNames($pdo, $validationSummary['unresolvedTeams']);
            $summaryText = 'No possible schedule available for ' . implode(', ', array_values($unresolvedNames)) . '.';
            $detailText = implode(' ', describeValidationIssues($validationIssues, $pdo));
            $failurePayload = buildValidationFailurePayload($pdo, $validationMode, $validationSummary, $validationIssues);

            error_log('VALIDATION SUMMARY: ' . json_encode($failurePayload['validationCounts']));
            foreach (array_slice($failurePayload['validationIssues'], 0, 5) as $message) {
                error_log('VALIDATION DETAIL: ' . $message);
            }

            updateProgress($pdo, $progressId, $validationMode === 'strict' ? 'error' : 'info', $summaryText, null);

            if ($validationMode === 'strict') {
                if (ob_get_level()) ob_end_clean();
                $payload = array_merge([
                    'success' => false,
                    'message' => $summaryText,
                ], $failurePayload);
                echo json_encode($payload);
                exit;
            }

            updateProgress(
                $pdo,
                $progressId,
                'info',
                'Schedule generation completed with unresolved conflicts: ' . implode(' | ', array_slice($failurePayload['validationIssues'], 0, 3)),
                32
            );
            error_log('SOFT MODE: ' . $summaryText . ' ' . $detailText);
        }

        if (!empty($unresolvedFromPreValidation)) {
            if ($validationMode === 'strict') {
                $teamsBeforeFilter = count($teams);
                $teams = array_values(array_filter($teams, static function ($team) use ($unresolvedFromPreValidation) {
                    return !in_array((int) ($team['id'] ?? 0), $unresolvedFromPreValidation, true);
                }));

                foreach ($unresolvedFromPreValidation as $badTeamId) {
                    unset($validCandidatePool[$badTeamId]);
                }

                updateProgress(
                    $pdo,
                    $progressId,
                    'info',
                    'Constraint pre-filter removed ' . count($unresolvedFromPreValidation) . ' unschedulable team(s); continuing with ' . count($teams) . ' schedulable team(s).',
                    33
                );
                error_log('PRE-FILTER: reduced GA scope from ' . $teamsBeforeFilter . ' to ' . count($teams) . ' teams; unresolved=' . implode(',', $unresolvedFromPreValidation));
            } else {
                updateProgress(
                    $pdo,
                    $progressId,
                    'info',
                    count($unresolvedFromPreValidation) . ' team(s) have no candidate slots under the current filters; continuing in ' . $validationMode . ' mode with partial scheduling enabled.',
                    33
                );
                error_log('PRE-FILTER WARNING: unresolved teams kept in ' . $validationMode . ' mode: ' . implode(',', $unresolvedFromPreValidation));
            }
        }

        if (empty($teams)) {
            $payload = buildValidationFailurePayload($pdo, $validationMode, $validationSummary, $validationIssues);
            $msgNoRunnable = 'No schedulable teams remain after conflict pre-filtering. Widen dates/rooms or enable overlap exceptions for research classes where appropriate.';
            updateProgress($pdo, $progressId, 'error', $msgNoRunnable, null);
            if (ob_get_level()) {
                ob_end_clean();
            }
            echo json_encode(array_merge([
                'success' => false,
                'message' => $msgNoRunnable,
                'unresolved_team_ids' => $unresolvedFromPreValidation,
            ], $payload));
            exit;
        }

        // Validate input parameters
        if (empty($teams) || empty($panelists)) {
            updateProgress($pdo, $progressId, 'error', 'No teams or panelists available for scheduling', null);
            throw new Exception("No teams or panelists available for scheduling");
        }

        error_log("SCHEDULER: About to generate schedules for " . count($teams) . " teams");

        updateProgress($pdo, $progressId, 'running', 'Starting genetic algorithm optimization from valid candidate pool...', 35);

        // Optimize parameters for better performance-quality balance
        // REDUCED for faster execution to prevent timeout
        $populationSize = 50;      // Reduced from 200 for faster execution
        $generations = 100;        // Reduced from 500 for faster execution  
        $mutationRate = 0.2;
        $earlyStopGenerations = 50; // Stop if no improvements after 50 generations
        
        error_log("Genetic Algorithm Parameters: Population=$populationSize, Generations=$generations, EarlyStop=$earlyStopGenerations");

        $geneticOutcome = geneticAlgorithm(
            $pdo,
            $teams,
            $panelists,
            $rooms,
            $slotsByTeamDay,
            $eligibleDaysByTeam,
            $userSchedules,
            $populationSize,
            $generations,
            $mutationRate,
            $earlyStopGenerations,
            $progressId, // Pass progress ID for tracking
            $validCandidatePool
        );
        $gaAlternates = [];
        if (is_array($geneticOutcome) && isset($geneticOutcome['best'])) {
            $bestSchedule = $geneticOutcome['best'];
            $gaAlternates = is_array($geneticOutcome['alternates'] ?? null) ? $geneticOutcome['alternates'] : [];
        } else {
            // Backward-compatible single return value
            $bestSchedule = $geneticOutcome;
        }

        error_log('Genetic Algorithm completed successfully (alternate candidates: ' . count($gaAlternates) . ')');

        // === POST-GA VALIDATION: Double-check for overlapping schedules ===
        updateProgress($pdo, $progressId, 'running', 'Post-GA validation: defense-defense overlaps, faculty/section classes, rooms…', 87);
        $validationResult = validateAndFixOverlaps($pdo, $bestSchedule, $userSchedules, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $panelists);
        $bestSchedule = $validationResult['schedule'];
        $overlapIssues = $validationResult['issues'];
        $overlapFixes = $validationResult['fixes'];
        $remainingConflicts = $validationResult['remainingConflicts'];

        if ($remainingConflicts > 0 && !empty($gaAlternates)) {
            foreach ($gaAlternates as $altSchedule) {
                $workingAlt = copyDefenseScheduleForPostProcess($altSchedule);
                $altVal = validateAndFixOverlaps($pdo, $workingAlt, $userSchedules, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $panelists);
                if ((int) $altVal['remainingConflicts'] === 0) {
                    $bestSchedule = $altVal['schedule'];
                    $overlapIssues = $altVal['issues'];
                    $overlapFixes = $altVal['fixes'];
                    $remainingConflicts = 0;
                    error_log('POST-GA: Switched primary schedule to alternate GA candidate (zero remaining conflicts)');
                    break;
                }
            }
        }

        if (!empty($overlapIssues)) {
            error_log("POST-GA VALIDATION: Found " . count($overlapIssues) . " overlap issues, applied $overlapFixes fixes, remaining: $remainingConflicts");
        } else {
            error_log("POST-GA VALIDATION: No overlap issues found - schedule is clean");
        }

        // Check if this is a preview request
        $isPreview = isset($_POST['preview']) && $_POST['preview'] === 'true';

        $candidateVariants = [];
        $candidateVariants[] = [
            'variant_id' => 'A',
            'source' => 'primary',
            'validated' => [
                'schedule' => $bestSchedule,
                'issues' => $overlapIssues,
                'fixes' => $overlapFixes,
                'remainingConflicts' => $remainingConflicts,
            ],
        ];

        $variantLabelOrd = ['B', 'C', 'D', 'E', 'F'];
        $variantOrdIdx = 0;
        foreach ($gaAlternates as $altSchedule) {
            if ($variantOrdIdx >= count($variantLabelOrd)) {
                break;
            }

            $workingCopy = copyDefenseScheduleForPostProcess($altSchedule);
            $altValidation = validateAndFixOverlaps($pdo, $workingCopy, $userSchedules, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $panelists);
            $candidateVariants[] = [
                'variant_id' => $variantLabelOrd[$variantOrdIdx],
                'source' => 'alternate',
                'validated' => $altValidation,
            ];
            $variantOrdIdx++;
        }

        foreach ($candidateVariants as $idx => $variant) {
            $rows = prepareScheduleData($pdo, $variant['validated']['schedule']);
            $quality = computeScheduleQualityMetrics($rows);
            $remaining = (int) ($variant['validated']['remainingConflicts'] ?? 0);
            $fitness = (float) ($variant['validated']['schedule']->fitness ?? 0.0);
            $rankScore = (100000 - ($remaining * 10000)) + ($quality['score'] * 10.0) + ($fitness * 0.01);

            $candidateVariants[$idx]['rows'] = $rows;
            $candidateVariants[$idx]['quality'] = $quality;
            $candidateVariants[$idx]['rank_score'] = $rankScore;
        }

        usort($candidateVariants, static function ($a, $b) {
            return $b['rank_score'] <=> $a['rank_score'];
        });

        $topVariant = $candidateVariants[0] ?? null;
        if ($topVariant !== null) {
            $bestSchedule = $topVariant['validated']['schedule'];
            $overlapIssues = $topVariant['validated']['issues'];
            $overlapFixes = $topVariant['validated']['fixes'];
            $remainingConflicts = (int) $topVariant['validated']['remainingConflicts'];
        }

        $scheduleVariantsPrepared = [];
        if ($isPreview) {
            foreach (array_slice($candidateVariants, 0, 3) as $idx => $variant) {
                $isPrimary = $variant['source'] === 'primary';
                $labelSuffix = $isPrimary ? 'primary result' : 'alternate GA candidate';
                $scheduleVariantsPrepared[] = [
                    'variant_id' => $variant['variant_id'],
                    'label' => 'Option ' . $variant['variant_id'] . ' — ' . $labelSuffix
                        . ' · score ' . number_format((float) ($variant['quality']['score'] ?? 0), 1)
                        . ' · util ' . number_format((float) ($variant['quality']['utilization_pct'] ?? 0), 1) . '%'
                        . ' · gap ' . number_format((float) ($variant['quality']['avg_gap_minutes'] ?? 0), 1) . 'm',
                    'schedules' => $variant['rows'],
                    'overlap_warnings' => $variant['validated']['issues'],
                    'overlap_fixes' => $variant['validated']['fixes'],
                    'remaining_conflicts' => (int) $variant['validated']['remainingConflicts'],
                    'quality' => $variant['quality'],
                    'rank_score' => $variant['rank_score'],
                ];
            }
        }

        if ($remainingConflicts > 0) {
            $conflictFailMsg = 'Cannot produce a strictly conflict-free schedule with the current rooms, dates, times, class timetables, and existing defenses. '
                . 'Broaden availability or resolve overlaps. (' . $remainingConflicts . ' internal conflict marker(s) remain after automated repair.)';
            updateProgress($pdo, $progressId, 'error', $conflictFailMsg, null);
            while (ob_get_level()) {
                ob_end_clean();
            }
            echo json_encode([
                'success' => false,
                'preview' => $isPreview ? true : null,
                'message' => $conflictFailMsg,
                'remaining_conflicts' => $remainingConflicts,
                'overlap_warnings' => array_slice($overlapIssues, 0, 20),
                'candidate_rankings' => array_map(static function ($v) {
                    return [
                        'variant_id' => $v['variant_id'],
                        'rank_score' => $v['rank_score'],
                        'quality' => $v['quality'],
                        'remaining_conflicts' => (int) ($v['validated']['remainingConflicts'] ?? 0),
                    ];
                }, array_slice($candidateVariants, 0, 3)),
            ]);
            exit;
        }

        $acceptedRowsForResolution = $isPreview
            ? ($scheduleVariantsPrepared[0]['schedules'] ?? [])
            : prepareScheduleData($pdo, $bestSchedule);
        $scheduledTeamIds = [];
        foreach ($acceptedRowsForResolution as $row) {
            $tidRow = (int) ($row['team_id'] ?? 0);
            if ($tidRow > 0) {
                $scheduledTeamIds[] = $tidRow;
            }
        }
        $scheduledTeamIds = array_values(array_unique($scheduledTeamIds));
        $unresolvedTeamIds = array_values(array_unique(array_merge(
            $unresolvedFromPreValidation,
            array_values(array_diff($requestedScopeTeamIds, $scheduledTeamIds))
        )));

        if ($isPreview && !empty($scheduleVariantsPrepared)) {
            $validPreviewVariants = [];
            $classGateFailures = [];
            foreach ($scheduleVariantsPrepared as $idx => $variant) {
                $classGate = validatePreviewScheduleRowsAgainstClasses($pdo, $variant['schedules'], $userSchedules, $duration);
                if (!empty($classGate)) {
                    $classGateFailures[] = [
                        'variant_id' => $variant['variant_id'] ?? ('#' . $idx),
                        'errors' => $classGate,
                    ];
                    error_log('CLASS-GATE DROP: variant ' . ($variant['variant_id'] ?? $idx) . ' rejected: ' . implode(' | ', array_slice($classGate, 0, 3)));
                    continue;
                }
                $validPreviewVariants[] = $variant;
            }

            if (empty($validPreviewVariants)) {
                $firstFailure = $classGateFailures[0]['errors'] ?? [];
                $msg = 'All generated layout options failed final class/faculty/venue checks. '
                    . implode(' | ', array_slice($firstFailure, 0, 4));
                updateProgress($pdo, $progressId, 'error', $msg, null);
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo json_encode([
                    'success' => false,
                    'preview' => true,
                    'message' => $msg,
                    'class_gate_failures' => $classGateFailures,
                ]);
                exit;
            }

            $scheduleVariantsPrepared = array_values($validPreviewVariants);
        } elseif (!$isPreview) {
            $gateRows = $acceptedRowsForResolution;
            $classGate = validatePreviewScheduleRowsAgainstClasses($pdo, $gateRows, $userSchedules, $duration);
            if (!empty($classGate)) {
                $msg = 'Schedule failed final class/faculty/venue checks before save: ' . implode(' | ', array_slice($classGate, 0, 4));
                updateProgress($pdo, $progressId, 'error', $msg, null);
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo json_encode([
                    'success' => false,
                    'message' => $msg,
                    'class_gate_errors' => $classGate,
                ]);
                exit;
            }
        }

        if ($isPreview) {
            $acceptedRowsForResolution = $scheduleVariantsPrepared[0]['schedules'] ?? [];
        }
        $scheduledTeamIds = [];
        foreach ($acceptedRowsForResolution as $row) {
            $tidRow = (int) ($row['team_id'] ?? 0);
            if ($tidRow > 0) {
                $scheduledTeamIds[] = $tidRow;
            }
        }
        $scheduledTeamIds = array_values(array_unique($scheduledTeamIds));
        $unresolvedTeamIds = array_values(array_unique(array_merge(
            $unresolvedFromPreValidation,
            array_values(array_diff($requestedScopeTeamIds, $scheduledTeamIds))
        )));

        if ($isPreview) {
            // Preview mode: prepare data without saving
            updateProgress($pdo, $progressId, 'running', 'Preparing schedule preview with class overlay and schedule alternatives…', 90);
            error_log("Preparing schedule preview (not saving to DB)");

            $previewData = $scheduleVariantsPrepared[0]['schedules'] ?? prepareScheduleData($pdo, $bestSchedule);
            $repairNotes = count($overlapIssues);
            $previewMessage = 'Preview ready — conflict-free after validation' . ($repairNotes ? ' (' . $repairNotes . ' automatic slot move(s) were applied during repair).' : '.');
            if (count($scheduleVariantsPrepared) > 1) {
                $previewMessage .= ' ' . count($scheduleVariantsPrepared) . ' layout option(s): use the dropdown to compare.';
            }
            updateProgress($pdo, $progressId, 'completed', $previewMessage, 100);

            $result = [
                'success' => true,
                'preview' => true,
                'progressId' => $progressId,
                'schedules' => $previewData,
                'schedule_variants' => $scheduleVariantsPrepared,
                'overlapWarnings' => $overlapIssues,
                'overlapFixes' => $overlapFixes,
                'remaining_conflicts' => 0,
                'validationMode' => $validationMode,
                'validationSummary' => $validationSummary,
                'validationCounts' => summarizeValidationIssues($validationIssues),
                'accepted_schedules' => $previewData,
                'accepted_team_ids' => $scheduledTeamIds,
                'unresolved_team_ids' => $unresolvedTeamIds,
                'is_partial' => !empty($unresolvedTeamIds),
                'message' => 'Schedule preview is conflict-free. Review then confirm to save.',
            ];
            ob_end_clean(); // Discard any buffered output (PHP warnings etc.)
            $json = json_encode($result);
            if ($json === false) {
                error_log('JSON encode error: ' . json_last_error_msg());
                echo json_encode(['success' => false, 'message' => 'Failed to encode schedule data: ' . json_last_error_msg()]);
            } else {
                error_log('Preview JSON response length: ' . strlen($json));
                echo $json;
            }
        } else {
            // Original flow: save immediately
            updateProgress($pdo, $progressId, 'running', 'Saving schedule to database...', 90);
            error_log("About to save schedule to database");

            if (saveScheduleToDatabase($pdo, $bestSchedule)) {
                $completionMessage = 'Schedule generated and saved successfully (conflict-free).';
                updateProgress($pdo, $progressId, 'completed', $completionMessage, 100);
                $result = [
                    'success' => true,
                    'progressId' => $progressId,
                    'initialPopulationSize' => count(DefenseSchedule::$initialPopulation),
                    'crossoverCount' => DefenseSchedule::$crossoverCount,
                    'mutationCount' => DefenseSchedule::$mutationCount,
                    'conflictCounts' => DefenseSchedule::$averageConflictCounts,
                    'fitnessScores' => DefenseSchedule::$averageFitnessScores,
                    'populationPerGeneration' => DefenseSchedule::$populationPerGeneration,
                    'overwrittenTeams' => !empty($scheduledTeams) ? count($scheduledTeams) : 0,
                    'overlapWarnings' => $overlapIssues,
                    'overlapFixes' => $overlapFixes,
                    'remaining_conflicts' => 0,
                    'validationMode' => $validationMode,
                    'validationSummary' => $validationSummary,
                    'validationCounts' => summarizeValidationIssues($validationIssues),
                    'accepted_team_ids' => $scheduledTeamIds,
                    'unresolved_team_ids' => $unresolvedTeamIds,
                    'is_partial' => !empty($unresolvedTeamIds),
                    'message' => $completionMessage,
                ];

                file_put_contents('schedule_data.json', json_encode($result));
                ob_end_clean(); // Discard any buffered output
                echo json_encode($result);
            } else {
                throw new Exception("Failed to save schedule to database");
            }
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log("ERROR IN SCHEDULER: " . $e->getMessage());
    error_log("ERROR LOCATION: " . $e->getFile() . " line " . $e->getLine());
    error_log("STACK TRACE: \n" . $e->getTraceAsString());
    if (ob_get_level()) ob_end_clean(); // Clean any buffered output
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

/**
 * Extract HH:MM or HH:MM:SS from user_schedules / defense rows (handles DATETIME strings).
 */
function scheduler_time_fragment_from_db($clock): string
{
    $s = trim((string) $clock);
    if ($s === '') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}\s+(\d{1,2}:\d{2}(?::\d{2})?)/', $s, $m)) {
        return $m[1];
    }
    if (preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)/', $s, $m)) {
        return $m[1];
    }

    return '';
}

function scheduler_normalize_hms(string $frag): string
{
    $frag = trim($frag);
    if ($frag === '') {
        return '00:00:00';
    }
    $parts = explode(':', $frag);
    $h = isset($parts[0]) ? (int) trim($parts[0]) : 0;
    $m = isset($parts[1]) ? (int) trim($parts[1]) : 0;
    $sec = isset($parts[2]) ? (int) trim($parts[2]) : 0;

    return sprintf('%02d:%02d:%02d', max(0, min(23, $h)), max(0, min(59, $m)), max(0, min(59, $sec)));
}

function scheduler_calendar_day_from_raw($dayRaw): ?string
{
    $ts = strtotime(trim((string) $dayRaw));

    return $ts !== false ? date('Y-m-d', $ts) : null;
}

function scheduler_unix_on_calendar_day(string $dayYmd, $clock): int|false
{
    $dayYmd = trim($dayYmd);
    $fragRaw = scheduler_time_fragment_from_db($clock);
    if ($dayYmd === '' || $fragRaw === '') {
        return false;
    }

    return strtotime($dayYmd . ' ' . scheduler_normalize_hms($fragRaw));
}

/** Defense interval on a specific calendar date (fixes mixing DATETIME class rows with bare time-slot defenses). */
function scheduler_defense_range_on_day(?string $defenseDayRaw, string $timeSlot, $durationHours): ?array
{
    $ymd = scheduler_calendar_day_from_raw((string) $defenseDayRaw);
    if ($ymd === null) {
        return null;
    }
    $start = scheduler_unix_on_calendar_day($ymd, $timeSlot);
    if ($start === false) {
        return null;
    }
    $end = strtotime('+' . (float) $durationHours . ' hour', $start);

    return $end !== false ? ['start' => $start, 'end' => $end] : null;
}

function scheduler_user_class_range_on_calendar_day(?string $defenseDayRaw, array $schedRow): ?array
{
    $ymd = scheduler_calendar_day_from_raw((string) $defenseDayRaw);
    if ($ymd === null) {
        return null;
    }
    if ((int) date('w', strtotime($ymd)) !== (int) ($schedRow['day_of_week'] ?? -999)) {
        return null;
    }
    $schedStart = scheduler_unix_on_calendar_day($ymd, $schedRow['start_time'] ?? '');
    $schedEnd = scheduler_unix_on_calendar_day($ymd, $schedRow['end_time'] ?? '');
    if ($schedStart === false || $schedEnd === false) {
        return null;
    }

    return ['start' => $schedStart, 'end' => $schedEnd];
}

function scheduler_slot_ranges_overlap(int $sa, int $ea, int $sb, int $eb): bool
{
    return ($sa < $eb) && ($ea > $sb);
}

// New function to validate all inputs
function validateInputs() {
    // Check for required fields
    $requiredFields = ['timeDuration', 'rooms', 'timeSlots', 'days'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("Missing required field: " . $field);
            return false;
        }
    }
    
    // Validate time duration is a positive number
    if (!is_numeric($_POST['timeDuration']) || floatval($_POST['timeDuration']) <= 0) {
        error_log("Invalid time duration: " . $_POST['timeDuration']);
        return false;
    }
    
    // Validate rooms array
    if (!is_array($_POST['rooms']) || empty($_POST['rooms'])) {
        error_log("Invalid rooms array");
        return false;
    }
    
    // Validate timeSlots array
    if (!is_array($_POST['timeSlots']) || empty($_POST['timeSlots'])) {
        error_log("Invalid timeSlots array");
        return false;
    }
    
    // Validate days array
    if (!is_array($_POST['days']) || empty($_POST['days'])) {
        error_log("Invalid days array");
        return false;
    }
    
    return true;
}

function parseSchedulerTeamIdList($raw): array
{
    if (is_string($raw)) {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }
        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $raw = $decoded;
        } else {
            $raw = preg_split('/\s*,\s*/', $trimmed);
        }
    }

    if (!is_array($raw)) {
        return [];
    }

    $ids = [];
    foreach ($raw as $value) {
        $id = (int) $value;
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return array_values(array_unique($ids));
}

/**
 * Drop start times where defense would end after latestClock (default 20:30), matching GA hard cap.
 *
 * @return list<string>
 */
function filter_time_slots_respecting_latest_end(array $slots, float $durationHours, string $latestClock = '20:30'): array
{
    $out = [];
    $baseAnchor = '2000-06-07'; // fixed weekday anchor for strtotime
    foreach ($slots as $slot) {
        $slot = trim((string) $slot);
        if ($slot === '') {
            continue;
        }
        $start = strtotime($baseAnchor . ' ' . $slot);
        if ($start === false) {
            continue;
        }
        $end = strtotime('+' . $durationHours . ' hours', $start);
        $cap = strtotime($baseAnchor . ' ' . $latestClock);
        if ($end !== false && $cap !== false && $end <= $cap) {
            $out[] = date('H:i', $start);
        }
    }

    return array_values(array_unique($out));
}

/** Start times for one team/calendar-day that pass validateCandidateSlot for at least one room */
function scheduler_slots_for_team_day(?array $slotsByTeamDay, $teamId, string $day): array
{
    $teamKey = (int) $teamId;
    if ($slotsByTeamDay === null || !isset($slotsByTeamDay[$teamKey][$day]) || !is_array($slotsByTeamDay[$teamKey][$day])) {
        return [];
    }

    return array_values(array_filter($slotsByTeamDay[$teamKey][$day], static fn ($t) => trim((string) $t) !== ''));
}

// Check existing schedules with detailed information (date, status, defense_type)
function checkExistingSchedules($pdo, $sections = []) {
    try {
        $query = "SELECT ds.*, t.name AS team_name,
                         ds.schedule_date AS defense_date,
                         ds.defense_status,
                         ds.defense_type,
                         ds.start_time AS time_slot,
                         ds.room
                  FROM defense_schedules ds
                  JOIN teams t ON ds.team_id = t.id
                  JOIN team_members tm ON t.id = tm.team_id
                  JOIN users u ON tm.user_id = u.id
                  WHERE ds.status = 'scheduled'";

        $params = [];
        if (!empty($sections)) {
            $placeholders = implode(',', array_fill(0, count($sections), '?'));
            $query .= " AND u.section IN ($placeholders)";
            $params = $sections;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $scheduledTeams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $scheduledTeams[$row['team_id']] = $row;
        }
        
        error_log("checkExistingSchedules: Found " . count($scheduledTeams) . " teams with existing schedules");
        if (!empty($scheduledTeams)) {
            foreach ($scheduledTeams as $teamId => $schedule) {
                error_log("  - Team $teamId: defense_date={$schedule['defense_date']}, defense_type={$schedule['defense_type']}, defense_status={$schedule['defense_status']}");
            }
        }
        return $scheduledTeams;
    } catch (PDOException $e) {
        error_log("checkExistingSchedules ERROR: " . $e->getMessage());
        return [];
    }
}

// Function to get team names from team IDs
function getTeamNames($pdo, $teamIds) {
    if (empty($teamIds)) return [];
    
    try {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($placeholders)");
        $stmt->execute($teamIds);
        
        $teams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $teams[$row['id']] = $row['name'];
        }
        return $teams;
    } catch (PDOException $e) {
        error_log("Error fetching team names: " . $e->getMessage());
        return [];
    }
}

// Function to remove existing FUTURE schedules for specific teams (preserves past schedules)
function removeExistingSchedules($pdo, $teamIds) {
    if (empty($teamIds)) {
        error_log("removeExistingSchedules: No team IDs provided");
        return;
    }
    
    try {
        $currentDate = date('Y-m-d');
        error_log("removeExistingSchedules: Attempting to remove FUTURE schedules (after $currentDate) for teams: " . implode(', ', $teamIds));
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        
        // Only delete FUTURE schedules that are still pending
        // Never delete past schedules or schedules that are passed/failed
        $stmt = $pdo->prepare("
            DELETE FROM defense_schedules 
            WHERE team_id IN ($placeholders) 
            AND status = 'scheduled' 
            AND schedule_date >= ?
            AND defense_status = 'pending'
        ");
        $params = array_merge($teamIds, [$currentDate]);
        $stmt->execute($params);
        $deletedCount = $stmt->rowCount();
        error_log("removeExistingSchedules: Successfully removed $deletedCount FUTURE schedule(s) for " . count($teamIds) . " team(s)");
    } catch (PDOException $e) {
        error_log("removeExistingSchedules ERROR: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Check if team has complete grades from all panelists
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param int $scheduleId Defense schedule ID
 * @return bool True if all panelists have submitted grades
 */
function teamHasCompleteGrades($pdo, $teamId, $scheduleId) {
    try {
        // Get the schedule with panelists
        $stmt = $pdo->prepare("
            SELECT panelist_id, panelist_id2, panelist_id3
            FROM defense_schedules
            WHERE id = ?
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schedule) {
            error_log("teamHasCompleteGrades: Schedule $scheduleId not found");
            return false;
        }
        
        $panelists = [
            $schedule['panelist_id'],
            $schedule['panelist_id2'],
            $schedule['panelist_id3']
        ];
        
        // Check if all panelists have submitted evaluations
        foreach ($panelists as $panelistId) {
            if (empty($panelistId)) continue;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM evaluations
                WHERE defense_schedule_id = ? AND evaluator_id = ?
            ");
            $stmt->execute([$scheduleId, $panelistId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                error_log("teamHasCompleteGrades: Team $teamId missing evaluation from panelist $panelistId");
                return false;
            }
        }
        
        error_log("teamHasCompleteGrades: Team $teamId has all grades from all panelists");
        return true;
    } catch (PDOException $e) {
        error_log("teamHasCompleteGrades ERROR: " . $e->getMessage());
        return false;
    }
}

/**
 * Handle defense type progression for teams with past schedules
 * - If passed: Move defense type up one level
 *   title_proposal → title_defense → final_defense
 * - If failed: Set as re_defense
 */
function handleDefenseProgression($pdo, $teamsToProgress) {
    $progressedTeams = [];
    
    foreach ($teamsToProgress as $teamId => $data) {
        $oldSchedule = $data['old_schedule'];
        $status = $data['status'];
        $currentDefenseType = $oldSchedule['defense_type'] ?? 'title_proposal';
        
        // Determine new defense type based on status
        if ($status === 'passed') {
            // Progress to next defense level
            switch ($currentDefenseType) {
                case 'title_proposal':
                    $newDefenseType = 'title_defense';
                    break;
                case 'title_defense':
                    $newDefenseType = 'final_defense';
                    break;
                case 'final_defense':
                    // Already at final level, no progression needed
                    error_log("Team $teamId already completed final_defense, skipping progression");
                    continue 2;
                default:
                    $newDefenseType = 'title_defense';
            }
            error_log("Team $teamId passed $currentDefenseType, progressing to $newDefenseType");
        } else {
            // Failed: Create re_defense
            $newDefenseType = 're_defense';
            error_log("Team $teamId failed $currentDefenseType, scheduling re_defense");
        }
        
        // Store the new defense type in team metadata or mark team for re-scheduling
        // The actual schedule will be created by the genetic algorithm
        try {
            // Update team's next defense type (you may need to add this field to teams table)
            // For now, we'll track it in a way that the scheduler can pick it up
            $stmt = $pdo->prepare("
                UPDATE teams 
                SET next_defense_type = ? 
                WHERE id = ?
            ");
            $stmt->execute([$newDefenseType, $teamId]);
            
            $progressedTeams[] = [
                'team_id' => $teamId,
                'old_defense_type' => $currentDefenseType,
                'new_defense_type' => $newDefenseType,
                'status' => $status
            ];
        } catch (PDOException $e) {
            // If column doesn't exist, log and continue
            error_log("Could not update next_defense_type for team $teamId: " . $e->getMessage());
            error_log("Consider adding 'next_defense_type' column to teams table");
        }
    }
    
    return $progressedTeams;
}

// Update fetchTeams to handle multiple sections and respect next_defense_type
// Also includes locked panelists for panelist lock feature
function fetchTeams($pdo, $sections = [])
{
    if (empty($sections)) {
        $stmt = $pdo->query("
            SELECT DISTINCT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise,
                   COALESCE(t.next_defense_type, 'title_proposal') as defense_type,
                   t.locked_panelist1, t.locked_panelist2, t.locked_panelist3
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id AND tm.role = 'adviser'
            WHERE 1=1
        ");
    } else {
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise,
                   COALESCE(t.next_defense_type, 'title_proposal') as defense_type,
                   t.locked_panelist1, t.locked_panelist2, t.locked_panelist3
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id AND tm.role = 'adviser'
            WHERE EXISTS (
                SELECT 1 FROM team_members tm2
                JOIN users u ON tm2.user_id = u.id
                WHERE tm2.team_id = t.id AND u.section IN ($placeholders)
            )
        ");
        $stmt->execute($sections);
    }
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($teams) . " teams for sections: " . (!empty($sections) ? implode(", ", $sections) : 'All Sections'));
    return $teams;
}

function fetchPanelists($pdo)
{
    $stmt = $pdo->query("SELECT id, area_of_expertise, is_parttime FROM users WHERE usertype = 2 OR (usertype = 0 AND id != 0)");
    $panelists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert the result to a more usable format: id => [expertise, is_parttime]
    $formattedPanelists = [];
    foreach ($panelists as $panelist) {
        $formattedPanelists[$panelist['id']] = [
            'expertise' => $panelist['area_of_expertise'],
            'is_parttime' => isset($panelist['is_parttime']) ? $panelist['is_parttime'] : 0
        ];
    }

    return $formattedPanelists;
}

function fetchPanelistsByProgram($pdo, $teamProgram, $teamExpertise, $allPanelists)
{
    $sameProgramPanelists = [];
    $differentProgramPanelists = [];

    // Ensure teamExpertise is a string
    $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';

    foreach ($allPanelists as $panelistId => $panelistData) {
        $panelistInfo = getPanelistData($pdo, $panelistId);
        $expertise = is_array($panelistData) ? ($panelistData['expertise'] ?? '') : '';

        if ($panelistInfo['program'] == $teamProgram) {
            $sameProgramPanelists[] = ['id' => $panelistId, 'expertise' => $expertise];
        } else {
            $differentProgramPanelists[] = ['id' => $panelistId, 'expertise' => $expertise];
        }
    }

    // Sort panelists by expertise similarity
    usort($sameProgramPanelists, function ($a, $b) use ($teamExpertise) {
        $expertiseA = is_string($a['expertise']) ? $a['expertise'] : '';
        $expertiseB = is_string($b['expertise']) ? $b['expertise'] : '';
        return similar_text($teamExpertise, $expertiseB) - similar_text($teamExpertise, $expertiseA);
    });

    usort($differentProgramPanelists, function ($a, $b) use ($teamExpertise) {
        $expertiseA = is_string($a['expertise']) ? $a['expertise'] : '';
        $expertiseB = is_string($b['expertise']) ? $b['expertise'] : '';
        return similar_text($teamExpertise, $expertiseB) - similar_text($teamExpertise, $expertiseA);
    });

    return [
        'same' => array_column($sameProgramPanelists, 'id'),
        'different' => array_column($differentProgramPanelists, 'id')
    ];
}

function getPanelistData($pdo, $panelistId)
{
    static $cache = [];

    if (!isset($cache[$panelistId])) {
        $stmt = $pdo->prepare("SELECT program, area_of_expertise, is_parttime FROM users WHERE id = ?");
        $stmt->execute([$panelistId]);
        $cache[$panelistId] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return $cache[$panelistId];
}

function fetchUserSchedules($pdo)
{
    $overlapCols = userSchedulesHasOverlapExceptionColumns($pdo);
    $allowOverlapSelect = $overlapCols['allow_overlap'] ? 'COALESCE(allow_overlap, 0)' : '0';
    $isResearchClassSelect = $overlapCols['is_research_class'] ? 'COALESCE(is_research_class, 0)' : '0';

    $stmt = $pdo->query("SELECT user_id, day_of_week, start_time, end_time, room, class_name, {$allowOverlapSelect} AS allow_overlap, {$isResearchClassSelect} AS is_research_class FROM user_schedules");
    $schedules = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dowNorm = normalize_user_schedule_day_to_week_int($row['day_of_week'] ?? '');
        if ($dowNorm === null) {
            continue;
        }
        $row['day_of_week'] = $dowNorm;
        $row['allow_overlap'] = (int) ($row['allow_overlap'] ?? 0);
        $row['is_research_class'] = (int) ($row['is_research_class'] ?? 0);
        $schedules[$row['user_id']][] = $row;
    }

    mergeProgramSectionClassTemplatesIntoUserSchedules($pdo, $schedules);

    return $schedules;
}

function geneticAlgorithm(
    $pdo,
    $teams,
    $panelists,
    $rooms,
    array $slotsByTeamDay,
    array $eligibleDaysByTeam,
    $userSchedules,
    $populationSize,
    $generations,
    $mutationRate,
    $earlyStopGenerations = 30,
    $progressId = null,
    $validCandidatePool = []
) {
    // Store teams globally for use in other functions
    $GLOBALS['teams'] = $teams;

    // Pre-compute the theoretical maximum fitness so we can detect a "perfect" candidate
    $teamCount = count($teams);
    // Perfect score per defense: +20 expertise per panelist (3) = 60, no penalties
    // This is an upper-bound estimate; exact value depends on expertise data
    $perfectFitnessEstimate = $teamCount * 60;

    $population = createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $validCandidatePool);
    $existingSchedules = fetchExistingDefenseSchedules($pdo);
    $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);
    DefenseSchedule::$initialPopulation = $population;
    DefenseSchedule::$populationPerGeneration = [];
    DefenseSchedule::$conflictCounts = [];
    DefenseSchedule::$fitnessScores = [];
    DefenseSchedule::$averageConflictCounts = [];
    DefenseSchedule::$averageFitnessScores = [];

    $bestSchedule = null;
    $bestFitness = PHP_INT_MIN;
    $lastImproveGeneration = 0;
    $startTime = microtime(true);
    $perfectFound = false;

    // Elitism: always carry forward the top N best schedules unchanged
    $eliteCount = max(2, intval($populationSize * 0.1)); // top 10%

    for ($i = 0; $i < $generations; $i++) {
        $generationImproved = false;

        // Update progress every 10 generations
        if ($progressId && $i % 10 == 0) {
            $percentage = 35 + (($i / $generations) * 50); // Progress from 35% to 85%
            updateProgress($pdo, $progressId, 'running', "Processing generation " . ($i + 1) . " of $generations (best fitness: $bestFitness)", $percentage);
        }

        // Evaluate fitness for each schedule
        foreach ($population as $schedule) {
            $schedule->calculateFitness($userSchedules);
            if ($schedule->fitness > $bestFitness) {
                $bestFitness = $schedule->fitness;
                $bestSchedule = $schedule;
                $generationImproved = true;
                $lastImproveGeneration = $i;

                // === PERFECT CANDIDATE EARLY STOP ===
                // A perfect candidate has zero conflicts (fitness >= 0) and
                // fitness is at or above our estimated theoretical max
                if ($bestFitness >= $perfectFitnessEstimate && $bestFitness >= 0) {
                    error_log("PERFECT candidate found at generation $i with fitness $bestFitness (threshold: $perfectFitnessEstimate). Stopping early.");
                    $perfectFound = true;
                    break;
                }
            }
        }

        if ($perfectFound) {
            break; // Exit outer loop
        }

        // Also stop early if fitness >= 0 (no conflicts) and we've run enough gens
        if ($bestFitness >= 0 && $i >= 10) {
            error_log("Zero-conflict schedule found at generation $i with fitness $bestFitness. Stopping early.");
            break;
        }

        // Early stopping if no improvement for several generations
        if ($i - $lastImproveGeneration >= $earlyStopGenerations) {
            error_log("Early stopping at generation $i - no improvement for $earlyStopGenerations generations");
            break;
        }

        // Only store data every 5 generations to reduce memory usage
        if ($i % 5 == 0) {
            // Track population data for the current generation
            DefenseSchedule::$populationPerGeneration[] = array_map(function ($schedule) {
                return [
                    'fitness' => $schedule->fitness,
                    'chromosomes' => [] // Don't store full chromosomes to save memory
                ];
            }, $population);

            // Store metrics for this generation
            DefenseSchedule::$averageConflictCounts[] = array_sum(array_map(function ($schedule) {
                return $schedule->fitness < 0 ? 1 : 0;
            }, $population)) / $populationSize;

            DefenseSchedule::$averageFitnessScores[] = array_sum(array_column($population, 'fitness')) / $populationSize;
        }

        // Log progress every 20 generations
        if ($i % 20 == 0) {
            $elapsedTime = microtime(true) - $startTime;
            error_log("Generation $i completed. Best fitness: $bestFitness. Elapsed time: " . round($elapsedTime, 2) . "s");
        }

        // === ELITISM + TOURNAMENT SELECTION ===
        // Sort population to pick elites
        usort($population, function ($a, $b) {
            return $b->fitness - $a->fitness;
        });
        $elites = array_slice($population, 0, $eliteCount);

        // Tournament selection for parents (faster than sorting for selection)
        $selected = tournamentSelection($population, intval($populationSize / 2), 3);

        // Create new population starting with elites
        $newPopulation = $elites;

        // Fill rest with crossover children
        $childrenToCreate = $populationSize - count($newPopulation);
        $selectedCount = count($selected);
        for ($c = 0; $c < $childrenToCreate; $c++) {
            $parent1 = $selected[mt_rand(0, $selectedCount - 1)];
            $parent2 = $selected[mt_rand(0, $selectedCount - 1)];
            $child = crossover($parent1, $parent2, $userSchedules, $slotsByTeamDay, $eligibleDaysByTeam, $rooms, $panelists, $validCandidatePool);

            // Only mutate some children to save time
            if (mt_rand(0, 1) == 1) {
                mutation($child, $mutationRate, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $userSchedules, $validCandidatePool);
            }

            $newPopulation[] = $child;

            // Break early if we have enough children
            if (count($newPopulation) >= $populationSize) {
                break;
            }
        }

        // Only apply diversity preservation occasionally (and less aggressively)
        if ($i % 15 == 0 && $i > 0) {
            $newPopulation = diversityPreservation($newPopulation, $populationSize, $pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
        }

        $population = $newPopulation;

        // Dynamic Mutation Rate Adjustment every few generations
        if ($i % 5 == 0) {
            $mutationRate = adjustMutationRate($mutationRate, $population);
        }
    }

    if ($bestSchedule === null) {
        usort($population, function ($a, $b) {
            return $b->fitness - $a->fitness;
        });
        $bestSchedule = $population[0];
    }

    foreach ($population as $finalizeFitness) {
        $finalizeFitness->calculateFitness($userSchedules);
    }
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });
    $bestSchedule = $population[0];

    $chromosomeSig = static function ($sched) {
        $parts = [];
        foreach ($sched->chromosomes as $row) {
            $p = implode('-', isset($row['panelist_ids']) && is_array($row['panelist_ids']) ? $row['panelist_ids'] : []);
            $parts[] = ($row['team_id'] ?? '')
                . '|' . ($row['day'] ?? '')
                . '|' . ($row['time_slot'] ?? '')
                . '|' . ($row['room'] ?? '')
                . '|' . $p;
        }
        sort($parts);

        return sha1(implode(';', $parts));
    };

    $bestSig = $chromosomeSig($bestSchedule);
    $signatureSeen = [$bestSig => true];
    $alternateSchedules = [];
    foreach (array_slice($population, 1) as $candidateSol) {
        $sigCandidate = $chromosomeSig($candidateSol);
        if (isset($signatureSeen[$sigCandidate])) {
            continue;
        }
        $signatureSeen[$sigCandidate] = true;
        $alternateSchedules[] = copyDefenseScheduleForPostProcess($candidateSol);
        if (count($alternateSchedules) >= 5) {
            break;
        }
    }

    $totalTime = microtime(true) - $startTime;
    error_log(
        'Genetic algorithm completed in '
        . round($totalTime, 2)
        . ' seconds after '
        . min(isset($i) ? $i + 1 : $generations, $generations)
        . ' generations — best fitness: '
        . $bestSchedule->fitness
        . ', queued alternates: '
        . count($alternateSchedules)
    );

    return [
        'best' => $bestSchedule,
        'alternates' => $alternateSchedules,
    ];
}

function createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $validCandidatePool = [])
{
    $population = [];
    for ($i = 0; $i < $populationSize; $i++) {
        $schedule = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
        if (!empty($validCandidatePool)) {
            foreach ($schedule->chromosomes as $index => $defense) {
                $candidate = chooseCandidateFromPool($validCandidatePool, $defense['team_id'], $defense);
                if ($candidate !== null) {
                    $schedule->chromosomes[$index] = $candidate;
                    $schedule->all_defenses[$index] = $candidate;
                }
            }
        }
        $population[] = $schedule;
    }
    return $population;
}

function selection($population)
{
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });
    return array_slice($population, 0, count($population) / 2);
}

/**
 * Tournament selection - faster than sorting entire population.
 * Picks `numWinners` individuals by running tournaments of `tournamentSize`.
 */
function tournamentSelection($population, $numWinners, $tournamentSize = 3)
{
    $popSize = count($population);
    if ($popSize === 0) return [];
    $winners = [];
    for ($i = 0; $i < $numWinners; $i++) {
        $best = null;
        for ($t = 0; $t < $tournamentSize; $t++) {
            $candidate = $population[mt_rand(0, $popSize - 1)];
            if ($best === null || $candidate->fitness > $best->fitness) {
                $best = $candidate;
            }
        }
        $winners[] = $best;
    }
    return $winners;
}

function crossover($parent1, $parent2, $userSchedules, array $slotsByTeamDay, array $eligibleDaysByTeam, $rooms, $panelists, $validCandidatePool = [])
{
    $child = new DefenseSchedule($parent1->pdo, [], [], $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
    $crossoverPoint = rand(0, count($parent1->chromosomes) - 1);
    $child->chromosomes = array_merge(
        array_slice($parent1->chromosomes, 0, $crossoverPoint),
        array_slice($parent2->chromosomes, $crossoverPoint)
    );
    $child->all_defenses = $child->chromosomes;

    foreach ($child->chromosomes as &$defense) {
        $attempts = 0;
        $maxAttempts = 100;
        while (hasConflicts($parent1->pdo, $defense, $userSchedules, $child->all_defenses) && $attempts < $maxAttempts) {
            if (!empty($validCandidatePool)) {
                $candidate = chooseCandidateFromPool($validCandidatePool, $defense['team_id']);
                if ($candidate !== null && empty(validateCandidateSlot($parent1->pdo, $candidate, $userSchedules, fetchExistingDefenseSchedules($parent1->pdo), buildRoomOccupancyMap($userSchedules), $GLOBALS['timeDuration'])) && !hasConflicts($parent1->pdo, $candidate, $userSchedules, $child->all_defenses)) {
                    $defense = $candidate;
                    break;
                }
            }

            $tid = (int) $defense['team_id'];
            $eligible = $eligibleDaysByTeam[$tid] ?? [];
            
            // Try to pick a new day with valid slots
            $dayToUse = $defense['day'];
            if (!empty($eligible)) {
                // Try eligible days until we find one with slots
                $eligibleShuffled = $eligible;
                shuffle($eligibleShuffled);
                foreach ($eligibleShuffled as $candidateDay) {
                    $slotsForDay = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $candidateDay);
                    if (!empty($slotsForDay)) {
                        $dayToUse = $candidateDay;
                        break;
                    }
                }
            }
            
            $defense['day'] = $dayToUse;
            $slots = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $defense['day']);
            if (empty($slots)) {
                // No valid slots for this team on any eligible day; repair attempt failed
                error_log("Crossover: team $tid has no valid slots even on eligible days; repair failed");
                $attempts = $maxAttempts; // Force exit of repair loop
                break;
            }
            $defense['time_slot'] = $slots[array_rand($slots)];
            $defense['room'] = $rooms[array_rand($rooms)];

            $team = fetchTeamById($parent1->pdo, $defense['team_id']);
            $panelistsByProgram = fetchPanelistsByProgram($parent1->pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $defense['panelist_ids'] = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id'], $defense['team_id']);

            $attempts++;
        }

        if ($attempts >= $maxAttempts) {
            $child->fitness -= 50;
        }
    }

    DefenseSchedule::$crossoverCount++;
    return $child;
}

function mutation($schedule, $mutationRate, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $userSchedules, $validCandidatePool = [])
{
    foreach ($schedule->chromosomes as $index => &$defense) {
        if (rand() / getrandmax() < $mutationRate) {
            if (!empty($validCandidatePool)) {
                $candidate = chooseCandidateFromPool($validCandidatePool, $defense['team_id']);
                if ($candidate !== null && empty(validateCandidateSlot($schedule->pdo, $candidate, $userSchedules, fetchExistingDefenseSchedules($schedule->pdo), buildRoomOccupancyMap($userSchedules), $GLOBALS['timeDuration'])) && !hasConflicts($schedule->pdo, $candidate, $userSchedules, $schedule->all_defenses)) {
                    $original = $defense;
                    $defense = $candidate;
                    if (hasConflicts($schedule->pdo, $defense, $userSchedules, $schedule->all_defenses)) {
                        $defense = $original;
                    } else {
                        $schedule->all_defenses[$index] = $defense;
                        DefenseSchedule::$mutationCount++;
                    }
                    continue;
                }
            }

            $mutationType = rand(0, 3);
            $original = $defense;
            switch ($mutationType) {
                case 0:
                    $team = fetchTeamById($schedule->pdo, $defense['team_id']);
                    $panelistsByProgram = fetchPanelistsByProgram($schedule->pdo, $team['program'], $team['area_of_expertise'], $panelists);
                    $defense['panelist_ids'] = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id'], $defense['team_id']);
                    break;
                case 1:
                    $defense['room'] = $rooms[array_rand($rooms)];
                    break;
                case 2:
                    $slotsMut = scheduler_slots_for_team_day($slotsByTeamDay, $defense['team_id'], $defense['day']);
                    if (!empty($slotsMut)) {
                        $defense['time_slot'] = $slotsMut[array_rand($slotsMut)];
                    }
                    break;
                case 3:
                    $tidMut = (int) $defense['team_id'];
                    $eligibleMut = $eligibleDaysByTeam[$tidMut] ?? [];
                    if (!empty($eligibleMut)) {
                        $defense['day'] = $eligibleMut[array_rand($eligibleMut)];
                    }
                    $slotsDay = scheduler_slots_for_team_day($slotsByTeamDay, $tidMut, $defense['day']);
                    if (!empty($slotsDay)) {
                        $defense['time_slot'] = $slotsDay[array_rand($slotsDay)];
                    }
                    break;
            }
            if (hasConflicts($schedule->pdo, $defense, $userSchedules, $schedule->all_defenses)) {
                $defense = $original;
            } else {
                $schedule->all_defenses[$index] = $defense;
                DefenseSchedule::$mutationCount++;
            }
        }
    }
}

function hasConflicts($pdo, $defense, $userSchedules, $all_defenses)
{
    static $conflictCache = [];

    // Faster cache key: avoid expensive json_encode + md5
    $key = $defense['team_id'] . '|' . $defense['day'] . '|' . $defense['time_slot'] . '|' . $defense['room'] . '|' . implode(',', $defense['panelist_ids']);

    if (isset($conflictCache[$key])) {
        return $conflictCache[$key];
    }

    // Check panelist conflicts (pass currentTeamId so we skip self)
    foreach ($defense['panelist_ids'] as $panelist_id) {
        if (hasScheduleConflict($pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses, $defense['team_id'])) {
            $conflictCache[$key] = true;
            return true;
        }
    }

    // Cache team members to avoid repeated queries
    static $teamMembersCache = [];

    // Check conflicts with team members
    if (!isset($teamMembersCache[$defense['team_id']])) {
        $teamMembersCache[$defense['team_id']] = getTeamMembers($pdo, $defense['team_id'], 'array');
    }

    foreach ($teamMembersCache[$defense['team_id']] as $member) {
        if (hasScheduleConflict($pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses, $defense['team_id'])) {
            $conflictCache[$key] = true;
            return true;
        }
    }

    $conflictCache[$key] = false;
    return false;
}

function hasScheduleConflict($pdo, $user_id, $day, $time_slot, $userSchedules, $room, $all_defenses, $currentTeamId = null)
{
    $duration = $GLOBALS['timeDuration'];
    $myDay = scheduler_calendar_day_from_raw((string) $day);
    $defRange = scheduler_defense_range_on_day((string) $day, (string) $time_slot, $duration);
    if ($myDay === null || $defRange === null) {
        return false;
    }

    $defense_start = $defRange['start'];
    $defense_end = $defRange['end'];

    // CHECK 1: User personal schedule (classes) vs this defense
    if (isset($userSchedules[$user_id])) {
        foreach ($userSchedules[$user_id] as $schedule) {
            $sch = scheduler_user_class_range_on_calendar_day($myDay, $schedule);
            if ($sch === null) {
                continue;
            }
            if (scheduler_slot_ranges_overlap($defense_start, $defense_end, $sch['start'], $sch['end'])) {
                return true;
            }
        }
    }

    // CHECK 2: Room conflicts AND user double-booking across ALL defenses
    if (is_array($all_defenses)) {
        // Cache team members for panelist/member lookup
        static $memberLookupCache = [];

        foreach ($all_defenses as $existing_defense) {
            // Skip self (same team)
            if ($currentTeamId !== null && $existing_defense['team_id'] == $currentTeamId) continue;

            $theirDay = scheduler_calendar_day_from_raw((string) ($existing_defense['day'] ?? ''));
            if ($theirDay === null || $theirDay !== $myDay) {
                continue;
            }

            $exRange = scheduler_defense_range_on_day((string) $existing_defense['day'], (string) $existing_defense['time_slot'], $duration);
            if ($exRange === null) {
                continue;
            }

            // Check time overlap
            $timesOverlap = scheduler_slot_ranges_overlap($defense_start, $defense_end, $exRange['start'], $exRange['end']);
            if (!$timesOverlap) continue;

            // 2a: Same room at overlapping time = room conflict
            if ($existing_defense['room'] == $room) {
                return true;
            }

            // 2b: Is this user assigned as panelist in the other defense? (panelist double-booking)
            if (in_array($user_id, $existing_defense['panelist_ids'])) {
                return true;
            }

            // 2c: Is this user a team member in the other defense? (student/adviser double-booking)
            if (!isset($memberLookupCache[$existing_defense['team_id']])) {
                $memberLookupCache[$existing_defense['team_id']] = array_column(
                    getTeamMembers($pdo, $existing_defense['team_id'], 'array'), 'id'
                );
            }
            if (in_array($user_id, $memberLookupCache[$existing_defense['team_id']])) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Prepare schedule data for preview without saving to DB.
 * Returns array of schedule objects with resolved names.
 */
function prepareScheduleData($pdo, $schedule)
{
    $expectedTeams = [];
    foreach ($GLOBALS['teams'] as $team) {
        $expectedTeams[] = $team['id'];
    }

    $scheduledTeams = [];
    $previewSchedules = [];

    $defenses = $schedule->chromosomes;
    foreach ($defenses as &$defense) {
        $defense['fitness'] = calculateDefenseFitness($pdo, $defense);
    }
    unset($defense);

    $byBestTeam = [];
    foreach ($defenses as $defense) {
        $tid = (int) ($defense['team_id'] ?? 0);
        if ($tid <= 0) {
            continue;
        }
        $fit = isset($defense['fitness']) ? (float) $defense['fitness'] : 0.0;
        $prevFit = isset($byBestTeam[$tid]['fitness']) ? (float) $byBestTeam[$tid]['fitness'] : -PHP_INT_MAX;
        if (!isset($byBestTeam[$tid]) || $fit > $prevFit) {
            $byBestTeam[$tid] = $defense;
        }
    }
    $defenses = array_values($byBestTeam);

    usort($defenses, function ($a, $b) {
        return $b['fitness'] <=> $a['fitness'];
    });

    // Prepare name-resolution statements
    $teamStmt = $pdo->prepare("SELECT t.name AS team_name, rt.title AS thesis_title FROM teams t LEFT JOIN research_titles rt ON t.id = rt.team_id WHERE t.id = ?");
    $userStmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE id = ?");
    $adviserStmt = $pdo->prepare("
        SELECT CONCAT(u.first_name, ' ', u.last_name) AS full_name
        FROM team_members tm JOIN users u ON tm.user_id = u.id
        WHERE tm.team_id = ? AND tm.role = 'adviser' LIMIT 1
    ");

    // First pass
    foreach ($defenses as $defense) {
        if (in_array($defense['team_id'], $scheduledTeams)) continue;
        if (!isset($defense['panelist_ids']) || count($defense['panelist_ids']) < 3) continue;

        $scheduledTeams[] = $defense['team_id'];
        $dateObj = parseDate($defense['day']);
        $date = $dateObj->format('Y-m-d');
        $startTime = new DateTime($defense['time_slot']);
        $endTime = clone $startTime;
        $endTime->modify('+' . $GLOBALS['timeDuration'] . ' hour');
        $defenseType = $defense['defense_type'] ?? 'title_proposal';

        // Resolve names
        $teamStmt->execute([$defense['team_id']]);
        $teamInfo = $teamStmt->fetch(PDO::FETCH_ASSOC);

        $panelistNames = [];
        foreach ($defense['panelist_ids'] as $pid) {
            $userStmt->execute([$pid]);
            $u = $userStmt->fetch(PDO::FETCH_ASSOC);
            $panelistNames[] = $u ? $u['full_name'] : 'Unknown';
        }

        $adviserStmt->execute([$defense['team_id']]);
        $advRow = $adviserStmt->fetch(PDO::FETCH_ASSOC);

        $previewSchedules[] = [
            'team_id' => $defense['team_id'],
            'team_name' => $teamInfo['team_name'] ?? 'Unknown',
            'thesis_title' => $teamInfo['thesis_title'] ?? '',
            'adviser' => $advRow['full_name'] ?? 'N/A',
            'panelist_id' => $defense['panelist_ids'][0],
            'panelist_id2' => $defense['panelist_ids'][1],
            'panelist_id3' => $defense['panelist_ids'][2],
            'panelist1_name' => $panelistNames[0],
            'panelist2_name' => $panelistNames[1],
            'panelist3_name' => $panelistNames[2],
            'schedule_date' => $date,
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'room' => $defense['room'],
            'defense_type' => $defenseType,
        ];
    }

    // Second pass - missing teams (same logic as saveScheduleToDatabase)
    $missingTeams = array_diff($expectedTeams, $scheduledTeams);
    if (false && !empty($missingTeams)) {
        foreach ($missingTeams as $missingTeamId) {
            $teamDefense = null;
            foreach ($defenses as $defense) {
                if ($defense['team_id'] == $missingTeamId) { $teamDefense = $defense; break; }
            }
            if (!$teamDefense) {
                $team = null;
                foreach ($GLOBALS['teams'] as $filteredTeam) {
                    if ($filteredTeam['id'] == $missingTeamId) { $team = $filteredTeam; break; }
                }
                if (!$team) continue;

                $eligibleFb = isset($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                    && is_array($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                    && !empty($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                    ? $GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId]
                    : (isset($GLOBALS['schedulerDays']) && is_array($GLOBALS['schedulerDays']) ? $GLOBALS['schedulerDays'] : ($_POST['days'] ?? []));
                $slotMapFb = $GLOBALS['schedulerSlotsByTeamDay'] ?? null;
                $pickDayFb = is_array($eligibleFb) && !empty($eligibleFb) ? $eligibleFb[array_rand($eligibleFb)] : date('n/j/Y');
                $slotOpts = scheduler_slots_for_team_day($slotMapFb, (int) $missingTeamId, $pickDayFb);
                if (empty($slotOpts)) {
                    error_log("No candidate slot available for fallback team $missingTeamId; skipping fallback (will remain unresolved)");
                    continue;
                }
                $pickTimeFb = $slotOpts[array_rand($slotOpts)];

                $rooms = $_POST['rooms'];
                $panelists = fetchPanelists($pdo);
                $panelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
                $selectedPanelists = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

                $teamDefense = [
                    'team_id' => $missingTeamId,
                    'panelist_ids' => $selectedPanelists,
                    'room' => $rooms[array_rand($rooms)],
                    'time_slot' => $pickTimeFb,
                    'day' => $pickDayFb,
                    'defense_type' => 'title_proposal'
                ];
            }
            if (!isset($teamDefense['panelist_ids']) || count($teamDefense['panelist_ids']) < 3) {
                continue;
            }

            $snapUs = $GLOBALS['schedulerUserSchedulesSnapshot'] ?? null;
            if (
                function_exists('validateCandidateSlot')
                && function_exists('fetchExistingDefenseSchedules')
                && function_exists('buildRoomOccupancyMap')
                && is_array($snapUs)
                && isset($GLOBALS['timeDuration'])
            ) {
                $existingDb = fetchExistingDefenseSchedules($pdo);
                $roomOcc = buildRoomOccupancyMap($snapUs);
                $ve = validateCandidateSlot(
                    $pdo,
                    $teamDefense,
                    $snapUs,
                    $existingDb,
                    $roomOcc,
                    (float) $GLOBALS['timeDuration']
                );
                if (!empty($ve)) {
                    error_log('prepareScheduleData: skipping second-pass team ' . $missingTeamId . ' — ' . implode('; ', array_slice($ve, 0, 2)));
                    continue;
                }
            }

            $dateObj = parseDate($teamDefense['day']);
            $date = $dateObj->format('Y-m-d');
            $startTime = new DateTime($teamDefense['time_slot']);
            $endTime = clone $startTime;
            $endTime->modify('+' . $GLOBALS['timeDuration'] . ' hour');
            $defenseType = $teamDefense['defense_type'] ?? 'title_proposal';

            $teamStmt->execute([$missingTeamId]);
            $teamInfo = $teamStmt->fetch(PDO::FETCH_ASSOC);
            $panelistNames = [];
            foreach ($teamDefense['panelist_ids'] as $pid) {
                $userStmt->execute([$pid]);
                $u = $userStmt->fetch(PDO::FETCH_ASSOC);
                $panelistNames[] = $u ? $u['full_name'] : 'Unknown';
            }
            $adviserStmt->execute([$missingTeamId]);
            $advRow = $adviserStmt->fetch(PDO::FETCH_ASSOC);

            $previewSchedules[] = [
                'team_id' => $missingTeamId,
                'team_name' => $teamInfo['team_name'] ?? 'Unknown',
                'thesis_title' => $teamInfo['thesis_title'] ?? '',
                'adviser' => $advRow['full_name'] ?? 'N/A',
                'panelist_id' => $teamDefense['panelist_ids'][0],
                'panelist_id2' => $teamDefense['panelist_ids'][1],
                'panelist_id3' => $teamDefense['panelist_ids'][2],
                'panelist1_name' => $panelistNames[0],
                'panelist2_name' => $panelistNames[1],
                'panelist3_name' => $panelistNames[2],
                'schedule_date' => $date,
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'room' => $teamDefense['room'],
                'defense_type' => $defenseType,
            ];
            $scheduledTeams[] = $missingTeamId;
        }
    }

    return $previewSchedules;
}

function saveScheduleToDatabase($pdo, $schedule)
{
    try {
        error_log("saveScheduleToDatabase: Starting transaction");
        $pdo->beginTransaction();

        // Get all teams that should be scheduled
        $expectedTeams = [];
        foreach ($GLOBALS['teams'] as $team) {
            $expectedTeams[] = $team['id'];
        }
        $expectedTeamCount = count($expectedTeams);
        error_log("saveScheduleToDatabase: Expecting to schedule $expectedTeamCount teams: " . implode(', ', $expectedTeams));

        // Track teams that have been scheduled to prevent duplicates
        $scheduledTeams = [];

        // Sort chromosomes by fitness score
        $defenses = $schedule->chromosomes;
        foreach ($defenses as &$defense) {
            $defense['fitness'] = calculateDefenseFitness($pdo, $defense);
        }

        usort($defenses, function ($a, $b) {
            return $b['fitness'] - $a['fitness']; // Best to worst (reversed)
        });

        $stmt = $pdo->prepare("
            INSERT INTO defense_schedules 
            (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Include notification functions
        require_once dirname(__DIR__) . '/../assets/includes/notification_functions.php';

        // First pass - schedule teams with best fitness
        foreach ($defenses as $defense) {
            // Skip if this team was already scheduled
            if (in_array($defense['team_id'], $scheduledTeams)) {
                continue;
            }

            $scheduledTeams[] = $defense['team_id'];

            $dateObj = parseDate($defense['day']);
            $date = $dateObj->format('Y-m-d');
            $startTime = new DateTime($defense['time_slot']);
            $endTime = clone $startTime;
            // === PROBLEMATIC LINE START ===
            // Previously: $duration = $_POST['timeDuration'];
            // FIX: Use global time duration variable.
            $duration = $GLOBALS['timeDuration'];
            // === PROBLEMATIC LINE END ===
            $endTime->modify('+' . $duration . ' hour');

            // Get defense type from defense array (set during initialization)
            $defenseType = $defense['defense_type'] ?? 'title_proposal';

            // Validate panelist_ids array has exactly 3 elements
            if (!isset($defense['panelist_ids']) || count($defense['panelist_ids']) < 3) {
                error_log("ERROR: Defense for team {$defense['team_id']} has invalid panelist_ids: " . json_encode($defense['panelist_ids'] ?? 'null'));
                continue; // Skip this defense
            }

            $stmt->execute([
                $defense['team_id'],
                $defense['panelist_ids'][0],
                $defense['panelist_ids'][1],
                $defense['panelist_ids'][2],
                $date,
                $startTime->format('H:i:s'),
                $endTime->format('H:i:s'),
                $defense['room'],
                $defenseType,      // defense_type
                'scheduled',       // status
                'pending_chair'    // approval_status - awaits chair review first
            ]);

            // CREATE DEFENSE SCHEDULE NOTIFICATIONS
            $scheduleId = $pdo->lastInsertId();
            
            error_log("DEFENSE SCHEDULER: About to create chair review notifications for schedule ID: $scheduleId, team: {$defense['team_id']}");
            
            // Step 1: Notify program chairs for review (panelists are NOT notified yet)
            $chairNotificationResult = createChairReviewNotifications($pdo, $scheduleId, $defense['team_id'],
                $date, $startTime->format('H:i'), $endTime->format('H:i'), $defense['room']);
            
            error_log("DEFENSE SCHEDULER: Chair notification creation result: " . ($chairNotificationResult ? 'SUCCESS' : 'FAILED'));

            // Track panelist assignments
            foreach ($defense['panelist_ids'] as $panelist_id) {
                global $lastAssignedPanelists;
                $lastAssignedPanelists[$defense['day']][] = $panelist_id;
            }
        }

        // Check if all teams were scheduled
        $missingTeams = array_diff($expectedTeams, $scheduledTeams);

        // Second pass - ensure all teams get scheduled
        if (false && !empty($missingTeams)) {
            error_log("Missing teams detected: " . implode(", ", $missingTeams));

            // For any missed teams, create a schedule forcefully
            foreach ($missingTeams as $missingTeamId) {
                // Find any solution for this team from chromosomes
                $teamDefense = null;
                foreach ($defenses as $defense) {
                    if ($defense['team_id'] == $missingTeamId) {
                        $teamDefense = $defense;
                        break;
                    }
                }

                // If no solution found in chromosomes, create a new one
                if (!$teamDefense) {
                    error_log("Creating fallback schedule for team ID: $missingTeamId");

                    $team = null;
                    foreach ($GLOBALS['teams'] as $filteredTeam) {
                        if ($filteredTeam['id'] == $missingTeamId) {
                            $team = $filteredTeam;
                            break;
                        }
                    }
                    if (!$team) {
                        error_log("Error: Filtered team data not found for ID: $missingTeamId");
                        continue;
                    }

                    $eligibleFb = isset($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                        && is_array($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                        && !empty($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                        ? $GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId]
                        : (isset($GLOBALS['schedulerDays']) && is_array($GLOBALS['schedulerDays']) ? $GLOBALS['schedulerDays'] : ($_POST['days'] ?? []));
                    $slotMapFb = $GLOBALS['schedulerSlotsByTeamDay'] ?? null;
                    $pickDayFb = is_array($eligibleFb) && !empty($eligibleFb) ? $eligibleFb[array_rand($eligibleFb)] : date('n/j/Y');
                    $slotOpts = scheduler_slots_for_team_day($slotMapFb, (int) $missingTeamId, $pickDayFb);
                    if (empty($slotOpts)) {
                        error_log("No candidate slot available for fallback team $missingTeamId; skipping fallback (will remain unresolved)");
                        continue;
                    }
                    $pickTimeFb = $slotOpts[array_rand($slotOpts)];

                    $rooms = $_POST['rooms'];
                    $panelists = fetchPanelists($pdo);

                    $panelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
                    $selectedPanelists = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

                    $teamDefense = [
                        'team_id' => $missingTeamId,
                        'panelist_ids' => $selectedPanelists,
                        'room' => $rooms[array_rand($rooms)],
                        'time_slot' => $pickTimeFb,
                        'day' => $pickDayFb
                    ];
                }

                $dateObj = parseDate($teamDefense['day']);
                $date = $dateObj->format('Y-m-d');
                $startTime = new DateTime($teamDefense['time_slot']);
                $endTime = clone $startTime;
                // === PROBLEMATIC LINE START ===
                // Previously: $duration = $_POST['timeDuration'];
                // FIX: Use global time duration variable.
                $duration = $GLOBALS['timeDuration'];
                // === PROBLEMATIC LINE END ===
                $endTime->modify('+' . $duration . ' hour');

                // Get defense type for missing team
                $defenseType = $teamDefense['defense_type'] ?? 'title_proposal';

                // Validate panelist_ids array has exactly 3 elements
                if (!isset($teamDefense['panelist_ids']) || count($teamDefense['panelist_ids']) < 3) {
                    error_log("ERROR: Defense for missing team {$teamDefense['team_id']} has invalid panelist_ids: " . json_encode($teamDefense['panelist_ids'] ?? 'null'));
                    continue; // Skip this defense
                }

                $stmt->execute([
                    $teamDefense['team_id'],
                    $teamDefense['panelist_ids'][0],
                    $teamDefense['panelist_ids'][1],
                    $teamDefense['panelist_ids'][2],
                    $date,
                    $startTime->format('H:i:s'),
                    $endTime->format('H:i:s'),
                    $teamDefense['room'],
                    $defenseType,      // defense_type
                    'scheduled',       // status
                    'pending_chair'    // approval_status - awaits chair review first
                ]);

                // CREATE CHAIR REVIEW NOTIFICATIONS FOR MISSING TEAMS
                $scheduleId = $pdo->lastInsertId();
                createChairReviewNotifications($pdo, $scheduleId, $teamDefense['team_id'],
                    $date, $startTime->format('H:i'), $endTime->format('H:i'), $teamDefense['room']);

                $scheduledTeams[] = $missingTeamId;
            }
        }

        $scheduledCount = count($scheduledTeams);
        error_log("saveScheduleToDatabase: Scheduled $scheduledCount out of $expectedTeamCount expected teams");

        if ($scheduledCount < $expectedTeamCount) {
            error_log("saveScheduleToDatabase WARNING: Not all teams were scheduled! Missing " . ($expectedTeamCount - $scheduledCount) . " teams");
        }

        error_log("saveScheduleToDatabase: Committing transaction...");
        $pdo->commit();
        error_log("saveScheduleToDatabase: Transaction committed successfully. Total schedules created: $scheduledCount");
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("saveScheduleToDatabase ERROR: " . $e->getMessage());
        error_log("Error location: " . $e->getFile() . " on line " . $e->getLine());
        error_log("Stack trace: " . $e->getTraceAsString());
        error_log("Failed transaction details: " . json_encode([
            'teams_count' => count($GLOBALS['teams'] ?? []),
            'scheduled_teams' => count($scheduledTeams ?? []),
            'defense_count' => count($defenses ?? [])
        ]));
        throw new Exception("Failed to save schedule: " . $e->getMessage());
    }
}

// Helper function to parse a day string from either "Y-m-d" or "m-d-Y":
function parseDate($dayStr)
{
    $date = DateTime::createFromFormat('Y-m-d', $dayStr);
    if (!$date) {
        $date = DateTime::createFromFormat('m-d-Y', $dayStr);
    }
    return $date;
}

// Helper function to calculate fitness for a single defense
function calculateDefenseFitness($pdo, $defense)
{
    static $fitnessCache = [];

    // Create a cache key
    $key = $defense['team_id'] . '-' . implode(',', $defense['panelist_ids']);

    if (isset($fitnessCache[$key])) {
        return $fitnessCache[$key];
    }

    $fitness = 0;

    // Calculate expertise matching
    $teamExpertise = getTeamExpertise($pdo, $defense['team_id']) ?? '';
    $expertiseMatchFound = false;

    // Ensure we have strings
    $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';

    foreach ($defense['panelist_ids'] as $panelist_id) {
        $panelistExpertise = getPanelistExpertise($pdo, $panelist_id) ?? '';
        $panelistExpertise = is_string($panelistExpertise) ? $panelistExpertise : '';

        $similarity = 0;
        if (!empty($teamExpertise) && !empty($panelistExpertise)) {
            $similarity = similar_text($teamExpertise, $panelistExpertise) /
                max(strlen($teamExpertise), strlen($panelistExpertise)) * 100;
        }

        if ($similarity > 70) {
            $expertiseMatchFound = true;
            $fitness += 50; // Reward for expertise match
        }
        $fitness += $similarity; // Add similarity score
    }

    if (!$expertiseMatchFound) {
        $fitness -= 100; // Heavy penalty for no expertise match
    }

    // NEW: Add specialization matching to fitness
    $teamSpecializations = getTeamSpecializations($pdo, $defense['team_id']);
    if (!empty($teamSpecializations)) {
        foreach ($defense['panelist_ids'] as $panelist_id) {
            $panelistSpecializations = getUserSpecializations($pdo, $panelist_id);
            $specializationScore = calculateSpecializationMatch($teamSpecializations, $panelistSpecializations);
            $fitness += $specializationScore; // Add specialization matching bonus
        }
    }

    $fitnessCache[$key] = $fitness;
    return $fitness;
}

function scheduleClockToMinutes($clock)
{
    $frag = scheduler_time_fragment_from_db($clock);
    if ($frag === '') {
        return null;
    }
    $parts = explode(':', $frag);
    if (count($parts) < 2) {
        return null;
    }
    $h = (int) $parts[0];
    $m = (int) $parts[1];
    if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
        return null;
    }

    return ($h * 60) + $m;
}

function computeScheduleQualityMetrics(array $preparedRows): array
{
    if (empty($preparedRows)) {
        return [
            'score' => 0.0,
            'utilization_pct' => 0.0,
            'avg_gap_minutes' => 0.0,
            'room_balance_std' => 0.0,
            'morning_ratio_pct' => 0.0,
        ];
    }

    $rowsByDay = [];
    $roomLoadMin = [];
    $morningStarts = 0;
    $totalRows = 0;
    $sumDuration = 0;

    foreach ($preparedRows as $row) {
        $day = (string) ($row['schedule_date'] ?? '');
        $startMin = scheduleClockToMinutes($row['start_time'] ?? '');
        $endMin = scheduleClockToMinutes($row['end_time'] ?? '');
        if ($day === '' || $startMin === null || $endMin === null || $endMin <= $startMin) {
            continue;
        }

        $duration = $endMin - $startMin;
        $sumDuration += $duration;
        $totalRows++;

        if ($startMin < 12 * 60) {
            $morningStarts++;
        }

        $room = trim((string) ($row['room'] ?? ''));
        if ($room === '') {
            $room = 'UNASSIGNED';
        }
        $roomLoadMin[$room] = ($roomLoadMin[$room] ?? 0) + $duration;

        $rowsByDay[$day][] = [
            'start' => $startMin,
            'end' => $endMin,
            'room' => $room,
        ];
    }

    if ($totalRows === 0) {
        return [
            'score' => 0.0,
            'utilization_pct' => 0.0,
            'avg_gap_minutes' => 0.0,
            'room_balance_std' => 0.0,
            'morning_ratio_pct' => 0.0,
        ];
    }

    $totalGap = 0;
    $gapCount = 0;
    $totalSpan = 0;
    foreach ($rowsByDay as $dayRows) {
        usort($dayRows, static function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        $minStart = $dayRows[0]['start'];
        $maxEnd = $dayRows[0]['end'];
        $prevEnd = $dayRows[0]['end'];

        for ($i = 1; $i < count($dayRows); $i++) {
            $curr = $dayRows[$i];
            if ($curr['start'] > $prevEnd) {
                $totalGap += ($curr['start'] - $prevEnd);
                $gapCount++;
            }
            if ($curr['end'] > $maxEnd) {
                $maxEnd = $curr['end'];
            }
            if ($curr['end'] > $prevEnd) {
                $prevEnd = $curr['end'];
            }
        }

        $totalSpan += max(0, $maxEnd - $minStart);
    }

    $avgGap = $gapCount > 0 ? ($totalGap / $gapCount) : 0.0;
    $utilization = $totalSpan > 0 ? min(1.0, $sumDuration / $totalSpan) : 0.0;

    $loads = array_values($roomLoadMin);
    $roomStd = 0.0;
    if (count($loads) > 1) {
        $mean = array_sum($loads) / count($loads);
        $variance = 0.0;
        foreach ($loads as $load) {
            $variance += pow($load - $mean, 2);
        }
        $roomStd = sqrt($variance / count($loads));
    }

    $morningRatio = $totalRows > 0 ? ($morningStarts / $totalRows) : 0.0;
    $score = (35.0 * $utilization)
        + (12.0 * $morningRatio)
        - (0.07 * $avgGap)
        - (0.02 * $roomStd);

    return [
        'score' => round($score, 2),
        'utilization_pct' => round($utilization * 100, 2),
        'avg_gap_minutes' => round($avgGap, 2),
        'room_balance_std' => round($roomStd, 2),
        'morning_ratio_pct' => round($morningRatio * 100, 2),
    ];
}

class DefenseSchedule
{
    public $pdo;  // Change this to public
    public $chromosomes = [];
    public $fitness = 0;
    public static $initialPopulation = [];
    public static $crossoverCount = 0;
    public static $mutationCount = 0;
    public static $conflictCounts = [];
    public static $fitnessScores = [];
    public static $averageConflictCounts = [];
    public static $averageFitnessScores = [];
    public static $populationPerGeneration = [];
    public $all_defenses = [];

    public function __construct($pdo, $teams, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam)
    {
        $this->pdo = $pdo;
        foreach ($teams as $team) {
            $panelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            // Use defense_type from team data (set during progression logic)
            $defenseType = $team['defense_type'] ?? 'title_proposal';

            $tidConst = (int) $team['id'];
            $eligibleConst = isset($eligibleDaysByTeam[$tidConst]) ? $eligibleDaysByTeam[$tidConst] : [];
            $eligibleWithSlots = [];
            foreach ($eligibleConst as $dayCandidate) {
                $slotCandidateList = scheduler_slots_for_team_day($slotsByTeamDay, $tidConst, $dayCandidate);
                if (!empty($slotCandidateList)) {
                    $eligibleWithSlots[] = $dayCandidate;
                }
            }

            // Only schedule teams with at least one valid slot on an eligible day.
            // Teams with no valid slots will remain unscheduled (unresolved).
            if (!empty($eligibleWithSlots)) {
                $dayPick = $eligibleWithSlots[array_rand($eligibleWithSlots)];
                $slotList = scheduler_slots_for_team_day($slotsByTeamDay, $tidConst, $dayPick);
                $slotPick = !empty($slotList) ? $slotList[array_rand($slotList)] : null;

                // Guard against null slot (should not happen if eligibleWithSlots was built correctly)
                if ($slotPick === null) {
                    error_log("WARNING: team $tidConst picked day $dayPick but got null slot; skipping");
                    continue; // Skip this team; it will be unresolved
                }

                $defense = [
                    'team_id' => $team['id'],
                    'panelist_ids' => $selectedPanelists,
                    'room' => !empty($rooms) ? $rooms[array_rand($rooms)] : '',
                    'time_slot' => $slotPick,
                    'day' => $dayPick,
                    'defense_type' => $defenseType
                ];
                $this->chromosomes[] = $defense;
                $this->all_defenses[] = $defense;
            } else {
                // No valid slots for this team on any eligible day
                error_log("Team $tidConst has no valid slots in slotsByTeamDay; will remain unscheduled");
            }
        }
    }

    public function calculateFitness($userSchedules)
    {
        // Cache for performance
        static $teamMembersCache = [];
        static $panelistDataCache = [];

        $this->fitness = 0;
        $conflicts = 0;
        $panelistDailyAssignments = [];

        // Initialize tracking structure for panelist assignments
        foreach ($this->chromosomes as $defense) {
            foreach ($defense['panelist_ids'] as $panelist_id) {
                if (!isset($panelistDailyAssignments[$panelist_id])) {
                    $panelistDailyAssignments[$panelist_id] = [];
                }
                if (!isset($panelistDailyAssignments[$panelist_id][$defense['day']])) {
                    $panelistDailyAssignments[$panelist_id][$defense['day']] = 1;
                } else {
                    $panelistDailyAssignments[$panelist_id][$defense['day']]++;
                }
            }
        }

        foreach ($this->chromosomes as $defenseKey => $defense) {
            // Use cached team members data
            if (!isset($teamMembersCache[$defense['team_id']])) {
                $teamMembersCache[$defense['team_id']] = getTeamMembers($this->pdo, $defense['team_id'], 'array');
            }
            $teamMembers = $teamMembersCache[$defense['team_id']];

            // Team member conflict check
            if (is_array($teamMembers)) {
                foreach ($teamMembers as $member) {
                    if (hasScheduleConflict($this->pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses, $defense['team_id'])) {
                        $this->fitness -= 200; // HARD penalty - must avoid user schedule conflicts
                        $conflicts++;
                    }
                }
            } else {
                $this->fitness -= 200;
                $conflicts++;
            }

            // Check panelist assignments and expertise match
            $teamExpertise = getTeamExpertise($this->pdo, $defense['team_id']) ?? '';
            $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';
            $expertiseMatchFound = false;

            foreach ($defense['panelist_ids'] as $panelist_id) {
                // Cache panelist data
                if (!isset($panelistDataCache[$panelist_id])) {
                    $panelistDataCache[$panelist_id] = getPanelistData($this->pdo, $panelist_id);
                }
                $panelistData = $panelistDataCache[$panelist_id];

                $isPartTime = isset($panelistData['is_parttime']) ? $panelistData['is_parttime'] : 0;
                $maxAllowed = $isPartTime ? 1 : 3; // 1 for part-time, 3 for full-time

                if (isset($panelistDailyAssignments[$panelist_id][$defense['day']])) {
                    $dailyAssignments = $panelistDailyAssignments[$panelist_id][$defense['day']];
                    if ($dailyAssignments > $maxAllowed) {
                        $this->fitness -= 30;
                        $conflicts++;
                    }
                }

                // Expertise matching check (using cached data)
                $panelistExpertise = $panelistData['area_of_expertise'] ?? '';
                $panelistExpertise = is_string($panelistExpertise) ? $panelistExpertise : '';

                $similarity = 0;
                if (!empty($teamExpertise) && !empty($panelistExpertise)) {
                    $similarity = similar_text($teamExpertise, $panelistExpertise) /
                        max(strlen($teamExpertise), strlen($panelistExpertise)) * 100;
                }

                if ($similarity > 70) {
                    $expertiseMatchFound = true;
                    $this->fitness += 20;
                }

                // Other checks
                if (hasConsecutiveAssignment($panelist_id, $defense['day'], $defense['time_slot'])) {
                    $this->fitness -= 5;
                    $conflicts++;
                }

                if (hasScheduleConflict($this->pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses, $defense['team_id'])) {
                    $this->fitness -= 200; // HARD penalty - must avoid panelist schedule/double-booking conflicts
                    $conflicts++;
                }
            }

            if (!$expertiseMatchFound) {
                $this->fitness -= 25;
                $conflicts++;
            }

            // Room conflict check - check ALL pairs, don't break early
            foreach ($this->chromosomes as $otherKey => $otherDefense) {
                $durH = floatval($GLOBALS['timeDuration']);
                $dayA = scheduler_calendar_day_from_raw((string) $defense['day']);
                $dayB = scheduler_calendar_day_from_raw((string) $otherDefense['day']);

                if ($defenseKey != $otherKey && $dayA !== null && $dayB !== null && $dayA === $dayB) {
                    $rA = scheduler_defense_range_on_day((string) $defense['day'], (string) $defense['time_slot'], $durH);
                    $rB = scheduler_defense_range_on_day((string) $otherDefense['day'], (string) $otherDefense['time_slot'], $durH);

                    if ($rA === null || $rB === null) {
                        continue;
                    }

                    $defenseStart = $rA['start'];
                    $defenseEnd = $rA['end'];
                    $otherStart = $rB['start'];
                    $otherEnd = $rB['end'];

                    if (($defenseStart < $otherEnd) && ($defenseEnd > $otherStart)) {
                        // Same room at overlapping time = room conflict
                        if ($defense['room'] == $otherDefense['room']) {
                            $this->fitness -= 500; // MASSIVE penalty for room overlap
                            $conflicts++;
                        }

                        // Panelist double-booking at overlapping time (any room)
                        $sharedPanelists = array_intersect($defense['panelist_ids'], $otherDefense['panelist_ids']);
                        if (!empty($sharedPanelists)) {
                            $this->fitness -= 500; // MASSIVE penalty for panelist double-booking
                            $conflicts++;
                        }
                    }
                }
            }
        }

        self::$conflictCounts[] = $conflicts;
        self::$fitnessScores[] = $this->fitness;
    }
}

/** Deep-copy chromosome rows so post-GA repair can run on alternates without aliasing. */
function copyDefenseScheduleForPostProcess(DefenseSchedule $src): DefenseSchedule
{
    $c = clone $src;
    $rows = [];
    foreach ($src->chromosomes as $row) {
        if (!is_array($row)) {
            $rows[] = $row;
            continue;
        }
        $copyRow = array_merge([], $row);
        if (isset($copyRow['panelist_ids']) && is_array($copyRow['panelist_ids'])) {
            $copyRow['panelist_ids'] = array_merge([], $copyRow['panelist_ids']);
        }
        $rows[] = $copyRow;
    }
    $c->chromosomes = $rows;
    $c->all_defenses = array_merge([], $rows);

    return $c;
}

// Add a function to check for consecutive assignments
function hasConsecutiveAssignment($panelist_id, $day, $time_slot)
{
    // Implement logic to check if the panelist was assigned in the immediately previous schedule
    // This may require tracking the order of schedules and panelist assignments
    // For simplicity, assume a global or session-based tracking mechanism
    global $lastAssignedPanelists;
    if (isset($lastAssignedPanelists[$day])) {
        return in_array($panelist_id, $lastAssignedPanelists[$day]);
    }
    return false;
}

function getTeamMembers($pdo, $team_id, $format = 'array')
{
    static $cache = [];

    if (!isset($cache[$team_id])) {
        $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, tm.role FROM team_members tm JOIN users u ON tm.user_id = u.id WHERE tm.team_id = ? ORDER BY FIELD(tm.role, 'adviser', 'leader', 'member')");
        $stmt->execute([$team_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cache[$team_id] = array_map(function ($member) {
            return [
                'id' => $member['id'],
                'name' => $member['first_name'] . ' ' . $member['last_name'],
                'role' => $member['role']
            ];
        }, $members);
    }

    if ($format === 'array') {
        return $cache[$team_id];
    } else {
        $output = '';
        foreach ($cache[$team_id] as $member) {
            $output .= htmlspecialchars($member['name']) . ' (' . ucfirst($member['role']) . ')<br>';
        }
        return $output;
    }
}

// Function to fetch team by ID
function fetchTeamById($pdo, $team_id)
{
    $stmt = $pdo->prepare("
        SELECT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise,
               t.locked_panelist1, t.locked_panelist2, t.locked_panelist3
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE t.id = ? AND tm.role = 'adviser'
    ");
    $stmt->execute([$team_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get panelist expertise
function getPanelistExpertise($pdo, $panelist_id)
{
    static $cache = [];

    if (!isset($cache[$panelist_id])) {
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM users WHERE id = ?");
        $stmt->execute([$panelist_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$panelist_id] = $result ? $result['area_of_expertise'] : '';
    }

    return $cache[$panelist_id];
}

// Function to get team expertise
function getTeamExpertise($pdo, $team_id)
{
    static $cache = [];

    if (!isset($cache[$team_id])) {
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$team_id] = $result ? $result['area_of_expertise'] : '';
    }

    return $cache[$team_id];
}

// Diversity Preservation function
function diversityPreservation($population, $populationSize, $pdo, $teams, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam)
{
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });

    $elites = array_slice($population, 0, intval($populationSize / 4));
    $newPopulation = $elites;

    while (count($newPopulation) < $populationSize) {
        $newSchedule = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
        $newPopulation[] = $newSchedule;
    }

    return $newPopulation;
}

// Adjust Mutation Rate function
function adjustMutationRate($mutationRate, $population)
{
    $avgFitness = array_sum(array_column($population, 'fitness')) / count($population);
    $bestFitness = max(array_column($population, 'fitness'));

    if ($bestFitness == $avgFitness) {
        // Increase mutation rate if the population is converging
        $mutationRate *= 1.1;
    } else {
        // Otherwise, slightly decrease it
        $mutationRate *= 0.9;
    }

    // Keep mutation rate within reasonable bounds
    $mutationRate = max(0.05, min(0.5, $mutationRate));

    return $mutationRate;
}

function getDepartment($program) {
    $program = (string)$program; // Force string type
    if (stripos($program, 'Architecture') !== false) {
        return "Architecture";
    } elseif (stripos($program, 'Engineering') !== false) {
        return "Engineering";
    }
    return "Computer Studies";
}

/**
 * Get specializations for a team (from area_of_expertise field)
 */
function getTeamSpecializations($pdo, $teamId) {
    static $cache = [];
    
    if (!isset($cache[$teamId])) {
        $stmt = $pdo->prepare("
            SELECT area_of_expertise 
            FROM teams
            WHERE id = ?
        ");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($team && !empty($team['area_of_expertise'])) {
            $cache[$teamId] = array_filter(array_map('trim', explode(',', $team['area_of_expertise'])));
        } else {
            $cache[$teamId] = [];
        }
    }
    
    return $cache[$teamId];
}

/**
 * Get specializations for a user/panelist (from area_of_expertise field)
 */
function getUserSpecializations($pdo, $userId) {
    static $cache = [];
    
    if (!isset($cache[$userId])) {
        $stmt = $pdo->prepare("
            SELECT area_of_expertise 
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['area_of_expertise'])) {
            $cache[$userId] = array_filter(array_map('trim', explode(',', $user['area_of_expertise'])));
        } else {
            $cache[$userId] = [];
        }
    }
    
    return $cache[$userId];
}

/**
 * Calculate specialization match score between team and panelist
 */
function calculateSpecializationMatch($teamSpecializations, $panelistSpecializations) {
    if (empty($teamSpecializations) || empty($panelistSpecializations)) {
        return 0; // No bonus if either has no specializations
    }
    
    $matchCount = count(array_intersect($teamSpecializations, $panelistSpecializations));
    return $matchCount * 10; // 10 points per matching specialization
}

function selectPanelists($panelistsByProgram, $allPanelists, $adviserId, $teamId = null)
{
    global $pdo; // needed to call getPanelistData()
    $selectedPanelists = [];
    $teamData = null;

    // Prefer team ID lookup to avoid adviser-based cross-team matches.
    if ($teamId !== null) {
        foreach ($GLOBALS['teams'] as $team) {
            if ((int)$team['id'] === (int)$teamId) {
                $teamData = $team;
                break;
            }
        }
    }

    // Backward-compatible fallback for older call sites.
    if (!$teamData) {
        foreach ($GLOBALS['teams'] as $team) {
            if ($team['adviser_id'] == $adviserId) {
                $teamData = $team;
                break;
            }
        }
    }

    if (!$teamData) {
        // Fallback: randomly pick 3 panelists excluding the adviser
        $remaining = array_diff(array_keys($allPanelists), [$adviserId]);
        return array_slice($remaining, 0, 3);
    }
    
    // CHECK FOR LOCKED PANELISTS FIRST
    // If team has locked panelists, use them instead of auto-assigning
    $lockedPanelists = [];
    if (!empty($teamData['locked_panelist1'])) {
        $lockedId = (int)$teamData['locked_panelist1'];
        if (isset($allPanelists[$lockedId])) {
            $lockedPanelists[] = $lockedId;
        }
    }
    if (!empty($teamData['locked_panelist2'])) {
        $lockedId = (int)$teamData['locked_panelist2'];
        if (isset($allPanelists[$lockedId])) {
            $lockedPanelists[] = $lockedId;
        }
    }
    if (!empty($teamData['locked_panelist3'])) {
        $lockedId = (int)$teamData['locked_panelist3'];
        if (isset($allPanelists[$lockedId])) {
            $lockedPanelists[] = $lockedId;
        }
    }
    $lockedPanelists = array_values(array_unique($lockedPanelists));
    
    // If all 3 panelists are locked, return them directly
    if (count($lockedPanelists) >= 3) {
        error_log("Team {$teamData['id']}: Using all 3 locked panelists: " . implode(', ', $lockedPanelists));
        return array_slice($lockedPanelists, 0, 3);
    }
    
    // If some panelists are locked, start with them
    if (!empty($lockedPanelists)) {
        $selectedPanelists = $lockedPanelists;
        error_log("Team {$teamData['id']}: Starting with " . count($lockedPanelists) . " locked panelists: " . implode(', ', $lockedPanelists));
    }
    
    $teamProgram = (string)$teamData['program'];
    $teamDepartment = getDepartment($teamProgram);
    $teamSpecializations = getTeamSpecializations($pdo, $teamData['id']);

    // Score all panelists based on multiple criteria
    $panelistScores = [];
    foreach (array_keys($allPanelists) as $id) {
        if ($id == $adviserId) continue;
        
        $pdata = getPanelistData($pdo, $id);
        $score = 0;
        
        // Same program (highest priority)
        if (strcasecmp((string)($pdata['program'] ?? ''), $teamProgram) === 0) {
            $score += 100;
        }
        
        // Same department
        if (strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0) {
            $score += 50;
        }
        
        // Specialization matching (new!)
        $panelistSpecializations = getUserSpecializations($pdo, $id);
        $specializationScore = calculateSpecializationMatch($teamSpecializations, $panelistSpecializations);
        $score += $specializationScore;
        
        $panelistScores[$id] = $score;
    }
    
    // Sort panelists by score (descending)
    arsort($panelistScores);
    
    // Select top 3 panelists with some randomization for diversity
    $topCandidates = array_values(array_diff(array_keys($panelistScores), $selectedPanelists));
    
    // Candidate 0: Best match (top scorer or random from top 3)
    $top3 = array_slice($topCandidates, 0, min(3, count($topCandidates)));
    if (!empty($top3)) {
        $selectedPanelists[] = $top3[array_rand($top3)];
    }
    
    // Candidate 1: From same department (prefer not already selected)
    $sameDept = array_filter($topCandidates, function($id) use ($pdo, $teamDepartment, $selectedPanelists) {
        if (in_array($id, $selectedPanelists)) return false;
        $pdata = getPanelistData($pdo, $id);
        if (!$pdata) return false;
        return strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0;
    });
    
    if (!empty($sameDept)) {
        $sameDeptValues = array_values($sameDept);
        $selectedPanelists[] = $sameDeptValues[array_rand($sameDeptValues)];
    } else {
        $remaining = array_diff($topCandidates, $selectedPanelists);
        if (!empty($remaining)) {
            $remainingValues = array_values($remaining);
            $selectedPanelists[] = $remainingValues[0];
        }
    }
    
    // Candidate 2: From different department for diversity
    $diffDept = array_filter($topCandidates, function($id) use ($pdo, $teamDepartment, $selectedPanelists) {
        if (in_array($id, $selectedPanelists)) return false;
        $pdata = getPanelistData($pdo, $id);
        if (!$pdata) return false;
        return strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) !== 0;
    });
    
    if (!empty($diffDept)) {
        $diffDeptValues = array_values($diffDept);
        $selectedPanelists[] = $diffDeptValues[array_rand($diffDeptValues)];
    } else {
        $remaining = array_diff($topCandidates, $selectedPanelists);
        if (!empty($remaining)) {
            $remainingValues = array_values($remaining);
            $selectedPanelists[] = $remainingValues[0];
        }
    }
    
    // Ensure we have exactly 3 panelists
    while (count($selectedPanelists) < 3 && count($selectedPanelists) < count($topCandidates)) {
        $remaining = array_diff($topCandidates, $selectedPanelists);
        if (!empty($remaining)) {
            $remainingValues = array_values($remaining);
            $selectedPanelists[] = $remainingValues[0];
        } else {
            break;
        }
    }

    return $selectedPanelists;
}

// Unused functions are kept at the end
function isTimeSlotAvailable($schedule, $day, $timeSlot, $duration, $room)
{
    foreach ($schedule->chromosomes as $defense) {
        if ($defense['day'] == $day && $defense['room'] == $room) {
            // Calculate start and end times for the existing defense
            $existingStart = strtotime($defense['time_slot']);
            $existingEnd = strtotime('+' . $duration . ' hour', $existingStart);

            // Calculate start and end times for the new defense
            $newStart = strtotime($timeSlot);
            $newEnd = strtotime('+' . $duration . ' hour', $newStart);

            // Check if the time slots overlap
            if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                return false;
            }
        }
    }
    return true;
}

function getAvailableTimeSlot($schedule, array $eligibleDaysByTeam, array $slotsByTeamDay, $duration, $rooms)
{
    $availableSlots = [];
    foreach ($eligibleDaysByTeam as $tid => $dayList) {
        foreach ((array) $dayList as $day) {
            foreach (scheduler_slots_for_team_day($slotsByTeamDay, $tid, $day) as $timeSlot) {
                foreach ($rooms as $room) {
                    if (isTimeSlotAvailable($schedule, $day, $timeSlot, $duration, $room)) {
                        $availableSlots[] = ['day' => $day, 'time_slot' => $timeSlot, 'room' => $room];
                    }
                }
            }
        }
    }
    return $availableSlots ? $availableSlots[array_rand($availableSlots)] : null;
}

// ======================================================================
// POST-GA OVERLAP VALIDATION
// Loops until ZERO conflicts remain — guarantees a clean schedule.
// ======================================================================

/**
 * Validate the final schedule and fix ALL overlaps.
 * Runs multiple passes until zero conflicts remain or max iterations.
 * Checks:
 *  1) Defense vs Defense: room overlap, panelist double-booking, member double-booking
 *  2) User schedule (classes) vs defense schedule for panelists & team members
 */
function validateAndFixOverlaps($pdo, $schedule, $userSchedules, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $panelists)
{
    $duration = $GLOBALS['timeDuration'];
    $defenses = $schedule->chromosomes;
    $allIssues = [];
    $totalFixes = 0;
    $maxPasses = 48; // Safety limit (repair may need many moves when defenses are tightly packed)

    // Pre-cache team member IDs
    $memberCache = [];
    foreach ($defenses as $d) {
        if (!isset($memberCache[$d['team_id']])) {
            $memberCache[$d['team_id']] = array_column(getTeamMembers($pdo, $d['team_id'], 'array'), 'id');
        }
    }

    error_log("=== POST-GA OVERLAP VALIDATION START ===");
    error_log("Validating " . count($defenses) . " defense entries (max $maxPasses passes)");

    for ($pass = 0; $pass < $maxPasses; $pass++) {
        $issuesThisPass = 0;
        $fixesThisPass = 0;

        // --- Defense-vs-Defense checks ---
        for ($i = 0; $i < count($defenses); $i++) {
            $d1 = &$defenses[$i];
            $dn1 = scheduler_calendar_day_from_raw((string) $d1['day']);
            $r1 = $dn1 !== null ? scheduler_defense_range_on_day((string) $d1['day'], (string) $d1['time_slot'], $duration) : null;
            if ($r1 === null) {
                continue;
            }
            $d1Start = $r1['start'];
            $d1End = $r1['end'];

            for ($j = $i + 1; $j < count($defenses); $j++) {
                $d2 = &$defenses[$j];

                $dn2 = scheduler_calendar_day_from_raw((string) $d2['day']);
                if ($dn2 === null || $dn2 !== $dn1) continue;

                $r2 = scheduler_defense_range_on_day((string) $d2['day'], (string) $d2['time_slot'], $duration);
                if ($r2 === null) continue;

                $d2Start = $r2['start'];
                $d2End = $r2['end'];
                $timesOverlap = ($d1Start < $d2End) && ($d1End > $d2Start);
                if (!$timesOverlap) continue;

                // CHECK 1: Same room overlap
                if ($d1['room'] === $d2['room']) {
                    $msg = "ROOM OVERLAP: Team {$d1['team_id']} & Team {$d2['team_id']} room {$d1['room']} on {$d1['day']} at {$d1['time_slot']}/{$d2['time_slot']}";
                    $allIssues[] = $msg;
                    error_log("PASS $pass: $msg");
                    $issuesThisPass++;

                    if (fixDefenseSlot($defenses, $j, $d2, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo)) {
                        $fixesThisPass++;
                        $totalFixes++;
                    }
                    continue; // Re-check after fix
                }

                // CHECK 2: Panelist double-booking
                $sharedPanelists = array_intersect($d1['panelist_ids'], $d2['panelist_ids']);
                if (!empty($sharedPanelists)) {
                    $msg = "PANELIST DOUBLE-BOOK: Panelist(s) " . implode(',', $sharedPanelists) . " Team {$d1['team_id']} & Team {$d2['team_id']} on {$d1['day']} {$d1['time_slot']}/{$d2['time_slot']}";
                    $allIssues[] = $msg;
                    error_log("PASS $pass: $msg");
                    $issuesThisPass++;

                    if (fixDefenseSlot($defenses, $j, $d2, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo)) {
                        $fixesThisPass++;
                        $totalFixes++;
                    }
                }

                // CHECK 3: Team member double-booking
                if (!isset($memberCache[$d1['team_id']])) {
                    $memberCache[$d1['team_id']] = array_column(getTeamMembers($pdo, $d1['team_id'], 'array'), 'id');
                }
                if (!isset($memberCache[$d2['team_id']])) {
                    $memberCache[$d2['team_id']] = array_column(getTeamMembers($pdo, $d2['team_id'], 'array'), 'id');
                }
                $sharedMembers = array_intersect($memberCache[$d1['team_id']], $memberCache[$d2['team_id']]);
                if (!empty($sharedMembers)) {
                    $msg = "MEMBER DOUBLE-BOOK: User(s) " . implode(',', $sharedMembers) . " Team {$d1['team_id']} & Team {$d2['team_id']} on {$d1['day']}";
                    $allIssues[] = $msg;
                    error_log("PASS $pass: $msg");
                    $issuesThisPass++;

                    if (fixDefenseSlot($defenses, $j, $d2, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo)) {
                        $fixesThisPass++;
                        $totalFixes++;
                    }
                }
            }
        }

        // --- User schedule vs Defense checks ---
        for ($i = 0; $i < count($defenses); $i++) {
            $d = &$defenses[$i];
            $dYmd = scheduler_calendar_day_from_raw((string) $d['day']);
            $dr = $dYmd !== null ? scheduler_defense_range_on_day((string) $d['day'], (string) $d['time_slot'], $duration) : null;
            if ($dr === null || $dYmd === null) {
                continue;
            }
            $defStart = $dr['start'];
            $defEnd = $dr['end'];

            // All users to check: panelists + team members
            $usersToCheck = $d['panelist_ids'];
            if (!isset($memberCache[$d['team_id']])) {
                $memberCache[$d['team_id']] = array_column(getTeamMembers($pdo, $d['team_id'], 'array'), 'id');
            }
            $usersToCheck = array_unique(array_merge($usersToCheck, $memberCache[$d['team_id']]));

            foreach ($usersToCheck as $userId) {
                if (!isset($userSchedules[$userId])) continue;
                foreach ($userSchedules[$userId] as $sched) {
                    if (schedulerClassRowAllowsOverlap($sched)) {
                        continue;
                    }
                    $schedR = scheduler_user_class_range_on_calendar_day($dYmd, $sched);
                    if ($schedR === null) continue;
                    if (scheduler_slot_ranges_overlap($defStart, $defEnd, $schedR['start'], $schedR['end'])) {
                        $msg = "USER SCHED CONFLICT: User $userId vs Team {$d['team_id']} on {$d['day']} {$d['time_slot']} (" . schedulerClassRowDescriptor($sched) . ")";
                        $allIssues[] = $msg;
                        error_log("PASS $pass: $msg");
                        $issuesThisPass++;

                        if (fixDefenseSlot($defenses, $i, $d, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo)) {
                            $fixesThisPass++;
                            $totalFixes++;
                            // Recalculate times after fix
                            $dYmd = scheduler_calendar_day_from_raw((string) $d['day']);
                            $dr = ($dYmd !== null)
                                ? scheduler_defense_range_on_day((string) $d['day'], (string) $d['time_slot'], $duration)
                                : null;
                            $defStart = $dr !== null ? $dr['start'] : $defStart;
                            $defEnd = $dr !== null ? $dr['end'] : $defEnd;
                        }
                        break 2; // Restart user checks for this defense after fix
                    }
                }
            }
        }

        error_log("PASS $pass complete: $issuesThisPass issues found, $fixesThisPass fixed");

        // If no issues found this pass, we're clean!
        if ($issuesThisPass === 0) {
            error_log("=== VALIDATION CLEAN after $pass pass(es) ===");
            break;
        }

        // If we found issues but couldn't fix any, stop to avoid infinite loop
        if ($fixesThisPass === 0) {
            error_log("=== VALIDATION STUCK: $issuesThisPass issues remain unfixable ===");
            break;
        }
    }

    // Apply fixes back to schedule
    $schedule->chromosomes = $defenses;
    $schedule->all_defenses = $defenses;

    // --- FINAL VERIFICATION PASS (read-only, zero tolerance) ---
    $remainingConflicts = countRemainingConflicts($pdo, $defenses, $duration, $userSchedules, $memberCache);

    error_log("=== POST-GA OVERLAP VALIDATION COMPLETE ===");
    error_log("Total issues found: " . count($allIssues) . ", Total fixes: $totalFixes, Remaining conflicts: $remainingConflicts");

    return [
        'issues' => $allIssues,
        'fixes' => $totalFixes,
        'remainingConflicts' => $remainingConflicts,
        'schedule' => $schedule
    ];
}

/**
 * Try to fix a defense by moving it to a conflict-free slot.
 * Tries: different room → different time → different day → different day+time.
 * Returns true if fixed.
 */
function fixDefenseSlot(&$defenses, $idx, &$defense, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo)
{
    $originalDay = $defense['day'];
    $originalTime = $defense['time_slot'];
    $originalRoom = $defense['room'];

    // Collect all user IDs involved in this defense
    $involvedUsers = $defense['panelist_ids'];
    if (isset($memberCache[$defense['team_id']])) {
        $involvedUsers = array_unique(array_merge($involvedUsers, $memberCache[$defense['team_id']]));
    }

    // Strategy 1: Try a different room (same day/time)
    foreach ($rooms as $altRoom) {
        if ($altRoom === $originalRoom) continue;
        $defense['room'] = $altRoom;
        $defenses[$idx] = $defense;
        if (!hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo)) {
            error_log("FIX: Team {$defense['team_id']} → room $altRoom");
            return true;
        }
    }
    $defense['room'] = $originalRoom; // revert

    // Strategy 2: Try a different time (same calendar day — only pre-validated feasible starts)
    foreach (scheduler_slots_for_team_day($slotsByTeamDay, $defense['team_id'], $originalDay) as $altTime) {
        if ($altTime === $originalTime) continue;
        $defense['time_slot'] = $altTime;
        $defenses[$idx] = $defense;
        if (!hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo)) {
            error_log("FIX: Team {$defense['team_id']} → time $altTime");
            return true;
        }
    }
    $defense['time_slot'] = $originalTime; // revert

    // Alternate days × feasible slots for this team × rooms (covers prior “change day only” attempts)
    $teamIdFx = (int) $defense['team_id'];
    foreach (($eligibleDaysByTeam[$teamIdFx] ?? []) as $altDay) {
        foreach (scheduler_slots_for_team_day($slotsByTeamDay, $teamIdFx, $altDay) as $altTime) {
            foreach ($rooms as $altRoom) {
                if ($altDay === $originalDay && $altTime === $originalTime && $altRoom === $originalRoom) continue;
                $defense['day'] = $altDay;
                $defense['time_slot'] = $altTime;
                $defense['room'] = $altRoom;
                $defenses[$idx] = $defense;
                if (!hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo)) {
                    error_log("FIX: Team {$defense['team_id']} → $altDay $altTime $altRoom");
                    return true;
                }
            }
        }
    }

    // Could not fix - revert to original
    $defense['day'] = $originalDay;
    $defense['time_slot'] = $originalTime;
    $defense['room'] = $originalRoom;
    $defenses[$idx] = $defense;
    error_log("CANNOT FIX: Team {$defense['team_id']} - no valid slot found");
    return false;
}

/**
 * Comprehensive conflict check for a single defense against ALL other defenses
 * and user schedules. Returns true if ANY conflict exists.
 */
function hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo)
{
    $myYmd = scheduler_calendar_day_from_raw((string) $defense['day']);
    $dR = scheduler_defense_range_on_day((string) $defense['day'], (string) $defense['time_slot'], $duration);
    if ($myYmd === null || $dR === null) {
        return false;
    }
    $dStart = $dR['start'];
    $dEnd = $dR['end'];

    // CHECK 1: Against all other defenses
    foreach ($defenses as $otherIdx => $other) {
        if ($otherIdx == $idx) continue;
        $oYmd = scheduler_calendar_day_from_raw((string) ($other['day'] ?? ''));
        if ($oYmd === null || $oYmd !== $myYmd) continue;

        $oRange = scheduler_defense_range_on_day((string) $other['day'], (string) $other['time_slot'], $duration);
        if ($oRange === null) continue;

        $oStart = $oRange['start'];
        $oEnd = $oRange['end'];
        $timesOverlap = ($dStart < $oEnd) && ($dEnd > $oStart);
        if (!$timesOverlap) continue;

        // 1a: Room conflict
        if ($defense['room'] === $other['room']) {
            return true;
        }

        // 1b: Panelist double-booking
        if (!empty(array_intersect($defense['panelist_ids'], $other['panelist_ids']))) {
            return true;
        }

        // 1c: Any involved user is also in the other defense (as panelist or team member)
        $otherUsers = $other['panelist_ids'];
        if (isset($memberCache[$other['team_id']])) {
            $otherUsers = array_merge($otherUsers, $memberCache[$other['team_id']]);
        }
        if (!empty(array_intersect($involvedUsers, $otherUsers))) {
            return true;
        }
    }

    // CHECK 2: User schedule conflicts for all involved users
    foreach ($involvedUsers as $userId) {
        if (!isset($userSchedules[$userId])) continue;
        foreach ($userSchedules[$userId] as $sched) {
            if (schedulerClassRowAllowsOverlap($sched)) {
                continue;
            }
            $sr = scheduler_user_class_range_on_calendar_day($myYmd, $sched);
            if ($sr === null) continue;
            if (scheduler_slot_ranges_overlap($dStart, $dEnd, $sr['start'], $sr['end'])) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Final read-only count of remaining conflicts after all fixes.
 */
function countRemainingConflicts($pdo, $defenses, $duration, $userSchedules, $memberCache)
{
    $conflicts = 0;

    for ($i = 0; $i < count($defenses); $i++) {
        $d1 = $defenses[$i];
        $dn1 = scheduler_calendar_day_from_raw((string) $d1['day']);
        $gr1 = $dn1 !== null ? scheduler_defense_range_on_day((string) $d1['day'], (string) $d1['time_slot'], $duration) : null;
        if ($gr1 === null) {
            continue;
        }
        $d1Start = $gr1['start'];
        $d1End = $gr1['end'];

        // Defense vs Defense
        for ($j = $i + 1; $j < count($defenses); $j++) {
            $d2 = $defenses[$j];
            $dn2 = scheduler_calendar_day_from_raw((string) $d2['day']);
            if ($dn2 === null || $dn2 !== $dn1) continue;
            $gr2 = scheduler_defense_range_on_day((string) $d2['day'], (string) $d2['time_slot'], $duration);
            if ($gr2 === null) continue;
            $d2Start = $gr2['start'];
            $d2End = $gr2['end'];
            if (!(($d1Start < $d2End) && ($d1End > $d2Start))) continue;

            // Room overlap
            if ($d1['room'] === $d2['room']) {
                error_log("REMAINING CONFLICT: Room overlap Team {$d1['team_id']} & {$d2['team_id']}");
                $conflicts++;
            }
            // Panelist double-book
            if (!empty(array_intersect($d1['panelist_ids'], $d2['panelist_ids']))) {
                error_log("REMAINING CONFLICT: Panelist double-book Team {$d1['team_id']} & {$d2['team_id']}");
                $conflicts++;
            }
            // Member double-book
            $m1 = $memberCache[$d1['team_id']] ?? [];
            $m2 = $memberCache[$d2['team_id']] ?? [];
            if (!empty(array_intersect($m1, $m2))) {
                error_log("REMAINING CONFLICT: Member double-book Team {$d1['team_id']} & {$d2['team_id']}");
                $conflicts++;
            }
        }

        // User schedule vs Defense
        $allUsers = array_unique(array_merge($d1['panelist_ids'], $memberCache[$d1['team_id']] ?? []));
        foreach ($allUsers as $userId) {
            if (!isset($userSchedules[$userId])) continue;
            foreach ($userSchedules[$userId] as $sched) {
                if (schedulerClassRowAllowsOverlap($sched)) {
                    continue;
                }
                $sr = scheduler_user_class_range_on_calendar_day($dn1, $sched);
                if ($sr === null) continue;
                if (scheduler_slot_ranges_overlap($d1Start, $d1End, $sr['start'], $sr['end'])) {
                    error_log("REMAINING CONFLICT: User $userId schedule vs Team {$d1['team_id']} ({$d1['day']} {$d1['time_slot']}, " . schedulerClassRowDescriptor($sched) . ")");
                    $conflicts++;
                }
            }
        }
    }

    return $conflicts;
}

/**
 * Legacy helper kept for backward compatibility.
 */
function hasRoomConflictAt($defenses, $excludeIdx, $day, $timeSlot, $room, $duration)
{
    $newStart = strtotime($timeSlot);
    $newEnd = strtotime('+' . $duration . ' hour', $newStart);
    foreach ($defenses as $idx => $def) {
        if ($idx == $excludeIdx) continue;
        if ($def['day'] !== $day || $def['room'] !== $room) continue;
        $existStart = strtotime($def['time_slot']);
        $existEnd = strtotime('+' . $duration . ' hour', $existStart);
        if (($newStart < $existEnd) && ($newEnd > $existStart)) {
            return true;
        }
    }
    return false;
}
