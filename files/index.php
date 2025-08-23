<?php
define('TITLE', "Files");
include '../assets/layouts/header.php';
check_verified();
include '../assets/setup/db.inc.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all programs, ordered by college, then department, then name
// Remove the LEFT JOIN as parent_id column doesn't exist
$sqlPrograms = "SELECT * FROM programs ORDER BY college, department, name"; 
$stmtPrograms = $pdo->prepare($sqlPrograms);
$stmtPrograms->execute();
$programs = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);
if ($_SESSION['usertype'] == '1') {
    // Restrict access to students
    // admin = 0 students = 1 staff = 2
        echo '<div class="container mt-4"><div class="alert alert-warning">NOT AUTHORIZED, NICE TRY ASSHOLE!!!</div></div>';
        include '../assets/layouts/footer.php';
        header("Location: " . 'http://' . $_SERVER['HTTP_HOST'] . 'atlas/home');
}

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Files</h1>
        <?php if ($_SESSION['usertype'] == '0'): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload File
            </button>
        <?php endif; ?>
    </div>
    <hr>

    <div class="file-browser">
        <?php if (empty($programs)): ?>
            <p>No programs found.</p>
        <?php else: ?>
            <?php 
            $current_college = null;
            $current_department = null; // Track current department
            $college_index = 0; // For unique IDs
            $program_index = 0; // For unique IDs
            foreach ($programs as $program): 
                // Display college header only when it changes
                if ($program['college'] !== $current_college):
                    if ($current_college !== null):
                        // Close previous college's sub-folders 
                        echo '</div></div>'; // Close sub-folders and program-folder
                    endif;
                    $current_college = $program['college'];
                    $current_department = null; // Reset department when college changes
                    $college_id = 'college-' . $college_index++; // Unique ID for college collapse
            ?>
                <div class="folder program-folder mb-3">
                    <h5 data-bs-toggle="collapse" data-bs-target="#<?php echo $college_id; ?>" role="button" aria-expanded="false" aria-controls="<?php echo $college_id; ?>">
                        <i class="fas fa-university"></i> <?php echo htmlspecialchars($program['college']); ?> <i class="fas fa-chevron-down collapse-icon float-right"></i>
                    </h5>
                    <div class="collapse sub-folders ml-4" id="<?php echo $college_id; ?>">
            <?php 
                endif; // End of college header check

                // Display department header only when it changes within the current college
                if ($program['department'] !== $current_department && !empty($program['department'])):
                    $current_department = $program['department'];
            ?>
                    <h6 class="department-header mt-3 mb-2 text-secondary"><?php echo htmlspecialchars($program['department']); ?></h6>
            <?php
                endif; // End of department header check

                // Display Program Name + Specialization under the College/Department
                // Use the 'name' column directly as the base name
                $programBaseName = $program['name']; 
                $programDisplayName = htmlspecialchars($programBaseName);
                if (!empty($program['specialization'])) {
                    $programDisplayName .= ' - ' . htmlspecialchars($program['specialization']);
                }
                $program_id_html = 'program-' . $program_index++; // Unique ID for program collapse
            ?>
                <div class="folder program-sub-folder mb-2 ml-3"> <!-- Added ml-3 for indent under department -->
                     <h6 data-bs-toggle="collapse" data-bs-target="#<?php echo $program_id_html; ?>" role="button" aria-expanded="false" aria-controls="<?php echo $program_id_html; ?>">
                        <i class="fas fa-graduation-cap"></i> <?php echo $programDisplayName; ?> <i class="fas fa-chevron-down collapse-icon float-right"></i>
                    </h6>
                     <div class="collapse sub-folders ml-4" id="<?php echo $program_id_html; ?>">
                        <!-- Placeholder for files uploaded directly to program -->
                        <div class="uploaded-files-container" data-level="program" data-id="<?php echo $program['id']; ?>"></div>
                        <?php
                        // Fetch teams for the current program using program ID
                        $sqlTeams = "SELECT * FROM teams WHERE program = :program_id ORDER BY name";
                        $stmtTeams = $pdo->prepare($sqlTeams);
                        // Bind the program ID from the programs table
                        $stmtTeams->bindParam(':program_id', $program['id'], PDO::PARAM_INT); 
                        $stmtTeams->execute();
                        $teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if (empty($teams)): ?>
                            <p class="text-muted ml-3">No teams found in this program.</p>
                        <?php else: ?>
                            <?php foreach ($teams as $team): 
                                $team_id_html = 'team-' . $team['id']; // Unique ID for team collapse (optional)
                            ?>
                                <div class="folder team-folder" data-team-id="<?php echo $team['id']; ?>">
                                    <p><i class="fas fa-users"></i> <?php echo htmlspecialchars($team['name']); ?></p> 
                                    <!-- Placeholder for files uploaded directly to team -->
                                    <div class="uploaded-files-container ml-3" data-level="team" data-id="<?php echo $team['id']; ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php 
                // Close the last college's sub-folders
                if ($current_college !== null) {
                    echo '</div></div>'; // Close sub-folders and program-folder
                }
            ?>
        <?php endif; ?>
    </div>

