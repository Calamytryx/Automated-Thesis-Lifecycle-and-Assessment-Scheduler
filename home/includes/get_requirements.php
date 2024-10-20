
<?
/**
 * 
 * This script handles fetching requirements from the database and returning them as a JSON response.
 * 
 * Functionality:
 * - Starts a session.
 * - Ensures the user is logged in by checking the session.
 * - Connects to the database using a PDO instance.
 * - Fetches requirements from the database and returns them in JSON format.
 * - Handles any database errors and returns an appropriate JSON error message.
 * 
 * JSON Response:
 * - On success: { "success": true, "requirements": [ { "id": int, "name": string }, ... ] }
 * - On failure: { "success": false, "message": string }
 * 
 * Dependencies:
 * - Requires the database connection setup file located at '../../assets/setup/db.inc.php'.
 * 
 * Error Handling:
 * - If the user is not logged in, returns a JSON response with success set to false and an appropriate message.
 * - If a database error occurs, returns a JSON response with success set to false and the error message.
 */
session_start();
require_once '../../assets/setup/db.inc.php';

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    // Fetch requirements from the database
    $stmt = $pdo->prepare("SELECT id, name FROM requirements ORDER BY id");
    $stmt->execute();
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'requirements' => $requirements]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}