<!-- Class Professor Assignments Tab -->
<div class="tab-pane fade" id="professor-assignments" role="tabpanel" aria-labelledby="professor-assignments-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Class Professor Assignments</h3>
                <p class="text-muted">Assign research professors to class sections</p>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="row">
            <div class="col-12">
                <div class="user-controls-container p-0">
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="profAssignSectionSelect" class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select" id="profAssignSectionSelect" required>
                                <option value="">Select a section...</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-4">
                            <label for="profAssignProfSelect" class="form-label">Research Professor <span class="text-danger">*</span></label>
                            <select class="form-select" id="profAssignProfSelect" required>
                                <option value="">Select a professor...</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button class="btn feature-btn user-control-height flex-fill" id="assignBtn" style="background-color: #0d6efd; color: white;">
                                <i class="fas fa-plus me-1"></i>Assign
                            </button>
                            <button class="btn btn-secondary user-control-height" id="refreshBtn">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Section</th>
                                <th style="width: 30%">Research Professor</th>
                                <th style="width: 25%">Email</th>
                                <th class="text-center" style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentsBody">
                            <tr><td colspan="4" class="text-center text-muted py-5">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('🚀 Professor Assignments Tab - Document Ready');
    
    // Small delay to ensure tab is visible
    setTimeout(function() {
        console.log('⏰ Starting data load after delay');
        // Load all data
        loadSections();
        loadProfessors();
        loadAssignments();
    }, 100);

    // Event listeners
    $('#assignBtn').on('click', function() {
        assignProfessor();
    });

    $('#refreshBtn').on('click', function() {
        loadAssignments();
    });
    
    // Also load when tab is shown
    $('a[href="#professor-assignments"]').on('shown.bs.tab', function (e) {
        console.log('📑 Professor Assignments tab shown - reloading data');
        loadSections();
        loadProfessors();
        loadAssignments();
    });
});

// Load all unique sections from users table
function loadSections() {
    console.log('🔍 loadSections() called');
    
    // Debug: Check if element exists BEFORE AJAX
    const selectBefore = $('#profAssignSectionSelect');
    console.log('🔍 BEFORE AJAX - Select element exists:', selectBefore.length);
    console.log('🔍 BEFORE AJAX - Select element:', selectBefore[0]);
    console.log('🔍 BEFORE AJAX - Is visible:', selectBefore.is(':visible'));
    console.log('🔍 BEFORE AJAX - Parent visible:', selectBefore.parent().is(':visible'));
    
    $.ajax({
        url: '/api/professor_assignments.php',
        type: 'GET',
        data: { action: 'list_sections' },
        dataType: 'json',
        success: function(response) {
            console.log('✅ loadSections SUCCESS:', response);
            if (response.success && response.data) {
                const select = $('#profAssignSectionSelect');
                console.log('📍 AFTER AJAX - Select element found:', select.length > 0 ? 'YES' : 'NO');
                console.log('📍 AFTER AJAX - Select element:', select[0]);
                console.log('📍 AFTER AJAX - Current HTML:', select.html());
                
                select.find('option:not(:first)').remove();
                console.log('🗑️ Cleared existing options');
                
                let optionsAdded = 0;
                response.data.forEach(section => {
                    const option = $('<option></option>').val(section).text(section);
                    select.append(option);
                    optionsAdded++;
                    console.log('➕ Added option:', section);
                });
                
                console.log('📊 Added ' + optionsAdded + ' sections');
                console.log('📊 Total options now:', select.find('option').length);
                console.log('📊 Final HTML:', select.html());
                
                // Verify they're actually in the DOM
                select.find('option').each(function(i, opt) {
                    console.log('  Option ' + i + ':', opt.value, opt.text);
                });
                
            } else {
                console.warn('⚠️ loadSections: No data or not successful', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Load sections error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                responseText: xhr.responseText
            });
        }
    });
}

