<?php

define('TITLE', "Home");
include '../assets/layouts/header.php';
check_verified();

?>


<main role="main" class="container">

    <div class="row">
        <div class="col-sm-3">

            <?php include('../assets/layouts/profile-card.php'); ?>

        </div>
        <div class="col-sm-9">

            <div class="d-flex align-items-center p-3 my-3 text-white opacity-75 bg-color rounded shadow-sm">
                <img class="mr-3" src="../assets/images/logonotextwhite.png" alt="" width="48" height="48">
                <div class="lh-100">
                    <h6 class="mb-0 text-white lh-100">Admin Dashboard</h6>
                    <small>[Development in Progress]</small>
                </div>
            </div>

        </div>
    </div>
</main>




    <?php

    include '../assets/layouts/footer.php'

    ?>