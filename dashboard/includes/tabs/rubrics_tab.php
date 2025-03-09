<?php
if (!defined('TITLE')) {
    header('Location: ../../index.php');
    exit();
}

if ($_SESSION['usertype'] != 0) {
    echo '<div class="alert alert-danger">You do not have permission to access this page.</div>';
    exit();
}
?>

<div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
    <!-- Rubric Information Container -->
    <div class="rubric-info-container p-3 mb-4 bg-light rounded border">
        <h4 class="mb-3">Rubric Management</h4>
        <p>Create and manage evaluation rubrics using Google Forms. Choose an option below to work with rubrics.</p>
        <p>After creating a form, copy its embed link and assign it to a defense schedule to replace the default scoring system.</p>
    </div>
    
    <div class="google-forms-container p-4 bg-white rounded border">
        <div class="row justify-content-center">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle fa-2x text-success mb-3"></i>
                        <h5 class="card-title">Create New Form</h5>
                        <p class="card-text">Create a new Google Form for rubrics and evaluations</p>
                        <button class="btn btn-primary" onclick="openGoogleForm('create')">
                            <i class="fas fa-edit me-2"></i>Create Form
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-folder-open fa-2x text-warning mb-3"></i>
                        <h5 class="card-title">Manage Existing Forms</h5>
                        <p class="card-text">View and edit your existing Google Forms</p>
                        <button class="btn btn-primary" onclick="openGoogleForm('manage')">
                            <i class="fas fa-list me-2"></i>View Forms
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Embed Link Assignment Section -->
        <div class="mt-4 p-3 bg-light rounded">
            <h5 class="mb-3"><i class="fas fa-link me-2 text-primary"></i>Assign Form to Defense Schedule</h5>
            <form id="embedLinkForm" class="mb-3">
                <div class="mb-3">
                    <label for="embedLink" class="form-label">Google Form Embed Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="embedLink" placeholder="Paste your Google Form embed link here" required>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="How to get embed link">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                    <div class="form-text">To get the embed link: Open your form > Click "Send" > Click the embed tab (< >) > Copy the HTML code</div>
                </div>
                
                <div class="mb-3">
                    <label for="defenseScheduleSelect" class="form-label">Select Defense Schedule</label>
                    <select class="form-select" id="defenseScheduleSelect" required>
                        <option value="" selected disabled>Choose a defense schedule...</option>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Assign Form to Schedule
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Assigned Forms Table -->
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-secondary"></i>Assigned Forms</h5>
                <button class="btn btn-sm btn-outline-secondary" id="refreshAssignedForms">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="assignedFormsTable">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Defense Date</th>
                            <th>Form Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <p class="text-muted mb-0">No forms assigned yet</p>
                                <small>Assign a form to see it listed here</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 p-3 bg-light rounded">
            <h6><i class="fas fa-info-circle me-2 text-primary"></i>How to use Google Forms for rubrics:</h6>
            <ol class="mb-0">
                <li>Click "Create Form" to open Google Forms in a popup window</li>
                <li>Design your evaluation form with appropriate questions and scoring</li>
                <li>When finished, click "Send" and go to the embed tab (< >)</li>
                <li>Copy the entire HTML code and paste it in the "Google Form Embed Link" field above</li>
                <li>Select the defense schedule you want to assign this form to</li>
                <li>Click "Assign Form to Schedule" to save your assignment</li>
                <li>The assigned form will replace the default scoring system for that defense</li>
            </ol>
        </div>
    </div>
    
    <!-- Form Popup Status -->
    <div id="popupStatus" class="alert alert-info mt-3" style="display: none;">
        <i class="fas fa-external-link-alt me-2"></i>
        <span id="popupStatusText">Google Forms is open in a popup window</span>
    </div>
    
    <!-- Help Modal -->
    <div class="modal fade" id="embedHelpModal" tabindex="-1" aria-labelledby="embedHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="embedHelpModalLabel">How to Get Google Form Embed Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="steps-container">
                        <div class="step mb-4">
                            <h6>Step 1: Open your Google Form</h6>
                            <p>Go to your Google Form in edit mode.</p>
                        </div>
                        <div class="step mb-4">
                            <h6>Step 2: Click the "Send" button</h6>
                            <p>In the top-right corner of your form, click the "Send" button.</p>
                        </div>
                        <div class="step mb-4">
                            <h6>Step 3: Go to the Embed tab</h6>
                            <p>In the "Send form" dialog, click the embed tab (looks like &lt; &gt;).</p>
                        </div>
                        <div class="step mb-4">
                            <h6>Step 4: Copy the HTML code</h6>
                            <p>Copy the entire HTML code provided in the text box. It should start with &lt;iframe&gt; and end with &lt;/iframe&gt;.</p>
                        </div>
                        <div class="step">
                            <h6>Step 5: Paste in the form</h6>
                            <p>Paste the copied HTML code into the "Google Form Embed Link" field in this dashboard.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
