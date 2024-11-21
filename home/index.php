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
include '..\assets\setup\db.inc.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// echo '<pre>';
// print_r($_SESSION['team_id'][0]);
// echo '</pre>';
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
            } else {
                document.getElementById('scheduling').classList.add("show", "active");
            }
            if (activeNavLink) {
                activeNavLink.classList.add("active");
            } else {
                document.getElementById('scheduling-link').classList.add("active");
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
        <div class="col-sm-3">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="d-flex align-items-center p-3 my-3 sidebar-header rounded shadow-sm">
                    <!-- <img class="mr-3" src="../assets/images/logonotextwhite.png" alt="" width="48" height="48"> -->
                    <div class="lh-100">
                        <h5 class="mb-0 lh-100"><?php echo $_SESSION['usertype'] == 0 ? "Admin Dashboard" : "User Dashboard"; ?></h5>
                        <small><?php echo $_SESSION['usertype'] == 0 ? "System Management" : "Welcome"; ?></small>
                    </div>
                </div>
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="scheduling-link" data-bs-toggle="pill" href="#scheduling" role="tab" aria-controls="scheduling" aria-selected="false">Calendar</a>
                    <a class="nav-link" id="thesis-topic-link" data-bs-toggle="pill" href="#thesis-topic" role="tab" aria-controls="thesis-topic" aria-selected="true">Thesis Topic Decision</a>
                    <a class="nav-link" id="research-title-link" data-bs-toggle="pill" href="#research-title" role="tab" aria-controls="research-title" aria-selected="false">Research Title Acceptance</a>
                    <a class="nav-link" id="requirement-checker-link" data-bs-toggle="pill" href="#requirement-checker" role="tab" aria-controls="requirement-checker" aria-selected="false">Requirement Checker</a>
                </div>
            </div>
        </div>

        <div class="col-sm-9">
            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="scheduling" role="tabpanel" aria-labelledby="scheduling-link">
                    <div class="row"> <!-- Added a row wrapper -->
                        <div class="col-sm-9 my-3 p-3 home-sidebar-box">
                            <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Schedule</h6>
                            <div class="media text-muted pt-3">
                                <!-- Calendar Div -->
                                <div id="calendar"></div>
                            </div>
                        </div>
                        <?php
                        $stmt = $pdo->query("SELECT name, due_date FROM coecsa_thesis.requirements;");
                        $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="requirements-list col-sm-3 my-3 p-3">
                            <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Requirements</h6>
                            <ul class="list-group">
                                <?php foreach ($requirements as $requirement): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo htmlspecialchars($requirement['name']); ?></strong>
                                        <br>
                                        <small>Due Date: <?php echo htmlspecialchars($requirement['due_date']); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="thesis-topic" role="tabpanel" aria-labelledby="thesis-topic-link">
                    <div class="my-3 p-3 home-sidebar-box">
                        <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Latest topic trends</h6>
                        <div class="media text-muted pt-3">
                            <div class="form-group">
                                <label for="thesisField">Select a field:</label>
                                <select id="thesisField" class="form-select">
                                    <option value="">Select a field</option>
                                    <option value="Architecture">Architecture</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Information Technology">Information Technology</option>
                                    <option value="Aeronautical Engineering">Aeronautical Engineering</option>
                                    <option value="Civil Engineering">Civil Engineering</option>
                                    <option value="Computer Engineering">Computer Engineering</option>
                                    <option value="Engineering Technology with a major in Construction Technology and Management">Engineering Technology (Construction Technology and Management)</option>
                                    <option value="Electrical Engineering">Electrical Engineering</option>
                                    <option value="Electronics Engineering">Electronics Engineering</option>
                                    <option value="Industrial Engineering">Industrial Engineering</option>
                                    <option value="Mechanical Engineering">Mechanical Engineering</option>
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
                            }
                        };

                        // Send the selected field to the server
                        xhr.send('field=' + encodeURIComponent(selectedField));
                    });
                </script>

                <div class="tab-pane fade" id="research-title" role="tabpanel" aria-labelledby="research-title-link">
                    <div class="my-3 p-3 home-sidebar-box">
                        <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Research Title Acceptance Tool</h6>
                        <div class="media text-muted pt-3">
                            <p class="media-body pb-3 mb-0 small lh-125 feature-subtitle">
                                <strong class="d-block text-gray-dark">Title Uniqueness Check</strong>
                            <form id="titleSubmissionForm">
                                <div class="mb-3">
                                    <label for="researchTitle" class="form-label">Proposed Research Title</label>
                                    <input type="text" class="form-control" id="researchTitle" name="researchTitle" required>
                                </div>
                                <div class="mb-3">
                                    <label for="researchField" class="form-label">Research Field</label>
                                    <input type="text" class="form-control" id="researchField" name="researchField" required>
                                </div>
                                <button type="button" id="submitTitleBtn" class="btn btn-primary feature-btn">Check Title</button>
                            </form>
                            <div id="uniquenessResult" class="mt-3"></div>
                            <div id="aiSuggestions" class="mt-3"></div>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="requirement-checker" role="tabpanel" aria-labelledby="requirement-checker-link">
                    <div class="my-3 p-3 home-sidebar-box">
                        <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Requirement Checker Tool</h6>
                        <div class="media text-muted pt-3">
                            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-secondary">
                                <strong class="d-block text-gray-dark">Document Checklist</strong>
                            <div id="teamSelectorContainer">
                                <!-- The dropdown will be dynamically inserted here -->
                            </div>
                            <div id="requirementChecklist">
                                <!-- Checklist items will be dynamically added here -->
                            </div>


                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>




