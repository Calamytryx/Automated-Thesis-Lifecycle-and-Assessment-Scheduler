<?php

/**
 * Home Page
 * 
 * This file serves as the home page for the COECSAT Thesis Management System.
 * It includes various sections and tools for users to interact with, such as 
 * thesis topic decision, research title acceptance, scheduling system, and 
 * requirement checker.
 * 
 * @file /c:/xampp/htdocs/coecsathesis/home/index.php
 * 
 * @constant TITLE The title of the page.
 * 
 * @include ../assets/layouts/header.php
 * @include ../assets/layouts/footer.php
 * 
 * @function check_verified Verifies if the user is authenticated and verified.
 * 
 * @section Main Content
 * The main content of the page is divided into several tabs:
 * 
 * - Thesis Topic Decision: Allows users to select a field and get the latest thesis topics.
 * - Research Title Acceptance: Provides a form for users to check the uniqueness of their proposed research title.
 * - Scheduling System: Displays the user's schedule and defense schedule.
 * - Requirement Checker: Provides a checklist for users to check their document requirements.
 * 
 * @section Scripts
 * The following scripts are included for functionality:
 * 
 * - mainModule.js: Main module JavaScript file.
 * - app.js: Application-specific JavaScript file.
 * - marked.min.js: Library for parsing Markdown.
 */
define('TITLE', "Home");
include '../assets/layouts/header.php';
check_verified();
include '../assets/setup/db.inc.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// echo '<pre>';
// print_r($_SESSION['team_id'][0]);
// echo '</pre>';
?>

<script>
// Move fetchTeamOverview to global scope
function fetchTeamOverview(teamId = null) {
    const url = teamId ? `includes/get_team_overview.php?team_id=${teamId}` : 'includes/get_team_overview.php';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const teamOverviewContent = document.getElementById('teamOverviewContent');

                let content = ''; // Team Selector (if multiple teams available)
                if (data.teams.length > 1) {
                    content += `
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card team-selector-card">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-3 text-muted">Select Team</h6>
                                                <select id="teamSelector" class="form-select">
                                                    ${data.teams.map(team => 
                                                        `<option value="${team.id}" ${team.id == data.selectedTeam.id ? 'selected' : ''}>${team.name}</option>`
                                                    ).join('')}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                }

                // Current Team Info
                content += `
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card team-info-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="card-title mb-1">${data.selectedTeam.name}</h5>
                                                    <p class="text-muted mb-0">Team Overview</p>
                                                </div>
                                                <div class="text-end">
                                                    <div class="d-flex gap-3">
                                                        <div class="text-center">
                                                            <div class="h4 mb-0 text-success">${data.completedCount}</div>
                                                            <small class="text-muted">Completed</small>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="h4 mb-0 text-warning">${data.pendingCount}</div>
                                                            <small class="text-muted">Pending</small>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="h4 mb-0">${data.totalRequirements}</div>
                                                            <small class="text-muted">Total</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                // Progress Overview
                const progressPercentage = data.totalRequirements > 0 ? Math.round((data.completedCount / data
                    .totalRequirements) * 100) : 0;
                content += `
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card progress-overview-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-subtitle mb-0">Overall Progress</h6>
                                                <span class="badge bg-dark">${progressPercentage}%</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: ${progressPercentage}%" aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `; // Requirements by Category
                content += `
                            <div class="row">
                                <!-- Completed Requirements -->
                                <div class="col-md-6 mb-4">
                                    <div class="card requirements-completed-card h-100">
                                        <div class="card-header requirements-header bg-transparent pb-3">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Completed</h6>
                                                    <small class="text-muted">${data.completedCount} requirements</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-3">
                                            ${data.requirements.completed.length > 0 
                                                ? data.requirements.completed.map(req => `
                                                    <div class="d-flex align-items-center py-2 border-bottom border-light">
                                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                        <span class="flex-grow-1">${req.name}</span>
                                                    </div>
                                                `).join('')
                                                : '<p class="text-muted mb-0">No completed requirements yet</p>'
                                            }
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Requirements -->
                                <div class="col-md-6 mb-4">
                                    <div class="card requirements-pending-card h-100">
                                        <div class="card-header requirements-header bg-transparent pb-3">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Pending</h6>
                                                    <small class="text-muted">${data.pendingCount} requirements</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-3">
                                            ${data.requirements.pending.length > 0 
                                                ? data.requirements.pending.map(req => {
                                                    const statusIcon = req.status === 'submitted' ? 'bi-hourglass-split text-info' : 
                                                                      req.status === 'rejected' ? 'bi-x-circle-fill text-danger' : 
                                                                      'bi-circle text-muted';
                                                    const statusText = req.status === 'submitted' ? 'Submitted' : 
                                                                      req.status === 'rejected' ? 'Rejected' : 
                                                                      'Not Started';
                                                    return `
                                                        <div class="d-flex align-items-center py-2 border-bottom border-light">
                                                            <i class="bi ${statusIcon} me-2"></i>
                                                            <div class="flex-grow-1">
                                                                <div>${req.name}</div>
                                                                <small class="text-muted">${statusText}</small>
                                                            </div>
                                                        </div>
                                                    `;
                                                }).join('')
                                                : '<p class="text-muted mb-0">All requirements completed!</p>'
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `; // Defense Schedule
                content += `
                            <div class="row">
                                <div class="col-12">
                                    <div class="card defense-schedule-card">
                                        <div class="card-header defense-schedule-header bg-transparent pb-3">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Next Defense Schedule</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-3">
                                            ${data.defense 
                                                ? `
                                                    <div class="row g-3">
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="text-center p-3 bg-light rounded">
                                                                <i class="bi bi-calendar3 text-muted mb-2 d-block"></i>
                                                                <div class="fw-semibold">${new Date(data.defense.schedule_date).toLocaleDateString()}</div>
                                                                <small class="text-muted">Date</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="text-center p-3 bg-light rounded">
                                                                <i class="bi bi-clock text-muted mb-2 d-block"></i>
                                                                <div class="fw-semibold">${data.defense.start_time} - ${data.defense.end_time}</div>
                                                                <small class="text-muted">Time</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="text-center p-3 bg-light rounded">
                                                                <i class="bi bi-geo-alt text-muted mb-2 d-block"></i>
                                                                <div class="fw-semibold">${data.defense.room || 'TBA'}</div>
                                                                <small class="text-muted">Room</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `
                                                : `
                                                    <div class="text-center py-4">
                                                        <i class="bi bi-calendar-x text-muted mb-2" style="font-size: 2rem;"></i>
                                                        <p class="text-muted mb-0">No defense scheduled yet</p>
                                                    </div>
                                                `
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                teamOverviewContent.innerHTML = content;

                // Add event listener for team selector after content is inserted
                const teamSelector = document.getElementById('teamSelector');
                if (teamSelector) {
                    teamSelector.addEventListener('change', function() {
                        fetchTeamOverview(this.value);
                    });
                }
            } else {
                console.error(data.message);
                document.getElementById('teamOverviewContent').innerHTML = `
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${data.message}
                        </div>
                    `;
            }
        })
        .catch(error => {
            console.error('Error fetching team overview:', error);
            document.getElementById('teamOverviewContent').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Failed to load team overview. Please try again.
                    </div>
                `;
        });
}

document.addEventListener("DOMContentLoaded", function() {
    // Check if there's a previously selected tab stored in localStorage
    const activeTab = localStorage.getItem("activeTab") || "overview";

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

    if (activeTabPane) {
        activeTabPane.classList.add("show", "active");
    } else {
        document.getElementById('overview').classList.add("show", "active");
    }
    if (activeNavLink) {
        activeNavLink.classList.add("active");
    } else {
        document.getElementById('overview-link').classList.add("active");
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

    // Load team overview content when the overview tab is clicked
    document.getElementById('overview-link').addEventListener('click', function() {
        fetchTeamOverview();
    });

    // Fetch team overview content on page load if the overview tab is active
    if (activeTab === "overview") {
        fetchTeamOverview();
    }

    // Handle view mode switching between Dashboard and Calendar
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.id === 'calendar-view') {
                document.getElementById('dashboardView').style.display = 'none';
                document.getElementById('calendarView').style.display = 'block';
                // Initialize calendar for the overview tab when calendar view is selected
                setTimeout(() => {
                    initializeOverviewCalendar();
                }, 100);
            } else {
                document.getElementById('dashboardView').style.display = 'block';
                document.getElementById('calendarView').style.display = 'none';
            }
        });
    });
});

