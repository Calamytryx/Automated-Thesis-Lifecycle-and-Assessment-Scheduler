
<?php
/**
 * 
 * This file serves as the main dashboard for the COECS Thesis Management System.
 * It includes various functionalities for both admin and regular users.
 * 
 * Constants:
 * - TITLE: The title of the dashboard.
 * 
 * Includes:
 * - '../assets/layouts/header.php': The header layout.
 * - '../assets/setup/db.inc.php': Database connection setup.
 * - 'includes/edit_functions.php': Functions for editing data.
 * 
 * Functions:
 * - fetchAllUsers($pdo): Fetches all users from the database.
 * - fetchAllThesisTopics($pdo): Fetches all thesis topics from the database.
 * - fetchAllResearchTitles($pdo): Fetches all research titles from the database.
 * - fetchAllDefenseSchedules($pdo): Fetches all defense schedules from the database.
 * - fetchAllRubrics($pdo): Fetches all rubrics from the database.
 * - fetchAllTeams($pdo): Fetches all teams from the database.
 * - fetchAllRequirements($pdo): Fetches all requirements from the database.
 * - fetchAllEvaluations($pdo): Fetches all evaluations from the database.
 * - fetchAllEnvVariables($pdo): Fetches all environment variables from the database.
 * - getTeamName($pdo, $team_id): Gets the team name based on the team ID.
 * - getResearchTitle($pdo, $team_id): Gets the research title based on the team ID.
 * - getTeamMembersForScheduling($pdo, $team_id, $return_type): Gets team members for scheduling.
 * 
 * Variables:
 * - $users: Stores all users fetched from the database.
 * - $thesisTopics: Stores all thesis topics fetched from the database.
 * - $researchTitles: Stores all research titles fetched from the database.
 * - $defenseSchedules: Stores all defense schedules fetched from the database.
 * - $rubrics: Stores all rubrics fetched from the database.
 * - $teams: Stores all teams fetched from the database.
 * - $requirements: Stores all requirements fetched from the database.
 * - $evaluations: Stores all evaluations fetched from the database.
 * - $envVariables: Stores all environment variables fetched from the database.
 * 
 * Form Handling:
 * - Handles form submission for updating user data.
 * - Handles form submission for general updates.
 * 
 * HTML Structure:
 * - Main container with tabs for different sections (Users, Thesis Topics, Research Titles, etc.).
 * - Modals for editing and adding items.
 * 
 * JavaScript:
 * - Handles edit, add, and delete functionalities.
 * - Handles form submissions via AJAX.
 * - Handles dynamic addition of team members.
 * - Handles defense schedule generation.
 */
define('TITLE', "Dashboard");
include '../assets/layouts/header.php';
check_verified();

// Include database connection
require '../assets/setup/db.inc.php';
require_once 'includes/edit_functions.php';

