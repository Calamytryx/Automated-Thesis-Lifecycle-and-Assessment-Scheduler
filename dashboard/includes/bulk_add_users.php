<?php
header('Content-Type: application/json');

// NEW: Include database connection
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine upload method if provided
    $uploadMethod = isset($_POST['upload_method']) ? trim($_POST['upload_method']) : '';

    // Fallback: if not provided, detect based on available data
    if (empty($uploadMethod)) {
        if (!empty($_FILES['bulkFile']['tmp_name'])) {
            $uploadMethod = 'file';
        } elseif (!empty($_POST['bulkTextInput'])) {
            $uploadMethod = 'paste';
        } elseif (!empty($_POST['users'])) {
            $uploadMethod = 'form';
        }
    }

    $processedUsers = [];

    if ($uploadMethod === 'file' && !empty($_FILES['bulkFile']['tmp_name'])) {
        $handle = fopen($_FILES['bulkFile']['tmp_name'], "r");
        if ($handle !== FALSE) {
            // Remove header row
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) < 4) continue;
                $csvUsername = trim($row[0]);
                $nameField   = trim($row[1]);
                $program     = trim($row[2]);
                $noUsername  = trim($row[3]);

                // Expected format: last_name,first_name
                if (strpos($nameField, ',') !== false) {
                    list($last_name, $first_name) = array_map('trim', explode(',', $nameField, 2));
                } else {
                    $first_name = $nameField;
                    $last_name  = $nameField;
                }

                if (strtolower($noUsername) === 'true' || $noUsername === '1' || strtolower($noUsername) === 'on') {
                    $username = strtolower(str_replace(' ', '', $first_name . $last_name));
                } else {
                    $username = $csvUsername;
                }
                
                $password = "1234";
                $email = $username . '@lpunetwork.edu.ph';
                
                $processedUsers[] = [
                    'username'    => $username,
                    'first_name'  => $first_name,
                    'last_name'   => $last_name,
                    'program'     => $program,
                    'password'    => $password,
                    'email'       => $email,
                    'usertype'    => 1
                ];
            }
            fclose($handle);
        }
    }
    elseif ($uploadMethod === 'paste' && !empty($_POST['bulkTextInput'])) {
        $bulkData = trim($_POST['bulkTextInput']);
        $lines = preg_split('/\r\n|\n|\r/', $bulkData);
        if (count($lines) > 1) {
            // Remove header row
            array_shift($lines);
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line);
                if (count($row) < 4) continue;
                $csvUsername = trim($row[0]);
                $nameField   = trim($row[1]);
                $program     = trim($row[2]);
                $noUsername  = trim($row[3]);

                // Expected format: last_name,first_name
                if (strpos($nameField, ',') !== false) {
                    list($last_name, $first_name) = array_map('trim', explode(',', $nameField, 2));
                } else {
                    $first_name = $nameField;
                    $last_name  = $nameField;
                }

                if (strtolower($noUsername) === 'true' || $noUsername === '1' || strtolower($noUsername) === 'on') {
                    $username = strtolower(str_replace(' ', '', $first_name . $last_name));
                } else {
                    $username = $csvUsername;
                }
                
                $password = "1234";
                $email = $username . '@lpunetwork.com';
                
                $processedUsers[] = [
                    'username'    => $username,
                    'first_name'  => $first_name,
                    'last_name'   => $last_name,
                    'program'     => $program,
                    'password'    => $password,
                    'email'       => $email,
                    'usertype'    => 1
                ];
            }
        }
    }
    elseif ($uploadMethod === 'form' && !empty($_POST['users'])) {
        foreach ($_POST['users'] as $userData) {
            if (!isset($userData['name']) || !isset($userData['program'])) continue;
            $csvUsername = isset($userData['id']) ? trim($userData['id']) : '';
            $nameField   = trim($userData['name']);
            $program     = trim($userData['program']);
            $noUsername  = isset($userData['no_username']) ? trim($userData['no_username']) : '';

            if (strpos($nameField, ',') !== false) {
                list($last_name, $first_name) = array_map('trim', explode(',', $nameField, 2));
                $first_name = ucwords(strtolower($first_name));
                $last_name = ucwords(strtolower($last_name));
            } else {
                $first_name = ucwords(strtolower($nameField));
                $last_name  = ucwords(strtolower($nameField));
            }

            if (strtolower($noUsername) === 'true' || $noUsername === '1' || strtolower($noUsername) === 'on') {
                $username = strtolower(str_replace(' ', '', $first_name . $last_name));
            } else {
                $username = $csvUsername;
            }
            
            $password = $last_name;
            $email = $username . '@lpunetwork.edu.ph';

            $processedUsers[] = [
                'username'    => $username,
                'first_name'  => $first_name,
                'last_name'   => $last_name,
                'program'     => $program,
                'password'    => $password,
                'email'       => $email,
                'usertype'    => 1
            ];
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'No valid upload method selected or data provided']);
        exit;
    }

    // At this point, $processedUsers contains the processed rows.
    if (empty($processedUsers)) {
        echo json_encode(['success' => false, 'message' => 'No valid data to insert']);
        exit;
    }
    
    $insertedCount = 0;
    // Prepare insertion statement (only inserting required columns)
    $stmt = $pdo->prepare("INSERT INTO users (usertype, username, program, email, password, first_name, last_name) 
        VALUES (:usertype, :username, :program, :email, :password, :first_name, :last_name)");

    foreach ($processedUsers as $user) {
        // Validate email (should be valid per add_items.php)
        if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        // Hash password before insertion
        $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
        // Force usertype to 1
        $user['usertype'] = 1;
        if ($stmt->execute($user)) {
            $insertedCount++;
        }
    }

    echo json_encode([
        'success'         => true,
        'message'         => 'Users added successfully',
        'processed_count' => count($processedUsers),
        'inserted_count'  => $insertedCount,
        'users'           => $processedUsers
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
