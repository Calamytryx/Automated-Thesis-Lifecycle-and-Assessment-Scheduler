<?php
// Prevent PHP errors from breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Include database connection
    include '../../assets/setup/db.inc.php';
    
    // Check if PDO exists
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }
    
    // Get search query
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if (empty($searchQuery)) {
        echo json_encode(['success' => false, 'message' => 'Search query is required']);
        exit;
    }
    
    // Get all requirements for progress calculation
    $requirementsStmt = $pdo->prepare("SELECT id, name FROM requirements");
    $requirementsStmt->execute();
    $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);
    $totalRequirements = count($requirements);
    
    // Search teams by name (case-insensitive)
    $searchParam = '%' . $searchQuery . '%';
    $teamsStmt = $pdo->prepare("
        SELECT id, name, created_at
        FROM teams 
        WHERE name LIKE ? 
        ORDER BY name ASC
    ");
    $teamsStmt->execute([$searchParam]);
    $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($teams)) {
        echo json_encode([
            'success' => true, 
            'message' => 'No teams found matching your search.', 
            'teams' => []
        ]);
        exit;
    }
    
    $teamResults = [];
    
    foreach ($teams as $team) {
        // Get team requirements completion
        $completedStmt = $pdo->prepare("
            SELECT COUNT(*) as completed_count
            FROM team_requirements tr
            WHERE tr.team_id = ? AND tr.status IN ('approved', 'submitted')
        ");
        $completedStmt->execute([$team['id']]);
        $completedCount = $completedStmt->fetchColumn();
        
        // Get pending requirements
        $pendingStmt = $pdo->prepare("
            SELECT COUNT(*) as pending_count
            FROM team_requirements tr
            WHERE tr.team_id = ? AND tr.status = 'pending'
        ");
        $pendingStmt->execute([$team['id']]);
        $pendingCount = $pendingStmt->fetchColumn();
        
        // Calculate remaining requirements
        $remainingCount = $totalRequirements - $completedCount - $pendingCount;
        
        // Calculate completion percentage
        $completionPercentage = $totalRequirements > 0 
            ? round(($completedCount / $totalRequirements) * 100) 
            : 0;
        
        // Get adviser name (team member with usertype = 2)
        $adviserStmt = $pdo->prepare("
            SELECT CONCAT(u.first_name, ' ', u.last_name) as name 
            FROM team_members tm
            JOIN users u ON tm.user_id = u.id
            WHERE tm.team_id = ? AND u.usertype = 2
            ORDER BY tm.id ASC LIMIT 1
        ");
        $adviserStmt->execute([$team['id']]);
        $adviser = $adviserStmt->fetch(PDO::FETCH_ASSOC);
        $adviserName = $adviser ? $adviser['name'] : 'No adviser assigned';
        
        // Get research title if available
        $researchTitle = 'No approved title';
        $titleStmt = $pdo->prepare("
            SELECT title 
            FROM research_titles 
            WHERE team_id = ? AND approved_at IS NOT NULL 
            ORDER BY approved_at DESC LIMIT 1
        ");
        $titleStmt->execute([$team['id']]);
        $title = $titleStmt->fetch(PDO::FETCH_ASSOC);
        if ($title) {
            $researchTitle = $title['title'];
        }
        
        // Determine status category
        $statusCategory = 'none';
        if ($completionPercentage == 100) {
            $statusCategory = 'completed';
        } elseif ($completionPercentage > 0) {
            $statusCategory = 'partial';
        }
        
        // Get detailed requirement breakdown
        $requirementDetails = [];
        foreach ($requirements as $requirement) {
            $reqStatusStmt = $pdo->prepare("
                SELECT status 
                FROM team_requirements 
                WHERE team_id = ? AND requirement_id = ?
            ");
            $reqStatusStmt->execute([$team['id'], $requirement['id']]);
            $reqStatus = $reqStatusStmt->fetchColumn();
            
            $requirementDetails[] = [
                'id' => $requirement['id'],
                'name' => $requirement['name'],
                'status' => $reqStatus ?: 'missing'
            ];
        }
        
        $teamResults[] = [
            'id' => $team['id'],
            'name' => $team['name'],
            'adviser' => $adviserName,
            'research_title' => $researchTitle,
            'completed_requirements' => $completedCount,
            'pending_requirements' => $pendingCount,
            'remaining_requirements' => $remainingCount,
            'total_requirements' => $totalRequirements,
            'completion_percentage' => $completionPercentage,
            'status_category' => $statusCategory,
            'requirement_details' => $requirementDetails,
            'created_at' => $team['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'teams' => $teamResults,
        'total_found' => count($teamResults),
        'search_query' => $searchQuery
    ]);
    
} catch (Exception $e) {
    // Return any errors as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
