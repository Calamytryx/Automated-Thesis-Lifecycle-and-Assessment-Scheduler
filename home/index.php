<?php

define('TITLE', "Home");
include '../assets/layouts/header.php';
check_verified();

?>


<main role="main" class="container">

    <div class="row">
        <div class="col-sm-3">

            <?php include('../assets/layouts/profile-card.php'); ?>

        </div>
        <div class="col-sm-9">

            <div class="d-flex align-items-center p-3 mt-5 mb-3 text-white-50 bg-color rounded box-shadow">
                <img class="mr-3" src="../assets/images/logonotextwhite.png" alt="" width="48" height="48">
                <div class="lh-100">
                    <h6 class="mb-0 text-white lh-100">Hey there, <?php echo $_SESSION['username']; ?></h6>
                    <small>Last logged in at <?php echo date("m-d-Y", strtotime($_SESSION['last_login_at'])); ?></small>
                </div>
            </div>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="thesis-topic-tab" data-bs-toggle="tab" data-bs-target="#thesis-topic" type="button" role="tab" aria-controls="thesis-topic" aria-selected="true">Thesis Topic Decision</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="research-title-tab" data-bs-toggle="tab" data-bs-target="#research-title" type="button" role="tab" aria-controls="research-title" aria-selected="false">Research Title Acceptance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="scheduling-tab" data-bs-toggle="tab" data-bs-target="#scheduling" type="button" role="tab" aria-controls="scheduling" aria-selected="false">Scheduling System</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="requirement-checker-tab" data-bs-toggle="tab" data-bs-target="#requirement-checker" type="button" role="tab" aria-controls="requirement-checker" aria-selected="false">Requirement Checker</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="thesis-topic" role="tabpanel" aria-labelledby="thesis-topic-tab">
                    <div class="my-3 p-3 bg-white rounded box-shadow">
                        <h6 class="border-bottom border-gray pb-2 mb-0">Thesis Topic Decision Tool</h6>
                        <div class="media text-muted pt-3">
                            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                                <strong class="d-block text-gray-dark">Suggested Topics</strong>
                                <!-- Add form or content for thesis topic suggestion here -->
                                <form id="topicSuggestionForm">
                                    <div class="mb-3">
                                        <label for="field" class="form-label">Field of Study</label>
                                        <input type="text" class="form-control" id="field" name="field" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Get Suggestions</button>
                                </form>
                                <div id="suggestedTopics" class="mt-3"></div>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="research-title" role="tabpanel" aria-labelledby="research-title-tab">
                    <div class="my-3 p-3 bg-white rounded box-shadow">
                        <h6 class="border-bottom border-gray pb-2 mb-0">Research Title Acceptance Tool</h6>
                        <div class="media text-muted pt-3">
                            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                                <strong class="d-block text-gray-dark">Title Uniqueness Check</strong>
                                <!-- Add form for research title submission here -->
                                <form id="titleSubmissionForm">
                                    <div class="mb-3">
                                        <label for="researchTitle" class="form-label">Proposed Research Title</label>
                                        <input type="text" class="form-control" id="researchTitle" name="researchTitle" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Check Uniqueness</button>
                                </form>
                                <div id="uniquenessResult" class="mt-3"></div>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="scheduling" role="tabpanel" aria-labelledby="scheduling-tab">
                    <div class="my-3 p-3 bg-white rounded box-shadow">
                        <div class="media text-muted pt-3">
                            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                                <strong class="d-block text-gray-dark">Schedule</strong>
                                <div id="userSchedule">
                                    <!-- Calendar will be inserted here -->
                                </div>
                                <!-- Add this inside the scheduling tab -->
                                <div id="userDefenseSchedule">
                                    <!-- User's defense schedule will be displayed here -->
                                </div>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="requirement-checker" role="tabpanel" aria-labelledby="requirement-checker-tab">
                    <div class="my-3 p-3 bg-white rounded box-shadow">
                        <h6 class="border-bottom border-gray pb-2 mb-0">Requirement Checker Tool</h6>
                        <div class="media text-muted pt-3">
                            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                                <strong class="d-block text-gray-dark">Document Checklist</strong>
                                <!-- Add checklist or form for requirement checking here -->
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

