<?php
/**
 * Specialization Assignment API
 * Handles assigning specializations to users and teams
 * Accessible to: Admins and Research Professors (teachers with team assignments)
 */

session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/auth_functions.php';
require_once __DIR__ . '/section_access.php'; // Include section access functions

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = (int) $_SESSION['id'];
$usertype = (int) $_SESSION['usertype'];
$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

function toPositiveInt($value) {
    $filtered = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $filtered === false ? null : (int)$filtered;
}

/**
 * Base program name = the program with any " - specialization" suffix stripped, so e.g.
 * "BS Computer Science - Software Engineering" and "BS Computer Science - Data Science" both
 * resolve to "BS Computer Science". Used to scope a program chair to their program's faculty.
 */
function normalizeBaseProgram($program) {
    if (!is_string($program)) {
        return '';
    }
    $parts = preg_split('/\s*[-–—]\s*/u', $program);
    return trim($parts[0] ?? '');
}

/** Memoized single-user lookup (id, usertype, program). */
function getUserRow($pdo, $uid) {
    static $cache = [];
    $uid = (int) $uid;
    if (!array_key_exists($uid, $cache)) {
        $stmt = $pdo->prepare("SELECT id, usertype, program FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $cache[$uid] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    return $cache[$uid];
}

/**
 * Authority to VIEW/EDIT a target user's specialization. Single source of truth for reads + writes.
 *  - Super admin (type 0, id 0): any faculty/admin.
 *  - Program chair (type 0, id != 0): self only among admins (same rank can't edit each other),
 *    plus any faculty (type 2) in the SAME base program (specialization suffix ignored).
 *  - Faculty (type 2): self only.
 */
function canManageUserSpecialization($pdo, $actorId, $actorType, $targetId) {
    $actorId = (int) $actorId;
    $targetId = (int) $targetId;
    $actorType = (int) $actorType;
    if ($targetId <= 0) {
        return false;
    }
    if ($actorId === $targetId) {
        return true; // self is always allowed (faculty + chair manage their own)
    }

    $target = getUserRow($pdo, $targetId);
    if (!$target) {
        return false;
    }
    $targetType = (int) $target['usertype'];
    if (!in_array($targetType, [0, 2], true)) {
        return false; // only faculty/admin carry user specializations
    }

    // Super admin: full access.
    if ($actorType === 0 && $actorId === 0) {
        return true;
    }

    // Program chair: faculty in the same base program only (never another admin/chair).
    if ($actorType === 0 && $actorId !== 0) {
        if ($targetType !== 2) {
            return false; // same rank / other admin — only self (handled above)
        }
        $actor = getUserRow($pdo, $actorId);
        $chairProgram = normalizeBaseProgram($actor['program'] ?? '');
        $facultyProgram = normalizeBaseProgram($target['program'] ?? '');
        return $chairProgram !== '' && $chairProgram === $facultyProgram;
    }

    // Faculty: nothing beyond self.
    return false;
}

function sanitizeAssignmentText($value, $maxLength = 255) {
    $cleaned = trim((string)($value ?? ''));
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);
    if (mb_strlen($cleaned) > $maxLength) {
        $cleaned = mb_substr($cleaned, 0, $maxLength);
    }
    return $cleaned;
}

try {
    switch ($action) {
        case 'assign_to_user':
            // Check permissions
            if (!canAssignSpecializations($pdo, $userId, $usertype)) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            assignToUser($pdo, $_POST, $userId, $usertype);
            break;
        
        case 'assign_to_team':
            // Check permissions
            if (!canAssignSpecializations($pdo, $userId, $usertype)) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            assignToTeam($pdo, $_POST, $userId);
            break;
        
        case 'remove_from_user':
            // Check permissions
            if (!canAssignSpecializations($pdo, $userId, $usertype)) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            removeFromUser($pdo, $_POST, $userId, $usertype);
            break;
        
        case 'remove_from_team':
            // Check permissions
            if (!canAssignSpecializations($pdo, $userId, $usertype)) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            removeFromTeam($pdo, $_POST);
            break;
        
        case 'get_user_specializations':
            getUserSpecializations($pdo, toPositiveInt($_GET['user_id'] ?? null), $userId, $usertype);
            break;
        
        case 'get_team_specializations':
            getTeamSpecializations($pdo, toPositiveInt($_GET['team_id'] ?? null));
            break;
        
        case 'get_my_teams':
            // Get teams assigned to the current professor
            getMyTeams($pdo, $userId, $usertype);
            break;
        
        case 'get_assignable_users':
            // Get users that the professor can assign specializations to
            getAssignableUsers($pdo, $userId, $usertype);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Specialization Assignment API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Check if user can assign specializations
 * - Super Admin (id=0): YES (all teams and users)
 * - Program Chair (usertype=0, id!=0): YES (college-restricted)
 * - Research Professors (usertype=2): YES (only their teams)
 * - Others: NO
 */
function canAssignSpecializations($pdo, $userId, $usertype) {
    // Admin always has permission
    if ($usertype === 0) {
        return true;
    }
    
    // Faculty (usertype=2) - can assign to their teams only
    if ($usertype === 2) {
        return true; // They can assign if they have teams (checked in getMyTeams)
    }
    
    return false;
}

/**
 * Assign specialization to user (updates area_of_expertise field)
 */
function assignToUser($pdo, $data, $assignedBy, $assignedByType) {
    $userId = toPositiveInt($data['user_id'] ?? null);
    $specializationId = toPositiveInt($data['specialization_id'] ?? null);

    if (!$userId || !$specializationId) {
        echo json_encode(['success' => false, 'message' => 'User ID and Specialization ID required']);
        return;
    }

    if (!canManageUserSpecialization($pdo, $assignedBy, $assignedByType, $userId)) {
        echo json_encode(['success' => false, 'message' => 'You can only edit specializations for yourself or faculty in your program.']);
        return;
    }

    try {
        // Get specialization name
        $stmt = $pdo->prepare("SELECT name FROM specialization_pool WHERE id = ?");
        $stmt->execute([$specializationId]);
        $specialization = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$specialization) {
            echo json_encode(['success' => false, 'message' => 'Invalid specialization ID']);
            return;
        }
        
        // Get current area_of_expertise
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentExpertise = $user['area_of_expertise'] ?? '';
        
        // Check if already has this specialization
        $currentArray = array_filter(array_map('trim', explode(',', $currentExpertise)));
        if (in_array($specialization['name'], $currentArray)) {
            echo json_encode(['success' => false, 'message' => 'This specialization is already assigned to this user']);
            return;
        }
        
        // Add new specialization
        $currentArray[] = $specialization['name'];
        $newExpertise = implode(', ', $currentArray);
        
        // Update user's area_of_expertise
        $stmt = $pdo->prepare("UPDATE users SET area_of_expertise = ? WHERE id = ?");
        $stmt->execute([$newExpertise, $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Specialization assigned to user successfully'
        ]);
    } catch (PDOException $e) {
        throw $e;
    }
}

/**
 * Assign specialization to team (updates area_of_expertise field)
 */
function assignToTeam($pdo, $data, $assignedBy) {
    $teamId = toPositiveInt($data['team_id'] ?? null);
    $specializationId = toPositiveInt($data['specialization_id'] ?? null);
    
    if (!$teamId || !$specializationId) {
        echo json_encode(['success' => false, 'message' => 'Team ID and Specialization ID required']);
        return;
    }
    
    try {
        // Get specialization name
        $stmt = $pdo->prepare("SELECT name FROM specialization_pool WHERE id = ?");
        $stmt->execute([$specializationId]);
        $specialization = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$specialization) {
            echo json_encode(['success' => false, 'message' => 'Invalid specialization ID']);
            return;
        }
        
        // Get current area_of_expertise
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM teams WHERE id = ?");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentExpertise = $team['area_of_expertise'] ?? '';
        
        // Check if already has this specialization
        $currentArray = array_filter(array_map('trim', explode(',', $currentExpertise)));
        if (in_array($specialization['name'], $currentArray)) {
            echo json_encode(['success' => false, 'message' => 'This specialization is already assigned to this team']);
            return;
        }
        
        // Add new specialization
        $currentArray[] = $specialization['name'];
        $newExpertise = implode(', ', $currentArray);
        
        // Update team's area_of_expertise
        $stmt = $pdo->prepare("UPDATE teams SET area_of_expertise = ? WHERE id = ?");
        $stmt->execute([$newExpertise, $teamId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Specialization assigned to team successfully'
        ]);
    } catch (PDOException $e) {
        throw $e;
    }
}

/**
 * Remove specialization from user
 */
function removeFromUser($pdo, $data, $actorId, $actorType) {
    $userId = toPositiveInt($data['user_id'] ?? null);
    $specializationName = sanitizeAssignmentText($data['specialization_name'] ?? '', 150);

    if (!$userId || !$specializationName) {
        echo json_encode(['success' => false, 'message' => 'User ID and Specialization Name required']);
        return;
    }

    if (!canManageUserSpecialization($pdo, $actorId, $actorType, $userId)) {
        echo json_encode(['success' => false, 'message' => 'You can only edit specializations for yourself or faculty in your program.']);
        return;
    }

    try {
        // Get current area_of_expertise
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentExpertise = $user['area_of_expertise'] ?? '';
        
        // Remove the specialization
        $currentArray = array_filter(array_map('trim', explode(',', $currentExpertise)));
        $currentArray = array_diff($currentArray, [$specializationName]);
        $newExpertise = implode(', ', $currentArray);
        
        // Update user's area_of_expertise
        $stmt = $pdo->prepare("UPDATE users SET area_of_expertise = ? WHERE id = ?");
        $stmt->execute([$newExpertise, $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Specialization removed from user successfully'
        ]);
    } catch (PDOException $e) {
        throw $e;
    }
}

/**
 * Remove specialization from team
 */
function removeFromTeam($pdo, $data) {
    $teamId = toPositiveInt($data['team_id'] ?? null);
    $specializationName = sanitizeAssignmentText($data['specialization_name'] ?? '', 150);
    
    if (!$teamId || !$specializationName) {
        echo json_encode(['success' => false, 'message' => 'Team ID and Specialization Name required']);
        return;
    }
    
    try {
        // Get current area_of_expertise
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM teams WHERE id = ?");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentExpertise = $team['area_of_expertise'] ?? '';
        
        // Remove the specialization
        $currentArray = array_filter(array_map('trim', explode(',', $currentExpertise)));
        $currentArray = array_diff($currentArray, [$specializationName]);
        $newExpertise = implode(', ', $currentArray);
        
        // Update team's area_of_expertise
        $stmt = $pdo->prepare("UPDATE teams SET area_of_expertise = ? WHERE id = ?");
        $stmt->execute([$newExpertise, $teamId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Specialization removed from team successfully'
        ]);
    } catch (PDOException $e) {
        throw $e;
    }
}

/**
 * Get user's specializations (from area_of_expertise field)
 */
function getUserSpecializations($pdo, $userId, $actorId = null, $actorType = null) {
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        return;
    }

    if ($actorId !== null && !canManageUserSpecialization($pdo, $actorId, $actorType, $userId)) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT area_of_expertise
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $specializations = [];
    if (!empty($user['area_of_expertise'])) {
        $specArray = array_filter(array_map('trim', explode(',', $user['area_of_expertise'])));
        foreach ($specArray as $spec) {
            $specializations[] = [
                'specialization_name' => $spec,
                'area_of_expertise' => $user['area_of_expertise']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $specializations
    ]);
}

/**
 * Get team's specializations (from area_of_expertise field)
 */
function getTeamSpecializations($pdo, $teamId) {
    if (!$teamId) {
        echo json_encode(['success' => false, 'message' => 'Team ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT area_of_expertise
        FROM teams
        WHERE id = ?
    ");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $specializations = [];
    if (!empty($team['area_of_expertise'])) {
        $specArray = array_filter(array_map('trim', explode(',', $team['area_of_expertise'])));
        foreach ($specArray as $spec) {
            $specializations[] = [
                'specialization_name' => $spec,
                'area_of_expertise' => $team['area_of_expertise']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $specializations
    ]);
}

/**
 * Get teams assigned to the current professor (research professor, not adviser)
 */
function getMyTeams($pdo, $userId, $usertype) {
    // Note: No caching to ensure fresh data when sections are reassigned
    if ($usertype === 0) {
        // Check if super admin or program chair
        if ($userId === 0) {
            // Super Admin: Get all teams
            $stmt = $pdo->prepare("
                SELECT 
                    t.id,
                    t.name,
                    t.program,
                    GROUP_CONCAT(
                        CONCAT(u.first_name, ' ', u.last_name) 
                        ORDER BY 
                            CASE tm.role 
                                WHEN 'adviser' THEN 1 
                                WHEN 'leader' THEN 2 
                                ELSE 3 
                            END
                        SEPARATOR ', '
                    ) as members
                FROM teams t
                LEFT JOIN team_members tm ON t.id = tm.team_id
                LEFT JOIN users u ON tm.user_id = u.id
                GROUP BY t.id, t.name, t.program
                ORDER BY t.name
            ");
            $stmt->execute();
        } else {
            // Program Chair: Get teams in their college only
            require_once __DIR__ . '/../../assets/includes/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            if (!$userCollege) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }
            $stmt = $pdo->prepare("
                SELECT 
                    t.id,
                    t.name,
                    t.program,
                    GROUP_CONCAT(
                        CONCAT(u.first_name, ' ', u.last_name) 
                        ORDER BY 
                            CASE tm.role 
                                WHEN 'adviser' THEN 1 
                                WHEN 'leader' THEN 2 
                                ELSE 3 
                            END
                        SEPARATOR ', '
                    ) as members
                FROM teams t
                JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)
                LEFT JOIN team_members tm ON t.id = tm.team_id
                LEFT JOIN users u ON tm.user_id = u.id
                WHERE p.college = ?
                GROUP BY t.id, t.name, t.program
                ORDER BY t.name
            ");
            $stmt->execute([$userCollege]);
        }
    } else if ($usertype === 2) {
        // Research Professor: Use section_access.php logic (same as Teams tab)
        $visibleTeamIds = getVisibleTeamsForProfessor($pdo, $userId);
        
        if (empty($visibleTeamIds)) {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }
        
        // Get team details for visible teams
        $placeholders = implode(',', array_fill(0, count($visibleTeamIds), '?'));
        $stmt = $pdo->prepare("
            SELECT 
                t.id,
                t.name,
                t.program,
                (
                    SELECT GROUP_CONCAT(
                        CONCAT(u2.first_name, ' ', u2.last_name) 
                        ORDER BY 
                            CASE tm2.role 
                                WHEN 'adviser' THEN 1 
                                WHEN 'leader' THEN 2 
                                ELSE 3 
                            END
                        SEPARATOR ', '
                    )
                    FROM team_members tm2
                    JOIN users u2 ON tm2.user_id = u2.id
                    WHERE tm2.team_id = t.id
                ) as members
            FROM teams t
            WHERE t.id IN ($placeholders)
            ORDER BY t.name
        ");
        $stmt->execute($visibleTeamIds);
    } else {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $teams
    ]);
}

/**
 * Get users that can be assigned specializations
 * Only usertype 0 (admins) and usertype 2 (faculty) can receive assignments
 */
function getAssignableUsers($pdo, $userId, $usertype) {
    $roleLabel = static function ($t) {
        return ((int) $t === 0) ? 'Admin' : (((int) $t === 2) ? 'Faculty' : 'Other');
    };

    // Super Admin: every admin and faculty member.
    if ($usertype === 0 && $userId === 0) {
        $stmt = $pdo->prepare("
            SELECT id, username, first_name, last_name,
                   CONCAT(first_name, ' ', last_name) as name, email, program,
                   CASE usertype WHEN 0 THEN 'Admin' WHEN 2 THEN 'Faculty' ELSE 'Other' END as role
            FROM users
            WHERE usertype IN (0, 2)
            ORDER BY first_name, last_name
        ");
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        return;
    }

    // Program Chair: themselves + faculty in the SAME base program (no other admins/chairs).
    if ($usertype === 0 && $userId !== 0) {
        $actor = getUserRow($pdo, $userId);
        $chairProgram = normalizeBaseProgram($actor['program'] ?? '');

        $stmt = $pdo->prepare("
            SELECT id, username, first_name, last_name,
                   CONCAT(first_name, ' ', last_name) as name, email, program, usertype
            FROM users
            WHERE usertype = 2 OR id = ?
            ORDER BY first_name, last_name
        ");
        $stmt->execute([$userId]);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $isSelf = ((int) $row['id'] === $userId);
            $sameProgramFaculty = ((int) $row['usertype'] === 2)
                && $chairProgram !== ''
                && normalizeBaseProgram($row['program'] ?? '') === $chairProgram;
            if ($isSelf || $sameProgramFaculty) {
                $row['role'] = $roleLabel($row['usertype']);
                unset($row['usertype']);
                $out[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $out]);
        return;
    }

    // Faculty: only themselves (view/edit own specializations).
    if ($usertype === 2) {
        $stmt = $pdo->prepare("
            SELECT id, username, first_name, last_name,
                   CONCAT(first_name, ' ', last_name) as name, email, program
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data = [];
        if ($row) {
            $row['role'] = 'Faculty';
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        return;
    }

    echo json_encode(['success' => true, 'data' => []]);
}
