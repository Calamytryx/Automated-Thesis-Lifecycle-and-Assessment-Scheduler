<!-- Unified Specialization Management Tab (Pool + Assignment) -->
<div class="tab-pane fade show active" id="specialization-management" role="tabpanel" aria-labelledby="specialization-management-tab">
<style>
.specialization-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    margin: 0.25rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}
.specialization-badge.active {
    background-color: #d4edda;
    color: #155724;
}
.specialization-badge.inactive {
    background-color: #f8d7da;
    color: #721c24;
}
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

/* Meatball menu for specialization table */
#specialization-management .meatball-btn {
    background: none;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    color: #6c757d;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#specialization-management .meatball-btn:hover {
    background-color: #f8f9fa;
    color: #495057;
}

#specialization-management .meatball-dropdown-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 8px 16px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    font-size: 14px;
    color: #495057;
    transition: all 0.2s ease;
    gap: 8px;
}

#specialization-management .meatball-dropdown-item:hover {
    background-color: #f8f9fa;
    color: #212529;
}

#specialization-management .meatball-dropdown-item.edit-item {
    color: #0d6efd;
}

#specialization-management .meatball-dropdown-item.edit-item i {
    color: #0d6efd;
}

#specialization-management .meatball-dropdown-item.delete-item {
    color: #dc3545;
}

#specialization-management .meatball-dropdown-item.delete-item i {
    color: #dc3545;
}

#specialization-management .meatball-dropdown-item.edit-item:hover {
    background-color: #e7f3ff;
}

#specialization-management .meatball-dropdown-item.edit-item:hover i {
    color: #0d6efd;
}

#specialization-management .meatball-dropdown-item.delete-item:hover {
    background-color: #f8d7da;
}

#specialization-management .meatball-dropdown-item.delete-item:hover i {
    color: #dc3545;
}

#specialization-management .meatball-dropdown-item i {
    width: 16px;
    text-align: center;
}

