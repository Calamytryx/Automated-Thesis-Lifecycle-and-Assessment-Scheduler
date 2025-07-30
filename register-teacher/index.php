<?php
define('TITLE', "Signup");
include '../assets/layouts/header.php';
require_once '../assets/setup/db.inc.php';
check_logged_out();
$app_name = APP_NAME;
$first_letter = substr($app_name, 0, 1);
$rest_of_name = substr($app_name, 1);
?>

<div class="register-wrapper position-relative min-vh-100">
    <div class="container position-relative">
        <div class="row min-vh-100 align-items-center justify-content-center justify-content-lg">
            <div class="col-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 py-4">
                <div class="card shadow-lg p-4 register-card" style="max-width: 900px; width: 100%;">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="../assets/images/logo_full_lightbg.png" alt="Logo" class="img-fluid" style="width: 200px; height: auto;">
                        </div>
                        <form class="form-auth" action="includes/register.inc.php" method="post" enctype="multipart/form-data">
                            <?php insert_csrf_token(); ?>
                            <!--
                            <div class="picCard text-center">
                                <div class="avatar-upload">
                                    <div class="avatar-preview text-center">
                                        <div id="imagePreview" style="background-image: url(../assets/uploads/users/_defaultUser.png);"></div>
                                    </div>
                                    <div class="avatar-edit">
                                        <input name='avatar' id="avatar" class="fas fa-pencil" type='file' />
                                        <label for="avatar"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <sub class="text-danger">
                                    <?php
                                    if (isset($_SESSION['ERRORS']['imageerror']))
                                        echo $_SESSION['ERRORS']['imageerror'];
                                    ?>
                                </sub>
                            </div>
                            -->
                            <h6 class="h3 mb-2 font-weight-normal text-muted text-center">Faculty Registration</h6>
                            <div class="text-center mb-3">
                                <small class="text-success font-weight-bold">
                                    <?php
                                    if (isset($_SESSION['STATUS']['signupstatus']))
                                        echo $_SESSION['STATUS']['signupstatus'];
                                    ?>
                                </small>
                            </div>
                            <div id="mainFields">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" id="username_display" class="form-control" value="" disabled>
                                            <input type="hidden" id="username" name="username" value="">
                                            <sub class="text-danger">
                                                <?php
                                                if (isset($_SESSION['ERRORS']['usernameerror']))
                                                    echo $_SESSION['ERRORS']['usernameerror'];
                                                ?>
                                            </sub>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email address</label>
                                            <div class="input-group">
                                                <input type="text" id="email" name="email" class="form-control" placeholder="Email address" required autofocus>
                                                <span class="input-group-text">@lpu.edu.ph</span>
                                            </div>
                                            <sub class="text-danger">
                                                <?php
                                                if (isset($_SESSION['ERRORS']['emailerror']))
                                                    echo $_SESSION['ERRORS']['emailerror'];
                                                ?>
                                            </sub>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    // Sync username (hidden and display) with email
                                    const emailInput = document.getElementById('email');
                                    const usernameDisplay = document.getElementById('username_display');
                                    const usernameHidden = document.getElementById('username');
                                    function syncUsername() {
                                        usernameDisplay.value = emailInput.value;
                                        usernameHidden.value = emailInput.value;
                                    }
                                    emailInput.addEventListener('input', syncUsername);
                                    // On page load, in case of autofill
                                    window.addEventListener('DOMContentLoaded', syncUsername);
                                </script>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="first_name" >First Name</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="last_name" >Last Name</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="program">Program</label>
                                    <select id="program" name="program" class="form-control" required>
                                        <option value="" disabled selected>Select Program</option>
                                        <?php
                                        try {
                                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                            $stmt = $pdo->query("SELECT DISTINCT name FROM programs ORDER BY name ASC");
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                            }
                                        } catch (PDOException $e) {
                                            echo '<option disabled>Error loading programs</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" >Password</label>
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="confirmpassword" >Confirm Password</label>
                                            <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="Confirm Password" required>
                                            <sub class="text-danger mb-4">
                                                <?php
                                                if (isset($_SESSION['ERRORS']['passworderror']))
                                                    echo $_SESSION['ERRORS']['passworderror'];
                                                ?>
                                            </sub>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <input type="checkbox" id="toggleOptional" class="custom-control-input" />
                                <label for="toggleOptional" class="custom-control-label">Optional fields</label>
                            </div>
                            <div id="optionalFields" style="display:none;">
                                <div class="form-group">
                                    <label for="headline" >Headline</label>
                                    <input type="text" id="headline" name="headline" class="form-control" placeholder="Headline">
                                </div>
                                <div class="form-group">
                                    <label for="bio" >Profile Details</label>
                                    <textarea type="text" id="bio" name="bio" class="form-control" placeholder="Tell us about yourself..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="w-100">Gender</label>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="male" name="gender" class="custom-control-input" value="m">
                                        <label class="custom-control-label" for="male">Male</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="female" name="gender" class="custom-control-input" value="f">
                                        <label class="custom-control-label" for="female">Female</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="others" name="gender" class="custom-control-input" value="o">
                                        <label class="custom-control-label" for="others">Others</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-lg btn-primary btn-block w-100" type="submit" name='signupsubmit'>Signup</button>
                            <p class="mt-4 mb-3 text-muted text-center">
                                <a href="https://cavite.lpu.edu.ph/contact-info/" target="_blank" class="login-register-a">Contact Us</a> |
                                <a href="../login/" target="_blank" class="login-register-a">Already have an account? Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../assets/layouts/footer.php'; ?>

<script type="text/javascript">
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#avatar").change(function() {
        readURL(this);
    });
    $('#toggleOptional').change(function() {
        if (this.checked) {
            $('#mainFields').hide();
            $('#optionalFields').show();
        } else {
            $('#optionalFields').hide();
            $('#mainFields').show();
        }
    });
</script>
