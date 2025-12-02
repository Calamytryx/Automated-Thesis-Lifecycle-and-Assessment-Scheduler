<?php
session_start();
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

// Check if user is faculty
if (!isset($_SESSION['id']) || $_SESSION['usertype'] != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$facultyId = $_SESSION['id'];

try {
    // Get sections accessible to this faculty member
    require_once '../../dashboard/includes/section_access.php';
    $accessibleSections = getProfessorSections($pdo, $facultyId);
    
    if (empty($accessibleSections)) {
        echo json_encode(['success' => false, 'message' => 'No sections assigned']);
        exit;
    }
    
    // Fetch students with their evaluation scores grouped by section
    $placeholders = implode(',', array_fill(0, count($accessibleSections), '?'));
    
    $query = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.section,
            t.id as team_id,
            t.name as team_name,
            rt.title as research_title,
            -- Get average score from evaluations
            (
                SELECT AVG(ep.total_score)
                FROM evaluation_per_panel ep
                JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                WHERE ds.team_id = t.id AND ep.student_id = u.id
            ) as avg_score,
            -- Count of evaluations
            (
                SELECT COUNT(*)
                FROM evaluation_per_panel ep
                JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                WHERE ds.team_id = t.id AND ep.student_id = u.id
            ) as evaluation_count,
            -- Get latest defense type
            (
                SELECT ds.defense_type
                FROM defense_schedules ds
                WHERE ds.team_id = t.id
                ORDER BY ds.schedule_date DESC
                LIMIT 1
            ) as latest_defense_type
        FROM users u
        JOIN team_members tm ON u.id = tm.user_id
        JOIN teams t ON tm.team_id = t.id
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        WHERE u.usertype = 1 
        AND u.section IN ($placeholders)
        ORDER BY u.section ASC, u.last_name ASC, u.first_name ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($accessibleSections);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group students by section
    $recordsBySection = [];
    foreach ($students as $student) {
        $section = $student['section'] ?: 'No Section';
        if (!isset($recordsBySection[$section])) {
            $recordsBySection[$section] = [];
        }
        $recordsBySection[$section][] = $student;
    }
    
    // Sort sections alphabetically
    ksort($recordsBySection);
    
    echo json_encode([
        'success' => true,
        'sections' => $recordsBySection,
        'total_students' => count($students)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_class_record.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading class records: ' . $e->getMessage()
    ]);
}
?>
