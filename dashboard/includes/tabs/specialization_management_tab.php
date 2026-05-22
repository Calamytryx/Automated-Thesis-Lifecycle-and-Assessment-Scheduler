<!-- Unified Specialization Management Tab (Pool + Assignment) -->
<div class="tab-pane fade" id="specialization-management" role="tabpanel" aria-labelledby="specialization-management-tab">

<div class="container-fluid py-4 content-container">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-2">Field of specialization</h3>
            <p class="text-muted">Manage specialization pool and assign it to teams</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="spec-nav-container mb-3">
        <div class="spec-tab-group" role="group" aria-label="Specialization tabs">
            <?php if ($_SESSION['usertype'] == 0): ?>
            <button type="button" class="btn spec-tab-btn active" data-spec-tab="poolManagement">
                <i class="bi bi-collection-fill me-2"></i>Specialization Pool
            </button>
            <?php endif; ?>
            <button type="button" class="btn spec-tab-btn <?php echo ($_SESSION['usertype'] == 2) ? 'active' : ''; ?>" data-spec-tab="teamAssignment">
                <i class="bi bi-people-fill me-2"></i>Assign to Groups
            </button>
            <?php if ($_SESSION['usertype'] == 0 || $_SESSION['usertype'] == 2): ?>
            <button type="button" class="btn spec-tab-btn" data-spec-tab="userAssignment">
                <i class="bi bi-person-badge-fill me-2"></i><?php echo ($_SESSION['usertype'] == 2) ? 'My Specializations' : 'User Specializations'; ?>
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
            <div class="row">
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
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center flex-wrap mt-2" id="poolPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Team Assignment Tab -->
        <div class="spec-tab-pane <?php echo ($_SESSION['usertype'] == 2) ? 'active' : ''; ?>" id="teamAssignment">
            <div class="row">
                <div class="col-12">
                    <!-- Team Controls -->
                    <div class="user-controls-container p-0 mt-3">
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-12 col-md-4 col-lg-4">
                                <!-- Search Input -->
                                <div class="users-search-container">
                                    <div class="input-group user-control-height m-0">
                                        <span class="input-group-text border-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control border-0" id="teamSearch" placeholder="Search teams by name...">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4 col-lg-4">
                                <!-- Program Filter -->
                                <select class="form-select user-control-height" id="teamProgramFilter">
                                    <option value="">All Programs</option>
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-4 col-lg-4">
                                <!-- Sort Dropdown -->
                                <select class="form-select user-control-height" id="teamSpecSortSelect">
                                    <option value="name:asc">Group Name (A-Z)</option>
                                    <option value="name:desc">Group Name (Z-A)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teams Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table" id="teamsSpecTable">
                            <thead>
                                <tr>
                                    <th>Group Name</th>
                                    <th class="d-none d-md-table-cell">Program</th>
                                    <th class="d-none d-lg-table-cell">Adviser</th>
                                    <th class="d-none d-lg-table-cell">Members</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teamsSpecTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center flex-wrap mt-2" id="teamsSpecPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- User Specialization Tab (self for faculty; self + same-program faculty for chairs; all for super admin) -->
        <?php if ($_SESSION['usertype'] == 0 || $_SESSION['usertype'] == 2): ?>
        <div class="spec-tab-pane" id="userAssignment">
            <div class="row">
                <div class="col-12">
                    <div class="user-controls-container p-0 mt-3">
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-12 col-md-6 col-lg-5">
                                <div class="users-search-container">
                                    <div class="input-group user-control-height m-0">
                                        <span class="input-group-text border-0"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control border-0" id="userSpecSearch" placeholder="Search users by name or email...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table" id="usersSpecTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="d-none d-md-table-cell">Program</th>
                                    <th class="d-none d-lg-table-cell">Role</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersSpecTableBody">
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center flex-wrap mt-2" id="usersSpecPagination"></ul>
                    </nav>
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
                        <label for="specializationDepartment" class="form-label">Department/Program</label>
                        <select class="form-select" id="specializationDepartment" name="department">
                            <option value="">Select Department/Program</option>
                        </select>
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

<!-- Delete Specialization Confirmation Modal -->
<div class="modal fade" id="specializationDeleteConfirmModal" tabindex="-1" aria-labelledby="specializationDeleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="text-danger mb-3" style="font-size: 3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
                    </svg>
                </div>
                <h4 class="fw-bold mb-3" id="specializationDeleteConfirmModalLabel">Confirm Deletion</h4>
                <p>Are you sure you want to delete <span id="specializationDeleteTarget" class="fw-semibold">this field of specialization</span>?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmSpecializationDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Team Specialization Assignment Modal -->
