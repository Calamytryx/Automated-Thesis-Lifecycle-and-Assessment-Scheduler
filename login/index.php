<?php
define('TITLE', "Login");
include '../assets/layouts/header.php';
check_logged_out();
$app_name = APP_NAME;
$first_letter = substr($app_name, 0, 1);
$rest_of_name = substr($app_name, 1);
?>

<div class="container" id="log-reg-container">
    <div class="row g-0">
        <div class="col-md-6 img-container">
            <img src="../assets/images/login-bg.jpg" alt="Building">
        </div>
        <div class="col-md-6 form-container">
            <div class="logo">
            <span class="text-dark"><?php echo $first_letter; ?></span><span class="text-primary"><?php echo $rest_of_name; ?></span>
            </div>
            <form class="form-auth" action="includes/login.inc.php" method="post">

                <?php insert_csrf_token(); ?>

                <div class="text-center mb-3">
                    <small class="text-success font-weight-bold">
                        <?php
                            if (isset($_SESSION['STATUS']['loginstatus']))
                                echo $_SESSION['STATUS']['loginstatus'];
                        ?>
                    </small>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required autofocus>
                    <sub class="text-danger">
                        <?php
                            if (isset($_SESSION['ERRORS']['nouser']))
                                echo $_SESSION['ERRORS']['nouser'];
                        ?>
                    </sub>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    <sub class="text-danger">
                        <?php
                            if (isset($_SESSION['ERRORS']['wrongpassword']))
                                echo $_SESSION['ERRORS']['wrongpassword'];
                        ?>
                    </sub>
                </div>

                <div class="mb-3 text-end">
                    <a href="../reset-password/" class="text-decoration-none">Forgot password?</a>
                </div>

                <button class="btn btn-primary w-100" type="submit" value="loginsubmit" name="loginsubmit">Login</button>

                <div class="col-auto my-1 mb-4">
                    <div class="custom-control custom-checkbox mr-sm-2">
                        <input type="checkbox" class="custom-control-input" id="rememberme" name="rememberme">
                        <label class="custom-control-label" for="rememberme">Remember me</label>
                    </div>
                </div>

                <p class="mt-4 mb-3 text-muted text-center">
                    <a href="../contact" target="_blank">Contact Us</a> |
                    <a href="../register/" target="_blank">No account? Sign up</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php
include '../assets/layouts/footer.php';
?>
