<?php

function availableUsername($conn, $username) {
    $sql = "SELECT id FROM users WHERE username = :username";

    try {
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':username', $username);
        
        // Execute the statement
        $stmt->execute();
        
        // Check the number of rows returned
        $resultCheck = $stmt->rowCount();

        if ($resultCheck > 0) {
            return false; // Username is taken
        } else {
            return true; // Username is available
        }
    } catch (PDOException $e) {
        $_SESSION['ERRORS']['scripterror'] = 'SQL error: ' . $e->getMessage();
        return false; // Handle error
    }
}

function availableEmail($conn, $email) {
    $sql = "SELECT id FROM users WHERE email = :email";

    try {
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':email', $email);
        
        // Execute the statement
        $stmt->execute();
        
        // Check the number of rows returned
        $resultCheck = $stmt->rowCount();

        if ($resultCheck > 0) {
            return false; // Email is taken
        } else {
            return true; // Email is available
        }
    } catch (PDOException $e) {
        $_SESSION['ERRORS']['scripterror'] = 'SQL error: ' . $e->getMessage();
        return false; // Handle error
    }
}
