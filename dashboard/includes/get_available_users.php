<?php
/**
 * API endpoint to get available users for team member/adviser selection
 * Respects section-based and college-based filtering
 * 
 * Query Parameters:
 * - type: 'students' (return students from professor's assigned sections)
 *         'advisers' (return professors from same college)
 * - team_program: Required for type='advisers'
 * - team_id: Optional, current team being edited (for exclusion checks)
 */

// CRITICAL: Start session FIRST before accessing $_SESSION
session_start();

require_once '../../assets/setup/db.inc.php';
require_once 'section_access.php';

function getCollegeForProgram(PDO $pdo, ?string $programDisplay): ?string {
    if (!$programDisplay) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT college FROM programs 
            WHERE CONCAT(name, CASE WHEN specialization IS NOT NULL AND specialization != '' 
                THEN CONCAT(' - ', specialization) ELSE '' END) = ?
            LIMIT 1");
        $stmt->execute([$programDisplay]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['college'] : null;
    } catch (Exception $e) {
        error_log('getCollegeForProgram error: ' . $e->getMessage());
        return null;
    }
}

function getProgramsForCollege(PDO $pdo, string $college): array {
    try {
        $stmt = $pdo->prepare("SELECT name, CONCAT(name, CASE WHEN specialization IS NOT NULL AND specialization != '' 
                THEN CONCAT(' - ', specialization) ELSE '' END) AS display_name
            FROM programs
            WHERE college = ?");
        $stmt->execute([$college]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        foreach ($rows as $row) {
            if (!empty($row['name'])) {
                $names[] = $row['name'];
            }
            if (!empty($row['display_name'])) {
                $names[] = $row['display_name'];
            }
        }

        return array_values(array_unique($names));
    } catch (Exception $e) {
        error_log('getProgramsForCollege error: ' . $e->getMessage());
        return [];
    }
}

function fetchAccessibleStudents(PDO $pdo, int $userId, int $userType, ?int $currentTeamId, ?string $teamProgram = null, ?string $sessionCollege = null): array {
    $teamCollege = getCollegeForProgram($pdo, $teamProgram);

    if ($userType === 2) {
        $students = getAvailableStudentsForProfessor($pdo, $userId);
    } else {
        $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.usertype, u.username, u.email, u.section
                FROM users u
                WHERE u.id != 0 AND u.usertype = 1";
        $params = [];
        $excludeAssigned = !($userId === 0 || $userType === 0);

        if ($excludeAssigned) {
            if ($currentTeamId) {
                $sql .= " AND u.id NOT IN (
                            SELECT DISTINCT tm.user_id FROM team_members tm
                            JOIN teams t ON tm.team_id = t.id
                            WHERE t.id != ?
                        )";
                $params[] = $currentTeamId;
            } else {
                $sql .= " AND u.id NOT IN (
                            SELECT DISTINCT tm.user_id FROM team_members tm
                        )";
            }
        }

        if ($userId !== 0) {
            $programNames = [];

            if ($teamCollege) {
                $programNames = getProgramsForCollege($pdo, $teamCollege);
            } elseif ($userType === 0) {
                $chairCollege = $sessionCollege ?: getProfessorCollege($pdo, $userId);
                if ($chairCollege) {
                    $programNames = getProgramsForCollege($pdo, $chairCollege);
                }
            }

            if (!empty($programNames)) {
                $placeholders = implode(',', array_fill(0, count($programNames), '?'));
                $sql .= " AND u.program IN ($placeholders)";
                $params = array_merge($params, $programNames);
            }
        }

        $sql .= " ORDER BY u.last_name, u.first_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (empty($students)) {
        return [];
    }

    // Apply team membership exclusion for faculty results as well
    if ($userType === 2) {
        $studentIds = array_column($students, 'id');
        if (!empty($studentIds)) {
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $params = $studentIds;
            $sql = "SELECT DISTINCT tm.user_id
                    FROM team_members tm
                    JOIN teams t ON tm.team_id = t.id";
            $extraParams = [];
            if ($currentTeamId) {
                $sql .= " WHERE t.id != ? AND tm.user_id IN ($placeholders)";
                $extraParams[] = $currentTeamId;
            } else {
                $sql .= " WHERE tm.user_id IN ($placeholders)";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($extraParams, $params));
            $blockedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($blockedIds)) {
                $students = array_filter($students, function ($student) use ($blockedIds) {
                    return !in_array($student['id'], $blockedIds);
                });
            }
        }
    }

    return array_values($students);
}

