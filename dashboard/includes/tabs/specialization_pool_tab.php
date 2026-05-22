<!-- Specialization Pool Management Tab -->
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
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard-fill me-2"></i>
                            Specialization Pool Management
                        </h5>
                        <button class="btn btn-primary" id="addSpecializationBtn">
                            <i class="bi bi-plus-circle me-1"></i>
                            Add Specialization
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Controls -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="filterCollege">
                                <option value="">All Colleges</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filterDepartment">
                                <option value="">All Departments</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Specializations Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="specializationsTable">
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
    </div>
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

<script>
$(document).ready(function() {
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

        if (specializationsData.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="text-center text-muted">No records found.</td>
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
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary edit-spec-btn" data-id="${spec.id}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-${spec.is_active == 1 ? 'warning' : 'success'} toggle-status-btn" data-id="${spec.id}">
                            <i class="bi bi-toggle-${spec.is_active == 1 ? 'on' : 'off'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-spec-btn" data-id="${spec.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
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
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Error: ' + error);
            }
        });
    });

    // Toggle status
    $(document).on('click', '.toggle-status-btn', function() {
        const id = $(this).data('id');
        
        if (confirm('Are you sure you want to toggle the status of this specialization?')) {
            $.ajax({
                url: 'includes/specialization_pool_api.php',
                method: 'POST',
                data: { action: 'toggle_status', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadSpecializations();
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

    // Helper function to escape HTML
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

    // Show alert
    function showAlert(type, message) {
        if (typeof showToast === 'function') {
            const normalizedType = type === 'danger' ? 'error' : (type === 'warning' ? 'notice' : type);
            const title = normalizedType === 'success' ? 'Success' : normalizedType === 'notice' ? 'Notice' : normalizedType === 'info' ? 'Info' : 'Error';
            showToast(title, message, normalizedType);
            return;
        }

        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.card-body').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initial load
    loadSpecializations();
});
</script>
