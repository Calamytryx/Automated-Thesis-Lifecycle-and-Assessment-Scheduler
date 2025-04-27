<?php
// Include necessary files
require_once '../../assets/setup/db.inc.php';
require_once '../../assets/includes/auth_functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get parameters from request
$table = $_POST['table'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Current user info
$userId = $_SESSION['id'];
$usertype = $_SESSION['usertype'];
$response = ['success' => false, 'message' => 'Unknown error'];

try {
    // For admin users (usertype 0) who aren't the superadmin (userId 0), check their college
    // The superadmin (usertype 0, userId 0) bypasses this check.
    if ($usertype == 0 && $userId != 0) {
        $userCollege = get_user_college($pdo, $userId);

        // Verify the item belongs to the admin's college
        switch ($table) {
            case 'programs':
                $stmt = $pdo->prepare("SELECT college FROM programs WHERE id = ?");
                $stmt->execute([$id]);
                $itemCollege = $stmt->fetchColumn();

                if ($itemCollege != $userCollege) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'You can only delete programs from your own college.'
                    ]);
                    exit;
                }
                break;

            case 'teams':
                $stmt = $pdo->prepare("
                    SELECT p.college FROM teams t
                    JOIN programs p ON t.program = p.id
                    WHERE t.id = ?
                ");
                $stmt->execute([$id]);
                $itemCollege = $stmt->fetchColumn();

                if ($itemCollege != $userCollege) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'You can only delete teams from your own college.'
                    ]);
                    exit;
                }
                break;

            case 'users':
                // Fetch the college associated with the user being deleted
                $stmt = $pdo->prepare("SELECT college FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $itemCollege = $stmt->fetchColumn();

                // Check if the user being deleted belongs to the admin's college
                if ($itemCollege !== null && $itemCollege != $userCollege) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'You can only delete users from your own college.'
                    ]);
                    exit;
                }
                // If the user being deleted has no college assigned, or belongs to the admin's college, allow deletion.
                break;

            // Add similar checks for other tables if needed
        }
    }

    // Proceed with deletion (Superadmin or authorized admin)
    // Basic validation to prevent deleting from unexpected tables
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs'];
    if (!in_array($table, $allowedTables)) {
         throw new Exception("Invalid table specified for deletion.");
    }
    if ($id <= 0) {
        throw new Exception("Invalid ID specified for deletion.");
    }

    // Disable foreign key checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

    // Prepare and execute the delete statement
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?"); // Use backticks for table name
    $stmt->execute([$id]);

    // Re-enable foreign key checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

    if ($stmt->rowCount() > 0) {
        $response = ['success' => true, 'message' => 'Item deleted successfully'];
        // Client-side JavaScript should handle reload based on success: true
    } else {
        // This could happen if the ID doesn't exist or was already deleted
        $response = ['success' => false, 'message' => 'Item not found or already deleted.'];
    }

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    // Provide a generic error message to the user for security
    $response = ['success' => false, 'message' => 'A database error occurred. Please try again later.'];
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    $response = ['success' => false, 'message' => $e->getMessage()]; // Show specific error for validation issues
}

// Ensure headers are not already sent before outputting JSON
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode($response);