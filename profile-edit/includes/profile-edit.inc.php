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

    foreach($_POST as $key => $value){

        $_POST[$key] = _cleaninjections(trim($value));
    }

    /*
    * -------------------------------------------------------------------------------
    *   Verifying CSRF token
    * -------------------------------------------------------------------------------
    */

    if (!verify_csrf_token()){

        $_SESSION['STATUS']['editstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }


    require '../../assets/setup/db.inc.php';
    require '../../assets/includes/datacheck.php';

    // Enhanced validation and sanitization functions
    function validate_field($field_name, $value, $usertype = null) {
        $errors = [];
        
        if (empty(trim($value))) {
            if ($field_name !== 'headline' && $field_name !== 'bio' && $field_name !== 'username' && $field_name !== 'first_name' && $field_name !== 'last_name') { // These can be optional
                return ['This field is required'];
            }
            return [];
        }

        $trimmed_value = trim($value);

        // Check for emojis
        if (preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $trimmed_value)) {
            $errors[] = 'Emojis are not allowed';
        }

        // Check for HTML tags
        if (preg_match('/<[^>]*>/', $trimmed_value)) {
            $errors[] = 'HTML tags are not allowed';
        }

        // Field-specific validations
        switch ($field_name) {
            case 'username':
                if ($usertype == 1) { // Student
                    if (!preg_match('/^20\d{2}-\d{1}-\d{5}$/', $trimmed_value)) {
                        $errors[] = 'Student ID must be in format: 20XX-X-XXXXX';
                    }
                } else {
                    if (!preg_match('/^[a-zA-Z0-9\._-]{3,50}$/', $trimmed_value)) {
                        $errors[] = 'Username must be 3-50 characters, alphanumeric only';
                    }
                }
                break;

            case 'email':
                if (!filter_var($trimmed_value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Please enter a valid email address';
                }
                break;

            case 'first_name':
            case 'last_name':
                if (strlen($trimmed_value) < 2) {
                    $errors[] = 'Name must be at least 2 characters long';
                }
                if (strlen($trimmed_value) > 50) {
                    $errors[] = 'Name cannot exceed 50 characters';
                }
                if (preg_match('/^\d+$/', $trimmed_value)) {
                    $errors[] = 'Name cannot be only numbers';
                }
                if (!preg_match('/^[a-zA-Z0-9\s\.\,\-\_\@\(\)]+$/', $trimmed_value)) {
                    $errors[] = 'Name contains invalid characters';
                }
                break;

            case 'headline':
                if (!empty($trimmed_value) && strlen($trimmed_value) > 150) {
                    $errors[] = 'Headline cannot exceed 150 characters';
                }
                break;

            case 'bio':
                if (!empty($trimmed_value) && strlen($trimmed_value) > 500) {
                    $errors[] = 'Bio cannot exceed 500 characters';
                }
                break;
        }

        return $errors;
    }

    // Sanitize input data
    $username = $_SESSION['username']; // Non-editable
    $email = sanitize_html_input($_POST['email']);
    $first_name = $_SESSION['first_name']; // Non-editable
    $last_name = $_SESSION['last_name']; // Non-editable
    $headline = sanitize_html_input($_POST['headline']);
    $bio = sanitize_html_input($_POST['bio']);
    
    // Handle area_of_expertise from hidden field (comma-separated from multi-select)
    $area_of_expertise = isset($_POST['area_of_expertise_text']) ? sanitize_html_input($_POST['area_of_expertise_text']) : null;

    if (isset($_POST['gender'])) 
        $gender = $_POST['gender'];
    else
        $gender = NULL;

    // Validate all fields
    $validation_errors = [];
    $usertype = $_SESSION['usertype'];

    // Required fields (excluding non-editable ones)
    $required_fields = [];
    
    // Only allow admins to change email
    if ($usertype == 0) {
        $required_fields[] = 'email';
    } else {
        // For non-admins, keep original email
        $email = $_SESSION['email'];
    }

    foreach ($required_fields as $field) {
        $field_errors = validate_field($field, $$field, $usertype);
        if (!empty($field_errors)) {
            $validation_errors[$field] = $field_errors;
        }
    }

    // Validate optional fields if they have values
    $optional_fields = ['headline', 'bio'];
    foreach ($optional_fields as $field) {
        if (!empty($$field)) {
            $field_errors = validate_field($field, $$field, $usertype);
            if (!empty($field_errors)) {
                $validation_errors[$field] = $field_errors;
            }
        }
    }

    // If there are validation errors, return them
    if (!empty($validation_errors)) {
        $error_messages = [];
        foreach ($validation_errors as $field => $errors) {
            $error_messages[] = ucfirst($field) . ': ' . implode(', ', $errors);
        }
        $_SESSION['ERRORS']['validationerror'] = implode('; ', $error_messages);
        header("Location: ../");
        exit();
    }

    // Additional validation for email availability (only if admin is changing email)
    if ($usertype == 0 && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ERRORS']['emailerror'] = 'invalid email, try again';
        header("Location: ../");
        exit();
    } 
    if ($usertype == 0 && $_SESSION['email'] != $email && !availableEmail($pdo, $email)) {
        $_SESSION['ERRORS']['emailerror'] = 'email already taken';
        header("Location: ../");
        exit();
    }
    
    // Username availability check
    if ( $_SESSION['username'] != $username && !availableUsername($pdo, $username)) {
        $_SESSION['ERRORS']['usernameerror'] = 'username already taken';
        header("Location: ../");
        exit();
    }

    // Handle password fields with enhanced validation
    $oldPassword = $_POST['password'];
    $newpassword = $_POST['newpassword'];
    $passwordrepeat  = $_POST['confirmpassword'];

    // Initialize password update flag
    $passwordUpdated = false;

    // Check if password change is requested
    if (!empty($oldPassword) || !empty($newpassword) || !empty($passwordrepeat)) {
        include 'password-edit.inc.php';
    }

    /*
    * -------------------------------------------------------------------------------
    *   Image Upload with Enhanced Validation
    * -------------------------------------------------------------------------------
    */

    $FileNameNew = $_SESSION['profile_image'];
    $file = $_FILES['avatar'];

    if (!empty($_FILES['avatar']['name']))
    {
        $fileName = $_FILES['avatar']['name'];
        $fileTmpName = $_FILES['avatar']['tmp_name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileError = $_FILES['avatar']['error'];
        $fileType = $_FILES['avatar']['type']; 

        // Enhanced validation
        $imageErrors = [];

        // Check file upload errors
        if ($fileError !== 0) {
            $imageErrors[] = 'File upload failed. Please try again';
        }

        // Validate file extension
        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        
        if (!in_array($fileActualExt, $allowed)) {
            $imageErrors[] = 'Invalid file type. Only JPG, PNG, and GIF images are allowed';
        }

        // Validate MIME type for additional security
        $allowedMimeTypes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
        if (!in_array($fileType, $allowedMimeTypes)) {
            $imageErrors[] = 'Invalid file format detected';
        }

        // Validate file size (5MB limit instead of 10MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($fileSize > $maxSize) {
            $imageErrors[] = 'Image size must be less than 5MB';
        }

        // Validate minimum file size (prevent empty files)
        if ($fileSize < 1024) { // 1KB minimum
            $imageErrors[] = 'Image file is too small or corrupted';
        }

        // Validate image dimensions
        $imageInfo = getimagesize($fileTmpName);
        if ($imageInfo === false) {
            $imageErrors[] = 'Invalid image file or corrupted';
        } else {
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            // Check minimum dimensions
            if ($width < 100 || $height < 100) {
                $imageErrors[] = 'Image must be at least 100x100 pixels';
            }
            
            // Check maximum dimensions (prevent extremely large images)
            if ($width > 5000 || $height > 5000) {
                $imageErrors[] = 'Image dimensions too large. Maximum 5000x5000 pixels';
            }
        }

        // If there are validation errors, return them
        if (!empty($imageErrors)) {
            $_SESSION['ERRORS']['imageerror'] = implode('; ', $imageErrors);
            header("Location: ../");
            exit();
        }

        // All validations passed, proceed with upload
        $FileNameNew = uniqid('profile_', true) . "." . $fileActualExt;
        $fileDestination = '../../assets/uploads/users/' . $FileNameNew;
        
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            /*
            * -------------------------------------------------------------------------------
            *   Deleting old profile photo
            * -------------------------------------------------------------------------------
            */
            if ( $_SESSION['profile_image'] != "_defaultUser.png" ) {
                $oldImagePath = '../../assets/uploads/users/' . $_SESSION['profile_image'];
                if (file_exists($oldImagePath)) {
                    if (!unlink($oldImagePath)) {  
                        $_SESSION['ERRORS']['imageerror'] = 'Old image could not be deleted';
                        header("Location: ../");
                        exit();
                    } 
                }
            }
        } else {
            $_SESSION['ERRORS']['imageerror'] = 'Failed to save uploaded image. Please try again';
            header("Location: ../");
            exit();
        }
    }


        /*
        * -------------------------------------------------------------------------------
        *   Password Updation
        * -------------------------------------------------------------------------------
        */

        if( !empty($oldPassword) || !empty($newpassword) || !empty($passwordRepeat)){

            include 'password-edit.inc.php';
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

            foreach($mail_variables as $key => $value) {
                
                $message = str_replace('{{ '.$key.' }}', $value, $message);
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
            } 
            catch (Exception $e) {
        
                
            }
        }


        /*
        * -------------------------------------------------------------------------------
        *   User Updation
        * -------------------------------------------------------------------------------
        */

        $sql = "UPDATE users 
            SET username=?,
            email=?, 
            first_name=?, 
            last_name=?, 
            gender=?, 
            headline=?, 
            bio=?, 
            area_of_expertise=?,
            profile_image=?";

        if ($passwordUpdated){
            $sql .= ", password=? 
                    WHERE id=?";
        }
        else{
            $sql .= " WHERE id=?";
        }

        $stmt = $pdo->prepare($sql);

        if (!$stmt) {
            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
            header("Location: ../");
            exit();
        } 
        else {

            if ($passwordUpdated){
                $hashedPwd = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt->execute([
                    $username,
                    $email,
                    $first_name,
                    $last_name,
                    $gender,
                    $headline,
                    $bio,
                    $area_of_expertise,
                    $FileNameNew,
                    $hashedPwd,
                    $_SESSION['id']
                ]);
            }
            else{
                $stmt->execute([
                    $username,
                    $email,
                    $first_name,
                    $last_name,
                    $gender,
                    $headline,
                    $bio,
                    $area_of_expertise,
                    $FileNameNew,
                    $_SESSION['id']
                ]);
            }

            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['gender'] = $gender;
            $_SESSION['headline'] = $headline;
            $_SESSION['bio'] = $bio;
            $_SESSION['area_of_expertise'] = $area_of_expertise;
            $_SESSION['profile_image'] = $FileNameNew;

            $_SESSION['STATUS']['editstatus'] = 'profile successfully updated';
            header("Location: ../");
            exit();
        }

    $stmt->closeCursor();
    $pdo = null;
} 
else {

    header("Location: ../");
    exit();
}