// Function to fetch all users
function fetchAllUsers($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Function to fetch all thesis topics
function fetchAllThesisTopics($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM thesis_topics");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all research titles
function fetchAllResearchTitles($pdo)
{
    $stmt = $pdo->prepare("SELECT id, team_id, title, approved_at, updated_at FROM research_titles");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all defense schedules
function fetchAllDefenseSchedules($pdo)
{
    $stmt = $pdo->prepare("
        SELECT 
            ds.id,  -- Include the id field
            ds.schedule_date,
            ds.start_time,
            ds.end_time,
            ds.room,
            t.name AS team_name,
            rt.title AS thesis_title,
            GROUP_CONCAT(DISTINCT CONCAT(u_student.first_name, ' ', u_student.last_name) ORDER BY tm.id SEPARATOR ', ') AS team_members,
            GROUP_CONCAT(DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name) ORDER BY FIELD(ds.panelist_id, ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', ') AS panelists,
            (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name) 
             FROM team_members tm_adviser 
             JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id 
             WHERE tm_adviser.team_id = t.id AND tm_adviser.role = 'adviser' 
             ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        JOIN research_titles rt ON t.id = rt.team_id
        JOIN team_members tm ON t.id = tm.team_id
        JOIN users u_student ON tm.user_id = u_student.id
        LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)
        GROUP BY ds.id, t.name, rt.title
        ORDER BY ds.schedule_date, ds.start_time
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all rubrics
function fetchAllRubrics($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM rubrics");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all teams
function fetchAllTeams($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM teams");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all requirements
function fetchAllRequirements($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM requirements");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all evaluations
function fetchAllEvaluations($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM evaluations");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all environment variables
function fetchAllEnvVariables($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM env_variables");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get team name
function getTeamName($pdo, $team_id)
{
    $stmt = $pdo->prepare("SELECT title FROM research_titles WHERE team_id = ?");
    $stmt->execute([$team_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['title'] : 'Unknown Team';
}

// Function to get research title
function getResearchTitle($pdo, $team_id)
{
    $stmt = $pdo->prepare("SELECT title FROM research_titles WHERE team_id = ?");
    $stmt->execute([$team_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['title'] : 'No title assigned';
}

function getTeamMembersForScheduling($pdo, $team_id, $return_type = 'array')
{
    $query = "SELECT u.id, u.first_name, u.last_name, tm.role 
              FROM team_members tm 
              JOIN users u ON tm.user_id = u.id 
              WHERE tm.team_id = :team_id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['team_id' => $team_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($return_type === 'array') {
            return $members;
        } else {
            return implode(', ', array_map(function ($member) {
                return $member['first_name'] . ' ' . $member['last_name'];
            }, $members));
        }
    } catch (PDOException $e) {
        error_log("Error in getTeamMembersForScheduling: " . $e->getMessage());
        return ($return_type === 'array') ? [] : '';
    }
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
        <!-- <div class="col-sm-3">
            <?php //include('../assets/layouts/profile-card.php'); 
            ?>
        </div> -->
        <div class="col-sm-12">
            <div class="d-flex align-items-center p-3 my-3 text-white bg-color rounded shadow-sm">
                <img class="mr-3" src="../assets/images/logonotextwhite.png" alt="" width="48" height="48">
                <div class="lh-100">
                    <h6 class="mb-0 text-white lh-100"><?php echo $_SESSION['usertype'] == 0 ? "Admin Dashboard" : "User Dashboard"; ?></h6>
                    <small><?php echo $_SESSION['usertype'] == 0 ? "System Management" : "Welcome"; ?></small>
                </div>
            </div>

            <?php if ($_SESSION['usertype'] == 0): ?>
                <!-- Admin dashboard content -->
                <div class="my-3 p-3 bg-white rounded shadow-sm">
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
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <!-- Users Tab -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="users">Add User</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
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
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                                <td><?php echo getUserType($user['usertype']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="users" data-id="<?php echo $user['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="users" data-id="<?php echo $user['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Thesis Topics Tab -->
                        <div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="thesis_topics">Add Thesis Topic</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($thesisTopics as $topic): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($topic['name']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['description']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="thesis_topics" data-id="<?php echo $topic['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="thesis_topics" data-id="<?php echo $topic['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Research Titles Tab -->
                        <div class="tab-pane fade" id="research-titles" role="tabpanel" aria-labelledby="research-titles-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="research_titles">Add Research Title</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Team</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($researchTitles as $title):
                                            $team = getTeamName($pdo, $title['team_id']);
                                            $status = $title['approved_at']
                                                ? "Approved (" . date('Y-m-d H:i:s', strtotime($title['updated_at'])) . ")"
                                                : "Pending (" . date('Y-m-d H:i:s', strtotime($title['updated_at'])) . ")";
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($title['title']); ?></td>
                                                <td><?php echo htmlspecialchars($team); ?></td>
                                                <td><?php echo htmlspecialchars($status); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="research_titles" data-id="<?php echo $title['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="research_titles" data-id="<?php echo $title['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Defense Schedules Tab -->
<div class="tab-pane fade" id="defense-schedules" role="tabpanel" aria-labelledby="defense-schedules-tab">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-primary btn-sm" id="generateSchedule">Generate Defense Schedule</button>
        <span id="scheduleGenerationStatus" class="ml-2"></span>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Team</th>
                    <th>Members</th>
                    <th>Adviser</th>
                    <th>Thesis Title</th>
                    <th>Panelists</th>
                    <th>Room</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $current_date = null;
                foreach ($defenseSchedules as $schedule):
                    $schedule_date = date('M-d-y', strtotime($schedule['schedule_date']));
                    $start_time = date('H:i', strtotime($schedule['start_time']));
                    $end_time = date('H:i', strtotime($schedule['end_time']));

                    // Display the date only if it's different from the previous row
                    $date_display = ($current_date !== $schedule_date) ? $schedule_date . '<br>' : '';
                    $current_date = $schedule_date;

                    // Filter out staff members from team_members
                    $team_members = array_filter(explode(', ', $schedule['team_members']), function ($member) {
                        // Assuming staff names always start with "Staff"
                        return strpos($member, 'Staff') !== 0;
                    });
                    $team_members = implode(', ', $team_members);
                ?>
                    <tr>
                        <td><?php echo $date_display . $start_time . ' - ' . $end_time; ?></td>
                        <td><?php echo htmlspecialchars($schedule['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($team_members); ?></td>
                        <td><?php echo htmlspecialchars($schedule['adviser']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['thesis_title']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['panelists']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-btn" data-table="defense_schedules" data-id="<?php echo $schedule['id']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-table="defense_schedules" data-id="<?php echo $schedule['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Metrics Section -->
        <div id="metrics">
            <h5>Schedule Metrics</h5>
            <p>Initial Population Size: <span id="initialPopulationSize"></span></p>
            <p>Crossover Operations: <span id="crossoverCount"></span></p>
            <p>Mutation Operations: <span id="mutationCount"></span></p>
            <p>Conflicts per Generation: <span id="conflictCounts"></span></p>
            <p>Fitness per Generation: <span id="fitnessScores"></span></p>
        </div>
    </div>
</div>


                        <!-- Rubrics Tab -->
                        <div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="rubrics">Add Rubric</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rubrics as $rubric): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rubric['name']); ?></td>
                                                <td><?php echo htmlspecialchars($rubric['description']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="rubrics" data-id="<?php echo $rubric['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="rubrics" data-id="<?php echo $rubric['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Teams Tab -->
                        <div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="teams">Add Team</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Research Title</th>
                                            <th>Members</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teams as $team):
                                            $teamMembers = getTeamMembersForEdit($pdo, $team['id'], 'html');
                                            $researchTitle = getResearchTitle($pdo, $team['id']);
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($team['name']); ?></td>
                                                <td><?php echo htmlspecialchars($researchTitle); ?></td>
                                                <td><?php echo $teamMembers ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="teams" data-id="<?php echo $team['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="teams" data-id="<?php echo $team['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Requirements Tab -->
                        <div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="requirements">Add Requirement</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Due Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requirements as $requirement): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($requirement['name']); ?></td>
                                                <td><?php echo htmlspecialchars($requirement['description']); ?></td>
                                                <td><?php echo htmlspecialchars($requirement['due_date']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="requirements" data-id="<?php echo $requirement['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="requirements" data-id="<?php echo $requirement['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Evaluations Tab -->
                        <div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="evaluations">Add Evaluation</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Defense Schedule</th>
                                            <th>Panelist</th>
                                            <th>Rubric</th>
                                            <th>Score</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($evaluations as $evaluation): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(getDefenseScheduleInfo($pdo, $evaluation['defense_schedule_id'])); ?></td>
                                                <td><?php echo htmlspecialchars(getUserName($pdo, $evaluation['panelist_id'])); ?></td>
                                                <td><?php echo htmlspecialchars(getRubricName($pdo, $evaluation['rubric_id'])); ?></td>
                                                <td><?php echo htmlspecialchars($evaluation['score']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="evaluations" data-id="<?php echo $evaluation['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="evaluations" data-id="<?php echo $evaluation['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Environment Variables Tab -->
                        <div class="tab-pane fade" id="env-variables" role="tabpanel" aria-labelledby="env-variables-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="env_variables">Add Environment Variable</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Value</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($envVariables as $variable): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($variable['key']); ?></td>
                                                <td><?php echo htmlspecialchars($variable['value']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="env_variables" data-id="<?php echo $variable['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="env_variables" data-id="<?php echo $variable['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Regular user dashboard content -->
                <p>Welcome to your dashboard. You can view your team and research information here.</p>
                <!-- Add more content for regular users as needed -->
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
                    <input type="hidden" name="id">
                    <input type="hidden" name="table">

                    <!-- Other form fields... -->

                    <div id="teamMembers">
                        <!-- Team members will be dynamically added here -->
                    </div>
                    <button type="button" id="addMember" class="btn btn-secondary mt-2">Add Member</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- HTML form structure -->
                <form id="addForm">
                    <input type="hidden" name="table" value="your_table_name">
                    <!-- Add other form fields here -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <!-- Add more fields as needed -->
                </form>
                <!-- Form fields will be dynamically inserted here -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="addItem">Add Item</button>
            </div>
        </div>
    </div>
</div>

<?php include '../assets/layouts/footer.php'; ?>
<script>
    $(document).ready(function() {
        // Edit button functionality
        $(document).on('click', '.edit-btn', function() {
            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Edit button clicked. Table:', table, 'ID:', id);

            $.ajax({
                url: 'includes/get_item_details.php',
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var form = $('#editForm');
                        form.empty();

                        // Add hidden inputs for table and id
                        form.append('<input type="hidden" name="table" value="' + table + '">');
                        form.append('<input type="hidden" name="id" value="' + id + '">');

                        // Rest of your form population code...
                        if (table === 'users') {
                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="usertype" class="form-label">User Type</label>
                                    <select class="form-select" id="usertype" name="usertype">
                                        <option value="0"${response.data.usertype == 0 ? ' selected' : ''}>Admin</option>
                                        <option value="1"${response.data.usertype == 1 ? ' selected' : ''}>Student</option>
                                        <option value="2"${response.data.usertype == 2 ? ' selected' : ''}>Faculty</option>
                                    </select>
                                </div>
                            `;

                            var fieldsToShow = ['username', 'email', 'first_name', 'last_name', 'gender', 'headline', 'bio'];

                            fieldsToShow.forEach(function(key) {
                                var value = response.data[key] || '';
                                var inputType = (key === 'email') ? 'email' : 'text';
                                var label = key.replace('_', ' ').charAt(0).toUpperCase() + key.slice(1);

                                if (key === 'bio') {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <textarea class="form-control" id="${key}" name="${key}" rows="3">${value}</textarea>
                                        </div>
                                    `;
                                } else {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <input type="${inputType}" class="form-control" id="${key}" name="${key}" value="${value}">
                                        </div>
                                    `;
                                }
                            });

                            form.html(formHtml);
                        } else if (table === 'thesis_topics') {
                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="topic" class="form-label">Topic</label>
                                    <input type="text" class="form-control" id="topic" name="topic" value="${response.data.topic}">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" value="${response.data.category}">
                                </div>
                                <div class="mb-3">
                                    <label for="suggested_by" class="form-label">Suggested By</label>
                                    <input type="text" class="form-control" id="suggested_by" name="suggested_by" value="${response.data.suggested_by}">
                                </div>
                            `;
                            form.html(formHtml);
                        } else if (table === 'teams') {
                            var formHtml = `
        <input type="hidden" name="table" value="${table}">
        <input type="hidden" name="id" value="${id}">
        <div class="mb-3">
            <label for="name" class="form-label">Team Name</label>
            <input type="text" class="form-control" id="name" name="name" value="${response.data.name}">
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Research Title</label>
            <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
        </div>
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
    `;

                            response.data.members.forEach(function(member, index) {
                                formHtml += `
            <div class="mb-3 row team-member" data-user-id="${member.id}">
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
                </div>
                <div class="col-sm-5">
                    <select class="form-select" name="member_role[]">
                        <option value="adviser"${member.role === 'adviser' ? ' selected' : ''}>Adviser</option>
                        <option value="leader"${member.role === 'leader' ? ' selected' : ''}>Leader</option>
                        <option value="member"${member.role === 'member' ? ' selected' : ''}>Member</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                </div>
            </div>
        `;
                            });

                            formHtml += `
        </div>
        <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
    `;
                            form.html(formHtml);

                            // Add team member functionality
                            $('#addTeamMember').on('click', function() {
                                console.log('Add Team Member button clicked');
                                addNewTeamMember();
                            });

                        } else if (table === 'research_titles') {
                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="team_id" class="form-label">Team ID</label>
                                    <input type="text" class="form-control" id="team_id" name="team_id" value="${response.data.team_id}">
                                </div>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="approved" name="approved" value="1" ${response.data.approved_at ? 'checked' : ''}>
                                        <label class="form-check-label" for="approved">Approved</label>
                                    </div>
                                </div>
                            `;
                            form.html(formHtml);
                        } else if (table === 'teams') {
                            var formHtml = `
        <input type="hidden" name="table" value="${table}">
        <input type="hidden" name="id" value="${id}">
        <div class="mb-3">
            <label for="name" class="form-label">Team Name</label>
            <input type="text" class="form-control" id="name" name="name" value="${response.data.name}">
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Research Title</label>
            <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
        </div>
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
    `;

                            response.data.members.forEach(function(member, index) {
                                formHtml += `
            <div class="mb-3 row team-member" data-user-id="${member.id}">
                <div class="col-sm-7">
                    <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
                </div>
                <div class="col-sm-5">
                    <select class="form-select" name="member_role[]">
                        <option value="adviser"${member.role === 'adviser' ? ' selected' : ''}>Adviser</option>
                        <option value="leader"${member.role === 'leader' ? ' selected' : ''}>Leader</option>
                        <option value="member"${member.role === 'member' ? ' selected' : ''}>Member</option>
                    </select>
                </div>
            </div>
        `;
                            });

                            formHtml += `
        </div>
    `;

                            form.html(formHtml);

                            // Add team member functionality
                            $('#addTeamMember').on('click', function() {
                                console.log('Add Team Member button clicked');
                                addNewTeamMember();
                            });



                        } else if (table === 'env_variables') {
                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="key" class="form-label">Key</label>
                                    <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                                </div>
                                <div class="mb-3">
                                    <label for="value" class="form-label">Value</label>
                                    <input type="text" class="form-control" id="value" name="value" value="${response.data.value}">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                                </div>
                            `;
                            form.html(formHtml);
                        } else if (table === 'defense_schedules') {
                    var formHtml = `
                        <div class="mb-3">
                            <label for="schedule_date" class="form-label">Schedule Date</label>
                            <input type="date" class="form-control" id="schedule_date" name="schedule_date" value="${response.data.schedule_date}" required>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" value="${response.data.start_time}" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" value="${response.data.end_time}" required>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Room</label>
                            <input type="text" class="form-control" id="room" name="room" value="${response.data.room}" required>
                        </div>
                    `;
                    form.html(formHtml);
                }
                        // Add more conditions for other tables as needed

                        $('#editModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error: Unable to fetch item details');
                }
            });
        });

        // Add button functionality
        $('.add-btn').on('click', function() {
            var table = $(this).data('table');
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');

            if (table === 'users') {
                form.append('<div class="mb-3">' +
                    '<label for="username" class="form-label">Username</label>' +
                    '<input type="text" class="form-control" id="username" name="username" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="email" class="form-label">Email</label>' +
                    '<input type="email" class="form-control" id="email" name="email" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="password" class="form-label">Password</label>' +
                    '<input type="password" class="form-control" id="password" name="password" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="first_name" class="form-label">First Name</label>' +
                    '<input type="text" class="form-control" id="first_name" name="first_name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="last_name" class="form-label">Last Name</label>' +
                    '<input type="text" class="form-control" id="last_name" name="last_name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="usertype" class="form-label">User Type</label>' +
                    '<select class="form-select" id="usertype" name="usertype" required>' +
                    '<option value="0">Admin</option>' +
                    '<option value="1">Student</option>' +
                    '<option value="2">Faculty</option>' +
                    '</select>' +
                    '</div>');
            } else if (table === 'thesis_topics') {
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="suggested_by" class="form-label">Suggested By</label>' +
                    '<input type="text" class="form-control" id="suggested_by" name="suggested_by" required>' +
                    '</div>');
            } else if (table === 'research_titles') {
                form.append('<div class="mb-3">' +
                    '<label for="team_id" class="form-label">Team ID</label>' +
                    '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="title" class="form-label">Title</label>' +
                    '<input type="text" class="form-control" id="title" name="title" required>' +
                    '</div>' +
                    '<div class="mb-3 form-check">' +
                    '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                    '<label class="form-check-label" for="approved">Approved</label>' +
                    '</div>');
            } else if (table === 'teams') {
                var formHtml = `
            <div class="mb-3">
                <label for="name" class="form-label">Team Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Research Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <h5 class="mt-4">Team Members</h5>
            <div id="teamMembers">
                <!-- Team members will be added here -->
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
        `;
                form.append(formHtml);

                // Add team member functionality
                $('#addTeamMember').on('click', function() {
                    console.log('Add Team Member button clicked');
                    addNewTeamMember();
                });



            } else if (table === 'rubrics') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="created_by" class="form-label">Created By</label>' +
                    '<input type="text" class="form-control" id="created_by" name="created_by" required>' +
                    '</div>');
            } else if (table === 'requirements') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="due_date" class="form-label">Due Date</label>' +
                    '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                    '</div>');
            } else if (table === 'evaluations') {
                form.append('<div class="mb-3">' +
                    '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                    '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                    '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="total_score" class="form-label">Total Score</label>' +
                    '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="comments" class="form-label">Comments</label>' +
                    '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="recommendation" class="form-label">Recommendation</label>' +
                    '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                    '</div>');
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            $('#addModal').modal('show');
        });

        $('#saveChanges').on('click', function() {
            var form = $('#editForm');
            var formData = new FormData(form[0]);

            if (formData.get('table') === 'teams') {
                var members = [];
                $('.team-member').each(function() {
                    var userId = $(this).data('user-id') || $(this).find('select[name="new_user_id[]"]').val();
                    var role = $(this).find('select[name="member_role[]"], select[name="new_role[]"]').val();
                    if (userId && role) {
                        members.push({
                            id: userId,
                            role: role
                        });
                    }
                });
                formData.set('members', JSON.stringify(members));
                formData.delete('member_role[]');
                formData.delete('new_user_id[]');
                formData.delete('new_role[]');
            }

            console.log('Form data before send:', Object.fromEntries(formData));

            $.ajax({
                url: 'includes/update_item.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    if (response.success) {
                        alert('Item updated successfully');
                        $('#editModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        console.error('Update failed:', response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    console.log('Response Text:', jqXHR.responseText);
                    console.log('Status:', jqXHR.status);
                    console.log('Status Text:', jqXHR.statusText);
                    alert('Error: Unable to update item. Check console for details.');
                }
            });
        });

        // JavaScript code to handle form submission
        $('#addItem').on('click', function() {
            var form = $('#addForm');
            var formData = new FormData(form[0]);

            $.ajax({
                url: 'includes/add_items.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Team added successfully');
                        $('#addModal').modal('hide');
                        // Optionally, refresh the table or page
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error: Unable to add team');
                }
            });
        });

        // JavaScript code to handle delete button click
        $(document).on('click', '.delete-btn', function() {
    var id = $(this).data('id');
    var table = $(this).data('table');

    if (confirm('Are you sure you want to delete this item from table ' + table + ' with ID ' + id + '?')) {
        $.ajax({
            url: 'includes/delete_item.php',
            method: 'POST',
            data: { id: id, table: table },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Item deleted successfully from table ' + table + ' with ID ' + id);
                    // Optionally, refresh the table or page
                } else {
                    alert('Error: ' + response.message + ' (Table: ' + table + ', ID: ' + id + ')');
                }
            },
            error: function() {
                alert('Error: Unable to delete item from table ' + table + ' with ID ' + id);
            }
        });
    }
});

$(document).ready(function() {
    $('#generateSchedule').on('click', function() {
        var $button = $(this);
        var $status = $('#scheduleGenerationStatus');

        $button.prop('disabled', true).text('Generating...');
        $status.text('Generating schedule...').removeClass('text-success text-danger').addClass('text-warning');

        $.ajax({
            url: 'includes/run_scheduler.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $status.text('Schedule generated successfully!').removeClass('text-warning').addClass('text-success');
                    
                    // Update metrics
                    $('#initialPopulationSize').text(response.initialPopulationSize);
                    $('#crossoverCount').text(response.crossoverCount);
                    $('#mutationCount').text(response.mutationCount);
                    $('#conflictCounts').text(response.conflictCounts.join(', '));
                    $('#fitnessScores').text(response.fitnessScores.join(', '));

                    setTimeout(function() {
                        //location.reload();
                    }, 2000);
                } else {
                    $status.text('Error: ' + response.message).removeClass('text-warning').addClass('text-danger');
                    $button.prop('disabled', false).text('Generate Defense Schedule');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                if (jqXHR.responseText) {
                    console.error('Server Response:', jqXHR.responseText);
                }
                $status.text('An error occurred while generating the schedule.').removeClass('text-warning').addClass('text-danger');
                $button.prop('disabled', false).text('Generate Defense Schedule');
            }
        });
    });
});
    });
    // Define addNewTeamMember function globally
    function addNewTeamMember() {
        console.log('addNewTeamMember function called');
        $.ajax({
            url: 'includes/get_users.php',
            method: 'GET',
            dataType: 'json',
            success: function(users) {
                console.log('Users fetched:', users);
                var newMemberHtml = `
                <div class="mb-3 row team-member">
                    <div class="col-sm-5">
                        <select class="form-select" name="new_user_id[]">
                            <option value="">Select a user</option>
                            ${users.map(user => `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <select class="form-select" name="new_role[]">
                            <option value="adviser">Adviser</option>
                            <option value="leader">Leader</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                    </div>
                </div>
            `;
                console.log('New member HTML:', newMemberHtml);
                $('#teamMembers').append(newMemberHtml);
                console.log('New member added to DOM');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching users:', textStatus, errorThrown);
            }
        });
    }
    // Remove team member functionality
    $(document).on('click', '.remove-member', function() {
        var teamMember = $(this).closest('.team-member');
        var userId = teamMember.data('user-id');
        var teamId = $('input[name="id"]').val();

        if (userId && teamId) {
            if (confirm('Are you sure you want to remove this team member? This action cannot be undone.')) {
                $.ajax({
                    url: 'includes/remove_team_member.php',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        team_id: teamId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            teamMember.remove();
                            alert('Team member removed successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error: Unable to remove team member');
                    }
                });
            }
        } else {
            // If it's a new member (not yet saved to database), just remove from form
            teamMember.remove();
        }
    });
</script>