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

    $sql = "SELECT * FROM auth_tokens WHERE auth_type='account_verify' AND selector=? AND expires_at >= NOW() LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selector]);
    $row = $stmt->fetch();

    if (!$row) {

        $_SESSION['STATUS']['verify'] = 'non-existent or expired token, please use new verification email';
        header("Location: ../");
        exit();
    }
    else {

        $tokenBin = hex2bin($validator);
        $tokenCheck = password_verify($tokenBin, $row['token']);

        if ($tokenCheck === false) {

            $_SESSION['STATUS']['verify'] = 'invalid token, please use new verification email';
            header("Location: ../");
            exit();
        }
        else if ($tokenCheck === true) {

            $tokenEmail = $row['user_email'];

            $sql = 'SELECT * FROM users WHERE email=? LIMIT 1;';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tokenEmail]);
            $row = $stmt->fetch();

            if (!$row) {
                
                $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new verification email';
                header("Location: ../");
                exit();
            }
            else {

                $sql = 'UPDATE users SET verified_at=NOW() WHERE email=?;';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tokenEmail]);

                $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='account_verify';";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tokenEmail]);

                if (isset($_SESSION['auth'])){

                    $_SESSION['auth'] = 'verified';
                }

                $_SESSION['STATUS']['loginstatus'] = 'account activated, please login';
                header ("Location: ../../login/");
            }
        }
    }
}
else {

    header("Location: ../");
    exit();
}