<div class="modal fade" id="teamSpecAssignmentModal" tabindex="-1" aria-labelledby="teamSpecAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teamSpecAssignmentModalLabel">
                    <i class="bi bi-mortarboard me-2"></i>Manage Team Specializations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="selectedTeamInfoModal" class="mb-3 alert alert-info">
                    <strong id="selectedTeamNameModal"></strong>
                </div>
                
                <!-- Current Specializations -->
                <h6 class="mb-3">Current Specializations</h6>
                <div id="teamSpecializationsModal" class="mb-4"></div>
                
                <!-- Assign New Specialization -->
                <hr>
                <h6 class="mb-3">Assign New Specialization</h6>
                <div class="mb-3">
                    <label for="teamSpecializationSelect" class="form-label">Select Specialization</label>
                    <select class="form-select" id="teamSpecializationSelect">
                        <option value="">Select Specialization</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="teamSpecNotes" class="form-label">Notes (optional)</label>
                    <textarea class="form-control" id="teamSpecNotes" placeholder="Add notes about this specialization assignment..." rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="assignToTeamBtn">
                    <i class="bi bi-plus-circle me-1"></i>Assign Specialization
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Specialization Assignment Modal -->
<div class="modal fade" id="userSpecAssignmentModal" tabindex="-1" aria-labelledby="userSpecAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userSpecAssignmentModalLabel">
                    <i class="bi bi-mortarboard me-2"></i>Manage User Specializations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="selectedUserInfoModal" class="mb-3 alert alert-info">
                    <strong id="selectedUserNameModal"></strong>
                </div>

                <!-- Current Specializations -->
                <h6 class="mb-3">Current Specializations</h6>
                <div id="userSpecializationsModal" class="mb-4"></div>

                <!-- Assign New Specializations -->
                <hr>
                <h6 class="mb-3">Assign New Specializations</h6>
                <div class="mb-3">
                    <label for="userSpecializationSelectModal" class="form-label">Select Specializations</label>
                    <select class="form-select" id="userSpecializationSelectModal" multiple size="6"></select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple specializations.</small>
                </div>
                <div class="mb-3">
                    <label for="userSpecNotesModal" class="form-label">Notes (optional)</label>
                    <textarea class="form-control" id="userSpecNotesModal" placeholder="Add notes about this assignment..." rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="assignToUserBtn">
                    <i class="bi bi-plus-circle me-1"></i>Assign Specializations
                </button>
            </div>
        </div>
    </div>
</div>

</div>

