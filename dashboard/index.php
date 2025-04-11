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

// Assume $active_tab is set based on user interaction or the default tab.
$active_tab = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'overview_tab';  // Default to 'overview_tab'

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
    $stmt = $pdo->prepare("SELECT * FROM evaluation_per_panel");
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
    $stmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
    $stmt->execute([$team_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['name'] : 'Unknown Team';
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

// Function to fetch evaluation details with team name and user names
function fetchEvaluationDetails($pdo)
{
    $query = "
        SELECT 
            ep.id,
            ds.team_id,
            t.name AS team_name,
            e.first_name AS evaluator_first_name,
            e.last_name AS evaluator_last_name,
            s.first_name AS student_first_name,
            s.last_name AS student_last_name,
            ep.group_score,
            ep.solo_score,
            ep.total_score,
            ep.comments
        FROM 
            evaluation_per_panel ep
        JOIN 
            defense_schedules ds ON ep.defense_schedule_id = ds.id
        JOIN 
            teams t ON ds.team_id = t.id
        JOIN 
            users e ON ep.evaluator_id = e.id
        JOIN 
            users s ON ep.student_id = s.id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$users = fetchAllUsers($pdo);
$thesisTopics = fetchAllThesisTopics($pdo);
$researchTitles = fetchAllResearchTitles($pdo);
$defenseSchedules = fetchAllDefenseSchedules($pdo);
$rubrics = fetchAllRubrics($pdo);
$teams = fetchAllTeams($pdo);
$requirements = fetchAllRequirements($pdo);
$evaluations = fetchEvaluationDetails($pdo);
$envVariables = fetchAllEnvVariables($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = handleEditSubmission($pdo, $_POST['table'], $_POST['id'], $_POST);
    if ($result) {
        $_SESSION['SUCCESS'] = "Update successful";
    } else {
        $_SESSION['ERROR'] = "Update failed";
    }
}

?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if there's a previously selected tab stored in localStorage
        const activeTab = localStorage.getItem("activeTab");

        // If there is a stored active tab, activate it
        if (activeTab) {
            // Deactivate all tab-panes and nav-links
            const allTabPanes = document.querySelectorAll('.tab-pane');
            const allNavLinks = document.querySelectorAll('.nav-link');

            allTabPanes.forEach(pane => {
                pane.classList.remove("show", "active");
            });

            allNavLinks.forEach(link => {
                link.classList.remove("active");
            });

            // Activate the tab and its content
            const activeTabPane = document.getElementById(activeTab);
            const activeNavLink = document.querySelector(`.nav-link[href="#${activeTab}"]`);
            console.log("Active tab:", activeTab);
            console.log("Active tab pane:", activeTabPane);
            console.log("Active nav link:", activeNavLink);

            if (activeTabPane) {
                activeTabPane.classList.add("show", "active");
                
                // Trigger the shown.bs.tab event manually to ensure content is loaded
                if (activeNavLink) {
                    const event = new Event('shown.bs.tab');
                    activeNavLink.dispatchEvent(event);
                }
            } else {
                document.getElementById('overview').classList.add("show", "active");
                
                // Trigger the shown.bs.tab event manually for the overview tab
                const overviewTab = document.getElementById('overview-tab');
                if (overviewTab) {
                    const event = new Event('shown.bs.tab');
                    overviewTab.dispatchEvent(event);
                }
            }
            if (activeNavLink) {
                activeNavLink.classList.add("active");
            } else {
                document.getElementById('overview-tab').classList.add("active");
            }
        } else {
            // If no active tab is stored, activate the overview tab and trigger its event
            document.getElementById('overview').classList.add("show", "active");
            document.getElementById('overview-tab').classList.add("active");
            
            // Trigger the shown.bs.tab event manually for the overview tab
            const overviewTab = document.getElementById('overview-tab');
            if (overviewTab) {
                const event = new Event('shown.bs.tab');
                overviewTab.dispatchEvent(event);
            }
        }

        // Add event listener to tabs to update localStorage when clicked
        const tabs = document.querySelectorAll('#v-pills-tab .nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('click', function(event) {
                // Store the ID of the clicked tab-pane
                const clickedTabId = event.target.getAttribute('href').substring(1);
                localStorage.setItem('activeTab', clickedTabId);
            });
        });
    });
</script>

