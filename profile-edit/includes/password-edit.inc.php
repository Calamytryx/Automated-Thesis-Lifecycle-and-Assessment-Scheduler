<?php

if (isset($_POST['update-profile'])) {

    if( !empty($oldPassword) && !empty($newpassword) && !empty($passwordrepeat)){

        $sql = "SELECT password FROM users WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if (!$stmt) {
            $_SESSION['ERRORS']['sqlerror'] = 'SQL ERROR';
            header("Location: ../");
            exit();
        }
        else {
            $stmt->execute([$_SESSION['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row){
                $pwdCheck = password_verify($oldPassword, $row['password']);

                if ($pwdCheck == false){
                    $_SESSION['ERRORS']['passworderror'] = 'incorrect current password';
                    header("Location: ../");
                    exit();
                }
                if ($oldPassword == $newpassword){
                    $_SESSION['ERRORS']['passworderror'] = 'new password cannot be same as old password';
                    header("Location: ../");
                    exit();
                }
                if ($newpassword !== $passwordrepeat){
                    $_SESSION['ERRORS']['passworderror'] = 'confirmed password does not match new password';
                    header("Location: ../");
                    exit();
                }

                $passwordUpdated = true;

                // script endpoint --------->>
            }
        }
    }
    else{
        $_SESSION['ERRORS']['passworderror'] = 'password fields cannot be empty for password updation';
        header("Location: ../");
        exit();
    }  
} 
else {
    header("Location: ../");
    exit();
}

