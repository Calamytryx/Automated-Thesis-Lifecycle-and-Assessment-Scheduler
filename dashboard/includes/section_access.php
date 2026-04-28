<?php
/**
 * Section-based access control for professors
 * Ensures professors can only see students from their assigned section
 */

/**
 * Get the section assigned to a professor
 * @param PDO $pdo Database connection
 * @param int $professor_id User ID of the professor
 * @return string|null Section name or null if not assigned
 */
function getProfessorSection($pdo, $professor_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT section 
            FROM section_professors 
            WHERE professor_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$professor_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['section'] : null;
    } catch (Exception $e) {
        error_log("Error getting professor section: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if a professor has a section assignment
 * @param PDO $pdo Database connection
 * @param int $professor_id User ID of the professor
 * @return bool True if professor has a section assignment
 */
function professorHasSectionAssignment($pdo, $professor_id) {
    return getProfessorSection($pdo, $professor_id) !== null;
}

/**
 * Check if a user can access the dashboard page
 *
 * Rules:
 * - Superadmin and Program Chair (usertype=0): allowed
 * - Section Professors (usertype=2 with section_professors assignment): allowed
 * - Other users: denied
 *
 * @param PDO $pdo Database connection
 * @param int $userId Current user's ID
 * @param int $userType Current user's type
 * @return bool True if user can access dashboard
 */
function userCanAccessDashboard($pdo, $userId, $userType) {
    // Superadmin + Program Chair
    if ((int)$userType === 0) {
        return true;
    }

    // Only faculty can qualify as section professors
    if ((int)$userType !== 2 || (int)$userId <= 0) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM section_professors WHERE professor_id = ?");
        $stmt->execute([(int)$userId]);
        return ((int)$stmt->fetchColumn()) > 0;
    } catch (Exception $e) {
        error_log("Error checking dashboard access: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all sections assigned to a professor (if any)
 * @param PDO $pdo Database connection
 * @param int $professor_id User ID of the professor
 * @return array Array of section names
 */
function getProfessorSections($pdo, $professor_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT section 
            FROM section_professors 
            WHERE professor_id = ?
            ORDER BY section
        ");
        $stmt->execute([$professor_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error getting professor sections: " . $e->getMessage());
        return [];
    }
}

/**
 * Add WHERE clause fragment for filtering by professor's section
 * @param string $userType User type (0=admin, 1=student, 2=faculty)
 * @param int $userId Current user's ID
 * @param string $tableAlias Table alias for the students table (e.g., 'u' for users)
 * @return array ['clause' => WHERE clause, 'params' => parameter values]
 */
function getSectionFilterClause($pdo, $userType, $userId, $tableAlias = 'u') {
    // Only apply section filter for faculty (usertype 2)
    if ($userType !== 2) {
        return ['clause' => '', 'params' => []];
    }
    
    $sections = getProfessorSections($pdo, $userId);

    if (empty($sections)) {
        // Professor has no section assignment, see all data
        return ['clause' => '', 'params' => []];
    }

    // Professor has one or more section assignments, filter by them
    $placeholders = implode(',', array_fill(0, count($sections), '?'));
    return [
        'clause' => " AND {$tableAlias}.section IN ($placeholders)",
        'params' => $sections
    ];
}

/**
 * Get students visible to a professor (based on section assignment)
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @return array Array of student user IDs
 */
function getVisibleStudentsForProfessor($pdo, $professor_id) {
    try {
        $sections = getProfessorSections($pdo, $professor_id);
        
        if (empty($sections)) {
            // No section assignment, return all students
            $stmt = $pdo->query("SELECT id FROM users WHERE usertype = 1");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        // Return only students from all assigned sections
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $sql = "
            SELECT id 
            FROM users 
            WHERE usertype = 1 AND section IN ($placeholders)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($sections);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error getting visible students: " . $e->getMessage());
        return [];
    }
}

/**
 * Get teams visible to a professor (based on their section assignment)
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @return array Array of team IDs
 */
function getVisibleTeamsForProfessor($pdo, $professor_id) {
    try {
        $sections = getProfessorSections($pdo, $professor_id);

        if (empty($sections)) {
            // No section assignment, return all teams
            $stmt = $pdo->query("SELECT id FROM teams");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // Return only teams with members from any of the assigned sections
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $sql = "
            SELECT DISTINCT t.id
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            JOIN users u ON tm.user_id = u.id
            WHERE u.section IN ($placeholders)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($sections);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error getting visible teams: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if a professor can view a specific user/student
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @param int $student_id Student's user ID
 * @return bool True if professor can view this student
 */
function canProfessorViewStudent($pdo, $professor_id, $student_id) {
    try {
        $sections = getProfessorSections($pdo, $professor_id);

        if (empty($sections)) {
            // No section assignment, can view all
            return true;
        }

        // Check if student is in any of the professor's sections
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $sql = "SELECT 1 FROM users WHERE id = ? AND section IN ($placeholders)";
        $params = array_merge([$student_id], $sections);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Error checking professor view permission: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a professor can view a specific team
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @param int $team_id Team's ID
 * @return bool True if professor can view this team
 */
function canProfessorViewTeam($pdo, $professor_id, $team_id) {
    try {
        $sections = getProfessorSections($pdo, $professor_id);

        if (empty($sections)) {
            // No section assignment, can view all
            return true;
        }

        // Check if team has members from any assigned section
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $sql = "
            SELECT 1
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            JOIN users u ON tm.user_id = u.id
            WHERE t.id = ? AND u.section IN ($placeholders)
        ";
        $params = array_merge([$team_id], $sections);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Error checking team view permission: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a professor can CREATE a team with given members
 * Professors can only add students from their ASSIGNED SECTION(S)
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @param array $memberIds Array of student user IDs to be added to team
 * @return array ['canCreate' => bool, 'message' => string]
 */
function canProfessorCreateTeam($pdo, $professor_id, $memberIds = []) {
    try {
        // Get ALL sections assigned to the professor (can have multiple)
        $assignedSections = getProfessorSections($pdo, $professor_id);
        
        // If professor has no section assignment, they can create teams with any students
        if (empty($assignedSections)) {
            return ['canCreate' => true, 'message' => ''];
        }
        
        // If no members specified, professor can create
        if (empty($memberIds)) {
            return ['canCreate' => true, 'message' => ''];
        }

        // Only student members are constrained by section.
        // Advisers/faculty often have NULL section values and should not fail this check.
        $memberIds = array_values(array_unique(array_filter(array_map('intval', $memberIds), function ($id) {
            return $id > 0;
        })));

        if (empty($memberIds)) {
            return ['canCreate' => true, 'message' => ''];
        }
        
        // Check if all student members are from professor's assigned sections
        $sectionPlaceholders = implode(',', array_fill(0, count($assignedSections), '?'));
        $memberPlaceholders = implode(',', array_fill(0, count($memberIds), '?'));
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN section IN ($sectionPlaceholders) THEN 1 ELSE 0 END) as in_allowed_sections
            FROM users
            WHERE id IN ($memberPlaceholders)
              AND usertype = 1
        ");
        
        $params = array_merge($assignedSections, $memberIds);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0 && $result['in_allowed_sections'] < $result['total']) {
            $sectionsStr = implode(', ', $assignedSections);
            return [
                'canCreate' => false,
                'message' => "You can only create teams with students from your assigned section(s): $sectionsStr"
            ];
        }
        
        return ['canCreate' => true, 'message' => ''];
    } catch (Exception $e) {
        error_log("Error checking team create permission: " . $e->getMessage());
        return ['canCreate' => false, 'message' => 'Permission check failed: ' . $e->getMessage()];
    }
}

/**
 * Check if a title contains "title proposal" (case-insensitive)
 * @param string $title The title to check
 * @return bool True if title is for a title proposal
 */
function isTitleProposal($title) {
    return stripos($title, 'title proposal') !== false;
}

/**
 * Get the college of a professor (based on their programs)
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @return string|null College name or null if not found
 */
function getProfessorCollege($pdo, $professor_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.college
            FROM users u
            LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' 
                THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
            WHERE u.id = ? AND p.college IS NOT NULL
            LIMIT 1
        ");
        $stmt->execute([$professor_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['college'] : null;
    } catch (Exception $e) {
        error_log("Error getting professor college: " . $e->getMessage());
        return null;
    }
}

/**
 * Get available students for professor to add to a team
 * Only returns students from professor's assigned section(s)
 * If professor has sections but no students match, falls back to all students
 * @param PDO $pdo Database connection
 * @param int $professor_id Professor's user ID
 * @return array Array of students [id, first_name, last_name, username, section]
 */
function getAvailableStudentsForProfessor($pdo, $professor_id) {
    try {
        $sections = getProfessorSections($pdo, $professor_id);
        error_log("getAvailableStudentsForProfessor - professor_id: $professor_id, sections: " . json_encode($sections));
        
        // If no sections assigned, return all students
        if (empty($sections)) {
            error_log("getAvailableStudentsForProfessor - No sections assigned, returning all students");
            $stmt = $pdo->prepare("
                SELECT id, first_name, last_name, username, section, usertype
                FROM users
                WHERE usertype = 1
                ORDER BY first_name, last_name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Try to return students from assigned sections
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, username, section, usertype
            FROM users
            WHERE usertype = 1 AND section IN ($placeholders)
            ORDER BY section, first_name, last_name
        ");
        $stmt->execute($sections);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("getAvailableStudentsForProfessor - Query returned " . count($students) . " students from sections");
        
        // FALLBACK: If no students found with matching sections, return ALL students
        // This handles the case where students don't have section values populated
        if (empty($students)) {
            error_log("getAvailableStudentsForProfessor - No students in assigned sections, falling back to ALL students");
            $stmt = $pdo->prepare("
                SELECT id, first_name, last_name, username, section, usertype
                FROM users
                WHERE usertype = 1
                ORDER BY first_name, last_name
            ");
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("getAvailableStudentsForProfessor - Fallback returned " . count($students) . " students");
        }
        
        return $students;
    } catch (Exception $e) {
        error_log("Error getting available students: " . $e->getMessage());
        return [];
    }
}

/**
 * Get available advisers for a team with access control
 * Advisers must be from the SAME COLLEGE as the team's program
 * Access control:
 * - Admin (id=0): Full access to all advisers
 * - Program Chair (usertype=0, id!=0): Access to college advisers only
 * - Faculty (usertype=2): Access to section advisers only
 * @param PDO $pdo Database connection
 * @param string $teamProgram Team's program name
 * @param int $currentUserId Current user's ID
 * @param int $currentUserType Current user's type (0=admin/chair, 2=faculty)
 * @return array Array of professors [id, first_name, last_name, username]
 */
function getAvailableAdvisersForTeam($pdo, $teamProgram, $currentUserId = 0, $currentUserType = 0) {
    try {
        // Get college from the team's program
        $stmt = $pdo->prepare("
            SELECT college FROM programs 
            WHERE CONCAT(name, CASE WHEN specialization IS NOT NULL AND specialization != '' 
                THEN CONCAT(' - ', specialization) ELSE '' END) = ?
            LIMIT 1
        ");
        $stmt->execute([$teamProgram]);
        $programResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$programResult) {
            return []; // Program not found
        }
        
        $college = $programResult['college'];
        
        // Access control: determine what advisers user can see
        if ($currentUserId === 0) {
            // Admin (id=0): Full access to all advisers
            error_log("getAvailableAdvisersForTeam - Admin (id=0) accessing all advisers in college: $college");
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.first_name, u.last_name, u.username
                FROM users u
                LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' 
                    THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                WHERE u.usertype = 2 AND p.college = ?
                ORDER BY u.first_name, u.last_name
            ");
            $stmt->execute([$college]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($currentUserType === 0) {
            // Program Chair (usertype=0, id!=0): Access to college advisers only
            error_log("getAvailableAdvisersForTeam - Program Chair (id=$currentUserId) accessing college advisers: $college");
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.first_name, u.last_name, u.username
                FROM users u
                LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' 
                    THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                WHERE u.usertype = 2 AND p.college = ?
                ORDER BY u.first_name, u.last_name
            ");
            $stmt->execute([$college]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($currentUserType === 2) {
            // Faculty (usertype=2): Access to section advisers only
            error_log("getAvailableAdvisersForTeam - Faculty (id=$currentUserId) accessing section advisers");
                $sections = getProfessorSections($pdo, $currentUserId);
            
                if (empty($sections)) {
                // No section assignment, see all college advisers
                error_log("getAvailableAdvisersForTeam - Faculty has no section, accessing all college advisers: $college");
                $stmt = $pdo->prepare("
                    SELECT DISTINCT u.id, u.first_name, u.last_name, u.username
                    FROM users u
                    LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' 
                        THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                    WHERE u.usertype = 2 AND p.college = ?
                    ORDER BY u.first_name, u.last_name
                ");
                $stmt->execute([$college]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
                // Faculty has section(s): only show advisers from same section(s)
                error_log("getAvailableAdvisersForTeam - Faculty has sections: " . json_encode($sections) . ", filtering advisers");
                $placeholders = implode(',', array_fill(0, count($sections), '?'));
                $sql = "
                    SELECT DISTINCT u.id, u.first_name, u.last_name, u.username
                    FROM users u
                    LEFT JOIN section_professors sp ON u.id = sp.professor_id
                    LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' 
                        THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                    WHERE u.usertype = 2 AND sp.section IN ($placeholders) AND p.college = ?
                    ORDER BY u.first_name, u.last_name
                ";
                $stmt = $pdo->prepare($sql);
                $params = array_merge($sections, [$college]);
                $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Default: no access
        error_log("getAvailableAdvisersForTeam - Unknown user type: $currentUserType, no access granted");
        return [];
    } catch (Exception $e) {
        error_log("Error getting available advisers: " . $e->getMessage());
        return [];
    }
}

?>
