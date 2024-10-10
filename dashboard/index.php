<?php

define('TITLE', "Dashboard");
include '../assets/layouts/header.php';
check_verified();

// Include database connection
require '../assets/setup/db.inc.php';
require_once 'includes/edit_functions.php';

// Function to fetch all users
function fetchAllUsers($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all thesis topics
function fetchAllThesisTopics($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM thesis_topics");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all research titles
function fetchAllResearchTitles($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM research_titles");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all defense schedules
function fetchAllDefenseSchedules($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM defense_schedules");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all rubrics
function fetchAllRubrics($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM rubrics");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all teams
function fetchAllTeams($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM teams");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all requirements
function fetchAllRequirements($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM requirements");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all evaluations
function fetchAllEvaluations($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM evaluations");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all environment variables
function fetchAllEnvVariables($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM env_variables");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission for updating user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user']) && $_SESSION['usertype'] == 0) {
    // ... (existing user update code)
}

$users = fetchAllUsers($pdo);
$thesisTopics = fetchAllThesisTopics($pdo);
$researchTitles = fetchAllResearchTitles($pdo);
$defenseSchedules = fetchAllDefenseSchedules($pdo);
$rubrics = fetchAllRubrics($pdo);
$teams = fetchAllTeams($pdo);
$requirements = fetchAllRequirements($pdo);
$evaluations = fetchAllEvaluations($pdo);
$envVariables = fetchAllEnvVariables($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = handleEditSubmission($pdo);
    if ($result) {
        $_SESSION['SUCCESS'] = "Update successful";
    } else {
        $_SESSION['ERROR'] = "Update failed";
    }
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
                    <h6 class="mb-0 text-white lh-100"><?php echo $_SESSION['usertype'] == 0 ? "Admin Dashboard" : "User Dashboard"; ?></h6>
                    <small><?php echo $_SESSION['usertype'] == 0 ? "System Management" : "Welcome"; ?></small>
                </div>
            </div>

            <?php if ($_SESSION['usertype'] == 0): ?>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">Users</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="thesis-topics-tab" data-bs-toggle="tab" data-bs-target="#thesis-topics" type="button" role="tab" aria-controls="thesis-topics" aria-selected="false">Thesis Topics</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="research-titles-tab" data-bs-toggle="tab" data-bs-target="#research-titles" type="button" role="tab" aria-controls="research-titles" aria-selected="false">Research Titles</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="defense-schedules-tab" data-bs-toggle="tab" data-bs-target="#defense-schedules" type="button" role="tab" aria-controls="defense-schedules" aria-selected="false">Defense Schedules</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rubrics-tab" data-bs-toggle="tab" data-bs-target="#rubrics" type="button" role="tab" aria-controls="rubrics" aria-selected="false">Rubrics</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams" type="button" role="tab" aria-controls="teams" aria-selected="false">Teams</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="requirements-tab" data-bs-toggle="tab" data-bs-target="#requirements" type="button" role="tab" aria-controls="requirements" aria-selected="false">Requirements</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="evaluations-tab" data-bs-toggle="tab" data-bs-target="#evaluations" type="button" role="tab" aria-controls="evaluations" aria-selected="false">Evaluations</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="env-variables-tab" data-bs-toggle="tab" data-bs-target="#env-variables" type="button" role="tab" aria-controls="env-variables" aria-selected="false">Environment Variables</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="scheduler-tab" data-bs-toggle="tab" data-bs-target="#scheduler" type="button" role="tab" aria-controls="scheduler" aria-selected="false">Defense Scheduler</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schedules-tab" data-bs-toggle="tab" data-bs-target="#schedules" type="button" role="tab" aria-controls="schedules" aria-selected="false">User Schedules</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <!-- Users table content -->
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Users Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>User Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo $user['username']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo $user['first_name']; ?></td>
                                            <td><?php echo $user['last_name']; ?></td>
                                            <td>
                                                <?php
                                                switch ($user['usertype']) {
                                                    case 0:
                                                        echo 'Admin';
                                                        break;
                                                    case 1:
                                                        echo 'Student';
                                                        break;
                                                    case 2:
                                                        echo 'Staff';
                                                        break;
                                                    default:
                                                        echo 'Unknown';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="users" data-id="<?php echo $user['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
                        <!-- Thesis topics table content -->
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Thesis Topics Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Topic</th>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Suggested By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($thesisTopics as $topic): ?>
                                        <tr>
                                            <td><?php echo $topic['id']; ?></td>
                                            <td><?php echo $topic['topic']; ?></td>
                                            <td><?php echo $topic['description']; ?></td>
                                            <td><?php echo $topic['category']; ?></td>
                                            <td><?php echo $topic['suggested_by']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="thesis_topics" data-id="<?php echo $topic['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Research Titles -->
                    <div class="tab-pane fade" id="research-titles" role="tabpanel" aria-labelledby="research-titles-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Research Titles Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>User ID</th>
                                            <th>Status</th>
                                            <th>Uniqueness Score</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($researchTitles as $title): ?>
                                        <tr>
                                            <td><?php echo $title['id']; ?></td>
                                            <td><?php echo $title['title']; ?></td>
                                            <td><?php echo $title['user_id']; ?></td>
                                            <td><?php echo $title['status']; ?></td>
                                            <td><?php echo $title['uniqueness_score']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="research_titles" data-id="<?php echo $title['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Defense Schedules -->
                    <div class="tab-pane fade" id="defense-schedules" role="tabpanel" aria-labelledby="defense-schedules-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Defense Schedules Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Student ID</th>
                                            <th>Panelist ID</th>
                                            <th>Panelist ID 2</th>
                                            <th>Panelist ID 3</th>
                                            <th>Date</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Room</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($defenseSchedules as $schedule): ?>
                                        <tr>
                                            <td><?php echo $schedule['id']; ?></td>
                                            <td><?php echo $schedule['student_id']; ?></td>
                                            <td><?php echo $schedule['panelist_id']; ?></td>
                                            <td><?php echo $schedule['panelist_id2']; ?></td>
                                            <td><?php echo $schedule['panelist_id3']; ?></td>
                                            <td><?php echo date('M-d-y', strtotime($schedule['schedule_date'])); ?></td>
                                            <td><?php echo $schedule['start_time']; ?></td>
                                            <td><?php echo $schedule['end_time']; ?></td>
                                            <td><?php echo $schedule['room']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="defense_schedules" data-id="<?php echo $schedule['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Rubrics -->
                    <div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Rubrics Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rubrics as $rubric): ?>
                                        <tr>
                                            <td><?php echo $rubric['id']; ?></td>
                                            <td><?php echo $rubric['name']; ?></td>
                                            <td><?php echo $rubric['description']; ?></td>
                                            <td><?php echo $rubric['created_by']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="rubrics" data-id="<?php echo $rubric['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Teams -->
                    <div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Teams Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teams as $team): ?>
                                        <tr>
                                            <td><?php echo $team['id']; ?></td>
                                            <td><?php echo $team['name']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="teams" data-id="<?php echo $team['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Requirements -->
                    <div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Requirements Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Due Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requirements as $requirement): ?>
                                        <tr>
                                            <td><?php echo $requirement['id']; ?></td>
                                            <td><?php echo $requirement['name']; ?></td>
                                            <td><?php echo $requirement['description']; ?></td>
                                            <td><?php echo $requirement['due_date']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="requirements" data-id="<?php echo $requirement['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Evaluations -->
                    <div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Evaluations Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Defense Schedule ID</th>
                                            <th>Evaluator ID</th>
                                            <th>Total Score</th>
                                            <th>Comments</th>
                                            <th>Recommendation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($evaluations as $evaluation): ?>
                                        <tr>
                                            <td><?php echo $evaluation['id']; ?></td>
                                            <td><?php echo $evaluation['defense_schedule_id']; ?></td>
                                            <td><?php echo $evaluation['evaluator_id']; ?></td>
                                            <td><?php echo $evaluation['total_score']; ?></td>
                                            <td><?php echo $evaluation['comments']; ?></td>
                                            <td><?php echo $evaluation['recommendation']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="evaluations" data-id="<?php echo $evaluation['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="env-variables" role="tabpanel" aria-labelledby="env-variables-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Environment Variables Management</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Key</th>
                                            <th>Value</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($envVariables as $variable): ?>
                                        <tr>
                                            <td><?php echo $variable['id']; ?></td>
                                            <td><?php echo $variable['key']; ?></td>
                                            <td><?php echo $variable['value']; ?></td>
                                            <td><?php echo $variable['description']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="env_variables" data-id="<?php echo $variable['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="scheduler" role="tabpanel" aria-labelledby="scheduler-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">Defense Scheduler</h6>
                            <button id="generateSchedule" class="btn btn-primary mt-3">Generate Defense Schedule</button>
                            <div id="schedulerProgress" class="progress mt-3" style="display: none;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div id="scheduleResult" class="mt-3"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="schedules" role="tabpanel" aria-labelledby="schedules-tab">
                        <div class="my-3 p-3 bg-white rounded shadow-sm">
                            <h6 class="border-bottom border-gray pb-2 mb-0">User Schedules</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Day</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Class Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $schedules = $pdo->query("SELECT * FROM user_schedules ORDER BY user_id, day_of_week, start_time")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($schedules as $schedule):
                                        ?>
                                        <tr>
                                            <td><?php echo $schedule['user_id']; ?></td>
                                            <td><?php echo $schedule['day_of_week']; ?></td>
                                            <td><?php echo $schedule['start_time']; ?></td>
                                            <td><?php echo $schedule['end_time']; ?></td>
                                            <td><?php echo $schedule['class_name']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="user_schedules" data-id="<?php echo $schedule['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif($_SESSION['usertype'] == 1): ?>
                <!-- Regular user dashboard content -->
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          <!-- Form fields will be dynamically added here -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveChanges">Save changes</button>
      </div>
    </div>
  </div>
</div>

<?php include '../assets/layouts/footer.php'; ?><script>
$(document).ready(function() {
    $('.edit-btn').on('click', function() {
        var table = $(this).data('table');
        var id = $(this).data('id');
        
        // AJAX request to get item details
        $.ajax({
            url: 'includes/get_item_details.php',
            method: 'POST',
            data: { table: table, id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var form = $('#editForm');
                    form.empty(); // Clear previous form fields
                    
                    // Add hidden fields for table and id
                    form.append('<input type="hidden" name="table" value="' + table + '">');
                    form.append('<input type="hidden" name="id" value="' + id + '">');
                    
                    // Generate form fields based on the response
                    $.each(response.data, function(key, value) {
                        if(key !== 'id') {
                            if(table === 'env_variables') {
                                if(key === 'value') {
                                    form.append('<div class="mb-3">' +
                                        '<label for="' + key + '" class="form-label">Value</label>' +
                                        '<input type="text" class="form-control" id="' + key + '" name="' + key + '" value="' + value + '">' +
                                        '</div>');
                                } else {
                                    form.append('<input type="hidden" name="' + key + '" value="' + value + '">');
                                }
                            } else if(table === 'users') {
                                if(!['password', 'verified_at', 'created_at', 'updated_at', 'deleted_at', 'last_login_at', 'profile_image'].includes(key)) {
                                    if(key === 'usertype') {
                                        form.append('<div class="mb-3">' +
                                            '<label class="form-label">User Type</label><br>' +
                                            '<div class="form-check form-check-inline">' +
                                            '<input class="form-check-input" type="radio" name="usertype" id="usertype0" value="0"' + (value == 0 ? ' checked' : '') + '>' +
                                            '<label class="form-check-label" for="usertype0">Admin</label>' +
                                            '</div>' +
                                            '<div class="form-check form-check-inline">' +
                                            '<input class="form-check-input" type="radio" name="usertype" id="usertype1" value="1"' + (value == 1 ? ' checked' : '') + '>' +
                                            '<label class="form-check-label" for="usertype1">Student</label>' +
                                            '</div>' +
                                            '<div class="form-check form-check-inline">' +
                                            '<input class="form-check-input" type="radio" name="usertype" id="usertype2" value="2"' + (value == 2 ? ' checked' : '') + '>' +
                                            '<label class="form-check-label" for="usertype2">Staff</label>' +
                                            '</div>' +
                                            '</div>');
                                    } else if(key === 'gender') {
                                        form.append('<div class="mb-3">' +
                                            '<label class="form-label">Gender</label><br>' +
                                            '<div class="form-check form-check-inline">' +
                                            '<input class="form-check-input" type="radio" name="gender" id="genderm" value="m"' + (value === 'm' ? ' checked' : '') + '>' +
                                            '<label class="form-check-label" for="genderm">Male</label>' +
                                            '</div>' +
                                            '<div class="form-check form-check-inline">' +
                                            '<input class="form-check-input" type="radio" name="gender" id="genderf" value="f"' + (value === 'f' ? ' checked' : '') + '>' +
                                            '<label class="form-check-label" for="genderf">Female</label>' +
                                            '</div>' +
                                            '<div class="form-check form-check-inline">' +
                                            '<input class="form-check-input" type="radio" name="gender" id="gendero" value="o"' + (value === 'o' ? ' checked' : '') + '>' +
                                            '<label class="form-check-label" for="gendero">Other</label>' +
                                            '</div>' +
                                            '</div>');
                                    } else if(key === 'bio') {
                                        form.append('<div class="mb-3">' +
                                            '<label for="' + key + '" class="form-label">Bio</label>' +
                                            '<textarea class="form-control" id="' + key + '" name="' + key + '" style="resize: vertical; min-height: 100px;">' + value + '</textarea>' +
                                            '</div>');
                                    } else {
                                        form.append('<div class="mb-3">' +
                                            '<label for="' + key + '" class="form-label">' + key.replace('_', ' ').charAt(0).toUpperCase() + key.slice(1) + '</label>' +
                                            '<input type="text" class="form-control" id="' + key + '" name="' + key + '" value="' + value + '">' +
                                            '</div>');
                                    }
                                }
                            } else {
                                form.append('<div class="mb-3">' +
                                    '<label for="' + key + '" class="form-label">' + key.replace('_', ' ').charAt(0).toUpperCase() + key.slice(1) + '</label>' +
                                    '<input type="text" class="form-control" id="' + key + '" name="' + key + '" value="' + value + '">' +
                                    '</div>');
                            }
                        }
                    });
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error: Could not retrieve item details.');
            }
        });
    });

    $('#saveChanges').on('click', function() {
        var formData = $('#editForm').serialize();
        
        $.ajax({
            url: 'includes/update_item.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Item updated successfully!');
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    editModal.hide();
                    location.reload(); // Reload the page to show updated data
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Error: Could not update item. Check console for details.');
            }
        });
    });

    $('#generateSchedule').on('click', function() {
        $.ajax({
            url: 'includes/run_scheduler.php',
            method: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#generateSchedule').prop('disabled', true).text('Generating...');
            },
            success: function(response) {
                if(response.success) {
                    alert('Schedule generated successfully!');
                    location.reload(); // Reload the page to show the new schedule
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Error: Could not generate schedule. Check console for details.');
            },
            complete: function() {
                $('#generateSchedule').prop('disabled', false).text('Generate Defense Schedule');
            }
        });
    });
});
</script>