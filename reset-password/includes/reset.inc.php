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
<<<<<<< HEAD
=======

>>>>>>> parent of 0b78fe5 (changed all to pdo)
        $_POST[$key] = _cleaninjections(trim($value));
    }

    /*
    * -------------------------------------------------------------------------------
    *   Verifying CSRF token
    * -------------------------------------------------------------------------------
    */

    if (!verify_csrf_token()){
<<<<<<< HEAD
=======

>>>>>>> parent of 0b78fe5 (changed all to pdo)
        $_SESSION['STATUS']['resetsubmit'] = 'Request could not be validated';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }


    $selector = $_POST['selector'];
    $validator = $_POST['validator'];
    $password = $_POST['newpassword'];
    $passwordRepeat = $_POST['confirmpassword'];    

    if (empty($selector) || empty($validator)) {
<<<<<<< HEAD
        $_SESSION['STATUS']['resentsend'] = 'Invalid token, please use new reset email';
=======

        $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
>>>>>>> parent of 0b78fe5 (changed all to pdo)
        header("Location: ../");
        exit();
    }
    if (empty($password) || empty($passwordRepeat)) {
<<<<<<< HEAD
        $_SESSION['ERRORS']['passworderror'] = 'Passwords cannot be empty';
=======

        $_SESSION['ERRORS']['passworderror'] = 'passwords cannot be empty';
>>>>>>> parent of 0b78fe5 (changed all to pdo)
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    else if ($password != $passwordRepeat) {
<<<<<<< HEAD
        $_SESSION['ERRORS']['passworderror'] = 'Passwords do not match';
=======

        $_SESSION['ERRORS']['passworderror'] = 'passwords donot match';
>>>>>>> parent of 0b78fe5 (changed all to pdo)
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $sql = "SELECT * FROM auth_tokens WHERE auth_type='password_reset' AND selector=? AND expires_at >= NOW() LIMIT 1";
<<<<<<< HEAD
    $stmt = $conn->prepare($sql);
    $stmt->execute([$selector]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['STATUS']['resentsend'] = 'Non-existent or expired token, please use new reset email';
        error_log("Token not found or expired: selector = " . $selector);  // Debug log
        header("Location: ../");
        exit();
    } else {
        $tokenBin = hex2bin($validator);
        error_log("Validator: " . $validator . " | TokenBin: " . $tokenBin);  // Debug log
        $tokenCheck = password_verify($tokenBin, $row['token']);

        if ($tokenCheck === false) {
            $_SESSION['STATUS']['resentsend'] = 'Invalid token, please use new reset email';
            error_log("Token verification failed.");  // Debug log
            header("Location: ../");
            exit();
        } else if ($tokenCheck === true) {
            $tokenEmail = $row['user_email'];

            $sql = 'SELECT * FROM users WHERE email=?';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$tokenEmail]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $_SESSION['STATUS']['resentsend'] = 'Invalid token, please use new reset email';
                error_log("User not found for token email: " . $tokenEmail);  // Debug log
                header("Location: ../");
                exit();
            } else {
                $sql = 'UPDATE users SET password=? WHERE email=?';
                $stmt = $conn->prepare($sql);
                $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt->execute([$newPwdHash, $tokenEmail]);

                $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='password_reset'";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$tokenEmail]);

                $_SESSION['STATUS']['loginstatus'] = 'Password updated, please log in';
                header ("Location: ../../login/");
                exit();
=======
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {

        $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    else {

        mysqli_stmt_bind_param($stmt, "s", $selector);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt);

        if (!($row = mysqli_fetch_assoc($results))) {

            $_SESSION['STATUS']['resentsend'] = 'non-existent or expired token, please use new reset email';
            header("Location: ../");
            exit();
        }
        else {

            $tokenBin = hex2bin($validator);
            $tokenCheck = password_verify($tokenBin, $row['token']);

            if ($tokenCheck === false) {

                $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
                header("Location: ../");
                exit();
            }
            else if ($tokenCheck === true) {

                $tokenEmail = $row['user_email'];

                $sql = 'SELECT * FROM users WHERE email=?;';
                $stmt = mysqli_stmt_init($conn);

                if (!mysqli_stmt_prepare($stmt, $sql)){

                    $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    exit();
                }
                else {

                    mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                    mysqli_stmt_execute($stmt);
                    $results = mysqli_stmt_get_result($stmt);

                    if (!$row = mysqli_fetch_assoc($results)) {
                        
                        $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new reset email';
                        header("Location: ../");
                        exit();
                    }
                    else {
                        
                        $sql = 'UPDATE users SET password=? WHERE email=?;';
                        $stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt, $sql))
                        {
                            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
                            header("Location: " . $_SERVER['HTTP_REFERER']);
                            exit();
                        }
                        else {

                            $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
                            mysqli_stmt_bind_param($stmt, "ss", $newPwdHash, $tokenEmail);
                            mysqli_stmt_execute($stmt);
                            
                            $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='password_reset';";
                            $stmt = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($stmt, $sql)){

                                $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
                                header("Location: " . $_SERVER['HTTP_REFERER']);
                                exit();
                            }
                            else{

                                mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                                mysqli_stmt_execute($stmt);
                                
                                $_SESSION['STATUS']['loginstatus'] = 'password updated, please log in';
                                header ("Location: ../../login/");
                            }
                        }
                    }
                }
>>>>>>> parent of 0b78fe5 (changed all to pdo)
            }
        }
    }
}
else {
<<<<<<< HEAD
=======

>>>>>>> parent of 0b78fe5 (changed all to pdo)
    header("Location: ../");
    exit();
}