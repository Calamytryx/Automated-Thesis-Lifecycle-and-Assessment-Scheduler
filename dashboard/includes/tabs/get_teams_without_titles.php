<?php
require_once '../../../assets/setup/db.inc.php';
require_once '../../../assets/includes/auth_functions.php';
require_once '../section_access.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function tableColumnExists(PDO $pdo, string $tableName, string $columnName): bool
{
    static $cache = [];

    $cacheKey = $tableName . '.' . $columnName;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
    $stmt->execute([$tableName, $columnName]);

    $cache[$cacheKey] = (bool)$stmt->fetchColumn();
    return $cache[$cacheKey];
}

try {
    if (!isset($_SESSION['id'], $_SESSION['usertype'])) {
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }

    $userId = (int) $_SESSION['id'];
    $userType = (int) $_SESSION['usertype'];
    $isSuperAdmin = ($userType === 0 && $userId === 0);

    $teamJoin = "FROM teams t";
    $titleJoin = "LEFT JOIN research_titles rt ON t.id = rt.team_id";
    $conditions = ["rt.id IS NULL"];
    $params = [];

    if (tableColumnExists($pdo, 'teams', 'deleted_at')) {
        $conditions[] = "t.deleted_at IS NULL";
    }

    if (tableColumnExists($pdo, 'research_titles', 'deleted_at')) {
        $titleJoin = "LEFT JOIN research_titles rt ON t.id = rt.team_id AND rt.deleted_at IS NULL";
    }

    if (!$isSuperAdmin) {
        $userCollege = get_user_college($pdo, $userId);
        if ($userCollege === null) {
            echo json_encode([]);
            exit;
        }

        $teamJoin .= "\n            JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
        $conditions[] = "p.college = :college";
        $params[':college'] = $userCollege;

        if ($userType === 2) {
            $assignedSections = getProfessorSections($pdo, $userId);
            if (!empty($assignedSections)) {
                $visibleTeamIds = getVisibleTeamsForProfessor($pdo, $userId);
                if (empty($visibleTeamIds)) {
                    echo json_encode([]);
                    exit;
                }

                $teamIdPlaceholders = [];
                foreach ($visibleTeamIds as $index => $teamId) {
                    $paramKey = ":team_id_{$index}";
                    $teamIdPlaceholders[] = $paramKey;
                    $params[$paramKey] = $teamId;
                }

                $conditions[] = 't.id IN (' . implode(',', $teamIdPlaceholders) . ')';
            }
        }
    }

    $sql = "SELECT t.id, t.name, t.program
            {$teamJoin}
            {$titleJoin}
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY t.name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response to highlight name and program
    $result = [];
    foreach ($teams as $team) {
        $result[] = [
            'id' => $team['id'],
            'name' => $team['name'],
            'program' => $team['program']
        ];
    }
    
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}