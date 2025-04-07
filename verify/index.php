<?php


define('TITLE', "Verify Email");
include '../assets/layouts/header.php';
check_logged_in_butnot_verified(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateemailsubmit'])) {
  // Validate CSRF token
  // Validate new email prefix (e.g., filter_var with appropriate rules)
  $prefix = trim($_POST['newemail']);
  if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 1) {
      $newemail = $prefix . "@lpunetwork.edu.ph";
  } elseif (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 2) {
      $newemail = $prefix . "@lpu.edu.ph";
  } else {
      $newemail = $prefix; // fallback if user type is not 1 or 2
  }
  
  // Database update logic using PDO:
  require_once '../assets/setup/db.inc.php'; // load PDO instance ($pdo)
  $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
  $stmt->bindParam(':email', $newemail, PDO::PARAM_STR);
  $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
  if ($stmt->execute()) {
      $_SESSION['STATUS']['update'] = "Your email has been updated to $newemail. Please verify your new email.";
      $_SESSION['email'] = $newemail; // Update session email
  } else {
      $_SESSION['STATUS']['update'] = "Error updating email.";
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

?>

<main role="main" class="container">

    <div class="row">
        <div class="col-sm-3">

            <?php include('../assets/layouts/profile-card.php'); ?>

        </div>
        <div class="shadow-lg box-shadow col-sm-7 px-5 m-5 bg-light rounded align-self-center verify-message">

            <form action="includes/sendverificationemail.inc.php" method="post">

                <?php insert_csrf_token(); ?>
            
                <h5 class="text-center mb-5 text-primary">Verify Your Email Address</h5>

                <p>
                    Before proceeding, please check your email for a verification link. If you did not receive the email,
                    <button type="submit" name="verifysubmit">click here to send another</button>.
                </p>
                <p class="text-center mt-3">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#updateEmailModal">Wrong Email?</a>
                </p>
                <br>
                <div class="text-center mt-5">
                    <h6 class="text-success">
                        <?php
                            if (isset($_SESSION['STATUS']['verify']))
                                echo $_SESSION['STATUS']['verify'];
                            if (isset($_SESSION['STATUS']['update']))
                                echo $_SESSION['STATUS']['update'];
                        ?>
                    </h6>
                </div>

            </form>

        </div>
    </div>
</main>

<!-- Modal for updating email -->
<div class="modal fade" id="updateEmailModal" tabindex="-1" role="dialog" aria-labelledby="updateEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="" method="post">
        <?php insert_csrf_token(); ?>
        <div class="modal-header">
          <h5 class="modal-title" id="updateEmailModalLabel">Update Your Email</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="newemail">
              <?php
                if (isset($_SESSION['usertype']) && ($_SESSION['usertype'] == 1 || $_SESSION['usertype'] == 2)) {
                    echo "Email Prefix";
                } else {
                    echo "New Email Address";
                }
              ?>
            </label>
            <?php if(isset($_SESSION['usertype']) && ($_SESSION['usertype'] == 1 || $_SESSION['usertype'] == 2)): ?>
              <div class="input-group">
                <input type="text" class="form-control" id="newemail" name="newemail" placeholder="<?php echo ($_SESSION['usertype'] == 1) ? 'xxxx-x-xxxxx' : 'f-xxxxxx'; ?>" required>
                <span class="input-group-text"><?php echo ($_SESSION['usertype'] == 1) ? '@lpunetwork.edu.ph' : '@lpu.edu.ph'; ?></span>
              </div>
            <?php else: ?>
              <input type="email" class="form-control" id="newemail" name="newemail" placeholder="example@example.com" required>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="updateemailsubmit" class="btn btn-primary">Update Email</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php

include '../assets/layouts/footer.php'

?>