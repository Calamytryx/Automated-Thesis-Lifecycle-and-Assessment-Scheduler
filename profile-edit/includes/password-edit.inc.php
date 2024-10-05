<?php

if (isset($_POST['update-profile'])) {
    // Ensure oldPassword, newpassword, and passwordrepeat are properly defined
    $oldPassword = $_POST['oldPassword'] ?? ''; // Assuming this comes from a form
    $newpassword = $_POST['newpassword'] ?? ''; // Assuming this comes from a form
    $passwordrepeat = $_POST['passwordrepeat'] ?? ''; // Assuming this comes from a form

    if (!empty($oldPassword) && !empty($newpassword) && !empty($passwordrepeat)) {

        $sql = "SELECT password FROM users WHERE id = :id;";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_SESSION['id']);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pwdCheck = password_verify($oldPassword, $row['password']);

                if ($pwdCheck === false) {
                    $_SESSION['ERRORS']['passworderror'] = 'incorrect current password';
                    header("Location: ../");
                    exit();
                }
                if ($oldPassword === $newpassword) {
                    $_SESSION['ERRORS']['passworderror'] = 'new password cannot be same as old password';
                    header("Location: ../");
                    exit();
                }
                if ($newpassword !== $passwordrepeat) {
                    $_SESSION['ERRORS']['passworderror'] = 'confirmed password does not match new password';
                    header("Location: ../");
                    exit();
                }

                // Proceed to update the password
                $passwordHashed = password_hash($newpassword, PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET password = :password WHERE id = :id;";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindParam(':password', $passwordHashed);
                $updateStmt->bindParam(':id', $_SESSION['id']);
                $updateStmt->execute();

                // Password updated successfully
                $_SESSION['STATUS']['passwordUpdate'] = 'Password updated successfully';
                header("Location: ../");
                exit();
            } else {
                $_SESSION['ERRORS']['passworderror'] = 'User not found';
                header("Location: ../");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['ERRORS']['sqlerror'] = 'SQL ERROR: ' . $e->getMessage();
            header("Location: ../");
            exit();
        }
    } else {
        $_SESSION['ERRORS']['passworderror'] = 'password fields cannot be empty for password updation';
        header("Location: ../");
        exit();
    }  
} else {
    header("Location: ../");
    exit();
}
