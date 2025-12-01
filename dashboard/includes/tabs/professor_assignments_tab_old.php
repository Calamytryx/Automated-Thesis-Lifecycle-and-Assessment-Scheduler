<!-- Professor Assignments Tab -->
<div class="tab-pane fade" id="professor-assignments" role="tabpanel" aria-labelledby="professor-assignments-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Class Professor Assignments</h3>
                <p class="text-muted">Assign research professors to class sections</p>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="row">
                <!-- Controls Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="user-controls-container p-0 mt-3">
                            <!-- Assign Section Teacher Row -->
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label for="sectionSelect" class="form-label">Section <span class="text-danger">*</span></label>
                                    <select class="form-select" id="sectionSelect" required>
                                        <option value="">Select a section...</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 col-md-4">
                                    <label for="sectionProfSelect" class="form-label">Research Professor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="sectionProfSelect" required>
                                        <option value="">Select a professor...</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 col-md-4 d-flex gap-2">
                                    <button class="btn feature-btn user-control-height flex-fill" id="assignSectionBtn" style="background-color: #0d6efd; color: white;">
                                        <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                        <span class="d-none d-lg-inline">Assign</span>
                                        <span class="d-lg-none">Assign</span>
                                    </button>
                                    <button class="btn btn-secondary user-control-height" id="refreshSectionBtn">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Teachers Table -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm db-table" id="sectionTeachersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="d-none d-md-table-cell" style="width: 30%">Section</th>
                                        <th class="d-none d-md-table-cell" style="width: 30%">Research Professor</th>
                                        <th class="d-none d-md-table-cell" style="width: 20%">Email</th>
                                        <th class="text-center" style="width: 10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="sectionTeachersBody">
                                    <tr><td colspan="4" class="text-center text-muted py-5">Loading section teachers...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Team Assignments Tab -->
            <div class="tab-pane fade" id="team-content" role="tabpanel" aria-labelledby="team-tab">
                <!-- Controls Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="user-controls-container p-0 mt-3">
                            <!-- Search and Filter Row -->
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-12 col-md-4 col-lg-3">
                                    <div class="users-search-container">
                                        <div class="input-group user-control-height m-0">
                                            <span class="input-group-text border-0"> 
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text" class="form-control border-0" id="teamSearchInput" placeholder="Search teams...">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-2 col-lg-2">
                                    <select class="form-select user-control-height" id="defenseTypeFilter">
                                        <option value="">All Types</option>
                                        <option value="title_proposal">Title Proposal</option>
                                        <option value="title_defense">Title Defense</option>
                                        <option value="final_defense">Final Defense</option>
                                        <option value="re-defense">Re-defense</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 col-md-6 col-lg-7">
                                    <div class="d-flex gap-2">
                                        <button class="btn feature-btn user-control-height flex-fill" id="assignAdviserBtn" style="background-color: #0d6efd; color: white;">
                                            <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                            <span class="d-none d-lg-inline">Assign Adviser</span>
                                            <span class="d-lg-none">Assign</span>
                                        </button>
                                        <button class="btn btn-secondary user-control-height" id="refreshBtn">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignments Table -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm db-table" id="assignmentsTable">
                                <thead>
                                    <tr>
                                        <th class="d-none d-md-table-cell">Team</th>
                                        <th class="d-table-cell d-md-none">Team</th>
                                        <th class="d-none d-lg-table-cell">Adviser</th>
                                        <th class="d-none d-sm-table-cell">Program</th>
                                        <th class="d-none d-md-table-cell">Defense Type</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="assignmentsBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade" id="history-content" role="tabpanel" aria-labelledby="history-tab">
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm db-table" id="historyTable">
                                <thead>
                                    <tr>
                                        <th class="d-none d-md-table-cell">Team</th>
                                        <th class="d-table-cell d-md-none">Team</th>
                                        <th class="d-none d-lg-table-cell">Adviser</th>
                                        <th class="d-none d-sm-table-cell">Action</th>
                                        <th class="d-none d-md-table-cell">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="historyBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Assignment Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">Assign Adviser to Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm">
                    <div class="mb-3">
                        <label for="teamSelect" class="form-label">Team <span class="text-danger">*</span></label>
                        <select class="form-select" id="teamSelect" required>
                            <option value="">Select a team...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adviserSelect" class="form-label">Adviser <span class="text-danger">*</span></label>
                        <select class="form-select" id="adviserSelect" required>
                            <option value="">Select an adviser...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="defenseType" class="form-label">Defense Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="defenseType" required>
                            <option value="">Select defense type...</option>
                            <option value="title_proposal">Title Proposal (Section Adviser)</option>
                            <option value="title_defense">Title Defense</option>
                            <option value="final_defense">Final Defense</option>
                            <option value="re-defense">Re-defense</option>
                        </select>
                        <small class="form-text text-muted d-block mt-2">
                            <strong>Title Proposal:</strong> Professor acts as adviser for entire section<br>
                            <strong>Other Types:</strong> Specific adviser for this team's defense
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAssignBtn">Assign</button>
            </div>
        </div>
    </div>
