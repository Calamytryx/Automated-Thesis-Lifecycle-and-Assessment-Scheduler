<?php
session_start();
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

// Check if user is faculty or program chair
if (!isset($_SESSION['id']) || !in_array((int)($_SESSION['usertype'] ?? -1), [0, 2], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$facultyId = (int)$_SESSION['id'];

try {
    // Get sections accessible to this faculty member
    require_once '../../dashboard/includes/section_access.php';
    $accessibleSections = getProfessorSections($pdo, $facultyId);
    
    if (empty($accessibleSections)) {
        echo json_encode(['success' => false, 'message' => 'No sections assigned']);
        exit;
    }
    
    // Fetch students with their evaluation scores grouped by section and team
    $placeholders = implode(',', array_fill(0, count($accessibleSections), '?'));
    
    $query = "
        SELECT 
            u.id,
            u.username as student_number,
            u.first_name,
            u.last_name,
            u.section,
            t.id as team_id,
            t.name as team_name,
            rt.title as research_title,
            -- Get latest defense schedule
            (
                SELECT ds.id
                FROM defense_schedules ds
                WHERE ds.team_id = t.id
                ORDER BY ds.schedule_date DESC
                LIMIT 1
            ) as latest_defense_id,
            -- Get average score from evaluations (latest defense only)
            (
                SELECT AVG(ep.total_score)
                FROM evaluation_per_panel ep
                WHERE ep.defense_schedule_id = (
                    SELECT ds2.id FROM defense_schedules ds2 
                    WHERE ds2.team_id = t.id 
                    ORDER BY ds2.schedule_date DESC LIMIT 1
                ) AND ep.student_id = u.id
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
        ORDER BY u.section ASC, t.name ASC, u.last_name ASC, u.first_name ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($accessibleSections);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get panelist grades and average scores (group, individual, total) for each student
    foreach ($students as &$student) {
        if ($student['latest_defense_id']) {
            // Get rubric group and pass thresholds for this defense
            $thresholdQuery = "
                SELECT 
                    MIN(r.pass_threshold_1) as pass_threshold_1,
                    MIN(r.pass_threshold_2) as pass_threshold_2,
                    MIN(r.pass_threshold_3) as pass_threshold_3
                FROM defense_schedules ds
                JOIN rubric_groups rg ON rg.defense_type = ds.defense_type
                JOIN rubric_group_items rgi ON rgi.group_id = rg.id
                JOIN rubrics r ON rgi.rubric_id = r.id
                WHERE ds.id = ? AND r.rubric_type = 'passfail'
            ";
            $thresholdStmt = $pdo->prepare($thresholdQuery);
            $thresholdStmt->execute([$student['latest_defense_id']]);
            $thresholds = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
            
            // Set pass thresholds (use defaults if not found)
            $student['pass_threshold_1'] = $thresholds['pass_threshold_1'] ?? 81;
            $student['pass_threshold_2'] = $thresholds['pass_threshold_2'] ?? 75;
            $student['pass_threshold_3'] = $thresholds['pass_threshold_3'] ?? 65;
            
            $gradeQuery = "
                SELECT 
                    ep.evaluator_id,
                    ep.group_score,
                    ep.solo_score,
                    ep.total_score,
                    CONCAT(u.first_name, ' ', u.last_name) as panelist_name
                FROM evaluation_per_panel ep
                JOIN users u ON ep.evaluator_id = u.id
                WHERE ep.defense_schedule_id = ? AND ep.student_id = ?
                ORDER BY ep.evaluator_id ASC
            ";
            $gradeStmt = $pdo->prepare($gradeQuery);
            $gradeStmt->execute([$student['latest_defense_id'], $student['id']]);
            $student['panelist_grades'] = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate average group_score, solo_score, and total_score
            if (!empty($student['panelist_grades'])) {
                $groupScores = array_filter(array_column($student['panelist_grades'], 'group_score'), function($val) {
                    return $val !== null;
                });
                $soloScores = array_filter(array_column($student['panelist_grades'], 'solo_score'), function($val) {
                    return $val !== null;
                });
                $totalScores = array_filter(array_column($student['panelist_grades'], 'total_score'), function($val) {
                    return $val !== null;
                });
                
                $student['avg_group_score'] = !empty($groupScores) ? round(array_sum($groupScores) / count($groupScores), 2) : null;
                $student['avg_solo_score'] = !empty($soloScores) ? round(array_sum($soloScores) / count($soloScores), 2) : null;
                $student['avg_total_score'] = !empty($totalScores) ? round(array_sum($totalScores) / count($totalScores), 2) : null;
            } else {
                $student['avg_group_score'] = null;
                $student['avg_solo_score'] = null;
                $student['avg_total_score'] = null;
            }
        } else {
            $student['panelist_grades'] = [];
            $student['avg_group_score'] = null;
            $student['avg_solo_score'] = null;
            $student['avg_total_score'] = null;
            // Default thresholds for students without defenses
            $student['pass_threshold_1'] = 81;
            $student['pass_threshold_2'] = 75;
            $student['pass_threshold_3'] = 65;
        }
    }
    
    // Group students by section and then by team
    $recordsBySection = [];
    foreach ($students as $student) {
        $section = $student['section'] ?: 'No Section';
        $teamId = $student['team_id'] ?: 'no_team';
        
        if (!isset($recordsBySection[$section])) {
            $recordsBySection[$section] = [];
        }
        if (!isset($recordsBySection[$section][$teamId])) {
            $recordsBySection[$section][$teamId] = [
                'team_name' => $student['team_name'] ?: 'No Team',
                'research_title' => $student['research_title'] ?: 'No Title',
                'students' => []
            ];
        }
        $recordsBySection[$section][$teamId]['students'][] = $student;
    }
    
    // Sort sections alphabetically
    ksort($recordsBySection);
    
    // Get academic year for this professor
    $ayStmt = $pdo->prepare("
        SELECT academic_year 
        FROM section_professors 
        WHERE professor_id = ? AND status = 'active' AND academic_year IS NOT NULL 
        LIMIT 1
    ");
    $ayStmt->execute([$facultyId]);
    $ayRow = $ayStmt->fetch(PDO::FETCH_ASSOC);
    $academicYear = $ayRow ? $ayRow['academic_year'] : null;
    
    echo json_encode([
        'success' => true,
        'sections' => $recordsBySection,
        'total_students' => count($students),
        'academic_year' => $academicYear
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_class_record.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading class records. Please try again later.'
    ]);
}
?>
