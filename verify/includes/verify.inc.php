<?php

session_start();

require '../../assets/setup/env.php';
require '../../assets/setup/db.inc.php';
require '../../assets/includes/security_functions.php';

if (isset($_GET['selector']) && isset($_GET['validator'])) {

    /*
    * -------------------------------------------------------------------------------
    *   Securing against Header Injection
    * -------------------------------------------------------------------------------
    */

    foreach ($_GET as $key => $value) {
        $_GET[$key] = _cleaninjections(trim($value));
    }

    $selector = $_GET['selector'];
    $validator = $_GET['validator'];

    if (empty($selector) || empty($validator)) {
        $_SESSION['STATUS']['verify'] = 'Invalid token, please use a new verification email';
        header("Location: ../");
        exit();
    }

    try {
        $sql = "SELECT * FROM auth_tokens WHERE auth_type='account_verify' AND selector=? AND expires_at >= NOW() LIMIT 1;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$selector]);

        if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['STATUS']['verify'] = 'Non-existent or expired token, please use a new verification email';
            header("Location: ../");
            exit();
        }

        $tokenBin = hex2bin($validator);
        $tokenCheck = password_verify($tokenBin, $row['token']);

        if ($tokenCheck === false) {
            $_SESSION['STATUS']['verify'] = 'Invalid token, please use a new verification email';
            header("Location: ../");
            exit();
        }

        $tokenEmail = $row['user_email'];

        // Check if user exists
        $sql = 'SELECT * FROM users WHERE email=? LIMIT 1;';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tokenEmail]);

        if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['STATUS']['resentsend'] = 'Invalid token, please use a new verification email';
            header("Location: ../");
            exit();
        }

        // Update user verification status
        $sql = 'UPDATE users SET verified_at=NOW() WHERE email=?;';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tokenEmail]);

        // Delete auth token
        $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='account_verify';";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tokenEmail]);

        if (isset($_SESSION['auth'])) {
            $_SESSION['auth'] = 'verified';
        }

        $_SESSION['STATUS']['loginstatus'] = 'Account activated, please login';
        header("Location: ../../login/");
        exit();

    } catch (PDOException $e) {
        // Log the error message for debugging
        $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR: ' . $e->getMessage();
        header("Location: ../");
        exit();
    }
} else {
    header("Location: ../");
    exit();
}