// Load all professors (faculty users)
function loadProfessors() {
    console.log('🔍 loadProfessors() called');
    $.ajax({
        url: '/api/professor_assignments.php',
        type: 'GET',
        data: { action: 'list_professors' },
        dataType: 'json',
        success: function(response) {
            console.log('✅ loadProfessors SUCCESS:', response);
            if (response.success && response.data) {
                const select = $('#profAssignProfSelect');
                select.find('option:not(:first)').remove();
                response.data.forEach(prof => {
                    select.append(`<option value="${prof.id}">${prof.first_name} ${prof.last_name}</option>`);
                });
                console.log('📊 Loaded ' + response.data.length + ' professors');
            } else {
                console.warn('⚠️ loadProfessors: No data or not successful', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Load professors error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                responseText: xhr.responseText
            });
        }
    });
}

// Load all assignments
function loadAssignments() {
    $.ajax({
        url: '/api/professor_assignments.php',
        type: 'GET',
        data: { action: 'list_section_professors' },
        dataType: 'json',
        success: function(response) {
            displayAssignments(response.data || []);
        },
        error: function(xhr, status, error) {
            console.error('Load assignments error:', error);
            $('#assignmentsBody').html('<tr><td colspan="4" class="text-center text-danger py-5">Failed to load assignments</td></tr>');
        }
    });
}

// Display assignments in table
function displayAssignments(assignments) {
    const tbody = $('#assignmentsBody');
    
    if (!assignments || assignments.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center text-muted py-5">No assignments yet</td></tr>');
        return;
    }

    let html = '';
    assignments.forEach(assignment => {
        const profName = assignment.first_name ? `${assignment.first_name} ${assignment.last_name}` : 'Unknown';
        const section = assignment.section || 'N/A';
        const email = assignment.email || 'N/A';
        
        html += `<tr>
            <td>${section}</td>
            <td>${profName}</td>
            <td>${email}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-danger" onclick="deleteAssignment(${assignment.id})" title="Remove">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });

    tbody.html(html);
}

// Assign professor to section
function assignProfessor() {
    console.log('=== ASSIGN FUNCTION CALLED ===');
    const section = $('#profAssignSectionSelect').val();
    const profId = $('#profAssignProfSelect').val();

    console.log('Section value:', section, 'Type:', typeof section);
    console.log('Professor ID value:', profId, 'Type:', typeof profId);
    console.log('Full dropdown contents - Section:', $('#profAssignSectionSelect').find('option').length, 'options');
    console.log('Full dropdown contents - Professor:', $('#profAssignProfSelect').find('option').length, 'options');

    if (!section || !profId) {
        console.log('❌ VALIDATION FAILED - section=' + section + ', profId=' + profId);
        alert('Please select both section and professor');
        return;
    }

    const payload = {
        section: section,
        professor_id: parseInt(profId)
    };

    console.log('✅ Payload ready:', JSON.stringify(payload));

    console.log('📤 Sending AJAX request to /api/professor_assignments.php?action=assign_professor_to_section');
    
    $.ajax({
        url: '/api/professor_assignments.php?action=assign_professor_to_section',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        dataType: 'json',
        success: function(response) {
            console.log('✅ SUCCESS Response:', response);
            if (response.success) {
                alert('Professor assigned successfully');
                $('#profAssignSectionSelect').val('');
                $('#profAssignProfSelect').val('');
                loadAssignments();
            } else {
                alert('Error: ' + (response.message || 'Failed to assign'));
            }
        },
        error: function(xhr, status, error) {
            console.log('❌ ERROR Response received');
            let errorMsg = error;
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || error;
            } catch (e) {
                errorMsg = xhr.responseText || error;
            }
            
            console.error('❌ Assign error details:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error,
                parsed: errorMsg
            });
            
            alert('ERROR (' + xhr.status + '): ' + errorMsg);
        }
    });
}

// Delete assignment
function deleteAssignment(assignmentId) {
    if (!confirm('Remove this assignment?')) return;

    $.ajax({
        url: '/api/professor_assignments.php?action=delete_section_assignment',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            section_id: assignmentId
        }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Assignment removed');
                loadAssignments();
            } else {
                alert('Error: ' + (response.message || 'Failed to delete'));
            }
        }
    });
}
</script>
