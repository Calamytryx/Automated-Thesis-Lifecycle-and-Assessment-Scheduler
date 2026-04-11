<?php
/**
 * Specialization Pool Management API
 * Handles CRUD operations for specialization pool
 */

session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/auth_functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['id'];
$usertype = $_SESSION['usertype'];
$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

function sanitizeSpecializationText($value, $maxLength = 255) {
    $cleaned = trim((string)($value ?? ''));
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);
    if (mb_strlen($cleaned) > $maxLength) {
        $cleaned = mb_substr($cleaned, 0, $maxLength);
    }
    return $cleaned;
}

function containsHtmlTags($value) {
    return preg_match('/<[^>]*>/', (string)$value) === 1;
}

function containsEmoji($value) {
    return preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', (string)$value) === 1;
}

function toPositiveInt($value) {
    $filtered = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $filtered === false ? null : (int)$filtered;
}

try {
    switch ($action) {
        case 'get_all':
            getAllSpecializations($pdo);
            break;
        
        case 'get_by_id':
            getSpecializationById($pdo, toPositiveInt($_GET['id'] ?? null));
            break;
        
        case 'add':
            // Only admins and program chairs can add specializations
            if ($usertype !== 0) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            addSpecialization($pdo, $_POST, $userId);
            break;
        
        case 'update':
            // Only admins can update specializations
            if ($usertype !== 0) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            updateSpecialization($pdo, $_POST, $userId);
            break;
        
        case 'delete':
            // Only admins can delete specializations
            if ($usertype !== 0) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            deleteSpecialization($pdo, toPositiveInt($_POST['id'] ?? null));
            break;
        
        case 'toggle_status':
            // Only admins can toggle status
            if ($usertype !== 0) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            toggleStatus($pdo, toPositiveInt($_POST['id'] ?? null));
            break;
        
        case 'get_active':
            // Anyone can view active specializations
            getActiveSpecializations($pdo);
            break;
        
        case 'get_by_college':
            // Get specializations by college
            getSpecializationsByCollege($pdo, sanitizeSpecializationText($_GET['college'] ?? '', 150));
            break;
        
        case 'get_colleges':
            // Get colleges from programs table
            getCollegesFromPrograms($pdo);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Specialization API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Get all specializations
 */
function getAllSpecializations($pdo) {
    $userId = $_SESSION['id'] ?? 0;
    $usertype = $_SESSION['usertype'] ?? -1;
    
    // Apply college restriction for program chairs (usertype=0, id!=0)
    $query = "
        SELECT 
            sp.*,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name
        FROM specialization_pool sp
        LEFT JOIN users u ON sp.created_by = u.id
    ";
    
    $params = [];
    
    // Program chairs can only see their college's specializations
    if ($usertype === 0 && $userId !== 0) {
        require_once __DIR__ . '/../../assets/includes/auth_functions.php';
        $userCollege = get_user_college($pdo, $userId);
        if ($userCollege) {
            $query .= " WHERE sp.college = ?";
            $params[] = $userCollege;
        }
    }
    
    $query .= " ORDER BY sp.college, sp.department, sp.name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $specializations,
        'count' => count($specializations)
    ]);
}

/**
 * Get specialization by ID
 */
function getSpecializationById($pdo, $id) {
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            sp.*,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name
        FROM specialization_pool sp
        LEFT JOIN users u ON sp.created_by = u.id
        WHERE sp.id = ?
    ");
    $stmt->execute([$id]);
    $specialization = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($specialization) {
        echo json_encode(['success' => true, 'data' => $specialization]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Specialization not found']);
    }
}

/**
 * Add new specialization
 */
function addSpecialization($pdo, $data, $userId) {
    $rawName = $data['name'] ?? '';
    $rawDescription = $data['description'] ?? '';
    $rawDepartment = $data['department'] ?? '';
    $rawCollege = $data['college'] ?? '';

    if (containsHtmlTags($rawName) || containsEmoji($rawName)) {
        echo json_encode(['success' => false, 'message' => 'Invalid specialization name']);
        return;
    }
    if (containsHtmlTags($rawDescription) || containsEmoji($rawDescription)) {
        echo json_encode(['success' => false, 'message' => 'Invalid specialization description']);
        return;
    }
    if (containsHtmlTags($rawDepartment) || containsEmoji($rawDepartment)) {
        echo json_encode(['success' => false, 'message' => 'Invalid department/program value']);
        return;
    }
    if (containsHtmlTags($rawCollege) || containsEmoji($rawCollege)) {
        echo json_encode(['success' => false, 'message' => 'Invalid college value']);
        return;
    }

    $name = sanitizeSpecializationText($rawName, 150);
    $description = sanitizeSpecializationText($rawDescription, 500);
    $department = sanitizeSpecializationText($rawDepartment, 150);
    $college = sanitizeSpecializationText($rawCollege, 150);
    $isActive = isset($data['is_active']) && (int)$data['is_active'] === 0 ? 0 : 1;
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        return;
    }
    
    // For program chairs (id != 0), enforce their college
    $usertype = $_SESSION['usertype'] ?? -1;
    if ($usertype === 0 && $userId !== 0) {
        require_once __DIR__ . '/../../assets/includes/auth_functions.php';
        $userCollege = get_user_college($pdo, $userId);
        if (!$userCollege) {
            echo json_encode(['success' => false, 'message' => 'Your college could not be determined']);
            return;
        }
        if ($college !== $userCollege) {
            echo json_encode(['success' => false, 'message' => 'You can only add specializations for your college']);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO specialization_pool 
            (name, description, department, college, is_active, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $department, $college, $isActive, $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Specialization added successfully',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Specialization already exists']);
        } else {
            throw $e;
        }
    }
}