<script>
$(document).ready(function() {
    const specializationDeleteConfirmModal = new bootstrap.Modal(document.getElementById('specializationDeleteConfirmModal'));
    let specializationIdToDelete = null;
    let specializationNameToDelete = 'this field of specialization';
    const specEmojiRegex = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu;

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
    let currentPoolPage = 1;
    const poolItemsPerPage = 10;

    // Initialize modal with specific configuration to prevent duplicates
    const modalElement = document.getElementById('specializationModal');
    if (modalElement) {
        // Remove any existing modal instances
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }
        // Create new modal instance
        modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
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
    function renderSpecializations(page = 1) {
        currentPoolPage = page;
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
                    <td colspan="7" class="text-center text-muted">No records found.</td>
                </tr>
            `);
            $('#poolPagination').empty();
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

        // Pagination calculations
        const totalPages = Math.ceil(filtered.length / poolItemsPerPage);
        const startIndex = (page - 1) * poolItemsPerPage;
        const endIndex = startIndex + poolItemsPerPage;
        const paginatedItems = filtered.slice(startIndex, endIndex);

        paginatedItems.forEach(spec => {
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
            const deleteBtn = dropdownPortal.querySelector('.delete-spec-btn');
            if (deleteBtn) {
                deleteBtn.dataset.specName = spec.name || 'Unnamed field';
            }
            
            // Ensure it's appended to body
            try {
                document.body.appendChild(dropdownPortal);
            } catch (error) {
                console.error('Error appending dropdown portal:', error);
            }
        });

        // Render pagination
        renderPoolPagination(totalPages, page, filtered.length);
    }

    // Render pool pagination
    function renderPoolPagination(totalPages, currentPage, totalItems) {
        const pagination = $('#poolPagination');
        pagination.empty();

        if (totalPages <= 1) return;

        // Previous button
        pagination.append(`
            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
            </li>
        `);

        // Page numbers with ellipsis
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
            if (startPage > 2) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
            pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
        }

        // Next button
        pagination.append(`
            <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
            </li>
        `);
    }

    // Handle pool pagination clicks
    $(document).on('click', '#poolPagination a.page-link', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (!isNaN(page) && page !== currentPoolPage) {
            renderSpecializations(page);
        }
    });

    // Populate filters
    function populateFilters() {
        // Get unique colleges from specialization data
        const colleges = [...new Set(specializationsData.map(s => s.college).filter(c => c))].sort();
        const departments = [...new Set(specializationsData.map(s => s.department).filter(d => d))].sort();

        // Populate college filter with data from specialization_pool
        $('#filterCollege').empty().append('<option value="">All Colleges</option>');
        colleges.forEach(college => {
            $('#filterCollege').append(`<option value="${escapeHtml(college)}">${escapeHtml(college)}</option>`);
        });

        // Populate department filter
        $('#filterDepartment').empty().append('<option value="">All Departments</option>');
        departments.forEach(dept => {
            $('#filterDepartment').append(`<option value="${escapeHtml(dept)}">${escapeHtml(dept)}</option>`);
        });

        // Load programs from programs table for modal dropdowns
        $.ajax({
            url: 'includes/specialization_pool_api.php?action=get_colleges',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const modalColleges = response.data;
                    $('#specializationCollege').empty().append('<option value="">Select College</option>');
                    modalColleges.forEach(college => {
                        $('#specializationCollege').append(`<option value="${escapeHtml(college)}">${escapeHtml(college)}</option>`);
                    });
                }
            }
        });
    }

    // Add specialization button - prevent global handler from interfering
    $('#addSpecializationBtn').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $('#specializationModalLabel').text('Add Specialization');
        $('#specializationForm')[0].reset();
        $('#specializationId').val('');
        $('#specializationActive').prop('checked', true);
        // Reset department dropdown
        $('#specializationDepartment').empty().append('<option value="">Select Department/Program</option>');
        clearFormValidation('#specializationForm');
        if (modal) modal.show();
    });

    // Handle college selection in modal - populate departments
    $('#specializationCollege').change(function() {
        const selectedCollege = $(this).val();
        const deptSelect = $('#specializationDepartment');
        
        deptSelect.empty().append('<option value="">Select Department/Program</option>');
        
        if (selectedCollege) {
            // Fetch programs/departments for selected college
            $.ajax({
                url: 'includes/get_programs.php',
                method: 'GET',
                data: { college: selectedCollege },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        // Response.data is nested: {college: {department: [programs]}} or {college: [programs]}
                        const collegeData = response.data[selectedCollege];
                        
                        if (collegeData) {
                            let departments = [];
                            
                            // Check if college has department subgroups (object) or direct programs (array)
                            if (Array.isArray(collegeData)) {
                                // Direct programs list - no departments
                                // We can't show departments, so disable the dropdown
                                deptSelect.prop('disabled', true);
                            } else {
                                // Department subgroups exist
                                departments = Object.keys(collegeData).filter(d => d !== 'Uncategorized Department').sort();
                                
                                departments.forEach(dept => {
                                    deptSelect.append(`<option value="${escapeHtml(dept)}">${escapeHtml(dept)}</option>`);
                                });
                                
                                // Enable department select if we have departments
                                if (departments.length > 0) {
                                    deptSelect.prop('disabled', false);
                                } else {
                                    deptSelect.prop('disabled', true);
                                }
                            }
                        }
                    } else {
                        deptSelect.prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching programs:', error);
                    deptSelect.prop('disabled', true);
                }
            });
        } else {
            deptSelect.prop('disabled', true);
        }
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
            $('#specializationActive').prop('checked', spec.is_active == 1);
            
            // Populate department dropdown for selected college, then set value
            if (spec.college) {
                $.ajax({
                    url: 'includes/get_programs.php',
                    method: 'GET',
                    data: { college: spec.college },
                    dataType: 'json',
                    success: function(progResponse) {
                        const deptSelect = $('#specializationDepartment');
                        deptSelect.empty().append('<option value="">Select Department/Program</option>');
                        
                        if (progResponse.success && progResponse.data) {
                            const collegeData = progResponse.data[spec.college];
                            
                            if (collegeData) {
                                let departments = [];
                                
                                // Check if college has department subgroups (object) or direct programs (array)
                                if (!Array.isArray(collegeData)) {
                                    // Department subgroups exist
                                    departments = Object.keys(collegeData).filter(d => d !== 'Uncategorized Department').sort();
                                    
                                    departments.forEach(dept => {
                                        deptSelect.append(`<option value="${escapeHtml(dept)}">${escapeHtml(dept)}</option>`);
                                    });
                                    
                                    // Set the current department value
                                    deptSelect.val(spec.department || '');
                                    
                                    if (departments.length > 0) {
                                        deptSelect.prop('disabled', false);
                                    } else {
                                        deptSelect.prop('disabled', true);
                                    }
                                } else {
                                    // No departments, disable dropdown
                                    deptSelect.prop('disabled', true);
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching programs for edit:', error);
                        $('#specializationDepartment').prop('disabled', true);
                    }
                });
            } else {
                $('#specializationDepartment').empty().append('<option value="">Select Department/Program</option>').prop('disabled', true);
            }
            
            clearFormValidation('#specializationForm');
            if (modal) modal.show();
        }
    });

    // Save specialization
    $('#saveSpecializationBtn').click(function() {
        if (!validateSpecializationForm()) {
            showAlert('danger', 'Please complete all required fields correctly before saving.');
            return;
        }

        const formData = $('#specializationForm').serializeArray();
        const rawData = {};
        formData.forEach(item => {
            rawData[item.name] = item.value;
        });

        const data = {
            id: sanitizeModalText(rawData.id || ''),
            name: sanitizeModalText(rawData.name || '', 150),
            description: sanitizeModalText(rawData.description || '', 500),
            college: sanitizeModalText(rawData.college || '', 150),
            department: sanitizeModalText(rawData.department || '', 150)
        };

        if (!data.name) {
            setFieldValidationError($('#specializationName'), 'Name is required.');
            showAlert('danger', 'Please complete all required fields correctly before saving.');
            return;
        }

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
        const deleteBtn = dropdownPortal.querySelector('.delete-spec-btn');
        if (deleteBtn) {
            deleteBtn.dataset.specName = spec.name || 'Unnamed field';
        }
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
        specializationIdToDelete = $(this).data('id');
        specializationNameToDelete = $(this).data('specName') || 'this field of specialization';
        $('#specializationDeleteTarget').text(specializationNameToDelete);
        specializationDeleteConfirmModal.show();
    });

    $('#confirmSpecializationDeleteBtn').on('click', function() {
        if (!specializationIdToDelete) {
            specializationDeleteConfirmModal.hide();
            return;
        }

        const id = specializationIdToDelete;
        specializationIdToDelete = null;

        $.ajax({
            url: 'includes/specialization_pool_api.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                specializationDeleteConfirmModal.hide();
                if (response.success) {
                    showAlert('success', response.message);
                    loadSpecializations();
                    loadActiveSpecializations(); // Reload for assignment dropdowns
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                specializationDeleteConfirmModal.hide();
                showAlert('danger', 'Error: ' + error);
            }
        });
    });

    // College filter change - update department filter dynamically
    $('#filterCollege').change(function() {
        const selectedCollege = $(this).val();
        updateDepartmentFilter(selectedCollege);
        renderSpecializations(1);
    });

    // Department and status filter changes
    $('#filterDepartment, #filterStatus').change(function() {
        renderSpecializations(1);
    });

    // Update department filter based on selected college
    function updateDepartmentFilter(selectedCollege) {
        const deptFilter = $('#filterDepartment');
        const statusFilter = $('#filterStatus');
        
        if (!selectedCollege) {
            // All colleges selected - show all departments
            const allDepts = [...new Set(specializationsData.map(s => s.department).filter(d => d))].sort();
            deptFilter.empty().append('<option value="">All Departments</option>');
            allDepts.forEach(dept => {
                deptFilter.append(`<option value="${escapeHtml(dept)}">${escapeHtml(dept)}</option>`);
            });
            deptFilter.prop('disabled', false);
            statusFilter.prop('disabled', false);
        } else {
            // Filter departments by selected college
            const filteredSpecs = specializationsData.filter(s => s.college === selectedCollege);
            const collegeDepts = [...new Set(filteredSpecs.map(s => s.department).filter(d => d))].sort();
            
            deptFilter.empty().append('<option value="">All Departments</option>');
            
            if (collegeDepts.length > 0) {
                collegeDepts.forEach(dept => {
                    deptFilter.append(`<option value="${escapeHtml(dept)}">${escapeHtml(dept)}</option>`);
                });
                deptFilter.prop('disabled', false);
                statusFilter.prop('disabled', false);
            } else {
                // No departments for this college - disable filters
                deptFilter.prop('disabled', true);
                statusFilter.prop('disabled', true);
            }
        }
    }

    // ==================== ASSIGNMENT MANAGEMENT ====================
    let myTeams = [];
    let activeSpecializations = [];
    let selectedTeamId = null;
    let currentTeamsPage = 1;
    const teamsPerPage = 10;

    // Load all assignment data
    function loadAllAssignmentData() {
        loadMyTeams();
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
                    populateTeamProgramFilter();
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

    // Populate program filter with unique programs from teams
    function populateTeamProgramFilter() {
        const programs = [...new Set(myTeams.map(t => t.program).filter(p => p))];
        const filterSelect = $('#teamProgramFilter');
        
        programs.sort().forEach(program => {
            filterSelect.append(`<option value="${escapeHtml(program)}">${escapeHtml(program)}</option>`);
        });
    }

    // Render teams
    function renderTeams(page = 1) {
        currentTeamsPage = page;
        const tbody = $('#teamsSpecTableBody');
        tbody.empty();

        // Get filter and sort values
        const searchFilter = $('#teamSearch').val().toLowerCase();
        const programFilter = $('#teamProgramFilter').val();
        const sortValue = $('#teamSpecSortSelect').val();

        // Apply filters
        let filtered = myTeams;
        
        // Search filter
        if (searchFilter) {
            filtered = filtered.filter(t => 
                t.name.toLowerCase().includes(searchFilter)
            );
        }
        
        // Program filter
        if (programFilter) {
            filtered = filtered.filter(t => t.program === programFilter);
        }
        
        // Apply sorting
        const [sortField, sortOrder] = sortValue.split(':');
        filtered.sort((a, b) => {
            const aVal = a[sortField];
            const bVal = b[sortField];
            
            if (typeof aVal === 'string' && typeof bVal === 'string') {
                const comparison = aVal.toLowerCase().localeCompare(bVal.toLowerCase());
                return sortOrder === 'asc' ? comparison : -comparison;
            }
            return 0;
        });

        if (filtered.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="5" class="text-center text-muted">No records found.</td>
                </tr>
            `);
            $('#teamsSpecPagination').empty();
            return;
        }

        // Pagination calculations
        const totalPages = Math.ceil(filtered.length / teamsPerPage);
        const startIndex = (page - 1) * teamsPerPage;
        const endIndex = startIndex + teamsPerPage;
        const paginatedTeams = filtered.slice(startIndex, endIndex);

        paginatedTeams.forEach(team => {
            // Parse members from comma-separated string
            let adviser = '';
            let leader = '';
            const members = [];
            
            if (team.members) {
                const membersList = team.members.split(', ');
                membersList.forEach((member, index) => {
                    if (index === 0) {
                        adviser = member; // First is always adviser
                    } else if (index === 1) {
                        leader = member; // Second is always leader
                        members.push({ name: member }); // Include leader in members list
                    } else {
                        members.push({ name: member }); // Rest are members
                    }
                });
            }
            
            // Build members HTML as bulleted list
            const membersHtml = members.length > 0 
                ? '<ul class="mb-0 ps-3 small">' + members.map(m => `<li>${escapeHtml(m.name)}</li>`).join('') + '</ul>'
                : '<span class="text-muted">No members</span>';
            
            const row = `
                <tr>
                    <td><strong>${escapeHtml(team.name)}</strong></td>
                    <td class="d-none d-md-table-cell">${escapeHtml(team.program || '-')}</td>
                    <td class="d-none d-lg-table-cell">${adviser ? escapeHtml(adviser) : '<span class="text-muted">No adviser</span>'}</td>
                    <td class="d-none d-lg-table-cell">${membersHtml}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary assign-team-spec-btn" data-team-id="${team.id}" data-team-name="${escapeHtml(team.name)}">
                            <i class="bi bi-mortarboard me-1"></i>Assign
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Render pagination
        renderTeamsPagination(totalPages, page);
    }

    // Render teams pagination
    function renderTeamsPagination(totalPages, currentPage) {
        const pagination = $('#teamsSpecPagination');
        pagination.empty();

        if (totalPages <= 1) return;

        // Previous button
        pagination.append(`
            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
            </li>
        `);

        // Page numbers with ellipsis
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
            if (startPage > 2) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
            pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
        }

        // Next button
        pagination.append(`
            <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
            </li>
        `);
    }

    // Handle teams pagination clicks
    $(document).on('click', '#teamsSpecPagination a.page-link', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (!isNaN(page) && page !== currentTeamsPage) {
            renderTeams(page);
        }
    });

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
        teamSelect.empty().append('<option value="">Select Specialization</option>');

        const userSelect = $('#userSpecializationSelectModal');
        userSelect.empty();

        activeSpecializations.forEach(spec => {
            const option = `<option value="${spec.id}">${escapeHtml(spec.name)}</option>`;
            teamSelect.append(option);
            userSelect.append(option);
        });
    }

    // Team assign button handler - Opens modal
    $(document).on('click', '.assign-team-spec-btn', function() {
        const teamId = $(this).data('team-id');
        const teamName = $(this).data('team-name');
        
        selectedTeamId = teamId;
        const team = myTeams.find(t => t.id == teamId);
        
        if (team) {
            $('#selectedTeamNameModal').html(`${escapeHtml(teamName)}<br><small class="text-muted">${escapeHtml(team.program || 'No program')}</small>`);
            $('#teamSpecNotes').val('');
            $('#teamSpecializationSelect').val('');
            loadTeamSpecializationsInModal(teamId);
            
            // Open modal
            const modal = new bootstrap.Modal(document.getElementById('teamSpecAssignmentModal'));
            modal.show();
        }
    });

    // Load team specializations in modal
    function loadTeamSpecializationsInModal(teamId) {
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_team_specializations&team_id=' + teamId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                renderTeamSpecializationsInModal(response.data || []);
            },
            error: function() {
                $('#teamSpecializationsModal').html('<p class="text-danger">Error loading specializations</p>');
            }
        });
    }

    // Render team specializations in modal
    function renderTeamSpecializationsInModal(specs) {
        const container = $('#teamSpecializationsModal');
        container.empty();

        if (specs.length === 0) {
            container.html('<p class="text-muted">No specializations assigned</p>');
            return;
        }

        specs.forEach(spec => {
            const item = `
                <div class="d-flex justify-content-between align-items-start mb-2 p-2 border rounded">
                    <div>
                        <strong>${escapeHtml(spec.specialization_name)}</strong><br>
                        <small class="text-muted">${escapeHtml(spec.notes || 'No notes')}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-team-spec-btn" data-spec-name="${escapeHtml(spec.specialization_name)}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.append(item);
        });
    }

    // Assign to team
    $('#assignToTeamBtn').click(function() {
        if (!validateTeamAssignmentForm() || !selectedTeamId) {
            showAlert('danger', 'Please complete all required fields correctly before assigning.');
            return;
        }

        const specializationId = $('#teamSpecializationSelect').val();
        const notes = sanitizeModalText($('#teamSpecNotes').val(), 500);

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
                    clearFormValidation('#teamSpecAssignmentModal');
                    loadTeamSpecializationsInModal(selectedTeamId);
                } else {
                    showAlert('danger', response.message);
                }
            }
        });
    });

    // Real-time validation in specialization modals
    $(document).off('input.specializationValidation').on('input.specializationValidation',
        '#specializationName, #specializationDescription, #teamSpecNotes',
        function() {
            const $input = $(this);
            const fieldMap = {
                specializationName: { label: 'Name', required: true, maxLength: 150 },
                specializationDescription: { label: 'Description', required: false, maxLength: 500 },
                teamSpecNotes: { label: 'Notes', required: false, maxLength: 500 }
            };
            const config = fieldMap[$input.attr('id')];

            if (!config) {
                return;
            }

            validateTextField($input, config.label, {
                required: config.required,
                maxLength: config.maxLength
            });
        }
    );

    $(document).off('change.specializationValidation').on('change.specializationValidation', '#teamSpecializationSelect', function() {
        const $specializationSelect = $(this);
        clearFieldValidation($specializationSelect);
        if (!$specializationSelect.val()) {
            setFieldValidationError($specializationSelect, 'Specialization is required.');
        }
    });

    $('#specializationModal').on('hidden.bs.modal', function() {
        clearFormValidation('#specializationForm');
    });

    $('#teamSpecAssignmentModal').on('hidden.bs.modal', function() {
        clearFormValidation('#teamSpecAssignmentModal');
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
                        loadTeamSpecializationsInModal(selectedTeamId);
                    } else {
                        showAlert('danger', response.message);
                    }
                }
            });
        }
    });

    // Team search
    $('#teamSearch').on('input', function() {
        renderTeams(1);
    });

    // Team program filter change
    $('#teamProgramFilter').change(function() {
        renderTeams(1);
    });

    // Team sort change
    $('#teamSpecSortSelect').change(function() {
        renderTeams(1);
    });

    // ==================== USER SPECIALIZATION MANAGEMENT ====================
    let assignableUsers = [];
    let selectedUserId = null;
    let currentUsersSpecPage = 1;
    const usersSpecPerPage = 10;
    let userSpecModalInstance = null;

    function loadAssignableUsers() {
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_assignable_users',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                assignableUsers = (response.success && response.data) ? response.data : [];
                renderUsersSpec(1);
            },
            error: function() {
                $('#usersSpecTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading users</td></tr>');
            }
        });
    }

    function renderUsersSpec(page = 1) {
        currentUsersSpecPage = page;
        const tbody = $('#usersSpecTableBody');
        tbody.empty();

        const search = ($('#userSpecSearch').val() || '').toLowerCase();
        let filtered = assignableUsers;
        if (search) {
            filtered = filtered.filter(u =>
                (u.name || '').toLowerCase().includes(search) ||
                (u.email || '').toLowerCase().includes(search));
        }

        if (filtered.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted">No records found.</td></tr>');
            $('#usersSpecPagination').empty();
            return;
        }

        const totalPages = Math.ceil(filtered.length / usersSpecPerPage);
        const start = (page - 1) * usersSpecPerPage;
        const pageItems = filtered.slice(start, start + usersSpecPerPage);

        pageItems.forEach(u => {
            tbody.append(`
                <tr>
                    <td><strong>${escapeHtml(u.name)}</strong><br><small class="text-muted">${escapeHtml(u.email || '')}</small></td>
                    <td class="d-none d-md-table-cell">${escapeHtml(u.program || '-')}</td>
                    <td class="d-none d-lg-table-cell">${escapeHtml(u.role || '-')}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary manage-user-spec-btn" data-user-id="${u.id}" data-user-name="${escapeHtml(u.name)}">
                            <i class="bi bi-mortarboard me-1"></i>Manage
                        </button>
                    </td>
                </tr>
            `);
        });

        renderUsersSpecPagination(totalPages, page);
    }

    function renderUsersSpecPagination(totalPages, currentPage) {
        const pagination = $('#usersSpecPagination');
        pagination.empty();
        if (totalPages <= 1) return;

        pagination.append(`<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a></li>`);
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`<li class="page-item ${currentPage === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
        }
        pagination.append(`<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a></li>`);
    }

    $(document).on('click', '#usersSpecPagination a.page-link', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (!isNaN(page) && page !== currentUsersSpecPage) renderUsersSpec(page);
    });

    $('#userSpecSearch').on('input', function() { renderUsersSpec(1); });

    // Open the manage modal for a user
    $(document).on('click', '.manage-user-spec-btn', function() {
        selectedUserId = $(this).data('user-id');
        const uname = $(this).data('user-name');
        const u = assignableUsers.find(x => x.id == selectedUserId);
        $('#selectedUserNameModal').html(`${escapeHtml(uname)}<br><small class="text-muted">${escapeHtml((u && u.program) || '')}</small>`);
        $('#userSpecNotesModal').val('');
        $('#userSpecializationSelectModal').val([]);
        loadUserSpecializationsInModal(selectedUserId);
        if (!userSpecModalInstance) {
            userSpecModalInstance = new bootstrap.Modal(document.getElementById('userSpecAssignmentModal'));
        }
        userSpecModalInstance.show();
    });

    function loadUserSpecializationsInModal(userId) {
        $('#userSpecializationsModal').html('<div class="text-muted">Loading…</div>');
        $.ajax({
            url: 'includes/specialization_assignment_api.php?action=get_user_specializations&user_id=' + userId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    $('#userSpecializationsModal').html(`<p class="text-danger">${escapeHtml(response.message || 'Permission denied')}</p>`);
                    return;
                }
                renderUserSpecializationsInModal(response.data || []);
            },
            error: function() { $('#userSpecializationsModal').html('<p class="text-danger">Error loading specializations</p>'); }
        });
    }

    function renderUserSpecializationsInModal(specs) {
        const container = $('#userSpecializationsModal');
        container.empty();
        if (specs.length === 0) { container.html('<p class="text-muted">No specializations assigned</p>'); return; }
        specs.forEach(spec => {
            container.append(`
                <div class="d-flex justify-content-between align-items-start mb-2 p-2 border rounded">
                    <div><strong>${escapeHtml(spec.specialization_name)}</strong></div>
                    <button class="btn btn-sm btn-outline-danger remove-user-spec-btn" data-spec-name="${escapeHtml(spec.specialization_name)}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `);
        });
    }

    // Assign selected specializations to the user (one request per specialization)
    $('#assignToUserBtn').click(function() {
        const ids = $('#userSpecializationSelectModal').val() || [];
        const notes = sanitizeModalText($('#userSpecNotesModal').val(), 500);
        if (!selectedUserId || ids.length === 0) {
            showAlert('danger', 'Please select at least one specialization.');
            return;
        }
        let done = 0, okc = 0, failc = 0;
        ids.forEach(specId => {
            $.ajax({
                url: 'includes/specialization_assignment_api.php',
                method: 'POST',
                dataType: 'json',
                data: { action: 'assign_to_user', user_id: selectedUserId, specialization_id: specId, notes: notes },
                success: function(r) { r.success ? okc++ : failc++; },
                error: function() { failc++; },
                complete: function() {
                    done++;
                    if (done === ids.length) {
                        if (okc > 0) showAlert('success', `${okc} specialization(s) assigned.`);
                        if (failc > 0) showAlert('warning', `${failc} could not be assigned (already assigned or not permitted).`);
                        $('#userSpecializationSelectModal').val([]);
                        $('#userSpecNotesModal').val('');
                        loadUserSpecializationsInModal(selectedUserId);
                    }
                }
            });
        });
    });

    // Remove a specialization from the user
    $(document).on('click', '.remove-user-spec-btn', function() {
        const specName = $(this).data('spec-name');
        if (!confirm('Remove this specialization from the user?')) return;
        $.ajax({
            url: 'includes/specialization_assignment_api.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'remove_from_user', user_id: selectedUserId, specialization_name: specName },
            success: function(r) {
                if (r.success) { showAlert('success', r.message); loadUserSpecializationsInModal(selectedUserId); }
                else showAlert('danger', r.message);
            }
        });
    });

    // ==================== SHARED UTILITIES ====================
    function sanitizeModalText(value, maxLength = null) {
        let cleaned = (value || '').toString();
        cleaned = cleaned.replace(/<[^>]*>/g, '');
        cleaned = cleaned.replace(specEmojiRegex, '');
        cleaned = cleaned.replace(/\s+/g, ' ').trim();

        if (maxLength && cleaned.length > maxLength) {
            cleaned = cleaned.substring(0, maxLength);
        }

        return cleaned;
    }

    function clearFieldValidation($input) {
        $input.removeClass('is-invalid');
        $input.siblings('.invalid-feedback').remove();
    }

    function setFieldValidationError($input, message) {
        clearFieldValidation($input);
        $input.addClass('is-invalid');
        $input.after(`<div class="invalid-feedback">${message}</div>`);
    }

    function clearFormValidation(formSelector) {
        const $form = $(formSelector);
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    }

    function validateTextField($input, fieldName, options = {}) {
        const config = {
            required: false,
            maxLength: null,
            ...options
        };

        const rawValue = ($input.val() || '').toString();
        const trimmedValue = rawValue.trim();
        const hasHtml = typeof ValidationUtils !== 'undefined' && ValidationUtils.containsHTML(rawValue);
        const hasEmoji = typeof ValidationUtils !== 'undefined' && ValidationUtils.containsEmoji(rawValue);

        clearFieldValidation($input);

        if (config.required && !trimmedValue) {
            setFieldValidationError($input, `${fieldName} is required.`);
            return false;
        }

        if (trimmedValue) {
            if (hasHtml) {
                setFieldValidationError($input, `HTML tags are not allowed in ${fieldName}.`);
                return false;
            }

            if (hasEmoji) {
                setFieldValidationError($input, `Emojis are not allowed in ${fieldName}.`);
                return false;
            }

            if (config.maxLength && trimmedValue.length > config.maxLength) {
                setFieldValidationError($input, `${fieldName} cannot exceed ${config.maxLength} characters.`);
                return false;
            }
        }

        return true;
    }

    function validateSpecializationForm() {
        let isValid = true;

        isValid = validateTextField($('#specializationName'), 'Name', { required: true, maxLength: 150 }) && isValid;
        isValid = validateTextField($('#specializationDescription'), 'Description', { maxLength: 500 }) && isValid;

        return isValid;
    }

    function validateTeamAssignmentForm() {
        let isValid = true;
        const $specializationSelect = $('#teamSpecializationSelect');

        clearFieldValidation($specializationSelect);
        if (!$specializationSelect.val()) {
            setFieldValidationError($specializationSelect, 'Specialization is required.');
            isValid = false;
        }

        isValid = validateTextField($('#teamSpecNotes'), 'Notes', { maxLength: 500 }) && isValid;

        return isValid;
    }

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
    $('.spec-tab-btn[data-spec-tab="teamAssignment"]').on('click', function() {
        if (myTeams.length === 0) {
            loadAllAssignmentData();
        }
    });

    // Load assignable users when the User Specializations tab button is clicked.
    // (activeSpecializations is loaded by loadAllAssignmentData so the modal's select is populated.)
    $('.spec-tab-btn[data-spec-tab="userAssignment"]').on('click', function() {
        if (activeSpecializations.length === 0) {
            loadActiveSpecializations();
        }
        if (assignableUsers.length === 0) {
            loadAssignableUsers();
        }
    });
});
</script>
</div>