<script>
$(document).ready(function() {
    // Thesis Topic Decision Tool
    $('#topicSuggestionForm').on('submit', function(e) {
        e.preventDefault();
        var field = $('#field').val();
        // AJAX call to get topic suggestions
        $.ajax({
            url: 'includes/get_topic_suggestions.php',
            method: 'POST',
            data: { field: field },
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
                $('#suggestedTopics').html('<p>Error fetching suggestions. Please try again.</p>');
            }
        });
    });

    // Research Title Acceptance Tool
    $('#titleSubmissionForm').on('submit', function(e) {
        e.preventDefault();
        var title = $('#researchTitle').val();
        // AJAX call to check title uniqueness
        $.ajax({
            url: 'includes/check_title_uniqueness.php',
            method: 'POST',
            data: { title: title },
            dataType: 'json',
            success: function(response) {
                $('#uniquenessResult').html('<p>Uniqueness Score: ' + response.score + '</p><p>' + response.feedback + '</p>');
            },
            error: function() {
                $('#uniquenessResult').html('<p>Error checking uniqueness. Please try again.</p>');
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
                if(response.success) {
                    var userScheduleHtml = createScheduleTable(response.user_schedules, "User Schedule");
                    var defenseScheduleHtml = createScheduleTable(response.defense_schedules, "Defense Schedule");
                    
                    $('#userSchedule').html(userScheduleHtml + defenseScheduleHtml);
                } else {
                    $('#userSchedule').html('<p>Error loading schedules: ' + response.error + '</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
                $('#userSchedule').html('<p>Error loading schedules. Please try again later.</p>');
            }
        });
    }

    function createScheduleTable(schedules, title) {
        if (schedules.length === 0) {
            return '<h4>' + title + '</h4><p>No scheduled events found.</p>';
        }

        var scheduleHtml = '<h4>' + title + '</h4>';
        scheduleHtml += '<table class="table table-striped">';
        scheduleHtml += '<thead><tr><th>Date</th><th>Time</th><th>Room</th><th>Description</th></tr></thead><tbody>';
        schedules.forEach(function(event) {
            scheduleHtml += '<tr>';
            scheduleHtml += '<td>' + event.date + '</td>';
            scheduleHtml += '<td>' + event.start_time + ' - ' + event.end_time + '</td>';
            scheduleHtml += '<td>' + event.room + '</td>';
            scheduleHtml += '<td>' + event.description + '</td>';
            scheduleHtml += '</tr>';
        });
        scheduleHtml += '</tbody></table>';
        return scheduleHtml;
    }

    // Requirement Checker Tool
    function loadRequirements() {
        $.ajax({
            url: 'includes/get_requirements.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                var checklistHtml = '<form id="requirementForm">';
                response.requirements.forEach(function(req) {
                    checklistHtml += '<div class="form-check">' +
                        '<input class="form-check-input" type="checkbox" value="' + req.id + '" id="req' + req.id + '" name="requirements[]">' +
                        '<label class="form-check-label" for="req' + req.id + '">' + req.name + '</label>' +
                        '</div>';
                });
                checklistHtml += '<button type="submit" class="btn btn-primary mt-3">Update Requirements</button></form>';
                $('#requirementChecklist').html(checklistHtml);
            },
            error: function() {
                $('#requirementChecklist').html('<p>Error loading requirements. Please refresh the page.</p>');
            }
        });
    }

    loadRequirements();

    $(document).on('submit', '#requirementForm', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: 'includes/update_requirements.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                alert(response.message);
            },
            error: function() {
                alert('Error updating requirements. Please try again.');
            }
        });
    });

    // Call this function when the page loads
    $(document).ready(function() {
        loadUserSchedule();
    });
});
</script>