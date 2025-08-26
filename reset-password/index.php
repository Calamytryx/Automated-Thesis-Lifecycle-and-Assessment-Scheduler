<?php

define('TITLE', "Login");
include '../assets/layouts/header.php';
check_logged_out();

?>



<div class="reset-wrapper position-relative min-vh-100">
    <div class="container position-relative">
        <div class="row min-vh-100 align-items-center justify-content-center justify-content-lg">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4 py-4">
                <div class="card shadow-lg p-4 reset-card" style="max-width: 480px; width: 100%;">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="../assets/images/logo_full_lightbg.png" alt="Logo" class="img-fluid" style="width: 200px; height: auto;">
                        </div>
                        <?php if (isset($_GET['selector']) && isset($_GET['validator'])) { ?>
                            <form class="form-auth" action="includes/reset.inc.php" method="post">
                                <?php
                                    insert_csrf_token();
                                    $selector = $_GET['selector'];
                                    $validator = $_GET['validator'];
                                ?>
                                <input type="hidden" name="selector" value="<?php echo $selector; ?>">
                                <input type="hidden" name="validator" value="<?php echo $validator; ?>">
                                <h6 class="h5 mb-3 font-weight-normal text-muted text-center">Reset password</h6>
                                <div class="text-center mb-3">
                                    <small class="text-success font-weight-bold">
                                        <?php
                                            if (isset($_SESSION['STATUS']['resetsubmit']))
                                                echo $_SESSION['STATUS']['resetsubmit'];
                                        ?>
                                    </small>
                                </div>
                                <div class="text-center mb-3">
                                    <sub class="text-danger">
                                        <?php
                                            if (isset($_SESSION['ERRORS']['passworderror']))
                                                echo $_SESSION['ERRORS']['passworderror'];
                                        ?>
                                    </sub>
                                </div>
                                <div class="form-group mb-3">
                                    <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="New Password" autocomplete="new-password">
                                </div>
                                <div class="form-group mb-4">
                                    <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="Confirm Password" autocomplete="new-password">
                                </div>
                                <button class="btn btn-primary w-100" type="submit" value="resetsubmit" name="resetsubmit">
                                    Reset Password
                                </button>
                            </form>
                        <?php } else { ?>
                            <form class="form-auth" action="includes/sendtoken.inc.php" method="post">
                                <?php insert_csrf_token(); ?>
                                <h6 class="h5 mb-3 font-weight-normal text-muted text-center">Reset password</h6>
                                <div class="text-center mb-3">
                                    <small class="text-success font-weight-bold">
                                        <?php
                                            if (isset($_SESSION['STATUS']['resentsend']))
                                                echo $_SESSION['STATUS']['resentsend'];
                                        ?>
                                    </small>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="email" class="sr-only">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email" required autofocus>
                                    <sub class="text-danger">
                                        <?php
                                            if (isset($_SESSION['ERRORS']['emailerror']))
                                                echo $_SESSION['ERRORS']['emailerror'];
                                        ?>
                                    </sub>
                                </div>
                                <button class="btn btn-primary w-100" type="submit" value="resentsend" name="resentsend">
                                    Send Password Reset Link
                                </button>
                                 <p class="mt-2 mb-0 text-muted text-center">
                                    <small>
                                        If you don't receive the email, please check your spam folder.
                                    </small>
                                    <br>
                                    <a href="../" class="login-register-a">Go back</a>
                                </p>
                                <!-- <p class="mt-4 mb-3 text-muted text-center">
                                    <a href="https://github.com/msaad1999/PHP-Login-System" target="_blank">
                                        Login System
                                    </a> | 
                                    <a href="https://github.com/msaad1999/PHP-Login-System/blob/master/LICENSE" target="_blank">
                                        MIT License
                                    </a>
                                </p> -->
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php

include '../assets/layouts/footer.php'

?>