</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="uploadForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel">Upload File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="fileToUpload" class="form-label">Select file</label>
            <input class="form-control" type="file" id="fileToUpload" name="fileToUpload" required>
          </div>
          <div class="mb-3">
            <!-- Change Label, ID, and Name -->
            <label for="uploadProgramLocation" class="form-label">Target Program</label> 
            <select class="form-select" id="uploadProgramLocation" name="program"> 
              <option value="" selected>Select Program</option> 
              <!-- Options/Optgroups populated by JS -->
            </select>
          </div>
          <div class="mb-3" id="teamSelectContainer" style="display: none;">
            <label for="uploadTeam" class="form-label">Target Team (Optional)</label> <!-- Clarify optional -->
            <select class="form-select" id="uploadTeam" name="team">
              <option value="" selected>Select Team</option> 
              <!-- Options populated by JS -->
            </select>
          </div>
           <div class="mb-3">
                <label for="fileDescription" class="form-label">Description (Optional)</label>
                <textarea class="form-control" id="fileDescription" name="description" rows="2"></textarea>
            </div>
            <div id="uploadStatus" class="mt-3"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>


<style>
    /* Optional: Basic styling for folders */
    .folder i {
        margin-right: 5px;
    }
    .program-folder > h5, .program-sub-folder > h6 {
        cursor: pointer; /* Indicate it might be clickable/expandable */
        padding: 5px; /* Add some padding for easier clicking */
        border-radius: 3px;
    }
     .program-folder > h5:hover, .program-sub-folder > h6:hover {
         background-color: #f8f9fa; /* Light background on hover */
     }

    .program-folder > h5 {
        font-weight: bold;
    }
     .program-folder > h5 > i.fa-university {
         color: #007bff; /* Primary color for college */
     }

    .department-header {
        font-weight: 500;
        padding-bottom: 2px;
        margin-left: -15px; /* Align with college header */
    }

    .program-sub-folder > h6 > i.fa-graduation-cap {
         color: #6f42c1; /* Custom purple for program */
    }
     .team-folder > p > i {
         color: #17a2b8; /* Info color for team */
     }
    .sub-folders {
        border-left: 1px solid #eee;
        padding-left: 15px;
        margin-left: 10px; /* Adjust as needed */
        /* Remove margin-top if added by collapse */
        margin-top: 0 !important; 
    }
    .team-folder p {
        margin-bottom: 0.5rem;
    }

    /* Style for the collapse icon */
    .collapse-icon {
        transition: transform 0.3s ease;
        font-size: 0.8em; /* Make icon smaller */
        color: #6c757d; /* Grey color */
        margin-top: 0.2em; /* Align vertically */
    }
    /* Rotate icon when collapsed element is shown */
    [aria-expanded="true"] > .collapse-icon { /* Ensure this targets the icon within the trigger */
        transform: rotate(180deg);
    }
    /* Remove default collapse margin */
    .collapse {
        margin-top: 0;
    }
    .collapsing {
         margin-top: 0;
         transition: height 0.3s ease;
    }
    .uploaded-files-container p {
        margin-bottom: 0.3rem;
        font-size: 0.9em;
    }
    .uploaded-files-container i.fa-file {
        color: #6c757d; /* Secondary color for file icon */
        margin-right: 5px;
    }
    /* Style for delete button */
    .delete-file-btn {
        margin-left: 8px;
        color: #dc3545; /* Danger color */
        cursor: pointer;
        font-size: 0.9em;
    }
    .delete-file-btn:hover {
        color: #a71d2a;
    }
