<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $processedUsers = [];

    // 1. Process CSV file upload if provided
    if (!empty($_FILES['bulkFile']['tmp_name'])) {
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

                // Split the name field by comma if present
                if (strpos($nameField, ',') !== false) {
                    list($last_name, $first_name) = array_map('trim', explode(',', $nameField, 2));
                } else {
                    $first_name = $nameField;
                    $last_name  = $nameField;
                }

                // Determine username; if "No Username" flag is true, auto-generate username
                if (strtolower($noUsername) === 'true' || $noUsername === '1') {
                    $username = strtolower(str_replace(' ', '', $first_name . $last_name));
                } else {
                    $username = $csvUsername;
                }
                
                $password = "1234";
                $firstToken = strtolower(explode(' ', $first_name)[0]);
                $lastToken  = strtolower(explode(' ', $last_name)[0]);
                $email = $firstToken . '.' . $lastToken . '@lpunetwork.ude.ph';
                
                $processedUsers[] = [
                    'username'    => $username,
                    'first_name'  => $first_name,
                    'last_name'   => $last_name,
                    'program'     => $program,
                    'password'    => $password,
                    'email'       => $email,
                    'usertype'    => 1  // force usertype to 1
                ];
            }
            fclose($handle);
        }
    }
    // 2. Process pasted text if provided
    elseif (!empty($_POST['bulkTextInput'])) {
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

                if (strpos($nameField, ',') !== false) {
                    list($last_name, $first_name) = array_map('trim', explode(',', $nameField, 2));
                } else {
                    $first_name = $nameField;
                    $last_name  = $nameField;
                }

                if (strtolower($noUsername) === 'true' || $noUsername === '1') {
                    $username = strtolower(str_replace(' ', '', $first_name . $last_name));
                } else {
                    $username = $csvUsername;
                }
                
                $password = "1234";
                $firstToken = strtolower(explode(' ', $first_name)[0]);
                $lastToken  = strtolower(explode(' ', $last_name)[0]);
                $email = $firstToken . '.' . $lastToken . '@lpunetwork.ude.ph';
                
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
    // 3. Process form submission (manual rows)
    elseif (!empty($_POST['users'])) {
        foreach ($_POST['users'] as $userData) {
            // Expecting keys: id, name, program, no_username
            if (!isset($userData['name']) || !isset($userData['program'])) continue;
            $csvUsername = isset($userData['id']) ? trim($userData['id']) : '';
            $nameField   = trim($userData['name']);
            $program     = trim($userData['program']);
            $noUsername  = isset($userData['no_username']) ? trim($userData['no_username']) : '';

            if (strpos($nameField, ',') !== false) {
                list($last_name, $first_name) = array_map('trim', explode(',', $nameField, 2));
            } else {
                $first_name = $nameField;
                $last_name  = $nameField;
            }

            if (strtolower($noUsername) === 'true' || $noUsername === '1') {
                $username = strtolower(str_replace(' ', '', $first_name . $last_name));
            } else {
                $username = $csvUsername;
            }
            
            $password = "1234";
            $firstToken = strtolower(explode(' ', $first_name)[0]);
            $lastToken  = strtolower(explode(' ', $last_name)[0]);
            $email = $firstToken . '.' . $lastToken . '@lpunetwork.ude.ph';
            
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
    
    // Simulate insertion (for example, by returning the processed user details)
    echo json_encode([
        'success'         => true,
        'message'         => 'Users added successfully',
        'processed_count' => count($processedUsers),
        'users'           => $processedUsers
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
