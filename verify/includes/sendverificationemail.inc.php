<?php

session_start();

require '../../assets/includes/auth_functions.php';
require '../../assets/includes/security_functions.php';
check_logged_in_butnot_verified();

require '../../assets/setup/env.php';
require '../../assets/setup/db.inc.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../assets/vendor/PHPMailer/src/Exception.php';
require '../../assets/vendor/PHPMailer/src/PHPMailer.php';
require '../../assets/vendor/PHPMailer/src/SMTP.php';

if (isset($_POST['verifysubmit'])) {

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
        $_SESSION['STATUS']['verify'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }

    $selector = bin2hex(random_bytes(8));
    $token = random_bytes(32);
    $url = "localhost/loginsystem/verify/includes/verify.inc.php?selector=" . $selector . "&validator=" . bin2hex($token);
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); // Set expiration time to 1 hour from now

    $email = $_SESSION['email'];

    try {
        // Delete any existing tokens for this user
        $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='account_verify'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        // Insert new token
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);
        $sql = "INSERT INTO auth_tokens (user_email, auth_type, selector, token, expires_at) 
                VALUES (?, 'account_verify', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email, $selector, $hashedToken, $expires]);

        // Prepare email
        $to = $email;
        $subject = 'Verify Your Account';
        
        /*
        * -------------------------------------------------------------------------------
        *   Using email template
        * -------------------------------------------------------------------------------
        */
        $mail_variables = [
            'APP_NAME' => APP_NAME,
            'email' => $email,
            'url' => $url,
        ];

        $message = file_get_contents("./template_verificationemail.php");
        foreach ($mail_variables as $key => $value) {
            $message = str_replace('{{ ' . $key . ' }}', $value, $message);
        }

        // Sending email
        $mail = new PHPMailer(true);
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

        $_SESSION['STATUS']['verify'] = 'Verification email sent';
        header("Location: ../");
        exit();

    } catch (PDOException $e) {
        // Log the error message for development purposes
        $_SESSION['ERRORS']['sqlerror'] = 'SQL ERROR: ' . $e->getMessage();
        header("Location: ../");
        exit();
    } catch (Exception $e) {
        $_SESSION['STATUS']['verify'] = 'Email could not be sent, try again later';
        header("Location: ../");
        exit();
    }
} else {
    header("Location: ../");
    exit();
}