/* Specialization tab navigation buttons */
.spec-tab-btn {
    background-color: var(--main-white);
    color: var(--main-black);
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
    border: 1px solid var(--main-primary);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.spec-tab-btn.active {
    background-color: var(--white-600);
    color: var(--main-black);
    font-weight: 700;
    border: 1px solid var(--main-primary);
}

.spec-tab-btn.active i{
    color: var(--main-primary);
}

.spec-tab-btn:hover {
    background-color: var(--white-600);
    color: var(--main-black);
    border: 1px solid var(--main-primary);
}

/* Specialization tab navigation container */
.spec-nav-container {
    display: flex;
    width: 100%;
}

.spec-tab-group {
    display: flex;
    gap: 0;
    width: 100%;
}

@media (min-width: 769px) {
    .spec-tab-btn {
        height: 48px;
        flex: 1;
    }

    .spec-tab-btn:first-child {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    .spec-tab-btn:not(:first-child):not(:last-child) {
        border-radius: 0;
    }
    
    .spec-tab-btn:last-child {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    .spec-tab-btn:not(:first-child) {
        margin-left: -1px;
    }
}

@media (max-width: 768px) {
    .spec-nav-container {
        justify-content: stretch;
    }
    
    .spec-tab-group {
        flex-direction: column;
        width: 100%;
    }
    
    .spec-tab-btn {
        width: 100%;
        border-radius: 4px !important;
        margin-bottom: 0.5rem;
        text-align: center;
    }
    
    .spec-tab-btn:last-child {
        margin-bottom: 0;
    }
}

/* Specialization tab panes */
.spec-tab-pane {
    display: none;
}
.spec-tab-pane.active {
    display: block;
}
</style>

<div class="container-fluid py-4 content-container">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-2">Specialization Management</h3>
            <p class="text-muted">Manage specialization pool and its assignment to Teams and Users</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="spec-nav-container mb-4">
        <div class="spec-tab-group" role="group" aria-label="Specialization tabs">
            <?php if ($_SESSION['usertype'] == 0): ?>
            <button type="button" class="btn spec-tab-btn active" data-spec-tab="poolManagement">
                <i class="bi bi-collection-fill me-2"></i>Specialization Pool
            </button>
            <?php endif; ?>
            <button type="button" class="btn spec-tab-btn <?php echo ($_SESSION['usertype'] == 2) ? 'active' : ''; ?>" data-spec-tab="teamAssignment">
                <i class="bi bi-people-fill me-2"></i>Assign to Teams
            </button>
            <?php if ($_SESSION['usertype'] == 0): ?>
            <button type="button" class="btn spec-tab-btn" data-spec-tab="userAssignment">
                <i class="bi bi-person-fill me-2"></i>Assign to Users
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div id="specializationManagementContent">
        <!-- Pool Management Tab (Admin Only) -->
        <?php if ($_SESSION['usertype'] == 0): ?>
        <div class="spec-tab-pane active" id="poolManagement">
            <div class="row">
                <div class="col-12">
                    <!-- Controls Container -->
                    <div class="user-controls-container p-0 mt-3">
                        <!-- Filter and Add Button Row -->
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-12 col-md-3 col-lg-3">
                                <!-- College Filter -->
                                <select class="form-select user-control-height" id="filterCollege">
                                    <option value="">All Colleges</option>
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-3 col-lg-3">
                                <!-- Department Filter -->
                                <select class="form-select user-control-height" id="filterDepartment">
                                    <option value="">All Departments</option>
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-3 col-lg-3">
                                <!-- Status Filter -->
                                <select class="form-select user-control-height" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-3 col-lg-3">
                                <!-- Add Button -->
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn feature-btn add-btn user-control-height w-100 w-md-auto" id="addSpecializationBtn">
                                        <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                        <span class="d-none d-sm-inline">Add Specialization</span>
                                        <span class="d-sm-none">Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specializations Table -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-hover db-table" id="specializationsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Department</th>
                                    <th>College</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="specializationsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Team Assignment Tab -->
        <div class="spec-tab-pane <?php echo ($_SESSION['usertype'] == 2) ? 'active' : ''; ?>" id="teamAssignment">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>My Teams
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
                                <i class="bi bi-mortarboard me-2"></i>Team Specializations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="selectedTeamInfo" class="mb-3 text-muted">
                                <i class="bi bi-arrow-left"></i> Select a team to view and manage specializations
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

        <!-- User Assignment Tab (Admin Only) -->
        <?php if ($_SESSION['usertype'] == 0): ?>
        <div class="spec-tab-pane" id="userAssignment">
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
        <?php endif; ?>
    </div>

    <!-- Add/Edit Specialization Modal -->
<div class="modal fade" id="specializationModal" tabindex="-1" aria-labelledby="specializationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="specializationModalLabel">Add Specialization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="specializationForm">
                    <input type="hidden" id="specializationId" name="id">
                    
                    <div class="mb-3">
                        <label for="specializationName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="specializationName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="specializationDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="specializationDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="specializationCollege" class="form-label">College</label>
                        <select class="form-select" id="specializationCollege" name="college">
                            <option value="">Select College</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="specializationDepartment" class="form-label">Department</label>
                        <input type="text" class="form-control" id="specializationDepartment" name="department">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="specializationActive" name="is_active" checked>
                            <label class="form-check-label" for="specializationActive">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSpecializationBtn">Save</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
$(document).ready(function() {
    // ==================== TAB SWITCHING ====================
    const specTabButtons = document.querySelectorAll('.spec-tab-btn');
    const specTabPanes = document.querySelectorAll('.spec-tab-pane');

    specTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-spec-tab');
            
            // Remove active class from all buttons
            specTabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Hide all tab panes
            specTabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Show target tab pane
            const targetPane = document.getElementById(targetTab);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });

    // ==================== POOL MANAGEMENT ====================
    let specializationsData = [];
    let modal = null;

    // Initialize modal
    const modalElement = document.getElementById('specializationModal');
    if (modalElement) {
        modal = new bootstrap.Modal(modalElement);
    }

    // Load specializations
    function loadSpecializations() {
        $.ajax({
            url: 'includes/specialization_pool_api.php?action=get_all',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    specializationsData = response.data;
                    renderSpecializations();
                    populateFilters();
                } else {
                    showAlert('danger', 'Failed to load specializations: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Error loading specializations: ' + error);
            }
        });
    }

    // Render specializations table
    function renderSpecializations() {
        const tbody = $('#specializationsTableBody');
        tbody.empty();

        // Clear any existing dropdowns - use native JS for better reliability
        const existingDropdowns = document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-spec-"]');
        existingDropdowns.forEach(dropdown => {
            if (dropdown && dropdown.parentNode) {
                dropdown.parentNode.removeChild(dropdown);
            }
        });

        if (specializationsData.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="text-center text-muted">No specializations found</td>
                </tr>
            `);
            return;
        }

        // Apply filters
        let filtered = specializationsData;
        const collegeFilter = $('#filterCollege').val();
        const deptFilter = $('#filterDepartment').val();
        const statusFilter = $('#filterStatus').val();

        if (collegeFilter) {
            filtered = filtered.filter(s => s.college === collegeFilter);
        }
        if (deptFilter) {
            filtered = filtered.filter(s => s.department === deptFilter);
        }
        if (statusFilter !== '') {
            filtered = filtered.filter(s => s.is_active == statusFilter);
        }

        filtered.forEach(spec => {
            const statusBadge = spec.is_active == 1 
                ? '<span class="specialization-badge active">Active</span>'
                : '<span class="specialization-badge inactive">Inactive</span>';

            const row = `
                <tr>
                    <td><strong>${escapeHtml(spec.name)}</strong></td>
                    <td>${escapeHtml(spec.description || '-')}</td>
                    <td>${escapeHtml(spec.department || '-')}</td>
                    <td>${escapeHtml(spec.college || '-')}</td>
                    <td>${statusBadge}</td>
                    <td>${escapeHtml(spec.created_by_name || '-')}</td>
                    <td class="action-buttons text-center">
                        <button class="meatball-btn" data-spec-id="${spec.id}" aria-label="Actions">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
            
            // Create dropdown portal outside table
            const dropdownPortal = document.createElement('div');
            dropdownPortal.className = 'meatball-dropdown-portal';
            dropdownPortal.id = `dropdown-spec-${spec.id}`;
            dropdownPortal.setAttribute('data-spec-id', spec.id);
            dropdownPortal.style.cssText = `
                position: fixed;
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                min-width: 120px;
                padding: 4px 0;
                display: none;
            `;
            dropdownPortal.innerHTML = `
                <button class="meatball-dropdown-item edit-item edit-spec-btn" data-id="${spec.id}">
                    <i class="fas fa-edit"></i>
                    Edit
                </button>
                <button class="meatball-dropdown-item delete-item delete-spec-btn" data-id="${spec.id}">
                    <i class="fas fa-trash-alt"></i>
                    Delete
                </button>
            `;
            
            // Ensure it's appended to body
            try {
                document.body.appendChild(dropdownPortal);
            } catch (error) {
                console.error('Error appending dropdown portal:', error);
            }
        });
    }

    // Populate filters
    function populateFilters() {
        const departments = [...new Set(specializationsData.map(s => s.department).filter(d => d))];

        // Load colleges from programs table via API
        $.ajax({
            url: 'includes/specialization_pool_api.php?action=get_colleges',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const colleges = response.data;
                    
                    $('#filterCollege').empty().append('<option value="">All Colleges</option>');
                    colleges.forEach(college => {
                        $('#filterCollege').append(`<option value="${escapeHtml(college)}">${escapeHtml(college)}</option>`);
                    });

                    // Also populate modal college dropdown
                    $('#specializationCollege').empty().append('<option value="">Select College</option>');
                    colleges.forEach(college => {
                        $('#specializationCollege').append(`<option value="${escapeHtml(college)}">${escapeHtml(college)}</option>`);
                    });
                }
            }
        });

        $('#filterDepartment').empty().append('<option value="">All Departments</option>');
        departments.forEach(dept => {
            $('#filterDepartment').append(`<option value="${escapeHtml(dept)}">${escapeHtml(dept)}</option>`);
        });
    }

    // Add specialization button
    $('#addSpecializationBtn').click(function() {
        $('#specializationModalLabel').text('Add Specialization');
        $('#specializationForm')[0].reset();
        $('#specializationId').val('');
        $('#specializationActive').prop('checked', true);
        if (modal) modal.show();
    });

    // Edit specialization button
    $(document).on('click', '.edit-spec-btn', function() {
        const id = $(this).data('id');
        const spec = specializationsData.find(s => s.id == id);
        
        if (spec) {
            $('#specializationModalLabel').text('Edit Specialization');
            $('#specializationId').val(spec.id);
            $('#specializationName').val(spec.name);
            $('#specializationDescription').val(spec.description || '');
            $('#specializationCollege').val(spec.college || '');
            $('#specializationDepartment').val(spec.department || '');
            $('#specializationActive').prop('checked', spec.is_active == 1);
            if (modal) modal.show();
        }
    });

    // Save specialization
    $('#saveSpecializationBtn').click(function() {
        const formData = $('#specializationForm').serializeArray();
        const data = {};
        formData.forEach(item => {
            data[item.name] = item.value;
        });
        data.is_active = $('#specializationActive').is(':checked') ? 1 : 0;
        data.action = data.id ? 'update' : 'add';

        $.ajax({
            url: 'includes/specialization_pool_api.php',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    if (modal) modal.hide();
                    loadSpecializations();
                    loadActiveSpecializations(); // Reload for assignment dropdowns
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Error: ' + error);
            }
        });
    });

    // Meatball menu functionality
    $(document).on('click', function(e) {
        // Only handle clicks in specialization-management tab
        if (!$(e.target).closest('#specialization-management').length) {
            return;
        }

        // Handle meatball button clicks
        if ($(e.target).closest('.meatball-btn').length) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = $(e.target).closest('.meatball-btn')[0];
            const specId = $(btn).data('spec-id');
            
            if (!specId) {
                console.error('No spec-id found on button');
                return;
            }
            
            const dropdown = document.getElementById(`dropdown-spec-${specId}`);
            
            if (!dropdown) {
                console.error('Dropdown not found for spec:', specId);
                // Try to recreate dropdown if missing
                const spec = specializationsData.find(s => s.id == specId);
                if (spec) {
                    console.log('Attempting to recreate dropdown for spec:', specId);
                    createDropdownForSpec(spec);
                    // Try again
                    setTimeout(() => {
                        const retryDropdown = document.getElementById(`dropdown-spec-${specId}`);
                        if (retryDropdown) {
                            positionAndShowDropdown(retryDropdown, btn);
                        }
                    }, 10);
                }
                return;
            }
            
            const isCurrentlyOpen = dropdown.style.display === 'block';
            
            // Close all other dropdowns first
            document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => {
                dd.style.display = 'none';
            });
            
            // Toggle current dropdown
            if (!isCurrentlyOpen) {
                positionAndShowDropdown(dropdown, btn);
            }
        }
        // Close dropdowns when clicking outside
        else if (!$(e.target).closest('.meatball-dropdown-portal').length && !$(e.target).closest('.meatball-btn').length) {
            document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => {
                dd.style.display = 'none';
            });
        }
    });

    // Helper function to position and show dropdown
    function positionAndShowDropdown(dropdown, btn) {
        const btnRect = btn.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const dropdownWidth = 120;
        
        // Calculate position
        let left = btnRect.right - dropdownWidth;
        let top = btnRect.bottom + 5;
        
        // Adjust for mobile screens
        if (viewportWidth < 768) {
            left = btnRect.left + (btnRect.width / 2) - (dropdownWidth / 2);
        }
        
        // Ensure dropdown doesn't go off-screen
        if (left < 10) left = 10;
        if (left + dropdownWidth > viewportWidth - 10) {
            left = viewportWidth - dropdownWidth - 10;
        }
        
        dropdown.style.position = 'fixed';
        dropdown.style.top = `${top}px`;
        dropdown.style.left = `${left}px`;
        dropdown.style.display = 'block';
    }

    // Helper function to create dropdown for a spec
    function createDropdownForSpec(spec) {
        // Remove existing if any
        const existing = document.getElementById(`dropdown-spec-${spec.id}`);
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }
        
        const dropdownPortal = document.createElement('div');
        dropdownPortal.className = 'meatball-dropdown-portal';
        dropdownPortal.id = `dropdown-spec-${spec.id}`;
        dropdownPortal.setAttribute('data-spec-id', spec.id);
        dropdownPortal.style.cssText = `
            position: fixed;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            min-width: 120px;
            padding: 4px 0;
            display: none;
        `;
        dropdownPortal.innerHTML = `
            <button class="meatball-dropdown-item edit-item edit-spec-btn" data-id="${spec.id}">
                <i class="fas fa-edit"></i>
                Edit
            </button>
            <button class="meatball-dropdown-item delete-item delete-spec-btn" data-id="${spec.id}">
                <i class="fas fa-trash-alt"></i>
                Delete
            </button>
        `;
        document.body.appendChild(dropdownPortal);
    }

    // Handle meatball dropdown item clicks
    $(document).on('click', '.meatball-dropdown-item', function() {
        const dropdown = $(this).closest('.meatball-dropdown-portal')[0];
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    });

    // Delete specialization
    $(document).on('click', '.delete-spec-btn', function() {
        const id = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this specialization? This action cannot be undone.')) {
            $.ajax({
                url: 'includes/specialization_pool_api.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadSpecializations();
                        loadActiveSpecializations(); // Reload for assignment dropdowns
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('danger', 'Error: ' + error);
                }
            });
        }
    });

    // Filter changes
    $('#filterCollege, #filterDepartment, #filterStatus').change(function() {
        renderSpecializations();
    });

    // ==================== ASSIGNMENT MANAGEMENT ====================
    let myTeams = [];
    let assignableUsers = [];
    let activeSpecializations = [];
    let selectedTeamId = null;
    let selectedUserId = null;

    // Load all assignment data
    function loadAllAssignmentData() {
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
                    $('#teamsList').html('<p class="text-muted">No teams assigned</p>');
                }
            },
            error: function() {
                $('#teamsList').html('<p class="text-danger">Error loading teams</p>');
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
            container.html('<p class="text-muted">No users found</p>');
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

    // ==================== SHARED UTILITIES ====================
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

    // ==================== INITIALIZATION ====================
    
    // Load data when main Specializations nav is clicked/shown
    $('a[href="#specialization-management"]').on('shown.bs.tab shown.bs.pill click', function() {
        console.log('Specializations tab shown - loading data for usertype:', <?php echo $_SESSION['usertype']; ?>);
        <?php if ($_SESSION['usertype'] == 0): ?>
        // Admin: Load specialization pool
        loadSpecializations();
        // Also force active tab
        $('.spec-tab-btn[data-spec-tab="poolManagement"]').click();
        <?php else: ?>
        // Faculty: Load teams
        loadAllAssignmentData();
        // Also force active tab
        $('.spec-tab-btn[data-spec-tab="teamAssignment"]').click();
        <?php endif; ?>
    });

    // Auto-load on page ready if this is the active tab (useful if page refreshed)
    if ($('#specialization-management-tab').hasClass('active')) {
        <?php if ($_SESSION['usertype'] == 0): ?>
        loadSpecializations();
        $('.spec-tab-btn[data-spec-tab="poolManagement"]').click();
        <?php else: ?>
        loadAllAssignmentData();
        $('.spec-tab-btn[data-spec-tab="teamAssignment"]').click();
        <?php endif; ?>
    }

    // Load pool data when pool tab button is clicked
    $('.spec-tab-btn[data-spec-tab="poolManagement"]').on('click', function() {
        if (specializationsData.length === 0) {
            loadSpecializations();
        }
    });

    // Load assignment data when assignment tab buttons are clicked
    $('.spec-tab-btn[data-spec-tab="teamAssignment"], .spec-tab-btn[data-spec-tab="userAssignment"]').on('click', function() {
        if (myTeams.length === 0 && assignableUsers.length === 0) {
            loadAllAssignmentData();
        }
    });
});
</script>
</div>