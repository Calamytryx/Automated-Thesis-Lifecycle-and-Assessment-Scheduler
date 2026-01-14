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

$userId = $_SESSION['id'];
$usertype = $_SESSION['usertype'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'assign_to_user':
            // Check permissions
            if (!canAssignSpecializations($pdo, $userId, $usertype)) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            assignToUser($pdo, $_POST, $userId);
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
            removeFromUser($pdo, $_POST);
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
            getUserSpecializations($pdo, $_GET['user_id'] ?? null);
            break;
        
        case 'get_team_specializations':
            getTeamSpecializations($pdo, $_GET['team_id'] ?? null);
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
function assignToUser($pdo, $data, $assignedBy) {
    $userId = $data['user_id'] ?? null;
    $specializationId = $data['specialization_id'] ?? null;
    
    if (!$userId || !$specializationId) {
        echo json_encode(['success' => false, 'message' => 'User ID and Specialization ID required']);
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
    $teamId = $data['team_id'] ?? null;
    $specializationId = $data['specialization_id'] ?? null;
    
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
function removeFromUser($pdo, $data) {
    $userId = $data['user_id'] ?? null;
    $specializationName = $data['specialization_name'] ?? null;
    
    if (!$userId || !$specializationName) {
        echo json_encode(['success' => false, 'message' => 'User ID and Specialization Name required']);
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
    $teamId = $data['team_id'] ?? null;
    $specializationName = $data['specialization_name'] ?? null;
    
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
function getUserSpecializations($pdo, $userId) {
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
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
    if ($usertype === 0) {
        // Check if super admin or program chair
        if ($userId === 0) {
            // Super Admin: Can assign to all admins and faculty
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    CONCAT(first_name, ' ', last_name) as name,
                    email,
                    program,
                    CASE usertype 
                        WHEN 0 THEN 'Admin'
                        WHEN 2 THEN 'Faculty'
                        ELSE 'Other'
                    END as role
                FROM users
                WHERE usertype IN (0, 2)
                ORDER BY first_name, last_name
            ");
            $stmt->execute();
        } else {
            // Program Chair: Can assign to admins and faculty in their college only
            require_once __DIR__ . '/../../assets/includes/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            if (!$userCollege) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    u.id,
                    CONCAT(u.first_name, ' ', u.last_name) as name,
                    u.email,
                    u.program,
                    CASE u.usertype 
                        WHEN 0 THEN 'Admin'
                        WHEN 2 THEN 'Faculty'
                        ELSE 'Other'
                    END as role
                FROM users u
                LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                WHERE u.usertype IN (0, 2) AND (p.college = ? OR u.id = ?)
                ORDER BY u.first_name, u.last_name
            ");
            $stmt->execute([$userCollege, $userId]);
        }
    } else if ($usertype === 2) {
        // Research Professor: Cannot assign to users, only teams
        echo json_encode(['success' => true, 'data' => []]);
        return;
    } else {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
}
