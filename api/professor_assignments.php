<?php
/**
 * Professor Assignments API
 * 
 * Handles CRUD operations for professor-to-team/section assignments
 * Admin (usertype 0): Can create and manage assignments
 * Faculty (usertype 2): Can accept/reject assignments
 * Students (usertype 1): Can view their team's assigned professors
 */

session_start();
require_once '../assets/setup/db.inc.php';
require_once '../dashboard/includes/section_access.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['id'];
$userType = $_SESSION['usertype'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// DEBUG: Log what we're receiving
error_log("=== PROFESSOR_ASSIGNMENTS API DEBUG ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("URL: " . $_SERVER['REQUEST_URI']);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? 'none'));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
error_log("GET params: " . json_encode($_GET));
error_log("POST params: " . json_encode($_POST));
error_log("Action extracted: " . ($action ?? 'NULL'));
error_log("Raw input: " . file_get_contents('php://input'));
error_log("=======================================");

try {
    match ($action) {
        'list_pending' => listPendingAssignments(),
        'list_section_professors' => listSectionProfessors(),
        'assign_professor_to_section' => assignProfessorToSection(),
        'delete_section_assignment' => deleteSectionAssignment(),
        'assign_professor_to_team' => assignProfessorToTeam(),
        'accept_assignment' => acceptAssignment(),
        'reject_assignment' => rejectAssignment(),
        'get_assignment_status' => getAssignmentStatus(),
        'list_team_professors' => listTeamProfessors(),
        'list_teams' => listTeams(),
        'list_sections' => listSections(),
        'list_professors' => listProfessors(),
        'list_history' => listAssignmentHistory(),
        'update_status' => updateAssignmentStatus(),
        'delete_assignment' => deleteAssignment(),
        default => handleInvalidAction()
    };
} catch (Exception $e) {
    error_log("Professor Assignments API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * List pending professor assignments for a faculty member (usertype 2)
 */
function listPendingAssignments() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 2) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only faculty can view pending assignments']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT 
            tpa.id,
            tpa.team_id,
            tpa.professor_id,
            tpa.defense_type,
            tpa.assignment_status,
            tpa.assignment_notes,
            tpa.created_at,
            t.name AS team_name,
            t.program,
            u.first_name,
            u.last_name,
            u.email
        FROM team_professor_assignments tpa
        JOIN teams t ON tpa.team_id = t.id
        JOIN users u ON tpa.assigned_by = u.id
        WHERE tpa.professor_id = ? AND tpa.assignment_status = 'pending'
        ORDER BY tpa.created_at DESC
    ");
    
    $stmt->execute([$userId]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $assignments,
        'count' => count($assignments)
    ]);
}

/**
 * List all professors assigned to sections
 */
function listSectionProfessors() {
    global $pdo, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only admins can list section professors']);
        return;
    }

    try {
        // Check if table exists
        $stmt = $pdo->prepare("
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'section_professors'
        ");
        $stmt->execute();
        if (!$stmt->fetch()) {
            // Table doesn't exist, return empty
            echo json_encode([
                'success' => true,
                'data' => [],
                'count' => 0,
                'message' => 'No assignments table'
            ]);
            return;
        }

        // Get all columns to handle both old and new schema
        $descStmt = $pdo->query("DESCRIBE section_professors");
        $columns = $descStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // Build query based on what columns exist
        if (in_array('section', $columns)) {
            // New schema with string section
            $query = "
                SELECT 
                    sp.id,
                    sp.section,
                    sp.professor_id,
                    sp.status,
                    u.first_name,
                    u.last_name,
                    u.email,
                    sp.assigned_at
                FROM section_professors sp
                JOIN users u ON sp.professor_id = u.id
                ORDER BY sp.section, u.last_name
            ";
        } else {
            // Old schema with section_id foreign key
            $query = "
                SELECT 
                    sp.id,
                    s.name AS section,
                    sp.professor_id,
                    sp.status,
                    u.first_name,
                    u.last_name,
                    u.email,
                    sp.assigned_at
                FROM section_professors sp
                JOIN sections s ON sp.section_id = s.id
                JOIN users u ON sp.professor_id = u.id
                ORDER BY s.name, u.last_name
            ";
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $professors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $professors,
            'count' => count($professors)
        ]);
    } catch (Exception $e) {
        // Return empty data instead of error
        error_log("listSectionProfessors error: " . $e->getMessage());
        echo json_encode([
            'success' => true,
            'data' => [],
            'count' => 0
        ]);
    }
}

