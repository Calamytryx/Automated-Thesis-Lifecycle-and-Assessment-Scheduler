<?php

session_start();

require '../../assets/includes/auth_functions.php';
require '../../assets/includes/datacheck.php';
require '../../assets/includes/security_functions.php';

check_logged_out();

if (isset($_POST['signupsubmit'])) {

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
        $_SESSION['STATUS']['signupstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }

    require '../../assets/setup/db.inc.php';

    // Filter POST data
    function input_filter($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $username = input_filter($_POST['username']);
    $email = input_filter($_POST['email']);
    $password = input_filter($_POST['password']);
    $passwordRepeat = input_filter($_POST['confirmpassword']);
    $headline = input_filter($_POST['headline']);
    $bio = input_filter($_POST['bio']);
    $full_name = input_filter($_POST['first_name']);
    $last_name = input_filter($_POST['last_name']);
    $gender = isset($_POST['gender']) ? input_filter($_POST['gender']) : NULL;

    /*
    * -------------------------------------------------------------------------------
    *   Data Validation
    * -------------------------------------------------------------------------------
    */

    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $_SESSION['ERRORS']['formerror'] = 'required fields cannot be empty, try again';
        header("Location: ../");
        exit();
    } elseif (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
        $_SESSION['ERRORS']['usernameerror'] = 'invalid username';
        header("Location: ../");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ERRORS']['emailerror'] = 'invalid email';
        header("Location: ../");
        exit();
    } elseif ($password !== $passwordRepeat) {
        $_SESSION['ERRORS']['passworderror'] = 'passwords do not match';
        header("Location: ../");
        exit();
    } else {
        // Check if username or email is available
        if (!availableUsername($conn, $username)) {
            $_SESSION['ERRORS']['usernameerror'] = 'username already taken';
            header("Location: ../");
            exit();
        }
        if (!availableEmail($conn, $email)) {
            $_SESSION['ERRORS']['emailerror'] = 'email already taken';
            header("Location: ../");
            exit();
        }

        /*
        * -------------------------------------------------------------------------------
        *   Image Upload
        * -------------------------------------------------------------------------------
        */

        $FileNameNew = '_defaultUser.png';
        if (!empty($_FILES['avatar']['name'])) {
            $fileName = $_FILES['avatar']['name'];
            $fileTmpName = $_FILES['avatar']['tmp_name'];
            $fileSize = $_FILES['avatar']['size'];
            $fileError = $_FILES['avatar']['error'];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    if ($fileSize < 10000000) {
                        $FileNameNew = uniqid('', true) . "." . $fileActualExt;
                        $fileDestination = '../../assets/uploads/users/' . $FileNameNew;
                        move_uploaded_file($fileTmpName, $fileDestination);
                    } else {
                        $_SESSION['ERRORS']['imageerror'] = 'image size should be less than 10MB';
                        header("Location: ../");
                        exit();
                    }
                } else {
                    $_SESSION['ERRORS']['imageerror'] = 'image upload failed, try again';
                    header("Location: ../");
                    exit();
                }
            } else {
                $_SESSION['ERRORS']['imageerror'] = 'invalid image type, try again';
                header("Location: ../");
                exit();
            }
        }

        /*
        * -------------------------------------------------------------------------------
        *   User Creation
        * -------------------------------------------------------------------------------
        */
        try {
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, gender, headline, bio, profile_image, created_at) 
                    VALUES (:username, :email, :password, :first_name, :last_name, :gender, :headline, :bio, :profile_image, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPwd,
                ':first_name' => $full_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':headline' => $headline,
                ':bio' => $bio,
                ':profile_image' => $FileNameNew,
            ]);

            /*
            * -------------------------------------------------------------------------------
            *   Sending Verification Email for Account Activation
            * -------------------------------------------------------------------------------
            */
            require 'sendverificationemail.inc.php';

            $_SESSION['STATUS']['loginstatus'] = 'Account Created, please Login';
            header("Location: ../../login/");
            exit();
        } catch (PDOException $e) {
            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR: ' . $e->getMessage();
            header("Location: ../");
            exit();
        }
    }

    $conn = null; // Close the database connection
} else {
    header("Location: ../");
    exit();
}
