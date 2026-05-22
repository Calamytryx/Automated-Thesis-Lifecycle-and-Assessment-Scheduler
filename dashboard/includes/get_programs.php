<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php'; // Ensure this path is correct

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Check if filtering by college
    $filterCollege = isset($_GET['college']) ? trim($_GET['college']) : null;
    
    // Select college and department for grouping
    $query = "SELECT id, college, department, name, specialization
              FROM programs";
    
    // Add WHERE clause if filtering by college
    $params = [];
    if ($filterCollege) {
        $query .= " WHERE college = ?";
        $params[] = $filterCollege;
    }
    
    $query .= " ORDER BY college, department, name"; // Order appropriately
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $tempData = [];
    $collegeHasDepartments = []; // Track colleges that have at least one non-empty department

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Use college as the primary grouping key.
        $college = !empty($row['college']) ? $row['college'] : 'Uncategorized College';
        $department = $row['department']; // Keep original department value (can be null/empty)

        // Initialize college array if it doesn't exist
        if (!isset($tempData[$college])) {
            $tempData[$college] = [];
        }

        // If this program has a department, mark the college as having departments
        if (!empty($department) && !isset($collegeHasDepartments[$college])) {
            $collegeHasDepartments[$college] = true;
        }

        // Construct the full program name
        $fullName = $row['name'];
        if (!empty($row['specialization'])) {
            $fullName .= ' - ' . $row['specialization'];
        }

        // Store program details temporarily, including department for later processing
        $tempData[$college][] = [
            'id' => $row['id'],
            'department' => $department, // Store department temporarily
            'value' => $fullName,
            'label' => $fullName
        ];
    }

    // Process the temporary data to create the final nested structure
    $finalResponseData = [];
    foreach ($tempData as $college => $programs) {
        if (isset($collegeHasDepartments[$college])) {
            // This college has departments, create department subgroups
            $departments = [];
            foreach ($programs as $program) {
                // Determine the department key for subgrouping
                $deptKey = !empty($program['department']) ? $program['department'] : 'Uncategorized Department';

                // Initialize department array if it doesn't exist
                if (!isset($departments[$deptKey])) {
                    $departments[$deptKey] = [];
                }

                // Add program to the correct department subgroup (excluding the temporary 'department' field)
                $departments[$deptKey][] = [
                    'id' => $program['id'],
                    'value' => $program['value'],
                    'label' => $program['label']
                ];
            }
            $finalResponseData[$college] = $departments; // Assign the department object to the college
        } else {
            // This college has NO departments, list programs directly under the college
            $programList = [];
            foreach ($programs as $program) {
                 // Add program (excluding the temporary 'department' field)
                $programList[] = [
                    'id' => $program['id'],
                    'value' => $program['value'],
                    'label' => $program['label']
                ];
            }
            $finalResponseData[$college] = $programList; // Assign the program list directly to the college
        }
    }


    if (!empty($finalResponseData)) {
        $response['success'] = true;
        $response['data'] = $finalResponseData;
    } else {
        $response['message'] = 'No programs found.';
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred.';
    // Consider sending a more generic error in production
    // $response['message'] = 'An error occurred while fetching programs.';
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred.';
}

echo json_encode($response);
?>