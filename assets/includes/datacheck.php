<?php

function availableUsername($pdo, $username){
    $sql = "SELECT id FROM users WHERE username=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    return $stmt->rowCount() === 0;
}

function availableEmail($pdo, $email){
    $sql = "SELECT id FROM users WHERE email=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->rowCount() === 0;
}