// Function to initialize calendar in the overview tab
function initializeOverviewCalendar() {
    const calendarEl = document.getElementById('calendar2');
    if (!calendarEl) return;

    // Clear any existing calendar
    calendarEl.innerHTML = '';

    // Initialize FullCalendar for the overview tab
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        events: [],
        eventClick: function(info) {
            // Handle event click
            console.log('Event clicked:', info.event);
        },
        dateClick: function(info) {
            if (calendar.view.type === 'dayGridMonth') {
                calendar.changeView('timeGridDay');
            }
            calendar.gotoDate(info.dateStr);
        }
    });

    // Fetch and display events
    $.ajax({
        url: 'includes/get_user_schedule.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const events = [];

                response.defense_schedules.forEach(defense => {
                    events.push({
                        title: defense.description,
                        start: `${defense.date}T${defense.start_time}`,
                        end: `${defense.date}T${defense.end_time}`,
                        location: defense.room,
                        eventType: 'defense'
                    });
                });

                response.user_schedules.forEach(schedule => {
                    events.push({
                        title: schedule.description,
                        start: `${getNextDateForDay(schedule.date)}T${schedule.start_time}`,
                        end: `${getNextDateForDay(schedule.date)}T${schedule.end_time}`,
                        location: schedule.room,
                        eventType: 'user'
                    });
                });

                calendar.removeAllEvents();
                calendar.addEventSource(events);
            }
        }
    });

    calendar.render();
}

// Handle requirements team selector change
function handleRequirementsTeamChange() {
    const teamSelector = document.getElementById('requirementsTeamSelector');
    if (teamSelector) {
        teamSelector.addEventListener('change', function() {
            const teamId = this.value;
            updateRequirementsList(teamId);
        });
    }
}

// Update requirements list based on selected team
function updateRequirementsList(teamId) {
    const requirementsList = document.getElementById('requirementsList');
    if (!requirementsList) return;

    // Show loading state
    requirementsList.innerHTML = '<li class="list-group-item text-center"><em>Loading...</em></li>';

    // Fetch requirements for the selected team
    fetch(`includes/get_team_requirements.php?team_id=${teamId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let listHtml = '';
                if (data.requirements && data.requirements.length > 0) {
                    data.requirements.forEach(requirement => {
                        let badgeClass = 'bg-warning';
                        let badgeText = 'Pending';

                        if (requirement.status === 'approved') {
                            badgeClass = 'bg-success';
                            badgeText = 'Approved';
                        } else if (requirement.status === 'submitted') {
                            badgeClass = 'bg-info';
                            badgeText = 'Submitted';
                        } else if (requirement.status === 'rejected') {
                            badgeClass = 'bg-danger';
                            badgeText = 'Rejected';
                        }

                        const dueDate = new Date(requirement.due_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        listHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center req-li">
                                    <div>
                                        <strong>${requirement.name}</strong>
                                        <br>
                                        <small class="text-muted due-date-txt">Due: ${dueDate}</small>
                                    </div>
                                    <span class="badge ${badgeClass} rounded-pill">${badgeText}</span>
                                </li>
                            `;
                    });
                } else {
                    listHtml =
                        '<li class="list-group-item text-center text-muted"><em>No requirements found for this team</em></li>';
                }
                requirementsList.innerHTML = listHtml;
            } else {
                requirementsList.innerHTML =
                    '<li class="list-group-item text-center text-danger"><em>Error loading requirements</em></li>';
            }
        })
        .catch(error => {
            console.error('Error fetching requirements:', error);
            requirementsList.innerHTML =
                '<li class="list-group-item text-center text-danger"><em>Error loading requirements</em></li>';
        });
}