</style>
<?php
include '../assets/layouts/footer.php'; 
?>
<script>
$(document).ready(function() {
    // --- Upload Modal Logic ---

    const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
    // Update selector for the first dropdown
    const programLocationSelect = $('#uploadProgramLocation'); 
    const teamSelect = $('#uploadTeam');
    const teamContainer = $('#teamSelectContainer');
    const uploadForm = $('#uploadForm');
    const uploadStatus = $('#uploadStatus');

    // Function to fetch locations
    function fetchLocations(level, parentId = null) {
        return $.ajax({
            url: 'get_locations.php',
            method: 'GET',
            data: { level: level, parent_id: parentId },
            dataType: 'json'
        });
    }

    // Populate program locations on modal show
    $('#uploadModal').on('show.bs.modal', function () {
        uploadStatus.html(''); // Clear status
        uploadForm[0].reset(); // Reset form
        teamContainer.hide();
        // Reset the first dropdown
        programLocationSelect.html('<option value="" selected>Select Program</option>'); 
        teamSelect.html('<option value="" selected>Select Team</option>'); // Reset team dropdown too

        // Fetch program locations instead of colleges
        fetchLocations('program_locations').done(function(data) { 
            console.log("fetchLocations('program_locations') response:", data); // <-- Add console log for success
            if (data.success && data.locations && data.locations.length > 0) {
                let currentCollege = null;
                let optgroup = null;

                $.each(data.locations, function(i, program) {
                    // Start a new optgroup if college changes
                    if (program.college !== currentCollege) {
                        currentCollege = program.college;
                        optgroup = $('<optgroup>', { label: currentCollege });
                        programLocationSelect.append(optgroup);
                    }

                    // Combine name and specialization for display
                    let displayText = program.name;
                    if (program.specialization) {
                        displayText += ' - ' + program.specialization;
                    }

                    // Append the option to the current optgroup
                    if (optgroup) {
                        optgroup.append($('<option>', {
                            value: program.id, // Use program ID as value
                            text: displayText
                        }));
                    } else {
                        // Log if optgroup is somehow null when it shouldn't be
                        console.error("Optgroup is null for program:", program); 
                    }
                });
            } else if (data.success) {
                 console.log("No program locations found."); // <-- Add console log
                 programLocationSelect.html('<option value="" selected>No programs found</option>');
            } else {
                 console.error("API call successful but data.success is false:", data.message); // <-- Add console log
                 programLocationSelect.html('<option value="" selected>Error loading programs</option>');
                 uploadStatus.html('<div class="alert alert-danger">Failed to load programs: ' + (data.message || '') + '</div>');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            // <-- Add detailed console log for failure
            console.error("fetchLocations('program_locations') failed:");
            console.error("Status:", textStatus);
            console.error("Error:", errorThrown);
            console.error("Response Text:", jqXHR.responseText); 
            programLocationSelect.html('<option value="" selected>Error loading programs</option>');
            uploadStatus.html('<div class="alert alert-danger">Failed to load programs. Check console for details.</div>');
        });
    });

    // Handle program location selection (previously programSelect handler)
    programLocationSelect.on('change', function() { // Changed selector
        const selectedProgramId = $(this).val();
        teamContainer.hide();
        teamSelect.html('<option value="" selected>Select Team</option>'); // Reset team dropdown

        if (selectedProgramId) {
            // Fetch teams based on the selected program ID
            fetchLocations('team', selectedProgramId).done(function(data) {
                 if (data.success && data.locations && data.locations.length > 0) {
                    // Add the default "Select Team" option first
                    teamSelect.html('<option value="" selected>Select Team (Optional)</option>'); 
                    $.each(data.locations, function(i, team) {
                        teamSelect.append($('<option>', {
                            value: team.id, // Use team ID as value
                            text: team.name
                        }));
                    });
                    teamContainer.show();
                 } else {
                     teamContainer.hide(); // Hide if no teams found for this program
                 }
            }).fail(function() {
                 // Display error within the team dropdown or status area
                 teamSelect.html('<option value="" selected>Error loading teams</option>'); 
                 uploadStatus.html('<div class="alert alert-danger">Failed to load teams.</div>');
            });
        }
    });

    // Handle form submission (remains largely the same, upload_handler.php needs adjustment)
    uploadForm.on('submit', function(e) {
        e.preventDefault();
        uploadStatus.html('<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Uploading...');

        var formData = new FormData(this);

        $.ajax({
            url: 'upload_handler.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    uploadStatus.html('<div class="alert alert-success">' + response.message + '</div>');
                    // Optionally close modal and refresh file list after delay
                    setTimeout(function() {
                        uploadModal.hide();
                        // loadUploadedFiles(); // Call function to refresh file list display
                        location.reload(); // Simple reload for now
                    }, 1500);
                } else {
                    uploadStatus.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                 uploadStatus.html('<div class="alert alert-danger">Upload failed. ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Server error.') + '</div>');
            }
        });
    });

    // --- Load Uploaded Files ---
    function loadUploadedFiles() {
        $('.uploaded-files-container').each(function() {
            const container = $(this);
            const level     = container.data('level');
            const id        = container.data('id');
            container.html('<small>Loading files…</small>');

            $.ajax({
                url: 'get_uploaded_files.php',
                method: 'GET',
                dataType: 'json',
                data: { level: level, id: id },
                success: function(resp) {
                    container.empty();
                    if (resp.success && resp.files.length) {
                        resp.files.forEach(f => {
                            // Store filepath safely
                            const filePathAttribute = f.filepath.replace(/"/g, '&quot;'); 
                            // Add a delete icon/button next to the link
                            container.append(
                                `<p>
                                  <a href="../uploads/${f.filepath}" target="_blank">
                                    <i class="fas fa-file"></i> ${f.filename}
                                  </a>
                                <?php if ($_SESSION['usertype'] == '0'): ?>
                                    <i class="fas fa-trash-alt delete-file-btn" 
                                       data-filepath="${filePathAttribute}" 
                                       title="Delete File"></i>
                                <?php endif; ?>
                                </p>`
                            );
                        });
                    } else if (resp.success) {
                        // Optional: Show message if no files
                        // container.html('<small class="text-muted">No files here.</small>');
                    } else {
                         container.html('<small class="text-danger">Error loading files list.</small>');
                    }
                },
                error: function() {
                    container.html('<small class="text-danger">Error loading files.</small>');
                }
            });
        });
    }

    // Initial load
    loadUploadedFiles();

    // --- Delete File Logic ---
    // Use event delegation for dynamically added delete buttons
    $('.file-browser').on('click', '.delete-file-btn', function() {
        const button = $(this);
        const filepath = button.data('filepath');
        
        if (!filepath) {
            alert('Error: Could not identify file to delete.');
            return;
        }

        // Confirmation dialog
        if (confirm(`Are you sure you want to delete this file?\n${filepath}`)) {
            // Disable button temporarily
            button.css('pointer-events', 'none').css('opacity', '0.5');

            $.ajax({
                url: 'delete_file.php',
                method: 'POST', // Use POST for actions that modify data
                dataType: 'json',
                data: { filepath: filepath }, // Send filepath to identify the file
                success: function(response) {
                    if (response.success) {
                        // Remove the paragraph containing the file link and button
                        button.closest('p').fadeOut(300, function() { $(this).remove(); });
                        // Optionally show a success message (e.g., using a toast notification library)
                        // alert(response.message); 
                    } else {
                        alert('Delete failed: ' + response.message);
                        // Re-enable button on failure
                        button.css('pointer-events', '').css('opacity', '');
                    }
                },
                error: function(xhr) {
                    alert('Delete request failed. Server error or invalid response.');
                    console.error("Delete Error:", xhr.responseText);
                     // Re-enable button on failure
                    button.css('pointer-events', '').css('opacity', '');
                }
            });
        }
    });

});
</script>