/**
 * Assign a professor to a section (admin only)
 */
function assignProfessorToSection() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only admins can assign professors to sections']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $section = $input['section'] ?? null;
    $sectionId = $input['section_id'] ?? null;
    $professorId = $input['professor_id'] ?? null;

    // Debug logging
    error_log("assignProfessorToSection input: " . json_encode([
        'section' => $section,
        'section_id' => $sectionId,
        'professor_id' => $professorId,
        'raw_input' => $input
    ]));

    if (!$professorId || (!$section && !$sectionId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'section/section_id and professor_id required. Got: section=' . ($section ?? 'NULL') . ', sectionId=' . ($sectionId ?? 'NULL') . ', professorId=' . ($professorId ?? 'NULL')]);
        return;
    }

    // Verify professor exists and is faculty (usertype 2)
    $profStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND usertype = 2");
    $profStmt->execute([$professorId]);
    if (!$profStmt->fetchColumn()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid professor ID: ' . $professorId]);
        return;
    }

    try {
        // Check what schema the table has
        $descStmt = $pdo->query("DESCRIBE section_professors");
        $columns = $descStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        if (in_array('section', $columns)) {
            // New schema with string section
            if (!$section) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'section is required for new schema']);
                return;
            }

            // Check if already assigned
            $checkStmt = $pdo->prepare("SELECT id FROM section_professors WHERE section = ? AND professor_id = ?");
            $checkStmt->execute([$section, $professorId]);
            if ($checkStmt->fetchColumn()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Professor already assigned to this section']);
                return;
            }

            $stmt = $pdo->prepare("
                INSERT INTO section_professors (section, professor_id, status, assigned_by, assigned_at)
                VALUES (?, ?, 'active', ?, NOW())
            ");
            $stmt->execute([$section, $professorId, $userId]);
            $key = $section;
        } else {
            // Old schema with section_id foreign key
            if (!$sectionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'section_id is required for old schema']);
                return;
            }

            // Check if already assigned
            $checkStmt = $pdo->prepare("SELECT id FROM section_professors WHERE section_id = ? AND professor_id = ?");
            $checkStmt->execute([$sectionId, $professorId]);
            if ($checkStmt->fetchColumn()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Professor already assigned to this section']);
                return;
            }

            $stmt = $pdo->prepare("
                INSERT INTO section_professors (section_id, professor_id, status, assigned_by, assigned_at)
                VALUES (?, ?, 'active', ?, NOW())
            ");
            $stmt->execute([$sectionId, $professorId, $userId]);
            $key = $sectionId;
        }

        error_log("Professor assignment: Admin {$userId} assigned professor {$professorId} to section {$key}");

        echo json_encode([
            'success' => true,
            'message' => 'Professor assigned to section successfully',
        ]);
    } catch (Exception $e) {
        error_log("assignProfessorToSection error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Table does not exist. Please create section_professors table first.']);
    }
}

/**
 * Delete section assignment (admin only)
 */
function deleteSectionAssignment() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only admins can delete assignments']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $sectionId = $input['section_id'] ?? null;

    if (!$sectionId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'section_id required']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM section_professors WHERE id = ?");
    $stmt->execute([$sectionId]);

    error_log("Section assignment deletion: Admin {$userId} removed professor assignment for section");

    echo json_encode([
        'success' => true,
        'message' => 'Section assignment deleted successfully'
    ]);
}

/**
 * List all sections (for dropdown in admin interface)
 * - Admin (id=0): All sections
 * - Program Chair (usertype=0, id!=0): Sections in their college only
 */
