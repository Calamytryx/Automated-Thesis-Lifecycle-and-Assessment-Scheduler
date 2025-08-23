<?php
require_once '../../../assets/setup/db.inc.php';
require_once '../../../assets/includes/auth_functions.php';

header('Content-Type: application/json');

// 📌 Session check
if (session_status() == PHP_SESSION_NONE) session_start();

// 🛡️ Basic access check - Must be logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

// 📦 GET parameters
$table    = $_GET['table'] ?? '';
$page     = max(1, intval($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;
$search   = $_GET['search'] ?? '';
$sortBy   = $_GET['sort_by'] ?? 'id';
$sortDir  = (isset($_GET['sort_dir']) && strtoupper($_GET['sort_dir']) == 'ASC') ? 'ASC' : 'DESC';
$usertypeFilter = isset($_GET['usertype']) ? intval($_GET['usertype']) : null;

// 🛡️ Allowed tables
$allowedTables = ['users', 'thesis_topics', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'evaluation_per_panel', 'programs', 'research_titles', 'rubric_groups', 'user_schedules'];

if (!in_array($table, $allowedTables)) {
    echo json_encode(['error' => 'Invalid table specified.']);
    exit;
}

// 🧠 User details and Initialization
$userId = $_SESSION['id'];
$currentUsertype = $_SESSION['usertype'];
$collegeRestrictionClause = '';
$params = [];
$baseQuery = '';
$countQuery = '';
$userCollege = null;

// --- Function to get base query and restrictions based on user type ---

function get_table_query($pdo, $table, $userId, $currentUsertype) {
    $userCollege = null;
    $params = [];
    $collegeRestrictionClause = '';
    $baseQuery = '';
    $countQuery = '';

    $isSuperAdmin = ($currentUsertype === 0 && $userId === 0);
    $isAdmin = (($currentUsertype === 0 || $currentUsertype === 2) && $userId !== 0);

//    echo $isSuperAdmin;

    if ($isSuperAdmin) {
        switch ($table) {
            case 'users':
                $baseQuery = "SELECT users.* FROM users";
                $countQuery = "SELECT COUNT(*) FROM users";
                break;
            case 'teams':
                // Select t.program directly. Remove JOIN to programs for name selection.
                $baseQuery = "SELECT t.id, t.name, rt.title AS research_title, t.program, -- Select t.program
                              GROUP_CONCAT(DISTINCT CASE WHEN u.usertype != 2 THEN CONCAT(u.first_name, ' ', u.last_name, ' (', tm.role, ')') END ORDER BY tm.id SEPARATOR ', ') AS team_members,
                              GROUP_CONCAT(DISTINCT CASE WHEN u.usertype = 2 THEN CONCAT(u.first_name, ' ', u.last_name) END ORDER BY tm.id SEPARATOR ', ') AS adviser
                              FROM teams t
                              LEFT JOIN research_titles rt ON t.id = rt.team_id
                              -- Removed: LEFT JOIN programs p ON t.program = p.id
                              JOIN team_members tm ON t.id = tm.team_id
                              JOIN users u ON tm.user_id = u.id";
                // Count query: Remove programs join if not needed for filtering here
                $countQuery = "SELECT COUNT(DISTINCT t.id) FROM teams t
                               LEFT JOIN research_titles rt ON t.id = rt.team_id
                               -- Removed: LEFT JOIN programs p ON t.program = p.id
                               JOIN team_members tm ON t.id = tm.team_id
                               JOIN users u ON tm.user_id = u.id";
                break;
            case 'programs':
                $baseQuery = "SELECT id, college, department, name, specialization FROM programs";
                $countQuery = "SELECT COUNT(*) FROM programs";
                break;
            case 'thesis_topics':
                $baseQuery = "SELECT * FROM thesis_topics";
                $countQuery = "SELECT COUNT(*) FROM thesis_topics";
                break;
            case 'defense_schedules':
                $baseQuery = "SELECT
                     ds.id,
                     ds.schedule_date,
                     ds.start_time,
                     ds.end_time,
                     ds.room,
                     t.name AS team_name,
                     rt.title AS thesis_title,
                     GROUP_CONCAT(
                         DISTINCT CASE
                             WHEN u_member.usertype != 2 THEN CONCAT(u_member.first_name, ' ', u_member.last_name)
                         END
                         ORDER BY tm.id SEPARATOR ', '
                     ) AS team_members,
                     GROUP_CONCAT(
                         DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name)
                         ORDER BY FIELD(u_panelist.id, ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', '
                     ) AS panelists,
                     (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name)
                      FROM team_members tm_adviser
                      JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id
                      WHERE tm_adviser.team_id = t.id AND u_adviser.usertype = 2
                      ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser
                 FROM defense_schedules ds
                 JOIN teams t ON ds.team_id = t.id
                 LEFT JOIN research_titles rt ON t.id = rt.team_id
                 LEFT JOIN team_members tm ON t.id = tm.team_id
                 LEFT JOIN users u_member ON tm.user_id = u_member.id
                 LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)";
                // Count query needs joins for potential filtering
                $countQuery = "SELECT COUNT(DISTINCT ds.id) FROM defense_schedules ds
                                JOIN teams t ON ds.team_id = t.id
                                LEFT JOIN research_titles rt ON t.id = rt.team_id";
                break;
            case 'rubrics':
                $baseQuery = "SELECT * FROM rubrics";
                $countQuery = "SELECT COUNT(*) FROM rubrics";
                break;
            case 'rubric_groups':
                $baseQuery = "SELECT * FROM rubric_groups";
                $countQuery = "SELECT COUNT(*) FROM rubric_groups";
                break;
            case 'evaluations':
                $baseQuery = "SELECT 
                ep.id AS evaluation_id,
                t.id AS team_id,
                t.name AS team_name,
                e.id AS evaluator_id,
                e.first_name AS evaluator_first_name,
                e.last_name AS evaluator_last_name,
                s.id AS student_id,
                s.first_name AS student_first_name,
                s.last_name AS student_last_name,
                ep.group_score,
                ep.solo_score,
                ep.total_score,
                ep.comments,
                ed.id AS detail_id,
                ed.rubric_id,
                ed.criterion_id,
                ed.score AS detail_score,
                ed.selected_option,
                ed.comment AS detail_comment,
                ed.created_at AS detail_created_at,
                ed.updated_at AS detail_updated_at
            FROM 
                evaluation_per_panel ep
            JOIN 
                teams t ON ep.student_id IN (
                    SELECT user_id FROM team_members WHERE team_id = t.id
                )
            JOIN 
                users e ON ep.evaluator_id = e.id
            JOIN 
                users s ON ep.student_id = s.id
            LEFT JOIN 
                evaluation_details ed ON ep.id = ed.evaluation_id";
                // Count query needs joins for potential filtering
                $countQuery = "SELECT COUNT(ep.id) FROM evaluation_per_panel ep
                               JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                               JOIN teams t ON ds.team_id = t.id
                               LEFT JOIN users e ON ep.evaluator_id = e.id
                               LEFT JOIN users s ON ep.student_id = s.id";
                break;
            case 'evaluation_per_panel':
                $baseQuery = "SELECT * FROM evaluation_per_panel";
                $countQuery = "SELECT COUNT(*) FROM evaluation_per_panel";
                break;
            case 'research_titles':
                $baseQuery = "SELECT rt.id, rt.team_id, rt.title, rt.approved_at, rt.updated_at, rt.created_at, t.name AS team_name
                              FROM research_titles rt
                              LEFT JOIN teams t ON rt.team_id = t.id";
                // Count query needs joins for potential filtering
                $countQuery = "SELECT COUNT(rt.id) FROM research_titles rt
                               LEFT JOIN teams t ON rt.team_id = t.id";
                break;
            case 'requirements':
                $baseQuery = "SELECT * FROM requirements";
                $countQuery = "SELECT COUNT(*) FROM requirements";
                break;
            case 'user_schedules':
                $baseQuery = "SELECT 
                                us.id,
                                us.user_id,
                                u.first_name,
                                u.last_name,
                                us.day_of_week,
                                us.start_time,
                                us.end_time,
                                us.class_name,
                                us.room,
                                us.section,
                                p.name AS program_name,
                                p.specialization
                            FROM user_schedules us
                            LEFT JOIN users u ON us.user_id = u.id
                            LEFT JOIN programs p ON us.program = p.id  -- assuming 'us.program' holds the program ID
                            "; // LEFT JOIN to include NULLs!

                $countQuery = "SELECT COUNT(us.id)
                       FROM user_schedules us
                       LEFT JOIN users u ON us.user_id = u.id"; // Also use LEFT JOIN here

                break;

            default:
                return ['error' => 'Invalid table context for Super Admin.'];
        }
    } elseif ($isAdmin) {
        $userCollege = get_user_college($pdo, $userId);
        if (!$userCollege) {
            return ['error' => 'Admin user college not found or could not be determined.'];
        }
        $params[':college'] = $userCollege;

        switch ($table) {
            case 'users':
                $baseQuery = "SELECT users.* FROM users
                              LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = users.program";
                $collegeRestrictionClause = "WHERE (p.college = :college OR users.id = :user_id)";
                $countQuery = "SELECT COUNT(users.id) FROM users
                               LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = users.program";
                $params[':user_id'] = $userId;
                break;
            case 'teams':
                // Select t.program directly. Keep JOIN programs p for filtering.
                $baseQuery = "SELECT t.id, t.name, rt.title AS research_title, t.program, -- Select t.program
                               GROUP_CONCAT(DISTINCT CASE WHEN u.usertype != 2 THEN CONCAT(u.first_name, ' ', u.last_name, ' (', tm.role, ')') END ORDER BY tm.id SEPARATOR ', ') AS team_members,
                               GROUP_CONCAT(DISTINCT CASE WHEN u.usertype = 2 THEN CONCAT(u.first_name, ' ', u.last_name) END ORDER BY tm.id SEPARATOR ', ') AS adviser
                               FROM teams t
                               LEFT JOIN research_titles rt ON t.id = rt.team_id
                               JOIN team_members tm ON t.id = tm.team_id
                               JOIN users u ON tm.user_id = u.id
                               JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)"; // Keep JOIN for filtering, assuming t.program stores name string
                $collegeRestrictionClause = "WHERE p.college = :college";
                // Count query needs the join for filtering
                $countQuery = "SELECT COUNT(DISTINCT t.id) FROM teams t
                               LEFT JOIN research_titles rt ON t.id = rt.team_id
                               JOIN team_members tm ON t.id = tm.team_id
                               JOIN users u ON tm.user_id = u.id
                               JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)"; // Keep JOIN for filtering
                break;
            case 'programs':
                $baseQuery = "SELECT id, college, department, name, specialization FROM programs";
                $collegeRestrictionClause = "WHERE college = :college";
                $countQuery = "SELECT COUNT(*) FROM programs";
                break;
            case 'thesis_topics':
                $baseQuery = "SELECT tt.* FROM thesis_topics tt JOIN programs p ON tt.program_id = p.id";
                $collegeRestrictionClause = "WHERE p.college = :college";
                $countQuery = "SELECT COUNT(tt.id) FROM thesis_topics tt JOIN programs p ON tt.program_id = p.id";
                break;
            case 'defense_schedules':
                // Modified baseQuery to correctly fetch adviser and ordered panelists
                $baseQuery = "SELECT
                     ds.id,
                     ds.schedule_date,
                     ds.start_time,
                     ds.end_time,
                     ds.room,
                     t.name AS team_name,
                     rt.title AS thesis_title,
                     (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name)
                      FROM team_members tm_adviser
                      JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id
                      WHERE tm_adviser.team_id = t.id AND u_adviser.usertype = 2
                      ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser,
                     GROUP_CONCAT(
                         DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name)
                         ORDER BY FIELD(u_panelist.id, ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', '
                     ) AS panelists
                 FROM defense_schedules ds
                 JOIN teams t ON ds.team_id = t.id
                 LEFT JOIN research_titles rt ON t.id = rt.team_id
                 JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) -- Join programs for college restriction using name string
                 LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)";
                $collegeRestrictionClause = "WHERE p.college = :college";
                // Count query only needs joins necessary for the WHERE clause (college restriction)
                $countQuery = "SELECT COUNT(ds.id) FROM defense_schedules ds
                               JOIN teams t ON ds.team_id = t.id
                               JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                // No need for LEFT JOIN research_titles or panelist joins in count query
                break;
            case 'rubrics':
                $baseQuery = "SELECT DISTINCT r.*
                               FROM rubrics r
                               JOIN rubric_programs rp ON r.id = rp.rubric_id
                               JOIN programs p ON rp.program_name = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                $collegeRestrictionClause = "WHERE p.college = :college";
                $countQuery = "SELECT COUNT(DISTINCT r.id)
                                FROM rubrics r
                                JOIN rubric_programs rp ON r.id = rp.rubric_id
                                JOIN programs p ON rp.program_name = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                break;
            case 'rubric_groups':
                $baseQuery = "SELECT DISTINCT rg.*
                               FROM rubric_groups rg
                               JOIN rubric_group_items rgi ON rg.id = rgi.group_id
                               JOIN rubrics r ON rgi.rubric_id = r.id
                               JOIN rubric_programs rp ON r.id = rp.rubric_id
                               JOIN programs p ON rp.program_name = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                $collegeRestrictionClause = "WHERE p.college = :college";
                $countQuery = "SELECT COUNT(DISTINCT rg.id)
                                FROM rubric_groups rg
                                JOIN rubric_group_items rgi ON rg.id = rgi.group_id
                                JOIN rubrics r ON rgi.rubric_id = r.id
                                JOIN rubric_programs rp ON r.id = rp.rubric_id
                                JOIN programs p ON rp.program_name = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                break;
            case 'evaluations':
                // FIX: Add JOIN programs p ON t.program = CONCAT(p.name, ...) for isAdmin baseQuery
                $baseQuery = "SELECT 
                ep.id AS evaluation_id,
                t.id AS team_id,
                t.name AS team_name,
                e.id AS evaluator_id,
                e.first_name AS evaluator_first_name,
                e.last_name AS evaluator_last_name,
                s.id AS student_id,
                s.first_name AS student_first_name,
                s.last_name AS student_last_name,
                ep.group_score,
                ep.solo_score,
                ep.total_score,
                ep.comments,
                ed.id AS detail_id,
                ed.rubric_id,
                ed.criterion_id,
                ed.score AS detail_score,
                ed.selected_option,
                ed.comment AS detail_comment,
                ed.created_at AS detail_created_at,
                ed.updated_at AS detail_updated_at
            FROM 
                evaluation_per_panel ep
            JOIN 
                teams t ON ep.student_id IN (
                    SELECT user_id FROM team_members WHERE team_id = t.id
                )
            JOIN 
                users e ON ep.evaluator_id = e.id
            JOIN 
                users s ON ep.student_id = s.id
            LEFT JOIN 
                evaluation_details ed ON ep.id = ed.evaluation_id
            JOIN 
                programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                $collegeRestrictionClause = "WHERE p.college = :college";
                $countQuery = "SELECT COUNT(ep.id)
                                FROM evaluation_per_panel ep
                                JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                                JOIN teams t ON ds.team_id = t.id
                                LEFT JOIN users e ON ep.evaluator_id = e.id
                                LEFT JOIN users s ON ep.student_id = s.id
                                JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                break;
            case 'evaluation_per_panel':
                $baseQuery = "SELECT * FROM evaluation_per_panel";
                $countQuery = "SELECT COUNT(*) FROM evaluation_per_panel";
                break;
            case 'requirements':
                $baseQuery = "SELECT * FROM requirements";
                $countQuery = "SELECT COUNT(*) FROM requirements";
                $collegeRestrictionClause = "";
                unset($params[':college']);
                break;
            case 'research_titles':
                $baseQuery = "SELECT rt.id, rt.team_id, rt.title, rt.approved_at, rt.updated_at, rt.created_at, t.name AS team_name
                               FROM research_titles rt
                               LEFT JOIN teams t ON rt.team_id = t.id
                               LEFT JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                $collegeRestrictionClause = "WHERE (p.college = :college OR t.id IS NULL)";
                $countQuery = "SELECT COUNT(rt.id)
                                FROM research_titles rt
                                LEFT JOIN teams t ON rt.team_id = t.id
                                LEFT JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";
                break;
            case 'user_schedules':
                $baseQuery = "SELECT 
                                us.id,
                                us.user_id,
                                u.first_name,
                                u.last_name,
                                us.day_of_week,
                                us.start_time,
                                us.end_time,
                                us.class_name,
                                us.room,
                                us.section,
                                p.name AS program_name,
                                p.specialization
                            FROM user_schedules us
                            LEFT JOIN users u ON us.user_id = u.id
                            LEFT JOIN programs p ON us.program = p.id  -- assuming 'us.program' holds the program ID
                            "; // LEFT JOIN to include NULLs!

                $countQuery = "SELECT COUNT(us.id)
                       FROM user_schedules us
                       LEFT JOIN users u ON us.user_id = u.id"; // Also use LEFT JOIN here

                break;
            default:
                return ['error' => 'Invalid table context for Admin.'];
        }
    } else {
        return ['error' => 'Access denied for this user type.'];
    }

    return [
        'baseQuery' => $baseQuery,
        'countQuery' => $countQuery,
        'collegeRestrictionClause' => $collegeRestrictionClause,
        'params' => $params,
        'userCollege' => $userCollege,
        'isSuperAdmin' => $isSuperAdmin,
        'isAdmin' => $isAdmin,
    ];
}

