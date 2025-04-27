<?php
ob_start();
function check_logged_in()
{

    if (isset($_SESSION['auth'])) {

        return true;
    } else {

        header("Location: ../login/");
        exit();
    }
}

function check_logged_in_butnot_verified()
{

    if (isset($_SESSION['auth'])) {

        if ($_SESSION['auth'] == 'loggedin') {

            return true;
        } elseif ($_SESSION['auth'] == 'verified') {

            header("Location: ../home/");
            exit();
        }
    } else {

        header("Location: ../login/");
        exit();
    }
}

function check_logged_out()
{

    if (!isset($_SESSION['auth'])) {

        return true;
    } else {

        header("Location: ../home/");
        exit();
    }
}

function check_verified()
{

    if (isset($_SESSION['auth'])) {

        if ($_SESSION['auth'] == 'verified') {

            return true;
        } elseif ($_SESSION['auth'] == 'loggedin') {

            header("Location: ../verify/");
            exit();
        }
    } else {

        header("Location: ../login/");
        exit();
    }
}

function force_login($email)
{

    require '../assets/setup/db.inc.php';

    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row) {
        return false;
    } else {
        if ($row['verified_at'] != NULL) {
            $_SESSION['auth'] = 'verified';
        } else {
            $_SESSION['auth'] = 'loggedin';
        }

        $_SESSION['id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['first_name'] = $row['first_name'];
        $_SESSION['last_name'] = $row['last_name'];
        $_SESSION['gender'] = $row['gender'];
        $_SESSION['headline'] = $row['headline'];
        $_SESSION['bio'] = $row['bio'];
        $_SESSION['profile_image'] = $row['profile_image'];
        $_SESSION['banner_image'] = $row['banner_image'];
        $_SESSION['user_level'] = $row['user_level'];
        $_SESSION['verified_at'] = $row['verified_at'];
        $_SESSION['created_at'] = $row['created_at'];
        $_SESSION['updated_at'] = $row['updated_at'];
        $_SESSION['deleted_at'] = $row['deleted_at'];
        $_SESSION['last_login_at'] = $row['last_login_at'];

        return true;
    }
}

function check_remember_me()
{

    require '../assets/setup/db.inc.php';

    if (empty($_SESSION['auth']) && !empty($_COOKIE['rememberme'])) {

        list($selector, $validator) = explode(':', $_COOKIE['rememberme']);

        $sql = "SELECT * FROM auth_tokens WHERE auth_type='remember_me' AND selector=? AND expires_at >= NOW() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$selector]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        } else {
            $tokenBin = hex2bin($validator);
            $tokenCheck = password_verify($tokenBin, $row['token']);

            if ($tokenCheck === false) {
                return false;
            } else if ($tokenCheck === true) {
                $email = $row['user_email'];
                force_login($email);
                return true;
            }
        }
    }
}

/**
 * Functions for retrieving and displaying page navigation
 */

/**
 * Get all published pages for menu
 * 
 * @param PDO $pdo PDO database connection
 * @return array Array of published pages with id, title, and slug
 */
function getPublishedPages($pdo)
{
    try {
        $query = "SELECT id, title, slug FROM page_content WHERE status = 'published' ORDER BY title ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching published pages: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get the college associated with a user based on their program
 * @param PDO $pdo Database connection
 * @param int $user_id User ID to check
 * @return string|null College name or null if not found
 */
function get_user_college($pdo, $user_id)
{
    try {
        // First get the user's program
        $stmt = $pdo->prepare("SELECT program FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $program = $stmt->fetchColumn();

        if (!$program) {
            return null;
        }

        // Look up the college based on the program name/specialization
        $stmt = $pdo->prepare("
            SELECT college FROM programs 
            WHERE CONCAT(name, CASE WHEN specialization != '' THEN CONCAT(' - ', specialization) ELSE '' END) = ?
        ");
        $stmt->execute([$program]);
        $college = $stmt->fetchColumn();

        return $college;
    } catch (PDOException $e) {
        error_log("Error getting user college: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if the current user can access data for a specific college
 * @param PDO $pdo Database connection
 * @param int $user_id User ID to check
 * @param string $college College to check access for
 * @return bool True if user has access, false otherwise
 */
function can_access_college($pdo, $user_id, $college)
{
    try {
        // Get user's usertype
        $stmt = $pdo->prepare("SELECT usertype FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $usertype = $stmt->fetchColumn();

        // Check if user exists and usertype was fetched
        if ($usertype === false) {
            error_log("User with ID $user_id not found or usertype is NULL.");
            return false; // User not found or has no usertype
        }

        // Super admin (usertype=0 AND id=0) can see all colleges
        if ($usertype === 0 && $user_id === 0) {
            return true;
        }

        // All other users (College Admins: usertype=0, id!=0; Regular Users: usertype > 0)
        // are restricted to their own college.
        $user_college = get_user_college($pdo, $user_id);

        // If we couldn't determine the user's college, deny access
        if ($user_college === null) {
            error_log("Could not determine college for user ID $user_id.");
            return false;
        }

        // Grant access only if the requested college matches the user's college
        return $user_college === $college;

    } catch (PDOException $e) {
        error_log("Error checking college access for user ID $user_id: " . $e->getMessage());
        return false; // Deny access on database error
    }
}
