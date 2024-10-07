<?php

session_start();

require '../../assets/includes/auth_functions.php';
require '../../assets/includes/datacheck.php';
require '../../assets/includes/security_functions.php';

check_logged_out();

if (!isset($_POST['loginsubmit'])){

    header("Location: ../");
    exit();
}
else {

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

        $_SESSION['STATUS']['loginstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }

    
    require '../../assets/setup/db.inc.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {

        $_SESSION['STATUS']['loginstatus'] = 'fields cannot be empty';
        header("Location: ../");
        exit();
    } 
    else {

        /*
        * -------------------------------------------------------------------------------
        *   Updating last_login_at
        * -------------------------------------------------------------------------------
        */

        $sql = "UPDATE users SET last_login_at=NOW() WHERE username=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);

        /*
        * -------------------------------------------------------------------------------
        *   Creating SESSION Variables
        * -------------------------------------------------------------------------------
        */

        $sql = "SELECT * FROM users WHERE username=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        if (!$row) {

            $_SESSION['ERRORS']['nouser'] = 'username does not exist';
            header("Location: ../");
            exit();
        } else {

            $pwdCheck = password_verify($password, $row['password']);

            if ($pwdCheck == false) {

                $_SESSION['ERRORS']['wrongpassword'] = 'wrong password';
                header("Location: ../");
                exit();
            } 
            else if ($pwdCheck == true) {

                session_start();

                if($row['verified_at'] != NULL){

                    $_SESSION['auth'] = 'verified';
                } else{

                    $_SESSION['auth'] = 'loggedin';
                }

                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                $_SESSION['gender'] = $row['gender'];
                $_SESSION['headline'] = $row['headline'];
                $_SESSION['bio'] = $row['bio'];
                $_SESSION['profile_image'] = $row['profile_image'];
                $_SESSION['banner_image'] = $row['banner_image'];
                $_SESSION['user_level'] = $row['user_level'];
                $_SESSION['verified_at'] = $row['verified_at'];
                $_SESSION['created_at'] = $row['created_at'];
                $_SESSION['updated_at'] = $row['updated_at'];
                $_SESSION['deleted_at'] = $row['deleted_at'];
                $_SESSION['last_login_at'] = $row['last_login_at'];


                /*
                * -------------------------------------------------------------------------------
                *   Setting rememberme cookie
                * -------------------------------------------------------------------------------
                */

                if (isset($_POST['rememberme'])){

                    $selector = bin2hex(random_bytes(8));
                    $token = random_bytes(32);

                    $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='remember_me'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_SESSION['email']]);

                    setcookie(
                        'rememberme',
                        $selector . ':' . bin2hex($token),
                        time() + 864000,
                        '/',
                        false, // Secure cookie
                        true,  // HTTP-only
                        true   // SameSite attribute
                    );

                    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                    $expires = date('Y-m-d H:i:s', time() + 864000);

                    $sql = "INSERT INTO auth_tokens (user_email, auth_type, selector, token, expires_at) 
                            VALUES (?, 'remember_me', ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_SESSION['email'], $selector, $hashedToken, $expires]);
                }

                header("Location: ../../home/");
                exit();
            } 
        } 
    }
}