function listSections() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    // Admin (id=0): Get all sections
    if ($userId === 0) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT section
            FROM users
            WHERE section IS NOT NULL AND section != ''
            ORDER BY section ASC
        ");
        $stmt->execute();
    }
    // Program Chair (usertype=0, id!=0): Get sections in their college
    else {
        require_once '../assets/includes/auth_functions.php';
        $userCollege = get_user_college($pdo, $userId);
        
        if (!$userCollege) {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No college found for user']);
            return;
        }
        
        error_log("listSections - Program Chair (userId=$userId) college: $userCollege");
        
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.section
            FROM users u
            LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
            WHERE u.section IS NOT NULL 
              AND u.section != ''
              AND p.college = ?
            ORDER BY u.section ASC
        ");
        $stmt->execute([$userCollege]);
        
        error_log("listSections - Query executed for college: $userCollege");
    }
    
    $sections = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    error_log("listSections - Found " . count($sections) . " sections: " . implode(', ', $sections));
    
    echo json_encode(['success' => true, 'data' => $sections]);
}

/**
 * Assign a professor to a specific team for a defense (admin only)
 * Only applies to title_defense and above
 */
function assignProfessorToTeam() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only admins can assign professors to teams']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $teamId = $input['team_id'] ?? null;
    $professorId = $input['professor_id'] ?? null;
    $defenseType = $input['defense_type'] ?? null;

    if (!$teamId || !$professorId || !$defenseType) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'team_id, professor_id, and defense_type required']);
        return;
    }

    // Verify professor exists and is faculty
    $profStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND usertype = 2");
    $profStmt->execute([$professorId]);
    if (!$profStmt->fetchColumn()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid professor ID']);
        return;
    }

    // Check if team exists
    $teamStmt = $pdo->prepare("SELECT id FROM teams WHERE id = ?");
    $teamStmt->execute([$teamId]);
    if (!$teamStmt->fetchColumn()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid team ID']);
        return;
    }

    try {
        // Check if adviser already assigned for this team
        $checkStmt = $pdo->prepare("SELECT id FROM team_members WHERE team_id = ? AND role = 'adviser'");
        $checkStmt->execute([$teamId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing adviser
            $stmt = $pdo->prepare(
                "UPDATE team_members SET user_id = ?, updated_at = NOW() WHERE team_id = ? AND role = 'adviser'"
            );
            $stmt->execute([$professorId, $teamId]);
            $message = 'Adviser updated for team successfully';
        } else {
            // Insert new adviser role for team
            $stmt = $pdo->prepare(
                "INSERT INTO team_members (team_id, user_id, role, created_at, updated_at) 
                 VALUES (?, ?, 'adviser', NOW(), NOW())"
            );
            $stmt->execute([$teamId, $professorId]);
            $message = 'Adviser assigned to team successfully';
        }

        error_log("Team assignment: Admin {$userId} assigned professor {$professorId} to team {$teamId} for defense type {$defenseType}");

        echo json_encode([
            'success' => true,
            'message' => $message,
            'team_id' => $teamId,
            'professor_id' => $professorId,
            'defense_type' => $defenseType
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Faculty member accepts a pending assignment
 */
function acceptAssignment() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 2) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only faculty can accept assignments']);
        return;
    }

    $assignmentId = $_POST['assignment_id'] ?? null;

    if (!$assignmentId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'assignment_id required']);
        return;
    }

    // Verify the assignment belongs to the current user
    $stmt = $pdo->prepare(
        "SELECT id, team_id, professor_id, defense_type, assignment_status FROM team_professor_assignments WHERE id = ?"
    );
    $stmt->execute([$assignmentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
        return;
    }

    if ($assignment['professor_id'] != $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only accept your own assignments']);
        return;
    }

    if ($assignment['assignment_status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Cannot accept assignment with status: {$assignment['assignment_status']}"]);
        return;
    }

    $updateStmt = $pdo->prepare(
        "UPDATE team_professor_assignments SET assignment_status = 'accepted', accepted_at = NOW() WHERE id = ?"
    );
    $updateStmt->execute([$assignmentId]);

    // Log the action
    $logStmt = $pdo->prepare("
        INSERT INTO professor_assignment_history 
        (assignment_id, team_id, professor_id, defense_type, old_status, new_status, action_by, action_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Faculty accepted assignment')
    ");
    $logStmt->execute([
        $assignmentId,
        $assignment['team_id'],
        $assignment['professor_id'],
        $assignment['defense_type'],
        'pending',
        'accepted',
        $userId
    ]);

    error_log("Assignment {$assignmentId} accepted by professor {$userId}");

    echo json_encode([
        'success' => true,
        'message' => 'Assignment accepted successfully',
        'assignment_id' => $assignmentId
    ]);
}

/**
 * Faculty member rejects a pending assignment
 */
function rejectAssignment() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 2) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only faculty can reject assignments']);
        return;
    }

    $assignmentId = $_POST['assignment_id'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if (!$assignmentId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'assignment_id required']);
        return;
    }

    // Verify the assignment belongs to the current user
    $stmt = $pdo->prepare(
        "SELECT id, team_id, professor_id, defense_type, assignment_status FROM team_professor_assignments WHERE id = ?"
    );
    $stmt->execute([$assignmentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
        return;
    }

    if ($assignment['professor_id'] != $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only reject your own assignments']);
        return;
    }

    if ($assignment['assignment_status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Cannot reject assignment with status: {$assignment['assignment_status']}"]);
        return;
    }

    $updateStmt = $pdo->prepare(
        "UPDATE team_professor_assignments SET assignment_status = 'rejected' WHERE id = ?"
    );
    $updateStmt->execute([$assignmentId]);

    // Log the action
    $logStmt = $pdo->prepare("
        INSERT INTO professor_assignment_history 
        (assignment_id, team_id, professor_id, defense_type, old_status, new_status, action_by, action_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $logStmt->execute([
        $assignmentId,
        $assignment['team_id'],
        $assignment['professor_id'],
        $assignment['defense_type'],
        'pending',
        'rejected',
        $userId,
        $reason
    ]);

    error_log("Assignment {$assignmentId} rejected by professor {$userId}. Reason: {$reason}");

    echo json_encode([
        'success' => true,
        'message' => 'Assignment rejected',
        'assignment_id' => $assignmentId
    ]);
}

/**
 * Get assignment status for a specific team and defense type
 */
function getAssignmentStatus() {
    global $pdo;

    $teamId = $_GET['team_id'] ?? null;
    $defenseType = $_GET['defense_type'] ?? null;

    if (!$teamId || !$defenseType) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'team_id and defense_type required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT 
            tpa.id,
            tpa.professor_id,
            tpa.assignment_status,
            tpa.accepted_at,
            tpa.created_at,
            u.first_name,
            u.last_name,
            u.email,
            u.username
        FROM team_professor_assignments tpa
        LEFT JOIN users u ON tpa.professor_id = u.id
        WHERE tpa.team_id = ? AND tpa.defense_type = ?
        ORDER BY tpa.created_at
    ");
    
    $stmt->execute([$teamId, $defenseType]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'team_id' => $teamId,
        'defense_type' => $defenseType,
        'assignments' => $assignments,
        'count' => count($assignments)
    ]);
}

/**
 * Get all professors assigned to a team
 */
function listTeamProfessors() {
    global $pdo;

    // Get all teams with their adviser (team member with role='adviser' and usertype=2)
    $query = "
        SELECT 
            t.id AS team_id,
            t.name AS team_name,
            t.program,
            u.id AS professor_id,
            u.first_name,
            u.last_name,
            u.email,
            tm.role,
            'title_proposal' AS defense_type
        FROM team_members tm
        JOIN teams t ON tm.team_id = t.id
        JOIN users u ON tm.user_id = u.id
        WHERE tm.role = 'adviser' AND u.usertype = 2
        ORDER BY t.name, u.last_name
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $professors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'professors' => $professors,
        'count' => count($professors)
    ]);
}

