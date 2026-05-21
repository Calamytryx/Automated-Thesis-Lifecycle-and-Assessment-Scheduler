<?php
/**
 * Allied-programs configuration endpoint (Academic Structure tab).
 *
 * Backs the per-program "Allied Programs" setting. The scheduler reads this adjacency to decide
 * panelist-2 eligibility (hard constraint 6): a team in `program_id` may seat, as panelist 2,
 * faculty whose program is one of the `allied_program_id`s configured here. Directed edges.
 *
 *   GET  ?program_id=ID  -> { program, allied:[ids], options:[{id,label,college}] }
 *   POST {program_id, allied_ids:[...]} -> replaces that program's allied set
 *
 * Auth mirrors the programs tab: super admin (usertype 0, id 0) anywhere; program chair
 * (usertype 0, id != 0) only for programs in their own college.
 */

require_once '../../../assets/setup/db.inc.php';
require_once '../../../assets/includes/auth_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['id'], $_SESSION['usertype'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = (int) $_SESSION['id'];
$userType = (int) $_SESSION['usertype'];
$isSuperAdmin = ($userType === 0 && $userId === 0);
$isProgramChair = ($userType === 0 && $userId !== 0);

if (!$isSuperAdmin && !$isProgramChair) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$chairCollege = $isProgramChair ? get_user_college($pdo, $userId) : null;

function alliedProgramLabel(array $p): string
{
    return $p['name'] . (!empty($p['specialization']) ? ' - ' . $p['specialization'] : '');
}

function alliedFetchProgram(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT id, name, specialization, college FROM programs WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/** Chair may only touch programs in their own college; super admin may touch any. */
function alliedAuthorizeProgram(?array $program, bool $isSuperAdmin, bool $isProgramChair, ?string $chairCollege): bool
{
    if (!$program) {
        return false;
    }
    if ($isSuperAdmin) {
        return true;
    }
    return $isProgramChair && $chairCollege && $program['college'] === $chairCollege;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $programId = (int) ($_GET['program_id'] ?? 0);
        $program = $programId > 0 ? alliedFetchProgram($pdo, $programId) : null;
        if (!alliedAuthorizeProgram($program, $isSuperAdmin, $isProgramChair, $chairCollege)) {
            http_response_code($program ? 403 : 404);
            echo json_encode(['error' => $program ? 'Forbidden' : 'Program not found']);
            exit;
        }

        // Selectable allies: every other program (admin) or same-college programs (chair).
        if ($isSuperAdmin) {
            $stmt = $pdo->prepare("SELECT id, name, specialization, college FROM programs WHERE id != ? ORDER BY college, name");
            $stmt->execute([$programId]);
        } else {
            $stmt = $pdo->prepare("SELECT id, name, specialization, college FROM programs WHERE id != ? AND college = ? ORDER BY name");
            $stmt->execute([$programId, $chairCollege]);
        }
        $options = array_map(static function ($p) {
            return ['id' => (int) $p['id'], 'label' => alliedProgramLabel($p), 'college' => $p['college'] ?? ''];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        $stmt = $pdo->prepare("SELECT allied_program_id FROM allied_programs WHERE program_id = ?");
        $stmt->execute([$programId]);
        $allied = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        echo json_encode([
            'program' => ['id' => (int) $program['id'], 'label' => alliedProgramLabel($program), 'college' => $program['college'] ?? ''],
            'allied' => $allied,
            'options' => $options,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $raw = json_decode(file_get_contents('php://input'), true);
        if (!is_array($raw)) {
            $raw = $_POST;
        }
        $programId = (int) ($raw['program_id'] ?? 0);
        $alliedIds = array_values(array_unique(array_map('intval', (array) ($raw['allied_ids'] ?? []))));

        $program = $programId > 0 ? alliedFetchProgram($pdo, $programId) : null;
        if (!alliedAuthorizeProgram($program, $isSuperAdmin, $isProgramChair, $chairCollege)) {
            http_response_code($program ? 403 : 404);
            echo json_encode(['error' => $program ? 'Forbidden' : 'Program not found']);
            exit;
        }

        // Keep only real, non-self programs the user is allowed to link.
        $valid = [];
        foreach ($alliedIds as $aid) {
            if ($aid <= 0 || $aid === $programId) {
                continue;
            }
            $ap = alliedFetchProgram($pdo, $aid);
            if (!$ap) {
                continue;
            }
            if ($isProgramChair && $ap['college'] !== $chairCollege) {
                continue;
            }
            $valid[] = $aid;
        }

        $pdo->beginTransaction();
        $del = $pdo->prepare("DELETE FROM allied_programs WHERE program_id = ?");
        $del->execute([$programId]);
        if (!empty($valid)) {
            $ins = $pdo->prepare("INSERT INTO allied_programs (program_id, allied_program_id) VALUES (?, ?)");
            foreach ($valid as $aid) {
                $ins->execute([$programId, $aid]);
            }
        }
        $pdo->commit();

        echo json_encode(['success' => true, 'program_id' => $programId, 'allied' => $valid, 'count' => count($valid)]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('allied_programs endpoint: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
