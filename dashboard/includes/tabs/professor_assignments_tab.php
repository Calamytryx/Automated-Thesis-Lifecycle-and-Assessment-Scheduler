<!-- Class Professor Assignments Tab -->
<div class="tab-pane fade" id="professor-assignments" role="tabpanel" aria-labelledby="professor-assignments-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Faculty Assignments</h3>
                <p class="text-muted">Assign subject teachers to class sections</p>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="row">
            <div class="col-12">
                <div class="user-controls-container p-0">
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="profAssignSectionSelect" class="form-label">Select a section</label>
                            <select class="form-select user-control-height" id="profAssignSectionSelect" required>
                                <option value="">Select a section...</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-4">
                            <label for="profAssignProfSelect" class="form-label">Select a subject teacher</label>
                            <select class="form-select user-control-height" id="profAssignProfSelect" required>
                                <option value="">Select a faculty...</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button class="btn feature-btn prof-assign-add-btn user-control-height flex-fill" id="assignBtn">
                                <i class="fas fa-plus me-1"></i>Assign
                            </button>
                            <button class="btn prof-assign-refresh-btn user-control-height" id="refreshBtn">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Section</th>
                                <th style="width: 30%">Subject Teacher</th>
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

<!-- Remove Assignment Confirmation Modal -->
<div class="modal fade" id="profAssignDeleteConfirmModal" tabindex="-1" aria-labelledby="profAssignDeleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
                    </svg>
                </div>
                <h4 class="fw-bold mb-3" id="profAssignDeleteConfirmModalLabel">Confirm Removal</h4>
                <p>Are you sure you want to remove <span id="profAssignDeleteTarget" class="fw-semibold">this assignment</span>?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmProfAssignDeleteBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
function getAppRootPath() {
    const path = window.location.pathname;
    const dashboardIndex = path.indexOf('/dashboard/');
    return dashboardIndex === -1 ? '' : path.substring(0, dashboardIndex);
}

function professorAssignmentsApiUrl(action = '') {
    const appRoot = getAppRootPath();
    const base = `${appRoot}/api/professor_assignments.php`;
    return action ? `${base}?action=${encodeURIComponent(action)}` : base;
}

let allSections = [];
let allProfessors = [];
let currentAssignments = [];

function notifyUser(title, message, type = 'success') {
    if (typeof showToast === 'function') {
        showToast(title, message, type);
        return;
    }

    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        Swal.fire({
            icon: type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success'),
            title,
            text: message,
            timer: 2200,
            showConfirmButton: false
        });
        return;
    }

    alert(`${title}: ${message}`);
}

function confirmDeleteAssignment(targetLabel = 'this assignment') {
    return new Promise(function(resolve) {
        const modalElement = document.getElementById('profAssignDeleteConfirmModal');
        const confirmButton = document.getElementById('confirmProfAssignDeleteBtn');
        const targetElement = document.getElementById('profAssignDeleteTarget');

        if (targetElement) {
            targetElement.textContent = targetLabel;
        }

        if (!modalElement || !confirmButton || typeof bootstrap === 'undefined') {
            resolve(confirm('Remove this assignment?'));
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        let settled = false;

        const cleanup = function() {
            confirmButton.removeEventListener('click', onConfirm);
            modalElement.removeEventListener('hidden.bs.modal', onHidden);
        };

        const onConfirm = function() {
            if (settled) return;
            settled = true;
            cleanup();
            modal.hide();
            resolve(true);
        };

        const onHidden = function() {
            if (settled) return;
            settled = true;
            cleanup();
            resolve(false);
        };

        confirmButton.addEventListener('click', onConfirm);
        modalElement.addEventListener('hidden.bs.modal', onHidden);
        modal.show();
    });
}

function getAssignedSectionSet(assignments) {
    return new Set(
        (assignments || [])
            .map(item => (item.section || '').toString().trim())
            .filter(Boolean)
    );
}

function getAssignedProfessorIdSet(assignments) {
    return new Set(
        (assignments || [])
            .map(item => String(item.professor_id || '').trim())
            .filter(Boolean)
    );
}

function renderSectionDropdown() {
    const select = $('#profAssignSectionSelect');
    const selectedValue = select.val() || '';
    const assignedSections = getAssignedSectionSet(currentAssignments);

    select.find('option:not(:first)').remove();

    allSections.forEach(section => {
        const sectionValue = (section || '').toString().trim();
        if (!sectionValue) return;

        const option = $('<option></option>').val(sectionValue).text(sectionValue);
        if (assignedSections.has(sectionValue)) {
            option.prop('disabled', true).text(`${sectionValue} (Already assigned)`);
        }
        if (sectionValue === selectedValue && !assignedSections.has(sectionValue)) {
            option.prop('selected', true);
        }
        select.append(option);
    });

    if (selectedValue && assignedSections.has(selectedValue)) {
        select.val('');
    }
}

function renderProfessorDropdown() {
    const select = $('#profAssignProfSelect');
    const selectedValue = select.val() || '';
    const assignedProfessorIds = getAssignedProfessorIdSet(currentAssignments);

    select.find('option:not(:first)').remove();

    allProfessors.forEach(prof => {
        const professorId = String(prof.id);
        const professorName = `${prof.first_name} ${prof.last_name}`.trim();
        const option = $('<option></option>').val(professorId).text(professorName);

        if (assignedProfessorIds.has(professorId)) {
            option.prop('disabled', true).text(`${professorName} (Already assigned)`);
        }
        if (professorId === selectedValue && !assignedProfessorIds.has(professorId)) {
            option.prop('selected', true);
        }

        select.append(option);
    });

    if (selectedValue && assignedProfessorIds.has(selectedValue)) {
        select.val('');
    }
}

function applyAssignmentConstraints() {
    renderSectionDropdown();
    renderProfessorDropdown();
}

$(document).ready(function() {
    // Small delay to ensure tab is visible
    setTimeout(function() {
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
        loadSections();
        loadProfessors();
        loadAssignments();
    });
});

// Load all unique sections from users table
function loadSections() {
    $.ajax({
        url: professorAssignmentsApiUrl(),
        type: 'GET',
        data: { action: 'list_sections' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                allSections = response.data;
                applyAssignmentConstraints();
            }
        },
        error: function(xhr, status, error) {
            console.error('Load sections error:', error);
        }
    });
}

