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

    foreach($_POST as $key => $value){

        $_POST[$key] = _cleaninjections(trim($value));
    }

    /*
    * -------------------------------------------------------------------------------
    *   Verifying CSRF token
    * -------------------------------------------------------------------------------
    */

    if (!verify_csrf_token()){

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
    }
    else if ($password != $passwordRepeat) {

        $_SESSION['ERRORS']['passworderror'] = 'passwords donot match';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $sql = "SELECT * FROM auth_tokens WHERE auth_type='password_reset' AND selector=? AND expires_at >= NOW() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selector]);
    $row = $stmt->fetch();

    if (!$row) {

        $_SESSION['STATUS']['resentsend'] = 'non-existent or expired token, please use new reset email';
        header("Location: ../");
        exit();
    }

    $tokenBin = hex2bin($validator);
    $tokenCheck = password_verify($tokenBin, $row['token']);

    if ($tokenCheck === false) {

        $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
        header("Location: ../");
        exit();
    }

    // Token is valid, proceed with password reset
    $tokenEmail = $row['user_email'];

    $sql = 'UPDATE users SET password=? WHERE email=?';
    $stmt = $pdo->prepare($sql);
    $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->execute([$newPwdHash, $tokenEmail]);

    $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='password_reset'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tokenEmail]);

    $_SESSION['STATUS']['loginstatus'] = 'password updated, please log in';
    header("Location: ../../login/");
    exit();
}
else {

    header("Location: ../");
    exit();
}