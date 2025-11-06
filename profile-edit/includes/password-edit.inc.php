<?php

// Enhanced password validation function
function validate_password($password) {
    $errors = [];
    
    if (empty(trim($password))) {
        return ['Password is required'];
    }

    $trimmed_password = trim($password);

    // Check for emojis
    if (preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $trimmed_password)) {
        $errors[] = 'Emojis are not allowed in passwords';
    }

    // Check for HTML/script tags
    if (preg_match('/<[^>]*>/', $trimmed_password)) {
        $errors[] = 'HTML tags and scripts are not allowed in passwords';
    }

    // Check for script patterns
    if (preg_match('/(<script|javascript:|on\w+\s*=)/i', $trimmed_password)) {
        $errors[] = 'Script patterns are not allowed in passwords';
    }

    // Check minimum length
    if (strlen($trimmed_password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    // Check maximum length
    if (strlen($trimmed_password) > 128) {
        $errors[] = 'Password cannot exceed 128 characters';
    }

    // Check for required character types
    if (!preg_match('/[A-Z]/', $trimmed_password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $trimmed_password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/\d/', $trimmed_password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if (!preg_match('/[!@#$%^&*\(\)\-_=+\[\]{};:\'\",.<>\/?\\\\|~`]/', $trimmed_password)) {
        $errors[] = 'Password must contain at least one special character';
    }

    // Check for weak patterns
    if (preg_match('/(.)\1{2,}/', $trimmed_password)) {
        $errors[] = 'Password cannot contain 3 or more consecutive identical characters';
    }

    // Check for sequential patterns
    $sequences = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', '123', '234', '345', '456', '567', '678', '789'];
    foreach ($sequences as $seq) {
        if (stripos($trimmed_password, $seq) !== false) {
            $errors[] = 'Password cannot contain sequential characters';
            break;
        }
    }

    // Check for common weak passwords
    $common_passwords = ['password', 'password123', '12345678', 'qwerty', 'admin', 'letmein'];
    foreach ($common_passwords as $common) {
        if (stripos($trimmed_password, $common) !== false) {
            $errors[] = 'Password contains common weak patterns';
            break;
        }
    }

    return $errors;
}

if (isset($_POST['update-profile'])) {

    if( !empty($oldPassword) && !empty($newpassword) && !empty($passwordrepeat)){

        // Sanitize password inputs (but preserve the actual content for validation)
        $oldPassword = trim($oldPassword);
        $newpassword = trim($newpassword);
        $passwordrepeat = trim($passwordrepeat);

        // Validate new password strength
        $password_errors = validate_password($newpassword);
        if (!empty($password_errors)) {
            $_SESSION['ERRORS']['passworderror'] = implode('; ', $password_errors);
            header("Location: ../");
            exit();
        }

        // Check if passwords match
        if ($newpassword !== $passwordrepeat) {
            $_SESSION['ERRORS']['passworderror'] = 'Confirmed password does not match new password';
            header("Location: ../");
            exit();
        }

        // Check if new password is different from current
        if ($oldPassword === $newpassword) {
            $_SESSION['ERRORS']['passworderror'] = 'New password cannot be same as current password';
            header("Location: ../");
            exit();
        }

        $sql = "SELECT password FROM users WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if (!$stmt) {
            $_SESSION['ERRORS']['sqlerror'] = 'SQL ERROR';
            header("Location: ../");
            exit();
        }
        else {
            $stmt->execute([$_SESSION['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row){
                $pwdCheck = password_verify($oldPassword, $row['password']);

                if ($pwdCheck == false){
                    $_SESSION['ERRORS']['passworderror'] = 'Incorrect current password';
                    header("Location: ../");
                    exit();
                }

                $passwordUpdated = true;

                // script endpoint --------->>
            }
        }
    }
    else{
        // Check if any password field is filled but not all
        if (!empty($oldPassword) || !empty($newpassword) || !empty($passwordrepeat)) {
            $_SESSION['ERRORS']['passworderror'] = 'All password fields must be filled to change password';
        } else {
            // No password change requested, that's fine
            $passwordUpdated = false;
        }
        header("Location: ../");
        exit();
    }  
} 
else {
    header("Location: ../");
    exit();
}