// Initialize requirements team selector when document is ready
document.addEventListener("DOMContentLoaded", function() {
    handleRequirementsTeamChange();
});
</script>
<main role="main" class="container-fluid p-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="row g-0" style="height: 100vh; overflow: hidden;">
                <div id="homeSidebarContainer">
                    <div class="home-sidebar-header d-flex justify-content-end align-items-center">
                        <!-- <div class="home-sidebar-title">
                    <h5 class="mb-0">Navigation</h5>
                </div> -->
                        <button id="toggleHomeSidebar" class="btn btn-link">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                    </div>
                    <!-- Sidebar -->
                    <div class="home-sidebar">
                        <div class="nav flex-column nav-pills home-sidebar-nav" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <!-- Dashboard Overview -->
                            <div class="home-sidebar-section">
                                <div class="home-sidebar-category">
                                    Overview
                                </div>
                                <div class="home-sidebar-items">
                                    <a class="nav-link active my-1" id="overview-link" data-bs-toggle="pill"
                                        href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                                        <i class="bi bi-house me-2 hollow"></i>
                                        <i class="bi bi-house-fill me-2 filled"></i>
                                        <span class="nav-text">Overview</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Research Management -->
                            <div class="home-sidebar-section">
                                <div class="home-sidebar-category">
                                    Research Management
                                </div>
                                <div class="home-sidebar-items">
                                    <a class="nav-link my-1" id="thesis-topic-link" data-bs-toggle="pill"
                                        href="#thesis-topic" role="tab" aria-controls="thesis-topic"
                                        aria-selected="true">
                                        <i class="bi bi-lightbulb me-2 hollow"></i>
                                        <i class="bi bi-lightbulb-fill me-2 filled"></i>
                                        <span class="nav-text">Thesis Topic Decision</span>
                                    </a>
                                    <a class="nav-link my-1" id="research-title-link" data-bs-toggle="pill"
                                        href="#research-title" role="tab" aria-controls="research-title"
                                        aria-selected="false">
                                        <i class="bi bi-check-circle me-2 hollow"></i>
                                        <i class="bi bi-check-circle-fill me-2 filled"></i>
                                        <span class="nav-text">Research Title Acceptance</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Progress Tracking -->
                            <div class="home-sidebar-section">
                                <div class="home-sidebar-category">
                                    Progress Tracking
                                </div>
                                <div class="home-sidebar-items">
                                    <a class="nav-link my-1" id="requirement-checker-link" data-bs-toggle="pill"
                                        href="#requirement-checker" role="tab" aria-controls="requirement-checker"
                                        aria-selected="false">
                                        <i class="bi bi-list-check me-2 hollow"></i>
                                        <i class="bi bi-list-check me-2 filled"></i>
                                        <span class="nav-text">Requirement Checker</span>
                                    </a>
                                    <a class="nav-link my-1" id="research-evaluation-link" data-bs-toggle="pill"
                                        href="#research-evaluation" role="tab" aria-controls="research-evaluation"
                                        aria-selected="false">
                                        <i class="bi bi-chat-dots me-2 hollow"></i>
                                        <i class="bi bi-chat-dots-fill me-2 filled"></i>
                                        <span class="nav-text">Research Evaluation</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Section at bottom -->
                    <div class="profile-footer">
                        <a href="../profile" class="profile-container" title="View Profile"
                            style="text-decoration: none; color: inherit;">
                            <?php if(isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                            <img src="../assets/uploads/users/<?php echo $_SESSION['profile_image']; ?>"
                                alt="<?php echo $_SESSION['username']; ?>">
                            <?php else: ?>
                            <img src="../assets/images/sample-pic.png" alt="<?php echo $_SESSION['username']; ?>">
                            <?php endif; ?>

                            <div class="user-info">
                                <p class="user-name">
                                    <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
                                <p class="user-role"><?php 
                            if ($_SESSION['usertype'] == 0) {
                                echo "Administrator";
                            } elseif ($_SESSION['usertype'] == 1) {
                                echo "Student";
                            } elseif ($_SESSION['usertype'] == 2) {
                                echo "Faculty";
                            } else {
                                echo "User";
                            }
                        ?></p>
                            </div>
                        </a>

                        <a href="../logout/" class="logout-btn" title="Logout">
                            <i class="bi bi-power"></i>
                        </a>
                    </div>
                </div>

                <div id="homeMainContent">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade" id="thesis-topic" role="tabpanel"
                            aria-labelledby="thesis-topic-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-lightbulb text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Thesis Topic Decision Tool</h4>
                                        <p class="text-muted mb-0">Discover trending research topics in your field of
                                            study</p>
                                    </div>
                                </div>

                                <div class="card border-0 mb-4">
                                    <div class="form-group">
                                        <label for="thesisField" class="form-label fw-semibold mb-2">Select your field
                                            of study:</label>
                                        <select id="thesisField" class="form-select form-select-lg shadow-sm">
                                            <option value="">Choose a field</option>
                                            <?php
                                    // Assuming $conn is your database connection object (e.g., PDO or mysqli)
                                    // Include your database connection file if necessary
                                    // require_once '../assets/setup/db.inc.php'; // Already included at the top of the file

                                    try {
                                        // Check if $pdo is initialized
                                        if (!isset($pdo)) {
                                            // Connection is likely handled elsewhere, or throw error
                                            throw new Exception("Database connection not available.");
                                        }

                                        $stmt = $pdo->query("SELECT college, name, specialization FROM programs ORDER BY college, name");
                                        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        $groupedPrograms = [];
                                        foreach ($programs as $program) {
                                            $groupedPrograms[$program['college']][] = $program;
                                        }

                                        foreach ($groupedPrograms as $college => $collegePrograms) {
                                            echo '<optgroup label="' . htmlspecialchars($college) . '">';
                                            foreach ($collegePrograms as $program) {
                                                // Use the program name as the base display text
                                                $displayText = htmlspecialchars($program['name']);
                                                // Use the program name as the default value
                                                $optionValue = htmlspecialchars($program['name']);

                                                // If there is a specialization, append it to the display text
                                                if (!empty($program['specialization'])) {
                                                    $displayText .= ' - ' . htmlspecialchars($program['specialization']) . '';
                                                }

                                                // Output the option tag
                                                echo '<option value="' . $optionValue . '">' . $displayText . '</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    } catch (Exception $e) {
                                        // Log error or display a user-friendly message
                                        error_log("Error fetching programs: " . $e->getMessage());
                                        echo '<option value="" disabled>Error loading programs</option>';
                                    }
                                    ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="topicsTable">
                                    <!-- The filtered topics table will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <script>
                        document.getElementById('thesisField').addEventListener('change', function() {
                            let selectedField = this.value;

                            // Create an AJAX request
                            let xhr = new XMLHttpRequest();
                            xhr.open('POST', 'includes/get_topics.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    // Update the table with the server response
                                    document.getElementById('topicsTable').innerHTML = xhr.responseText;

                                    // Add event listeners for responsive table interactions
                                    addTopicTableEventListeners();
                                }
                            };

                            // Send the selected field to the server
                            xhr.send('field=' + encodeURIComponent(selectedField));
                        });

                        // Function to add event listeners for table interactions
                        function addTopicTableEventListeners() {
                            // Add click listeners for desktop rows (hidden description column)
                            const topicRows = document.querySelectorAll('.topic-row');
                            topicRows.forEach(row => {
                                // Add mobile-clickable class for touch devices
                                if (window.innerWidth < 768) {
                                    row.classList.add('mobile-clickable');
                                }

                                row.addEventListener('click', function(e) {
                                    // Only trigger on mobile/tablet (when description column is hidden)
                                    if (window.innerWidth < 768) {
                                        e.preventDefault();
                                        const topic = this.getAttribute('data-topic');
                                        const description = this.getAttribute('data-description');
                                        showTopicModal(topic, description);
                                    }
                                });

                                // Add touch feedback for mobile
                                row.addEventListener('touchstart', function(e) {
                                    if (window.innerWidth < 768) {
                                        this.style.backgroundColor = 'var(--primary-100, #cce7ff)';
                                    }
                                });

                                row.addEventListener('touchend', function(e) {
                                    if (window.innerWidth < 768) {
                                        setTimeout(() => {
                                            this.style.backgroundColor = '';
                                        }, 150);
                                    }
                                });
                            });

                            // Add click listeners for view buttons (mobile only)
                            const viewButtons = document.querySelectorAll('.view-topic-btn');
                            viewButtons.forEach(button => {
                                button.addEventListener('click', function(e) {
                                    e.stopPropagation(); // Prevent row click
                                    const topic = this.getAttribute('data-topic');
                                    const description = this.getAttribute('data-description');
                                    showTopicModal(topic, description);
                                });
                            });

                            // Handle window resize to update mobile state
                            window.addEventListener('resize', function() {
                                topicRows.forEach(row => {
                                    if (window.innerWidth < 768) {
                                        row.classList.add('mobile-clickable');
                                    } else {
                                        row.classList.remove('mobile-clickable');
                                        row.style.backgroundColor = '';
                                    }
                                });
                            });
                        }

                        // Function to show topic modal
                        function showTopicModal(topic, description) {
                            document.getElementById('topicModalLabel').textContent = topic;
                            document.getElementById('topicModalDescription').textContent = description;

                            const modal = new bootstrap.Modal(document.getElementById('topicModal'), {
                                backdrop: true,
                                keyboard: true
                            });
                            modal.show();
                        }

                        // Function to show evaluation modal
                        function showEvaluationModal(row) {
                            const teamName = row.getAttribute('data-team');
                            const researchTitle = row.getAttribute('data-title');
                            const studentName = row.getAttribute('data-student');
                            const evaluatorName = row.getAttribute('data-evaluator');
                            const comments = row.getAttribute('data-comments');
                            const score = row.getAttribute('data-score');

                            document.getElementById('evalModalTeam').textContent = teamName;
                            document.getElementById('evalModalTitle').textContent = researchTitle;
                            document.getElementById('evalModalEvaluator').textContent = evaluatorName;
                            document.getElementById('evalModalComments').textContent = comments;
                            document.getElementById('evalModalScore').textContent = score;

                            // Show/hide student section based on user type
                            const studentSection = document.getElementById('evalModalStudentSection');
                            if (studentName && studentName !== 'null') {
                                document.getElementById('evalModalStudent').textContent = studentName;
                                studentSection.style.display = 'block';
                            } else {
                                studentSection.style.display = 'none';
                            }

                            const modal = new bootstrap.Modal(document.getElementById('evaluationModal'), {
                                backdrop: true,
                                keyboard: true
                            });
                            modal.show();
                        }

                        // Add click handlers for evaluation table rows on mobile
                        document.addEventListener('DOMContentLoaded', function() {
                            function addEvaluationTableEventListeners() {
                                const evaluationRows = document.querySelectorAll('.evaluation-row');
                                evaluationRows.forEach(row => {
                                    // Add mobile-clickable class for touch devices
                                    if (window.innerWidth < 992) { // lg breakpoint
                                        row.classList.add('mobile-clickable');
                                    } else {
                                        row.classList.remove('mobile-clickable');
                                    }

                                    // Remove existing listeners
                                    row.removeEventListener('click', handleEvaluationRowClick);

                                    // Add click listener for mobile screens
                                    if (window.innerWidth < 992) {
                                        row.addEventListener('click', handleEvaluationRowClick);
                                    }

                                    // Add touch feedback for mobile
                                    row.addEventListener('touchstart', function(e) {
                                        if (window.innerWidth < 992) {
                                            this.classList.add('mobile-touching');
                                        }
                                    });

                                    row.addEventListener('touchend', function(e) {
                                        if (window.innerWidth < 992) {
                                            this.classList.remove('mobile-touching');
                                        }
                                    });
                                });
                            }

                            function handleEvaluationRowClick(e) {
                                if (window.innerWidth < 992) { // Only on mobile/tablet
                                    e.preventDefault();
                                    showEvaluationModal(this);
                                }
                            }

                            // Initialize table event listeners
                            addEvaluationTableEventListeners();

                            // Handle window resize to update mobile state
                            window.addEventListener('resize', function() {
                                addEvaluationTableEventListeners();
                            });
                        });
                        </script>

                        <!-- Topic Details Modal -->
                        <div class="modal fade" id="topicModal" tabindex="-1" aria-labelledby="topicModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="topicModalLabel">Topic Title</h5>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">Description</h6>
                                            <p id="topicModalDescription" class="mb-0">Topic description will appear
                                                here...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Research Evaluation Details Modal -->
                        <div class="modal fade" id="evaluationModal" tabindex="-1"
                            aria-labelledby="evaluationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="evaluationModalLabel">Evaluation Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Team Name</h6>
                                                <p id="evalModalTeam" class="mb-0 fw-semibold"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Research Title</h6>
                                                <p id="evalModalTitle" class="mb-0"></p>
                                            </div>
                                            <div class="col-12" id="evalModalStudentSection" style="display: none;">
                                                <h6 class="text-muted mb-2">Student Name</h6>
                                                <p id="evalModalStudent" class="mb-0"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Evaluator</h6>
                                                <p id="evalModalEvaluator" class="mb-0"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Total Score</h6>
                                                <p id="evalModalScore" class="mb-0 fw-bold text-primary"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Comments</h6>
                                                <div id="evalModalComments" class="bg-light p-3 rounded"
                                                    style="min-height: 100px; white-space: pre-line;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="research-title" role="tabpanel"
                            aria-labelledby="research-title-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-check-circle text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Research Title Acceptance Tool</h4>
                                        <p class="text-muted mb-0">Check the uniqueness of your research title and get
                                            AI-powered suggestions</p>
                                    </div>
                                </div>

                                <!-- Research Title Input Form -->
                                <div class="card research-title-form-card mb-4">
                                    <div class="card-body">
                                        <form id="titleSubmissionForm" class="needs-validation">
                                            <div class="mb-3">
                                                <label for="researchTitle" class="form-label fw-semibold">Proposed
                                                    Research Title</label>
                                                <textarea class="form-control research-title-textarea"
                                                    id="researchTitle" name="researchTitle"
                                                    placeholder="Enter your research title here..." rows="3"
                                                    required></textarea>
                                                <div class="form-text">Be specific and descriptive about your research
                                                    focus</div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <label for="researchField" class="form-label fw-semibold">Research
                                                        Field</label>
                                                    <input type="text" class="form-control research-title-input"
                                                        id="researchField" name="researchField"
                                                        placeholder="e.g., Computer Science" required>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label for="problem" class="form-label fw-semibold">Problem
                                                        Statement</label>
                                                    <input type="text" class="form-control research-title-input"
                                                        id="problem" name="problem"
                                                        placeholder="Brief description of the problem" required>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <div class="research-title-status">
                                                    <small class="text-muted">Fill in all fields to analyze your
                                                        title</small>
                                                </div>
                                                <button type="button" id="submitTitleBtn"
                                                    class="btn btn-primary research-title-btn">
                                                    <i class="bi bi-search me-2"></i>Analyze Title
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Results Section -->
                                <div class="research-title-results">
                                    <!-- Uniqueness Analysis Card -->
                                    <div class="card research-title-result-card mb-3" id="uniquenessCard"
                                        style="display: none;">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-shield-check text-primary me-2"></i>
                                                <h6 class="mb-0">Uniqueness Analysis</h6>
                                            </div>
                                        </div>
                                        <div class="card-body" id="uniquenessResult">
                                            <!-- Uniqueness results will be inserted here -->
                                        </div>
                                    </div>

                                    <!-- AI Suggestions Card -->
                                    <div class="card research-title-result-card mb-3" id="suggestionsCard"
                                        style="display: none;">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                                <h6 class="mb-0">AI-Powered Suggestions</h6>
                                            </div>
                                        </div>
                                        <div class="card-body" id="aiSuggestions">
                                            <!-- AI suggestions will be inserted here -->
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div class="research-title-empty-state" id="emptyState">
                                        <div class="text-center py-5">
                                            <div class="empty-state-icon mb-3">
                                                <i class="bi bi-clipboard2-check"></i>
                                            </div>
                                            <h5 class="text-muted mb-2">Ready to Analyze Your Research Title</h5>
                                            <p class="text-muted mb-0">
                                                Complete the form above to get AI-powered analysis on title uniqueness,
                                                clarity, and receive suggestions for improvement.
                                            </p>
                                            <div class="mt-4">
                                                <div class="row g-3 text-start">
                                                    <div class="col-md-4">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-1-circle text-primary me-2 mt-1"></i>
                                                            <div>
                                                                <small class="fw-semibold">Uniqueness Check</small>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    Compare against existing research titles
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-2-circle text-primary me-2 mt-1"></i>
                                                            <div>
                                                                <small class="fw-semibold">Quality Analysis</small>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    Evaluate clarity and specificity
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-3-circle text-primary me-2 mt-1"></i>
                                                            <div>
                                                                <small class="fw-semibold">AI Suggestions</small>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    Get recommendations for improvement
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="requirement-checker" role="tabpanel"
                            aria-labelledby="requirement-checker-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-tasks text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Requirement Checker Tool</h4>
                                        <p class="text-muted mb-0">Track and manage your thesis requirements and
                                            submissions</p>
                                    </div>
                                </div>

                                <div class="media text-muted pt-3">
                                    <div id="teamSelectorContainer" class="mb-4">
                                        <!-- The dropdown will be dynamically inserted here -->
                                    </div>
                                    <div id="requirementChecklist" class="row g-4">
                                        <!-- Checklist items will be dynamically added here in a grid -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="research-evaluation" role="tabpanel"
                            aria-labelledby="research-evaluation-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-comments text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Research Evaluation Comments</h4>
                                        <p class="text-muted mb-0">View evaluation feedback and comments from panelists
                                        </p>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-sm research-evaluation-table"
                                        id="researchEvaluationTable">
                                        <thead>
                                            <tr>
                                                <th class="d-none d-lg-table-cell">Team Name</th>
                                                <th class="d-table-cell d-lg-none">Evaluation</th>
                                                <th class="d-none d-lg-table-cell">Research Title</th>
                                                <?php if ($_SESSION['usertype'] != 1): ?>
                                                <th class="d-none d-md-table-cell">Student Name</th>
                                                <?php endif; ?>
                                                <th class="d-none d-sm-table-cell">Evaluator</th>
                                                <th class="d-none d-lg-table-cell">Comments</th>
                                                <th class="d-none d-sm-table-cell">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                    // Different queries for students and professors
                                    if ($_SESSION['usertype'] == 1) { // Student
                                        $query = "SELECT 
                                            t.name AS team_name,
                                            rt.title AS research_title,
                                            CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name,
                                            ep.comments,
                                            ep.total_score
                                        FROM evaluation_per_panel ep
                                        JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                                        JOIN teams t ON ds.team_id = t.id
                                        JOIN research_titles rt ON t.id = rt.team_id
                                        JOIN users e ON ep.evaluator_id = e.id
                                        JOIN team_members tm ON t.id = tm.team_id
                                        WHERE tm.user_id = ?
                                        ORDER BY ep.created_at DESC";
                                        
                                        $stmt = $pdo->prepare($query);
                                        $stmt->execute([$_SESSION['id']]);
                                    } else { // Professor/Panelist
                                        $query = "SELECT 
                                            t.name AS team_name,
                                            rt.title AS research_title,
                                            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                                            CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name,
                                            ep.comments,
                                            ep.total_score
                                        FROM evaluation_per_panel ep
                                        JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                                        JOIN teams t ON ds.team_id = t.id
                                        JOIN research_titles rt ON t.id = rt.team_id
                                        JOIN users e ON ep.evaluator_id = e.id
                                        JOIN users s ON ep.student_id = s.id
                                        WHERE ep.evaluator_id = ?
                                        ORDER BY ep.created_at DESC";
                                        
                                        $stmt = $pdo->prepare($query);
                                        $stmt->execute([$_SESSION['id']]);
                                    }
                                    
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                            <tr class="evaluation-row"
                                                data-team="<?php echo htmlspecialchars($row['team_name']); ?>"
                                                data-title="<?php echo htmlspecialchars($row['research_title']); ?>"
                                                <?php if ($_SESSION['usertype'] != 1): ?>
                                                data-student="<?php echo htmlspecialchars($row['student_name']); ?>"
                                                <?php endif; ?>
                                                data-evaluator="<?php echo htmlspecialchars($row['evaluator_name']); ?>"
                                                data-comments="<?php echo htmlspecialchars($row['comments']); ?>"
                                                data-score="<?php echo htmlspecialchars($row['total_score']); ?>">

                                                <td class="d-none d-lg-table-cell">
                                                    <?php echo htmlspecialchars($row['team_name']); ?></td>
                                                <td class="d-table-cell d-lg-none">
                                                    <div class="mobile-evaluation-info">
                                                        <strong
                                                            class="d-block"><?php echo htmlspecialchars($row['team_name']); ?></strong>
                                                        <small
                                                            class="text-muted"><?php echo htmlspecialchars($row['evaluator_name']); ?></small>
                                                        <br><small class="text-muted">Score:
                                                            <?php echo htmlspecialchars($row['total_score']); ?></small>
                                                    </div>
                                                </td>
                                                <td class="d-none d-lg-table-cell">
                                                    <?php echo htmlspecialchars($row['research_title']); ?></td>
                                                <?php if ($_SESSION['usertype'] != 1): ?>
                                                <td class="d-none d-md-table-cell">
                                                    <?php echo htmlspecialchars($row['student_name']); ?></td>
                                                <?php endif; ?>
                                                <td class="d-none d-sm-table-cell">
                                                    <?php echo htmlspecialchars($row['evaluator_name']); ?></td>
                                                <td class="d-none d-lg-table-cell">
                                                    <?php echo nl2br(htmlspecialchars($row['comments'])); ?></td>
                                                <td class="d-none d-sm-table-cell">
                                                    <?php echo htmlspecialchars($row['total_score']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade show active" id="overview" role="tabpanel"
                            aria-labelledby="overview-link">
                            <div class="container-fluid py-4 content-container team-overview">
                                <!-- Header with title and sub-tabs -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <h3 class="mb-2">Dashboard</h3>
                                        <p class="text-muted">Track your requirement progress and defense schedule</p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="viewMode" id="dashboard-view"
                                                checked>
                                            <label class="btn btn-outline-primary" for="dashboard-view">
                                                <i class="bi bi-grid-3x3"></i> Dashboard
                                            </label>

                                            <input type="radio" class="btn-check" name="viewMode" id="calendar-view">
                                            <label class="btn btn-outline-primary" for="calendar-view">
                                                <i class="bi bi-calendar"></i> Calendar
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dashboard View (Default) -->
                                <div id="dashboardView">
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="teamOverviewContent">
                                                <!-- Team overview content will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendar View (Hidden by default) -->
                                <div id="calendarView" style="display: none;">
                                    <div class="row">
                                        <div class="col-12 col-lg-9 mb-3">
                                            <div class="calendar-container p-3">
                                                <!-- Calendar Div -->
                                                <div id="calendar2"></div>
                                            </div>
                                        </div>
                                        <?php if ($_SESSION['usertype'] == 2): ?>
                                        <?php
                                    // Fetch teams for current user
                                    $userId = $_SESSION['id'];
                                    $userType = $_SESSION['usertype'];
                                    
                                    if ($userType == 1) { // Student
                                        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ?");
                                        $teamsStmt->execute([$userId]);
                                        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    } else { // Staff/Adviser
                                        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.role IN ('adviser', 'panelist')");
                                        $teamsStmt->execute([$userId]);
                                        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    }
                                    
                                    // Use first team as default if available
                                    $selectedTeamId = !empty($teams) ? $teams[0]['id'] : null;
                                    
                                    // Fetch requirements for selected team
                                    $requirements = [];
                                    if ($selectedTeamId) {
                                        $requirementsStmt = $pdo->prepare("
                                            SELECT r.id, r.name, r.description, r.due_date,
                                                   COALESCE(tr.status, 'pending') as status,
                                                   tr.submitted_at, tr.feedback
                                            FROM requirements r 
                                            LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
                                            ORDER BY r.due_date ASC, r.name ASC
                                        ");
                                        $requirementsStmt->execute([$selectedTeamId]);
                                        $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    }
                                    ?>
                                        <div class="requirements-list col-12 col-lg-3">
                                            <div class="accordion custom-accordion" id="requirementsAccordion2">
                                                <!-- Requirements Section -->
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingRequirements2">
                                                        <button class="accordion-button custom-accordion-btn"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseRequirements2" aria-expanded="true"
                                                            aria-controls="collapseRequirements2">
                                                            Requirements
                                                        </button>
                                                    </h2>
                                                    <div id="collapseRequirements2"
                                                        class="accordion-collapse collapse show"
                                                        aria-labelledby="headingRequirements2"
                                                        data-bs-parent="#requirementsAccordion2">
                                                        <div class="accordion-body custom-scrollbar">
                                                            <!-- Team Selector -->
                                                            <?php if (count($teams) > 1): ?>
                                                            <div class="mb-3">
                                                                <label for="requirementsTeamSelector"
                                                                    class="form-label small text-muted">Select
                                                                    Team:</label>
                                                                <select id="requirementsTeamSelector"
                                                                    class="form-select form-select-sm">
                                                                    <?php foreach ($teams as $team): ?>
                                                                    <option value="<?php echo $team['id']; ?>"
                                                                        <?php echo ($team['id'] == $selectedTeamId) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($team['name']); ?>
                                                                    </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <?php elseif (count($teams) == 1): ?>
                                                            <div class="mb-3">
                                                                <p class="small text-muted mb-2">Team:
                                                                    <strong><?php echo htmlspecialchars($teams[0]['name']); ?></strong>
                                                                </p>
                                                            </div>
                                                            <?php endif; ?>

                                                            <!-- Requirements List -->
                                                            <ul class="list-group" id="requirementsList">
                                                                <?php if (empty($teams)): ?>
                                                                <li class="list-group-item text-center text-muted">
                                                                    <em>No teams assigned to you</em>
                                                                </li>
                                                                <?php elseif (!empty($requirements)): ?>
                                                                <?php foreach ($requirements as $requirement): ?>
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center req-li">
                                                                    <div>
                                                                        <strong><?php echo htmlspecialchars($requirement['name']); ?></strong>
                                                                        <br>
                                                                        <small class="text-muted due-date-txt">Due:
                                                                            <?php echo date('M d, Y', strtotime($requirement['due_date'])); ?></small>
                                                                    </div>
                                                                    <?php
                                                                        $badgeClass = 'bg-warning';
                                                                        $badgeText = 'Pending';
                                                                        if ($requirement['status'] === 'approved') {
                                                                            $badgeClass = 'bg-success';
                                                                            $badgeText = 'Approved';
                                                                        } elseif ($requirement['status'] === 'submitted') {
                                                                            $badgeClass = 'bg-info';
                                                                            $badgeText = 'Submitted';
                                                                        } elseif ($requirement['status'] === 'rejected') {
                                                                            $badgeClass = 'bg-danger';
                                                                            $badgeText = 'Rejected';
                                                                        }
                                                                        ?>
                                                                    <span
                                                                        class="badge <?php echo $badgeClass; ?> rounded-pill"><?php echo $badgeText; ?></span>
                                                                </li>
                                                                <?php endforeach; ?>
                                                                <?php else: ?>
                                                                <li class="list-group-item text-center text-muted">
                                                                    <em>No requirements found for this team</em>
                                                                </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                            // Defense schedules for faculty users (copied from scheduling tab)
                                            $userId = $_SESSION['id'];
                                            $query = "SELECT
                                                        ds.id AS schedule_id,
                                                        ds.schedule_date,
                                                        ds.start_time,
                                                        ds.end_time,
                                                        ds.room,
                                                        t.name AS team_name,
                                                        t.program AS team_program,
                                                        (
                                                            SELECT rgi.group_id
                                                            FROM rubric_programs rp
                                                            JOIN rubric_group_items rgi ON rp.rubric_id = rgi.rubric_id
                                                            WHERE rp.program_name = t.program
                                                            LIMIT 1
                                                        ) AS rubric_group_id
                                                    FROM defense_schedules ds
                                                    JOIN teams t ON ds.team_id = t.id
                                                    WHERE
                                                        ds.panelist_id = :user_id1
                                                        OR ds.panelist_id2 = :user_id2
                                                        OR ds.panelist_id3 = :user_id3
                                                    ORDER BY
                                                        ds.schedule_date, ds.start_time";
                                            $stmt = $pdo->prepare($query);
                                            $stmt->execute([
                                                'user_id1' => $userId,
                                                'user_id2' => $userId,
                                                'user_id3' => $userId
                                            ]);
                                            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                                <div class="accordion-item mt-2">
                                                    <h2 class="accordion-header" id="headingDefenses2">
                                                        <button class="accordion-button custom-accordion-btn collapsed"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseDefenses2" aria-expanded="false"
                                                            aria-controls="collapseDefenses2">
                                                            Defense Schedules
                                                        </button>
                                                    </h2>
                                                    <div id="collapseDefenses2" class="accordion-collapse collapse"
                                                        aria-labelledby="headingDefenses2"
                                                        data-bs-parent="#requirementsAccordion2">
                                                        <div class="accordion-body custom-scrollbar">
                                                            <ul class="list-group">
                                                                <?php foreach ($schedules as $schedule):
                                                                $formatted_date = date('F j, Y', strtotime($schedule['schedule_date']));
                                                                $formatted_start_time = date('g:i a', strtotime($schedule['start_time']));
                                                                $formatted_end_time = date('g:i a', strtotime($schedule['end_time']));
                                                                $rubric_group_id = $schedule['rubric_group_id'];
                                                                $schedule_id = $schedule['schedule_id'];

                                                                $onclick_attr = '';
                                                                $item_class = 'list-group-item defense-item';
                                                                $disabled_message = '';
                                                                if ($rubric_group_id !== null) {
                                                                    $onclick_attr = 'onclick="redirectToDecisionSupport(' . $schedule_id . ', ' . $rubric_group_id . ')"';
                                                                } else {
                                                                    $item_class .= ' disabled';
                                                                    $disabled_message = '<small class="text-muted d-block mt-1">Evaluation not available (Rubric group not configured)</small>';
                                                                }
                                                            ?>
                                                                <li class="<?php echo $item_class; ?>"
                                                                    <?php echo $onclick_attr; ?>>
                                                                    <div class="defense-content">
                                                                        <h6 class="team-name mb-2">
                                                                            <?php echo htmlspecialchars($schedule['team_name']); ?>
                                                                        </h6>
                                                                        <div class="defense-details">
                                                                            <div class="detail-item">
                                                                                <i class="far fa-calendar me-2"></i>
                                                                                <?php echo htmlspecialchars($formatted_date); ?>
                                                                            </div>
                                                                            <div class="detail-item">
                                                                                <i class="far fa-clock me-2"></i>
                                                                                <?php echo htmlspecialchars($formatted_start_time . " - " . $formatted_end_time); ?>
                                                                            </div>
                                                                            <div class="detail-item">
                                                                                <i class="fas fa-door-open me-2"></i>
                                                                                <?php echo htmlspecialchars($schedule['room']); ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php echo $disabled_message; ?>
                                                                    </div>
                                                                </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Defense Approval Modal -->
<div class="modal fade" id="defenseApprovalModal" tabindex="-1" aria-labelledby="defenseApprovalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="defenseApprovalModalLabel">
                    <i class="bi bi-calendar-check me-2"></i>Defense Schedule Approval Required
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="approvalModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading approval request...</p>
                </div>
            </div>
        </div>
    </div>
</div>




<?php
include '../assets/layouts/footer.php'
?>
<!-- AI GEMINI MODULE -->
<!-- Main Module JS -->
<script type="module" src="../assets/js/mainModule.js"></script>
<!-- app.js -->
<script type="module" src="../assets/js/app.js"></script>
<?php
// Local: research_titles | Deployed: icei_38697196_coecsathesis.research_titles
$stmt = $pdo->query("SELECT title FROM research_titles;");
$titles = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<script>
// ==========================================
// COLLAPSIBLE SIDEBAR FUNCTIONALITY - HOME PAGE
// ==========================================
$(document).ready(function() {
    // Initialize sidebar toggle functionality
    initHomePageSidebar();
});

function initHomePageSidebar() {
    // Remove any existing click handlers to prevent conflicts
    $('#toggleHomeSidebar').off('click');

    // Check localStorage for saved sidebar state on page load
    const sidebarCollapsed = localStorage.getItem('homeSidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        $('#homeSidebarContainer').addClass('collapsed');
        $('#homeMainContent').addClass('expanded');
        $('#toggleHomeSidebar').find('i').css('transform', 'rotate(180deg)');
        $('body').addClass('home-sidebar-collapsed');
    }

    // Handle responsive behavior
    if ($(window).width() <= 768) {
        // Mobile behavior - matches dashboard breakpoint
        $('#toggleHomeSidebar').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Toggle collapsed state on mobile
            $('#homeSidebarContainer').toggleClass('collapsed');
            $('#homeMainContent').toggleClass('expanded');

            // Update icon rotation and body class
            if ($('#homeSidebarContainer').hasClass('collapsed')) {
                $(this).find('i').css('transform', 'rotate(180deg)');
                $('body').addClass('has-collapsed-home-sidebar');
            } else {
                $(this).find('i').css('transform', 'rotate(0deg)');
                $('body').removeClass('has-collapsed-home-sidebar');
            }

            // Save state to localStorage
            localStorage.setItem('homeSidebarCollapsed', $('#homeSidebarContainer').hasClass('collapsed'));
        });
    } else {
        // Desktop behavior
        $('#toggleHomeSidebar').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Toggle collapsed state
            $('#homeSidebarContainer').toggleClass('collapsed');
            $('#homeMainContent').toggleClass('expanded');

            // Update icon rotation
            if ($('#homeSidebarContainer').hasClass('collapsed')) {
                $(this).find('i').css('transform', 'rotate(180deg)');
                $('body').addClass('has-collapsed-home-sidebar');
            } else {
                $(this).find('i').css('transform', 'rotate(0deg)');
                $('body').removeClass('has-collapsed-home-sidebar');
            }

            // Save state to localStorage
            localStorage.setItem('homeSidebarCollapsed', $('#homeSidebarContainer').hasClass('collapsed'));
        });
    }

    // Handle window resize - reinitialize without infinite recursion
    $(window).off('resize.homeSidebar').on('resize.homeSidebar', function() {
        // Only reinitialize if we switch between mobile and desktop
        const isMobile = $(window).width() <= 768;
        const wasInitializedForMobile = $('#toggleHomeSidebar').data('mobile-mode') === true;

        if (isMobile !== wasInitializedForMobile) {
            $('#toggleHomeSidebar').data('mobile-mode', isMobile);
            initHomePageSidebar();
        }
    });

    // Mark current mode
    $('#toggleHomeSidebar').data('mobile-mode', $(window).width() <= 768);

    console.log('Home page sidebar toggle functionality initialized');
}

