<?php

define('TITLE', "Edit Profile");
include '../assets/layouts/header.php';
check_verified();

//XSS filter for session variables
function xss_filter($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>


<main class="container-fluid p-0 profile-edit-main-container">
    <div class="row g-0 profile-edit-row">
        <div class="col-sm-12 d-flex flex-column profile-edit-col">
            <div id="profileEditMainContent" class="d-flex flex-column flex-grow-1 profile-edit-main-content">
                <div id="profileEditCardContainer" class="d-flex flex-column align-items-center w-100">
                    <div class="profile-edit-card">
                        <div class="edit-section-title">
                            <span>Edit Your Profile</span>
                            <a href="../profile/" class="btn view-profile-btn">View Profile</a>
                        </div>
                        <form class="form-auth" action="includes/profile-edit.inc.php" method="post" enctype="multipart/form-data" autocomplete="off">
                            <?php insert_csrf_token(); ?>
                            <div class="edit-profile-grid">
                                <!-- Left: Profile Image & Status -->
                                <div class="edit-profile-left">
                                    <div class="profile-image-editor">
                                        <div class="avatar-upload">
                                            <div class="avatar-preview">
                                                <div id="imagePreview" style="background-image: url( ../assets/uploads/users/<?php echo $_SESSION['profile_image'] ?> );"></div>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input name='avatar' id="avatar" type='file' />
                                            <label for="avatar"><i class="fas fa-camera"></i> Edit Photo</label>
                                        </div>
                                        <div class="text-center mt-2">
                                            <sub class="text-danger">
                                                <?php if (isset($_SESSION['ERRORS']['imageerror'])) echo $_SESSION['ERRORS']['imageerror']; ?>
                                            </sub>
                                        </div>
                                        <div class="text-center">
                                            <small class="text-success font-weight-bold">
                                                <?php if (isset($_SESSION['STATUS']['editstatus'])) echo $_SESSION['STATUS']['editstatus']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <!-- Right: Form Fields -->
                                <div class="edit-profile-right">
                                    <div class="row profile-edit-card-row">
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="first_name">First Name</label>
                                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name" value="<?php echo xss_filter($_SESSION['first_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="last_name">Last Name</label>
                                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name" value="<?php echo xss_filter($_SESSION['last_name']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row profile-edit-card-row">
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="username">Username</label>
                                                <input type="text" id="username" name="username" class="form-control" placeholder="Username" value="<?php echo xss_filter($_SESSION['username']); ?>" autocomplete="off">
                                                <sub class="text-danger">
                                                    <?php if (isset($_SESSION['ERRORS']['usernameerror'])) echo $_SESSION['ERRORS']['usernameerror']; ?>
                                                </sub>
                                            </div>
                                        </div>
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="email">Email address</label>
                                                <input type="email" id="email" name="email" class="form-control" placeholder="Email address" value="<?php echo xss_filter($_SESSION['email']); ?>">
                                                <sub class="text-danger">
                                                    <?php if (isset($_SESSION['ERRORS']['emailerror'])) echo $_SESSION['ERRORS']['emailerror']; ?>
                                                </sub>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row profile-edit-card-row">
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="headline">Headline</label>
                                                <input type="text" id="headline" name="headline" class="form-control" placeholder="Headline (e.g. Computer Engineering Student)" value="<?php echo xss_filter($_SESSION['headline']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <div class="gender-options mt-1">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="male" name="gender" class="custom-control-input" value="m" <?php if ($_SESSION['gender'] == 'm') echo 'checked' ?> >
                                                        <label class="custom-control-label" for="male">Male</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="female" name="gender" class="custom-control-input" value="f" <?php if ($_SESSION['gender'] == 'f') echo 'checked' ?> >
                                                        <label class="custom-control-label" for="female">Female</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="bio">About</label>
                                        <textarea type="text" id="bio" name="bio" class="form-control" placeholder="Tell us about yourself..." rows="4"><?php echo xss_filter($_SESSION['bio']); ?></textarea>
                                    </div>
                                    <div class="password-section">
                                        <h3 class="password-title">Change Password</h3>
                                        <sub class="text-danger mb-4">
                                            <?php if (isset($_SESSION['ERRORS']['passworderror'])) echo $_SESSION['ERRORS']['passworderror']; ?>
                                        </sub>
                                        <div class="row profile-edit-card-row">
                                            <div class="col-md-4 profile-edit-card-col">
                                                <div class="form-group">
                                                    <label for="password">Current Password</label>
                                                    <input type="password" id="password" name="password" class="form-control" placeholder="Current Password" autocomplete="new-password">
                                                </div>
                                            </div>
                                            <div class="col-md-4 profile-edit-card-col">
                                                <div class="form-group">
                                                    <label for="newpassword">New Password</label>
                                                    <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="New Password" autocomplete="new-password">
                                                </div>
                                            </div>
                                            <div class="col-md-4 profile-edit-card-col">
                                                <div class="form-group">
                                                    <label for="confirmpassword">Confirm Password</label>
                                                    <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="Confirm Password" autocomplete="new-password">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group text-center mt-4 d-flex justify-content-end">
                                        <button class="btn edit-submit-btn" type="submit" name='update-profile'>Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

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
</script>
