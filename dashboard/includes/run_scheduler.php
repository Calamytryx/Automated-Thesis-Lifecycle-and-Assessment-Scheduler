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

// If the POST submission contains 'startTime' but no 'timeSlots', convert it server-side
if (isset($_POST['startTime']) && isset($_POST['endTime']) && (!isset($_POST['timeSlots']) || empty($_POST['timeSlots']))) {
    error_log("Converting startTime/endTime to timeSlots on server-side");
    $startTime = trim((string)($_POST['startTime'] ?? ''));
    $endTime = trim((string)($_POST['endTime'] ?? ''));
    $duration = floatval($_POST['timeDuration'] ?? 1);
    
    if ($startTime && $endTime && $duration > 0) {
        $timeSlots = [];
        $increment = ($duration == intval($duration)) ? 3600 : 1800; // 60 min or 30 min increments
        
        $currentTime = strtotime("1970-01-01 $startTime");
        $endDateTime = strtotime("1970-01-01 $endTime");
        
        if ($currentTime !== false && $endDateTime !== false) {
            while ($currentTime < $endDateTime) {
                $hhmm = date('H:i', $currentTime);
                // Check 20:30 ceiling: slot must end by 20:30
                $slotEndTime = $currentTime + ($duration * 3600);
                $capTime = strtotime('1970-01-01 20:30:00');
                if ($slotEndTime <= $capTime) {
                    $timeSlots[] = $hhmm;
                }
                $currentTime += $increment;
            }
            if (!empty($timeSlots)) {
                $_POST['timeSlots'] = $timeSlots;
                error_log("Generated " . count($timeSlots) . " timeSlots from $startTime to $endTime with duration $duration hours");
            }
        }
    }
}

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../assets/setup/db.inc.php';
    require_once __DIR__ . '/../includes/edit_functions.php';
    require_once __DIR__ . '/../includes/defense_type_functions.php'; // Add defense type helper
    require_once __DIR__ . '/../includes/panelist_combination_functions.php'; // Panelist combination logic

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection error');
    }

    // Progress tracking function
    function updateProgress($pdo, $progressId, $status, $message, $percentage = null) {
        error_log("ENTER updateProgress: progressId=" . (string) $progressId . ", status=" . (string) $status . ", message=" . (string) $message . ", percentage=" . (is_null($percentage) ? 'null' : (string) $percentage));
        try {
            error_log("TRY updateProgress: preparing statement");
            $stmt = $pdo->prepare("
                INSERT INTO schedule_progress (id, status, message, percentage) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                message = VALUES(message), 
                percentage = VALUES(percentage),
                updated_at = CURRENT_TIMESTAMP
            ");
            error_log("TRY updateProgress: executing statement");
            $stmt->execute([$progressId, $status, $message, $percentage]);
            error_log("TRY updateProgress: executed successfully");
        } catch (Exception $e) {
            error_log("Progress update failed: " . $e->getMessage());
        }
        error_log("EXIT updateProgress");
    }

    function normalizeValidationMode($mode)
    {
        error_log("ENTER normalizeValidationMode: mode_raw=" . (string) $mode);
        $mode = strtolower(trim((string)$mode));
        error_log("COND normalizeValidationMode: normalized_mode=" . $mode);
        $allowedModes = ['strict', 'soft', 'hybrid'];
        $isAllowed = in_array($mode, $allowedModes, true);
        error_log("COND normalizeValidationMode: is_allowed=" . ($isAllowed ? 'true' : 'false'));
        $result = $isAllowed ? $mode : 'hybrid';
        error_log("EXIT normalizeValidationMode: result=" . $result);
        return $result;
    }

    function parseTimeRange($day, $timeSlot, $duration)
    {
        error_log("ENTER parseTimeRange: day=" . (string) $day . ", timeSlot=" . (string) $timeSlot . ", duration=" . (string) $duration);
        $r = scheduler_defense_range_on_day(trim((string) $day), trim((string) $timeSlot), $duration);
        $isNullRange = ($r === null);
        error_log("COND parseTimeRange: range_null=" . ($isNullRange ? 'true' : 'false'));
        if ($isNullRange) {
            error_log("EXIT parseTimeRange: returning null (invalid range)");
            return null;
        }

        $dayNorm = scheduler_calendar_day_from_raw((string) $day);
        error_log("COND parseTimeRange: dayNorm_null=" . ($dayNorm === null ? 'true' : 'false'));
        $dStr = $dayNorm ?? trim((string) $day);
        error_log("parseTimeRange: resolved_day=" . (string) $dStr);

        $result = [
            'day' => $dStr,
            'start' => $r['start'],
            'end' => $r['end'],
            'day_of_week' => date('w', strtotime($dStr)),
        ];
        error_log("EXIT parseTimeRange: day_of_week=" . (string) $result['day_of_week'] . ", start=" . (string) $result['start'] . ", end=" . (string) $result['end']);
        return $result;
    }

    function scheduler_schedule_day_of_week(array $schedule): ?int
    {
        error_log("ENTER scheduler_schedule_day_of_week");
        $hasDow = array_key_exists('day_of_week', $schedule);
        error_log("COND scheduler_schedule_day_of_week: has_day_of_week=" . ($hasDow ? 'true' : 'false'));
        if ($hasDow) {
            $val = normalize_user_schedule_day_to_week_int($schedule['day_of_week']);
            error_log("EXIT scheduler_schedule_day_of_week: using day_of_week=" . (string) $val);
            return normalize_user_schedule_day_to_week_int($schedule['day_of_week']);
        }

        $hasDay = array_key_exists('day', $schedule);
        error_log("COND scheduler_schedule_day_of_week: has_day=" . ($hasDay ? 'true' : 'false'));
        if ($hasDay) {
            $val = normalize_user_schedule_day_to_week_int($schedule['day']);
            error_log("EXIT scheduler_schedule_day_of_week: using day=" . (string) $val);
            return normalize_user_schedule_day_to_week_int($schedule['day']);
        }

        error_log("EXIT scheduler_schedule_day_of_week: returning null");
        return null;
    }

    function slotRangesOverlap($startA, $endA, $startB, $endB)
    {
        error_log("ENTER slotRangesOverlap: startA=" . (string) $startA . ", endA=" . (string) $endA . ", startB=" . (string) $startB . ", endB=" . (string) $endB);
        $overlap = ($startA < $endB) && ($endA > $startB);
        error_log("EXIT slotRangesOverlap: overlap=" . ($overlap ? 'true' : 'false'));
        return $overlap;
    }

    function schedulerClassRowAllowsOverlap(array $scheduleRow): bool
    {
        error_log("ENTER schedulerClassRowAllowsOverlap");
        $allowOverlap = (int) ($scheduleRow['allow_overlap'] ?? 0);
        $isResearchClass = (int) ($scheduleRow['is_research_class'] ?? 0);
        $result = $allowOverlap === 1 || $isResearchClass === 1;
        error_log("EXIT schedulerClassRowAllowsOverlap: allow_overlap=" . (string) $allowOverlap . ", is_research_class=" . (string) $isResearchClass . ", result=" . ($result ? 'true' : 'false'));
        return $result;
    }

    function schedulerClassRowDescriptor(array $scheduleRow): string
    {
        error_log("ENTER schedulerClassRowDescriptor");
        $className = trim((string) ($scheduleRow['class_name'] ?? 'Class'));
        $room = trim((string) ($scheduleRow['room'] ?? ''));
        $start = trim((string) ($scheduleRow['start_time'] ?? ''));
        $end = trim((string) ($scheduleRow['end_time'] ?? ''));
        $parts = [$className !== '' ? $className : 'Class'];
        $hasTimes = ($start !== '' || $end !== '');
        error_log("COND schedulerClassRowDescriptor: has_times=" . ($hasTimes ? 'true' : 'false'));
        if ($hasTimes) {
            $parts[] = trim($start . '-' . $end, '-');
        }
        $hasRoom = ($room !== '');
        error_log("COND schedulerClassRowDescriptor: has_room=" . ($hasRoom ? 'true' : 'false'));
        if ($hasRoom) {
            $parts[] = 'room ' . $room;
        }
        $allowsOverlap = schedulerClassRowAllowsOverlap($scheduleRow);
        error_log("COND schedulerClassRowDescriptor: allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
        if ($allowsOverlap) {
            $parts[] = '(overlap-exempt)';
        }

        $result = implode(', ', $parts);
        error_log("EXIT schedulerClassRowDescriptor: result=" . $result);
        return $result;
    }

    function userSchedulesHasOverlapExceptionColumns($pdo)
    {
        error_log("ENTER userSchedulesHasOverlapExceptionColumns");
        static $cache = null;
        $hasCache = ($cache !== null);
        error_log("COND userSchedulesHasOverlapExceptionColumns: cache_hit=" . ($hasCache ? 'true' : 'false'));
        if ($hasCache) {
            error_log("EXIT userSchedulesHasOverlapExceptionColumns: returning cache");
            return $cache;
        }

        $cache = [
            'allow_overlap' => false,
            'is_research_class' => false,
        ];

        try {
            error_log("TRY userSchedulesHasOverlapExceptionColumns: query columns");
            $stmt = $pdo->query("SHOW COLUMNS FROM user_schedules WHERE Field IN ('allow_overlap','is_research_class')");
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $cache['allow_overlap'] = in_array('allow_overlap', $cols, true);
            $cache['is_research_class'] = in_array('is_research_class', $cols, true);
            error_log("TRY userSchedulesHasOverlapExceptionColumns: allow_overlap=" . ($cache['allow_overlap'] ? 'true' : 'false') . ", is_research_class=" . ($cache['is_research_class'] ? 'true' : 'false'));
        } catch (Exception $e) {
            error_log('userSchedulesHasOverlapExceptionColumns: ' . $e->getMessage());
        }

        error_log("EXIT userSchedulesHasOverlapExceptionColumns");
        return $cache;
    }

    function fetchExistingDefenseSchedules($pdo)
    {
        error_log("ENTER fetchExistingDefenseSchedules");
        $stmt = $pdo->query("SELECT team_id, room, schedule_date AS day, start_time, end_time FROM defense_schedules WHERE status = 'scheduled'");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("fetchExistingDefenseSchedules: row_count=" . count($rows));
        foreach ($rows as &$row) {
            $normalizedDay = scheduler_calendar_day_from_raw((string) ($row['day'] ?? ''));
            $hasNormalized = ($normalizedDay !== null);
            error_log("COND fetchExistingDefenseSchedules: normalized_day_available=" . ($hasNormalized ? 'true' : 'false'));
            if ($hasNormalized) {
                $row['day'] = $normalizedDay;
            }
        }

        error_log("EXIT fetchExistingDefenseSchedules");
        return $rows;
    }

    function buildRoomOccupancyMap($userSchedules)
    {
        error_log("ENTER buildRoomOccupancyMap: userSchedules_count=" . count($userSchedules));
        $roomMap = [];

        foreach ($userSchedules as $scheduleList) {
            error_log("buildRoomOccupancyMap: schedule_list_count=" . (is_array($scheduleList) ? count($scheduleList) : 0));
            foreach ($scheduleList as $schedule) {
                $allowsOverlap = schedulerClassRowAllowsOverlap($schedule);
                error_log("COND buildRoomOccupancyMap: class_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                if ($allowsOverlap) {
                    continue;
                }

                $hasRoom = !empty($schedule['room']);
                error_log("COND buildRoomOccupancyMap: has_room=" . ($hasRoom ? 'true' : 'false'));
                if (!$hasRoom) {
                    continue;
                }

                $room = $schedule['room'];
                $day = scheduler_schedule_day_of_week($schedule);
                $dayNull = ($day === null);
                error_log("COND buildRoomOccupancyMap: day_null=" . ($dayNull ? 'true' : 'false'));
                if ($dayNull) {
                    continue;
                }
                $rawStart = trim((string)($schedule['start_time'] ?? ''));
                $rawEnd = trim((string)($schedule['end_time'] ?? ''));
                $hasTimes = !($rawStart === '' || $rawEnd === '');
                error_log("COND buildRoomOccupancyMap: has_times=" . ($hasTimes ? 'true' : 'false'));
                if (!$hasTimes) {
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
                    $isSame = isset($existing['start_time'], $existing['end_time'])
                        && $existing['start_time'] === $entry['start_time']
                        && $existing['end_time'] === $entry['end_time']
                        && ((string)($existing['class_name'] ?? '') === (string)($entry['class_name'] ?? ''));
                    error_log("COND buildRoomOccupancyMap: duplicate_check=" . ($isSame ? 'true' : 'false'));
                    if ($isSame) {
                        $isDup = true;
                        break;
                    }
                }

                error_log("COND buildRoomOccupancyMap: is_duplicate=" . ($isDup ? 'true' : 'false'));
                if (!$isDup) {
                    $roomMap[$room][$day][] = $entry;

                    // Diagnostic logging: if multiple identical time blocks appear
                    // for the same room/day, log details to help track duplication sources.
                    $hasMultiple = (count($roomMap[$room][$day]) > 1);
                    error_log("COND buildRoomOccupancyMap: has_multiple_entries=" . ($hasMultiple ? 'true' : 'false'));
                    if ($hasMultiple) {
                        static $dupLogged = [];
                        $key = $room . '|' . $day . '|' . $entry['start_time'] . '|' . $entry['end_time'] . '|' . (string)($entry['class_name'] ?? '');
                        $alreadyLogged = !empty($dupLogged[$key]);
                        error_log("COND buildRoomOccupancyMap: dup_logged=" . ($alreadyLogged ? 'true' : 'false'));
                        if (!$alreadyLogged) {
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
            error_log("buildRoomOccupancyMap: room=" . (string) $room . " day_count=" . (is_array($dayMap) ? count($dayMap) : 0));
            foreach ($dayMap as $day => $entries) {
                error_log("buildRoomOccupancyMap: room=" . (string) $room . " day=" . (string) $day . " entry_count=" . (is_array($entries) ? count($entries) : 0));
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

        error_log("EXIT buildRoomOccupancyMap");
        return $roomMap;
    }

    /**
     * Augment occupancy map with schedule rows for specified teams.
     * Fetches section template and personal schedules directly from DB (like preview overlay does)
     * to ensure all applicable classes are included in conflict checking.
     */
    function augmentOccupancyMapWithTeamSchedules($pdo, &$roomMap, $teamIds)
    {
        error_log("ENTER augmentOccupancyMapWithTeamSchedules: teamIds_count=" . (is_array($teamIds) ? count($teamIds) : 0));
        $noTeams = empty($teamIds);
        error_log("COND augmentOccupancyMapWithTeamSchedules: empty_teamIds=" . ($noTeams ? 'true' : 'false'));
        if ($noTeams) {
            error_log("EXIT augmentOccupancyMapWithTeamSchedules: nothing to do");
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
            error_log("TRY augmentOccupancyMapWithTeamSchedules: fetch section rows");
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
            error_log("TRY augmentOccupancyMapWithTeamSchedules: fetch personal rows");
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
            $allowsOverlap = schedulerClassRowAllowsOverlap($row);
            error_log("COND augmentOccupancyMapWithTeamSchedules: class_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
            if ($allowsOverlap) {
                continue;
            }

            $hasRoom = !empty($row['room']);
            error_log("COND augmentOccupancyMapWithTeamSchedules: has_room=" . ($hasRoom ? 'true' : 'false'));
            if (!$hasRoom) {
                continue;
            }

            $dowNorm = normalize_user_schedule_day_to_week_int($row['day_of_week'] ?? '');
            $dowNull = ($dowNorm === null);
            error_log("COND augmentOccupancyMapWithTeamSchedules: dow_null=" . ($dowNull ? 'true' : 'false'));
            if ($dowNull) {
                continue;
            }

            $room = $row['room'];
            $day = $dowNorm;
            $rawStart = trim((string)($row['start_time'] ?? ''));
            $rawEnd = trim((string)($row['end_time'] ?? ''));
            $hasTimes = !($rawStart === '' || $rawEnd === '');
            error_log("COND augmentOccupancyMapWithTeamSchedules: has_times=" . ($hasTimes ? 'true' : 'false'));
            if (!$hasTimes) {
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
                $isSame = isset($existing['start'], $existing['end']) && 
                    $existing['start'] === $entry['start'] && 
                    $existing['end'] === $entry['end'] && 
                    ((string)($existing['class_name'] ?? '') === (string)($entry['class_name'] ?? ''));
                error_log("COND augmentOccupancyMapWithTeamSchedules: duplicate_check=" . ($isSame ? 'true' : 'false'));
                if ($isSame) {
                    $isDup = true;
                    break;
                }
            }

            error_log("COND augmentOccupancyMapWithTeamSchedules: is_duplicate=" . ($isDup ? 'true' : 'false'));
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
        error_log("EXIT augmentOccupancyMapWithTeamSchedules");
    }

    function validateCandidateSlot($pdo, $defense, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration)
    {
        error_log("ENTER validateCandidateSlot");
        $conflicts = [];
        $dayValue = is_array($defense['day'] ?? null) ? ($defense['day'][0] ?? '') : ($defense['day'] ?? '');
        $timeValue = is_array($defense['time_slot'] ?? null) ? ($defense['time_slot'][0] ?? '') : ($defense['time_slot'] ?? '');
        $roomValue = is_array($defense['room'] ?? null) ? ($defense['room'][0] ?? '') : ($defense['room'] ?? '');
        $panelistIds = $defense['panelist_ids'] ?? [];
        $panelistIdsIsArray = is_array($panelistIds);
        error_log("COND validateCandidateSlot: panelist_ids_is_array=" . ($panelistIdsIsArray ? 'true' : 'false'));
        if (!$panelistIdsIsArray) {
            $panelistIds = [$panelistIds];
        }

        error_log("validateCandidateSlot: dayValue=" . (string) $dayValue . ", timeValue=" . (string) $timeValue . ", roomValue=" . (string) $roomValue . ", panelistIds_count=" . count($panelistIds));

        $range = parseTimeRange($dayValue, $timeValue, $duration);

        $rangeNull = ($range === null);
        error_log("COND validateCandidateSlot: range_null=" . ($rangeNull ? 'true' : 'false'));
        if ($rangeNull) {
            error_log("EXIT validateCandidateSlot: invalid range");
            return ['Invalid day or time slot format'];
        }

        $defenseStart = $range['start'];
        $defenseEnd = $range['end'];
        $defenseDay = $range['day'];
        $defenseDayOfWeek = $range['day_of_week'];
        $defenseWindow = date('H:i', $defenseStart) . '-' . date('H:i', $defenseEnd);

        // Debug logging for room check
        $hasRoomValue = ($roomValue !== '');
        error_log("COND validateCandidateSlot: has_room_value=" . ($hasRoomValue ? 'true' : 'false'));
        if ($hasRoomValue) {
            $hasRoomMap = isset($roomOccupancyMap[$roomValue][$defenseDayOfWeek]);
            error_log("COND validateCandidateSlot: room_map_exists=" . ($hasRoomMap ? 'true' : 'false'));
            if ($hasRoomMap) {
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

        $roomMapHasDay = $roomValue !== '' && isset($roomOccupancyMap[$roomValue][$defenseDayOfWeek]);
        error_log("COND validateCandidateSlot: room_map_day_exists=" . ($roomMapHasDay ? 'true' : 'false'));
        if ($roomMapHasDay) {
            foreach ($roomOccupancyMap[$roomValue][$defenseDayOfWeek] as $occupied) {
                error_log("LOOP validateCandidateSlot: room occupancy entry");
                $occStart = scheduler_unix_on_calendar_day($defenseDay, $occupied['start_time'] ?? '');
                $occEnd = scheduler_unix_on_calendar_day($defenseDay, $occupied['end_time'] ?? '');
                $occInvalid = ($occStart === false || $occEnd === false);
                error_log("COND validateCandidateSlot: occ_invalid=" . ($occInvalid ? 'true' : 'false'));
                if ($occInvalid) {
                    continue;
                }
                $overlap = slotRangesOverlap($defenseStart, $defenseEnd, $occStart, $occEnd);
                error_log("COND validateCandidateSlot: room_overlap=" . ($overlap ? 'true' : 'false'));
                if ($overlap) {
                    $classStart = date('H:i', $occStart);
                    $classEnd = date('H:i', $occEnd);
                    $className = trim((string) ($occupied['class_name'] ?? 'Class'));
                    $conflicts[] = "Room schedule conflict: room {$roomValue} occupied by {$className} ({$classStart}-{$classEnd}) on {$defenseDay}, candidate {$defenseWindow}";
                    break;
                }
            }
        }
// PART-TIME RESTRICTION: block slots before 4:00 PM for part-time panelists
foreach ($panelistIds as $panelistId) {
    error_log("LOOP validateCandidateSlot: part-time restriction check for panelist=" . (string) $panelistId);
    if (is_array($panelistId)) continue;
    $pdata = getPanelistData($pdo, $panelistId);
    if ((int)($pdata['is_parttime'] ?? 0) === 1 && !isParttimePanelistAllowedAtTime($timeValue)) {
        $conflicts[] = "Part-time panelist {$panelistId} cannot be scheduled before 4:00 PM (slot: {$timeValue})";
    }
}
$hasConflictsEarly = !empty($conflicts);
error_log("COND validateCandidateSlot: conflicts_after_part_time=" . ($hasConflictsEarly ? 'true' : 'false'));
if ($hasConflictsEarly) {
    error_log("EXIT validateCandidateSlot: conflicts_found_after_part_time");
    return $conflicts;
}

        foreach ($panelistIds as $panelistId) {
            error_log("LOOP validateCandidateSlot: panelist schedule check panelist=" . (string) $panelistId);
            if (is_array($panelistId)) {
                continue;
            }
            $hasPanelistSchedules = isset($userSchedules[$panelistId]);
            error_log("COND validateCandidateSlot: panelist_has_schedules=" . ($hasPanelistSchedules ? 'true' : 'false'));
            if (!$hasPanelistSchedules) {
                continue;
            }

            foreach ($userSchedules[$panelistId] as $schedule) {
                error_log("LOOP validateCandidateSlot: panelist schedule entry");
                $allowsOverlap = schedulerClassRowAllowsOverlap($schedule);
                error_log("COND validateCandidateSlot: panelist_schedule_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                if ($allowsOverlap) {
                    continue;
                }

                $scheduleDayOfWeek = scheduler_schedule_day_of_week($schedule);
                $dayMismatch = ($scheduleDayOfWeek === null || (int) $scheduleDayOfWeek !== (int) $defenseDayOfWeek);
                error_log("COND validateCandidateSlot: panelist_schedule_day_match=" . ($dayMismatch ? 'false' : 'true'));
                if ($dayMismatch) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($defenseDay, $schedule);
                $schNull = ($sch === null);
                error_log("COND validateCandidateSlot: panelist_schedule_range_null=" . ($schNull ? 'true' : 'false'));
                if ($schNull) {
                    continue;
                }

                $overlap = slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end']);
                error_log("COND validateCandidateSlot: panelist_schedule_overlap=" . ($overlap ? 'true' : 'false'));
                if ($overlap) {
                    $conflicts[] = "Panelist schedule conflict: panelist {$panelistId}, {$defenseDay} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        $teamMembers = getTeamMembers($pdo, $defense['team_id'], 'array');
        error_log("validateCandidateSlot: team_id=" . (string) ($defense['team_id'] ?? '') . ", teamMembers_count=" . (is_array($teamMembers) ? count($teamMembers) : 0));
        foreach ($teamMembers as $member) {
            $memberId = $member['id'];
            $hasMemberSchedules = isset($userSchedules[$memberId]);
            error_log("COND validateCandidateSlot: member_has_schedules=" . ($hasMemberSchedules ? 'true' : 'false'));
            if (!$hasMemberSchedules) {
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
                error_log("LOOP validateCandidateSlot: member schedule entry");
                $allowsOverlap = schedulerClassRowAllowsOverlap($schedule);
                error_log("COND validateCandidateSlot: member_schedule_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                if ($allowsOverlap) {
                    continue;
                }

                $scheduleDayOfWeek = scheduler_schedule_day_of_week($schedule);
                $dayMismatch = ($scheduleDayOfWeek === null || (int) $scheduleDayOfWeek !== (int) $defenseDayOfWeek);
                error_log("COND validateCandidateSlot: member_schedule_day_match=" . ($dayMismatch ? 'false' : 'true'));
                if ($dayMismatch) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($defenseDay, $schedule);
                $schNull = ($sch === null);
                error_log("COND validateCandidateSlot: member_schedule_range_null=" . ($schNull ? 'true' : 'false'));
                if ($schNull) {
                    continue;
                }

                $overlap = slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end']);
                error_log("COND validateCandidateSlot: member_schedule_overlap=" . ($overlap ? 'true' : 'false'));
                if ($overlap) {
                    $conflicts[] = "Student schedule conflict: member {$memberId}, {$defenseDay} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        foreach ($existingSchedules as $existing) {
            $dayMatch = ($existing['day'] === $defenseDay);
            error_log("COND validateCandidateSlot: existing_day_match=" . ($dayMatch ? 'true' : 'false'));
            if (!$dayMatch) {
                continue;
            }

            $existingStart = scheduler_unix_on_calendar_day($defenseDay, $existing['start_time'] ?? '');
            $existingEnd = scheduler_unix_on_calendar_day($defenseDay, $existing['end_time'] ?? '');
            $existingInvalid = ($existingStart === false || $existingEnd === false);
            error_log("COND validateCandidateSlot: existing_range_invalid=" . ($existingInvalid ? 'true' : 'false'));
            if ($existingInvalid) {
                continue;
            }

            $existOverlap = slotRangesOverlap($defenseStart, $defenseEnd, $existingStart, $existingEnd);
            $roomMatch = ($existing['room'] === $roomValue);
            error_log("COND validateCandidateSlot: existing_overlap=" . ($existOverlap ? 'true' : 'false') . ", room_match=" . ($roomMatch ? 'true' : 'false'));
            if ($existOverlap && $roomMatch) {
                $existWindow = date('H:i', $existingStart) . '-' . date('H:i', $existingEnd);
                $conflicts[] = "Existing defense occupancy: room {$roomValue}, {$defenseDay} {$defenseWindow}, clashes with team {$existing['team_id']} at {$existWindow}";
            }
        }

        $result = array_values(array_unique($conflicts));
        error_log("EXIT validateCandidateSlot: conflicts_count=" . count($result));
        return $result;
    }

    function validateCandidateClassConflicts($pdo, $defense, $userSchedules, $duration)
    {
        error_log("ENTER validateCandidateClassConflicts");
        $conflicts = [];
        $dayValue = is_array($defense['day'] ?? null) ? ($defense['day'][0] ?? '') : ($defense['day'] ?? '');
        $timeValue = is_array($defense['time_slot'] ?? null) ? ($defense['time_slot'][0] ?? '') : ($defense['time_slot'] ?? '');
        $panelistIds = $defense['panelist_ids'] ?? [];
        $panelistIdsIsArray = is_array($panelistIds);
        error_log("COND validateCandidateClassConflicts: panelist_ids_is_array=" . ($panelistIdsIsArray ? 'true' : 'false'));
        if (!$panelistIdsIsArray) {
            $panelistIds = [$panelistIds];
        }

        error_log("validateCandidateClassConflicts: dayValue=" . (string) $dayValue . ", timeValue=" . (string) $timeValue . ", panelistIds_count=" . count($panelistIds));

        $range = parseTimeRange($dayValue, $timeValue, $duration);
        $rangeNull = ($range === null);
        error_log("COND validateCandidateClassConflicts: range_null=" . ($rangeNull ? 'true' : 'false'));
        if ($rangeNull) {
            error_log("EXIT validateCandidateClassConflicts: invalid range");
            return ['Invalid day or time slot format'];
        }

        $defenseStart = $range['start'];
        $defenseEnd = $range['end'];
        $defenseDayOfWeek = $range['day_of_week'];
        $defenseWindow = date('H:i', $defenseStart) . '-' . date('H:i', $defenseEnd);

        foreach ($panelistIds as $panelistId) {
            error_log("LOOP validateCandidateClassConflicts: panelist=" . (string) $panelistId);
            if (is_array($panelistId)) {
                continue;
            }
            $hasPanelistSchedules = isset($userSchedules[$panelistId]);
            error_log("COND validateCandidateClassConflicts: panelist_has_schedules=" . ($hasPanelistSchedules ? 'true' : 'false'));
            if (!$hasPanelistSchedules) {
                continue;
            }

            foreach ($userSchedules[$panelistId] as $schedule) {
                $allowsOverlap = schedulerClassRowAllowsOverlap($schedule);
                error_log("COND validateCandidateClassConflicts: panelist_schedule_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                if ($allowsOverlap) {
                    continue;
                }
                $scheduleDayOfWeek = scheduler_schedule_day_of_week($schedule);
                $dayMismatch = ($scheduleDayOfWeek === null || (int) $scheduleDayOfWeek !== (int) $defenseDayOfWeek);
                error_log("COND validateCandidateClassConflicts: panelist_schedule_day_match=" . ($dayMismatch ? 'false' : 'true'));
                if ($dayMismatch) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($range['day'], $schedule);
                $schNull = ($sch === null);
                error_log("COND validateCandidateClassConflicts: panelist_schedule_range_null=" . ($schNull ? 'true' : 'false'));
                if ($schNull) {
                    continue;
                }

                $overlap = slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end']);
                error_log("COND validateCandidateClassConflicts: panelist_schedule_overlap=" . ($overlap ? 'true' : 'false'));
                if ($overlap) {
                    $conflicts[] = "Panelist class conflict: panelist {$panelistId}, {$dayValue} {$defenseWindow}, blocked by " . schedulerClassRowDescriptor($schedule);
                    break;
                }
            }
        }

        $teamMembers = getTeamMembers($pdo, $defense['team_id'], 'array');
        static $loggedTeams = [];
        $isTeamLogged = in_array($defense['team_id'], $loggedTeams);
        error_log("COND validateCandidateClassConflicts: team_logged=" . ($isTeamLogged ? 'true' : 'false'));
        if (!$isTeamLogged) {
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
            $hasMemberSchedules = isset($userSchedules[$memberId]);
            error_log("COND validateCandidateClassConflicts: member_has_schedules=" . ($hasMemberSchedules ? 'true' : 'false'));
            if (!$hasMemberSchedules) {
                static $missedMembers = [];
                $memberLogged = in_array($memberId, $missedMembers);
                error_log("COND validateCandidateClassConflicts: missed_member_logged=" . ($memberLogged ? 'true' : 'false'));
                if (!$memberLogged) {
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
            $memberSchedLogged = in_array($memberId, $memberScheds);
            error_log("COND validateCandidateClassConflicts: member_sched_logged=" . ($memberSchedLogged ? 'true' : 'false'));
            if (!$memberSchedLogged) {
                $memberScheds[] = $memberId;
                error_log(sprintf(
                    "CLASS CONFLICT: Team member %d has %d schedules loaded",
                    $memberId,
                    count($userSchedules[$memberId])
                ));
                // Log first few schedules for debugging
                $count = 0;
                foreach ($userSchedules[$memberId] as $s) {
                    error_log("LOOP validateCandidateClassConflicts: member schedule sample");
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
                    $reachedLimit = ($count >= 3);
                    error_log("COND validateCandidateClassConflicts: sample_limit_reached=" . ($reachedLimit ? 'true' : 'false'));
                    if ($reachedLimit) break;
                }
            }

            foreach ($userSchedules[$memberId] as $schedule) {
                $allowsOverlap = schedulerClassRowAllowsOverlap($schedule);
                error_log("COND validateCandidateClassConflicts: member_schedule_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                if ($allowsOverlap) {
                    continue;
                }
                $scheduleDayOfWeek = scheduler_schedule_day_of_week($schedule);
                $dayMismatch = ($scheduleDayOfWeek === null || (int) $scheduleDayOfWeek !== (int) $defenseDayOfWeek);
                error_log("COND validateCandidateClassConflicts: member_schedule_day_match=" . ($dayMismatch ? 'false' : 'true'));
                if ($dayMismatch) {
                    continue;
                }

                $sch = scheduler_user_class_range_on_calendar_day($range['day'], $schedule);
                $schNull = ($sch === null);
                error_log("COND validateCandidateClassConflicts: member_schedule_range_null=" . ($schNull ? 'true' : 'false'));
                if ($schNull) {
                    continue;
                }

                $overlap = slotRangesOverlap($defenseStart, $defenseEnd, $sch['start'], $sch['end']);
                error_log("COND validateCandidateClassConflicts: member_schedule_overlap=" . ($overlap ? 'true' : 'false'));
                if ($overlap) {
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

        $result = array_values(array_unique($conflicts));
        error_log("EXIT validateCandidateClassConflicts: conflicts_count=" . count($result));
        return $result;
    }

    function buildPreGACandidatePool($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, $duration, $progressId = null, $validationMode = 'hybrid')
    {
        error_log("ENTER buildPreGACandidatePool: teams_count=" . count($teams) . ", rooms_count=" . count($rooms) . ", timeSlots_count=" . count($timeSlots) . ", days_count=" . count($days) . ", validationMode=" . (string) $validationMode);
        $candidatePool = [];
        $issues = [];
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);

        foreach ($teams as $team) {
            error_log("LOOP buildPreGACandidatePool: team_id=" . (string) ($team['id'] ?? ''));
            $tid = (int) $team['id'];
            $teamPanelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($teamPanelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            $teamCandidates = [];
            foreach ($days as $day) {
                error_log("LOOP buildPreGACandidatePool: day=" . (string) $day);
                foreach ($timeSlots as $timeSlot) {
                    error_log("LOOP buildPreGACandidatePool: timeSlot=" . (string) $timeSlot);
                    foreach ($rooms as $room) {
                        error_log("LOOP buildPreGACandidatePool: room=" . (string) $room);
                        $defense = [
                            'team_id' => $team['id'],
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];

                        $conflicts = validateCandidateSlot($pdo, $defense, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration);
                        $hasConflicts = !empty($conflicts);
                        error_log("COND buildPreGACandidatePool: candidate_has_conflicts=" . ($hasConflicts ? 'true' : 'false'));
                        if (!$hasConflicts) {
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

        error_log("buildPreGACandidatePool: summary_teams=" . (string) $summary['teams'] . ", validCandidates=" . (string) $summary['validCandidates'] . ", unresolvedTeams_count=" . count($summary['unresolvedTeams']));

        $result = [
            'candidatePool' => $candidatePool,
            'issues' => $issues,
            'summary' => $summary,
            'blocked' => !empty($summary['unresolvedTeams']) && $validationMode === 'strict',
            'existingSchedules' => $existingSchedules,
        ];
        error_log("EXIT buildPreGACandidatePool: blocked=" . (!empty($summary['unresolvedTeams']) && $validationMode === 'strict' ? 'true' : 'false'));
        return $result;
    }

    /**
     * Per team and calendar day: start times that pass validateCandidateSlot for at least one room
     * (student classes, panelist teaching loads, venue timetable, DB defenses — same checks as GA).
     */
    function computeSchedulerSlotContext($pdo, $teams, $panelists, $rooms, $timeSlotsFlat, $days, $userSchedules, $duration): array
    {
        error_log("ENTER computeSchedulerSlotContext: teams_count=" . count($teams) . ", rooms_count=" . count($rooms) . ", timeSlots_count=" . count($timeSlotsFlat) . ", days_count=" . count($days));
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
            error_log("LOOP computeSchedulerSlotContext: team_id=" . (string) ($team['id'] ?? ''));
            $tid = (int) $team['id'];
            $slotsByTeamDay[$tid] = [];
            $teamPanelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($teamPanelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            foreach ($days as $day) {
                error_log("LOOP computeSchedulerSlotContext: day=" . (string) $day);
                $good = [];
                foreach ($timeSlotsFlat as $timeSlot) {
                    error_log("LOOP computeSchedulerSlotContext: timeSlot=" . (string) $timeSlot);
                    $classConflicts = validateCandidateClassConflicts($pdo, [
                        'team_id' => $tid,
                        'panelist_ids' => $selectedPanelists,
                        'time_slot' => $timeSlot,
                        'day' => $day,
                        'defense_type' => $team['defense_type'] ?? 'title_proposal',
                    ], $userSchedules, $duration);
                    $hasClassConflicts = !empty($classConflicts);
                    error_log("COND computeSchedulerSlotContext: class_conflicts=" . ($hasClassConflicts ? 'true' : 'false'));
                    if ($hasClassConflicts) {
                        error_log("Slot REJECTED (class conflict): team=$tid day=$day time=$timeSlot");
                        continue;
                    }

                    $hasRoom = false;
                    foreach ($rooms as $room) {
                        error_log("LOOP computeSchedulerSlotContext: room=" . (string) $room);
                        $probe = [
                            'team_id' => $tid,
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];
                        $probeConflicts = validateCandidateSlot($pdo, $probe, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration);
                        $probeOk = empty($probeConflicts);
                        error_log("COND computeSchedulerSlotContext: probe_ok=" . ($probeOk ? 'true' : 'false'));
                        if ($probeOk) {
                            $hasRoom = true;
                            break;
                        }
                    }
                    error_log("COND computeSchedulerSlotContext: has_room_for_slot=" . ($hasRoom ? 'true' : 'false'));
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
                $hasSlotsForDay = !empty($slotsByTeamDay[$tid][$day]);
                error_log("COND computeSchedulerSlotContext: team_day_has_slots=" . ($hasSlotsForDay ? 'true' : 'false'));
                if ($hasSlotsForDay) {
                    $eligibleDaysByTeam[$tid][] = $day;
                }
            }
        }

        $schedulerDays = [];
        foreach ($days as $d) {
            $usableThisDay = false;
            foreach ($teams as $team) {
                $teamHasSlots = !empty($slotsByTeamDay[(int) $team['id']][$d]);
                error_log("COND computeSchedulerSlotContext: day=" . (string) $d . " team=" . (string) ($team['id'] ?? '') . " has_slots=" . ($teamHasSlots ? 'true' : 'false'));
                if ($teamHasSlots) {
                    $usableThisDay = true;
                    break;
                }
            }
            error_log("COND computeSchedulerSlotContext: usable_this_day=" . ($usableThisDay ? 'true' : 'false'));
            if ($usableThisDay) {
                $schedulerDays[] = $d;
            }
        }

        $daysWithNoSlots = [];
        foreach ($days as $d) {
            $isInSchedulerDays = in_array($d, $schedulerDays, true);
            error_log("COND computeSchedulerSlotContext: day_in_scheduler_days=" . ($isInSchedulerDays ? 'true' : 'false'));
            if (!$isInSchedulerDays) {
                $daysWithNoSlots[] = $d;
            }
        }

        error_log("=== COMPUTE SLOT CONTEXT END ===");

        $result = [
            'slotsByTeamDay' => $slotsByTeamDay,
            'eligibleDaysByTeam' => $eligibleDaysByTeam,
            'schedulerDays' => $schedulerDays,
            'daysWithNoSlots' => $daysWithNoSlots,
        ];
        error_log("EXIT computeSchedulerSlotContext: schedulerDays_count=" . count($schedulerDays) . ", daysWithNoSlots_count=" . count($daysWithNoSlots));
        return $result;
    }

    /**
     * Count of distinct (team, day, start time, room) placements that pass the same validation as the GA candidate pool.
     */
    function countFeasibleDefensePlacements($pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $userSchedules, $duration): int
    {
        error_log("ENTER countFeasibleDefensePlacements: teams_count=" . count($teams) . ", rooms_count=" . count($rooms));
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);
        $n = 0;
        foreach ($teams as $team) {
            error_log("LOOP countFeasibleDefensePlacements: team_id=" . (string) ($team['id'] ?? ''));
            $tid = (int) $team['id'];
            $teamPanelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($teamPanelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            foreach (($eligibleDaysByTeam[$tid] ?? []) as $day) {
                error_log("LOOP countFeasibleDefensePlacements: day=" . (string) $day);
                foreach (scheduler_slots_for_team_day($slotsByTeamDay, $tid, $day) as $timeSlot) {
                    error_log("LOOP countFeasibleDefensePlacements: timeSlot=" . (string) $timeSlot);
                    foreach ($rooms as $room) {
                        error_log("LOOP countFeasibleDefensePlacements: room=" . (string) $room);
                        $defense = [
                            'team_id' => $team['id'],
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];
                        $conflicts = validateCandidateSlot($pdo, $defense, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration);
                        $ok = empty($conflicts);
                        error_log("COND countFeasibleDefensePlacements: candidate_ok=" . ($ok ? 'true' : 'false'));
                        if ($ok) {
                            $n++;
                        }
                    }
                }
            }
        }

        error_log("EXIT countFeasibleDefensePlacements: feasible_count=" . (string) $n);
        return $n;
    }

    /**
     * Final gate: preview JSON rows must pass the same validateCandidateSlot checks as GA (members, panelists, room timetable).
     * Catches second-pass synthesis and any drift vs class schedules.
     *

    function buildBlockedTimeSlotMap($pdo, $teams, $panelists, $rooms, $days, $timeSlots, $userSchedules, $duration)
    {
        error_log("ENTER buildBlockedTimeSlotMap: teams_count=" . count($teams) . ", rooms_count=" . count($rooms) . ", days_count=" . count($days) . ", timeSlots_count=" . count($timeSlots));
        $blockedByDay = [];
        $existingSchedules = fetchExistingDefenseSchedules($pdo);
        $roomOccupancyMap = buildRoomOccupancyMap($userSchedules);
        $selectedPanelistsByTeam = [];

        foreach ($teams as $team) {
            error_log("LOOP buildBlockedTimeSlotMap: team_id=" . (string) ($team['id'] ?? ''));
            $selectedPanelistsByTeam[(int) $team['id']] = selectPanelists(
                fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists),
                $panelists,
                $team['adviser_id'],
                $team['id']
            );
        }

        foreach ($days as $day) {
            error_log("LOOP buildBlockedTimeSlotMap: day=" . (string) $day);
            $dayBlocked = [];

            foreach ($timeSlots as $timeSlot) {
                error_log("LOOP buildBlockedTimeSlotMap: timeSlot=" . (string) $timeSlot);
                $isUsable = false;
                $reasonCounts = [];
                $sampleReasons = [];

                foreach ($teams as $team) {
                    error_log("LOOP buildBlockedTimeSlotMap: team_id=" . (string) ($team['id'] ?? ''));
                    $teamId = (int) $team['id'];
                    $selectedPanelists = $selectedPanelistsByTeam[$teamId] ?? [];

                    foreach ($rooms as $room) {
                        error_log("LOOP buildBlockedTimeSlotMap: room=" . (string) $room);
                        $candidate = [
                            'team_id' => $teamId,
                            'panelist_ids' => $selectedPanelists,
                            'room' => $room,
                            'time_slot' => $timeSlot,
                            'day' => $day,
                            'defense_type' => $team['defense_type'] ?? 'title_proposal',
                        ];

                        $classConflicts = validateCandidateClassConflicts($pdo, $candidate, $userSchedules, $duration);
                        $hasClassConflicts = !empty($classConflicts);
                        error_log("COND buildBlockedTimeSlotMap: class_conflicts=" . ($hasClassConflicts ? 'true' : 'false'));
                        if ($hasClassConflicts) {
                            foreach ($classConflicts as $conflict) {
                                $reasonCounts[$conflict] = ($reasonCounts[$conflict] ?? 0) + 1;
                            }
                            $needSamples = (count($sampleReasons) < 5);
                            error_log("COND buildBlockedTimeSlotMap: need_samples_class=" . ($needSamples ? 'true' : 'false'));
                            if ($needSamples) {
                                foreach ($classConflicts as $conflict) {
                                    $alreadySampled = in_array($conflict, $sampleReasons, true);
                                    error_log("COND buildBlockedTimeSlotMap: class_conflict_sampled=" . ($alreadySampled ? 'true' : 'false'));
                                    if (!$alreadySampled) {
                                        $sampleReasons[] = $conflict;
                                    }
                                    $sampleLimitReached = (count($sampleReasons) >= 5);
                                    error_log("COND buildBlockedTimeSlotMap: class_sample_limit_reached=" . ($sampleLimitReached ? 'true' : 'false'));
                                    if ($sampleLimitReached) {
                                        break;
                                    }
                                }
                            }
                            continue;
                        }

                        $conflicts = validateCandidateSlot($pdo, $candidate, $userSchedules, $existingSchedules, $roomOccupancyMap, $duration);
                        $hasConflicts = !empty($conflicts);
                        error_log("COND buildBlockedTimeSlotMap: candidate_conflicts=" . ($hasConflicts ? 'true' : 'false'));
                        if (!$hasConflicts) {
                            $isUsable = true;
                            break 2;
                        }

                        foreach ($conflicts as $conflict) {
                            $reasonCounts[$conflict] = ($reasonCounts[$conflict] ?? 0) + 1;
                        }
                        $needSamples = (count($sampleReasons) < 5);
                        error_log("COND buildBlockedTimeSlotMap: need_samples_conflicts=" . ($needSamples ? 'true' : 'false'));
                        if ($needSamples) {
                            foreach ($conflicts as $conflict) {
                                $alreadySampled = in_array($conflict, $sampleReasons, true);
                                error_log("COND buildBlockedTimeSlotMap: conflict_sampled=" . ($alreadySampled ? 'true' : 'false'));
                                if (!$alreadySampled) {
                                    $sampleReasons[] = $conflict;
                                }
                                $sampleLimitReached = (count($sampleReasons) >= 5);
                                error_log("COND buildBlockedTimeSlotMap: sample_limit_reached=" . ($sampleLimitReached ? 'true' : 'false'));
                                if ($sampleLimitReached) {
                                    break;
                                }
                            }
                        }
                    }
                }

                error_log("COND buildBlockedTimeSlotMap: is_usable=" . ($isUsable ? 'true' : 'false'));
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

            $hasDayBlocked = !empty($dayBlocked);
            error_log("COND buildBlockedTimeSlotMap: day_has_blocked=" . ($hasDayBlocked ? 'true' : 'false'));
            if ($hasDayBlocked) {
                $blockedByDay[$day] = $dayBlocked;
            }
        }

        error_log("EXIT buildBlockedTimeSlotMap: blocked_days=" . count($blockedByDay));
        return $blockedByDay;
    }

    /**
     * @return list<string> Human-readable failures (empty = OK)
     */
    function validatePreviewScheduleRowsAgainstClasses($pdo, array $previewRows, $userSchedules, $duration): array
    {
        error_log("ENTER validatePreviewScheduleRowsAgainstClasses: rows_count=" . count($previewRows));
        $existing = fetchExistingDefenseSchedules($pdo);
        $roomMap = buildRoomOccupancyMap($userSchedules);
        
        // Extract team IDs from preview rows
        $teamIds = [];
        foreach ($previewRows as $row) {
            $hasTeamId = !empty($row['team_id']);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: row_has_team_id=" . ($hasTeamId ? 'true' : 'false'));
            if ($hasTeamId) {
                $teamIds[] = (int) $row['team_id'];
            }
        }
        $teamIds = array_values(array_unique($teamIds));
        error_log("validatePreviewScheduleRowsAgainstClasses: teamIds_count=" . count($teamIds));
        
        // Augment occupancy map with all team schedules (fetches from DB like preview overlay does)
        augmentOccupancyMapWithTeamSchedules($pdo, $roomMap, $teamIds);
        
        $failures = [];

        foreach ($previewRows as $row) {
            $missingRequired = empty($row['team_id']) || empty($row['schedule_date']) || $row['start_time'] === null || $row['start_time'] === '';
            error_log("COND validatePreviewScheduleRowsAgainstClasses: missing_required=" . ($missingRequired ? 'true' : 'false'));
            if ($missingRequired) {
                continue;
            }
            $ts = trim((string) $row['start_time']);
            $hasSeconds = (strlen($ts) >= 8);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: has_seconds=" . ($hasSeconds ? 'true' : 'false'));
            if ($hasSeconds) {
                $ts = substr($ts, 0, 5);
            }

            $p1 = isset($row['panelist_id']) ? (int) $row['panelist_id'] : 0;
            $p2 = isset($row['panelist_id2']) ? (int) $row['panelist_id2'] : 0;
            $p3 = isset($row['panelist_id3']) ? (int) $row['panelist_id3'] : 0;
            $panelIds = array_values(array_filter([$p1, $p2, $p3], function ($id) {
                return $id > 0;
            }));
            error_log("validatePreviewScheduleRowsAgainstClasses: panelIds_count=" . count($panelIds));

            $normalizedDay = scheduler_calendar_day_from_raw($row['schedule_date']);
            $normalizedNull = ($normalizedDay === null);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: normalized_day_null=" . ($normalizedNull ? 'true' : 'false'));
            if ($normalizedNull) {
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

            $insufficientPanelists = (count($panelIds) < 3);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: insufficient_panelists=" . ($insufficientPanelists ? 'true' : 'false'));
            if ($insufficientPanelists) {
                $failures[] = 'Team ' . $defense['team_id'] . ': preview row has fewer than 3 panelists.';

                continue;
            }

            // Only check room/venue conflicts. Skip class conflicts since GA already validated against pre-filtered slots.
            $roomErrs = [];
            $defenseDay = $normalizedDay ?? $row['schedule_date'];
            $ts = trim((string) $row['start_time']);
            $hasSeconds2 = (strlen($ts) >= 8);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: has_seconds_recheck=" . ($hasSeconds2 ? 'true' : 'false'));
            if ($hasSeconds2) {
                $ts = substr($ts, 0, 5);
            }
            $range = parseTimeRange($defenseDay, $ts, $duration);
            $rangeOk = ($range !== null);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: range_ok=" . ($rangeOk ? 'true' : 'false'));
            if ($rangeOk) {
                $defenseStart = $range['start'];
                $defenseEnd = $range['end'];
                $defenseDayOfWeek = $range['day_of_week'];
                $roomValue = $defense['room'] ?? '';
                
                // Check room occupancy
                $roomMapHasDay = $roomValue !== '' && isset($roomMap[$roomValue][$defenseDayOfWeek]);
                error_log("COND validatePreviewScheduleRowsAgainstClasses: room_map_has_day=" . ($roomMapHasDay ? 'true' : 'false'));
                if ($roomMapHasDay) {
                    foreach ($roomMap[$roomValue][$defenseDayOfWeek] as $occupied) {
                        $occStart = scheduler_unix_on_calendar_day($defenseDay, $occupied['start_time'] ?? '');
                        $occEnd = scheduler_unix_on_calendar_day($defenseDay, $occupied['end_time'] ?? '');
                        $occInvalid = ($occStart === false || $occEnd === false);
                        error_log("COND validatePreviewScheduleRowsAgainstClasses: occ_invalid=" . ($occInvalid ? 'true' : 'false'));
                        if ($occInvalid) {
                            continue;
                        }
                        $overlap = slotRangesOverlap($defenseStart, $defenseEnd, $occStart, $occEnd);
                        error_log("COND validatePreviewScheduleRowsAgainstClasses: room_overlap=" . ($overlap ? 'true' : 'false'));
                        if ($overlap) {
                            $roomErrs[] = "Room schedule conflict: room {$roomValue} occupied by " . schedulerClassRowDescriptor($occupied);
                            break;
                        }
                    }
                }
                
                // Check existing defense occupancy
                foreach ($existing as $existingDef) {
                    $dayMatch = ($existingDef['day'] === $defenseDay);
                    error_log("COND validatePreviewScheduleRowsAgainstClasses: existing_day_match=" . ($dayMatch ? 'true' : 'false'));
                    if (!$dayMatch) {
                        continue;
                    }
                    $existingStart = scheduler_unix_on_calendar_day($defenseDay, $existingDef['start_time'] ?? '');
                    $existingEnd = scheduler_unix_on_calendar_day($defenseDay, $existingDef['end_time'] ?? '');
                    $existingInvalid = ($existingStart === false || $existingEnd === false);
                    error_log("COND validatePreviewScheduleRowsAgainstClasses: existing_range_invalid=" . ($existingInvalid ? 'true' : 'false'));
                    if ($existingInvalid) {
                        continue;
                    }
                    $existOverlap = slotRangesOverlap($defenseStart, $defenseEnd, $existingStart, $existingEnd);
                    $roomMatch = ($existingDef['room'] === $roomValue);
                    error_log("COND validatePreviewScheduleRowsAgainstClasses: existing_overlap=" . ($existOverlap ? 'true' : 'false') . ", room_match=" . ($roomMatch ? 'true' : 'false'));
                    if ($existOverlap && $roomMatch) {
                        $roomErrs[] = "Room already scheduled: team {$existingDef['team_id']} at {$defenseDay}";
                        break;
                    }
                }
            }
            
            $hasRoomErrs = !empty($roomErrs);
            error_log("COND validatePreviewScheduleRowsAgainstClasses: room_errors=" . ($hasRoomErrs ? 'true' : 'false'));
            if ($hasRoomErrs) {
                $failures[] = 'Team ' . $defense['team_id'] . ': ' . implode('; ', array_slice($roomErrs, 0, 3));
            }
        }

        error_log("EXIT validatePreviewScheduleRowsAgainstClasses: failures_count=" . count($failures));
        return $failures;
    }

    function describeValidationIssues($issues, $pdo)
    {
        error_log("ENTER describeValidationIssues: issues_count=" . (is_array($issues) ? count($issues) : 0));
        $issuesEmpty = empty($issues);
        error_log("COND describeValidationIssues: issues_empty=" . ($issuesEmpty ? 'true' : 'false'));
        if ($issuesEmpty) {
            error_log("EXIT describeValidationIssues: no issues");
            return [];
        }

        $teamNames = getTeamNames($pdo, array_keys($issues));
        $messages = [];

        foreach ($issues as $teamId => $entries) {
            error_log("LOOP describeValidationIssues: team_id=" . (string) $teamId . ", entries_count=" . (is_array($entries) ? count($entries) : 0));
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

        error_log("EXIT describeValidationIssues: messages_count=" . count($messages));
        return $messages;
    }

    function summarizeValidationIssues($issues)
    {
        error_log("ENTER summarizeValidationIssues: issues_count=" . (is_array($issues) ? count($issues) : 0));
        $summary = [
            'teams' => count($issues),
            'room' => 0,
            'panelist' => 0,
            'member' => 0,
            'invalid' => 0,
        ];

        foreach ($issues as $entries) {
            error_log("LOOP summarizeValidationIssues: entries_count=" . (is_array($entries) ? count($entries) : 0));
            foreach ($entries as $entry) {
                error_log("LOOP summarizeValidationIssues: entry_conflicts_count=" . (is_array($entry['conflicts'] ?? null) ? count($entry['conflicts']) : 0));
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

        error_log("EXIT summarizeValidationIssues: room=" . (string) $summary['room'] . ", panelist=" . (string) $summary['panelist'] . ", member=" . (string) $summary['member'] . ", invalid=" . (string) $summary['invalid']);
        return $summary;
    }

    function buildValidationFailurePayload($pdo, $validationMode, $validationSummary, $validationIssues)
    {
        error_log("ENTER buildValidationFailurePayload: validationMode=" . (string) $validationMode);
        $issueMessages = describeValidationIssues($validationIssues, $pdo);
        $summaryCounts = summarizeValidationIssues($validationIssues);

        $result = [
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
        error_log("EXIT buildValidationFailurePayload: issues_count=" . count($issueMessages));
        return $result;
    }

    function chooseCandidateFromPool($candidatePool, $teamId)
    {
        error_log("ENTER chooseCandidateFromPool: teamId=" . (string) $teamId);
        $hasCandidates = isset($candidatePool[$teamId]['candidates']) && !empty($candidatePool[$teamId]['candidates']);
        error_log("COND chooseCandidateFromPool: has_candidates=" . ($hasCandidates ? 'true' : 'false'));
        if (!$hasCandidates) {
            error_log("EXIT chooseCandidateFromPool: no candidates");
            return null;
        }

        $choice = $candidatePool[$teamId]['candidates'][array_rand($candidatePool[$teamId]['candidates'])];
        error_log("EXIT chooseCandidateFromPool: picked");
        return $choice;
    }

    /**
     * Get accessible sections for current user based on role
     * - Admin (id=0): All sections
     * - Program Chair (usertype=0, id!=0): All sections in their college
     * - Faculty (usertype=2): Only their assigned sections
     */
    function getAccessibleSections($pdo, $userId, $usertype) {
        error_log("ENTER getAccessibleSections: userId=" . (string) $userId . ", usertype=" . (string) $usertype);
        try {
            // Admin (id=0): All sections
            $isAdmin = ($userId === 0 && $usertype === 0);
            error_log("COND getAccessibleSections: is_admin=" . ($isAdmin ? 'true' : 'false'));
            if ($isAdmin) {
                $stmt = $pdo->prepare("SELECT DISTINCT section FROM users WHERE section IS NOT NULL ORDER BY section");
                $stmt->execute();
                error_log("EXIT getAccessibleSections: admin_sections");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Program Chair (usertype=0, id!=0): All sections in their college
            $isChair = ($usertype === 0 && $userId !== 0);
            error_log("COND getAccessibleSections: is_chair=" . ($isChair ? 'true' : 'false'));
            if ($isChair) {
                require_once __DIR__ . '/../../assets/includes/auth_functions.php';
                $userCollege = get_user_college($pdo, $userId);
                
                $hasCollege = (bool) $userCollege;
                error_log("COND getAccessibleSections: has_college=" . ($hasCollege ? 'true' : 'false'));
                if (!$hasCollege) {
                    error_log("EXIT getAccessibleSections: no college");
                    return [];
                }

                $stmt = $pdo->prepare("
                    SELECT DISTINCT u.section FROM users u
                    LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                    WHERE u.section IS NOT NULL AND p.college = :college
                    ORDER BY u.section
                ");
                $stmt->execute([':college' => $userCollege]);
                error_log("EXIT getAccessibleSections: chair_sections");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Faculty (usertype=2): Only their assigned sections
            $isFaculty = ($usertype === 2);
            error_log("COND getAccessibleSections: is_faculty=" . ($isFaculty ? 'true' : 'false'));
            if ($isFaculty) {
                require_once __DIR__ . '/../../dashboard/includes/section_access.php';
                $sections = getProfessorSections($pdo, $userId);
                error_log("EXIT getAccessibleSections: faculty_sections_count=" . (is_array($sections) ? count($sections) : 0));
                return $sections;
            }

            error_log("EXIT getAccessibleSections: default_empty");
            return [];
        } catch (Exception $e) {
            error_log("Error getting accessible sections: " . $e->getMessage());
            error_log("EXIT getAccessibleSections: exception");
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
$GLOBALS['schedulerPanelists'] = $panelists;
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
        $GLOBALS['schedulerPanelists'] = $panelists;
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

        // ENHANCEMENT: If user enabled smart slot generation or provided startTime/endTime,
        // intelligently filter time slots by removing class schedule conflicts
        // Auto-enable if startTime/endTime are provided (even without explicit flag)
        $hasTimeRange = isset($_POST['startTime']) && isset($_POST['endTime']) && !empty($_POST['startTime']) && !empty($_POST['endTime']);
        $smartSlotGeneration = isset($_POST['smartSlotGeneration']) && $_POST['smartSlotGeneration'] === 'true';
        $autoEnabledSmartGeneration = $hasTimeRange && !isset($_POST['smartSlotGeneration']); // Auto-enable if time range provided
        
        error_log("SCHEDULER: Smart slot generation check:");
        error_log("  - hasTimeRange: " . ($hasTimeRange ? 'YES' : 'NO') . " (startTime=" . $_POST['startTime'] . ", endTime=" . $_POST['endTime'] . ")");
        error_log("  - smartSlotGeneration flag: " . ($smartSlotGeneration ? 'YES' : 'NO'));
        error_log("  - autoEnabledSmartGeneration: " . ($autoEnabledSmartGeneration ? 'YES' : 'NO'));
        error_log("  - Will use smart generation: " . (($smartSlotGeneration || $autoEnabledSmartGeneration) ? 'YES' : 'NO'));
        
        if ($hasTimeRange && ($smartSlotGeneration || $autoEnabledSmartGeneration)) {
            error_log("SCHEDULER: Using smart time slot generation (automatically excluding class schedules)");
            if ($autoEnabledSmartGeneration) {
                error_log("SCHEDULER: Auto-enabled smart slot generation (startTime/endTime provided)");
            }
            updateProgress($pdo, $progressId, 'running', 'Generating time slots while excluding class schedules…', 24);
            
            $startTime = trim((string)($_POST['startTime'] ?? ''));
            $endTime = trim((string)($_POST['endTime'] ?? ''));
            
            if ($startTime && $endTime) {
                // Build slots for each day, excluding class conflicts
                error_log("SCHEDULER: Building time slots from $startTime to $endTime for " . count($days) . " days");
                $slotsByDay = buildSchedulerTimeSlotsExcludingClasses($startTime, $endTime, $duration, $days, $userSchedules);
                
                if (!empty($slotsByDay)) {
                    // Flatten the slots by day into a single list
                    $allAvailableSlots = [];
                    foreach ($slotsByDay as $day => $slots) {
                        $allAvailableSlots = array_merge($allAvailableSlots, $slots);
                    }
                    $allAvailableSlots = array_values(array_unique($allAvailableSlots));
                    
                    if (!empty($allAvailableSlots)) {
                        error_log("SCHEDULER: Before ceiling filter: " . count($allAvailableSlots) . " slots");
                        $timeSlots = filter_time_slots_respecting_latest_end($allAvailableSlots, $duration);
                        error_log("SCHEDULER: Smart generation produced " . count($timeSlots) . " conflict-free time slots");
                        $_POST['timeSlots'] = $timeSlots;
                    }
                } else {
                    error_log("SCHEDULER: Smart slot generation found no available slots on any day");
                }
            }
        }

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
                'Pre-validation flagged potential unresolved teams; proceeding with optimization and final conflict repair.',
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
        $unresolvedTeamIds = array_values(array_diff($requestedScopeTeamIds, $scheduledTeamIds));

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
        $unresolvedTeamIds = array_values(array_diff($requestedScopeTeamIds, $scheduledTeamIds));
        $finalValidationSummary = $validationSummary;
        $finalValidationSummary['unresolvedTeams'] = $unresolvedTeamIds;

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
                'validationSummary' => $finalValidationSummary,
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
                    'validationSummary' => $finalValidationSummary,
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
    $raw = trim((string) $dayRaw);
    if ($raw === '') {
        return null;
    }

    // Prefer explicit parsing for ambiguous numeric dates.
    // UI often submits "MM-DD-YYYY" (e.g. 05-11-2026 meaning May 11, 2026).
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw)) {
        $dt = DateTime::createFromFormat('m-d-Y', $raw);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        return $raw;
    }

    $ts = strtotime($raw);
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
    $scheduleDayOfWeek = scheduler_schedule_day_of_week($schedRow);
    if ($scheduleDayOfWeek === null || (int) date('w', strtotime($ymd)) !== (int) $scheduleDayOfWeek) {
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
    error_log("ENTER validateInputs");
    // Check for required fields
    $requiredFields = ['timeDuration', 'rooms', 'timeSlots', 'days'];
    foreach ($requiredFields as $field) {
        $missing = !isset($_POST[$field]) || empty($_POST[$field]);
        error_log("COND validateInputs: field=" . (string) $field . " missing=" . ($missing ? 'true' : 'false'));
        if ($missing) {
            error_log("Missing required field: " . $field);
            error_log("EXIT validateInputs: false");
            return false;
        }
    }
    
    // Validate time duration is a positive number
    $durationInvalid = !is_numeric($_POST['timeDuration']) || floatval($_POST['timeDuration']) <= 0;
    error_log("COND validateInputs: duration_invalid=" . ($durationInvalid ? 'true' : 'false'));
    if ($durationInvalid) {
        error_log("Invalid time duration: " . $_POST['timeDuration']);
        error_log("EXIT validateInputs: false");
        return false;
    }
    
    // Validate rooms array
    $roomsInvalid = !is_array($_POST['rooms']) || empty($_POST['rooms']);
    error_log("COND validateInputs: rooms_invalid=" . ($roomsInvalid ? 'true' : 'false'));
    if ($roomsInvalid) {
        error_log("Invalid rooms array");
        error_log("EXIT validateInputs: false");
        return false;
    }
    
    // Validate timeSlots array
    $timeSlotsInvalid = !is_array($_POST['timeSlots']) || empty($_POST['timeSlots']);
    error_log("COND validateInputs: timeSlots_invalid=" . ($timeSlotsInvalid ? 'true' : 'false'));
    if ($timeSlotsInvalid) {
        error_log("Invalid timeSlots array");
        error_log("EXIT validateInputs: false");
        return false;
    }
    
    // Validate days array
    $daysInvalid = !is_array($_POST['days']) || empty($_POST['days']);
    error_log("COND validateInputs: days_invalid=" . ($daysInvalid ? 'true' : 'false'));
    if ($daysInvalid) {
        error_log("Invalid days array");
        error_log("EXIT validateInputs: false");
        return false;
    }
    
    error_log("EXIT validateInputs: true");
    return true;
}

function parseSchedulerTeamIdList($raw): array
{
    error_log("ENTER parseSchedulerTeamIdList");
    if (is_string($raw)) {
        $trimmed = trim($raw);
        $trimmedEmpty = ($trimmed === '');
        error_log("COND parseSchedulerTeamIdList: trimmed_empty=" . ($trimmedEmpty ? 'true' : 'false'));
        if ($trimmedEmpty) {
            error_log("EXIT parseSchedulerTeamIdList: empty string");
            return [];
        }
        $decoded = json_decode($trimmed, true);
        $jsonOk = (json_last_error() === JSON_ERROR_NONE && is_array($decoded));
        error_log("COND parseSchedulerTeamIdList: json_ok=" . ($jsonOk ? 'true' : 'false'));
        if ($jsonOk) {
            $raw = $decoded;
        } else {
            $raw = preg_split('/\s*,\s*/', $trimmed);
        }
    }

    $rawIsArray = is_array($raw);
    error_log("COND parseSchedulerTeamIdList: raw_is_array=" . ($rawIsArray ? 'true' : 'false'));
    if (!$rawIsArray) {
        error_log("EXIT parseSchedulerTeamIdList: raw not array");
        return [];
    }

    $ids = [];
    foreach ($raw as $value) {
        error_log("LOOP parseSchedulerTeamIdList: value=" . (string) $value);
        $id = (int) $value;
        $validId = ($id > 0);
        error_log("COND parseSchedulerTeamIdList: valid_id=" . ($validId ? 'true' : 'false'));
        if ($validId) {
            $ids[] = $id;
        }
    }

    $result = array_values(array_unique($ids));
    error_log("EXIT parseSchedulerTeamIdList: ids_count=" . count($result));
    return $result;
}

/**
 * Generate time slots from startTime to endTime, automatically excluding class schedule conflicts.
 * 
 * Example:
 * - Class schedule: Monday 7am-10am, 12pm-3pm
 * - Input: startTime="07:00", endTime="20:30", duration=1.0 hour, classSchedules=[...]
 * - Output: ["10:00", "11:00", "15:00", "16:00", "17:00", "18:00", "19:00"]
 *
 * @param string $startTime Start time (HH:MM format)
 * @param string $endTime End time (HH:MM format)
 * @param float $durationHours Defense duration in hours
 * @param array $classSchedulesForDay List of class schedules with 'start' and 'end' timestamps (can be from any date)
 * @return array List of available time slots (HH:MM format)
 */
function generateTimeSlotsExcludingClassSchedules(string $startTime, string $endTime, float $durationHours, array $classSchedulesForDay = []): array
{
    error_log("ENTER generateTimeSlotsExcludingClassSchedules: startTime=" . $startTime . ", endTime=" . $endTime . ", durationHours=" . (string) $durationHours . ", classSchedules_count=" . count($classSchedulesForDay));
    $baseAnchor = '2000-06-07'; // Fixed weekday anchor for consistency
    $startTimestamp = strtotime($baseAnchor . ' ' . $startTime);
    $endTimestamp = strtotime($baseAnchor . ' ' . $endTime);
    
    $rangeInvalid = ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp);
    error_log("COND generateTimeSlotsExcludingClassSchedules: range_invalid=" . ($rangeInvalid ? 'true' : 'false'));
    if ($rangeInvalid) {
        error_log("Invalid time range: $startTime to $endTime");
        error_log("EXIT generateTimeSlotsExcludingClassSchedules: empty");
        return [];
    }
    
    // Convert duration to seconds
    $durationSeconds = (int)($durationHours * 3600);
    
    // Determine increment: 60 min for whole hours, 30 min for fractional
    $increment = ($durationHours == intval($durationHours)) ? 3600 : 1800;
    
    // Normalize class schedules - extract ONLY the time-of-day component and convert to anchor date
    $blockedPeriods = [];
    foreach ($classSchedulesForDay as $classSchedule) {
        error_log("LOOP generateTimeSlotsExcludingClassSchedules: classSchedule");
        // Class schedules might be timestamps from a different date, so extract time-of-day only
        $numericTimes = is_numeric($classSchedule['start']) && is_numeric($classSchedule['end']);
        error_log("COND generateTimeSlotsExcludingClassSchedules: numeric_times=" . ($numericTimes ? 'true' : 'false'));
        if ($numericTimes) {
            // Convert from timestamp to time-of-day, then back to anchor date for comparison
            $classStartTime = date('H:i', $classSchedule['start']);
            $classEndTime = date('H:i', $classSchedule['end']);
            $classStart = strtotime($baseAnchor . ' ' . $classStartTime);
            $classEnd = strtotime($baseAnchor . ' ' . $classEndTime);
        } else {
            // Already in time format
            $classStart = strtotime($baseAnchor . ' ' . $classSchedule['start']);
            $classEnd = strtotime($baseAnchor . ' ' . $classSchedule['end']);
        }
        
        $classValid = ($classStart !== false && $classEnd !== false && $classStart < $classEnd);
        error_log("COND generateTimeSlotsExcludingClassSchedules: class_valid=" . ($classValid ? 'true' : 'false'));
        if ($classValid) {
            $blockedPeriods[] = [
                'start' => $classStart,
                'end' => $classEnd,
                'class_name' => $classSchedule['class_name'] ?? 'Class'
            ];
            error_log("    Class block: " . $classSchedule['class_name'] . " (" . date('H:i', $classStart) . "-" . date('H:i', $classEnd) . ")");
        }
    }
    
    // Sort blocked periods by start time
    usort($blockedPeriods, function($a, $b) {
        return $a['start'] - $b['start'];
    });
    
    error_log("  Generating slots from " . date('H:i', $startTimestamp) . " to " . date('H:i', $endTimestamp) . 
              " with duration " . $durationHours . "h, blocked periods: " . count($blockedPeriods));
    
    $availableSlots = [];
    $currentTime = $startTimestamp;
    
    while ($currentTime < $endTimestamp) {
        error_log("LOOP generateTimeSlotsExcludingClassSchedules: currentTime=" . (string) $currentTime);
        $slotEnd = $currentTime + $durationSeconds;
        
        // Check if slot would exceed the end time
        $exceedsEnd = ($slotEnd > $endTimestamp);
        error_log("COND generateTimeSlotsExcludingClassSchedules: exceeds_end=" . ($exceedsEnd ? 'true' : 'false'));
        if ($exceedsEnd) {
            break;
        }
        
        // Check if this time slot conflicts with any class schedule
        $hasConflict = false;
        foreach ($blockedPeriods as $blocked) {
            error_log("LOOP generateTimeSlotsExcludingClassSchedules: blocked_period");
            // Conflict exists if slot overlaps with blocked period
            $conflict = ($currentTime < $blocked['end'] && $slotEnd > $blocked['start']);
            error_log("COND generateTimeSlotsExcludingClassSchedules: conflict=" . ($conflict ? 'true' : 'false'));
            if ($conflict) {
                $hasConflict = true;
                error_log("    ❌ Slot " . date('H:i', $currentTime) . "-" . date('H:i', $slotEnd) . " CONFLICTS with " . $blocked['class_name'] . 
                         " (" . date('H:i', $blocked['start']) . "-" . date('H:i', $blocked['end']) . ")");
                break;
            }
        }
        
        error_log("COND generateTimeSlotsExcludingClassSchedules: has_conflict=" . ($hasConflict ? 'true' : 'false'));
        if (!$hasConflict) {
            $availableSlots[] = date('H:i', $currentTime);
            error_log("    ✅ Slot " . date('H:i', $currentTime) . "-" . date('H:i', $slotEnd) . " is available");
        }
        
        $currentTime += $increment;
    }
    
    error_log("  Generated " . count($availableSlots) . " available time slots after excluding classes");
    $result = array_values(array_unique($availableSlots));
    error_log("EXIT generateTimeSlotsExcludingClassSchedules: slots_count=" . count($result));
    return $result;
}

/**
 * Build time slots for the scheduler, automatically excluding class schedule conflicts.
 * 
 * This function intelligently generates time slots by:
 * 1. Taking the start/end times from scheduler settings
 * 2. Extracting class schedules for each day
 * 3. Removing blocked periods
 * 4. Generating slots only in available time gaps
 * 
 * Example:
 * - Class schedule on Monday: 7am-10am, 12pm-3pm
 * - Scheduler settings: 7am to 8:30pm, 1-hour duration
 * - Result: {"Monday" => ["10:00", "11:00", "15:00", "16:00", ...]}
 *
 * @param string $startTime Start time (HH:MM format)
 * @param string $endTime End time (HH:MM format)
 * @param float $durationHours Defense duration in hours
 * @param array $days List of calendar days (YYYY-MM-DD format)
 * @param array $userSchedules User schedules keyed by user_id
 * @return array Associative array: calendar_day => [list of available time slots]
 */
function buildSchedulerTimeSlotsExcludingClasses(string $startTime, string $endTime, float $durationHours, array $days, array $userSchedules): array
{
    error_log("ENTER buildSchedulerTimeSlotsExcludingClasses: startTime=" . $startTime . ", endTime=" . $endTime . ", durationHours=" . (string) $durationHours . ", days_count=" . count($days));
    $result = [];
    
    error_log("=== Building scheduler time slots excluding class schedules ===");
    error_log("Time window: $startTime to $endTime, Duration: $durationHours hours");
    error_log("Days to process: " . implode(", ", $days));
    
    foreach ($days as $day) {
        error_log("LOOP buildSchedulerTimeSlotsExcludingClasses: day=" . (string) $day);
        $calendarDay = scheduler_calendar_day_from_raw((string) $day);
        $calendarNull = ($calendarDay === null);
        error_log("COND buildSchedulerTimeSlotsExcludingClasses: calendar_day_null=" . ($calendarNull ? 'true' : 'false'));
        if ($calendarNull) {
            error_log("Warning: Could not normalize day '$day'");
            continue;
        }
        
        error_log("Processing day: $calendarDay (" . date('l', strtotime($calendarDay)) . ")");
        
        // Extract class schedule blocks for this day
        $classBlocks = extractClassScheduleBlocksForDay($calendarDay, $userSchedules);
        error_log("  Found " . count($classBlocks) . " class schedule blocks");
        
        // Generate available time slots excluding class blocks
        $availableSlots = generateTimeSlotsExcludingClassSchedules($startTime, $endTime, $durationHours, $classBlocks);
        
        $hasSlots = !empty($availableSlots);
        error_log("COND buildSchedulerTimeSlotsExcludingClasses: has_slots=" . ($hasSlots ? 'true' : 'false'));
        if ($hasSlots) {
            $result[$calendarDay] = $availableSlots;
            error_log("  Generated " . count($availableSlots) . " available slots for $calendarDay");
        } else {
            error_log("  No available slots for $calendarDay (all times blocked by classes)");
        }
    }
    
    error_log("=== Scheduler time slot generation complete ===");
    error_log("EXIT buildSchedulerTimeSlotsExcludingClasses: days_with_slots=" . count($result));
    return $result;
}

/**
 * Extract all class schedule blocks for a specific calendar day from user schedules.
 * 
 * @param string $calendarDay Calendar day in YYYY-MM-DD format
 * @param array $userSchedules User schedules keyed by user_id
 * @return array List of blocked periods with 'start', 'end', 'class_name' keys (timestamps)
 */
function extractClassScheduleBlocksForDay(string $calendarDay, array $userSchedules): array
{
    error_log("ENTER extractClassScheduleBlocksForDay: calendarDay=" . $calendarDay . ", userSchedules_count=" . count($userSchedules));
    $blockedPeriods = [];
    $dayOfWeek = (int) date('w', strtotime($calendarDay));
    
    foreach ($userSchedules as $userId => $schedules) {
        error_log("LOOP extractClassScheduleBlocksForDay: userId=" . (string) $userId . ", schedules_count=" . (is_array($schedules) ? count($schedules) : 0));
        foreach ($schedules as $schedule) {
            // Check if this schedule applies to this day of week
            $scheduleDayOfWeek = scheduler_schedule_day_of_week($schedule);
            $dayMismatch = ($scheduleDayOfWeek === null || (int) $scheduleDayOfWeek !== $dayOfWeek);
            error_log("COND extractClassScheduleBlocksForDay: day_match=" . ($dayMismatch ? 'false' : 'true'));
            if ($dayMismatch) {
                error_log("    Skipping schedule (wrong day of week): dow=" . ($schedule['day_of_week'] ?? 'null') . " vs " . $dayOfWeek);
                continue;
            }
            
            // Skip classes that allow overlap (e.g., research classes)
            $allowsOverlap = ((int)($schedule['allow_overlap'] ?? 0) === 1 || (int)($schedule['is_research_class'] ?? 0) === 1);
            error_log("COND extractClassScheduleBlocksForDay: allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
            if ($allowsOverlap) {
                error_log("    Skipping " . $schedule['class_name'] . " (allows overlap/research)");
                continue;
            }
            
            // Get the actual time range for this schedule on this calendar day
            $classRange = scheduler_user_class_range_on_calendar_day($calendarDay, $schedule);
            $classRangeOk = ($classRange !== null);
            error_log("COND extractClassScheduleBlocksForDay: class_range_ok=" . ($classRangeOk ? 'true' : 'false'));
            if ($classRangeOk) {
                $blockedPeriods[] = [
                    'start' => $classRange['start'],
                    'end' => $classRange['end'],
                    'class_name' => $schedule['class_name'] ?? 'Class',
                    'user_id' => $userId
                ];
                error_log("    Added class block: " . $schedule['class_name'] . " (" . date('H:i', $classRange['start']) . "-" . date('H:i', $classRange['end']) . ") for user $userId");
            } else {
                error_log("    Could not get class range for " . $schedule['class_name'] . " (calendar day normalization issue?)");
            }
        }
    }
    
    error_log("  Total blocked periods extracted: " . count($blockedPeriods));
    foreach ($blockedPeriods as $bp) {
        error_log("    - " . $bp['class_name'] . ": " . date('H:i', $bp['start']) . " to " . date('H:i', $bp['end']));
    }
    error_log("EXIT extractClassScheduleBlocksForDay: blocked_count=" . count($blockedPeriods));
    return $blockedPeriods;
}

/**
 * Drop start times where defense would end after latestClock (default 20:30), matching GA hard cap.
 *
 * @return list<string>
 */
function filter_time_slots_respecting_latest_end(array $slots, float $durationHours, string $latestClock = '20:30'): array
{
    error_log("ENTER filter_time_slots_respecting_latest_end: slots_count=" . count($slots) . ", durationHours=" . (string) $durationHours . ", latestClock=" . $latestClock);
    $out = [];
    $baseAnchor = '2000-06-07'; // fixed weekday anchor for strtotime
    foreach ($slots as $slot) {
        error_log("LOOP filter_time_slots_respecting_latest_end: slot=" . (string) $slot);
        $slot = trim((string) $slot);
        $slotEmpty = ($slot === '');
        error_log("COND filter_time_slots_respecting_latest_end: slot_empty=" . ($slotEmpty ? 'true' : 'false'));
        if ($slotEmpty) {
            continue;
        }
        $start = strtotime($baseAnchor . ' ' . $slot);
        $startInvalid = ($start === false);
        error_log("COND filter_time_slots_respecting_latest_end: start_invalid=" . ($startInvalid ? 'true' : 'false'));
        if ($startInvalid) {
            continue;
        }
        $end = strtotime('+' . $durationHours . ' hours', $start);
        $cap = strtotime($baseAnchor . ' ' . $latestClock);
        $endValid = ($end !== false && $cap !== false && $end <= $cap);
        error_log("COND filter_time_slots_respecting_latest_end: end_valid=" . ($endValid ? 'true' : 'false'));
        if ($endValid) {
            $out[] = date('H:i', $start);
        }
    }

    $result = array_values(array_unique($out));
    error_log("EXIT filter_time_slots_respecting_latest_end: result_count=" . count($result));
    return $result;
}

/** Start times for one team/calendar-day that pass validateCandidateSlot for at least one room */
function scheduler_slots_for_team_day(?array $slotsByTeamDay, $teamId, string $day): array
{
    error_log("ENTER scheduler_slots_for_team_day: teamId=" . (string) $teamId . ", day=" . $day);
    $teamKey = (int) $teamId;
    $slotsMissing = ($slotsByTeamDay === null || !isset($slotsByTeamDay[$teamKey][$day]) || !is_array($slotsByTeamDay[$teamKey][$day]));
    error_log("COND scheduler_slots_for_team_day: slots_missing=" . ($slotsMissing ? 'true' : 'false'));
    if ($slotsMissing) {
        error_log("EXIT scheduler_slots_for_team_day: empty");
        return [];
    }

    $result = array_values(array_filter($slotsByTeamDay[$teamKey][$day], static fn ($t) => trim((string) $t) !== ''));
    error_log("EXIT scheduler_slots_for_team_day: result_count=" . count($result));
    return $result;
}

// Check existing schedules with detailed information (date, status, defense_type)
function checkExistingSchedules($pdo, $sections = []) {
    error_log("ENTER checkExistingSchedules: sections_count=" . (is_array($sections) ? count($sections) : 0));
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
        $hasSections = !empty($sections);
        error_log("COND checkExistingSchedules: has_sections=" . ($hasSections ? 'true' : 'false'));
        if ($hasSections) {
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
        error_log("EXIT checkExistingSchedules: scheduledTeams_count=" . count($scheduledTeams));
        return $scheduledTeams;
    } catch (PDOException $e) {
        error_log("checkExistingSchedules ERROR: " . $e->getMessage());
        error_log("EXIT checkExistingSchedules: exception");
        return [];
    }
}

// Function to get team names from team IDs
function getTeamNames($pdo, $teamIds) {
    error_log("ENTER getTeamNames: teamIds_count=" . (is_array($teamIds) ? count($teamIds) : 0));
    $emptyIds = empty($teamIds);
    error_log("COND getTeamNames: empty_ids=" . ($emptyIds ? 'true' : 'false'));
    if ($emptyIds) {
        error_log("EXIT getTeamNames: empty");
        return [];
    }
    
    try {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($placeholders)");
        $stmt->execute($teamIds);
        
        $teams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $teams[$row['id']] = $row['name'];
        }
        error_log("EXIT getTeamNames: names_count=" . count($teams));
        return $teams;
    } catch (PDOException $e) {
        error_log("Error fetching team names: " . $e->getMessage());
        error_log("EXIT getTeamNames: exception");
        return [];
    }
}

// Function to remove existing FUTURE schedules for specific teams (preserves past schedules)
function removeExistingSchedules($pdo, $teamIds) {
    error_log("ENTER removeExistingSchedules: teamIds_count=" . (is_array($teamIds) ? count($teamIds) : 0));
    $emptyIds = empty($teamIds);
    error_log("COND removeExistingSchedules: empty_ids=" . ($emptyIds ? 'true' : 'false'));
    if ($emptyIds) {
        error_log("removeExistingSchedules: No team IDs provided");
        error_log("EXIT removeExistingSchedules: empty");
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
        error_log("EXIT removeExistingSchedules: deleted=" . (string) $deletedCount);
    } catch (PDOException $e) {
        error_log("removeExistingSchedules ERROR: " . $e->getMessage());
        error_log("EXIT removeExistingSchedules: exception");
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
    error_log("ENTER teamHasCompleteGrades: teamId=" . (string) $teamId . ", scheduleId=" . (string) $scheduleId);
    try {
        // Get the schedule with panelists
        $stmt = $pdo->prepare("
            SELECT panelist_id, panelist_id2, panelist_id3
            FROM defense_schedules
            WHERE id = ?
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $scheduleMissing = !$schedule;
        error_log("COND teamHasCompleteGrades: schedule_missing=" . ($scheduleMissing ? 'true' : 'false'));
        if ($scheduleMissing) {
            error_log("teamHasCompleteGrades: Schedule $scheduleId not found");
            error_log("EXIT teamHasCompleteGrades: false");
            return false;
        }
        
        $panelists = [
            $schedule['panelist_id'],
            $schedule['panelist_id2'],
            $schedule['panelist_id3']
        ];
        error_log("teamHasCompleteGrades: panelists_count=" . count($panelists));
        
        // Check if all panelists have submitted evaluations
        foreach ($panelists as $panelistId) {
            $panelistEmpty = empty($panelistId);
            error_log("COND teamHasCompleteGrades: panelist_empty=" . ($panelistEmpty ? 'true' : 'false'));
            if ($panelistEmpty) continue;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM evaluations
                WHERE defense_schedule_id = ? AND evaluator_id = ?
            ");
            $stmt->execute([$scheduleId, $panelistId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $missingEval = ($result['count'] == 0);
            error_log("COND teamHasCompleteGrades: missing_eval=" . ($missingEval ? 'true' : 'false'));
            if ($missingEval) {
                error_log("teamHasCompleteGrades: Team $teamId missing evaluation from panelist $panelistId");
                error_log("EXIT teamHasCompleteGrades: false");
                return false;
            }
        }
        
        error_log("teamHasCompleteGrades: Team $teamId has all grades from all panelists");
        error_log("EXIT teamHasCompleteGrades: true");
        return true;
    } catch (PDOException $e) {
        error_log("teamHasCompleteGrades ERROR: " . $e->getMessage());
        error_log("EXIT teamHasCompleteGrades: exception");
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
    error_log("ENTER handleDefenseProgression: teams_count=" . (is_array($teamsToProgress) ? count($teamsToProgress) : 0));
    $progressedTeams = [];
    
    foreach ($teamsToProgress as $teamId => $data) {
        error_log("LOOP handleDefenseProgression: teamId=" . (string) $teamId);
        $oldSchedule = $data['old_schedule'];
        $status = $data['status'];
        $currentDefenseType = $oldSchedule['defense_type'] ?? 'title_proposal';
        
        // Determine new defense type based on status
        $statusPassed = ($status === 'passed');
        error_log("COND handleDefenseProgression: status_passed=" . ($statusPassed ? 'true' : 'false'));
        if ($statusPassed) {
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
    
    error_log("EXIT handleDefenseProgression: progressed_count=" . count($progressedTeams));
    return $progressedTeams;
}

// Update fetchTeams to handle multiple sections and respect next_defense_type
// Also includes locked panelists for panelist lock feature
function fetchTeams($pdo, $sections = [])
{
    error_log("ENTER fetchTeams: sections_count=" . (is_array($sections) ? count($sections) : 0));
    $noSections = empty($sections);
    error_log("COND fetchTeams: no_sections=" . ($noSections ? 'true' : 'false'));
    if ($noSections) {
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
    error_log("EXIT fetchTeams: teams_count=" . count($teams));
    return $teams;
}

function fetchPanelists($pdo)
{
    error_log("ENTER fetchPanelists");
    $stmt = $pdo->query("SELECT id, area_of_expertise, is_parttime, is_external FROM users WHERE usertype = 2 OR (usertype = 0 AND id != 0)");
    $panelists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedPanelists = [];
    foreach ($panelists as $panelist) {
        $formattedPanelists[$panelist['id']] = [
            'expertise'    => $panelist['area_of_expertise'],
            'is_parttime'  => (int)($panelist['is_parttime'] ?? 0),
            'is_external'  => (int)($panelist['is_external'] ?? 0),
        ];
    }

    error_log("EXIT fetchPanelists: panelists_count=" . count($formattedPanelists));
    return $formattedPanelists;
}

function fetchPanelistsByProgram($pdo, $teamProgram, $teamExpertise, $allPanelists)
{
    error_log("ENTER fetchPanelistsByProgram: teamProgram=" . (string) $teamProgram . ", teamExpertise=" . (string) $teamExpertise . ", panelists_count=" . (is_array($allPanelists) ? count($allPanelists) : 0));
    $sameProgramPanelists = [];
    $differentProgramPanelists = [];

    // Ensure teamExpertise is a string
    $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';

    foreach ($allPanelists as $panelistId => $panelistData) {
        error_log("LOOP fetchPanelistsByProgram: panelistId=" . (string) $panelistId);
        $panelistInfo = getPanelistData($pdo, $panelistId);
        $expertise = is_array($panelistData) ? ($panelistData['expertise'] ?? '') : '';

        $sameProgram = ($panelistInfo['program'] == $teamProgram);
        error_log("COND fetchPanelistsByProgram: same_program=" . ($sameProgram ? 'true' : 'false'));
        if ($sameProgram) {
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

    $result = [
        'same' => array_column($sameProgramPanelists, 'id'),
        'different' => array_column($differentProgramPanelists, 'id')
    ];
    error_log("EXIT fetchPanelistsByProgram: same_count=" . count($result['same']) . ", diff_count=" . count($result['different']));
    return $result;
}

function getPanelistData($pdo, $panelistId)
{
    error_log("ENTER getPanelistData: panelistId=" . (string) $panelistId);
    static $cache = [];
    $cacheHit = isset($cache[$panelistId]);
    error_log("COND getPanelistData: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if (!$cacheHit) {
        $stmt = $pdo->prepare("SELECT program, area_of_expertise, is_parttime, is_external FROM users WHERE id = ?");
        $stmt->execute([$panelistId]);
        $cache[$panelistId] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    error_log("EXIT getPanelistData");
    return $cache[$panelistId];
}

function fetchUserSchedules($pdo)
{
    error_log("ENTER fetchUserSchedules");
    $overlapCols = userSchedulesHasOverlapExceptionColumns($pdo);
    $allowOverlapSelect = $overlapCols['allow_overlap'] ? 'COALESCE(allow_overlap, 0)' : '0';
    $isResearchClassSelect = $overlapCols['is_research_class'] ? 'COALESCE(is_research_class, 0)' : '0';

    $stmt = $pdo->query("SELECT user_id, day_of_week, start_time, end_time, room, class_name, {$allowOverlapSelect} AS allow_overlap, {$isResearchClassSelect} AS is_research_class FROM user_schedules");
    $schedules = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dowNorm = normalize_user_schedule_day_to_week_int($row['day_of_week'] ?? '');
        $dowNull = ($dowNorm === null);
        error_log("COND fetchUserSchedules: dow_null=" . ($dowNull ? 'true' : 'false'));
        if ($dowNull) {
            continue;
        }
        $row['day_of_week'] = $dowNorm;
        $row['allow_overlap'] = (int) ($row['allow_overlap'] ?? 0);
        $row['is_research_class'] = (int) ($row['is_research_class'] ?? 0);
        $schedules[$row['user_id']][] = $row;
    }

    mergeProgramSectionClassTemplatesIntoUserSchedules($pdo, $schedules);

    error_log("EXIT fetchUserSchedules: users_count=" . count($schedules));
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
    error_log("ENTER geneticAlgorithm: teams_count=" . count($teams) . ", populationSize=" . (string) $populationSize . ", generations=" . (string) $generations . ", mutationRate=" . (string) $mutationRate . ", earlyStop=" . (string) $earlyStopGenerations);
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
        error_log("LOOP geneticAlgorithm: generation=" . (string) $i);
        $generationImproved = false;

        // Update progress every 10 generations
        $updateProgressNow = ($progressId && $i % 10 == 0);
        error_log("COND geneticAlgorithm: update_progress=" . ($updateProgressNow ? 'true' : 'false'));
        if ($updateProgressNow) {
            $percentage = 35 + (($i / $generations) * 50); // Progress from 35% to 85%
            updateProgress($pdo, $progressId, 'running', "Processing generation " . ($i + 1) . " of $generations (best fitness: $bestFitness)", $percentage);
        }

        // Evaluate fitness for each schedule
        foreach ($population as $schedule) {
            error_log("LOOP geneticAlgorithm: evaluate schedule");
            $schedule->calculateFitness($userSchedules);
            $improved = ($schedule->fitness > $bestFitness);
            error_log("COND geneticAlgorithm: improved=" . ($improved ? 'true' : 'false'));
            if ($improved) {
                $bestFitness = $schedule->fitness;
                $bestSchedule = $schedule;
                $generationImproved = true;
                $lastImproveGeneration = $i;

                // === PERFECT CANDIDATE EARLY STOP ===
                // A perfect candidate has zero conflicts (fitness >= 0) and
                // fitness is at or above our estimated theoretical max
                $perfect = ($bestFitness >= $perfectFitnessEstimate && $bestFitness >= 0);
                error_log("COND geneticAlgorithm: perfect_candidate=" . ($perfect ? 'true' : 'false'));
                if ($perfect) {
                    error_log("PERFECT candidate found at generation $i with fitness $bestFitness (threshold: $perfectFitnessEstimate). Stopping early.");
                    $perfectFound = true;
                    break;
                }
            }
        }

        error_log("COND geneticAlgorithm: perfect_found=" . ($perfectFound ? 'true' : 'false'));
        if ($perfectFound) {
            break; // Exit outer loop
        }

        // Also stop early if fitness >= 0 (no conflicts) and we've run enough gens
        $zeroConflictEarly = ($bestFitness >= 0 && $i >= 10);
        error_log("COND geneticAlgorithm: zero_conflict_early=" . ($zeroConflictEarly ? 'true' : 'false'));
        if ($zeroConflictEarly) {
            error_log("Zero-conflict schedule found at generation $i with fitness $bestFitness. Stopping early.");
            break;
        }

        // Early stopping if no improvement for several generations
        $earlyStop = ($i - $lastImproveGeneration >= $earlyStopGenerations);
        error_log("COND geneticAlgorithm: early_stop=" . ($earlyStop ? 'true' : 'false'));
        if ($earlyStop) {
            error_log("Early stopping at generation $i - no improvement for $earlyStopGenerations generations");
            break;
        }

        // Only store data every 5 generations to reduce memory usage
        $storeMetrics = ($i % 5 == 0);
        error_log("COND geneticAlgorithm: store_metrics=" . ($storeMetrics ? 'true' : 'false'));
        if ($storeMetrics) {
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
        $logProgress = ($i % 20 == 0);
        error_log("COND geneticAlgorithm: log_progress=" . ($logProgress ? 'true' : 'false'));
        if ($logProgress) {
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
            error_log("LOOP geneticAlgorithm: child_index=" . (string) $c);
            $parent1 = $selected[mt_rand(0, $selectedCount - 1)];
            $parent2 = $selected[mt_rand(0, $selectedCount - 1)];
            $child = crossover($parent1, $parent2, $userSchedules, $slotsByTeamDay, $eligibleDaysByTeam, $rooms, $panelists, $validCandidatePool);

            // Only mutate some children to save time
            $doMutate = (mt_rand(0, 1) == 1);
            error_log("COND geneticAlgorithm: mutate_child=" . ($doMutate ? 'true' : 'false'));
            if ($doMutate) {
                mutation($child, $mutationRate, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $userSchedules, $validCandidatePool);
            }

            $newPopulation[] = $child;

            // Break early if we have enough children
            $popFull = (count($newPopulation) >= $populationSize);
            error_log("COND geneticAlgorithm: population_full=" . ($popFull ? 'true' : 'false'));
            if ($popFull) {
                break;
            }
        }

        // Only apply diversity preservation occasionally (and less aggressively)
        $doDiversity = ($i % 15 == 0 && $i > 0);
        error_log("COND geneticAlgorithm: diversity_preservation=" . ($doDiversity ? 'true' : 'false'));
        if ($doDiversity) {
            $newPopulation = diversityPreservation($newPopulation, $populationSize, $pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
        }

        $population = $newPopulation;

        // Dynamic Mutation Rate Adjustment every few generations
        $adjustMutation = ($i % 5 == 0);
        error_log("COND geneticAlgorithm: adjust_mutation=" . ($adjustMutation ? 'true' : 'false'));
        if ($adjustMutation) {
            $mutationRate = adjustMutationRate($mutationRate, $population);
        }
    }

    $bestNull = ($bestSchedule === null);
    error_log("COND geneticAlgorithm: best_null=" . ($bestNull ? 'true' : 'false'));
    if ($bestNull) {
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
        error_log("LOOP geneticAlgorithm: alternate_candidate");
        $sigCandidate = $chromosomeSig($candidateSol);
        $seen = isset($signatureSeen[$sigCandidate]);
        error_log("COND geneticAlgorithm: signature_seen=" . ($seen ? 'true' : 'false'));
        if ($seen) {
            continue;
        }
        $signatureSeen[$sigCandidate] = true;
        $alternateSchedules[] = copyDefenseScheduleForPostProcess($candidateSol);
        $altLimit = (count($alternateSchedules) >= 5);
        error_log("COND geneticAlgorithm: alternate_limit=" . ($altLimit ? 'true' : 'false'));
        if ($altLimit) {
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

    $result = [
        'best' => $bestSchedule,
        'alternates' => $alternateSchedules,
    ];
    error_log("EXIT geneticAlgorithm: alternates_count=" . count($alternateSchedules));
    return $result;
}

function createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $validCandidatePool = [])
{
    error_log("ENTER createInitialPopulation: populationSize=" . (string) $populationSize . ", teams_count=" . count($teams));
    $population = [];
    for ($i = 0; $i < $populationSize; $i++) {
        error_log("LOOP createInitialPopulation: index=" . (string) $i);
        $schedule = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
        $hasPool = !empty($validCandidatePool);
        error_log("COND createInitialPopulation: has_pool=" . ($hasPool ? 'true' : 'false'));
        if ($hasPool) {
            foreach ($schedule->chromosomes as $index => $defense) {
                error_log("LOOP createInitialPopulation: chromosome_index=" . (string) $index);
                $candidate = chooseCandidateFromPool($validCandidatePool, $defense['team_id'], $defense);
                $hasCandidate = ($candidate !== null);
                error_log("COND createInitialPopulation: candidate_available=" . ($hasCandidate ? 'true' : 'false'));
                if ($hasCandidate) {
                    $schedule->chromosomes[$index] = $candidate;
                    $schedule->all_defenses[$index] = $candidate;
                }
            }
        }
        $population[] = $schedule;
    }
    error_log("EXIT createInitialPopulation: population_count=" . count($population));
    return $population;
}

function selection($population)
{
    error_log("ENTER selection: population_count=" . (is_array($population) ? count($population) : 0));
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });
    $result = array_slice($population, 0, count($population) / 2);
    error_log("EXIT selection: selected_count=" . count($result));
    return $result;
}

/**
 * Tournament selection - faster than sorting entire population.
 * Picks `numWinners` individuals by running tournaments of `tournamentSize`.
 */
function tournamentSelection($population, $numWinners, $tournamentSize = 3)
{
    error_log("ENTER tournamentSelection: population_count=" . (is_array($population) ? count($population) : 0) . ", numWinners=" . (string) $numWinners . ", tournamentSize=" . (string) $tournamentSize);
    $popSize = count($population);
    $emptyPop = ($popSize === 0);
    error_log("COND tournamentSelection: empty_population=" . ($emptyPop ? 'true' : 'false'));
    if ($emptyPop) {
        error_log("EXIT tournamentSelection: empty");
        return [];
    }
    $winners = [];
    for ($i = 0; $i < $numWinners; $i++) {
        error_log("LOOP tournamentSelection: winner_index=" . (string) $i);
        $best = null;
        for ($t = 0; $t < $tournamentSize; $t++) {
            error_log("LOOP tournamentSelection: tournament_index=" . (string) $t);
            $candidate = $population[mt_rand(0, $popSize - 1)];
            $better = ($best === null || $candidate->fitness > $best->fitness);
            error_log("COND tournamentSelection: better_candidate=" . ($better ? 'true' : 'false'));
            if ($better) {
                $best = $candidate;
            }
        }
        $winners[] = $best;
    }
    error_log("EXIT tournamentSelection: winners_count=" . count($winners));
    return $winners;
}

function crossover($parent1, $parent2, $userSchedules, array $slotsByTeamDay, array $eligibleDaysByTeam, $rooms, $panelists, $validCandidatePool = [])
{
    error_log("ENTER crossover");
    $child = new DefenseSchedule($parent1->pdo, [], [], $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
    $crossoverPoint = rand(0, count($parent1->chromosomes) - 1);
    $child->chromosomes = array_merge(
        array_slice($parent1->chromosomes, 0, $crossoverPoint),
        array_slice($parent2->chromosomes, $crossoverPoint)
    );
    $child->all_defenses = $child->chromosomes;

    foreach ($child->chromosomes as &$defense) {
        error_log("LOOP crossover: defense_team_id=" . (string) ($defense['team_id'] ?? ''));
        $attempts = 0;
        $maxAttempts = 100;
        while (hasConflicts($parent1->pdo, $defense, $userSchedules, $child->all_defenses) && $attempts < $maxAttempts) {
            error_log("LOOP crossover: repair_attempt=" . (string) $attempts);
            if (!empty($validCandidatePool)) {
                $candidate = chooseCandidateFromPool($validCandidatePool, $defense['team_id']);
                $candidateOk = ($candidate !== null)
                    && empty(validateCandidateSlot($parent1->pdo, $candidate, $userSchedules, fetchExistingDefenseSchedules($parent1->pdo), buildRoomOccupancyMap($userSchedules), $GLOBALS['timeDuration']))
                    && !hasConflicts($parent1->pdo, $candidate, $userSchedules, $child->all_defenses);
                error_log("COND crossover: candidate_ok=" . ($candidateOk ? 'true' : 'false'));
                if ($candidateOk) {
                    $defense = $candidate;
                    break;
                }
            }

            $tid = (int) $defense['team_id'];
            $eligible = $eligibleDaysByTeam[$tid] ?? [];
            
            // Try to pick a new day with valid slots
            $dayToUse = $defense['day'];
            $hasEligible = !empty($eligible);
            error_log("COND crossover: has_eligible_days=" . ($hasEligible ? 'true' : 'false'));
            if ($hasEligible) {
                // Try eligible days until we find one with slots
                $eligibleShuffled = $eligible;
                shuffle($eligibleShuffled);
                foreach ($eligibleShuffled as $candidateDay) {
                    $slotsForDay = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $candidateDay);
                    $slotsAvailable = !empty($slotsForDay);
                    error_log("COND crossover: slots_available_for_day=" . ($slotsAvailable ? 'true' : 'false'));
                    if ($slotsAvailable) {
                        $dayToUse = $candidateDay;
                        break;
                    }
                }
            }
            
            $defense['day'] = $dayToUse;
            $slots = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $defense['day']);
            $slotsEmpty = empty($slots);
            error_log("COND crossover: slots_empty=" . ($slotsEmpty ? 'true' : 'false'));
            if ($slotsEmpty) {
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

        $attemptExceeded = ($attempts >= $maxAttempts);
        error_log("COND crossover: attempts_exceeded=" . ($attemptExceeded ? 'true' : 'false'));
        if ($attemptExceeded) {
            $child->fitness -= 50;
        }
    }

    DefenseSchedule::$crossoverCount++;
    error_log("EXIT crossover: crossoverCount=" . (string) DefenseSchedule::$crossoverCount);
    return $child;
}

function mutation($schedule, $mutationRate, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $userSchedules, $validCandidatePool = [])
{
    error_log("ENTER mutation: mutationRate=" . (string) $mutationRate . ", chromosomes_count=" . (is_array($schedule->chromosomes) ? count($schedule->chromosomes) : 0));
    foreach ($schedule->chromosomes as $index => &$defense) {
        $roll = rand() / getrandmax();
        $doMutate = ($roll < $mutationRate);
        error_log("COND mutation: do_mutate=" . ($doMutate ? 'true' : 'false'));
        if ($doMutate) {
            if (!empty($validCandidatePool)) {
                $candidate = chooseCandidateFromPool($validCandidatePool, $defense['team_id']);
                $candidateOk = ($candidate !== null)
                    && empty(validateCandidateSlot($schedule->pdo, $candidate, $userSchedules, fetchExistingDefenseSchedules($schedule->pdo), buildRoomOccupancyMap($userSchedules), $GLOBALS['timeDuration']))
                    && !hasConflicts($schedule->pdo, $candidate, $userSchedules, $schedule->all_defenses);
                error_log("COND mutation: candidate_ok=" . ($candidateOk ? 'true' : 'false'));
                if ($candidateOk) {
                    $original = $defense;
                    $defense = $candidate;
                    $hasConflictsNow = hasConflicts($schedule->pdo, $defense, $userSchedules, $schedule->all_defenses);
                    error_log("COND mutation: candidate_conflicts_after_set=" . ($hasConflictsNow ? 'true' : 'false'));
                    if ($hasConflictsNow) {
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
                    $slotsAvailable = !empty($slotsMut);
                    error_log("COND mutation: slots_available_day=" . ($slotsAvailable ? 'true' : 'false'));
                    if ($slotsAvailable) {
                        $defense['time_slot'] = $slotsMut[array_rand($slotsMut)];
                    }
                    break;
                case 3:
                    $tidMut = (int) $defense['team_id'];
                    $eligibleMut = $eligibleDaysByTeam[$tidMut] ?? [];
                    $eligibleAvailable = !empty($eligibleMut);
                    error_log("COND mutation: eligible_days_available=" . ($eligibleAvailable ? 'true' : 'false'));
                    if ($eligibleAvailable) {
                        $defense['day'] = $eligibleMut[array_rand($eligibleMut)];
                    }
                    $slotsDay = scheduler_slots_for_team_day($slotsByTeamDay, $tidMut, $defense['day']);
                    $slotsAvailable = !empty($slotsDay);
                    error_log("COND mutation: slots_available_new_day=" . ($slotsAvailable ? 'true' : 'false'));
                    if ($slotsAvailable) {
                        $defense['time_slot'] = $slotsDay[array_rand($slotsDay)];
                    }
                    break;
            }
            $hasConflictsNow = hasConflicts($schedule->pdo, $defense, $userSchedules, $schedule->all_defenses);
            error_log("COND mutation: post_mutation_conflicts=" . ($hasConflictsNow ? 'true' : 'false'));
            if ($hasConflictsNow) {
                $defense = $original;
            } else {
                $schedule->all_defenses[$index] = $defense;
                DefenseSchedule::$mutationCount++;
            }
        }
    }
    error_log("EXIT mutation: mutationCount=" . (string) DefenseSchedule::$mutationCount);
}

function hasConflicts($pdo, $defense, $userSchedules, $all_defenses)
{
    error_log("ENTER hasConflicts: team_id=" . (string) ($defense['team_id'] ?? '') . ", day=" . (string) ($defense['day'] ?? '') . ", time_slot=" . (string) ($defense['time_slot'] ?? '') . ", room=" . (string) ($defense['room'] ?? ''));
    static $conflictCache = [];

    // Faster cache key: avoid expensive json_encode + md5
    $key = $defense['team_id'] . '|' . $defense['day'] . '|' . $defense['time_slot'] . '|' . $defense['room'] . '|' . implode(',', $defense['panelist_ids']);

    $cacheHit = isset($conflictCache[$key]);
    error_log("COND hasConflicts: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if ($cacheHit) {
        error_log("EXIT hasConflicts: cached_result=" . ($conflictCache[$key] ? 'true' : 'false'));
        return $conflictCache[$key];
    }

    // Check panelist conflicts (pass currentTeamId so we skip self)
    foreach ($defense['panelist_ids'] as $panelist_id) {
        error_log("LOOP hasConflicts: panelist_id=" . (string) $panelist_id);
        if (hasScheduleConflict($pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses, $defense['team_id'])) {
            $conflictCache[$key] = true;
            error_log("EXIT hasConflicts: panelist_conflict=true");
            return true;
        }
    }

    // Cache team members to avoid repeated queries
    static $teamMembersCache = [];

    // Check conflicts with team members
    $teamCacheHit = isset($teamMembersCache[$defense['team_id']]);
    error_log("COND hasConflicts: team_members_cache_hit=" . ($teamCacheHit ? 'true' : 'false'));
    if (!$teamCacheHit) {
        $teamMembersCache[$defense['team_id']] = getTeamMembers($pdo, $defense['team_id'], 'array');
    }

    foreach ($teamMembersCache[$defense['team_id']] as $member) {
        error_log("LOOP hasConflicts: member_id=" . (string) ($member['id'] ?? ''));
        if (hasScheduleConflict($pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses, $defense['team_id'])) {
            $conflictCache[$key] = true;
            error_log("EXIT hasConflicts: member_conflict=true");
            return true;
        }
    }

    $conflictCache[$key] = false;
    error_log("EXIT hasConflicts: conflict=false");
    return false;
}

function isParttimePanelistAllowedAtTime(string $timeSlot): bool
{
    error_log("ENTER isParttimePanelistAllowedAtTime: timeSlot=" . $timeSlot);
    // Part-time panelists may only be scheduled from 16:00 (4:00 PM) onwards
    $start = strtotime('2000-01-01 ' . $timeSlot);
    $cutoff = strtotime('2000-01-01 16:00');
    $allowed = $start !== false && $cutoff !== false && $start >= $cutoff;
    error_log("EXIT isParttimePanelistAllowedAtTime: allowed=" . ($allowed ? 'true' : 'false'));
    return $allowed;
}

function hasScheduleConflict($pdo, $user_id, $day, $time_slot, $userSchedules, $room, $all_defenses, $currentTeamId = null)
{
    error_log("ENTER hasScheduleConflict: user_id=" . (string) $user_id . ", day=" . (string) $day . ", time_slot=" . (string) $time_slot . ", room=" . (string) $room . ", currentTeamId=" . (string) $currentTeamId);
    $duration = $GLOBALS['timeDuration'];
    $myDay = scheduler_calendar_day_from_raw((string) $day);
    $defRange = scheduler_defense_range_on_day((string) $day, (string) $time_slot, $duration);
    $rangeInvalid = ($myDay === null || $defRange === null);
    error_log("COND hasScheduleConflict: range_invalid=" . ($rangeInvalid ? 'true' : 'false'));
    if ($rangeInvalid) {
        error_log("EXIT hasScheduleConflict: false (invalid range)");
        return false;
    }

    $defense_start = $defRange['start'];
    $defense_end = $defRange['end'];

// PART-TIME RESTRICTION: part-time panelists cannot be scheduled before 4:00 PM
if (isset($GLOBALS['schedulerPanelists'][$user_id]['is_parttime'])
    && (int)$GLOBALS['schedulerPanelists'][$user_id]['is_parttime'] === 1
    && !isParttimePanelistAllowedAtTime((string)$time_slot)) {
    return true; // treat as conflict — blocks the slot
}

    // CHECK 1: User personal schedule (classes) vs this defense
    if (isset($userSchedules[$user_id])) {
        foreach ($userSchedules[$user_id] as $schedule) {
            $sch = scheduler_user_class_range_on_calendar_day($myDay, $schedule);
            $schNull = ($sch === null);
            error_log("COND hasScheduleConflict: schedule_range_null=" . ($schNull ? 'true' : 'false'));
            if ($schNull) {
                continue;
            }
            $overlap = scheduler_slot_ranges_overlap($defense_start, $defense_end, $sch['start'], $sch['end']);
            error_log("COND hasScheduleConflict: schedule_overlap=" . ($overlap ? 'true' : 'false'));
            if ($overlap) {
                error_log("EXIT hasScheduleConflict: true (user schedule)");
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
            $isSelf = ($currentTeamId !== null && $existing_defense['team_id'] == $currentTeamId);
            error_log("COND hasScheduleConflict: is_self=" . ($isSelf ? 'true' : 'false'));
            if ($isSelf) continue;

            $theirDay = scheduler_calendar_day_from_raw((string) ($existing_defense['day'] ?? ''));
            $dayMismatch = ($theirDay === null || $theirDay !== $myDay);
            error_log("COND hasScheduleConflict: day_match=" . ($dayMismatch ? 'false' : 'true'));
            if ($dayMismatch) {
                continue;
            }

            $exRange = scheduler_defense_range_on_day((string) $existing_defense['day'], (string) $existing_defense['time_slot'], $duration);
            $exNull = ($exRange === null);
            error_log("COND hasScheduleConflict: ex_range_null=" . ($exNull ? 'true' : 'false'));
            if ($exNull) {
                continue;
            }

            // Check time overlap
            $timesOverlap = scheduler_slot_ranges_overlap($defense_start, $defense_end, $exRange['start'], $exRange['end']);
            error_log("COND hasScheduleConflict: times_overlap=" . ($timesOverlap ? 'true' : 'false'));
            if (!$timesOverlap) continue;

            // 2a: Same room at overlapping time = room conflict
            if ($existing_defense['room'] == $room) {
                error_log("EXIT hasScheduleConflict: true (room conflict)");
                return true;
            }

            // 2b: Is this user assigned as panelist in the other defense? (panelist double-booking)
            if (in_array($user_id, $existing_defense['panelist_ids'])) {
                error_log("EXIT hasScheduleConflict: true (panelist double-book)");
                return true;
            }

            // 2c: Is this user a team member in the other defense? (student/adviser double-booking)
            $lookupHit = isset($memberLookupCache[$existing_defense['team_id']]);
            error_log("COND hasScheduleConflict: member_cache_hit=" . ($lookupHit ? 'true' : 'false'));
            if (!$lookupHit) {
                $memberLookupCache[$existing_defense['team_id']] = array_column(
                    getTeamMembers($pdo, $existing_defense['team_id'], 'array'), 'id'
                );
            }
            if (in_array($user_id, $memberLookupCache[$existing_defense['team_id']])) {
                error_log("EXIT hasScheduleConflict: true (member double-book)");
                return true;
            }
        }
    }

    error_log("EXIT hasScheduleConflict: false");
    return false;
}

/**
 * Prepare schedule data for preview without saving to DB.
 * Returns array of schedule objects with resolved names.
 */
function prepareScheduleData($pdo, $schedule)
{
    error_log("ENTER prepareScheduleData");
    $expectedTeams = [];
    foreach ($GLOBALS['teams'] as $team) {
        $expectedTeams[] = $team['id'];
    }
    error_log("prepareScheduleData: expectedTeams_count=" . count($expectedTeams));

    $scheduledTeams = [];
    $previewSchedules = [];
    $scheduledDefenses = [];
    $userSchedules = $GLOBALS['schedulerUserSchedulesSnapshot'] ?? fetchUserSchedules($pdo);

    $defenses = $schedule->chromosomes;
    foreach ($defenses as &$defense) {
        $defense['fitness'] = calculateDefenseFitness($pdo, $defense);
    }
    unset($defense);

    $byBestTeam = [];
    foreach ($defenses as $defense) {
        $tid = (int) ($defense['team_id'] ?? 0);
        $tidInvalid = ($tid <= 0);
        error_log("COND prepareScheduleData: tid_invalid=" . ($tidInvalid ? 'true' : 'false'));
        if ($tidInvalid) {
            continue;
        }
        $fit = isset($defense['fitness']) ? (float) $defense['fitness'] : 0.0;
        $prevFit = isset($byBestTeam[$tid]['fitness']) ? (float) $byBestTeam[$tid]['fitness'] : -PHP_INT_MAX;
        $isBetter = (!isset($byBestTeam[$tid]) || $fit > $prevFit);
        error_log("COND prepareScheduleData: best_for_team=" . ($isBetter ? 'true' : 'false'));
        if ($isBetter) {
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
        $alreadyScheduled = in_array($defense['team_id'], $scheduledTeams);
        error_log("COND prepareScheduleData: already_scheduled=" . ($alreadyScheduled ? 'true' : 'false'));
        if ($alreadyScheduled) continue;
        $panelistsOk = isset($defense['panelist_ids']) && count($defense['panelist_ids']) >= 3;
        error_log("COND prepareScheduleData: panelists_ok=" . ($panelistsOk ? 'true' : 'false'));
        if (!$panelistsOk) continue;

        $hasConflict = function_exists('hasConflicts') && hasConflicts($pdo, $defense, $userSchedules, $scheduledDefenses);
        error_log("COND prepareScheduleData: has_conflict=" . ($hasConflict ? 'true' : 'false'));
        if ($hasConflict) {
            error_log('prepareScheduleData: skipping conflicted defense for team ' . $defense['team_id'] . ' — ' . $defense['day'] . ' ' . $defense['time_slot']);
            continue;
        }

        $scheduledTeams[] = $defense['team_id'];
        $scheduledDefenses[] = $defense;
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
    $allowFallback = (false && !empty($missingTeams));
    error_log("COND prepareScheduleData: fallback_enabled=" . ($allowFallback ? 'true' : 'false'));
    if ($allowFallback) {
        foreach ($missingTeams as $missingTeamId) {
            $teamDefense = null;
            foreach ($defenses as $defense) {
                $match = ($defense['team_id'] == $missingTeamId);
                error_log("COND prepareScheduleData: fallback_match=" . ($match ? 'true' : 'false'));
                if ($match) { $teamDefense = $defense; break; }
            }
            if (!$teamDefense) {
                $team = null;
                foreach ($GLOBALS['teams'] as $filteredTeam) {
                    $match = ($filteredTeam['id'] == $missingTeamId);
                    error_log("COND prepareScheduleData: fallback_team_match=" . ($match ? 'true' : 'false'));
                    if ($match) { $team = $filteredTeam; break; }
                }
                $teamFound = (bool) $team;
                error_log("COND prepareScheduleData: fallback_team_found=" . ($teamFound ? 'true' : 'false'));
                if (!$teamFound) continue;

                $eligibleFb = isset($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                    && is_array($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                    && !empty($GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId])
                    ? $GLOBALS['schedulerEligibleDaysByTeam'][(int) $missingTeamId]
                    : (isset($GLOBALS['schedulerDays']) && is_array($GLOBALS['schedulerDays']) ? $GLOBALS['schedulerDays'] : ($_POST['days'] ?? []));
                $slotMapFb = $GLOBALS['schedulerSlotsByTeamDay'] ?? null;
                $pickDayFb = is_array($eligibleFb) && !empty($eligibleFb) ? $eligibleFb[array_rand($eligibleFb)] : date('n/j/Y');
                $slotOpts = scheduler_slots_for_team_day($slotMapFb, (int) $missingTeamId, $pickDayFb);
                $slotOptsEmpty = empty($slotOpts);
                error_log("COND prepareScheduleData: fallback_slot_opts_empty=" . ($slotOptsEmpty ? 'true' : 'false'));
                if ($slotOptsEmpty) {
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
            $panelistsOk = isset($teamDefense['panelist_ids']) && count($teamDefense['panelist_ids']) >= 3;
            error_log("COND prepareScheduleData: fallback_panelists_ok=" . ($panelistsOk ? 'true' : 'false'));
            if (!$panelistsOk) {
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
                $hasErrors = !empty($ve);
                error_log("COND prepareScheduleData: fallback_validation_errors=" . ($hasErrors ? 'true' : 'false'));
                if ($hasErrors) {
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

    error_log("EXIT prepareScheduleData: schedules_count=" . count($previewSchedules));
    return $previewSchedules;
}

function saveScheduleToDatabase($pdo, $schedule)
{
    error_log("ENTER saveScheduleToDatabase");
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
        $scheduledDefenses = [];
        $userSchedules = $GLOBALS['schedulerUserSchedulesSnapshot'] ?? fetchUserSchedules($pdo);
        $teamTitleById = [];
        foreach (($GLOBALS['teams'] ?? []) as $teamRow) {
            $tid = (int) ($teamRow['id'] ?? 0);
            $validTid = ($tid > 0);
            error_log("COND saveScheduleToDatabase: valid_team_id=" . ($validTid ? 'true' : 'false'));
            if ($validTid) {
                $teamTitleById[$tid] = trim((string) ($teamRow['title'] ?? ''));
            }
        }

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
            (team_id, title, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Include notification functions
        require_once dirname(__DIR__) . '/../assets/includes/notification_functions.php';

        // First pass - schedule teams with best fitness
        foreach ($defenses as $defense) {
            // Skip if this team was already scheduled
            $alreadyScheduled = in_array($defense['team_id'], $scheduledTeams);
            error_log("COND saveScheduleToDatabase: already_scheduled=" . ($alreadyScheduled ? 'true' : 'false'));
            if ($alreadyScheduled) {
                continue;
            }

            $hasConflict = function_exists('hasConflicts') && hasConflicts($pdo, $defense, $userSchedules, $scheduledDefenses);
            error_log("COND saveScheduleToDatabase: has_conflict=" . ($hasConflict ? 'true' : 'false'));
            if ($hasConflict) {
                error_log("saveScheduleToDatabase: skipping conflicted defense for team {$defense['team_id']} at {$defense['day']} {$defense['time_slot']}");
                continue;
            }

            $scheduledTeams[] = $defense['team_id'];
            $scheduledDefenses[] = $defense;

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
            $teamId = (int) ($defense['team_id'] ?? 0);
            $title = trim((string) ($defense['title'] ?? ($teamTitleById[$teamId] ?? '')));
            $titleEmpty = ($title === '');
            error_log("COND saveScheduleToDatabase: title_empty=" . ($titleEmpty ? 'true' : 'false'));
            if ($titleEmpty) {
                $title = 'Untitled';
            }

            // Validate panelist_ids array has exactly 3 elements
            $panelistsOk = isset($defense['panelist_ids']) && count($defense['panelist_ids']) >= 3;
            error_log("COND saveScheduleToDatabase: panelists_ok=" . ($panelistsOk ? 'true' : 'false'));
            if (!$panelistsOk) {
                error_log("ERROR: Defense for team {$defense['team_id']} has invalid panelist_ids: " . json_encode($defense['panelist_ids'] ?? 'null'));
                continue; // Skip this defense
            }

            $stmt->execute([
                $teamId,
                $title,
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
        $allowFallback = (false && !empty($missingTeams));
        error_log("COND saveScheduleToDatabase: fallback_enabled=" . ($allowFallback ? 'true' : 'false'));
        if ($allowFallback) {
            error_log("Missing teams detected: " . implode(", ", $missingTeams));

            // For any missed teams, create a schedule forcefully
            foreach ($missingTeams as $missingTeamId) {
                // Find any solution for this team from chromosomes
                $teamDefense = null;
                foreach ($defenses as $defense) {
                    $match = ($defense['team_id'] == $missingTeamId);
                    error_log("COND saveScheduleToDatabase: fallback_match=" . ($match ? 'true' : 'false'));
                    if ($match) {
                        $teamDefense = $defense;
                        break;
                    }
                }

                // If no solution found in chromosomes, create a new one
                if (!$teamDefense) {
                    error_log("Creating fallback schedule for team ID: $missingTeamId");

                    $team = null;
                    foreach ($GLOBALS['teams'] as $filteredTeam) {
                        $match = ($filteredTeam['id'] == $missingTeamId);
                        error_log("COND saveScheduleToDatabase: fallback_team_match=" . ($match ? 'true' : 'false'));
                        if ($match) {
                            $team = $filteredTeam;
                            break;
                        }
                    }
                    $teamFound = (bool) $team;
                    error_log("COND saveScheduleToDatabase: fallback_team_found=" . ($teamFound ? 'true' : 'false'));
                    if (!$teamFound) {
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
                    $slotOptsEmpty = empty($slotOpts);
                    error_log("COND saveScheduleToDatabase: fallback_slot_opts_empty=" . ($slotOptsEmpty ? 'true' : 'false'));
                    if ($slotOptsEmpty) {
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
                $teamId = (int) ($teamDefense['team_id'] ?? 0);
                $title = trim((string) ($teamDefense['title'] ?? ($teamTitleById[$teamId] ?? '')));
                $titleEmpty = ($title === '');
                error_log("COND saveScheduleToDatabase: fallback_title_empty=" . ($titleEmpty ? 'true' : 'false'));
                if ($titleEmpty) {
                    $title = 'Untitled';
                }

                // Validate panelist_ids array has exactly 3 elements
                $panelistsOk = isset($teamDefense['panelist_ids']) && count($teamDefense['panelist_ids']) >= 3;
                error_log("COND saveScheduleToDatabase: fallback_panelists_ok=" . ($panelistsOk ? 'true' : 'false'));
                if (!$panelistsOk) {
                    error_log("ERROR: Defense for missing team {$teamDefense['team_id']} has invalid panelist_ids: " . json_encode($teamDefense['panelist_ids'] ?? 'null'));
                    continue; // Skip this defense
                }

                $stmt->execute([
                    $teamId,
                    $title,
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
        error_log("EXIT saveScheduleToDatabase: success");
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
        error_log("EXIT saveScheduleToDatabase: exception");
        throw new Exception("Failed to save schedule: " . $e->getMessage());
    }
}

// Helper function to parse a day string from either "Y-m-d" or "m-d-Y":
function parseDate($dayStr)
{
    error_log("ENTER parseDate: dayStr=" . (string) $dayStr);
    $date = DateTime::createFromFormat('Y-m-d', $dayStr);
    $dateInvalid = !$date;
    error_log("COND parseDate: ymd_invalid=" . ($dateInvalid ? 'true' : 'false'));
    if ($dateInvalid) {
        $date = DateTime::createFromFormat('m-d-Y', $dayStr);
    }
    error_log("EXIT parseDate: success=" . ($date ? 'true' : 'false'));
    return $date;
}

// Helper function to calculate fitness for a single defense
function calculateDefenseFitness($pdo, $defense)
{
    error_log("ENTER calculateDefenseFitness: team_id=" . (string) ($defense['team_id'] ?? ''));
    static $fitnessCache = [];

    // Create a cache key
    $key = $defense['team_id'] . '-' . implode(',', $defense['panelist_ids']);

    $cacheHit = isset($fitnessCache[$key]);
    error_log("COND calculateDefenseFitness: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if ($cacheHit) {
        error_log("EXIT calculateDefenseFitness: cached");
        return $fitnessCache[$key];
    }

    $fitness = 0;

    // Calculate expertise matching
    $teamExpertise = getTeamExpertise($pdo, $defense['team_id']) ?? '';
    $expertiseMatchFound = false;

    // Ensure we have strings
    $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';

    foreach ($defense['panelist_ids'] as $panelist_id) {
        error_log("LOOP calculateDefenseFitness: panelist_id=" . (string) $panelist_id);
        $panelistExpertise = getPanelistExpertise($pdo, $panelist_id) ?? '';
        $panelistExpertise = is_string($panelistExpertise) ? $panelistExpertise : '';

        $similarity = 0;
        if (!empty($teamExpertise) && !empty($panelistExpertise)) {
            $similarity = similar_text($teamExpertise, $panelistExpertise) /
                max(strlen($teamExpertise), strlen($panelistExpertise)) * 100;
        }

        $isMatch = ($similarity > 70);
        error_log("COND calculateDefenseFitness: similarity_match=" . ($isMatch ? 'true' : 'false'));
        if ($isMatch) {
            $expertiseMatchFound = true;
            $fitness += 50; // Reward for expertise match
        }
        $fitness += $similarity; // Add similarity score
    }

    error_log("COND calculateDefenseFitness: expertise_match_found=" . ($expertiseMatchFound ? 'true' : 'false'));
    if (!$expertiseMatchFound) {
        $fitness -= 100; // Heavy penalty for no expertise match
    }

    // NEW: Add specialization matching to fitness
    $teamSpecializations = getTeamSpecializations($pdo, $defense['team_id']);
    $hasTeamSpecs = !empty($teamSpecializations);
    error_log("COND calculateDefenseFitness: team_specs=" . ($hasTeamSpecs ? 'true' : 'false'));
    if ($hasTeamSpecs) {
        foreach ($defense['panelist_ids'] as $panelist_id) {
            $panelistSpecializations = getUserSpecializations($pdo, $panelist_id);
            $specializationScore = calculateSpecializationMatch($teamSpecializations, $panelistSpecializations);
            $fitness += $specializationScore; // Add specialization matching bonus
        }
    }

    $fitnessCache[$key] = $fitness;
    error_log("EXIT calculateDefenseFitness: fitness=" . (string) $fitness);
    return $fitness;
}

function scheduleClockToMinutes($clock)
{
    error_log("ENTER scheduleClockToMinutes: clock=" . (string) $clock);
    $frag = scheduler_time_fragment_from_db($clock);
    $fragEmpty = ($frag === '');
    error_log("COND scheduleClockToMinutes: frag_empty=" . ($fragEmpty ? 'true' : 'false'));
    if ($fragEmpty) {
        error_log("EXIT scheduleClockToMinutes: null");
        return null;
    }
    $parts = explode(':', $frag);
    $partsShort = (count($parts) < 2);
    error_log("COND scheduleClockToMinutes: parts_short=" . ($partsShort ? 'true' : 'false'));
    if ($partsShort) {
        error_log("EXIT scheduleClockToMinutes: null");
        return null;
    }
    $h = (int) $parts[0];
    $m = (int) $parts[1];
    $invalid = ($h < 0 || $h > 23 || $m < 0 || $m > 59);
    error_log("COND scheduleClockToMinutes: invalid_time=" . ($invalid ? 'true' : 'false'));
    if ($invalid) {
        error_log("EXIT scheduleClockToMinutes: null");
        return null;
    }

    $result = ($h * 60) + $m;
    error_log("EXIT scheduleClockToMinutes: minutes=" . (string) $result);
    return $result;
}

function computeScheduleQualityMetrics(array $preparedRows): array
{
    error_log("ENTER computeScheduleQualityMetrics: rows_count=" . count($preparedRows));
    if (empty($preparedRows)) {
        error_log("EXIT computeScheduleQualityMetrics: empty");
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
        error_log("LOOP computeScheduleQualityMetrics: row");
        $day = (string) ($row['schedule_date'] ?? '');
        $startMin = scheduleClockToMinutes($row['start_time'] ?? '');
        $endMin = scheduleClockToMinutes($row['end_time'] ?? '');
        $invalid = ($day === '' || $startMin === null || $endMin === null || $endMin <= $startMin);
        error_log("COND computeScheduleQualityMetrics: invalid_row=" . ($invalid ? 'true' : 'false'));
        if ($invalid) {
            continue;
        }

        $duration = $endMin - $startMin;
        $sumDuration += $duration;
        $totalRows++;

        $isMorning = ($startMin < 12 * 60);
        error_log("COND computeScheduleQualityMetrics: is_morning=" . ($isMorning ? 'true' : 'false'));
        if ($isMorning) {
            $morningStarts++;
        }

        $room = trim((string) ($row['room'] ?? ''));
        $roomEmpty = ($room === '');
        error_log("COND computeScheduleQualityMetrics: room_empty=" . ($roomEmpty ? 'true' : 'false'));
        if ($roomEmpty) {
            $room = 'UNASSIGNED';
        }
        $roomLoadMin[$room] = ($roomLoadMin[$room] ?? 0) + $duration;

        $rowsByDay[$day][] = [
            'start' => $startMin,
            'end' => $endMin,
            'room' => $room,
        ];
    }

    $noRows = ($totalRows === 0);
    error_log("COND computeScheduleQualityMetrics: no_rows=" . ($noRows ? 'true' : 'false'));
    if ($noRows) {
        error_log("EXIT computeScheduleQualityMetrics: no_rows");
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
            error_log("LOOP computeScheduleQualityMetrics: dayRow_index=" . (string) $i);
            $curr = $dayRows[$i];
            $gap = ($curr['start'] > $prevEnd);
            error_log("COND computeScheduleQualityMetrics: has_gap=" . ($gap ? 'true' : 'false'));
            if ($gap) {
                $totalGap += ($curr['start'] - $prevEnd);
                $gapCount++;
            }
            $newMax = ($curr['end'] > $maxEnd);
            error_log("COND computeScheduleQualityMetrics: new_max_end=" . ($newMax ? 'true' : 'false'));
            if ($newMax) {
                $maxEnd = $curr['end'];
            }
            $newPrev = ($curr['end'] > $prevEnd);
            error_log("COND computeScheduleQualityMetrics: new_prev_end=" . ($newPrev ? 'true' : 'false'));
            if ($newPrev) {
                $prevEnd = $curr['end'];
            }
        }

        $totalSpan += max(0, $maxEnd - $minStart);
    }

    $avgGap = $gapCount > 0 ? ($totalGap / $gapCount) : 0.0;
    $utilization = $totalSpan > 0 ? min(1.0, $sumDuration / $totalSpan) : 0.0;

    $loads = array_values($roomLoadMin);
    $roomStd = 0.0;
    $multiRooms = (count($loads) > 1);
    error_log("COND computeScheduleQualityMetrics: multi_rooms=" . ($multiRooms ? 'true' : 'false'));
    if ($multiRooms) {
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

    $result = [
        'score' => round($score, 2),
        'utilization_pct' => round($utilization * 100, 2),
        'avg_gap_minutes' => round($avgGap, 2),
        'room_balance_std' => round($roomStd, 2),
        'morning_ratio_pct' => round($morningRatio * 100, 2),
    ];
    error_log("EXIT computeScheduleQualityMetrics: score=" . (string) $result['score']);
    return $result;
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
        error_log("ENTER DefenseSchedule::__construct: teams_count=" . (is_array($teams) ? count($teams) : 0) . ", rooms_count=" . (is_array($rooms) ? count($rooms) : 0));
        $this->pdo = $pdo;
        foreach ($teams as $team) {
            error_log("LOOP DefenseSchedule::__construct: team_id=" . (string) ($team['id'] ?? ''));
            $panelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id'], $team['id']);

            // Use defense_type from team data (set during progression logic)
            $defenseType = $team['defense_type'] ?? 'title_proposal';

            $tidConst = (int) $team['id'];
            $eligibleConst = isset($eligibleDaysByTeam[$tidConst]) ? $eligibleDaysByTeam[$tidConst] : [];
            $eligibleWithSlots = [];
            foreach ($eligibleConst as $dayCandidate) {
                $slotCandidateList = scheduler_slots_for_team_day($slotsByTeamDay, $tidConst, $dayCandidate);
                $hasSlots = !empty($slotCandidateList);
                error_log("COND DefenseSchedule::__construct: has_slots_for_day=" . ($hasSlots ? 'true' : 'false'));
                if ($hasSlots) {
                    $eligibleWithSlots[] = $dayCandidate;
                }
            }

            // Only schedule teams with at least one valid slot on an eligible day.
            // Teams with no valid slots will remain unscheduled (unresolved).
            $hasEligibleSlots = !empty($eligibleWithSlots);
            error_log("COND DefenseSchedule::__construct: has_eligible_slots=" . ($hasEligibleSlots ? 'true' : 'false'));
            if ($hasEligibleSlots) {
                $dayPick = $eligibleWithSlots[array_rand($eligibleWithSlots)];
                $slotList = scheduler_slots_for_team_day($slotsByTeamDay, $tidConst, $dayPick);
                $slotPick = !empty($slotList) ? $slotList[array_rand($slotList)] : null;

                // Guard against null slot (should not happen if eligibleWithSlots was built correctly)
                $slotNull = ($slotPick === null);
                error_log("COND DefenseSchedule::__construct: slot_null=" . ($slotNull ? 'true' : 'false'));
                if ($slotNull) {
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
        error_log("EXIT DefenseSchedule::__construct: chromosomes_count=" . count($this->chromosomes));
    }

    public function calculateFitness($userSchedules)
    {
        error_log("ENTER DefenseSchedule::calculateFitness: chromosomes_count=" . (is_array($this->chromosomes) ? count($this->chromosomes) : 0));
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
            error_log("LOOP DefenseSchedule::calculateFitness: defense_team_id=" . (string) ($defense['team_id'] ?? '') . ", defenseKey=" . (string) $defenseKey);
            // Use cached team members data
            $teamCacheHit = isset($teamMembersCache[$defense['team_id']]);
            error_log("COND DefenseSchedule::calculateFitness: team_cache_hit=" . ($teamCacheHit ? 'true' : 'false'));
            if (!$teamCacheHit) {
                $teamMembersCache[$defense['team_id']] = getTeamMembers($this->pdo, $defense['team_id'], 'array');
            }
            $teamMembers = $teamMembersCache[$defense['team_id']];

            // Team member conflict check
            $membersIsArray = is_array($teamMembers);
            error_log("COND DefenseSchedule::calculateFitness: members_is_array=" . ($membersIsArray ? 'true' : 'false'));
            if ($membersIsArray) {
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
                error_log("LOOP DefenseSchedule::calculateFitness: panelist_id=" . (string) $panelist_id);
                // Cache panelist data
                $panelistCacheHit = isset($panelistDataCache[$panelist_id]);
                error_log("COND DefenseSchedule::calculateFitness: panelist_cache_hit=" . ($panelistCacheHit ? 'true' : 'false'));
                if (!$panelistCacheHit) {
                    $panelistDataCache[$panelist_id] = getPanelistData($this->pdo, $panelist_id);
                }
                $panelistData = $panelistDataCache[$panelist_id];

                $isPartTime = isset($panelistData['is_parttime']) ? $panelistData['is_parttime'] : 0;
                $maxAllowed = $isPartTime ? 1 : 3; // 1 for part-time, 3 for full-time

                if (isset($panelistDailyAssignments[$panelist_id][$defense['day']])) {
                    $dailyAssignments = $panelistDailyAssignments[$panelist_id][$defense['day']];
                    $overLimit = ($dailyAssignments > $maxAllowed);
                    error_log("COND DefenseSchedule::calculateFitness: over_daily_limit=" . ($overLimit ? 'true' : 'false'));
                    if ($overLimit) {
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

                $similar = ($similarity > 70);
                error_log("COND DefenseSchedule::calculateFitness: expertise_match=" . ($similar ? 'true' : 'false'));
                if ($similar) {
                    $expertiseMatchFound = true;
                    $this->fitness += 20;
                }

                // Other checks
                $hasConsecutive = hasConsecutiveAssignment($panelist_id, $defense['day'], $defense['time_slot']);
                error_log("COND DefenseSchedule::calculateFitness: consecutive_assignment=" . ($hasConsecutive ? 'true' : 'false'));
                if ($hasConsecutive) {
                    $this->fitness -= 5;
                    $conflicts++;
                }

                $panelistConflict = hasScheduleConflict($this->pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses, $defense['team_id']);
                error_log("COND DefenseSchedule::calculateFitness: panelist_schedule_conflict=" . ($panelistConflict ? 'true' : 'false'));
                if ($panelistConflict) {
                    $this->fitness -= 200; // HARD penalty - must avoid panelist schedule/double-booking conflicts
                    $conflicts++;
                }
            }

            error_log("COND DefenseSchedule::calculateFitness: expertise_match_found=" . ($expertiseMatchFound ? 'true' : 'false'));
            if (!$expertiseMatchFound) {
                $this->fitness -= 25;
                $conflicts++;
            }

            // Room conflict check - check ALL pairs, don't break early
            foreach ($this->chromosomes as $otherKey => $otherDefense) {
                $durH = floatval($GLOBALS['timeDuration']);
                $dayA = scheduler_calendar_day_from_raw((string) $defense['day']);
                $dayB = scheduler_calendar_day_from_raw((string) $otherDefense['day']);

                $sameDay = ($defenseKey != $otherKey && $dayA !== null && $dayB !== null && $dayA === $dayB);
                error_log("COND DefenseSchedule::calculateFitness: same_day_pair=" . ($sameDay ? 'true' : 'false'));
                if ($sameDay) {
                    $rA = scheduler_defense_range_on_day((string) $defense['day'], (string) $defense['time_slot'], $durH);
                    $rB = scheduler_defense_range_on_day((string) $otherDefense['day'], (string) $otherDefense['time_slot'], $durH);

                    $rangesNull = ($rA === null || $rB === null);
                    error_log("COND DefenseSchedule::calculateFitness: ranges_null=" . ($rangesNull ? 'true' : 'false'));
                    if ($rangesNull) {
                        continue;
                    }

                    $defenseStart = $rA['start'];
                    $defenseEnd = $rA['end'];
                    $otherStart = $rB['start'];
                    $otherEnd = $rB['end'];

                    $overlap = (($defenseStart < $otherEnd) && ($defenseEnd > $otherStart));
                    error_log("COND DefenseSchedule::calculateFitness: overlap=" . ($overlap ? 'true' : 'false'));
                    if ($overlap) {
                        // Same room at overlapping time = room conflict
                        $roomOverlap = ($defense['room'] == $otherDefense['room']);
                        error_log("COND DefenseSchedule::calculateFitness: room_overlap=" . ($roomOverlap ? 'true' : 'false'));
                        if ($roomOverlap) {
                            $this->fitness -= 500; // MASSIVE penalty for room overlap
                            $conflicts++;
                        }

                        // Panelist double-booking at overlapping time (any room)
                        $sharedPanelists = array_intersect($defense['panelist_ids'], $otherDefense['panelist_ids']);
                        $hasShared = !empty($sharedPanelists);
                        error_log("COND DefenseSchedule::calculateFitness: shared_panelists=" . ($hasShared ? 'true' : 'false'));
                        if ($hasShared) {
                            $this->fitness -= 500; // MASSIVE penalty for panelist double-booking
                            $conflicts++;
                        }
                    }
                }
            }
        }

        self::$conflictCounts[] = $conflicts;
        self::$fitnessScores[] = $this->fitness;
        error_log("EXIT DefenseSchedule::calculateFitness: fitness=" . (string) $this->fitness . ", conflicts=" . (string) $conflicts);
    }
}

/** Deep-copy chromosome rows so post-GA repair can run on alternates without aliasing. */
function copyDefenseScheduleForPostProcess(DefenseSchedule $src): DefenseSchedule
{
    error_log("ENTER copyDefenseScheduleForPostProcess");
    $c = clone $src;
    $rows = [];
    foreach ($src->chromosomes as $row) {
        if (!is_array($row)) {
            $rows[] = $row;
            continue;
        }
        $copyRow = array_merge([], $row);
        $hasPanelists = isset($copyRow['panelist_ids']) && is_array($copyRow['panelist_ids']);
        error_log("COND copyDefenseScheduleForPostProcess: has_panelist_ids=" . ($hasPanelists ? 'true' : 'false'));
        if ($hasPanelists) {
            $copyRow['panelist_ids'] = array_merge([], $copyRow['panelist_ids']);
        }
        $rows[] = $copyRow;
    }
    $c->chromosomes = $rows;
    $c->all_defenses = array_merge([], $rows);

    error_log("EXIT copyDefenseScheduleForPostProcess: rows_count=" . count($rows));
    return $c;
}

// Add a function to check for consecutive assignments
function hasConsecutiveAssignment($panelist_id, $day, $time_slot)
{
    error_log("ENTER hasConsecutiveAssignment: panelist_id=" . (string) $panelist_id . ", day=" . (string) $day . ", time_slot=" . (string) $time_slot);
    // Implement logic to check if the panelist was assigned in the immediately previous schedule
    // This may require tracking the order of schedules and panelist assignments
    // For simplicity, assume a global or session-based tracking mechanism
    global $lastAssignedPanelists;
    $hasDay = isset($lastAssignedPanelists[$day]);
    error_log("COND hasConsecutiveAssignment: has_day=" . ($hasDay ? 'true' : 'false'));
    if ($hasDay) {
        $result = in_array($panelist_id, $lastAssignedPanelists[$day]);
        error_log("EXIT hasConsecutiveAssignment: result=" . ($result ? 'true' : 'false'));
        return $result;
    }
    error_log("EXIT hasConsecutiveAssignment: result=false");
    return false;
}

function getTeamMembers($pdo, $team_id, $format = 'array')
{
    error_log("ENTER getTeamMembers: team_id=" . (string) $team_id . ", format=" . (string) $format);
    static $cache = [];

    $cacheHit = isset($cache[$team_id]);
    error_log("COND getTeamMembers: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if (!$cacheHit) {
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
        error_log("EXIT getTeamMembers: array_count=" . (is_array($cache[$team_id]) ? count($cache[$team_id]) : 0));
        return $cache[$team_id];
    } else {
        $output = '';
        foreach ($cache[$team_id] as $member) {
            $output .= htmlspecialchars($member['name']) . ' (' . ucfirst($member['role']) . ')<br>';
        }
        error_log("EXIT getTeamMembers: html_length=" . strlen($output));
        return $output;
    }
}

// Function to fetch team by ID
function fetchTeamById($pdo, $team_id)
{
    error_log("ENTER fetchTeamById: team_id=" . (string) $team_id);
    $stmt = $pdo->prepare("
        SELECT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise,
               t.locked_panelist1, t.locked_panelist2, t.locked_panelist3
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE t.id = ? AND tm.role = 'adviser'
    ");
    $stmt->execute([$team_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("EXIT fetchTeamById: found=" . ($result ? 'true' : 'false'));
    return $result;
}

// Function to get panelist expertise
function getPanelistExpertise($pdo, $panelist_id)
{
    error_log("ENTER getPanelistExpertise: panelist_id=" . (string) $panelist_id);
    static $cache = [];

    $cacheHit = isset($cache[$panelist_id]);
    error_log("COND getPanelistExpertise: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if (!$cacheHit) {
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM users WHERE id = ?");
        $stmt->execute([$panelist_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$panelist_id] = $result ? $result['area_of_expertise'] : '';
    }

    error_log("EXIT getPanelistExpertise");
    return $cache[$panelist_id];
}

// Function to get team expertise
function getTeamExpertise($pdo, $team_id)
{
    error_log("ENTER getTeamExpertise: team_id=" . (string) $team_id);
    static $cache = [];

    $cacheHit = isset($cache[$team_id]);
    error_log("COND getTeamExpertise: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if (!$cacheHit) {
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$team_id] = $result ? $result['area_of_expertise'] : '';
    }

    error_log("EXIT getTeamExpertise");
    return $cache[$team_id];
}

// Diversity Preservation function
function diversityPreservation($population, $populationSize, $pdo, $teams, $panelists, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam)
{
    error_log("ENTER diversityPreservation: population_count=" . (is_array($population) ? count($population) : 0) . ", populationSize=" . (string) $populationSize);
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });

    $elites = array_slice($population, 0, intval($populationSize / 4));
    $newPopulation = $elites;

    while (count($newPopulation) < $populationSize) {
        error_log("LOOP diversityPreservation: newPopulation_count=" . count($newPopulation));
        $newSchedule = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $slotsByTeamDay, $eligibleDaysByTeam);
        $newPopulation[] = $newSchedule;
    }

    error_log("EXIT diversityPreservation: newPopulation_count=" . count($newPopulation));
    return $newPopulation;
}

// Adjust Mutation Rate function
function adjustMutationRate($mutationRate, $population)
{
    error_log("ENTER adjustMutationRate: mutationRate=" . (string) $mutationRate . ", population_count=" . (is_array($population) ? count($population) : 0));
    $avgFitness = array_sum(array_column($population, 'fitness')) / count($population);
    $bestFitness = max(array_column($population, 'fitness'));

    $converging = ($bestFitness == $avgFitness);
    error_log("COND adjustMutationRate: converging=" . ($converging ? 'true' : 'false'));
    if ($converging) {
        // Increase mutation rate if the population is converging
        $mutationRate *= 1.1;
    } else {
        // Otherwise, slightly decrease it
        $mutationRate *= 0.9;
    }

    // Keep mutation rate within reasonable bounds
    $mutationRate = max(0.05, min(0.5, $mutationRate));

    error_log("EXIT adjustMutationRate: newRate=" . (string) $mutationRate);
    return $mutationRate;
}

function getDepartment($program) {
    error_log("ENTER getDepartment: program=" . (string) $program);
    $program = (string)$program; // Force string type
    $isArch = (stripos($program, 'Architecture') !== false);
    error_log("COND getDepartment: is_architecture=" . ($isArch ? 'true' : 'false'));
    if ($isArch) {
        return "Architecture";
    } elseif (stripos($program, 'Engineering') !== false) {
        error_log("COND getDepartment: is_engineering=true");
        return "Engineering";
    }
    error_log("EXIT getDepartment: default=Computer Studies");
    return "Computer Studies";
}

/**
 * Get specializations for a team (from area_of_expertise field)
 */
function getTeamSpecializations($pdo, $teamId) {
    error_log("ENTER getTeamSpecializations: teamId=" . (string) $teamId);
    static $cache = [];
    
    $cacheHit = isset($cache[$teamId]);
    error_log("COND getTeamSpecializations: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if (!$cacheHit) {
        $stmt = $pdo->prepare("
            SELECT area_of_expertise 
            FROM teams
            WHERE id = ?
        ");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $hasExpertise = ($team && !empty($team['area_of_expertise']));
        error_log("COND getTeamSpecializations: has_expertise=" . ($hasExpertise ? 'true' : 'false'));
        if ($hasExpertise) {
            $cache[$teamId] = array_filter(array_map('trim', explode(',', $team['area_of_expertise'])));
        } else {
            $cache[$teamId] = [];
        }
    }
    
    error_log("EXIT getTeamSpecializations: count=" . count($cache[$teamId]));
    return $cache[$teamId];
}

/**
 * Get specializations for a user/panelist (from area_of_expertise field)
 */
function getUserSpecializations($pdo, $userId) {
    error_log("ENTER getUserSpecializations: userId=" . (string) $userId);
    static $cache = [];
    
    $cacheHit = isset($cache[$userId]);
    error_log("COND getUserSpecializations: cache_hit=" . ($cacheHit ? 'true' : 'false'));
    if (!$cacheHit) {
        $stmt = $pdo->prepare("
            SELECT area_of_expertise 
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $hasExpertise = ($user && !empty($user['area_of_expertise']));
        error_log("COND getUserSpecializations: has_expertise=" . ($hasExpertise ? 'true' : 'false'));
        if ($hasExpertise) {
            $cache[$userId] = array_filter(array_map('trim', explode(',', $user['area_of_expertise'])));
        } else {
            $cache[$userId] = [];
        }
    }
    
    error_log("EXIT getUserSpecializations: count=" . count($cache[$userId]));
    return $cache[$userId];
}

/**
 * Calculate specialization match score between team and panelist
 */
function calculateSpecializationMatch($teamSpecializations, $panelistSpecializations) {
    error_log("ENTER calculateSpecializationMatch: team_count=" . (is_array($teamSpecializations) ? count($teamSpecializations) : 0) . ", panelist_count=" . (is_array($panelistSpecializations) ? count($panelistSpecializations) : 0));
    $emptySet = (empty($teamSpecializations) || empty($panelistSpecializations));
    error_log("COND calculateSpecializationMatch: empty_set=" . ($emptySet ? 'true' : 'false'));
    if ($emptySet) {
        error_log("EXIT calculateSpecializationMatch: no_match");
        return 0; // No bonus if either has no specializations
    }
    
    $matchCount = count(array_intersect($teamSpecializations, $panelistSpecializations));
    $score = $matchCount * 10; // 10 points per matching specialization
    error_log("EXIT calculateSpecializationMatch: score=" . (string) $score);
    return $score;
}

function selectPanelists($panelistsByProgram, $allPanelists, $adviserId, $teamId = null)
{
    error_log("ENTER selectPanelists: adviserId=" . (string) $adviserId . ", teamId=" . (string) $teamId);
    global $pdo; // ensure $pdo is available

    // 1. Locked panelists (from teams table) take highest priority
    $lockedPanelists = [];
    $teamIdProvided = ($teamId !== null);
    error_log("COND selectPanelists: team_id_provided=" . ($teamIdProvided ? 'true' : 'false'));
    if ($teamIdProvided) {
        // Find team data (already loaded in $GLOBALS['teams'] or fetch again)
        $teamData = null;
        foreach ($GLOBALS['teams'] as $team) {
            $match = ((int)$team['id'] === (int)$teamId);
            error_log("COND selectPanelists: locked_team_match=" . ($match ? 'true' : 'false'));
            if ($match) {
                $teamData = $team;
                break;
            }
        }
        $hasLocked = ($teamData && (!empty($teamData['locked_panelist1']) || !empty($teamData['locked_panelist2']) || !empty($teamData['locked_panelist3'])));
        error_log("COND selectPanelists: has_locked=" . ($hasLocked ? 'true' : 'false'));
        if ($hasLocked) {
            $locked = [];
            $hasLocked1 = !empty($teamData['locked_panelist1']);
            $hasLocked2 = !empty($teamData['locked_panelist2']);
            $hasLocked3 = !empty($teamData['locked_panelist3']);
            error_log("COND selectPanelists: has_locked1=" . ($hasLocked1 ? 'true' : 'false'));
            error_log("COND selectPanelists: has_locked2=" . ($hasLocked2 ? 'true' : 'false'));
            error_log("COND selectPanelists: has_locked3=" . ($hasLocked3 ? 'true' : 'false'));
            if ($hasLocked1) $locked[] = (int)$teamData['locked_panelist1'];
            if ($hasLocked2) $locked[] = (int)$teamData['locked_panelist2'];
            if ($hasLocked3) $locked[] = (int)$teamData['locked_panelist3'];
            $locked = array_unique($locked);
            $lockedComplete = (count($locked) === 3);
            error_log("COND selectPanelists: locked_complete=" . ($lockedComplete ? 'true' : 'false'));
            if ($lockedComplete) {
                error_log("Team {$teamId}: using locked panelists: " . implode(',', $locked));
                error_log("EXIT selectPanelists: locked_complete");
                return $locked;
            }
            // If some are locked, use them as a base and try to complete the combination
            $lockedPanelists = $locked;
        }
    }

    // 2. Try to build optimal combination respecting is_external / is_parttime rules
    $exclude = [$adviserId];
    $hasLockedPanelists = !empty($lockedPanelists);
    error_log("COND selectPanelists: has_locked_panelists=" . ($hasLockedPanelists ? 'true' : 'false'));
    if ($hasLockedPanelists) {
        $exclude = array_merge($exclude, $lockedPanelists);
    }
    // Determine team program and college to force slots 1&2 to match
    $teamProgram = null;
    $teamCollege = null;
    $teamIdProvided = ($teamId !== null);
    error_log("COND selectPanelists: team_id_provided_for_program=" . ($teamIdProvided ? 'true' : 'false'));
    if ($teamIdProvided) {
        $stmt = $pdo->prepare("SELECT program FROM teams WHERE id = ? LIMIT 1");
        $stmt->execute([$teamId]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        $hasProgram = ($t && !empty($t['program']));
        error_log("COND selectPanelists: has_program=" . ($hasProgram ? 'true' : 'false'));
        if ($hasProgram) {
            $teamProgram = $t['program'];
            $norm = preg_split('/\s*[-–—]\s*/u', $teamProgram);
            $norm = trim($norm[0]);
            $pstmt = $pdo->prepare("SELECT college FROM programs WHERE name = ? LIMIT 1");
            $pstmt->execute([$norm]);
            $prog = $pstmt->fetch(PDO::FETCH_ASSOC);
            $teamCollege = $prog ? $prog['college'] : null;
        }
    }
    $optimalCombo = buildOptimalPanelistCombination($pdo, null, $exclude, $teamProgram, $teamCollege);
    $optimalOk = ($optimalCombo !== null && count($optimalCombo) === 3);
    error_log("COND selectPanelists: optimal_ok=" . ($optimalOk ? 'true' : 'false'));
    if ($optimalOk) {
        // Merge with any locked panelists (ensuring uniqueness)
        $final = array_values(array_unique(array_merge($lockedPanelists, $optimalCombo)));
        $finalOk = (count($final) >= 3);
        error_log("COND selectPanelists: final_ok=" . ($finalOk ? 'true' : 'false'));
        if ($finalOk) {
            error_log("Team {$teamId}: using optimal combination: " . implode(',', array_slice($final, 0, 3)));
            error_log("EXIT selectPanelists: optimal");
            return array_slice($final, 0, 3);
        }
    }

    // 3. Fallback: Original scoring logic (program, department, specialization)
    error_log("Team {$teamId}: falling back to scoring-based panelist selection");
    $selected = $lockedPanelists;
    $teamProgram = null;
    $teamExpertise = null;
    $teamIdProvided = ($teamId !== null);
    error_log("COND selectPanelists: team_id_provided_for_fallback=" . ($teamIdProvided ? 'true' : 'false'));
    if ($teamIdProvided) {
        $stmt = $pdo->prepare("SELECT program, area_of_expertise FROM teams WHERE id = ?");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
        $teamFound = (bool) $team;
        error_log("COND selectPanelists: team_found=" . ($teamFound ? 'true' : 'false'));
        if ($teamFound) {
            $teamProgram = $team['program'];
            $teamExpertise = $team['area_of_expertise'];
        }
    }
    $teamDepartment = $teamProgram ? getDepartment($teamProgram) : 'Computer Studies';
    $teamSpecializations = $teamExpertise ? array_filter(array_map('trim', explode(',', $teamExpertise))) : [];

    // Score all eligible panelists (excluding adviser and already selected)
    $eligibleIds = array_diff(array_keys($allPanelists), [$adviserId], $selected);
    $scores = [];
    foreach ($eligibleIds as $pid) {
        error_log("LOOP selectPanelists: pid=" . (string) $pid);
        $pdata = getPanelistData($pdo, $pid);
        $score = 0;
        $sameProgram = (strcasecmp($pdata['program'] ?? '', $teamProgram) === 0);
        $sameDepartment = (strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0);
        error_log("COND selectPanelists: same_program=" . ($sameProgram ? 'true' : 'false'));
        error_log("COND selectPanelists: same_department=" . ($sameDepartment ? 'true' : 'false'));
        if ($sameProgram) $score += 100;
        if ($sameDepartment) $score += 50;
        $panelistSpecs = !empty($pdata['area_of_expertise']) ? array_filter(array_map('trim', explode(',', $pdata['area_of_expertise']))) : [];
        $specScore = 0;
        foreach ($teamSpecializations as $ts) {
            $specMatch = in_array($ts, $panelistSpecs);
            error_log("COND selectPanelists: spec_match=" . ($specMatch ? 'true' : 'false'));
            if ($specMatch) $specScore += 10;
        }
        $score += $specScore;
        $scores[$pid] = $score;
    }
    arsort($scores);
    $topCandidates = array_keys($scores);

    while (count($selected) < 3 && !empty($topCandidates)) {
        error_log("LOOP selectPanelists: fill_selected_count=" . count($selected));
        $selected[] = array_shift($topCandidates);
    }

    // Ensure we have exactly 3 (pad with any remaining candidates if needed)
    while (count($selected) < 3 && !empty($topCandidates)) {
        error_log("LOOP selectPanelists: pad_selected_count=" . count($selected));
        $selected[] = array_shift($topCandidates);
    }

    error_log("Team {$teamId}: fallback selection: " . implode(',', $selected));
    error_log("EXIT selectPanelists: fallback");
    return $selected;
}

// Unused functions are kept at the end
function isTimeSlotAvailable($schedule, $day, $timeSlot, $duration, $room)
{
    error_log("ENTER isTimeSlotAvailable: day=" . (string) $day . ", timeSlot=" . (string) $timeSlot . ", room=" . (string) $room);
    $candidateDay = scheduler_calendar_day_from_raw((string) $day);
    $candidateRange = $candidateDay !== null ? scheduler_defense_range_on_day($candidateDay, (string) $timeSlot, $duration) : null;
    $invalid = ($candidateDay === null || $candidateRange === null);
    error_log("COND isTimeSlotAvailable: invalid_range=" . ($invalid ? 'true' : 'false'));
    if ($invalid) {
        error_log("EXIT isTimeSlotAvailable: false");
        return false;
    }

    $candidateStart = $candidateRange['start'];
    $candidateEnd = $candidateRange['end'];

    foreach ($schedule->chromosomes as $defense) {
        $existingDay = scheduler_calendar_day_from_raw((string) ($defense['day'] ?? ''));
        $skip = ($existingDay === null || $existingDay !== $candidateDay || (string) ($defense['room'] ?? '') !== (string) $room);
        error_log("COND isTimeSlotAvailable: skip_existing=" . ($skip ? 'true' : 'false'));
        if ($skip) {
            continue;
        }

        $existingRange = scheduler_defense_range_on_day($existingDay, (string) ($defense['time_slot'] ?? ''), $duration);
        $rangeNull = ($existingRange === null);
        error_log("COND isTimeSlotAvailable: existing_range_null=" . ($rangeNull ? 'true' : 'false'));
        if ($rangeNull) {
            continue;
        }

        $overlap = scheduler_slot_ranges_overlap($candidateStart, $candidateEnd, $existingRange['start'], $existingRange['end']);
        error_log("COND isTimeSlotAvailable: overlap=" . ($overlap ? 'true' : 'false'));
        if ($overlap) {
            error_log("EXIT isTimeSlotAvailable: false (overlap)");
            return false;
        }
    }
    error_log("EXIT isTimeSlotAvailable: true");
    return true;
}

function getAvailableTimeSlot($schedule, array $eligibleDaysByTeam, array $slotsByTeamDay, $duration, $rooms)
{
    error_log("ENTER getAvailableTimeSlot");
    $availableSlots = [];
    foreach ($eligibleDaysByTeam as $tid => $dayList) {
        error_log("LOOP getAvailableTimeSlot: team_id=" . (string) $tid);
        foreach ((array) $dayList as $day) {
            error_log("LOOP getAvailableTimeSlot: day=" . (string) $day);
            foreach (scheduler_slots_for_team_day($slotsByTeamDay, $tid, $day) as $timeSlot) {
                error_log("LOOP getAvailableTimeSlot: timeSlot=" . (string) $timeSlot);
                foreach ($rooms as $room) {
                    error_log("LOOP getAvailableTimeSlot: room=" . (string) $room);
                    if (isTimeSlotAvailable($schedule, $day, $timeSlot, $duration, $room)) {
                        $availableSlots[] = ['day' => $day, 'time_slot' => $timeSlot, 'room' => $room];
                    }
                }
            }
        }
    }
    $result = $availableSlots ? $availableSlots[array_rand($availableSlots)] : null;
    error_log("EXIT getAvailableTimeSlot: result=" . ($result ? 'found' : 'null'));
    return $result;
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
    error_log("ENTER validateAndFixOverlaps: defenses_count=" . (is_array($schedule->chromosomes) ? count($schedule->chromosomes) : 0));
    $duration = $GLOBALS['timeDuration'];
    $defenses = $schedule->chromosomes;
    $allIssues = [];
    $totalFixes = 0;
    $maxPasses = 48; // Safety limit (repair may need many moves when defenses are tightly packed)

    // Pre-cache team member IDs
    $memberCache = [];
    foreach ($defenses as $d) {
        $cacheHit = isset($memberCache[$d['team_id']]);
        error_log("COND validateAndFixOverlaps: member_cache_hit=" . ($cacheHit ? 'true' : 'false'));
        if (!$cacheHit) {
            $memberCache[$d['team_id']] = array_column(getTeamMembers($pdo, $d['team_id'], 'array'), 'id');
        }
    }

    error_log("=== POST-GA OVERLAP VALIDATION START ===");
    error_log("Validating " . count($defenses) . " defense entries (max $maxPasses passes)");

    for ($pass = 0; $pass < $maxPasses; $pass++) {
        error_log("LOOP validateAndFixOverlaps: pass=" . (string) $pass);
        $issuesThisPass = 0;
        $fixesThisPass = 0;

        // --- Defense-vs-Defense checks ---
        for ($i = 0; $i < count($defenses); $i++) {
            error_log("LOOP validateAndFixOverlaps: defense_index=" . (string) $i);
            $d1 = &$defenses[$i];
            $dn1 = scheduler_calendar_day_from_raw((string) $d1['day']);
            $r1 = $dn1 !== null ? scheduler_defense_range_on_day((string) $d1['day'], (string) $d1['time_slot'], $duration) : null;
            $r1Null = ($r1 === null);
            error_log("COND validateAndFixOverlaps: r1_null=" . ($r1Null ? 'true' : 'false'));
            if ($r1Null) {
                continue;
            }
            $d1Start = $r1['start'];
            $d1End = $r1['end'];

            for ($j = $i + 1; $j < count($defenses); $j++) {
                error_log("LOOP validateAndFixOverlaps: compare_index=" . (string) $j);
                $d2 = &$defenses[$j];

                $dn2 = scheduler_calendar_day_from_raw((string) $d2['day']);
                $dnMismatch = ($dn2 === null || $dn2 !== $dn1);
                error_log("COND validateAndFixOverlaps: day_match=" . ($dnMismatch ? 'false' : 'true'));
                if ($dnMismatch) continue;

                $r2 = scheduler_defense_range_on_day((string) $d2['day'], (string) $d2['time_slot'], $duration);
                $r2Null = ($r2 === null);
                error_log("COND validateAndFixOverlaps: r2_null=" . ($r2Null ? 'true' : 'false'));
                if ($r2Null) continue;

                $d2Start = $r2['start'];
                $d2End = $r2['end'];
                $timesOverlap = ($d1Start < $d2End) && ($d1End > $d2Start);
                error_log("COND validateAndFixOverlaps: times_overlap=" . ($timesOverlap ? 'true' : 'false'));
                if (!$timesOverlap) continue;

                // CHECK 1: Same room overlap
                $roomOverlap = ($d1['room'] === $d2['room']);
                error_log("COND validateAndFixOverlaps: room_overlap=" . ($roomOverlap ? 'true' : 'false'));
                if ($roomOverlap) {
                    $msg = "ROOM OVERLAP: Team {$d1['team_id']} & Team {$d2['team_id']} room {$d1['room']} on {$d1['day']} at {$d1['time_slot']}/{$d2['time_slot']}";
                    $allIssues[] = $msg;
                    error_log("PASS $pass: $msg");
                    $issuesThisPass++;

                    $fixed = fixDefenseSlot($defenses, $j, $d2, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo);
                    error_log("COND validateAndFixOverlaps: fix_room_overlap=" . ($fixed ? 'true' : 'false'));
                    if ($fixed) {
                        $fixesThisPass++;
                        $totalFixes++;
                    }
                    continue; // Re-check after fix
                }

                // CHECK 2: Panelist double-booking
                $sharedPanelists = array_intersect($d1['panelist_ids'], $d2['panelist_ids']);
                $hasSharedPanelists = !empty($sharedPanelists);
                error_log("COND validateAndFixOverlaps: shared_panelists=" . ($hasSharedPanelists ? 'true' : 'false'));
                if ($hasSharedPanelists) {
                    $msg = "PANELIST DOUBLE-BOOK: Panelist(s) " . implode(',', $sharedPanelists) . " Team {$d1['team_id']} & Team {$d2['team_id']} on {$d1['day']} {$d1['time_slot']}/{$d2['time_slot']}";
                    $allIssues[] = $msg;
                    error_log("PASS $pass: $msg");
                    $issuesThisPass++;

                    $fixed = fixDefenseSlot($defenses, $j, $d2, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo);
                    error_log("COND validateAndFixOverlaps: fix_panelist_overlap=" . ($fixed ? 'true' : 'false'));
                    if ($fixed) {
                        $fixesThisPass++;
                        $totalFixes++;
                    }
                }

                // CHECK 3: Team member double-booking
                $memberCacheHit = isset($memberCache[$d1['team_id']]);
                error_log("COND validateAndFixOverlaps: member_cache_hit_d1=" . ($memberCacheHit ? 'true' : 'false'));
                if (!$memberCacheHit) {
                    $memberCache[$d1['team_id']] = array_column(getTeamMembers($pdo, $d1['team_id'], 'array'), 'id');
                }
                $memberCacheHit2 = isset($memberCache[$d2['team_id']]);
                error_log("COND validateAndFixOverlaps: member_cache_hit_d2=" . ($memberCacheHit2 ? 'true' : 'false'));
                if (!$memberCacheHit2) {
                    $memberCache[$d2['team_id']] = array_column(getTeamMembers($pdo, $d2['team_id'], 'array'), 'id');
                }
                $sharedMembers = array_intersect($memberCache[$d1['team_id']], $memberCache[$d2['team_id']]);
                $hasSharedMembers = !empty($sharedMembers);
                error_log("COND validateAndFixOverlaps: shared_members=" . ($hasSharedMembers ? 'true' : 'false'));
                if ($hasSharedMembers) {
                    $msg = "MEMBER DOUBLE-BOOK: User(s) " . implode(',', $sharedMembers) . " Team {$d1['team_id']} & Team {$d2['team_id']} on {$d1['day']}";
                    $allIssues[] = $msg;
                    error_log("PASS $pass: $msg");
                    $issuesThisPass++;

                    $fixed = fixDefenseSlot($defenses, $j, $d2, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo);
                    error_log("COND validateAndFixOverlaps: fix_member_overlap=" . ($fixed ? 'true' : 'false'));
                    if ($fixed) {
                        $fixesThisPass++;
                        $totalFixes++;
                    }
                }
            }
        }

        // --- User schedule vs Defense checks ---
        for ($i = 0; $i < count($defenses); $i++) {
            error_log("LOOP validateAndFixOverlaps: user_check_index=" . (string) $i);
            $d = &$defenses[$i];
            $dYmd = scheduler_calendar_day_from_raw((string) $d['day']);
            $dr = $dYmd !== null ? scheduler_defense_range_on_day((string) $d['day'], (string) $d['time_slot'], $duration) : null;
            $drInvalid = ($dr === null || $dYmd === null);
            error_log("COND validateAndFixOverlaps: dr_invalid=" . ($drInvalid ? 'true' : 'false'));
            if ($drInvalid) {
                continue;
            }
            $defStart = $dr['start'];
            $defEnd = $dr['end'];

            // All users to check: panelists + team members
            $usersToCheck = $d['panelist_ids'];
            $memberCacheHit = isset($memberCache[$d['team_id']]);
            error_log("COND validateAndFixOverlaps: member_cache_hit=" . ($memberCacheHit ? 'true' : 'false'));
            if (!$memberCacheHit) {
                $memberCache[$d['team_id']] = array_column(getTeamMembers($pdo, $d['team_id'], 'array'), 'id');
            }
            $usersToCheck = array_unique(array_merge($usersToCheck, $memberCache[$d['team_id']]));

            foreach ($usersToCheck as $userId) {
                $hasSchedules = isset($userSchedules[$userId]);
                error_log("COND validateAndFixOverlaps: user_has_schedules=" . ($hasSchedules ? 'true' : 'false'));
                if (!$hasSchedules) continue;
                foreach ($userSchedules[$userId] as $sched) {
                    $allowsOverlap = schedulerClassRowAllowsOverlap($sched);
                    error_log("COND validateAndFixOverlaps: class_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                    if ($allowsOverlap) {
                        continue;
                    }
                    $schedR = scheduler_user_class_range_on_calendar_day($dYmd, $sched);
                    $schedNull = ($schedR === null);
                    error_log("COND validateAndFixOverlaps: sched_range_null=" . ($schedNull ? 'true' : 'false'));
                    if ($schedNull) continue;
                    $overlap = scheduler_slot_ranges_overlap($defStart, $defEnd, $schedR['start'], $schedR['end']);
                    error_log("COND validateAndFixOverlaps: schedule_overlap=" . ($overlap ? 'true' : 'false'));
                    if ($overlap) {
                        $msg = "USER SCHED CONFLICT: User $userId vs Team {$d['team_id']} on {$d['day']} {$d['time_slot']} (" . schedulerClassRowDescriptor($sched) . ")";
                        $allIssues[] = $msg;
                        error_log("PASS $pass: $msg");
                        $issuesThisPass++;

                        $fixed = fixDefenseSlot($defenses, $i, $d, $rooms, $slotsByTeamDay, $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo);
                        error_log("COND validateAndFixOverlaps: fix_user_overlap=" . ($fixed ? 'true' : 'false'));
                        if ($fixed) {
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
        $noIssues = ($issuesThisPass === 0);
        error_log("COND validateAndFixOverlaps: no_issues=" . ($noIssues ? 'true' : 'false'));
        if ($noIssues) {
            error_log("=== VALIDATION CLEAN after $pass pass(es) ===");
            break;
        }

        // If we found issues but couldn't fix any, stop to avoid infinite loop
        $noFixes = ($fixesThisPass === 0);
        error_log("COND validateAndFixOverlaps: no_fixes=" . ($noFixes ? 'true' : 'false'));
        if ($noFixes) {
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

    $result = [
        'issues' => $allIssues,
        'fixes' => $totalFixes,
        'remainingConflicts' => $remainingConflicts,
        'schedule' => $schedule
    ];
    error_log("EXIT validateAndFixOverlaps: remainingConflicts=" . (string) $remainingConflicts);
    return $result;
}

/**
 * Try to fix a defense by moving it to a conflict-free slot.
 * Tries: different room → different time → different day → different day+time.
 * Returns true if fixed.
 */
function fixDefenseSlot(&$defenses, $idx, &$defense, $rooms, array $slotsByTeamDay, array $eligibleDaysByTeam, $duration, $userSchedules, $memberCache, $pdo)
{
    error_log("ENTER fixDefenseSlot: idx=" . (string) $idx . ", team_id=" . (string) ($defense['team_id'] ?? ''));
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
        $sameRoom = ($altRoom === $originalRoom);
        error_log("COND fixDefenseSlot: same_room=" . ($sameRoom ? 'true' : 'false'));
        if ($sameRoom) continue;
        $defense['room'] = $altRoom;
        $defenses[$idx] = $defense;
        $hasConflict = hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo);
        error_log("COND fixDefenseSlot: room_conflict=" . ($hasConflict ? 'true' : 'false'));
        if (!$hasConflict) {
            error_log("FIX: Team {$defense['team_id']} → room $altRoom");
            error_log("EXIT fixDefenseSlot: fixed_room");
            return true;
        }
    }
    $defense['room'] = $originalRoom; // revert

    // Strategy 2: Try a different time (same calendar day — only pre-validated feasible starts)
    $sameDayPick = getAvailableTimeSlot((object) ['chromosomes' => $defenses], [$defense['team_id'] => [$originalDay]], $slotsByTeamDay, $duration, $rooms);
    $sameDayAvailable = ($sameDayPick !== null);
    error_log("COND fixDefenseSlot: same_day_available=" . ($sameDayAvailable ? 'true' : 'false'));
    if ($sameDayAvailable) {
        $defense['day'] = $sameDayPick['day'];
        $defense['time_slot'] = $sameDayPick['time_slot'];
        $defense['room'] = $sameDayPick['room'];
        $defenses[$idx] = $defense;
        $hasConflict = hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo);
        error_log("COND fixDefenseSlot: same_day_conflict=" . ($hasConflict ? 'true' : 'false'));
        if (!$hasConflict) {
            error_log("FIX: Team {$defense['team_id']} → {$sameDayPick['day']} {$sameDayPick['time_slot']} {$sameDayPick['room']}");
            error_log("EXIT fixDefenseSlot: fixed_same_day");
            return true;
        }
    }
    $defense['time_slot'] = $originalTime; // revert

    // Alternate days × feasible slots for this team × rooms (covers prior “change day only” attempts)
    $teamIdFx = (int) $defense['team_id'];
    foreach (($eligibleDaysByTeam[$teamIdFx] ?? []) as $altDay) {
        foreach (scheduler_slots_for_team_day($slotsByTeamDay, $teamIdFx, $altDay) as $altTime) {
            foreach ($rooms as $altRoom) {
                $sameSlot = ($altDay === $originalDay && $altTime === $originalTime && $altRoom === $originalRoom);
                error_log("COND fixDefenseSlot: same_slot=" . ($sameSlot ? 'true' : 'false'));
                if ($sameSlot) continue;
                $defense['day'] = $altDay;
                $defense['time_slot'] = $altTime;
                $defense['room'] = $altRoom;
                $defenses[$idx] = $defense;
                $hasConflict = hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo);
                error_log("COND fixDefenseSlot: alt_conflict=" . ($hasConflict ? 'true' : 'false'));
                if (!$hasConflict) {
                    error_log("FIX: Team {$defense['team_id']} → $altDay $altTime $altRoom");
                    error_log("EXIT fixDefenseSlot: fixed_alt");
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
    error_log("EXIT fixDefenseSlot: false");
    return false;
}

/**
 * Comprehensive conflict check for a single defense against ALL other defenses
 * and user schedules. Returns true if ANY conflict exists.
 */
function hasAnyConflictForDefense($defenses, $idx, $defense, $duration, $userSchedules, $involvedUsers, $memberCache, $pdo)
{
    error_log("ENTER hasAnyConflictForDefense: idx=" . (string) $idx . ", team_id=" . (string) ($defense['team_id'] ?? ''));
    $myYmd = scheduler_calendar_day_from_raw((string) $defense['day']);
    $dR = scheduler_defense_range_on_day((string) $defense['day'], (string) $defense['time_slot'], $duration);
    $invalid = ($myYmd === null || $dR === null);
    error_log("COND hasAnyConflictForDefense: invalid_range=" . ($invalid ? 'true' : 'false'));
    if ($invalid) {
        error_log("EXIT hasAnyConflictForDefense: false");
        return false;
    }
    $dStart = $dR['start'];
    $dEnd = $dR['end'];

    // CHECK 1: Against all other defenses
    foreach ($defenses as $otherIdx => $other) {
        $isSelf = ($otherIdx == $idx);
        error_log("COND hasAnyConflictForDefense: is_self=" . ($isSelf ? 'true' : 'false'));
        if ($isSelf) continue;
        $oYmd = scheduler_calendar_day_from_raw((string) ($other['day'] ?? ''));
        $dayMismatch = ($oYmd === null || $oYmd !== $myYmd);
        error_log("COND hasAnyConflictForDefense: day_match=" . ($dayMismatch ? 'false' : 'true'));
        if ($dayMismatch) continue;

        $oRange = scheduler_defense_range_on_day((string) $other['day'], (string) $other['time_slot'], $duration);
        $oNull = ($oRange === null);
        error_log("COND hasAnyConflictForDefense: o_range_null=" . ($oNull ? 'true' : 'false'));
        if ($oNull) continue;

        $oStart = $oRange['start'];
        $oEnd = $oRange['end'];
        $timesOverlap = ($dStart < $oEnd) && ($dEnd > $oStart);
        error_log("COND hasAnyConflictForDefense: times_overlap=" . ($timesOverlap ? 'true' : 'false'));
        if (!$timesOverlap) continue;

        // 1a: Room conflict
        if ($defense['room'] === $other['room']) {
            error_log("EXIT hasAnyConflictForDefense: true (room)");
            return true;
        }

        // 1b: Panelist double-booking
        if (!empty(array_intersect($defense['panelist_ids'], $other['panelist_ids']))) {
            error_log("EXIT hasAnyConflictForDefense: true (panelist)");
            return true;
        }

        // 1c: Any involved user is also in the other defense (as panelist or team member)
        $otherUsers = $other['panelist_ids'];
        if (isset($memberCache[$other['team_id']])) {
            $otherUsers = array_merge($otherUsers, $memberCache[$other['team_id']]);
        }
        if (!empty(array_intersect($involvedUsers, $otherUsers))) {
            error_log("EXIT hasAnyConflictForDefense: true (user)");
            return true;
        }
    }

    // CHECK 2: User schedule conflicts for all involved users
    foreach ($involvedUsers as $userId) {
        $hasSchedules = isset($userSchedules[$userId]);
        error_log("COND hasAnyConflictForDefense: user_has_schedules=" . ($hasSchedules ? 'true' : 'false'));
        if (!$hasSchedules) continue;
        foreach ($userSchedules[$userId] as $sched) {
            $allowsOverlap = schedulerClassRowAllowsOverlap($sched);
            error_log("COND hasAnyConflictForDefense: class_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
            if ($allowsOverlap) {
                continue;
            }
            $sr = scheduler_user_class_range_on_calendar_day($myYmd, $sched);
            $srNull = ($sr === null);
            error_log("COND hasAnyConflictForDefense: sched_range_null=" . ($srNull ? 'true' : 'false'));
            if ($srNull) continue;
            $overlap = scheduler_slot_ranges_overlap($dStart, $dEnd, $sr['start'], $sr['end']);
            error_log("COND hasAnyConflictForDefense: sched_overlap=" . ($overlap ? 'true' : 'false'));
            if ($overlap) {
                error_log("EXIT hasAnyConflictForDefense: true (class)");
                return true;
            }
        }
    }

    error_log("EXIT hasAnyConflictForDefense: false");
    return false;
}

/**
 * Final read-only count of remaining conflicts after all fixes.
 */
function countRemainingConflicts($pdo, $defenses, $duration, $userSchedules, $memberCache)
{
    error_log("ENTER countRemainingConflicts: defenses_count=" . (is_array($defenses) ? count($defenses) : 0));
    $conflicts = 0;

    for ($i = 0; $i < count($defenses); $i++) {
        $d1 = $defenses[$i];
        $dn1 = scheduler_calendar_day_from_raw((string) $d1['day']);
        $gr1 = $dn1 !== null ? scheduler_defense_range_on_day((string) $d1['day'], (string) $d1['time_slot'], $duration) : null;
        $gr1Null = ($gr1 === null);
        error_log("COND countRemainingConflicts: gr1_null=" . ($gr1Null ? 'true' : 'false'));
        if ($gr1Null) {
            continue;
        }
        $d1Start = $gr1['start'];
        $d1End = $gr1['end'];

        // Defense vs Defense
        for ($j = $i + 1; $j < count($defenses); $j++) {
            $d2 = $defenses[$j];
            $dn2 = scheduler_calendar_day_from_raw((string) $d2['day']);
            $dayMismatch = ($dn2 === null || $dn2 !== $dn1);
            error_log("COND countRemainingConflicts: day_match=" . ($dayMismatch ? 'false' : 'true'));
            if ($dayMismatch) continue;
            $gr2 = scheduler_defense_range_on_day((string) $d2['day'], (string) $d2['time_slot'], $duration);
            $gr2Null = ($gr2 === null);
            error_log("COND countRemainingConflicts: gr2_null=" . ($gr2Null ? 'true' : 'false'));
            if ($gr2Null) continue;
            $d2Start = $gr2['start'];
            $d2End = $gr2['end'];
            $overlap = (($d1Start < $d2End) && ($d1End > $d2Start));
            error_log("COND countRemainingConflicts: overlap=" . ($overlap ? 'true' : 'false'));
            if (!$overlap) continue;

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
            $hasSchedules = isset($userSchedules[$userId]);
            error_log("COND countRemainingConflicts: user_has_schedules=" . ($hasSchedules ? 'true' : 'false'));
            if (!$hasSchedules) continue;
            foreach ($userSchedules[$userId] as $sched) {
                $allowsOverlap = schedulerClassRowAllowsOverlap($sched);
                error_log("COND countRemainingConflicts: class_allows_overlap=" . ($allowsOverlap ? 'true' : 'false'));
                if ($allowsOverlap) {
                    continue;
                }
                $sr = scheduler_user_class_range_on_calendar_day($dn1, $sched);
                $srNull = ($sr === null);
                error_log("COND countRemainingConflicts: sched_range_null=" . ($srNull ? 'true' : 'false'));
                if ($srNull) continue;
                $overlap = scheduler_slot_ranges_overlap($d1Start, $d1End, $sr['start'], $sr['end']);
                error_log("COND countRemainingConflicts: sched_overlap=" . ($overlap ? 'true' : 'false'));
                if ($overlap) {
                    error_log("REMAINING CONFLICT: User $userId schedule vs Team {$d1['team_id']} ({$d1['day']} {$d1['time_slot']}, " . schedulerClassRowDescriptor($sched) . ")");
                    $conflicts++;
                }
            }
        }
    }

    error_log("EXIT countRemainingConflicts: conflicts=" . (string) $conflicts);
    return $conflicts;
}

/**
 * Legacy helper kept for backward compatibility.
 */
function hasRoomConflictAt($defenses, $excludeIdx, $day, $timeSlot, $room, $duration)
{
    error_log("ENTER hasRoomConflictAt: excludeIdx=" . (string) $excludeIdx . ", day=" . (string) $day . ", timeSlot=" . (string) $timeSlot . ", room=" . (string) $room);
    $newStart = strtotime($timeSlot);
    $newEnd = strtotime('+' . $duration . ' hour', $newStart);
    foreach ($defenses as $idx => $def) {
        $isExcluded = ($idx == $excludeIdx);
        error_log("COND hasRoomConflictAt: is_excluded=" . ($isExcluded ? 'true' : 'false'));
        if ($isExcluded) continue;
        $sameSlot = ($def['day'] === $day && $def['room'] === $room);
        error_log("COND hasRoomConflictAt: same_slot=" . ($sameSlot ? 'true' : 'false'));
        if (!$sameSlot) continue;
        $existStart = strtotime($def['time_slot']);
        $existEnd = strtotime('+' . $duration . ' hour', $existStart);
        $overlap = (($newStart < $existEnd) && ($newEnd > $existStart));
        error_log("COND hasRoomConflictAt: overlap=" . ($overlap ? 'true' : 'false'));
        if ($overlap) {
            error_log("EXIT hasRoomConflictAt: true");
            return true;
        }
    }
    error_log("EXIT hasRoomConflictAt: false");
    return false;
}
