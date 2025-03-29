<?php
header('Content-Type: application/json');

try {
    require_once '../../assets/setup/db.inc.php';

    // Trim the program value so "it" is not treated as empty.
    $program = isset($_POST['program']) ? trim($_POST['program']) : '';
    
    if (empty($program)) {
        // Count all teams if no program is selected
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teams");
        $stmt->execute();
    } else {
        // Count teams for the selected program
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teams WHERE program = ?");
        $stmt->execute([$program]);
    }
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'count' => $count,
        'program' => $program
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