// Load all professors (faculty users)
function loadProfessors() {
    $.ajax({
        url: professorAssignmentsApiUrl(),
        type: 'GET',
        data: { action: 'list_professors' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                allProfessors = response.data;
                applyAssignmentConstraints();
            }
        },
        error: function(xhr, status, error) {
            console.error('Load professors error:', error);
        }
    });
}

// Load all assignments
function loadAssignments() {
    $.ajax({
        url: professorAssignmentsApiUrl(),
        type: 'GET',
        data: { action: 'list_section_professors' },
        dataType: 'json',
        success: function(response) {
            currentAssignments = response.data || [];
            displayAssignments(currentAssignments);
            applyAssignmentConstraints();
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
        tbody.html('<tr><td colspan="4" class="text-center text-muted py-5">No records found.</td></tr>');
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
                <button class="btn btn-sm btn-outline-danger bg-danger-subtle border-danger text-danger" onclick="deleteAssignment(${assignment.id})" title="Remove assignment">
                    <i class="fas fa-trash me-1"></i>Remove
                </button>
            </td>
        </tr>`;
    });

    tbody.html(html);
}

// Assign professor to section
function assignProfessor() {
    const section = $('#profAssignSectionSelect').val();
    const profId = $('#profAssignProfSelect').val();

    if (!section || !profId) {
        notifyUser('Warning', 'Please select both section and professor.', 'warning');
        return;
    }

    const payload = {
        section: section,
        professor_id: parseInt(profId)
    };
    
    $.ajax({
        url: professorAssignmentsApiUrl('assign_professor_to_section'),
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                notifyUser('Success', 'Professor assigned successfully.', 'success');
                $('#profAssignSectionSelect').val('');
                $('#profAssignProfSelect').val('');
                loadAssignments();
            } else {
                notifyUser('Error', response.message || 'Failed to assign professor.', 'error');
            }
        },
        error: function(xhr, status, error) {
            let errorMsg = error;
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || error;
            } catch (e) {
                errorMsg = xhr.responseText || error;
            }

            notifyUser('Error', errorMsg || `Request failed (${xhr.status})`, 'error');
        }
    });
}

// Delete assignment
function deleteAssignment(assignmentId) {
    const assignment = (currentAssignments || []).find(item => String(item.id) === String(assignmentId));
    const section = assignment?.section || 'Unknown section';
    const professorName = assignment?.first_name ? `${assignment.first_name} ${assignment.last_name}` : 'Unknown professor';
    const assignmentLabel = `${section} - ${professorName}`;

    confirmDeleteAssignment(assignmentLabel).then(function(confirmed) {
        if (!confirmed) return;

        $.ajax({
            url: professorAssignmentsApiUrl('delete_section_assignment'),
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                section_id: assignmentId
            }),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    notifyUser('Success', 'Assignment removed.', 'success');
                    loadAssignments();
                } else {
                    notifyUser('Error', response.message || 'Failed to delete assignment.', 'error');
                }
            },
            error: function(xhr, status, error) {
                notifyUser('Error', error || 'Failed to delete assignment.', 'error');
            }
        });
    });
}
</script>