// --- Main unified logic ---
$queryInfo = get_table_query($pdo, $table, $userId, $currentUsertype);
if (isset($queryInfo['error'])) {
    echo json_encode(['error' => $queryInfo['error']]);
    exit;
}
$baseQuery = $queryInfo['baseQuery'];
$countQuery = $queryInfo['countQuery'];
$collegeRestrictionClause = $queryInfo['collegeRestrictionClause'];
$params = $queryInfo['params'];
$userCollege = $queryInfo['userCollege'];
$isSuperAdmin = $queryInfo['isSuperAdmin'];
$isAdmin = $queryInfo['isAdmin'];

// --- Define user role flags ---
$isAdmin = ($currentUsertype === 0 && $userId !== 0);
$isSuperAdmin = ($currentUsertype === 0 && $userId === 0);

// --- Build Final Queries ---
try {
    $dataQuery = $baseQuery;
    $countFinalQuery = $countQuery; // Start count query from its base
    $conditions = []; // Conditions specific to search/filter

    // --- Apply college restriction ---
    // Append to both data and count queries if applicable
    if (!empty($collegeRestrictionClause)) {
        $dataQuery .= " " . $collegeRestrictionClause;
        // Check if count query already includes the restriction logic (it should for Admin)
        // If not, append it. This ensures count matches filtered data.
        if (strpos($countFinalQuery, 'WHERE') === false) {
            $countFinalQuery .= " " . $collegeRestrictionClause;
        } else {
            // If count query already has WHERE, need to add college restriction with AND
            // Extract the condition part from $collegeRestrictionClause (e.g., "p.college = :college")
            $restrictionCondition = trim(str_ireplace('WHERE', '', $collegeRestrictionClause));
            if (!empty($restrictionCondition)) {
                $countFinalQuery .= " AND (" . $restrictionCondition . ")";
            }
        }
    }

    // Determine WHERE or AND for *additional* conditions (search, filter)
    $whereOrAndAdditionalData = (strpos($dataQuery, 'WHERE') !== false) ? " AND " : " WHERE ";
    $whereOrAndAdditionalCount = (strpos($countFinalQuery, 'WHERE') !== false) ? " AND " : " WHERE ";

    // --- Search condition ---
    if (!empty($search)) {
        $searchCondition = '';
        // Determine alias based on user type and table
        switch ($table) {
            case 'users':
                $searchCondition = "(users.username LIKE :search1 OR users.email LIKE :search2 OR users.first_name LIKE :search3 OR users.last_name LIKE :search4)";
                break;
            case 'teams':
                // Search t.program directly
                $searchCondition = "(t.name LIKE :search1 OR rt.title LIKE :search2 OR t.program LIKE :search3)";
                break;
            case 'thesis_topics':
                $alias = $isAdmin ? 'tt.' : '';
                $searchCondition = "({$alias}topic LIKE :search1 OR {$alias}description LIKE :search2)";
                break;
            case 'research_titles':
                $searchCondition = "(rt.title LIKE :search1 OR t.name LIKE :search2 OR 
                                   CASE WHEN rt.approved_at IS NOT NULL THEN 'approved' ELSE 'pending' END LIKE :search3)";
                break;
            /* COMMENTED OUT - No frontend search UI implemented for these tables
            case 'defense_schedules':
                $searchCondition = "(t.name LIKE :search1 OR rt.title LIKE :search2)";
                break;
            case 'rubrics':
                $alias = $isAdmin ? 'r.' : '';
                $searchCondition = "({$alias}name LIKE :search1 OR {$alias}description LIKE :search2 OR {$alias}defense_type LIKE :search3)";
                break;
            case 'rubric_groups':
                $alias = $isAdmin ? 'rg.' : '';
                $searchCondition = "({$alias}name LIKE :search1 OR {$alias}description LIKE :search2)";
                break;
            case 'requirements':
                $searchCondition = "(name LIKE :search1 OR description LIKE :search2)";
                break;
            case 'evaluations':
                $searchCondition = "(t.name LIKE :search1 OR CONCAT(e.first_name, ' ', e.last_name) LIKE :search2 OR CONCAT(s.first_name, ' ', s.last_name) LIKE :search3)";
                break;
            case 'evaluation_per_panel':
                $alias = $isAdmin ? 'ep.' : '';
                $searchCondition = "({$alias}comments LIKE :search1)";
                if ($isSuperAdmin) $searchCondition = "(comments LIKE :search1)";
                break;
            */
            case 'programs':
                $searchCondition = "(college LIKE :search1 OR department LIKE :search2 OR name LIKE :search3 OR specialization LIKE :search4)";
                break;
            case 'research_titles':
                $searchCondition = "(rt.title LIKE :search1 OR t.name LIKE :search2 OR 
                                   CASE WHEN rt.approved_at IS NOT NULL THEN 'approved' ELSE 'pending' END LIKE :search3)";
                break;
        }
        if (!empty($searchCondition)) {
            $conditions[] = $searchCondition;
            // Set multiple search parameters - count how many :search parameters are used
            $searchCount = substr_count($searchCondition, ':search');
            for ($i = 1; $i <= $searchCount; $i++) {
                $params[":search{$i}"] = "%$search%";
            }
        }
    }

    // --- Usertype filter condition ---
    if ($table === 'users' && $usertypeFilter !== null) {
        $conditions[] = "users.usertype = :usertypeFilter";
        $params[':usertypeFilter'] = $usertypeFilter;
    }

    // --- Schedule-specific filter conditions ---
    if ($table === 'user_schedules') {
        $programFilter = $_GET['program'] ?? '';
        $sectionFilter = $_GET['section'] ?? '';
        $instructorFilter = $_GET['instructor'] ?? '';

        if (!empty($programFilter)) {
            $conditions[] = "us.program = :program";
            $params[':program'] = $programFilter;
        }
        if (!empty($sectionFilter)) {
            // Use us.section for filtering (user_schedules.section)
            $conditions[] = "us.section = :section";
            $params[':section'] = $sectionFilter;
        }
        if (!empty($instructorFilter)) {
            $conditions[] = "us.user_id = :instructor";
            $params[':instructor'] = $instructorFilter;
        }
    }

    // --- Programs-specific filter conditions ---
    if ($table === 'programs') {
        $collegeFilter = $_GET['college'] ?? '';

        if (!empty($collegeFilter)) {
            $conditions[] = "college = :collegeFilter";
            $params[':collegeFilter'] = $collegeFilter;
        }
    }

    // --- Append additional conditions to both queries ---
    if (!empty($conditions)) {
        $dataQuery .= $whereOrAndAdditionalData . implode(' AND ', $conditions);
        $countFinalQuery .= $whereOrAndAdditionalCount . implode(' AND ', $conditions);
    }

    // --- GROUP BY for specific tables (only for data query) ---
    if ($table === 'teams') {
        if (strpos($dataQuery, 'GROUP BY t.id') === false) {
            $dataQuery .= " GROUP BY t.id";
        }
    } elseif ($table === 'defense_schedules') {
        if (strpos($dataQuery, 'GROUP BY ds.id') === false) {
            $dataQuery .= " GROUP BY ds.id"; // Group by schedule ID for correct aggregation
        }
    }
    // DISTINCT for rubrics/rubric_groups is handled in the base query for Admins

    // --- Sorting (only for data query) ---
    $allowedSortColumns = [
        'users' => ['id', 'username', 'email', 'first_name', 'last_name', 'usertype', 'program'],
        'teams' => ['id', 'name', 'research_title', 'program', 'adviser'], // Added 'program'
        'defense_schedules' => ['id', 'schedule_date', 'start_time', 'end_time', 'room', 'team_name', 'thesis_title', 'adviser', 'panelists'], // Added adviser/panelists
        'rubrics' => ['id', 'name', 'description', 'rubric_type', 'defense_type', 'is_active', 'created_at'],
        'requirements' => ['id', 'name', 'description'],
        'evaluations' => ['id', 'team_name', 'evaluator_first_name', 'student_first_name', 'group_score', 'solo_score', 'total_score', 'created_at'],
        'evaluation_per_panel' => ['id', 'defense_schedule_id', 'evaluator_id', 'student_id', 'group_score', 'solo_score', 'total_score', 'comments', 'created_at'],
        'programs' => ['id', 'college', 'department', 'name', 'specialization'],
        'thesis_topics' => ['id', 'topic', 'description', 'program_id'],
        'research_titles' => ['id', 'title', 'description', 'team_name', 'approved_at', 'updated_at', 'created_at', 'status'],
        'rubric_groups' => ['id', 'name', 'description', 'created_at'],
    ];

    $safeSortBy = 'id'; // Default safe sort
    $sortPrefix = '';

    // Determine the correct alias/prefix for sorting
    if ($isAdmin) {
        switch ($table) {
            case 'users':
                $sortPrefix = 'users.';
                break;
            case 'teams':
                $sortPrefix = 't.';
                break;
            case 'research_titles':
                $sortPrefix = 'rt.';
                break;
            case 'defense_schedules':
                $sortPrefix = 'ds.';
                break;
            case 'rubrics':
                $sortPrefix = 'r.';
                break;
            case 'rubric_groups':
                $sortPrefix = 'rg.';
                break;
            case 'evaluations':
                $sortPrefix = 'ep.';
                break;
            case 'evaluation_per_panel':
                $sortPrefix = 'ep.';
                break;
            case 'thesis_topics':
                $sortPrefix = 'tt.';
                break;
        }
    } elseif ($isSuperAdmin) {
        switch ($table) {
            case 'users':
                $sortPrefix = 'users.';
                break;
            case 'teams':
                $sortPrefix = 't.';
                break;
            case 'research_titles':
                $sortPrefix = 'rt.';
                break;
            case 'defense_schedules':
                $sortPrefix = 'ds.';
                break;
            case 'evaluations':
                $sortPrefix = 'ep.';
                break;
                // No prefix needed for simple SELECT * cases
        }
    }

    // Validate and apply sort column
    if (isset($allowedSortColumns[$table]) && in_array($sortBy, $allowedSortColumns[$table])) {
        if ($table === 'evaluations') {
            if ($sortBy == 'team_name') $safeSortBy = 't.name';
            elseif ($sortBy == 'evaluator_first_name') $safeSortBy = 'e.first_name';
            elseif ($sortBy == 'student_first_name') $safeSortBy = 's.first_name';
            else $safeSortBy = $sortPrefix . $sortBy;
        } elseif ($table === 'teams' && $sortBy == 'research_title') {
            $safeSortBy = 'rt.title';
        } elseif ($table === 'teams' && $sortBy == 'program') { // Sort by t.program
            $safeSortBy = 't.program';
        } elseif ($table === 'teams' && $sortBy == 'adviser') {
            $safeSortBy = $sortPrefix . 'id'; // Avoid sorting by GROUP_CONCAT
        } elseif ($table === 'defense_schedules') { // Added handling for adviser/panelists sort
            if ($sortBy == 'team_name') $safeSortBy = 't.name';
            elseif ($sortBy == 'thesis_title') $safeSortBy = 'rt.title';
            elseif ($sortBy == 'adviser') $safeSortBy = 'adviser'; // Sort by alias
            elseif ($sortBy == 'panelists') $safeSortBy = 'panelists'; // Sort by alias (might be slow)
            else $safeSortBy = $sortPrefix . $sortBy;
        } elseif ($table === 'research_titles' && $sortBy == 'team_name') {
            $safeSortBy = 't.name';
        } else {
            $safeSortBy = $sortPrefix . $sortBy;
        }
    } else {
        $defaultSortCol = 'id';
        switch ($table) {
            case 'defense_schedules':
                $defaultSortCol = 'schedule_date';
                break;
            case 'evaluations':
                $defaultSortCol = 'created_at';
                break;
            case 'evaluation_per_panel':
                $defaultSortCol = 'created_at';
                break;
        }
        $safeSortBy = $sortPrefix . $defaultSortCol;
    }
    $safeSortBy = rtrim($safeSortBy, '.');
    if (empty($safeSortBy)) $safeSortBy = 'id'; // Ensure there's always a sort column

    // Apply LIMIT only for non-programs tables
    if ($table !== 'programs') {
        $dataQuery .= " ORDER BY $safeSortBy $sortDir LIMIT $offset, $perPage";
    } else {
        $dataQuery .= " ORDER BY $safeSortBy $sortDir";
    }

    // --- Execute Count Query ---
    $countParams = [];
    // Filter parameters needed for the count query
    foreach ($params as $key => $value) {
        if (strpos($countFinalQuery, $key) !== false) {
            $countParams[$key] = $value;
        }
    }
    $countStmt = $pdo->prepare($countFinalQuery);
    $countStmt->execute($countParams);
    $totalRows = $countStmt->fetchColumn();

    // --- Execute Data Query ---
    $dataStmt = $pdo->prepare($dataQuery);
    // Filter parameters needed for the data query (should be all of them usually)
    $dataParams = [];
    foreach ($params as $key => $value) {
        if (strpos($dataQuery, $key) !== false) {
            $dataParams[$key] = $value;
        }
    }
    // Add limit/offset params if using prepared statements for them (currently directly embedded)
    // $dataParams[':limit'] = $perPage;
    // $dataParams[':offset'] = $offset;
    $dataStmt->execute($dataParams);
    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        'data' => $data,
        'page' => $page,
        'per_page' => $perPage,
        'total_rows' => (int)$totalRows,
        'total_pages' => ceil($totalRows / $perPage),
        // --- Uncomment below lines for debugging ---
        // 'debug_user_type' => $currentUsertype,
        // 'debug_user_id' => $userId,
        // 'debug_user_college' => $userCollege,
        // 'debug_data_query' => $dataQuery,
        // 'debug_data_params' => $dataParams,
        // 'debug_count_query' => $countFinalQuery,
        // 'debug_count_params' => $countParams,
    ]);
} catch (PDOException $e) {
    error_log('Database error in get_table.php: ' . $e->getMessage());
    error_log('Failing Data Query: ' . ($dataQuery ?? 'N/A'));
    error_log('Failing Data Params: ' . json_encode($dataParams ?? $params ?? []));
    error_log('Failing Count Query: ' . ($countFinalQuery ?? $countQuery ?? 'N/A'));
    error_log('Failing Count Params: ' . json_encode($countParams ?? []));
    echo json_encode([
        'error' => 'Database error occurred. Please check server logs.',
        'debug' => [
            'exception' => $e->getMessage(),
            'data_query' => $dataQuery ?? 'N/A',
            'data_params' => $dataParams ?? $params ?? [],
            'count_query' => $countFinalQuery ?? $countQuery ?? 'N/A',
            'count_params' => $countParams ?? []
        ]
    ]);
} catch (Exception $e) {
    error_log('General error in get_table.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An unexpected error occurred. Please check server logs.']);
}