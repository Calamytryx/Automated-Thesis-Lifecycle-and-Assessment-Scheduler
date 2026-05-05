<!-- Specialization Assignment Tab (For Research Professors) -->
<style>
.assignment-card {
    transition: all 0.3s ease;
}
.assignment-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.spec-tag {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    margin: 0.25rem;
    border-radius: 15px;
    background-color: #e7f3ff;
    color: #0066cc;
    font-size: 0.875rem;
}
#userSpecializationSelect {
    height: auto !important;
    min-height: 150px;
}
#userSpecializationSelect option {
    padding: 8px;
    margin: 2px 0;
}
#userSpecializationSelect option:checked {
    background: linear-gradient(#0066cc, #0066cc);
    color: white;
}
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <?php if ($_SESSION['usertype'] === 0): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Admin Access:</strong> You can assign specializations to groups and users (faculty and admins only).
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Research Professor Access:</strong> You can assign specializations to your groups.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs for Team and User Assignment -->
    <ul class="nav nav-tabs mb-4" id="assignmentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="team-assignment-tab" data-bs-toggle="tab" data-bs-target="#teamAssignment" type="button" role="tab">
                <i class="bi bi-people-fill me-2"></i>Assign to Groups
            </button>
        </li>
        <?php if ($_SESSION['usertype'] === 0): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="user-assignment-tab" data-bs-toggle="tab" data-bs-target="#userAssignment" type="button" role="tab">
                <i class="bi bi-person-fill me-2"></i>Assign to Users
            </button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="assignmentTabContent">
        <!-- Team Assignment Tab -->
        <div class="tab-pane fade show active" id="teamAssignment" role="tabpanel">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>My Groups
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="teamsList"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-mortarboard me-2"></i>Group Specializations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="selectedTeamInfo" class="mb-3 text-muted">
                                <i class="bi bi-arrow-left"></i> Select a group to view and manage specializations
                            </div>
                            <div id="teamSpecializations"></div>
                            <div id="addTeamSpecSection" style="display: none;">
                                <hr>
                                <h6>Assign New Specialization</h6>
                                <div class="mb-3">
                                    <select class="form-select" id="teamSpecializationSelect">
                                        <option value="">Select Specialization</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" id="teamSpecNotes" placeholder="Notes (optional)" rows="2"></textarea>
                                </div>
                                <button class="btn btn-success btn-sm" id="assignToTeamBtn">
                                    <i class="bi bi-plus-circle me-1"></i>Assign Specialization
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Assignment Tab -->
        <div class="tab-pane fade" id="userAssignment" role="tabpanel">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>Assignable Users
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                            </div>
                            <div id="usersList"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-mortarboard me-2"></i>User Specializations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="selectedUserInfo" class="mb-3 text-muted">
                                <i class="bi bi-arrow-left"></i> Select a user to view and manage specializations
                            </div>
                            <div id="userSpecializations"></div>
                            <div id="addUserSpecSection" style="display: none;">
                                <hr>
                                <h6>Assign New Specializations</h6>
                                <div class="mb-3">
                                    <select class="form-select" id="userSpecializationSelect" multiple size="6">
                                        <option value="" disabled>Select Specializations (Hold Ctrl/Cmd to select multiple)</option>
                                    </select>
                                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple specializations</small>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" id="userSpecNotes" placeholder="Notes (optional)" rows="2"></textarea>
                                </div>
                                <button class="btn btn-success btn-sm" id="assignToUserBtn">
                                    <i class="bi bi-plus-circle me-1"></i>Assign Specializations
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let myTeams = [];
    let assignableUsers = [];
    let activeSpecializations = [];
    let selectedTeamId = null;
    let selectedUserId = null;

    // Load all data
    function loadAllData() {
        loadMyTeams();
        loadAssignableUsers();
        loadActiveSpecializations();
    }

    // Load my teams
    function loadMyTeams() {
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_my_teams',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    myTeams = response.data;
                    renderTeams();
                } else {
                    $('#teamsList').html('<p class="text-muted">No groups assigned</p>');
                }
            },
            error: function() {
                $('#teamsList').html('<p class="text-danger">Error loading groups</p>');
            }
        });
    }

    // Render teams
    function renderTeams() {
        const container = $('#teamsList');
        container.empty();

        if (myTeams.length === 0) {
            container.html('<p class="text-muted">No teams assigned</p>');
            return;
        }

        myTeams.forEach(team => {
            const card = `
                <div class="card assignment-card mb-2 team-card" data-team-id="${team.id}" style="cursor: pointer;">
                    <div class="card-body">
                        <h6 class="mb-1">${escapeHtml(team.name)}</h6>
                        <small class="text-muted">
                            <i class="bi bi-book me-1"></i>${escapeHtml(team.program || 'No program')}
                        </small><br>
                        <small class="text-muted">
                            <i class="bi bi-people me-1"></i>${escapeHtml(team.members || 'No members')}
                        </small>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    // Load assignable users
    function loadAssignableUsers() {
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_assignable_users',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    assignableUsers = response.data;
                    renderUsers();
                } else {
                    $('#usersList').html('<p class="text-muted">No assignable users</p>');
                }
            },
            error: function() {
                $('#usersList').html('<p class="text-danger">Error loading users</p>');
            }
        });
    }

    // Render users
    function renderUsers(filter = '') {
        const container = $('#usersList');
        container.empty();

        let filtered = assignableUsers;
        if (filter) {
            const lower = filter.toLowerCase();
            filtered = assignableUsers.filter(u => 
                u.name.toLowerCase().includes(lower) || 
                u.email.toLowerCase().includes(lower)
            );
        }

        if (filtered.length === 0) {
            container.html('<p class="text-muted">No records found.</p>');
            return;
        }

        filtered.forEach(user => {
            const card = `
                <div class="card assignment-card mb-2 user-card" data-user-id="${user.id}" style="cursor: pointer;">
                    <div class="card-body">
                        <h6 class="mb-1">${escapeHtml(user.name)}</h6>
                        <small class="text-muted">
                            <i class="bi bi-envelope me-1"></i>${escapeHtml(user.email)}
                        </small><br>
                        <small class="text-muted">
                            <i class="bi bi-person-badge me-1"></i>${escapeHtml(user.role)}
                        </small>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    // Load active specializations
    function loadActiveSpecializations() {
        $.ajax({
            url: 'includes/specialization_pool_api.php?action=get_active',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    activeSpecializations = response.data;
                    populateSpecializationSelects();
                }
            }
        });
    }

    // Populate specialization dropdowns
    function populateSpecializationSelects() {
        const teamSelect = $('#teamSpecializationSelect');
        const userSelect = $('#userSpecializationSelect');
        
        teamSelect.empty().append('<option value="">Select Specialization</option>');
        userSelect.empty().append('<option value="">Select Specialization</option>');
        
        activeSpecializations.forEach(spec => {
            const option = `<option value="${spec.id}">${escapeHtml(spec.name)} ${spec.college ? `(${escapeHtml(spec.college)})` : ''}</option>`;
            teamSelect.append(option);
            userSelect.append(option);
        });
    }

    // Team click handler
    $(document).on('click', '.team-card', function() {
        $('.team-card').removeClass('border-primary');
        $(this).addClass('border-primary');
        
        selectedTeamId = $(this).data('team-id');
        const team = myTeams.find(t => t.id == selectedTeamId);
        
        if (team) {
            $('#selectedTeamInfo').html(`
                <strong>${escapeHtml(team.name)}</strong><br>
                <small class="text-muted">${escapeHtml(team.program || 'No program')}</small>
            `);
            $('#addTeamSpecSection').show();
            loadTeamSpecializations(selectedTeamId);
        }
    });

    // User click handler
    $(document).on('click', '.user-card', function() {
        $('.user-card').removeClass('border-primary');
        $(this).addClass('border-primary');
        
        selectedUserId = $(this).data('user-id');
        const user = assignableUsers.find(u => u.id == selectedUserId);
        
        if (user) {
            $('#selectedUserInfo').html(`
                <strong>${escapeHtml(user.name)}</strong><br>
                <small class="text-muted">${escapeHtml(user.email)}</small>
            `);
            $('#addUserSpecSection').show();
            loadUserSpecializations(selectedUserId);
        }
    });

    // Load team specializations
    function loadTeamSpecializations(teamId) {
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_team_specializations&team_id=' + teamId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                renderTeamSpecializations(response.data || []);
            }
        });
    }

    // Render team specializations
    function renderTeamSpecializations(specs) {
        const container = $('#teamSpecializations');
        container.empty();

        if (specs.length === 0) {
            container.html('<p class="text-muted">No specializations assigned</p>');
            return;
        }

        specs.forEach(spec => {
            const item = `
                <div class="d-flex justify-content-between align-items-start mb-2 p-2 border rounded">
                    <div>
                        <span class="spec-tag">${escapeHtml(spec.specialization_name)}</span>
                        ${spec.notes ? `<br><small class="text-muted ms-2">${escapeHtml(spec.notes)}</small>` : ''}
                        <br><small class="text-muted ms-2">Assigned by: ${escapeHtml(spec.assigned_by_name || 'Unknown')}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-team-spec-btn" data-spec-name="${escapeHtml(spec.specialization_name)}">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            container.append(item);
        });
    }

    // Load user specializations
    function loadUserSpecializations(userId) {
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_user_specializations&user_id=' + userId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                renderUserSpecializations(response.data || []);
            }
        });
    }

    // Render user specializations
    function renderUserSpecializations(specs) {
        const container = $('#userSpecializations');
        container.empty();

        if (specs.length === 0) {
            container.html('<p class="text-muted">No specializations assigned</p>');
            return;
        }

        specs.forEach(spec => {
            const item = `
                <div class="d-flex justify-content-between align-items-start mb-2 p-2 border rounded">
                    <div>
                        <span class="spec-tag">${escapeHtml(spec.specialization_name)}</span>
                        ${spec.notes ? `<br><small class="text-muted ms-2">${escapeHtml(spec.notes)}</small>` : ''}
                        <br><small class="text-muted ms-2">Assigned by: ${escapeHtml(spec.assigned_by_name || 'Unknown')}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-user-spec-btn" data-spec-name="${escapeHtml(spec.specialization_name)}">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            container.append(item);
        });
    }

    // Assign to team
    $('#assignToTeamBtn').click(function() {
        const specializationId = $('#teamSpecializationSelect').val();
        const notes = $('#teamSpecNotes').val();

        if (!specializationId || !selectedTeamId) {
            alert('Please select a specialization');
            return;
        }

        $.ajax({
            url: 'includes/specialization_assignment_api.php',
            method: 'POST',
            data: {
                action: 'assign_to_team',
                team_id: selectedTeamId,
                specialization_id: specializationId,
                notes: notes
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#teamSpecializationSelect').val('');
                    $('#teamSpecNotes').val('');
                    loadTeamSpecializations(selectedTeamId);
                } else {
                    showAlert('danger', response.message);
                }
            }
        });
    });

    // Assign to user
    $('#assignToUserBtn').click(function() {
        const specializationIds = $('#userSpecializationSelect').val();
        const notes = $('#userSpecNotes').val();

        if (!specializationIds || specializationIds.length === 0 || !selectedUserId) {
            showAlert('danger', 'Please select at least one specialization');
            return;
        }

        // Assign each specialization
        let assignedCount = 0;
        let failedCount = 0;
        const totalCount = specializationIds.length;

        specializationIds.forEach((specId, index) => {
            $.ajax({
                url: 'includes/specialization_assignment_api.php',
                method: 'POST',
                data: {
                    action: 'assign_to_user',
                    user_id: selectedUserId,
                    specialization_id: specId,
                    notes: notes
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        assignedCount++;
                    } else {
                        failedCount++;
                    }
                    
                    // Check if this is the last request
                    if (assignedCount + failedCount === totalCount) {
                        if (assignedCount > 0) {
                            showAlert('success', `${assignedCount} specialization(s) assigned successfully`);
                        }
                        if (failedCount > 0) {
                            showAlert('warning', `${failedCount} specialization(s) could not be assigned (possibly already assigned)`);
                        }
                        $('#userSpecializationSelect').val([]);
                        $('#userSpecNotes').val('');
                        loadUserSpecializations(selectedUserId);
                    }
                },
                error: function() {
                    failedCount++;
                    if (assignedCount + failedCount === totalCount) {
                        showAlert('danger', 'Some assignments failed');
                        loadUserSpecializations(selectedUserId);
                    }
                }
            });
        });
    });

    // Remove from team
    $(document).on('click', '.remove-team-spec-btn', function() {
        const specName = $(this).data('spec-name');
        
        if (confirm('Remove this specialization from the team?')) {
            $.ajax({
                url: 'includes/specialization_assignment_api.php',
                method: 'POST',
                data: {
                    action: 'remove_from_team',
                    team_id: selectedTeamId,
                    specialization_name: specName
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadTeamSpecializations(selectedTeamId);
                    } else {
                        showAlert('danger', response.message);
                    }
                }
            });
        }
    });

    // Remove from user
    $(document).on('click', '.remove-user-spec-btn', function() {
        const specName = $(this).data('spec-name');
        
        if (confirm('Remove this specialization from the user?')) {
            $.ajax({
                url: 'includes/specialization_assignment_api.php',
                method: 'POST',
                data: {
                    action: 'remove_from_user',
                    user_id: selectedUserId,
                    specialization_name: specName
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadUserSpecializations(selectedUserId);
                    } else {
                        showAlert('danger', response.message);
                    }
                }
            });
        }
    });

    // User search
    $('#userSearch').on('input', function() {
        renderUsers($(this).val());
    });

    // Helper functions
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function showAlert(type, message) {
        if (typeof showToast === 'function') {
            const normalizedType = type === 'danger' ? 'error' : (type === 'warning' ? 'notice' : type);
            const title = normalizedType === 'success' ? 'Success' : normalizedType === 'notice' ? 'Notice' : normalizedType === 'info' ? 'Info' : 'Error';
            showToast(title, message, normalizedType);
            return;
        }

        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Initial load
    loadAllData();
});
</script>
