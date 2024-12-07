<?php

if (isset($_SESSION['auth'])) {

    if ($_SESSION['usertype'] == 0) {
        header("Location: dashboard");
    } else {
        header("Location: home");
    }
    exit();
}
else {

    header("Location: login");
    exit();
}
