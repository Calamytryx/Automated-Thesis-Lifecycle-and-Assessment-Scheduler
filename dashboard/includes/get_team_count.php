<?php
session_start(); // Need session for access control
header('Content-Type: application/json');

try {
    require_once '../../assets/setup/db.inc.php';
    require_once 'section_access.php';
    require_once '../../assets/includes/auth_functions.php';

    // Get section parameter (changed from program)
    $section = isset($_POST['section']) ? trim($_POST['section']) : '';
    
    // Get current user for access control
    $currentUserId = $_SESSION['id'] ?? 0;
    $currentUsertype = $_SESSION['usertype'] ?? -1;
    
    // DEBUG: Log what we're receiving
    error_log("get_team_count.php - Section received: '" . $section . "', User: " . $currentUserId . ", Type: " . $currentUsertype);
    
    // Build the base query - count teams that have members in specific sections
    $baseQuery = "SELECT COUNT(DISTINCT t.id) as total 
                  FROM teams t
                  JOIN team_members tm ON t.id = tm.team_id
                  JOIN users u ON tm.user_id = u.id
                  WHERE 1=1";
    
    $params = [];
    
    // Apply section filter if specified
    if (!empty($section)) {
        $baseQuery .= " AND u.section = ?";
        $params[] = $section;
    } else {
        // No specific section - apply access control to get all accessible sections
        
        // Admin (id=0): All sections
        if ($currentUserId === 0 && $currentUsertype === 0) {
            // No additional filter - see all teams
        }
        // Program Chair (usertype=0, id!=0): College sections only
        elseif ($currentUsertype === 0 && $currentUserId !== 0) {
            $userCollege = get_user_college($pdo, $currentUserId);
            if ($userCollege) {
                $baseQuery .= " AND EXISTS (
                    SELECT 1 FROM programs p 
                    WHERE CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                    AND p.college = ?
                )";
                $params[] = $userCollege;
            } else {
                // No college found - return 0
                echo json_encode(['success' => true, 'count' => 0, 'section' => $section]);
                exit;
            }
        }
        // Faculty (usertype=2): Assigned sections only
        elseif ($currentUsertype === 2) {
            $sections = getProfessorSections($pdo, $currentUserId);
            if (!empty($sections)) {
                $placeholders = implode(',', array_fill(0, count($sections), '?'));
                $baseQuery .= " AND u.section IN ($placeholders)";
                $params = array_merge($params, $sections);
            } else {
                // No sections assigned - return 0
                echo json_encode(['success' => true, 'count' => 0, 'section' => $section]);
                exit;
            }
        }
    }
    
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['total'] ?? 0;
    
    // DEBUG: Log the query and result
    error_log("get_team_count.php - Query: " . $baseQuery);
    error_log("get_team_count.php - Params: " . print_r($params, true));
    error_log("get_team_count.php - Count result: " . $count);
    
    echo json_encode([
        'success' => true,
        'count' => $count,
        'section' => $section
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_team_count.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