<?php

include '../assets/layouts/footer.php'

?>
<!-- AI GEMINI MODULE -->
<!-- Main Module JS -->
<script type="module" src="../assets/js/mainModule.js"></script>
<!-- app.js -->
<?php
$stmt = $pdo->query("SELECT title FROM coecsa_thesis.research_titles;");
$titles = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<script>
    var existingTitles = "<?php echo implode(', ', $titles); ?>";
</script>
<script type="module" src="../assets/js/app.js"></script>
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
                            teamSelectHtml += '<option value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>';
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
                                var checklistHtml = '<form id="requirementForm" enctype="multipart/form-data">';
                                response.requirements.forEach(function(req) {
                                    checklistHtml += `
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="${req.id}" id="req${req.id}" name="requirements[]" ${req.status !== 'pending' ? 'checked' : ''}>
                            <label class="form-check-label" for="req${req.id}"><strong>${req.name}</strong></label>
                            <p class="mb-1 text-muted">${req.description || 'No description provided.'}</p>
                            <small class="text-muted">Due Date: ${new Date(req.due_date).toLocaleDateString()}</small>
                            <div class="mt-2">
                                <label for="status${req.id}">Status:</label>
                                <select id="status${req.id}" name="status[${req.id}]" class="form-select form-select-sm">
                                    <option value="pending" ${req.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="submitted" ${req.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                                    <option value="approved" ${req.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${req.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                </select>
                            </div>
                            <div class="mt-2">
                                <label for="feedback${req.id}">Feedback:</label>
                                <textarea id="feedback${req.id}" name="feedback[${req.id}]" class="form-control form-control-sm" rows="2">${req.feedback}</textarea>
                            </div>
                            <div class="mt-2">
                                <a href="./submission/${req.file_name}" class="btn btn-secondary" download>Download File</a>
                                <a href="./submission/viewer.html?file=${req.file_name}" class="btn btn-secondary">View and Download File</a>
                            </div>
                            <div class="mt-2">
                                <label for="feedbackFile${req.id}">Upload Feedback File:</label>
                                <input class="form-control form-control-sm" type="file" id="feedbackFile${req.id}" name="feedbackFile[${req.id}]">
                            </div>
                            <div class="mt-2">
                                ${req.feedback_file ? `<a href="./feedback/${req.feedback_file}" class="btn btn-secondary" download>Download Feedback File</a>` : ''}
                            </div>
                        </div>
                    `;
                                });
                                checklistHtml += '<button type="submit" class="btn btn-primary mt-3">Update Requirements</button></form>';
                                $('#requirementChecklist').html(checklistHtml);

                                // Handle form submission
                                $('#requirementForm').on('submit', function(e) {
                                    e.preventDefault(); // Prevent default form submission
                                    var formData = new FormData(this);

                                    $.ajax({
                                        url: 'includes/update_requirements.php', // Server-side script for updates
                                        method: 'POST',
                                        data: formData,
                                        processData: false, // Required for FormData
                                        contentType: false,
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.success) {
                                                alert('Requirements updated successfully!');
                                                loadRequirements(teamId); // Reload requirements
                                            } else {
                                                alert('Error: ' + response.error);
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            console.error("AJAX error:", textStatus, errorThrown);
                                            alert('An error occurred while updating requirements. Please try again.');
                                        }
                                    });
                                });
                            } else {
                                $('#requirementChecklist').html('<p class="text-danger">' + response.error + '</p>');
                                console.error('Error in response:', response.error);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#requirementChecklist').html('<p class="text-danger">Error loading requirements. Please refresh the page.</p>');
                            console.error("AJAX error:", textStatus, errorThrown);
                            console.error("Response Text:", jqXHR.responseText);
                        }
                    });
                }
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
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">${req.name}</h5>
                                            <p class="card-text">${req.description}</p>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><strong>Due Date:</strong> ${new Date(req.due_date).toLocaleDateString()}</li>
                                            <li class="list-group-item"><strong>Status:</strong> ${req.status}</li>
                                            <li class="list-group-item"><strong>Feedback:</strong> ${req.feedback}</li>
                                        </ul>
                                        <div class="card-footer">
                                            ${req.feedback_file ? 
                                                `<a href="./feedback/${req.feedback_file}" class="btn btn-secondary" download>Download Feedback File</a>` 
                                                : ''}
                                        </div>
                                        <?php if ($role === 'leader') { ?>
                                        <div class="card-footer">
                                            <form id="uploadForm-${req.id}" enctype="multipart/form-data">
                                                <input type="hidden" name="document_name" value="${req.name}">
                                                <input type="hidden" name="requirement_id" value="${req.id}">
                                                <div class="mb-3">
                                                <label for="file-${req.id}" class="form-label">Upload File</label>
                                                <input class="form-control" type="file" id="file-${req.id}" name="file" required>
                                                </div>
                                                <button type="submit" class="btn btn-secondary">Submit File</button>
                                            </form>
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
                            $('#requirementChecklist').html('<p class="text-danger">' + response.error + '</p>');
                            console.error('Error fetching requirements:', response.error);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#requirementChecklist').html('<p class="text-danger">Error loading requirements. Please refresh the page.</p>');
                        console.error("AJAX error:", textStatus, errorThrown);
                        console.error("Response Text:", jqXHR.responseText);
                        console.error("Status Code:", jqXHR.status);
                    }
                });
            }

        <?php } ?>

    });
    <?php require 'includes/calendar.js.php'; ?>
</script>