function fetchAccessibleAdvisers(PDO $pdo, int $userId, int $userType, ?string $teamProgram, ?string $sessionCollege = null): array {
    $teamCollege = getCollegeForProgram($pdo, $teamProgram);
    $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.usertype, u.username, u.email,
                (
                    SELECT COUNT(*) FROM team_members tm2
                    JOIN teams t2 ON tm2.team_id = t2.id
                    WHERE tm2.user_id = u.id AND tm2.role = 'adviser'
                ) AS adviser_count
            FROM users u
            WHERE u.id != 0 AND u.usertype = 2";
    $params = [];

    $programNames = [];
    if ($teamCollege) {
        $programNames = getProgramsForCollege($pdo, $teamCollege);
    } elseif ($userType === 0) {
        $userCollege = $sessionCollege ?: getProfessorCollege($pdo, $userId);
        if ($userCollege) {
            $programNames = getProgramsForCollege($pdo, $userCollege);
        }
    }

    if (!empty($programNames)) {
        $placeholders = implode(',', array_fill(0, count($programNames), '?'));
        $sql .= " AND u.program IN ($placeholders)";
        $params = array_merge($params, $programNames);
    } elseif ($teamCollege) {
        return [];
    }

    $sql .= " ORDER BY u.last_name, u.first_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function mergeUserLists(array $primary, array $secondary): array {
    $byId = [];

    foreach ($primary as $user) {
        $byId[$user['id']] = $user;
    }

    foreach ($secondary as $user) {
        $byId[$user['id']] = $user;
    }

    return array_values($byId);
}

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

// Support both new and old query parameter styles
$type = $_GET['type'] ?? $_GET['usertype_filter'] ?? null;
$teamProgram = $_GET['team_program'] ?? null;
$currentTeamId = isset($_GET['team_id']) ? intval($_GET['team_id']) : null;
$includeAdvisers = isset($_GET['include_advisers']) ? (bool)$_GET['include_advisers'] : false;

$userId = $_SESSION['id'] ?? 0;
$usertype = $_SESSION['usertype'] ?? -1;
$sessionCollege = $_SESSION['college'] ?? null;

error_log("get_available_users.php - type: $type, userId: $userId, usertype: $usertype");

try {
    if ($type === 'students') {
        $students = fetchAccessibleStudents($pdo, $userId, $usertype, $currentTeamId, $teamProgram, $sessionCollege);
        $response['success'] = true;
        $response['data'] = $students;
    } elseif ($type === 'advisers') {
        if (!$teamProgram) {
            $response['message'] = 'Team program is required for adviser selection';
            echo json_encode($response);
            exit;
        }

        $advisers = fetchAccessibleAdvisers($pdo, $userId, $usertype, $teamProgram, $sessionCollege);
        $response['success'] = true;
        $response['data'] = $advisers;
    } else {
        $students = fetchAccessibleStudents($pdo, $userId, $usertype, $currentTeamId, $teamProgram, $sessionCollege);
        $users = $students;

        if ($includeAdvisers) {
            $advisers = fetchAccessibleAdvisers($pdo, $userId, $usertype, $teamProgram, $sessionCollege);
            $users = mergeUserLists($users, $advisers);
        }

        $response['success'] = true;
        $response['data'] = $users;
    }

} catch (Exception $e) {
    error_log('Error fetching available users: ' . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>

