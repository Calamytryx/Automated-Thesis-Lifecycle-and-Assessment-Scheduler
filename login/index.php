<?php
define('TITLE', "Login");
include '../assets/layouts/header.php';
check_logged_out();
$app_name = APP_NAME;
$first_letter = substr($app_name, 0, 1);
$rest_of_name = substr($app_name, 1);
?>

<div class="login-wrapper position-relative min-vh-100">
    <div class="container position-relative">
        <div class="row min-vh-100 align-items-center justify-content-center justify-content-lg">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4 py-4">
                <div class="card shadow-lg p-4 login-card" style="max-width: 600px; width: 100%;">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="text-center mb-4">
                            <img src="../assets/images/logo_full_lightbg.png" alt="Logo" class="img-fluid" style="width: 200px; height: auto;">
                        </div>

                        <!-- Status Message -->
                        <div class="text-center mb-3">
                            <small class="text-success font-weight-bold">
                                <?php
                                    if (isset($_SESSION['STATUS']['loginstatus']))
                                        echo $_SESSION['STATUS']['loginstatus'];
                                ?>
                            </small>
                        </div> 
             
                        <!-- Login Form -->
                        <form action="includes/login.inc.php" method="post">
                            <!-- CSRF Token -->
                            <?php insert_csrf_token(); ?>

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

                            <!-- Forgot Password Link -->
                            <div class="mb-3 text-end">
                                <a href="../reset-password/" class="login-register-a">Forgot password?</a>
                            </div>

                            <!-- Submit Button -->
                            <button class="btn btn-primary w-100" type="submit" value="loginsubmit" name="loginsubmit">Login</button>

                            <!-- Remember Me -->
                            <div class="form-check my-3">
                                <input type="checkbox" class="form-check-input" id="rememberme" name="rememberme">
                                <label class="form-check-label" for="rememberme">Remember me</label>
                            </div>
                        </form>

                        <!-- Footer Links -->
                        <p class="mt-4 text-muted text-center">
                            <a href="../contact" target="_blank" class="login-register-a" rel="noopener noreferrer">Contact Us</a> | 
                            <a href="../register-student/" target="_blank" class="login-register-a" rel="noopener noreferrer">Don't have an account? Register</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../assets/layouts/footer.php';
?>