// Remove legacy mobile overlay styles - now using dashboard responsive approach
$('<style>')
    .prop('type', 'text/css')
    .html(`
            /* Home sidebar responsive helper styles */
            body.has-collapsed-home-sidebar {
                overflow-x: hidden;
            }
            
            @media (max-width: 768px) { 
                body.has-collapsed-home-sidebar #homeMainContent {
                    padding-left: 70px;
                }
            }
        `)
    .appendTo('head');

var existingTitles = "<?php echo implode(', ', $titles); ?>";
</script>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
$(document).ready(function() {
    <?php
        $role = isset($_SESSION['team_role']) ? $_SESSION['team_role'] : '';
        $teamId = isset($_SESSION['team_id']) ? $_SESSION['team_id'] : [];

        if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) {
            if ($role === 'adviser') {
                // Retrieve the teams from the session (ensure $teamId is an array)
                $teams = [];
                if (!empty($teamId)) {
                    // Query the team information based on the team_id from session
                    $teamIdStr = implode(',', (array)$teamId);
                    $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($teamIdStr)");
                    $stmt->execute();
                    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            ?>

    $(document).ready(function() {
        var teamSelectHtml = '<select id="teamSelect" class="form-select mb-3">';
        <?php if (!empty($teams)) { ?>
        <?php foreach ($teams as $team) { ?>
        teamSelectHtml +=
            '<option id="team_id-<?php echo $team['id']; ?>" value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>';
        <?php } ?>
        <?php } else { ?>
        teamSelectHtml += '<option value="">No teams available</option>';
        <?php } ?>
        teamSelectHtml += '</select>';
        console.log(teamSelectHtml);
        $('#teamSelectorContainer').html(teamSelectHtml);

        // Load initial requirements for the first team
        var initialTeamId = $('#teamSelect').val();
        if (initialTeamId) {
            loadRequirements(initialTeamId);
        }

        // Reload requirements when team changes
        $('#teamSelect').on('change', function() {
            var selectedTeamId = $(this).val();
            loadRequirements(selectedTeamId);
        });
    });

    function loadRequirements(teamId) {
        console.log("Loading requirements for teamId:", teamId);
        $.ajax({
            url: 'includes/get_requirements.php',
            method: 'GET',
            data: {
                team_id: teamId
            },
            dataType: 'json',
            success: function(response) {
                console.log("AJAX request successful. Response:", response);
                if (response.success) {
                    var checklistHtml =
                        '<form id="requirementChecklistForm" method="POST" action="includes/update_requirements.php" enctype="multipart/form-data" class="row g-4">';
                    response.requirements.forEach(function(req) {
                        checklistHtml += `
                        <div class="col-12 col-lg-6 requirement-card-wrapper">
                            <div class="card requirement-adviser-card h-100" id="req-card-${req.id}" data-req-id="${req.id}" data-status="${req.status}">
                                <div class="card-body requirement-card-body">
                                    <div class="d-flex justify-content-between align-items-start requirement-header-section">
                                        <div class="requirement-content requirement-info-section">
                                            <label class="form-check-label requirement-title-label" for="req${req.id}">
                                                <strong class="requirement-name">${req.name}</strong>
                                                <p class="mb-1 text-muted requirement-description">${req.description || 'No description provided.'}</p>
                                                <small class="text-muted requirement-due-date">Due Date: ${new Date(req.due_date).toLocaleDateString()}</small>
                                            </label>
                                        </div>
                                        <div class="form-check requirement-checkbox-section">
                                            <input class="form-check-input requirement-checkbox" type="checkbox" 
                                                   value="${req.id}" id="req${req.id}" 
                                                   name="requirements[]" ${req.status !== 'pending' ? 'checked' : ''}>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 requirement-status-section">
                                        <label for="status${req.id}" class="form-label requirement-status-label">Status:</label>
                                        <select id="status${req.id}" name="status[${req.id}]" class="form-select form-select-sm requirement-status-select">
                                            <option value="pending" ${req.status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="submitted" ${req.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                                            <option value="approved" ${req.status === 'approved' ? 'selected' : ''}>Approved</option>
                                            <option value="rejected" ${req.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mt-3 requirement-feedback-section">
                                        <label for="feedback${req.id}" class="form-label requirement-feedback-label">Feedback:</label>
                                        <textarea id="feedback${req.id}" name="feedback[${req.id}]" class="form-control form-control-sm requirement-feedback-textarea" rows="2">${req.feedback}</textarea>
                                    </div>
                                    ${req.file_name 
                                        ? `
                                            <div class="mt-3 d-flex gap-2 flex-wrap requirement-file-actions">
                                                <a href="../assets/uploads/submission/${req.file_name}" class="btn btn-sm btn-secondary requirement-download-btn" download>Download File</a>
                                                <a href="../assets/uploads/submission/viewer.html?file=${req.file_name}" class="btn btn-sm btn-secondary requirement-view-btn">View File</a>
                                            </div>
                                        ` 
                                        : ``
                                    }
                                    
                                    ${req.template_file 
                                        ? `
                                            <div class="mt-3 d-flex gap-2 flex-wrap requirement-template-actions">
                                                <a href="/dashboard/uploads/requirements/${req.template_file}" class="btn btn-sm btn-info requirement-template-btn" download>Download Template</a>
                                            </div>
                                        ` 
                                        : ``
                                    }
                                    
                                    <div class="mt-3 requirement-upload-section">
                                        <label for="feedbackFile${req.id}" class="form-label requirement-upload-label">Upload Feedback File:</label>
                                        <input class="form-control form-control-sm requirement-file-input" type="file" id="feedbackFile${req.id}" name="feedbackFile[${req.id}]">
                                    </div>
                                    
                                    <div class="mt-3 requirement-feedback-file-section">
                                        ${req.feedback_file ? `<a href="./feedback/${req.feedback_file}" class="btn btn-sm btn-secondary requirement-feedback-download-btn" download>Download Feedback File</a>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                    checklistHtml +=
                        '<div class="col-12"><button type="submit" class="btn btn-primary mt-3" id="updateReqsBtn">Update Requirements</button></div></form>';
                    // Insert the generated HTML into the container
                    $('#requirementChecklist').html(checklistHtml);
                } else {
                    $('#requirementChecklist').html('<p class="text-danger">' + response.error +
                        '</p>');
                    console.error('Error in response:', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#requirementChecklist').html(
                    '<p class="text-danger">Error loading requirements. Please refresh the page.</p>'
                    );
                console.error("AJAX error:", textStatus, errorThrown);
            }
        });
    }


    $(document).ready(function() {

        loadRequirements();


        $(document).on('submit', '#requirementChecklistForm', function(event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'includes/update_requirements.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log("Update response:", response);
                    if (response.success) {
                        alert("Requirements updated successfully!");
                    } else {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error:", textStatus, errorThrown);
                    alert("An error occurred while updating requirements.");
                }
            });
        });
    });
    <?php
            }
        } else if ($_SESSION['usertype'] == 1) { ?>
    // console.log("Loading requirements for teamId:", teamId);
    loadRequirements(); // Just call the function here for usertype 1

    function loadRequirements() {

        $.ajax({
            url: 'includes/get_requirements.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var displayHtml = '<div class="row">';
                    response.requirements.forEach(function(req) {
                        <?php if ($role === 'leader' || $role === 'member') { ?>
                            displayHtml += `
                                <div class="col-md-6 mb-4 requirement-student-wrapper">
                                    <div class="card requirement-student-card h-100 rounded" id="student-req-card-${req.id}" data-req-id="${req.id}" data-status="${req.status}">
                                        <div class="card-body requirement-student-body rct-cbody">
                                            <h5 class="card-title requirement-student-title rct-ctitle">${req.name}</h5>
                                            <p class="card-text requirement-student-description">${req.description}</p>
                                            <p class="card-text requirement-student-due-date"><strong>Due date:</strong> ${new Date(req.due_date).toLocaleDateString()}</p>
                                            <p class="card-text requirement-student-status"><strong>Status:</strong> ${req.status}</p>
                                            <p class="card-text requirement-student-feedback"><strong>Feedback:</strong> ${req.feedback}</p>
                                            ${req.template_file ? `
                                                <div class="mt-3 d-flex gap-2 flex-wrap requirement-template-actions">
                                                    <a href="../dashboard/uploads/requirements/${req.template_file}" class="btn btn-sm btn-info requirement-template-btn" download>Download Template</a>
                                                </div>
                                            ` : ''}
                                        </div>
                                        
                                        <div class="card-footer requirement-student-feedback-footer">
                                            ${req.feedback_file ? 
                                                `<a href="./feedback/${req.feedback_file}" class="btn btn-secondary requirement-feedback-download-btn" download>Download Feedback File</a>` 
                                                : '<span class="requirement-no-feedback-file">No Uploaded Feedback File</span>'}
                                        </div> 
                                        <?php if ($role === 'leader') { ?>
                                        <div class="card-footer requirement-student-upload-footer rct-cfooter">
                                            ${req.file_name 
                                                ? `<a href="../assets/uploads/submission/${req.file_name}" class="btn btn-secondary requirement-student-download-btn" download>Download Submitted File</a>` 
                                                : `
                                                    <form class="upload-form requirement-upload-form" data-req-id="${req.id}" enctype="multipart/form-data" action="includes/upload_file.php" method="POST">
                                                        <input type="hidden" name="document_name" value="${req.name}">
                                                        <input type="hidden" name="requirement_id" value="${req.id}">
                                                        <div class="mb-3 requirement-file-input-section">
                                                            <label for="file-${req.id}" class="form-label requirement-file-label">Upload File</label>
                                                            <input class="form-control requirement-file-input" type="file" id="file-${req.id}" name="file" required>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary feature-btn requirement-submit-btn">Submit File</button>
                                                        <span class="upload-status requirement-upload-status ms-2 small"></span> 
                                                    </form> 
                                                `}
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            `;
                        <?php } ?>
                    });
                    displayHtml += '</div>';
                    $('#requirementChecklist').html(displayHtml);
                } else {
                    $('#requirementChecklist').html('<p class="text-danger">' + response.error +
                        '</p>');
                    console.error('Error fetching requirements:', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#requirementChecklist').html(
                    '<p class="text-danger">Error loading requirements. Please refresh the page.</p>'
                    );
                console.error("AJAX error:", textStatus, errorThrown);
                console.error("Response Text:", jqXHR.responseText);
                console.error("Status Code:", jqXHR.status);
            }
        });
    }

    <?php } ?>

    // --- AJAX submission handler for student file uploads ---
    // Moved outside the usertype condition, uses delegation
    $(document).on('submit', '#requirementChecklist .upload-form', function(event) {
        console.log('Student upload form submission intercepted.'); // Added log
        event.preventDefault(); // Prevent default form submission

        var form = $(this);
        var formData = new FormData(this);
        var statusSpan = form.find('.upload-status');
        var submitButton = form.find('button[type="submit"]');

        statusSpan.text('Uploading...').removeClass('text-danger text-success');
        submitButton.prop('disabled', true);
        console.log('Initiating AJAX upload...'); // Added log

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: formData,
            processData: false, // Important for FormData
            contentType: false, // Important for FormData
            dataType: 'json', // Expect JSON response from upload_file.php
            success: function(response) {
                console.log('AJAX upload success response:', response); // Added log
                if (response.success) {
                    statusSpan.text('Upload successful! Refreshing...').addClass(
                        'text-success');
                    // Refresh the requirements list after a short delay
                    setTimeout(loadRequirements, 1500);
                } else {
                    statusSpan.text('Error: ' + (response.error || 'Unknown error'))
                        .addClass('text-danger');
                    submitButton.prop('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Log the raw response text to see what the server actually sent
                console.log('Raw response:', jqXHR.responseText);
                statusSpan.text('Upload failed. Please try again.').addClass('text-danger');
                console.error("AJAX upload error:", textStatus, errorThrown);
                submitButton.prop('disabled', false);
            }
        });
    });
    // --- End AJAX submission handler ---


});
//calendar
console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');
$(document).ready(function() {
    // Thesis Topic Decision Tool
    $('#topicSuggestionForm').on('submit', function(e) {
        e.preventDefault();
        var field = $('#field').val();
        // AJAX call to get topic suggestions
        $.ajax({
            url: 'includes/get_topic_suggestions.php',
            method: 'POST',
            data: {
                field: field
            },
            dataType: 'json',
            success: function(response) {
                var suggestionsHtml = '<ul>';
                response.suggestions.forEach(function(suggestion) {
                    suggestionsHtml += '<li>' + suggestion + '</li>';
                });
                suggestionsHtml += '</ul>';
                $('#suggestedTopics').html(suggestionsHtml);
            },
            error: function() {
                $('#suggestedTopics').html(
                    '<p>Error fetching suggestions. Please try again.</p>');
            }
        });
    });

    // Scheduling System
    function loadUserSchedule() {
        $.ajax({
            url: 'includes/get_user_schedule.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("AJAX response:", response);
                if (response.success) {
                    var events = [];
                    // Add user schedules to events
                    response.user_schedules.forEach(function(event) {
                        events.push({
                            title: event.description,
                            start: event.date + 'T' + event.start_time,
                            end: event.date + 'T' + event.end_time,
                        });
                    });
                    // Add defense schedules to events
                    response.defense_schedules.forEach(function(event) {
                        events.push({
                            title: event.description,
                            start: event.date + 'T' + event.start_time,
                            end: event.date + 'T' + event.end_time,
                        });
                    });
                    console.log("Events to be rendered:", events);
                    initializeCalendar(events);
                } else {
                    $('#userSchedule').html('<p>Error loading schedules: ' + response.error +
                        '</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
                $('#userSchedule').html('<p>Error loading schedules. Please try again later.</p>');
            }
        });
    }

    function initializeCalendar(events) {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto', // or set a specific height like '600px'
            events: events, // Use the dynamically loaded events
            eventClick: function(info) {
                alert('Event: ' + info.event.title);
            }
        });
        calendar.render();
    }

});