/**
 * Update specialization
 */
function updateSpecialization($pdo, $data, $userId) {
    $id = toPositiveInt($data['id'] ?? null);
    $rawName = $data['name'] ?? '';
    $rawDescription = $data['description'] ?? '';
    $rawDepartment = $data['department'] ?? '';
    $rawCollege = $data['college'] ?? '';

    if (containsHtmlTags($rawName) || containsEmoji($rawName)) {
        echo json_encode(['success' => false, 'message' => 'Invalid specialization name']);
        return;
    }
    if (containsHtmlTags($rawDescription) || containsEmoji($rawDescription)) {
        echo json_encode(['success' => false, 'message' => 'Invalid specialization description']);
        return;
    }
    if (containsHtmlTags($rawDepartment) || containsEmoji($rawDepartment)) {
        echo json_encode(['success' => false, 'message' => 'Invalid department/program value']);
        return;
    }
    if (containsHtmlTags($rawCollege) || containsEmoji($rawCollege)) {
        echo json_encode(['success' => false, 'message' => 'Invalid college value']);
        return;
    }

    $name = sanitizeSpecializationText($rawName, 150);
    $description = sanitizeSpecializationText($rawDescription, 500);
    $department = sanitizeSpecializationText($rawDepartment, 150);
    $college = sanitizeSpecializationText($rawCollege, 150);
    $isActive = isset($data['is_active']) && (int)$data['is_active'] === 0 ? 0 : 1;
    
    if (!$id || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'ID and name are required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE specialization_pool 
            SET name = ?, description = ?, department = ?, college = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $department, $college, $isActive, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Specialization updated successfully'
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Specialization name already exists']);
        } else {
            throw $e;
        }
    }
}

/**
 * Delete specialization
 */
function deleteSpecialization($pdo, $id) {
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    // Check if specialization is in use
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM (
            SELECT id FROM user_specializations WHERE specialization_id = ?
            UNION ALL
            SELECT id FROM team_specializations WHERE specialization_id = ?
        ) as combined
    ");
    $stmt->execute([$id, $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete specialization that is currently assigned to users or teams'
        ]);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM specialization_pool WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Specialization deleted successfully'
    ]);
}

/**
 * Toggle specialization status
 */
function toggleStatus($pdo, $id) {
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE specialization_pool SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);
}

/**
 * Get active specializations only
 */
function getActiveSpecializations($pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM specialization_pool 
        WHERE is_active = 1 
        ORDER BY college, department, name
    ");
    $stmt->execute();
    $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $specializations
    ]);
}

/**
 * Get specializations by college
 */
function getSpecializationsByCollege($pdo, $college) {
    if (empty($college)) {
        echo json_encode(['success' => false, 'message' => 'College required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM specialization_pool 
        WHERE college = ? AND is_active = 1 
        ORDER BY department, name
    ");
    $stmt->execute([$college]);
    $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $specializations
    ]);
}

/**
 * Get distinct colleges from programs table
 */
function getCollegesFromPrograms($pdo) {
    $userId = $_SESSION['id'] ?? 0;
    $usertype = $_SESSION['usertype'] ?? -1;
    
    $query = "SELECT DISTINCT college FROM programs WHERE college IS NOT NULL AND college != '' ORDER BY college";
    $params = [];
    
    // Program chairs can only see their college
    if ($usertype === 0 && $userId !== 0) {
        require_once __DIR__ . '/../../assets/includes/auth_functions.php';
        $userCollege = get_user_college($pdo, $userId);
        if ($userCollege) {
            $query = "SELECT DISTINCT college FROM programs WHERE college = ? ORDER BY college";
            $params[] = $userCollege;
        }
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $colleges = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'data' => $colleges
    ]);
}
