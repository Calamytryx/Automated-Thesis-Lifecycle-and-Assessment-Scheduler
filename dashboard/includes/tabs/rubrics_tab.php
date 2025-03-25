<!-- Rubrics Tab -->
<div class="tab-pane fade" id="rubrics" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center my-3">
        <h4>Rubrics Management</h4>
        <button class="btn btn-primary add-btn" data-table="rubrics">
            <i class="fas fa-plus"></i> Add New Rubric
        </button>
    </div>

    <!-- Rubrics Table -->
    <div class="table-responsive">
        <table class="table table-hover db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Max Score</th>
                    <th>Quality Criteria</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rubricsTableBody">
                <!-- Data will be loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Rubrics pagination">
        <ul class="pagination justify-content-center" id="rubricsPagination">
            <!-- Pagination will be loaded dynamically -->
        </ul>
    </nav>
</div>

<!-- Add/Edit Rubric Modal -->
<div class="modal fade" id="rubricModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rubricModalTitle">Add New Rubric</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rubricForm">
                    <input type="hidden" name="table" value="rubrics">
                    <input type="hidden" name="id" id="rubricId">
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Rubric Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="1" required></textarea>
                        </div>
                    </div>

                    <!-- Quality Criteria Controls -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="qualityCriteriaCount" class="form-label">Number of Quality Criteria</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="qualityCriteriaCount" min="1" max="5" value="1">
                                <button class="btn btn-outline-secondary" type="button" id="updateQualityCriteria">Update</button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-info mt-4">
                                <small>Set the number of quality levels for your rubric (e.g., Excellent, Good, Fair, Poor)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quality Criteria Configuration -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Quality Criteria Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div id="qualityCriteriaContainer">
                                <!-- Quality criteria will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Rubric Criteria Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <h6>Rubric Criteria</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addCriterion">
                                <i class="fas fa-plus"></i> Add Criterion
                            </button>
                        </div>
                    </div>

                    <!-- Rubric Table Preview -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Rubric Preview</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="rubricPreviewTable">
                                    <thead>
                                        <tr id="rubricHeaderRow">
                                            <th style="width: 30%;">Criteria</th>
                                            <!-- Quality criteria headers will be added here -->
                                            <th style="width: 10%;">Score</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rubricPreviewBody">
                                        <!-- Rubric rows will be added here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="100%" class="text-end">
                                                Total Score: <span id="totalScoreDisplay">0</span>
                                                <input type="hidden" name="max_total_score" id="maxTotalScore" value="0">
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRubric">Save Rubric</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="rubricDeleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this rubric?</p>
                <input type="hidden" id="rubricToDeleteId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRubricDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
