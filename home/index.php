
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
        <!-- <div class="col-sm-3">

            <?php //include('../assets/layouts/profile-card.php'); ?>

        </div> -->
        <div class="col-sm-12">

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
                            <button id="getTopicsBtn" class="btn btn-primary mt-3">Get Latest Thesis Topics</button>
                        </div>
                        <div id="topicAnalysisResult" class="mt-3">
                            <!-- Loading spinner (initially hidden) -->
                            <div id="loadingSpinner" class="text-center d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Searching for the latest topics...</p>
                            </div>
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
                                    <div class="mb-3">
                                        <label for="researchField" class="form-label">Research Field</label>
                                        <input type="text" class="form-control" id="researchField" name="researchField" required>
                                    </div>
                                    <button type="button" id="submitTitleBtn" class="btn btn-primary">Check Title</button>
                                </form>
                                <div id="uniquenessResult" class="mt-3"></div>
                                <div id="aiSuggestions" class="mt-3"></div>
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
<!-- AI GEMINI MODULE -->
<!-- Main Module JS -->
<script type="module" src="../assets/js/mainModule.js"></script>
<!-- app.js -->
<script type="module" src="../assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

