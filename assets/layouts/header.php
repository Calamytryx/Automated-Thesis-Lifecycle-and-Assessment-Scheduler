<?php

session_start();

require '../assets/setup/env.php';
require '../assets/setup/db.inc.php';
require '../assets/includes/auth_functions.php';
require '../assets/includes/security_functions.php';

if (isset($_SESSION['auth']))
    $_SESSION['expire'] = ALLOWED_INACTIVITY_TIME;

generate_csrf_token();
check_remember_me();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo APP_DESCRIPTION;  ?>">
    <meta name="author" content="<?php echo APP_OWNER;  ?>">

    <title><?php echo TITLE . ' | ' . APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script> 
        //   document.addEventListener('DOMContentLoaded', function() {
        //     var calendarEl = document.getElementById('calendar');
        //     var calendar = new FullCalendar.Calendar(calendarEl, {
        //       initialView: 'dayGridMonth',
        //       headerToolbar: {
        //                 left: 'prev,next today',
        //                 center: 'title',
        //                 right: 'dayGridMonth,timeGridWeek,timeGridDay'
        //             },
        //             height: 'auto', // or set a specific height like '600px'

        //     });
        //     calendar.render();
        //   });
    </script>

    <!-- Custom styles -->
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="custom.css">
    <script type="importmap">
        {
            "imports": {
                "@google/generative-ai": "https://esm.run/@google/generative-ai"
            }
        }
    </script>
     <?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 0): ?>
    <!-- Summernote Lite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <?php endif; ?>
</head>

<body>
    <?php if (isset($_SESSION['auth'])) { ?>
        <?php require 'navbar.php'; ?>
    <?php } ?>