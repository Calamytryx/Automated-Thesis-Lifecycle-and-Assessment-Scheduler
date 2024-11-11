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

?>


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
                    <!-- <a class="nav-link active" id="thesis-topic-link" data-bs-toggle="pill" href="#thesis-topic" role="tab" aria-controls="thesis-topic" aria-selected="true">Thesis Topic Decision</a> -->
                    <a class="nav-link active" id="scheduling-link" data-bs-toggle="pill" href="#scheduling" role="tab" aria-controls="scheduling" aria-selected="false">Calendar</a>
                    <a class="nav-link" id="research-title-link" data-bs-toggle="pill" href="#research-title" role="tab" aria-controls="research-title" aria-selected="false">Research Title Acceptance</a>
                    <a class="nav-link" id="requirement-checker-link" data-bs-toggle="pill" href="#requirement-checker" role="tab" aria-controls="requirement-checker" aria-selected="false">Requirement Checker</a>
                </div>
            </div>
        </div>

        <div class="col-sm-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!--<div class="tab-pane fade show active" id="thesis-topic" role="tabpanel" aria-labelledby="thesis-topic-link">
                    <div class="my-3 p-3 home-sidebar-box">
                        <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Thesis Topic Decision Tool</h6>
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
                            <button id="getTopicsBtn" class="btn btn-primary mt-3 feature-btn">Get Latest Topics</button>
                        </div>
                        <div id="topicAnalysisResult" class="mt-3"> -->
                <!-- Loading spinner (initially hidden) -->
                <!-- <div id="loadingSpinner" class="text-center d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Searching for the latest topics...</p>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="tab-pane fade show active" id="scheduling" role="tabpanel" aria-labelledby="scheduling-link">
                    <div class="my-3 p-3 home-sidebar-box">
                        <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Schedule</h6>
                        <div class="media text-muted pt-3">
                            <!-- <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-secondary"> -->
                            <div id="calendar"></div> <!-- Calendar div -->
                            </p>
                        </div>
                    </div>
                </div>

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
<script type="module" src="../assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
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
                data: {
                    title: title
                },
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
                        $('#userSchedule').html('<p>Error loading schedules: ' + response.error + '</p>');
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

            // Use setTimeout to ensure the calendar renders correctly
            //setTimeout(function() {
            calendar.render();
            //}, 100); // Adjust the timeout duration if necessary
        }

        <?php if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) { ?>
            // Requirement Checker Tool
            function loadRequirements() {
                $.ajax({
                    url: 'includes/get_requirements.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var checklistHtml = '<form id="requirementForm">';
                            response.requirements.forEach(function(req) {
                                checklistHtml += `
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="${req.id}" id="req${req.id}" name="requirements[]" ${req.status !== 'pending' ? 'checked' : ''}>
                                <label class="form-check-label" for="req${req.id}"><strong>${req.name}</strong></label>
                                <p class="mb-1 text-muted">${req.description}</p>
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
                            </div>
                        `;
                            });
                            checklistHtml += '<button type="submit" class="btn btn-primary mt-3">Update Requirements</button></form>';
                            $('#requirementChecklist').html(checklistHtml);
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
        <?php } elseif ($_SESSION['usertype'] == 1) { ?>
        
            function loadRequirements() {
                $.ajax({
                    url: 'includes/get_requirements.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var checklistHtml = '<form id="requirementForm">';
                            response.requirements.forEach(function(req) {
                                checklistHtml += '<div class="form-check mb-3">' +
                                    '<input class="form-check-input" type="checkbox" value="' + req.id + '" id="req' + req.id + '" name="requirements[]">' +
                                    '<label class="form-check-label" for="req' + req.id + '"><strong>' + req.name + '</strong></label>' +
                                    '<p class="mb-1 text-muted">' + req.description + '</p>' +
                                    '<small class="text-muted">Due Date: ' + new Date(req.due_date).toLocaleDateString() + '</small>' +
                                    '</div>';
                            });
                            checklistHtml += '<button type="submit" class="btn btn-primary mt-3">Update Requirements</button></form>';
                            $('#requirementChecklist').html(checklistHtml);
                        } else {
                            $('#requirementChecklist').html('<p class="text-danger">' + response.error + '</p>');
                            console.error('Error fetching requirements:', response.error);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#requirementChecklist').html('<p class="text-danger">Error loading requirements. Please refresh the page.</p>');
                        console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            }
        <?php } ?>


        // // Call loadRequirements() on page load
        // $(document).ready(function() {
        //     loadRequirements();

        //     // Handle form submission
        //     $('#requirementChecklist').on('submit', '#requirementForm', function(e) {
        //         e.preventDefault();
        //         var selectedRequirements = $(this).serialize();

        //         $.ajax({
        //             url: 'includes/update_requirements.php', // Ensure this endpoint exists
        //             method: 'POST',
        //             data: selectedRequirements,
        //             dataType: 'json',
        //             success: function(response) {
        //                 if (response.success) {
        //                     alert('Requirements updated successfully.');
        //                 } else {
        //                     alert('Error updating requirements: ' + response.error);
        //                 }
        //             },
        //             error: function() {
        //                 alert('An error occurred while updating requirements.');
        //             }
        //         });
        //     });
        // });
        loadRequirements();
        // $(document).on('submit', '#requirementForm', function(e) {
        //     e.preventDefault();
        //     var formData = $(this).serialize();
        //     $.ajax({
        //         url: 'includes/update_requirements.php',
        //         method: 'POST',
        //         data: formData,
        //         dataType: 'json',
        //         success: function(response) {
        //             alert(response.message);
        //         },
        //         error: function() {
        //             alert('Error updating requirements. Please try again.');
        //         }
        //     });
        // });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        // Fetch events via AJAX
        $.ajax({
            url: 'includes/get_user_schedule.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const events = [];

                    // Process defense schedules
                    response.defense_schedules.forEach(function(defense) {
                        events.push({
                            title: defense.description,
                            start: `${defense.date}T${defense.start_time}`,
                            end: `${defense.date}T${defense.end_time}`,
                            location: defense.room
                        });
                    });

                    // Process user schedules
                    response.user_schedules.forEach(function(schedule) {
                        events.push({
                            title: schedule.description,
                            start: `${getNextDateForDay(schedule.date)}T${schedule.start_time}`,
                            end: `${getNextDateForDay(schedule.date)}T${schedule.end_time}`,
                            location: schedule.room
                        });
                    });

                    // Initialize FullCalendar with events
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        height: 'auto',
                        events: events,
                        eventClick: function(info) {
                            const title = info.event.title;
                            const room = info.event.extendedProps.location;
                            alert('Event: ' + title + '\nRoom: ' + room);
                        }
                    });
                    calendar.render();
                } else {
                    console.error('Error fetching schedules:', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
            }
        });

        // Function to get the next date for a given day of the week
        function getNextDateForDay(dayOfWeek) {
            const days = {
                'Monday': 1,
                'Tuesday': 2,
                'Wednesday': 3,
                'Thursday': 4,
                'Friday': 5,
                'Saturday': 6,
                'Sunday': 0
            };
            const today = new Date();
            const targetDay = days[dayOfWeek];
            const currentDay = today.getDay();
            let delta = targetDay - currentDay;
            if (delta < 0) delta += 7;
            const nextDate = new Date(today);
            nextDate.setDate(today.getDate() + delta);
            return nextDate.toISOString().split('T')[0];
        }

        // Optional: Handle window resize to adjust calendar size
        window.addEventListener('resize', function() {
            if (calendar) {
                calendar.updateSize();
            }
        });
    });
</script>