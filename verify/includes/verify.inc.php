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

    foreach($_GET as $key => $value){

        $_GET[$key] = _cleaninjections(trim($value));
    }



    $selector = $_GET['selector'];
    $validator = $_GET['validator'];

    if (empty($selector) || empty($validator)) {

        $_SESSION['STATUS']['verify'] = 'invalid token, please use new verification email';
        header("Location: ../");
        exit();
    }

    $sql = "SELECT * FROM auth_tokens WHERE auth_type='account_verify' AND selector=? LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selector]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {

        error_log("Token not found. Selector: " . $selector);
        $_SESSION['STATUS']['verify'] = 'Invalid token, please use a new verification email';
        header("Location: ../");
        exit();
    }

    // Check if the token has expired
    if (strtotime($row['expires_at']) < time()) {

        error_log("Token expired. Selector: " . $selector);
        $_SESSION['STATUS']['verify'] = 'Token expired, please request a new verification email';
        header("Location: ../");
        exit();
    }

    $tokenBin = hex2bin($validator);
    $tokenCheck = password_verify($tokenBin, $row['token']);

    if ($tokenCheck === false) {

        error_log("Invalid token. Selector: " . $selector);
        $_SESSION['STATUS']['verify'] = 'Invalid token, please use a new verification email';
        header("Location: ../");
        exit();
    }

    // If we get here, the token is valid and not expired
    // Proceed with account verification
    $userEmail = $row['user_email'];

    // Update user's verified status
    $sql = "UPDATE users SET verified_at = NOW() WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userEmail]);

    // Delete the used token
    $sql = "DELETE FROM auth_tokens WHERE user_email = ? AND auth_type = 'account_verify'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userEmail]);

    $_SESSION['STATUS']['verify'] = 'Your account has been verified. You can now log in.';
    header("Location: ../../login/");
    exit();
}
else {

    header("Location: ../");
    exit();
}