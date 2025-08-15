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

    foreach($_POST as $key => $value){

        $_POST[$key] = _cleaninjections(trim($value));
    }

    /*
    * -------------------------------------------------------------------------------
    *   Verifying CSRF token
    * -------------------------------------------------------------------------------
    */

    if (!verify_csrf_token()){

        $_SESSION['STATUS']['signupstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }



    require '../../assets/setup/db.inc.php';
    
    //filter POST data
    function input_filter($data) {
        $data= trim($data);
        $data= stripslashes($data);
        $data= htmlspecialchars($data);
        return $data;
    }
    
    $username = input_filter($_POST['username']);
    $email = input_filter($_POST['email'] . "@lpunetwork.edu.ph");
    $password = input_filter($_POST['password']);
    $passwordRepeat  = input_filter($_POST['confirmpassword']);
    $headline = input_filter($_POST['headline']);
    $bio = input_filter($_POST['bio']);
    $first_name = input_filter($_POST['first_name']);
    $last_name = input_filter($_POST['last_name']);
    $program_name = input_filter($_POST['program']);
    $specialization = isset($_POST['specialization']) ? input_filter($_POST['specialization']) : '';
    $year = input_filter($_POST['year']);
    $section = input_filter($_POST['section']);

    $program = $program_name;
    if (!empty($specialization)) {
        $program .= ' ' . $specialization;
    }

    if (isset($_POST['gender'])) 
        $gender = input_filter($_POST['gender']);
    else
        $gender = NULL;


    /*
    * -------------------------------------------------------------------------------
    *   Data Validation
    * -------------------------------------------------------------------------------
    */

    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {

        $_SESSION['ERRORS']['formerror'] = 'required fields cannot be empty, try again';
        header("Location: ../");
        exit();
    } else if (!preg_match("/^[a-zA-Z0-9.\-]+$/", $username)) {

        $_SESSION['ERRORS']['usernameerror'] = 'invalid username';
        header("Location: ../");
        exit();
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['ERRORS']['emailerror'] = 'invalid email';
        header("Location: ../");
        exit();
    } else if ($password !== $passwordRepeat) {

        $_SESSION['ERRORS']['passworderror'] = 'passwords donot match';
        header("Location: ../");
        exit();
    } else {

        if (!preg_match("/^[a-zA-Z]+$/", $first_name) || !preg_match("/^[a-zA-Z]+$/", $last_name)) {
        $_SESSION['ERRORS']['formerror'] = 'Invalid name — letters only';
        header("Location: ../");
        exit();
        }

        if (!preg_match("/^[a-zA-Z]+$/", $section)) {
        $_SESSION['ERRORS']['formerror'] = 'Invalid section';
        header("Location: ../");
        exit();
        }

        if (!availableUsername($pdo, $username)){
            $_SESSION['ERRORS']['usernameerror'] = 'username already taken';
            header("Location: ../");
            exit();
        }
        if (!availableEmail($pdo, $email)){
            $_SESSION['ERRORS']['emailerror'] = 'email already taken';
            header("Location: ../");
            exit();
        }
        if (strlen($password) < 8 || strlen($passwordRepeat) < 8){

            $_SESSION['ERRORS']['passworderror'] = 'password must be at least 8 characters';
            header("Location: ../");
            exit();
        }
        // Only allow web-safe special characters in password: !@#$%^&*()-_=+[]{};:'",.<>/?\|~
        if (
            !preg_match("/[a-z]/", $password) ||
            !preg_match("/[A-Z]/", $password) ||
            !preg_match("/[0-9]/", $password) ||
            !preg_match("/[!@#$%^&*\(\)\-_=+\[\]{};:'\",.<>\/?\\\\|~]/", $password)
        ) {
            $_SESSION['ERRORS']['passworderror'] = 'password must contain at least 1 for each lowercase letter, uppercase letter, number, and special character';
            header("Location: ../");
            exit();
        }
        

        /*
        * -------------------------------------------------------------------------------
        *   Image Upload
        * -------------------------------------------------------------------------------
        */

        $FileNameNew = '_defaultUser.png';
        $file = $_FILES['avatar'];

        if (!empty($_FILES['avatar']['name'])){

            $fileName = $_FILES['avatar']['name'];
            $fileTmpName = $_FILES['avatar']['tmp_name'];
            $fileSize = $_FILES['avatar']['size'];
            $fileError = $_FILES['avatar']['error'];
            $fileType = $_FILES['avatar']['type']; 

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileActualExt, $allowed)){

                if ($fileError === 0){

                    if ($fileSize < 10000000){

                        $FileNameNew = uniqid('', true) . "." . $fileActualExt;
                        $fileDestination = '../../assets/uploads/users/' . $FileNameNew;
                        move_uploaded_file($fileTmpName, $fileDestination);

                    }
                    else {

                        $_SESSION['ERRORS']['imageerror'] = 'image size should be less than 10MB';
                        header("Location: ../");
                        exit(); 
                    }
                }
                else {

                    $_SESSION['ERRORS']['imageerror'] = 'image upload failed, try again';
                    header("Location: ../");
                    exit();
                }
            }
            else {

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

        $sql = "INSERT INTO users(username, email, password, first_name, last_name, gender, 
                headline, bio, profile_image, created_at, program, year, section) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

        $stmt->execute([$username, $email, $hashedPwd, $first_name, $last_name, $gender, $headline, $bio, $FileNameNew, $program, $year, $section]);

        /*
        * -------------------------------------------------------------------------------
        *   Sending Verification Email for Account Activation
        * -------------------------------------------------------------------------------
        */
        
        require 'sendverificationemail.inc.php';

        $_SESSION['STATUS']['loginstatus'] = 'Account Created, please Login';
        header("Location: ../../login/");
        exit();
    }

    $stmt->closeCursor();
    $pdo = null;
} 
else {

    header("Location: ../");
    exit();
}
