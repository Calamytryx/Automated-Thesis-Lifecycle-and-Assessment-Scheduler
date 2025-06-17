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

// Function to fetch all users with college restriction
function fetchAllUsers($pdo)
{
    $userId = $_SESSION['id'];
    $usertype = $_SESSION['usertype'];
    
    // If superadmin or non-admin, no restrictions
    if ($userId === 0 || $usertype != 0) {
        $stmt = $pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get admin's college
    require_once '../assets/includes/auth_functions.php';
    $userCollege = get_user_college($pdo, $userId);
    
    if (!$userCollege) {
        // If we can't determine the admin's college, return empty result
        return [];
    }
    
    // Get users from the same college
    $stmt = $pdo->prepare("
        SELECT u.* FROM users u
        LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' 
            THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
        WHERE p.college = :college OR u.id = :user_id
    ");
    $stmt->execute([
        ':college' => $userCollege,
        ':user_id' => $userId // Always include the current user
    ]);
    
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

// Function to fetch all teams with college restriction
function fetchAllTeams($pdo)
{
    $userId = $_SESSION['id'];
    $usertype = $_SESSION['usertype'];
    
    // If superadmin or non-admin, no restrictions
    if ($userId === 0 || $usertype != 0) {
        $stmt = $pdo->prepare("SELECT t.*, rt.title FROM teams t LEFT JOIN research_titles rt ON t.id = rt.team_id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get admin's college
    require_once '../assets/includes/auth_functions.php';
    $userCollege = get_user_college($pdo, $userId);
    
    if (!$userCollege) {
        // If we can't determine the admin's college, return empty result
        return [];
    }
    
    // Get teams from the same college
    $stmt = $pdo->prepare("
        SELECT t.*, rt.title FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        JOIN programs p ON t.program = p.id
        WHERE p.college = :college
    ");
    $stmt->execute([':college' => $userCollege]);
    
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
                // Check if the clicked element is a Bootstrap pill trigger
                if (event.target.hasAttribute('data-bs-toggle') && event.target.getAttribute('data-bs-toggle') === 'pill') {
                    // Store the ID of the clicked tab-pane (only if it's a pill)
                    const clickedTabId = event.target.getAttribute('href').substring(1);
                    localStorage.setItem('activeTab', clickedTabId);
                }
                // For links not intended as tabs (like ../files), do nothing with localStorage
            });
        });
    });
</script>

<main role="main" class="container-fluid p-0"> 
    <div class="row">
        <!-- <div class="col-sm-3">
            <?php //include('../assets/layouts/profile-card.php'); 
            ?>
        </div> -->
        <div class="col-sm-12 p-0">
            <?php if ($_SESSION['usertype'] == 0): ?>                <!-- Admin dashboard content -->
                <div class="row g-0" style="height: 100vh; overflow: hidden;">                    <div id="sidebarContainer">
                        <div class="sidebar-header d-flex justify-content-end align-items-center">
                            <button id="toggleSidebar" class="btn btn-link">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                        <div class="nav flex-column nav-pills pt-3 sidebar-nav" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <!-- Dashboard Overview -->
                            <div class="sidebar-section">
                                <div class="sidebar-category d-flex justify-content-between align-items-center">
                                    <span class="category-text">Dashboard</span>
                                </div>
                                <div class="sidebar-items">
                                    <a class="nav-link active my-1" id="overview-tab" data-bs-toggle="pill" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                                        <i class="bi bi-house me-2 hollow"></i>
                                        <i class="bi bi-house-fill me-2 filled"></i>
                                        Overview
                                    </a>
                                </div>
                            </div>

                            <!-- User Management -->
                            <div class="sidebar-section">
                                <div class="sidebar-category">
                                    User Management
                                </div>
                                <div class="sidebar-items">
                                    <a class="nav-link my-1" id="users-tab" data-bs-toggle="pill" href="#users" role="tab" aria-controls="users" aria-selected="false">
                                        <i class="bi bi-people me-2 hollow"></i>
                                        <i class="bi bi-people-fill me-2 filled"></i>Users
                                    </a>
                                    <a class="nav-link my-1" id="teams-tab" data-bs-toggle="pill" href="#teams" role="tab" aria-controls="teams" aria-selected="false">
                                        <i class="bi bi-people me-2 hollow"></i>
                                        <i class="bi bi-people-fill me-2 filled"></i>Teams
                                    </a>
                                </div>
                            </div>

                            <!-- Thesis Management -->
                            <div class="sidebar-section">
                                <div class="sidebar-category">
                                    Thesis Management
                                </div>
                                <div class="sidebar-items">
                                    <a class="nav-link my-1" id="thesis-topics-tab" data-bs-toggle="pill" href="#thesis-topics" role="tab" aria-controls="thesis-topics" aria-selected="false">
                                        <i class="bi bi-book me-2 hollow"></i>
                                        <i class="bi bi-book-fill me-2 filled"></i>Thesis Topics
                                    </a>
                                    <a class="nav-link my-1" id="research-titles-tab" data-bs-toggle="pill" href="#research-titles" role="tab" aria-controls="research-titles" aria-selected="false">
                                        <i class="bi bi-file-text me-2 hollow"></i>
                                        <i class="bi bi-file-text-fill me-2 filled"></i>Research Titles
                                    </a>
                                    <a class="nav-link my-1" id="programs-tab" data-bs-toggle="pill" href="#programs" role="tab" aria-controls="programs">
                                        <i class="bi bi-mortarboard me-2 hollow"></i>
                                        <i class="bi bi-mortarboard-fill me-2 filled"></i>Programs
                                    </a>
                                </div>
                            </div>

                            <!-- Defense Management -->
                            <div class="sidebar-section">
                                <div class="sidebar-category">
                                    Defense Management
                                </div>
                                <div class="sidebar-items">
                                    <a class="nav-link my-1" id="defense-schedules-tab" data-bs-toggle="pill" href="#defense-schedules" role="tab" aria-controls="defense-schedules" aria-selected="false">
                                        <i class="bi bi-calendar-event me-2 hollow"></i>
                                        <i class="bi bi-calendar-event-fill me-2 filled"></i>Defense Schedules
                                    </a>
                                    <a class="nav-link my-1" id="rubrics-tab" data-bs-toggle="pill" href="#rubrics" role="tab" aria-controls="rubrics" aria-selected="false">
                                        <i class="bi bi-list-check me-2 hollow"></i>
                                        <i class="bi bi-list-check me-2 filled"></i>Rubrics
                                    </a>
                                    <a class="nav-link my-1" id="rubric-groups-tab" data-bs-toggle="pill" href="#rubric-groups" role="tab" aria-controls="rubric-groups" aria-selected="false">
                                        <i class="bi bi-list-columns me-2 hollow"></i>
                                        <i class="bi bi-list-columns-reverse me-2 filled"></i>Rubric Groups 
                                    </a>
                                    <a class="nav-link my-1" id="evaluations-tab" data-bs-toggle="pill" href="#evaluations" role="tab" aria-controls="evaluations" aria-selected="false">
                                        <i class="bi bi-star me-2 hollow"></i>
                                        <i class="bi bi-star-fill me-2 filled"></i>Evaluations
                                    </a>
                                    <a class="nav-link my-1" id="requirements-tab" data-bs-toggle="pill" href="#requirements" role="tab" aria-controls="requirements" aria-selected="false">
                                        <i class="bi bi-check-square me-2 hollow"></i>
                                        <i class="bi bi-check-square-fill me-2 filled"></i>Requirements
                                    </a>
                                </div>
                            </div>

                            <!-- Files -->
                            <div class="sidebar-section">
                                <div class="sidebar-category">
                                    File Management
                                </div>
                                <div class="sidebar-items">
                                    <a class="nav-link my-1" href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/files'; ?>" target="_blank">
                                        <i class="bi bi-folder2-open me-2 hollow"></i>
                                        <i class="bi bi-folder2-open me-2 filled"></i>Files
                                    </a>
                                </div>
                            </div>
                              <!-- Settings -->
                            <div class="sidebar-section">
                                <div class="sidebar-category">
                                    System
                                </div>
                                <div class="sidebar-items">
                                    <a class="nav-link my-1" id="env-variables-tab" data-bs-toggle="pill" href="#env-variables" role="tab" aria-controls="env-variables" aria-selected="false">
                                        <i class="bi bi-gear me-2 hollow"></i>
                                        <i class="bi bi-gear-fill me-2 filled"></i>Content Management
                                    </a>
                                    <a class="nav-link my-1" href="https://php-myadmin.net/login.php?2=icei_38697196wejghelqwdtg3e54gVGtSWk5FOUVXWHBPUkZFelRWaDNhRWxUUldoSldIZzRaa2g0T0daSWVEaG1TSGhOWTIxa2FsSnNSbXRWTW1jd1RUQjRhMk5xVVQwPQ==wejghelqwdtg3e54gsql302.iceiy.comwejghelqwdtg3e54gicei_38697196_coecsathesis&db=icei_38697196_coecsathesis" target="_blank"> <!-- Removed role="tab" -->
                                        <i class="bi bi-database me-2 hollow"></i>
                                        <i class="bi bi-database-fill me-2 filled"></i>DataBase
                                    </a>
                                    <a class="nav-link my-1" id="guide-tab" data-bs-toggle="pill" href="#guide" role="tab" aria-controls="guide" aria-selected="false">
                                        <i class="bi bi-book me-2 hollow"></i>
                                        <i class="bi bi-book-fill me-2 filled"></i>Guide
                                    </a>
                                </div>
                            </div>
                        </div>                        <!-- User Profile Section at bottom -->
                        <div class="profile-footer">
                            <a href="../profile" class="profile-container" title="View Profile" style="text-decoration: none; color: inherit;">
                                <?php if(isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                                    <img src="../assets/uploads/users/<?php echo $_SESSION['profile_image']; ?>" alt="<?php echo $_SESSION['username']; ?>">
                                <?php else: ?>
                                    <img src="../assets/images/sample-pic.png" alt="<?php echo $_SESSION['username']; ?>">
                                <?php endif; ?>
                                
                                <div class="user-info">
                                    <p class="user-name"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
                                    <p class="user-role"><?php echo $_SESSION['usertype'] == 0 ? "Administrator" : "User"; ?></p>
                                </div>
                            </a>
                            
                            <a href="../logout/" class="logout-btn" title="Logout">
                                <i class="bi bi-power"></i>
                            </a>
                        </div>
                    </div>                    <div id="mainContent">
                        <div class="tab-content" id="v-pills-tabContent">
                            <?php include 'includes/tabs/overview_tab.php'; ?>

                            <?php include 'includes/tabs/users_tab.php'; ?>
                            <?php include 'includes/tabs/teams_tab.php'; ?>

                            <?php include 'includes/tabs/thesis_topics_tab.php'; ?>
                            <?php include 'includes/tabs/research_titles_tab.php'; ?>

                            <?php include 'includes/tabs/defense_schedules_tab.php'; ?>
                            <?php include 'includes/tabs/rubrics_tab.php'; ?>

                            <?php include 'includes/tabs/programs_tab.php'; ?>

                            <?php include 'includes/tabs/rubric_groups_tab.php'; ?>
                            <?php include 'includes/tabs/evaluations_tab.php'; ?>
                            <?php include 'includes/tabs/requirements_tab.php'; ?>
                            
                            <?php include 'includes/tabs/env_variables_tab.php'; ?>
                            <?php include 'includes/tabs/guide_tab.php'; ?>
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

            
 <!-- Page Content Manager JS -->
<script>
    // Reset sidebar functionality to fix toggle issue
    $(document).ready(function() {
        // IMPORTANT FIX: The sidebar toggle was being attached multiple times in app.js.php
        // causing the toggle to be ineffective. This script removes all click handlers
        // and establishes a single handler with proper functionality.
          // Remove all click handlers from the toggle button first        $('#toggleSidebar').off('click');
          // Add a single click handler
        $('#toggleSidebar').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            
            $('#sidebarContainer').toggleClass('collapsed');
            $('#mainContent').toggleClass('expanded');
            $(this).toggleClass('collapsed');            // Update icon rotation
            if ($('#sidebarContainer').hasClass('collapsed')) {
                $(this).find('i').css('transform', 'rotate(180deg)');
                $('body').addClass('has-collapsed-sidebar');
            } else {
                $(this).find('i').css('transform', 'rotate(0deg)');
                $('body').removeClass('has-collapsed-sidebar');
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', $('#sidebarContainer').hasClass('collapsed'));
        });
        
        // Check localStorage for saved sidebar state on page load
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';        if (sidebarCollapsed) {
            $('#sidebarContainer').addClass('collapsed');
            $('#mainContent').addClass('expanded');
            $('#toggleSidebar').addClass('collapsed');
            $('#toggleSidebar').find('i').css('transform', 'rotate(180deg)');
            $('body').addClass('has-collapsed-sidebar');
        }
        
        console.log('Sidebar toggle functionality reset successfully');
    });
    
    $(document).ready(function() {
        // Improved Summernote WYSIWYG editor initialization with full features
        function initSummernote() { 
            if ($('.summernote').length) {
                try {
                    // Destroy if already initialized to prevent conflicts
                    if ($('.summernote').summernote) {
                        $('.summernote').summernote('destroy');
                    }
                     
                    // Initialize with lite version - comprehensive configuration
                    $('.summernote').summernote({
                        height: 300,
                        minHeight: 150,
                        maxHeight: 600,
                        placeholder: 'Write your content here...',
                        tabsize: 2,
                        dialogsInBody: true,  // Important for modals
                        disableDragAndDrop: false,
                        styleTags: [
                            'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                            { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
                            { title: 'Pre', tag: 'pre', className: 'pre', value: 'pre' }
                        ],
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'italic', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        popover: {
                            image: [
                                ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                                ['float', ['floatLeft', 'floatRight', 'floatNone']],
                                ['remove', ['removeMedia']]
                            ],
                            link: [
                                ['link', ['linkDialogShow', 'unlink']]
                            ],
                            table: [
                                ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                                ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
                            ],
                            air: [
                                ['color', ['color']],
                                ['font', ['bold', 'underline', 'clear']],
                                ['para', ['ul', 'paragraph']]
                            ]
                        },
                        callbacks: {
                            onInit: function() {
                                console.log('Summernote initialized successfully');
                            },
                            onImageUpload: function(files) {
                                // You can implement image upload functionality here
                                alert('Image upload not yet implemented. Please use external image URLs.');
                            }
                        }
                    });
                    
                    // Fix common issues with dialog buttons in Bootstrap 5
                    $(document).on('click', '.note-modal .note-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                    
                } catch (e) {
                    console.error("Error initializing Summernote:", e);
                }
            }
        }

        // Auto-generate slug from title
        $('#page_title').on('keyup', function() {
            let title = $(this).val();
            let slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-')     // Replace spaces with hyphens
                .replace(/-+/g, '-');     // Replace multiple hyphens with a single hyphen
            
            $('#page_slug').val(slug);
        });

        // Add Page Content Button Click Handler - Use class selector for ALL page content buttons
        $(document).on('click', '.page-content-btn', function(e) {
            // Prevent default behavior and stop event propagation
            e.preventDefault();
            e.stopPropagation();
            
            // Reset the form
            $('#pageContentForm')[0].reset();
            $('#page_id').val('');
            
            // Update modal title
            $('#pageContentModalLabel').text('Add Page Content');
            
            // Show the modal
            $('#pageContentModal').modal('show');
            
            // Initialize Summernote after the modal is shown
            $('#pageContentModal').on('shown.bs.modal', function() {
                initSummernote();
            });
            
            // Return false to prevent other handlers from executing
            return false;
        });

        // Edit Page Button Click Handler
        $(document).on('click', '.edit-page-btn', function(e) {
            // Prevent default behavior and stop event propagation
            e.preventDefault();
            e.stopPropagation();
            
            const pageId = $(this).data('id');
            
            // Update modal title
            $('#pageContentModalLabel').text('Edit Page Content');
            
            // Fetch page details
            $.ajax({
                url: 'includes/page_content/get_page.php',
                type: 'GET',
                data: { id: pageId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const page = response.data;
                        
                        // Populate the form
                        $('#page_id').val(page.id);
                        $('#page_title').val(page.title);
                        $('#page_slug').val(page.slug);
                        $('#page_status').val(page.status);
                        
                        // Show the modal
                        $('#pageContentModal').modal('show');
                        
                        // Initialize Summernote and set content after the modal is shown
                        $('#pageContentModal').on('shown.bs.modal', function() {
                            initSummernote();
                            $('.summernote').summernote('code', page.content);
                        });
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showToast('Error', 'Failed to load page details: ' + error, 'error');
                }
            });
            
            // Return false to prevent other handlers from executing
            return false;
        });

        // Save Page Content Handler
        $('#savePageContent').on('click', function() {
            // Validate form
            const form = $('#pageContentForm');
            
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }
            
            // Get form data
            const pageData = {
                page_id: $('#page_id').val(),
                title: $('#page_title').val(),
                slug: $('#page_slug').val(),
                content: $('.summernote').summernote('code'),
                status: $('#page_status').val()
            };
            
            // Log the data being sent (for debugging)
            console.log('Saving page data:', pageData);
            
            // Save data
            $.ajax({
                url: 'includes/page_content/save_page.php',
                type: 'POST',
                data: JSON.stringify(pageData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', response.message, 'success');
                        
                        // Close modal and reload page to show updated data
                        $('#pageContentModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error response:', xhr.responseText);
                    let errorMessage = 'Failed to save page: ' + error;
                    
                    try {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            // Try to parse the response text as JSON
                            const errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    showToast('Error', errorMessage, 'error');
                }
            });
        });

        // Delete Page Button Click Handler
        $(document).on('click', '.delete-page-btn', function(e) {
            // Prevent default behavior
            e.preventDefault(); 
            e.stopPropagation();
            
            const pageId = $(this).data('id');
            $('#delete_page_id').val(pageId);
            $('#deletePageModal').modal('show');
            
            // Return false to prevent other handlers from executing
            return false;
        });

        // Confirm Delete Page Handler
        $('#confirmDeletePage').on('click', function() {
            const pageId = $('#delete_page_id').val();
            
            $.ajax({
                url: 'includes/page_content/delete_page.php',
                type: 'POST',
                data: JSON.stringify({ page_id: pageId }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', response.message, 'success');
                        
                        // Close modal and reload page
                        $('#deletePageModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Failed to delete page: ' + error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showToast('Error', errorMessage, 'error');
                }
            });
        });

        // Helper function to show toast notifications
        function showToast(title, message, type) {
            // Check if toastContainer exists, if not create it
            if ($('#toastContainer').length === 0) {
                $('body').append('<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>');
            }
            
            // Create a unique ID for this toast
            const toastId = 'toast-' + Date.now();
            
            // Determine the appropriate Bootstrap class based on type
            let bgClass = 'bg-primary';
            switch (type) {
                case 'success':
                    bgClass = 'bg-success';
                    break;
                case 'error':
                    bgClass = 'bg-danger';
                    break;
                case 'warning':
                    bgClass = 'bg-warning';
                    break;
                case 'info':
                    bgClass = 'bg-info';
                    break;
            }
            
            // Create the toast HTML
            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                    <div class="toast-header ${bgClass} text-white">
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            // Append the toast to the container
            $('#toastContainer').append(toastHtml);
            
            // Initialize and show the toast
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove the toast from DOM after it's hidden
            $(toastElement).on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
    });
</script>