const calendarEl = document.getElementById('calendar');

// Function to get the next date for a given day of the week
function getNextDateForDay(day) {
    const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const today = new Date();
    const targetDayIndex = daysOfWeek.indexOf(day);
    if (targetDayIndex === -1) {
        return day; // Return original if invalid day
    }
    const resultDate = new Date(today);
    resultDate.setDate(today.getDate() + ((7 + targetDayIndex - today.getDay()) % 7));
    return resultDate.toISOString().split('T')[0];
}


/**
 * Function to redirect to decision-support with the team_id as a POST value.
 * @param {number} teamId - The ID of the team to send via POST.
 */
function redirectToDecisionSupport(scheduleId, groupId) {
    if (!scheduleId || !groupId) {
        console.error('Missing scheduleId or groupId for redirection.');
        alert('Error: Cannot navigate to evaluation page. Missing information.');
        return;
    }
    // Construct the URL with both parameters
    const url = `../decision-support/index.php?schedule_id=${scheduleId}&group_id=${groupId}`;
    console.log(`Redirecting to: ${url}`);
    window.location.href = url;
}

// Defense Approval Modal System
$(document).ready(function() {
    // Check for pending defense approvals on page load
    checkPendingApprovals();

    function checkPendingApprovals() {
        $.ajax({
            url: '../assets/includes/get_pending_approvals.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.pendingApprovals.length > 0) {
                    // Show modal for the first pending approval
                    showApprovalModal(response.pendingApprovals[0]);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking pending approvals:', error);
            }
        });
    }

    function showApprovalModal(approval) {
        const modalBody = `
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <strong>You have been assigned as a panelist for the following defense:</strong>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="bi bi-calendar3 me-2"></i>Schedule Details
                                </h6>
                                <p class="mb-1"><strong>Date:</strong> ${approval.formatted_date}</p>
                                <p class="mb-1"><strong>Time:</strong> ${approval.formatted_time}</p>
                                <p class="mb-0"><strong>Room:</strong> ${approval.room}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="bi bi-people me-2"></i>Team Information
                                </h6>
                                <p class="mb-1"><strong>Team:</strong> ${approval.team_name}</p>
                                <p class="mb-1"><strong>Program:</strong> ${approval.program || 'N/A'}</p>
                                <p class="mb-0"><strong>Research:</strong> ${approval.research_title || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="text-primary">
                        <i class="bi bi-person-badge me-2"></i>Other Panelists
                    </h6>
                    <p class="mb-0">${approval.other_panelists}</p>
                </div>
                
                <div class="mb-4">
                    <label for="rejectionReason" class="form-label">
                        <i class="bi bi-chat-text me-2"></i>Reason for declining (optional):
                    </label>
                    <textarea class="form-control" id="rejectionReason" rows="3" 
                              placeholder="Please provide a reason if you cannot attend this defense session..."></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-outline-danger" onclick="respondToApproval('reject', ${approval.schedule_id})">
                        <i class="bi bi-x-circle me-2"></i>Decline
                    </button>
                    <button type="button" class="btn btn-success" onclick="respondToApproval('approve', ${approval.schedule_id})">
                        <i class="bi bi-check-circle me-2"></i>Accept
                    </button>
                </div>
            `;

        $('#approvalModalBody').html(modalBody);
        $('#defenseApprovalModal').modal('show');
    }

    // Global function for handling approval responses
    window.respondToApproval = function(action, scheduleId) {
        const reason = $('#rejectionReason').val().trim();
        const actionText = action === 'approve' ? 'accepting' : 'declining';

        // Show loading state
        const modalBody = $('#approvalModalBody');
        const originalContent = modalBody.html();
        modalBody.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <p>Processing your response...</p>
                </div>
            `);

        $.ajax({
            url: '../assets/includes/handle_defense_approval.php',
            method: 'POST',
            data: {
                action: action,
                schedule_id: scheduleId,
                rejection_reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    modalBody.html(`
                            <div class="text-center py-4">
                                <div class="text-success mb-3">
                                    <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-success">Response Recorded!</h5>
                                <p class="mb-0">${response.message}</p>
                            </div>
                        `);

                    // Close modal after 3 seconds and check for more approvals
                    setTimeout(function() {
                        $('#defenseApprovalModal').modal('hide');
                        checkPendingApprovals(); // Check for more pending approvals
                    }, 3000);

                } else {
                    // Show error message with retry option
                    modalBody.html(`
                            <div class="text-center py-4">
                                <div class="text-danger mb-3">
                                    <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-danger">Error</h5>
                                <p class="mb-3">${response.message}</p>
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                                </button>
                            </div>
                        `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error processing approval:', error);
                modalBody.html(`
                        <div class="text-center py-4">
                            <div class="text-danger mb-3">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-danger">Connection Error</h5>
                            <p class="mb-3">Failed to process your response. Please try again.</p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reload Page
                            </button>
                        </div>
                    `);
            }
        });
    };
});
</script>
</body>

</html>