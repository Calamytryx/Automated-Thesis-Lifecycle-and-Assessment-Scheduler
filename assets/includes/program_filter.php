<?php
/**
 * Program Filter System
 * 
 * Implements role-based program filtering:
 * - Admin (usertype=0, id=0): See all programs
 * - Program Chair (usertype=0, id!=0): See only college programs
 * - Faculty (usertype=2): See only assigned section programs
 * - Other users: See their assigned program
 */

// Include guard
if (!defined('PROGRAM_FILTER_INCLUDED')) {
    define('PROGRAM_FILTER_INCLUDED', true);

    /**
     * Get visible programs for current user
     * 
     * Returns SQL WHERE clause for filtering programs based on user role
     * 
     * @param PDO $pdo Database connection
     * @param int $userId Current user ID
     * @param int $usertype Current user type (0=admin, 1=student, 2=faculty)
     * @return array ['sql' => SQL WHERE clause, 'params' => bind parameters]
     */
    function getVisibleProgramsFilter($pdo, $userId, $usertype) {
        
        // Super admin (usertype=0, id=0): See everything
        if ($userId === 0 && $usertype === 0) {
            return [
                'sql' => '1=1', // No filter - see all
                'params' => []
            ];
        }
        
        // Program Chair (usertype=0, id!=0): See only their college's programs
        if ($userId !== 0 && $usertype === 0) {
            // Get admin's college
            require_once __DIR__ . '/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            
            if (!$userCollege) {
                return [
                    'sql' => '1=0', // No access
                    'params' => []
                ];
            }
            
            return [
                'sql' => 'programs.college = :admin_college',
                'params' => [':admin_college' => $userCollege]
            ];
        }
        
        // Faculty (usertype=2): See only their assigned program
        if ($usertype === 2) {
            // Get faculty's program from users table
            $stmt = $pdo->prepare("
                SELECT DISTINCT program
                FROM users
                WHERE id = :user_id AND usertype = 2
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !$result['program']) {
                return [
                    'sql' => '1=0', // No program assigned
                    'params' => []
                ];
            }
            
            // Faculty sees only their own program
            // Program names can have specialization format like "Computer Science - AI"
            $userProgram = $result['program'];
            
            return [
                'sql' => "CONCAT(programs.name, CASE WHEN programs.specialization != '' THEN CONCAT(' - ', programs.specialization) ELSE '' END) = :faculty_program",
                'params' => [':faculty_program' => $userProgram]
            ];
        }
        
        // Default: No access
        return [
            'sql' => '1=0',
            'params' => []
        ];
    }

    /**
     * Get visible programs data (name + id) for current user
     * Used for dropdown selectors
     * 
     * @param PDO $pdo Database connection
     * @param int $userId Current user ID
     * @param int $usertype Current user type
     * @return array Array of programs with id, name, specialization, college
     */
    function getVisiblePrograms($pdo, $userId, $usertype) {
        
        // Super admin: Get all programs
        if ($userId === 0 && $usertype === 0) {
            $stmt = $pdo->prepare("
                SELECT id, name, specialization, college
                FROM programs
                ORDER BY name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Program Chair: Get college programs
        if ($userId !== 0 && $usertype === 0) {
            require_once __DIR__ . '/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            
            if (!$userCollege) {
                return [];
            }
            
            $stmt = $pdo->prepare("
                SELECT id, name, specialization, college
                FROM programs
                WHERE college = :college
                ORDER BY name ASC
            ");
            $stmt->execute([':college' => $userCollege]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Faculty: Get their assigned program only
        if ($usertype === 2) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.specialization, p.college
                FROM programs p
                JOIN users u ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                WHERE u.id = :user_id AND u.usertype = 2
                LIMIT 1
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? [$result] : [];
        }
        
        // Default: No programs
        return [];
    }

    /**
     * Get SQL WHERE clause for filtering by visible programs
     * 
     * @param PDO $pdo Database connection
     * @param int $userId Current user ID
     * @param int $usertype Current user type
     * @param string $tableAlias Table alias for programs (e.g., 'p', 't')
     * @return array ['sql' => WHERE clause, 'params' => bind params]
     */
    function getVisibleProgramsWhereClause($pdo, $userId, $usertype, $tableAlias = 'p') {
        
        // Super admin: No filter
        if ($userId === 0 && $usertype === 0) {
            return [
                'sql' => '',
                'params' => []
            ];
        }
        
        // Program Chair: Filter by college
        if ($userId !== 0 && $usertype === 0) {
            require_once __DIR__ . '/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            
            if (!$userCollege) {
                return [
                    'sql' => 'AND 1=0',
                    'params' => []
                ];
            }
            
            return [
                'sql' => "AND $tableAlias.college = :admin_college",
                'params' => [':admin_college' => $userCollege]
            ];
        }
        
        // Faculty: Filter by assigned sections
        if ($usertype === 2) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT p.id
                FROM programs p
                JOIN sections s ON s.program_id = p.id
                JOIN section_professors sp ON sp.section_id = s.id
                WHERE sp.professor_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $programs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($programs)) {
                return [
                    'sql' => 'AND 1=0',
                    'params' => []
                ];
            }
            
            $placeholders = implode(',', array_fill(0, count($programs), '?'));
            return [
                'sql' => "AND $tableAlias.id IN ($placeholders)",
                'params' => $programs
            ];
        }
        
        // Default: No access
        return [
            'sql' => 'AND 1=0',
            'params' => []
        ];
    }

    /**
     * Filter users by visible programs
     * 
     * @param PDO $pdo Database connection
     * @param int $userId Current user ID
     * @param int $usertype Current user type
     * @return array Array of users
     */
    function getVisibleUsers($pdo, $userId, $usertype) {
        
        // Super admin: Get all users
        if ($userId === 0 && $usertype === 0) {
            $stmt = $pdo->prepare("SELECT * FROM users ORDER BY first_name, last_name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Program Chair: Get college users
        if ($userId !== 0 && $usertype === 0) {
            require_once __DIR__ . '/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            
            if (!$userCollege) {
                return [];
            }
            
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.* FROM users u
                LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' 
                    THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                WHERE p.college = :college OR u.id = :user_id
                ORDER BY u.first_name, u.last_name
            ");
            $stmt->execute([':college' => $userCollege, ':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Faculty: Get all users from same program
        if ($usertype === 2) {
            // Get faculty's own program
            $stmt = $pdo->prepare("
                SELECT program
                FROM users
                WHERE id = :user_id AND usertype = 2
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !$result['program']) {
                return [];
            }
            
            // Get all users with same program
            $stmt = $pdo->prepare("
                SELECT * FROM users 
                WHERE program = :program
                ORDER BY first_name, last_name
            ");
            $stmt->execute([':program' => $result['program']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Default: No users
        return [];
    }

    /**
     * Filter teams by visible programs
     * 
     * @param PDO $pdo Database connection
     * @param int $userId Current user ID
     * @param int $usertype Current user type
     * @return array Array of teams
     */
    function getVisibleTeams($pdo, $userId, $usertype) {
        
        // Super admin: Get all teams
        if ($userId === 0 && $usertype === 0) {
            $stmt = $pdo->prepare("
                SELECT t.*, rt.title FROM teams t 
                LEFT JOIN research_titles rt ON t.id = rt.team_id
                ORDER BY t.name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Program Chair: Get college teams
        if ($userId !== 0 && $usertype === 0) {
            require_once __DIR__ . '/auth_functions.php';
            $userCollege = get_user_college($pdo, $userId);
            
            if (!$userCollege) {
                return [];
            }
            
            $stmt = $pdo->prepare("
                SELECT t.*, rt.title FROM teams t 
                LEFT JOIN research_titles rt ON t.id = rt.team_id
                JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' 
                    THEN CONCAT(' - ', p.specialization) ELSE '' END) = t.program
                WHERE p.college = :college
                ORDER BY t.name
            ");
            $stmt->execute([':college' => $userCollege]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Faculty: Get teams from same program
        if ($usertype === 2) {
            $stmt = $pdo->prepare("
                SELECT program
                FROM users
                WHERE id = :user_id AND usertype = 2
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !$result['program']) {
                return [];
            }
            
            // Get all teams from same program
            $stmt = $pdo->prepare("
                SELECT t.*, rt.title FROM teams t 
                LEFT JOIN research_titles rt ON t.id = rt.team_id
                WHERE t.program = :program
                ORDER BY t.name
            ");
            $stmt->execute([':program' => $result['program']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Default: No teams
        return [];
    }
}
?>
