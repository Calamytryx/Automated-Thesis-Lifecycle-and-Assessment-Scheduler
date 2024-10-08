<?php

define('TITLE', "Dashboard");
include '../assets/layouts/header.php';
check_verified();

// Include database connection
require '../assets/setup/db.inc.php';

// Function to fetch all users
function fetchAllUsers($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission for updating user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user']) && $_SESSION['usertype'] == 0) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $headline = $_POST['headline'];
    $bio = $_POST['bio'];
    $usertype = $_POST['usertype'];

    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, gender = ?, headline = ?, bio = ?, usertype = ? WHERE id = ?");
    $updateStmt->execute([$username, $email, $first_name, $last_name, $gender, $headline, $bio, $usertype, $id]);

    // Refresh user data
    $users = fetchAllUsers($pdo);
} else {
    $users = fetchAllUsers($pdo);
}
?>

<main role="main" class="container">
    <div class="row">
        <div class="col-sm-3">
            <?php include('../assets/layouts/profile-card.php'); ?>
        </div>
        <div class="col-sm-9">
            <div class="d-flex align-items-center p-3 my-3 text-white bg-color rounded shadow-sm">
                <img class="mr-3" src="../assets/images/logonotextwhite.png" alt="" width="48" height="48">
                <div class="lh-100">
                    <h6 class="mb-0 text-white lh-100"><?php echo isset($_SESSION['usertype']) && $_SESSION['usertype'] == 0 ? "Admin Dashboard" : "User Dashboard"; ?></h6>
                    <small><?php echo isset($_SESSION['usertype']) && $_SESSION['usertype'] == 0 ? "User Management" : "Welcome"; ?></small>
                </div>
            </div>

            <?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 0): ?>
            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h6 class="border-bottom border-gray pb-2 mb-0">User List</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Gender</th>
                                <th>Headline</th>
                                <th>Bio</th>
                                <th>User Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <form method="post">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <td><?php echo $user['id']; ?></td>
                                    <td><input type="text" name="username" value="<?php echo $user['username']; ?>" required></td>
                                    <td><input type="email" name="email" value="<?php echo $user['email']; ?>" required></td>
                                    <td><input type="text" name="first_name" value="<?php echo $user['first_name']; ?>"></td>
                                    <td><input type="text" name="last_name" value="<?php echo $user['last_name']; ?>"></td>
                                    <td><input type="text" name="gender" value="<?php echo $user['gender']; ?>"></td>
                                    <td><input type="text" name="headline" value="<?php echo $user['headline']; ?>"></td>
                                    <td><textarea name="bio"><?php echo $user['bio']; ?></textarea></td>
                                    <td>
                                        <label><input type="radio" name="usertype" value="0" <?php echo ($user['usertype'] == 0) ? 'checked' : ''; ?>> Admin</label>
                                        <label><input type="radio" name="usertype" value="1" <?php echo ($user['usertype'] == 1) ? 'checked' : ''; ?>> User</label>
                                    </td>
                                    <td>
                                        <button type="submit" name="update_user" class="btn btn-primary btn-sm">Update</button>
                                    </td>
                                </form>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h6 class="border-bottom border-gray pb-2 mb-0">Welcome to Your Dashboard</h6>
                <div class="media text-muted pt-3">
                    <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                        <strong class="d-block text-gray-dark">@<?php echo $_SESSION['username']; ?></strong>
                        This is your personal dashboard. As a regular user, you have limited access to features.
                    </p>
                </div>
                <!-- Add more user-specific content here -->
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../assets/layouts/footer.php'; ?>