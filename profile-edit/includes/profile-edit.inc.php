<?php
session_start();

require '../../assets/includes/security_functions.php';
require '../../assets/includes/auth_functions.php';
check_verified();

require '../../assets/vendor/PHPMailer/src/Exception.php';
require '../../assets/vendor/PHPMailer/src/PHPMailer.php';
require '../../assets/vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['update-profile'])) {
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
        $_SESSION['STATUS']['editstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }

    require '../../assets/setup/db.inc.php';
    require '../../assets/includes/datacheck.php';

    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $headline = $_POST['headline'];
    $bio = $_POST['bio'];

    $gender = isset($_POST['gender']) ? $_POST['gender'] : NULL;

    $oldPassword = $_POST['password'];
    $newpassword = $_POST['newpassword'];
    $passwordrepeat = $_POST['confirmpassword'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ERRORS']['emailerror'] = 'invalid email, try again';
        header("Location: ../");
        exit();
    }
    if ($_SESSION['email'] != $email && !availableEmail($conn, $email)) {
        $_SESSION['ERRORS']['emailerror'] = 'email already taken';
        header("Location: ../");
        exit();
    }
    if ($_SESSION['username'] != $username && !availableUsername($conn, $username)) {
        $_SESSION['ERRORS']['usernameerror'] = 'username already taken';
        header("Location: ../");
        exit();
    } else {
        /*
        * -------------------------------------------------------------------------------
        *   Image Upload
        * -------------------------------------------------------------------------------
        */
        $FileNameNew = $_SESSION['profile_image'];
        $file = $_FILES['avatar'];

        if (!empty($_FILES['avatar']['name'])) {
            $fileName = $_FILES['avatar']['name'];
            $fileTmpName = $_FILES['avatar']['tmp_name'];
            $fileSize = $_FILES['avatar']['size'];
            $fileError = $_FILES['avatar']['error'];
            $fileType = $_FILES['avatar']['type'];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    if ($fileSize < 10000000) {
                        $FileNameNew = uniqid('', true) . "." . $fileActualExt;
                        $fileDestination = '../../assets/uploads/users/' . $FileNameNew;
                        move_uploaded_file($fileTmpName, $fileDestination);

                        /*
                        * -------------------------------------------------------------------------------
                        *   Deleting old profile photo
                        * -------------------------------------------------------------------------------
                        */
                        if ($_SESSION['profile_image'] != "_defaultUser.png") {
                            if (!unlink('../../assets/uploads/users/' . $_SESSION['profile_image'])) {
                                $_SESSION['ERRORS']['imageerror'] = 'old image could not be deleted';
                                header("Location: ../");
                                exit();
                            }
                        }
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
        *   Password Updation
        * -------------------------------------------------------------------------------
        */
        $passwordUpdated = false; // Initialize passwordUpdated
        if (!empty($oldPassword) || !empty($newpassword) || !empty($passwordrepeat)) {
            include 'password-edit.inc.php'; // This file should set $passwordUpdated to true if the password was updated
        }

        if ($passwordUpdated) {
            /*
            * -------------------------------------------------------------------------------
            *   Sending notification email on password update
            * -------------------------------------------------------------------------------
            */
            $to = $_SESSION['email'];
            $subject = 'Password Updated';

            /*
            * -------------------------------------------------------------------------------
            *   Using email template
            * -------------------------------------------------------------------------------
            */
            $mail_variables = array();
            $mail_variables['APP_NAME'] = APP_NAME;
            $mail_variables['email'] = $_SESSION['email'];

            $message = file_get_contents("./template_notificationemail.php");
            foreach ($mail_variables as $key => $value) {
                $message = str_replace('{{ ' . $key . ' }}', $value, $message);
            }

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = MAIL_ENCRYPTION;
                $mail->Port = MAIL_PORT;

                $mail->setFrom(MAIL_USERNAME, APP_NAME);
                $mail->addAddress($to, APP_NAME);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
            } catch (Exception $e) {
                // Handle error, e.g., log it
            }
        }

        /*
        * -------------------------------------------------------------------------------
        *   User Updation
        * -------------------------------------------------------------------------------
        */
        $sql = "UPDATE users 
            SET username = :username,
            email = :email, 
            first_name = :first_name, 
            last_name = :last_name, 
            gender = :gender, 
            headline = :headline, 
            bio = :bio, 
            profile_image = :profile_image";

        if ($passwordUpdated) {
            $sql .= ", password = :password WHERE id = :id;";
        } else {
            $sql .= " WHERE id = :id;";
        }

        try {
            $stmt = $conn->prepare($sql);

            if ($passwordUpdated) {
                $hashedPwd = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $hashedPwd);
            }

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':headline', $headline);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':profile_image', $FileNameNew);
            $stmt->bindParam(':id', $_SESSION['id']);

            $stmt->execute();

            // Update session variables
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['gender'] = $gender;
            $_SESSION['headline'] = $headline;
            $_SESSION['bio'] = $bio;
            $_SESSION['profile_image'] = $FileNameNew;

            $_SESSION['STATUS']['editstatus'] = 'profile successfully updated';
            header("Location: ../");
            exit();
        } catch (PDOException $e) {
            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR: ' . $e->getMessage();
            header("Location: ../");
            exit();
        }
    }
} else {
    header("Location: ../");
    exit();
}
