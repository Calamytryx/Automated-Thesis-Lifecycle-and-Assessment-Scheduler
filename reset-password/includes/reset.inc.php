<?php

session_start();

require '../../assets/includes/security_functions.php';
require '../../assets/includes/auth_functions.php';
check_logged_out();

require '../../assets/setup/env.php';
require '../../assets/setup/db.inc.php';

if (isset($_POST['resetsubmit'])) {

    /*
    * -------------------------------------------------------------------------------
    *   Securing against Header Injection
    * -------------------------------------------------------------------------------
    */

    foreach ($_POST as $key => $value) {
        $_POST[$key] = _cleaninjections(trim($value));
    }

    /*
    * -------------------------------------------------------------------------------
    *   Verifying CSRF token
    * -------------------------------------------------------------------------------
    */

    if (!verify_csrf_token()) {
        $_SESSION['STATUS']['resetsubmit'] = 'Request could not be validated';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $selector = $_POST['selector'];
    $validator = $_POST['validator'];
    $password = $_POST['newpassword'];
    $passwordRepeat = $_POST['confirmpassword'];

    if (empty($selector) || empty($validator)) {
        $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
        header("Location: ../");
        exit();
    }
    
    if (empty($password) || empty($passwordRepeat)) {
        $_SESSION['ERRORS']['passworderror'] = 'passwords cannot be empty';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } else if ($password != $passwordRepeat) {
        $_SESSION['ERRORS']['passworderror'] = 'passwords do not match';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    try {
        // Fetch token from the database
        $sql = "SELECT * FROM auth_tokens WHERE auth_type='password_reset' AND selector=? AND expires_at >= NOW() LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$selector]);

        if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['STATUS']['resentsend'] = 'non-existent or expired token, please use new reset email';
            header("Location: ../");
            exit();
        }

        $tokenBin = hex2bin($validator);
        $tokenCheck = password_verify($tokenBin, $row['token']);

        if (!$tokenCheck) {
            $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
            header("Location: ../");
            exit();
        }

        $tokenEmail = $row['user_email'];

        // Check if user exists
        $sql = 'SELECT * FROM users WHERE email=?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tokenEmail]);

        if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
            header("Location: ../");
            exit();
        }

        // Update user's password
        $sql = 'UPDATE users SET password=? WHERE email=?';
        $stmt = $conn->prepare($sql);
        $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$newPwdHash, $tokenEmail]);

        // Delete the token
        $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='password_reset'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tokenEmail]);

        $_SESSION['STATUS']['loginstatus'] = 'Password updated, please log in';
        header("Location: ../../login/");
        
    } catch (PDOException $e) {
        $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR: ' . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    header("Location: ../");
    exit();
}