/**
 * List all teams (for dropdown in admin interface)
 */
function listTeams() {
    global $pdo, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT id, name, program
        FROM teams
        ORDER BY name ASC
    ");
    $stmt->execute();
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $teams]);
}

/**
 * List all professors/faculty (for dropdown in admin interface)
 */
function listProfessors() {
    global $pdo, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email
        FROM users
        WHERE usertype = 2
        ORDER BY first_name, last_name ASC
    ");
    $stmt->execute();
    $professors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $professors]);
}

/**
 * List assignment history
 */
function listAssignmentHistory() {
    global $pdo, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    // Get all adviser assignments with their assignment dates
    $stmt = $pdo->prepare("
        SELECT 
            tm.id AS assignment_id,
            tm.team_id,
            t.name AS team_name,
            t.program,
            tm.user_id AS professor_id,
            u.first_name AS professor_first_name,
            u.last_name AS professor_last_name,
            u.email,
            tm.created_at,
            tm.updated_at
        FROM team_members tm
        JOIN teams t ON tm.team_id = t.id
        JOIN users u ON tm.user_id = u.id
        WHERE tm.role = 'adviser' AND u.usertype = 2
        ORDER BY tm.updated_at DESC
        LIMIT 100
    ");
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the history data
    $formattedHistory = array_map(function($record) {
        return [
            'id' => $record['assignment_id'],
            'team_id' => $record['team_id'],
            'team_name' => $record['team_name'],
            'program' => $record['program'],
            'professor_id' => $record['professor_id'],
            'professor_name' => $record['professor_first_name'] . ' ' . $record['professor_last_name'],
            'email' => $record['email'],
            'assigned_at' => $record['created_at'],
            'updated_at' => $record['updated_at']
        ];
    }, $history);
    
    echo json_encode(['success' => true, 'data' => $formattedHistory]);
}

/**
 * Update assignment status
 */
function updateAssignmentStatus() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $assignmentId = $input['assignment_id'] ?? null;
    $newStatus = $input['new_status'] ?? null;
    $notes = $input['notes'] ?? '';

    if (!$assignmentId || !$newStatus) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    // Get current assignment details
    $stmt = $pdo->prepare("
        SELECT team_id, professor_id, defense_type, assignment_status
        FROM team_professor_assignments
        WHERE id = ?
    ");
    $stmt->execute([$assignmentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
        return;
    }

    // Update assignment status
    $stmt = $pdo->prepare("
        UPDATE team_professor_assignments
        SET assignment_status = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $assignmentId]);

    // Log to history
    $stmt = $pdo->prepare("
        INSERT INTO professor_assignment_history 
        (assignment_id, team_id, professor_id, defense_type, old_status, new_status, action_by, action_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $assignmentId,
        $assignment['team_id'],
        $assignment['professor_id'],
        $assignment['defense_type'],
        $assignment['assignment_status'],
        $newStatus,
        $userId,
        $notes
    ]);

    echo json_encode(['success' => true, 'message' => 'Assignment status updated']);
}

/**
 * Delete assignment
 */
function deleteAssignment() {
    global $pdo, $userId, $userType;
    
    if ($userType !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $teamId = $input['team_id'] ?? null;

    if (!$teamId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing team ID']);
        return;
    }

    try {
        // Delete the adviser role for this team
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND role = 'adviser'");
        $stmt->execute([$teamId]);

        error_log("Assignment deletion: Admin {$userId} removed adviser assignment for team {$teamId}");

        echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleInvalidAction() {
    global $action;
    error_log("Invalid action received: " . ($action ?? 'NULL'));
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid action: ' . ($action ?? 'NULL/MISSING'),
        'debug' => [
            'action' => $action,
            'get_action' => $_GET['action'] ?? null,
            'post_action' => $_POST['action'] ?? null
        ]
    ]);
}
?>
