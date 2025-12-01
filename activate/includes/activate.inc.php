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

        $_SESSION['STATUS']['resentsend'] = 'Invalid token, please use a new activation link via email';
        header("Location: ../");
        exit();
    }
    if (empty($password) || empty($passwordRepeat)) {

        $_SESSION['ERRORS']['passworderror'] = 'Passwords cannot be empty';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    else if ($password != $passwordRepeat) {

        $_SESSION['ERRORS']['passworderror'] = 'Passwords do not match';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $currentDate = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM auth_tokens WHERE auth_type='password_reset' AND selector=? AND expires_at > ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selector, $currentDate]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {

        $_SESSION['STATUS']['resentsend'] = 'Token not found or expired, please request a new activation link via email';
        header("Location: ../");
        exit();
    }

    $tokenBin = hex2bin($validator);
    $tokenCheck = password_verify($tokenBin, $row['token']);

    if ($tokenCheck === false) {

        $_SESSION['STATUS']['resentsend'] = 'Invalid token, please use a new activation link via email';
        header("Location: ../");
        exit();
    }

    $tokenEmail = $row['user_email'];

    $sql = 'SELECT * FROM users WHERE email=?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tokenEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        $_SESSION['STATUS']['resentsend'] = 'User not found, please use a new activation link via email';
        header("Location: ../");
        exit();
    }

    $sql = 'UPDATE users SET password=? WHERE email=?';
    $stmt = $pdo->prepare($sql);
    $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
    $result = $stmt->execute([$newPwdHash, $tokenEmail]);

    if ($result) {

        $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='password_reset'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tokenEmail]);

        $_SESSION['STATUS']['loginstatus'] = 'Email activated, please log in';
        header("Location: ../../login/");
        exit();
    } else {

        $_SESSION['STATUS']['resentsend'] = 'Failed to activate email, please try again';
        header("Location: ../");
        exit();
    }
}
else {

    header("Location: ../");
    exit();
}