.quality-level-input {
    width: 100%;
    padding: 5px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.points-input {
    width: 60px;
    text-align: center;
}
.criterion-row {
    margin-bottom: 10px;
}
.criterion-description {
    width: 100%;
    padding: 5px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.score-input {
    width: 60px;
    text-align: center;
}
</style>

    <script>
// Initialize the rubrics table
function loadRubrics(page = 1) {
    $.ajax({
        url: 'includes/tabs/get_table.php',
        method: 'GET',
        data: {
            table: 'rubrics',
            page: page
        },
        success: function(response) {
            if (response.data) {
                var tbody = $('#rubricsTableBody');
                tbody.empty();
                
                response.data.forEach(function(rubric) {
                    var row = `
                                <tr>
                                    <td>${rubric.name}</td>
                                    <td>${rubric.description}</td>
                            <td>${rubric.max_total_score || 0}</td>
                            <td>${rubric.quality_criteria_count || 0}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input rubric-status" type="checkbox" 
                                           data-id="${rubric.id}" 
                                           ${rubric.is_active == 1 ? 'checked' : ''}>
                                        </div>
                                    </td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-rubric-btn" data-id="${rubric.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-rubric-btn" data-id="${rubric.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                    </td>
                                </tr>
                            `;
                    tbody.append(row);
                });

                // Update pagination
                updatePagination(response.total_pages, page);
            }
        }
    });
}

// Update pagination
function updatePagination(totalPages, currentPage) {
    var pagination = $('#rubricsPagination');
    pagination.empty();
    
    // Previous button
    pagination.append(`
        <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
                            </li>
    `);
    
    // Page numbers
    for (var i = 1; i <= totalPages; i++) {
        pagination.append(`
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
        `);
    }
    
    // Next button
    pagination.append(`
        <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
                            </li>
    `);
}

// Generate quality criteria inputs
function generateQualityCriteriaInputs() {
    var count = parseInt($('#qualityCriteriaCount').val()) || 1;
    var container = $('#qualityCriteriaContainer');
    container.empty();
    
    var row = $('<div class="row mb-2"></div>');
    
    // Add headers
    row.append('<div class="col-md-4"><strong>Level Name</strong></div>');
    row.append('<div class="col-md-2"><strong>Points</strong></div>');
    row.append('<div class="col-md-6"><strong>Description</strong></div>');
    
    container.append(row);
    
    // Add inputs for each quality level
    for (var i = 0; i < count; i++) {
        var levelRow = $('<div class="row mb-2"></div>');
        
        levelRow.append(`
            <div class="col-md-4">
                <input type="text" class="form-control quality-level-input" 
                       name="quality_level[]" value="Level ${i + 1}" 
                       data-level="${i + 1}" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control points-input" 
                       name="points[]" value="${5 - i}" min="0" max="10" 
                       data-level="${i + 1}" required>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" 
                       name="quality_description[]" 
                       placeholder="Description for Level ${i + 1}" 
                       data-level="${i + 1}">
            </div>
        `);
        
        container.append(levelRow);
    }
    
    // Update the table header
    updateRubricTableHeader();
}

// Update the rubric table header based on quality criteria
function updateRubricTableHeader() {
    var headerRow = $('#rubricHeaderRow');
    
    // Clear existing quality headers (keep the first and last columns)
    headerRow.find('th:not(:first-child):not(:last-child)').remove();
    
    // Add headers for each quality level
    var count = parseInt($('#qualityCriteriaCount').val()) || 1;
    var lastCell = headerRow.find('th:last-child');
    
    for (var i = 0; i < count; i++) {
        var levelName = $(`input[name="quality_level[]"][data-level="${i + 1}"]`).val() || `Level ${i + 1}`;
        var points = $(`input[name="points[]"][data-level="${i + 1}"]`).val() || (5 - i);
        
        $(`<th style="width: ${60 / count}%;">${levelName}<br>(${points} points)</th>`).insertBefore(lastCell);
    }
    
    // Update existing rows to match the new header structure
    updateExistingRows();
}

// Add a new criterion row to the rubric preview
function addCriterionRow() {
    var rowCount = $('#rubricPreviewBody tr').length + 1;
    var qualityCriteriaCount = parseInt($('#qualityCriteriaCount').val()) || 1;
    
    var row = $('<tr class="criterion-row"></tr>');
    
    // Add criterion description cell
    row.append(`
        <td>
            <div class="d-flex justify-content-between align-items-center">
                <input type="text" class="form-control criterion-description" 
                       name="criterion_description[]" 
                       placeholder="Enter criterion description" required>
                <button type="button" class="btn btn-sm btn-danger ms-2 delete-criterion">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `);
    
    // Add cells for each quality level
    for (var i = 0; i < qualityCriteriaCount; i++) {
        row.append(`
            <td class="text-center">
                <input type="radio" name="criterion_${rowCount}_level" 
                       value="${i + 1}" class="criterion-level-radio"
                       data-row="${rowCount}" data-level="${i + 1}">
            </td>
        `);
    }
    
    // Add score input cell
    var maxPoints = getMaxPoints();
    row.append(`
        <td>
            <input type="number" class="form-control score-input" 
                   name="criterion_score[]" value="0" min="0" max="${maxPoints}" 
                   data-row="${rowCount}" readonly>
        </td>
    `);
    
    $('#rubricPreviewBody').append(row);
    updateTotalScore();
}

// Update existing rows to match the current quality criteria structure
function updateExistingRows() {
    var rows = $('#rubricPreviewBody tr.criterion-row');
    var qualityCriteriaCount = parseInt($('#qualityCriteriaCount').val()) || 1;
    var maxPoints = getMaxPoints();
    
    rows.each(function(rowIndex) {
        var $row = $(this);
        var rowNum = rowIndex + 1;
        
        // Clear existing quality level cells (keep the first and last columns)
        $row.find('td:not(:first-child):not(:last-child)').remove();
        
        // Add cells for each quality level
        var scoreCell = $row.find('td:last-child');
        
        for (var i = 0; i < qualityCriteriaCount; i++) {
            $(`
                <td class="text-center">
                    <input type="radio" name="criterion_${rowNum}_level" 
                           value="${i + 1}" class="criterion-level-radio"
                           data-row="${rowNum}" data-level="${i + 1}">
                </td>
            `).insertBefore(scoreCell);
        }
        
        // Update max value for score input
        scoreCell.find('input.score-input').attr('max', maxPoints);
    });
    
    updateTotalScore();
}

// Get the maximum points from quality criteria
function getMaxPoints() {
    var maxPoints = 0;
    $('input.points-input').each(function() {
        var points = parseInt($(this).val()) || 0;
        maxPoints = Math.max(maxPoints, points);
    });
    return maxPoints;
}

// Update the total score
function updateTotalScore() {
    var totalScore = 0;
    $('input.score-input').each(function() {
        totalScore += parseInt($(this).val()) || 0;
    });
    
    $('#totalScoreDisplay').text(totalScore);
    $('#maxTotalScore').val(totalScore);
}

// Load rubric for editing
function loadRubricForEdit(rubricId) {
    $.ajax({
        url: 'includes/get_item_details.php',
        method: 'POST',
        data: {
            table: 'rubrics',
            id: rubricId
        },
        success: function(response) {
            console.log("Edit response:", response);
            if (response.success) {
                var rubric = response.data;
                
                // Set form mode to edit
                $('#rubricModalTitle').text('Edit Rubric');
                $('#rubricId').val(rubric.id);
                $('#name').val(rubric.name);
                $('#description').val(rubric.description);
                
                // Set quality criteria count
                $('#qualityCriteriaCount').val(rubric.quality_criteria_count || 1);
                
                // Generate quality criteria inputs
                generateQualityCriteriaInputs();
                
                // Clear existing rows
                $('#rubricPreviewBody').empty();
                
                // Populate quality criteria
                if (rubric.quality_criteria && rubric.quality_criteria.length > 0) {
                    // Quality criteria need to be sorted by quality_level
                    var sortedCriteria = rubric.quality_criteria.sort((a, b) => 
                        parseInt(a.quality_level) - parseInt(b.quality_level));
                        
                    sortedCriteria.forEach(function(criteria) {
                        var level = parseInt(criteria.quality_level);
                        $(`input[name="quality_level[]"][data-level="${level}"]`)
                            .val(criteria.description || `Level ${level}`);
                        $(`input[name="points[]"][data-level="${level}"]`)
                            .val(criteria.points);
                        $(`input[name="quality_description[]"][data-level="${level}"]`)
                            .val(criteria.description || '');
                    });
                    
                    // Update the header after setting values
                    updateRubricTableHeader();
                }
                
                // Add rows for each criterion
                if (rubric.rows && rubric.rows.length > 0) {
                    // Sort rows by order_index
                    var sortedRows = rubric.rows.sort((a, b) => 
                        parseInt(a.order_index) - parseInt(b.order_index));
                        
                    sortedRows.forEach(function(row) {
                        addCriterionRow();
                        var rowIndex = $('#rubricPreviewBody tr').length;
                        $(`#rubricPreviewBody tr:nth-child(${rowIndex}) input.criterion-description`)
                            .val(row.description);
                    });
                }
                
                // Show the modal
                $('#rubricModal').modal('show');
            } else {
                alert('Error loading rubric: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
            alert('Error loading rubric: ' + error);
        }
    });
}

// Save rubric
function saveRubric() {
    // Validate the form
    var isValid = true;
    var errorMessages = [];
    
    // Check if name is provided
    if (!$('#name').val().trim()) {
        isValid = false;
        errorMessages.push("Rubric name is required");
    }
    
    // Check if at least one criterion is added
    if ($('#rubricPreviewBody tr').length === 0) {
        isValid = false;
        errorMessages.push("At least one criterion is required");
    }
    
    // Check if all criteria have descriptions
    $('#rubricPreviewBody tr').each(function(index) {
        var description = $(this).find('input.criterion-description').val().trim();
        if (!description) {
            isValid = false;
            errorMessages.push(`Criterion ${index + 1} description is required`);
        }
    });
    
    // Check if all criteria have a selected level
    $('#rubricPreviewBody tr').each(function(index) {
        var rowNum = index + 1;
        if (!$(`input[name="criterion_${rowNum}_level"]:checked`).length) {
            isValid = false;
            errorMessages.push(`Criterion ${index + 1} needs a selected quality level`);
        }
    });
    
    if (!isValid) {
        alert("Please fix the following errors:\n- " + errorMessages.join("\n- "));
        return;
    }
    
    // Create FormData object
    var formData = new FormData(document.getElementById('rubricForm'));
    var isEdit = $('#rubricId').val() !== '';
    
    // Add criterion levels data
    $('#rubricPreviewBody tr').each(function(index) {
        var rowNum = index + 1;
        var selectedLevel = $(`input[name="criterion_${rowNum}_level"]:checked`).val();
        if (selectedLevel) {
            formData.append(`criterion_${rowNum}_selected_level`, selectedLevel);
        }
    });
    
    // Log the form data for debugging
    console.log("Saving rubric with data:");
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    $.ajax({
        url: isEdit ? 'includes/update_item.php' : 'includes/add_items.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log("Server response:", response);
            if (response.success) {
                $('#rubricModal').modal('hide');
                loadRubrics();
                alert(isEdit ? 'Rubric updated successfully' : 'Rubric added successfully');
            } else {
                var errorMsg = response.message || 'Unknown error occurred';
                alert('Error: ' + errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
            alert('Error: ' + error);
        }
    });
}

// Delete rubric
function deleteRubric(rubricId) {
    // Show confirmation modal instead of using confirm()
    $('#rubricToDeleteId').val(rubricId);
    var deleteModal = new bootstrap.Modal(document.getElementById('rubricDeleteConfirmModal'));
    deleteModal.show();
}

// Event Listeners
$(document).ready(function() {
    // Unbind any existing click handlers to prevent duplication
    $('.add-btn[data-table="rubrics"]').off('click');
    $('.edit-rubric-btn').off('click');
    $('.delete-rubric-btn').off('click');
    $('#updateQualityCriteria').off('click');
    $('#addCriterion').off('click');
    $('.quality-level-input, .points-input').off('input');
    $('.criterion-level-radio').off('change');
    $('#saveRubric').off('click');
    $('.rubric-status').off('change');
    $('.delete-criterion').off('click');
    
    // Load rubrics on page load
            loadRubrics();

    // Handle pagination clicks
    $('#rubricsPagination').off('click').on('click', 'a.page-link', function(e) {
                e.preventDefault();
        var page = $(this).data('page');
                        loadRubrics(page);
    });
    
    // Handle add rubric button click - use a namespaced event to avoid conflicts
    $(document).off('click.rubrics').on('click.rubrics', '.add-btn[data-table="rubrics"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Rubric tab: Add button clicked');
        
        // Reset form
        $('#rubricForm')[0].reset();
        $('#rubricId').val('');
        $('#rubricModalTitle').text('Add New Rubric');
        $('#rubricPreviewBody').empty();
        
        // Generate default quality criteria
        generateQualityCriteriaInputs();
        
        // Add one criterion row by default
        addCriterionRow();
        
        // Show the modal
        $('#rubricModal').modal('show');
        
        return false; // Important: prevent other handlers from running
    });
    
    // Handle edit rubric button click - use namespaced events
    $(document).off('click.rubricEdit').on('click.rubricEdit', '.edit-rubric-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Rubric tab: Edit button clicked');
        var rubricId = $(this).data('id');
        loadRubricForEdit(rubricId);
        return false; // Important: prevent other handlers from running
    });
    
    // Handle delete rubric button click - use namespaced events
    $(document).off('click.rubricDelete').on('click.rubricDelete', '.delete-rubric-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Rubric tab: Delete button clicked');
        var rubricId = $(this).data('id');
        deleteRubric(rubricId);
        return false; // Important: prevent other handlers from running
    });
    
    // Handle update quality criteria button click
    $('#updateQualityCriteria').off('click').on('click', function() {
        generateQualityCriteriaInputs();
    });
    
    // Handle add criterion button click
    $('#addCriterion').off('click').on('click', function() {
        addCriterionRow();
    });
    
    // Handle quality level input changes
    $(document).off('input.rubrics').on('input.rubrics', '.quality-level-input, .points-input', function() {
        updateRubricTableHeader();
    });
    
    // Handle criterion level radio button changes
    $(document).off('change.rubrics').on('change.rubrics', '.criterion-level-radio', function() {
        var row = $(this).data('row');
        var level = $(this).data('level');
        var points = $(`input[name="points[]"][data-level="${level}"]`).val() || 0;
        
        $(`input.score-input[data-row="${row}"]`).val(points);
        updateTotalScore();
    });
    
    // Handle save rubric button click
    $('#saveRubric').off('click').on('click', function() {
        saveRubric();
    });
    
    // Handle rubric status toggle
    $(document).off('change.rubricStatus').on('change.rubricStatus', '.rubric-status', function() {
        var rubricId = $(this).data('id');
        var isActive = $(this).prop('checked');
        
        $.ajax({
            url: 'includes/update_rubric_status.php',
            method: 'POST',
            data: {
                rubric_id: rubricId,
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    // Reload to show updated status
                    loadRubrics();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Handle delete criterion button click
    $(document).off('click.deleteCriterion').on('click.deleteCriterion', '.delete-criterion', function() {
        if (confirm('Are you sure you want to remove this criterion?')) {
            $(this).closest('tr').remove();
            updateTotalScore();
        }
    });

    // Handle confirm rubric delete button click
    $(document).off('click.confirmRubricDelete').on('click.confirmRubricDelete', '#confirmRubricDelete', function() {
        var rubricId = $('#rubricToDeleteId').val();
        
        $.ajax({
            url: 'includes/delete_item.php',
            method: 'POST',
            data: {
                table: 'rubrics',
                id: rubricId
            },
            success: function(response) {
                if (response.success) {
                    $('#rubricDeleteConfirmModal').modal('hide');
                    loadRubrics();
                    showToast('Success', 'Rubric deleted successfully', 'success');
                } else {
                    showToast('Error', response.message || 'Unknown error occurred', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                showToast('Error', 'Unable to delete rubric: ' + error, 'error');
            }
        });
    });
        });
    </script>