<main role="main" class="container"> 
    <div class="row">
        <!-- <div class="col-sm-3">
            <?php //include('../assets/layouts/profile-card.php'); 
            ?>
        </div> -->
        <div class="col-sm-12">
            <?php if ($_SESSION['usertype'] == 0): ?>
                <!-- Admin dashboard content -->
                <div class="row g-0">
                    <div id="sidebarContainer">
                        <div class="d-flex align-items-center mt-3 pb-3 sidebar-header">
                            <div class="lh-100 d-flex justify-content-between w-100">
                                <h2 class="mb-0 lh-100 dashboard-title"><?php echo $_SESSION['usertype'] == 0 ? "Admin Dashboard" : "User Dashboard"; ?></h2>
                                <button id="toggleSidebar" class="btn btn-link">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            </div>
                        </div>
                        <div class="nav flex-column nav-pills pt-3 sidebar-nav" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <!-- Dashboard Overview -->
                            <div class="sidebar-category mb-2">Dashboard</div>
                            <a class="nav-link active my-1" id="overview-tab" data-bs-toggle="pill" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                                <i class="fas fa-home me-2"></i>Overview
                            </a>

                            <!-- User Management -->
                            <div class="sidebar-category mb-2 mt-3">User Management</div>
                            <a class="nav-link my-1" id="users-tab" data-bs-toggle="pill" href="#users" role="tab" aria-controls="users" aria-selected="false">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                            <a class="nav-link my-1" id="teams-tab" data-bs-toggle="pill" href="#teams" role="tab" aria-controls="teams" aria-selected="false">
                                <i class="fas fa-user-friends me-2"></i>Teams
                            </a>

                            <!-- Thesis Management -->
                            <div class="sidebar-category mb-2 mt-3">Thesis Management</div>
                            <a class="nav-link my-1" id="thesis-topics-tab" data-bs-toggle="pill" href="#thesis-topics" role="tab" aria-controls="thesis-topics" aria-selected="false">
                                <i class="fas fa-book me-2"></i>Thesis Topics
                            </a>
                            <a class="nav-link my-1" id="research-titles-tab" data-bs-toggle="pill" href="#research-titles" role="tab" aria-controls="research-titles" aria-selected="false">
                                <i class="fas fa-file-alt me-2"></i>Research Titles
                            </a>

                            <!-- Defense Management -->
                            <div class="sidebar-category mb-2 mt-3">Defense Management</div>
                            <a class="nav-link my-1" id="defense-schedules-tab" data-bs-toggle="pill" href="#defense-schedules" role="tab" aria-controls="defense-schedules" aria-selected="false">
                                <i class="fas fa-calendar-alt me-2"></i>Defense Schedules
                            </a>
                            <a class="nav-link my-1" id="rubrics-tab" data-bs-toggle="pill" href="#rubrics" role="tab" aria-controls="rubrics" aria-selected="false">
                                <i class="fas fa-list-alt me-2"></i>Rubrics
                            </a>
                            <a class="nav-link my-1" id="evaluations-tab" data-bs-toggle="pill" href="#evaluations" role="tab" aria-controls="evaluations" aria-selected="false">
                                <i class="fas fa-star me-2"></i>Evaluations
                            </a>
                            <a class="nav-link my-1" id="requirements-tab" data-bs-toggle="pill" href="#requirements" role="tab" aria-controls="requirements" aria-selected="false">
                                <i class="fas fa-tasks me-2"></i>Requirements
                            </a>

                            <!-- Requirements -->
                            <div class="sidebar-category mb-2 mt-3">System</div>
                            <a class="nav-link my-1" id="env-variables-tab" data-bs-toggle="pill" href="#env-variables" role="tab" aria-controls="env-variables" aria-selected="false">
                                <i class="fas fa-cog me-2"></i>Content Management
                            </a>
                            <a class="nav-link my-1" href="../files" target="_blank" role="tab">
                                <i class="fas fa-folder-open me-2"></i>Files
                            </a>
                            <a class="nav-link my-1" href="../../phpmyadmin" target="_blank" role="tab">
                                <i class="fas fa-database me-2"></i>DataBase
                            </a>
                        </div>
                    </div>
                    <div id="mainContent">
                        <div class="tab-content" id="v-pills-tabContent">
                            <?php include 'includes/tabs/overview_tab.php'; ?>

                            <?php include 'includes/tabs/users_tab.php'; ?>
                            <?php include 'includes/tabs/teams_tab.php'; ?>

                            <?php include 'includes/tabs/thesis_topics_tab.php'; ?>
                            <?php include 'includes/tabs/research_titles_tab.php'; ?>

                            <?php include 'includes/tabs/defense_schedules_tab.php'; ?>
                            <?php include 'includes/tabs/rubrics_tab.php'; ?>
                            <?php include 'includes/tabs/evaluations_tab.php'; ?>
                            <?php include 'includes/tabs/requirements_tab.php'; ?>
                            
                            <?php include 'includes/tabs/env_variables_tab.php'; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Regular user dashboard content -->
                <script>
                    window.location.href = '../home';
                </script>
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
                    <!-- Hidden inputs for table and id -->
                    <input type="hidden" name="table" id="editTable">
                    <input type="hidden" name="id" id="editId">

                    <!-- Fields for defense schedule -->
                    <div class="mb-3">
                        <label for="editTeam" class="form-label">Team</label>
                        <select class="form-select" id="editTeam" name="team_id" required>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPanelists" class="form-label">Panelists</label>
                        <select class="form-select" id="editPanelists" name="panelist_ids[]" multiple required>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editRoom" class="form-label">Room</label>
                        <input type="text" class="form-control" id="editRoom" name="room" required>
                    </div>
                    <div class="mb-3">
                        <label for="editScheduleDate" class="form-label">Schedule Date</label>
                        <input type="date" class="form-control" id="editScheduleDate" name="schedule_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStartTime" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="editStartTime" name="start_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndTime" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="editEndTime" name="end_time" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mod-sec-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary mod-pri-btn" id="saveChanges">Save changes</button>
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
<!-- AI GEMINI MODULE -->
<!-- Main Module JS -->
<script type="module" src="../assets/js/mainModule.js"></script>
<!-- app.js -->
<script type="module" src="../assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<?php require 'app.js.php'; ?>