</div>

<script>
    let assignModalInstance = null;

    $(document).ready(function() {
        // Initialize modal once
        const modalElement = document.getElementById('assignModal');
        assignModalInstance = new bootstrap.Modal(modalElement, {backdrop: 'static', keyboard: false});

        // Load section teachers data
        loadSectionTeachers();
        populateSectionSelect();
        populateSectionProfSelect();

        // Section teachers event listeners
        $('#assignSectionBtn').on('click', function() {
            assignSectionTeacher();
        });

        $('#refreshSectionBtn').on('click', function() {
            loadSectionTeachers();
        });

        // Load team assignments data
        loadTeamAssignments();
        loadAdviserHistory();
        populateTeamSelect();
        populateAdviserSelect();

        // Event listeners
        $('#assignAdviserBtn').on('click', function() {
            $('#assignForm')[0].reset();
            assignModalInstance.show();
        });

        $('#confirmAssignBtn').on('click', function() {
            assignAdviser();
        });

        $('#refreshBtn').on('click', function() {
            loadTeamAssignments();
        });

        $('#teamSearchInput').on('keyup', function() {
            filterTeams();
        });
    });

    // ========== SECTION TEACHERS FUNCTIONS ==========
    function loadSectionTeachers() {
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_section_professors' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    displaySectionTeachers(response.data);
                } else {
                    $('#sectionTeachersBody').html('<tr><td colspan="4" class="text-center text-muted py-5">No section teachers assigned</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Load error:', error);
                $('#sectionTeachersBody').html('<tr><td colspan="4" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle me-2"></i>Failed to load section teachers</td></tr>');
            }
        });
    }

    function displaySectionTeachers(teachers) {
        const tbody = $('#sectionTeachersBody');
        
        if (!teachers || teachers.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted py-5">No section teachers assigned</td></tr>');
            return;
        }

        let html = '';
        teachers.forEach(teacher => {
            const profName = teacher.first_name ? `${teacher.first_name} ${teacher.last_name}` : 'Unknown';
            const sectionName = teacher.section || 'N/A';
            
            html += `<tr>
                <td class="d-none d-md-table-cell">${sectionName}</td>
                <td class="d-none d-md-table-cell">${profName}</td>
                <td class="d-none d-md-table-cell">${teacher.email || 'N/A'}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger" onclick="removeSectionTeacher(${teacher.id})" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });

        tbody.html(html);
    }

    function populateSectionSelect() {
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_sections' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const select = $('#sectionSelect');
                    select.find('option:not(:first)').remove();
                    
                    response.data.forEach(section => {
                        select.append(`<option value="${section}">${section}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Section load error:', error);
            }
        });
    }

    function populateSectionProfSelect() {
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_professors' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const select = $('#sectionProfSelect');
                    select.find('option:not(:first)').remove();
                    
                    response.data.forEach(prof => {
                        select.append(`<option value="${prof.id}">${prof.first_name} ${prof.last_name}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Professors load error:', error);
            }
        });
    }

    function assignSectionTeacher() {
        const sectionName = $('#sectionSelect').val();
        const professorId = $('#sectionProfSelect').val();

        if (!sectionName || !professorId) {
            Swal.fire('Error', 'Please select both a section and a research professor', 'error');
            return;
        }

        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'assign_professor_to_section',
                section: sectionName,
                professor_id: professorId
            }),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', 'Research professor assigned to section', 'success');
                    $('#sectionSelect').val('');
                    $('#sectionProfSelect').val('');
                    loadSectionTeachers();
                } else {
                    Swal.fire('Error', response.message || 'Failed to assign professor', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Assignment error:', error);
                Swal.fire('Error', 'Failed to assign professor', 'error');
            }
        });
    }

    function removeSectionTeacher(assignmentId) {
        if (confirm('Remove this section teacher assignment?')) {
            $.ajax({
                url: '/api/professor_assignments.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'delete_section_assignment',
                    section_id: assignmentId
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', 'Section teacher assignment removed', 'success');
                        loadSectionTeachers();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to remove assignment', 'error');
                    }
                }
            });
        }
    }

    // ========== TEAM ASSIGNMENTS FUNCTIONS ==========
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_team_professors' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.professors) {
                    displayAssignments(response.professors);
                } else {
                    $('#assignmentsBody').html('<tr><td colspan="6" class="text-center text-warning py-5">No assignments found</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Load error:', error);
                $('#assignmentsBody').html('<tr><td colspan="6" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle me-2"></i>Failed to load assignments</td></tr>');
            }
        });
    }

    function displayAssignments(assignments) {
        const tbody = $('#assignmentsBody');
        
        if (!assignments || assignments.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted py-5">No team assignments found</td></tr>');
            return;
        }

        let html = '';
        assignments.forEach(assignment => {
            const adviser = assignment.first_name ? `${assignment.first_name} ${assignment.last_name}` : 'Unassigned';
            const defenseType = assignment.defense_type || 'N/A';
            
            html += `<tr>
                <td class="d-none d-md-table-cell">${assignment.team_name || 'N/A'}</td>
                <td class="d-table-cell d-md-none">${assignment.team_name || 'N/A'}</td>
                <td class="d-none d-lg-table-cell">${adviser}</td>
                <td class="d-none d-sm-table-cell">${assignment.program || 'N/A'}</td>
                <td class="d-none d-md-table-cell"><span class="badge bg-info">${defenseType}</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger" onclick="removeAdviser(${assignment.team_id})" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });

        tbody.html(html);
    }

    function loadAdviserHistory() {
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_history' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    displayHistory(response.data);
                } else {
                    $('#historyBody').html('<tr><td colspan="5" class="text-center text-muted py-5">No history available</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('History load error:', error);
                $('#historyBody').html('<tr><td colspan="5" class="text-center text-muted py-5">Unable to load history</td></tr>');
            }
        });
    }

    function displayHistory(history) {
        const tbody = $('#historyBody');
        
        if (!history || history.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted py-5">No history found</td></tr>');
            return;
        }

        let html = '';
        history.forEach(record => {
            const date = new Date(record.created_at).toLocaleDateString();
            const adviser = record.professor_name || 'Unknown';
            
            html += `<tr>
                <td class="d-none d-md-table-cell">${record.team_name || 'N/A'}</td>
                <td class="d-table-cell d-md-none">${record.team_name || 'N/A'}</td>
                <td class="d-none d-lg-table-cell">${adviser}</td>
                <td class="d-none d-sm-table-cell">${record.new_status}</td>
                <td class="d-none d-md-table-cell">${date}</td>
            </tr>`;
        });

        tbody.html(html);
    }

    function populateTeamSelect() {
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_teams' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const select = $('#teamSelect');
                    select.find('option:not(:first)').remove();
                    
                    response.data.forEach(team => {
                        select.append(`<option value="${team.id}">${team.name}</option>`);
                    });
                }
            }
        });
    }

    function populateAdviserSelect() {
        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'GET',
            data: { action: 'list_professors' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const select = $('#adviserSelect');
                    select.find('option:not(:first)').remove();
                    
                    response.data.forEach(prof => {
                        select.append(`<option value="${prof.id}">${prof.first_name} ${prof.last_name}</option>`);
                    });
                }
            }
        });
    }

    function assignAdviser() {
        const teamId = $('#teamSelect').val();
        const adviserId = $('#adviserSelect').val();
        const defenseType = $('#defenseType').val();

        if (!teamId || !adviserId || !defenseType) {
            showAlert('Error', 'Please fill in all required fields', 'error');
            return;
        }

        $.ajax({
            url: '/api/professor_assignments.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'assign_professor_to_team',
                team_id: teamId,
                professor_id: adviserId,
                defense_type: defenseType
            }),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('Success', 'Adviser assigned successfully', 'success');
                    assignModalInstance.hide();
                    loadTeamAssignments();
                    loadAdviserHistory();
                } else {
                    showAlert('Error', response.message || 'Failed to assign adviser', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Assignment error:', error);
                showAlert('Error', 'Failed to assign adviser', 'error');
            }
        });
    }

    function removeAdviser(teamId) {
        if (confirm('Remove this adviser assignment?')) {
            $.ajax({
                url: '/api/professor_assignments.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'delete_assignment',
                    team_id: teamId
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('Success', 'Assignment removed', 'success');
                        loadTeamAssignments();
                        loadAdviserHistory();
                    } else {
                        showAlert('Error', response.message || 'Failed to remove assignment', 'error');
                    }
                }
            });
        }
    }

    function filterTeams() {
        const searchTerm = $('#teamSearchInput').val().toLowerCase();
        $('#assignmentsTable tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(searchTerm) > -1);
        });
    }

    function showAlert(title, message, type) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: message,
                icon: type,
                timer: 3000
            });
        } else {
            alert(`${title}: ${message}`);
        }
    }
</script>

<style>
    #assignmentTabs .nav-link {
        color: #6c757d;
        border-bottom: 2px solid transparent;
    }

    #assignmentTabs .nav-link.active {
        color: var(--main-primary, #007bff);
        border-bottom-color: var(--main-primary, #007bff);
        background-color: transparent;
    }

    #assignmentTabs .nav-link:hover {
        border-bottom-color: var(--main-primary, #007bff);
    }
</style>
