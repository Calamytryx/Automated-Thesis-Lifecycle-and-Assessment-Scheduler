<!-- Rubrics Tab -->
<div class="tab-pane fade" id="rubrics" role="tabpanel">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Rubrics Management</h3>
                <p class="text-muted">Create and manage evaluation rubrics for thesis defenses, including numerical, yes/no, and pass/fail scoring systems</p>
                <?php if ($_SESSION['usertype'] == 0): ?>
                <div class="mt-2">
                    <a href="#rubric-groups" class="tab-redirect-link" onclick="document.getElementById('rubric-groups-tab').click(); return false;">
                        <i class="bi bi-list-columns-reverse"></i>
                        <span>Group created Rubrics for Defense</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rubrics Management Controls -->
        <div class="row">
            <div class="col-12">
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="input-group user-control-height m-0">
                            <span class="input-group-text border-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-0" id="rubricsSearchInput" placeholder="Search rubric name...">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <select class="form-select user-control-height" id="rubricsTypeFilterSelect">
                            <option value="all">All Rubric Types</option>
                            <option value="numerical">Numerical</option>
                            <option value="yesno">Yes/No</option>
                            <option value="passfail">Pass/Fail</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-5">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn feature-btn add-btn user-control-height w-100 w-md-auto" data-table="rubrics">
                                <i class="fas fa-plus me-2"></i>Add New Rubric
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rubrics Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive db-table-container">
        <table class="table table-hover db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Type</th>
                   <!-- <th>Status</th> -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rubricsTableBody">
                <!-- Data will be loaded dynamically -->
            </tbody>
        </table>

                <!-- Pagination -->
                <nav aria-label="Rubrics pagination">
                    <ul class="pagination justify-content-center" id="rubricsPagination">
                        <!-- Pagination will be loaded dynamically -->
                    </ul>
                </nav>
                </div>
            </div>
        </div>
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

                    <!-- Rubric Type, Defense Type, and Description -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="rubric_type" class="form-label">Rubric Type</label>
                            <select class="form-select" id="rubric_type" name="rubric_type" required>
                                <option value="numerical" selected>Numerical Scoresheet</option>
                                <option value="yesno">Yes/No Option</option>
                                <option value="passfail">Pass/Fail Option</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="defense_type" class="form-label">Defense Type</label>
                            <select class="form-select" id="defense_type" name="defense_type" required>
                                <option value="Title Defense" selected>Title Defense</option>
                                <option value="Proposal Defense">Proposal Defense</option>
                                <option value="Final Defense">Final Defense</option>
                                <option value="Re-Defense">Re-Defense</option>
                            </select>


                        </div>
                        <div class="col-md-4">
                            <label for="rubric_description" class="form-label">Rubric Description</label>
                            <input type="text" class="form-control" id="rubric_description" name="rubric_description" placeholder="Optional description">
                        </div>
                    </div>

                    <!-- Applicable Programs (Collapsible Checkboxes) -->
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#programsCollapse" aria-expanded="false" aria-controls="programsCollapse">
                            Applicable Programs <i class="fas fa-chevron-down ms-2"></i>
                        </button>
                        <div class="collapse mt-2" id="programsCollapse">
                            <div class="card card-body">
                                <div id="programCheckboxesContainer">
                                    <!-- Checkboxes will be loaded here dynamically -->
                                    <p class="text-muted">Choose a program...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Applicable Programs -->

                    <!-- Numerical Rubric Configuration -->
                    <div id="numericalConfig">
                        <!-- Enable Individual Scoring Checkbox -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_individual_enabled" name="is_individual_enabled" value="1">
                                    <label class="form-check-label" for="is_individual_enabled">Enable Individual Scoring Features</label>
                                </div>
                            </div>
                        </div>

                        <!-- Max Members Input (Only shown if individual enabled) -->
                        <div class="row mb-3" id="maxMembersConfig" style="display:none;">
                            <div class="col-md-4">
                                <label for="max_members" class="form-label">Max Members for Individual Scoring</label>
                                <input type="number" class="form-control" id="max_members" name="max_members" min="1" max="10" value="5" required>
                                <small class="form-text text-muted">Max columns shown during evaluation.</small>
                            </div>
                        </div>

                        <!-- Individual Scoring Only -->
                        <div class="row mb-3" id="individualConfig" style="display:none;">
                            <div class="col-md-4">
                                <label for="max_score_per_criterion" class="form-label">Max Score per Criterion</label>
                                <input type="number" class="form-control" id="max_score_per_criterion" name="max_score_per_criterion" min="0" value="100" required>
                            </div>
                        </div>

                        <!-- Quality Criteria Controls -->
                        <div id="qualityCriteriaControls">
                            <!-- Quality Criteria Controls -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="qualityCriteriaCount" class="form-label">Number of Quality Levels</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="qualityCriteriaCount" min="1" max="5" value="1" required>
                                        <button class="btn btn-outline-secondary" type="button" id="updateQualityCriteria">Update</button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-info mt-4">
                                        <small>Set the number and point values for quality levels (e.g., Excellent, Good, Fair).</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Quality Criteria Configuration -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Quality Level Configuration</h6>
                                </div>
                                <div class="card-body">
                                    <div id="qualityCriteriaContainer">
                                        <!-- Quality criteria inputs for numerical type -->
                                        <!-- Structure will be modified by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Numerical Rubric Configuration -->

                    <!-- Pass/Fail Modifier Configuration (Hidden for other types) -->
                    <div id="passFailModifierConfig" style="display:none;">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Pass/Fail Option Configuration</h6>
                            </div>
                            <div class="card-body">
                                <!-- Pass Row Config -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="passRecommendationText" class="form-label">Pass Row Recommendation Text</label>
                                        <input type="text" class="form-control" id="passRecommendationText" value="System is accepted:">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="passModifierCount" class="form-label">Number of Pass Modifiers</label>
                                        <select class="form-select" id="passModifierCount">
                                            <option value="1" selected>1 (e.g., Pass)</option>
                                            <option value="2">2 (e.g., Pass w/ Minor Rev)</option>
                                            <option value="3">3 (e.g., Pass w/ Major Rev)</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="modifierInputsContainer">
                                    <!-- Inputs for modifier text (textarea) will be generated here -->
                                </div>
                                <hr>
                                <!-- Fail Row Config -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="failRecommendationText" class="form-label">Fail Row Recommendation Text</label>
                                        <input type="text" class="form-control" id="failRecommendationText" value="System is rejected:">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="failOptionText" class="form-label">Fail Option Description</label>
                                        <textarea class="form-control" id="failOptionText" rows="2">Rejected Description</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pass/Fail Thresholds -->
                        <div class="row mb-3" id="passThresholds">
                            <h6 class="mb-2">Acceptability Thresholds (%)</h6>
                            <div class="col-md-4">
                                <label for="total_pass" class="form-label">Pass (Modifier 1)</label>
                                <input type="number" class="form-control pass-threshold" id="total_pass" name="total_pass" value="100" min="0" max="100" data-modifier="1">
                            </div>
                            <div class="col-md-4 modifier-threshold" data-modifier="2" style="display:none;">
                                <label for="minor_revision_pass" class="form-label">Pass (Modifier 2)</label>
                                <input type="number" class="form-control pass-threshold" id="minor_revision_pass" name="minor_revision_pass" value="75" min="0" max="100" data-modifier="2">
                            </div>
                            <div class="col-md-4 modifier-threshold" data-modifier="3" style="display:none;">
                                <label for="major_revision_pass" class="form-label">Pass (Modifier 3)</label>
                                <input type="number" class="form-control pass-threshold" id="major_revision_pass" name="major_revision_pass" value="65" min="0" max="100" data-modifier="3">
                            </div>
                        </div>
                    </div>
                    <!-- End Pass/Fail Modifier Configuration -->

                    <!-- Rubric Criteria Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <h6>Rubric Preview</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addCriterion">
                                <i class="fas fa-plus"></i> Add Criterion
                            </button>
                        </div>
                    </div>

                    <!-- Rubric Table Preview -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="rubricPreviewTable">
                                    <thead>
                                        <tr id="rubricHeaderRow">
                                            <!-- Headers dynamically generated -->
                                        </tr>
                                    </thead>
                                    <tbody id="rubricPreviewBody">
                                        <!-- Rubric rows will be added here -->
                                    </tbody>
                                    <tfoot id="rubricFooter" style="display:none;">
                                        <tr>
                                            <th colspan="100%" class="text-end">
                                                Group Total Score: <span id="totalScoreDisplay">0</span>
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
                <button type="submit" class="btn btn-primary" id="saveRubric">Save Rubric</button>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="rubricDeleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="text-danger mb-3" style="font-size: 3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
                </div>
                <h4 class="fw-bold mb-3">Confirm Deletion</h4>
                <p>Are you sure you want to delete <span id="rubricDeleteTarget" class="fw-semibold">this rubric</span>?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
                <input type="hidden" id="rubricToDeleteId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRubricDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- jQuery UI for Sortable -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<style>
    .drag-over {
        background-color: #f0f0f0;
        border-top: 2px solid #007bff;
    }
    .drag-handle {
        cursor: move;
    }
    .drag-handle:hover {
        color: #007bff !important;
    }
    #rubricPreviewBody tr.criterion-row {
        transition: background-color 0.2s;
    }
    #rubricPreviewBody tr.criterion-row:hover {
        background-color: #f8f9fa;
    }
    #rubricPreviewBody tr.criterion-row[draggable="true"] {
        cursor: move;
    }
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

    /* Meatball menu styles */
    .meatball-btn {
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

    .meatball-btn:hover {
        background-color: #f8f9fa;
        color: #495057;
    }

    .meatball-dropdown-item {
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
        transition: background-color 0.2s ease;
        gap: 8px;
    }

    .meatball-dropdown-item:hover {
        background-color: #f8f9fa;
        color: #212529;
    }

    .meatball-dropdown-item i {
        width: 16px;
        font-size: 12px;
    }
</style>

<script>
    // Initialize the rubrics table
    function loadRubrics(page = 1, search = '', rubricType = 'all') {
        // Clear any existing dropdowns to prevent duplicates 
        document.querySelectorAll('.meatball-dropdown-portal').forEach(portal => portal.remove());
        
        $.ajax({
            url: 'includes/tabs/get_table.php',
            method: 'GET',
            data: {
                table: 'rubrics',
                page: page,
                search: search,
                rubric_type: rubricType
            },
            success: function(response) {
                if (response.data) {
                    var tbody = $('#rubricsTableBody');
                    tbody.empty();

                    if (response.data.length === 0) {
                        tbody.html('<tr><td colspan="4" class="text-center text-muted">No records found.</td></tr>');
                    } else {
                        response.data.forEach(function(rubric) {
                            let typeName = 'Unknown';
                            switch (rubric.rubric_type) {
                                case 'numerical':
                                    typeName = 'Numerical';
                                    break;
                                case 'yesno':
                                    typeName = 'Yes/No';
                                    break;
                                case 'passfail':
                                    typeName = 'Pass/Fail';
                                    break;
                            }

                            var row = `
                            <tr>
                                <td>${rubric.name || 'N/A'}</td>
                                <td>${rubric.description || 'N/A'}</td>
                                <td>${typeName}</td>
                                <td class="action-buttons text-center">
                                    <button class="meatball-btn" data-rubric-id="${rubric.id}" aria-label="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                            tbody.append(row);
                        });
                    }

                    // Update pagination
                    updatePagination(response.total_pages, page);
                } else {
                    $('#rubricsTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading data.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#rubricsTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading data. Please try again.</td></tr>');
            }
        });
    }

    function getCurrentRubricsFilters() {
        return {
            search: ($('#rubricsSearchInput').val() || '').trim(),
            rubricType: $('#rubricsTypeFilterSelect').val() || 'all'
        };
    }

    // Global reload function for rubrics (similar to other tabs)
    window.reloadCurrentRubricsView = function(page = 1) {
        var filters = getCurrentRubricsFilters();
        loadRubrics(page, filters.search, filters.rubricType);
    };

    // Update pagination
    function updatePagination(totalPages, currentPage) {
        var pagination = $('#rubricsPagination');
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

        for (var i = startPage; i <= endPage; i++) {
            pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
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

    // Generate quality criteria inputs (Only for Numerical)
    function generateQualityCriteriaInputs() {
        var count = parseInt($('#qualityCriteriaCount').val()) || 1;
        var container = $('#qualityCriteriaContainer');
        container.empty();

        var headerRow = $('<div class="row mb-2 align-items-center"></div>');
        headerRow.append('<div class="col-md-3"><strong>Level Name</strong></div>');
        headerRow.append('<div class="col-md-1 text-center"><strong>Range?</strong></div>');
        headerRow.append('<div class="col-md-1"><strong>Min</strong></div>');
        headerRow.append('<div class="col-md-1"><strong>Max</strong></div>');
        headerRow.append('<div class="col-md-6"><strong>Description</strong></div>');
        container.append(headerRow);

        // Add inputs for each quality level
        for (var i = 0; i < count; i++) {
            var levelRow = $('<div class="row mb-2 align-items-center quality-level-row"></div>');
            // --- MODIFIED: Calculate default points based on position ---
            // Example: If count is 5, levels get defaults 5, 4, 3, 2, 1
            // If count is 3, levels get defaults 3, 2, 1
            var defaultPoints = Math.max(1, count - i); // Ensure points are at least 1
            // --- END MODIFIED ---

            levelRow.append(`
            <div class="col-md-3">
                <input type="text" class="form-control quality-level-input"
                       name="quality_level_name[]" value="Level ${i + 1}"
                       data-level="${i + 1}" required>
            </div>
            <div class="col-md-1 text-center">
                <input type="checkbox" class="form-check-input is-range-checkbox"
                       name="quality_level_is_range[]" value="${i + 1}"
                       data-level="${i + 1}">
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control points-input points-min"
                       name="quality_level_points_min[]" value="${defaultPoints}" min="0" max="100"
                       data-level="${i + 1}" required>
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control points-input points-max"
                       name="quality_level_points_max[]" value="${defaultPoints}" min="0" max="100"
                       data-level="${i + 1}" style="display: none;"> <!-- Initially hidden, default value same as min -->
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control quality-description-input"
                       name="quality_level_description[]"
                       placeholder="Description for Level ${i + 1}"
                       data-level="${i + 1}">
            </div>
        `);

            container.append(levelRow);
        }

        // Add event listener for range checkboxes
        container.find('.is-range-checkbox').on('change', function() {
            var level = $(this).data('level');
            var maxInput = $(this).closest('.quality-level-row').find(`.points-max[data-level="${level}"]`);
            var minInput = $(this).closest('.quality-level-row').find(`.points-min[data-level="${level}"]`);
            if ($(this).is(':checked')) {
                maxInput.show();
                // Optionally ensure max >= min
                if (parseInt(maxInput.val()) < parseInt(minInput.val())) {
                    maxInput.val(minInput.val());
                }
            } else {
                maxInput.hide();
                maxInput.val(minInput.val()); // Set max to min when not a range
            }
            validateLevelPoints(); // Validate points after toggling range
        });

        // Add event listener for min points input to potentially update max when not range
        container.find('.points-min').on('input', function() {
            var level = $(this).data('level');
            var row = $(this).closest('.quality-level-row');
            var isRange = row.find(`.is-range-checkbox[data-level="${level}"]`).is(':checked');
            if (!isRange) {
                row.find(`.points-max[data-level="${level}"]`).val($(this).val());
            }
            validateLevelPoints(); // Validate points after changing min
        });
        // Add event listener for max points input to ensure max >= min
        container.find('.points-max').on('input', function() {
            var level = $(this).data('level');
            var row = $(this).closest('.quality-level-row');
            var minVal = parseInt(row.find(`.points-min[data-level="${level}"]`).val()) || 0;
            var maxVal = parseInt($(this).val()) || 0;
            if (maxVal < minVal) {
                $(this).val(minVal); // Ensure max is not less than min
            }
            validateLevelPoints(); // Validate points after changing max
        });

        // Initial validation after generating inputs
        validateLevelPoints();
    }

    // Validate point ranges for overlaps (Numerical Only)
    function validateLevelPoints() {
        var levels = [];
        var isValid = true;
        var $rows = $('#qualityCriteriaContainer .quality-level-row');

        // Collect level data
        $rows.each(function(index) {
            var $row = $(this);
            var level = index + 1; // Assuming levels are ordered 1, 2, 3...
            var isRange = $row.find(`.is-range-checkbox[data-level="${level}"]`).is(':checked');
            var minVal = parseInt($row.find(`.points-min[data-level="${level}"]`).val()) || 0;
            var maxVal = isRange ? (parseInt($row.find(`.points-max[data-level="${level}"]`).val()) || minVal) : minVal;

            // Ensure min <= max within the same level
            if (minVal > maxVal) {
                $row.find(`.points-max[data-level="${level}"]`).val(minVal);
                maxVal = minVal; // Correct maxVal for overlap check
            }

            levels.push({
                level: level,
                min: minVal,
                max: maxVal,
                $minInput: $row.find(`.points-min[data-level="${level}"]`),
                $maxInput: $row.find(`.points-max[data-level="${level}"]`)
            });
        });

        // Sort levels by min points (descending, as higher quality usually has higher points)
        levels.sort((a, b) => b.min - a.min);

        // Check for overlaps between adjacent sorted levels
        for (var i = 0; i < levels.length - 1; i++) {
            var currentLevel = levels[i];
            var nextLevel = levels[i + 1];

            // Remove previous warning styles
            currentLevel.$minInput.removeClass('is-invalid');
            currentLevel.$maxInput.removeClass('is-invalid');
            nextLevel.$minInput.removeClass('is-invalid');
            nextLevel.$maxInput.removeClass('is-invalid');

            // Check if the minimum points of the current level is less than or equal to the maximum points of the next level
            if (currentLevel.min <= nextLevel.max) {
                isValid = false;
                // Highlight the conflicting inputs
                currentLevel.$minInput.addClass('is-invalid');
                nextLevel.$maxInput.addClass('is-invalid');
                console.warn(`Overlap detected: Level ${currentLevel.level} (min: ${currentLevel.min}) overlaps with Level ${nextLevel.level} (max: ${nextLevel.max})`);
            }
        }

        if (!isValid) {
            showToast('Warning', 'Point ranges overlap between quality levels. Please adjust.', 'warning');
        }

        return isValid; // Return validation status
    }

    // Get the maximum possible points for a single criterion based on quality levels
    function getMaxPoints() {
        var maxPoints = 0;
        // Iterate through the max point inputs in the configuration
        $('#qualityCriteriaContainer .points-max').each(function() {
            // Consider only visible max inputs (for ranges) or min inputs (if not range)
            var $row = $(this).closest('.quality-level-row');
            var level = $row.find('.points-min').data('level');
            var isRange = $row.find(`.is-range-checkbox[data-level="${level}"]`).is(':checked');
            var pointsVal;

            if (isRange) {
                pointsVal = parseInt($(this).val()) || 0; // Use the max value if range
            } else {
                // If not a range, use the min value (since max is hidden/same as min)
                pointsVal = parseInt($row.find(`.points-min[data-level="${level}"]`).val()) || 0;
            }

            maxPoints = Math.max(maxPoints, pointsVal);
        });
        // Fallback if no levels defined yet
        if ($('#qualityCriteriaContainer .quality-level-row').length === 0) {
            return 100; // Default max if no levels configured
        }
        return maxPoints;
    }

    // Rebuilds the preview table body based on the current rubric type
    function rebuildPreviewTable() {
        var body = $('#rubricPreviewBody');
        var rubricType = $('#rubric_type').val();
        var individualEnabled = $('#is_individual_enabled').is(':checked');
        body.empty(); // Clear existing rows

        if (rubricType === 'passfail') {
            // Force exactly two rows for pass/fail
            addCriterionRow(); // Add row 1 (Pass)
            addCriterionRow(); // Add row 2 (Fail)
        } else {
            // For numerical and yes/no, add one default row
            addCriterionRow();
        }
        updateTotalScoreDisplay(); // Update footer visibility
    }

    // Update the rubric table header based on rubric type and individual flag
    function updateRubricTableHeader() {
        var headerRow = $('#rubricHeaderRow');
        headerRow.empty(); // Clear existing headers
        var rubricType = $('#rubric_type').val();
        var individualEnabled = $('#is_individual_enabled').is(':checked');

        // Numerical Type
        if (rubricType === 'numerical' && individualEnabled) {
            headerRow.append('<th>Criteria</th><th>Range (Min - Max)</th>');
            rebuildPreviewTable();
            return;
        }

        if (rubricType === 'numerical') {
            // Criteria column header
            headerRow.append(`<th style="width: ${individualEnabled ? '40%' : '30%'};">Criteria</th>`); // Adjust width

            // Quality Level Headers (Common for both group and individual numerical)
            var count = parseInt($('#qualityCriteriaCount').val()) || 1;
            var totalWidthAvailable = individualEnabled ? 60 : 60; // Width for quality levels
            var widthPerLevel = count > 0 ? totalWidthAvailable / count : totalWidthAvailable;

            for (var i = 0; i < count; i++) {
                var level = i + 1;
                var levelRow = $(`#qualityCriteriaContainer .quality-level-row:nth-child(${level + 1})`); // +1 to skip header row
                var levelName = levelRow.find(`input[name="quality_level_name[]"][data-level="${level}"]`).val() || `Level ${level}`;
                var isRange = levelRow.find(`input[name="quality_level_is_range[]"][data-level="${level}"]`).is(':checked');
                var pointsMin = levelRow.find(`input[name="quality_level_points_min[]"][data-level="${level}"]`).val() || 0;
                var pointsMax = levelRow.find(`input[name="quality_level_points_max[]"][data-level="${level}"]`).val() || pointsMin;

                var pointsText = "";
                if (isRange && pointsMin !== pointsMax) {
                    pointsText = `(${pointsMax}-${pointsMin} points)`;
                } else {
                    pointsText = `(${pointsMin} points)`;
                }

                headerRow.append(`<th style="width: ${widthPerLevel}%;">${levelName}<br>${pointsText}</th>`);
            }

            // Score column header (ONLY for group scoring)
            if (!individualEnabled) {
                headerRow.append('<th style="width: 10%;">Score</th>');
            }
        } else if (rubricType === 'yesno') {
            headerRow.append('<th style="width: 40%;">Criteria</th>');
            headerRow.append('<th style="width: 40%;">Description</th>');
            headerRow.append('<th style="width: 20%;">Option</th>');
        } else if (rubricType === 'passfail') {
            headerRow.append('<th style="width: 40%;">Recommendation</th>');
            headerRow.append('<th style="width: 60%;">Options</th>');
        }
        rebuildPreviewTable(); // Rebuild rows to match new header
        
        // Reinitialize sortable after rebuilding
        initializeSortable();
    }

    // Initialize sortable for rubric criteria rows using native HTML5 drag and drop
    function initializeSortable() {
        try {
            var tbody = document.getElementById('rubricPreviewBody');
            if (!tbody) {
                console.warn('rubricPreviewBody not found');
                return;
            }
            
            var draggedRow = null;
            
            // Remove existing listeners
            var rows = tbody.querySelectorAll('tr.criterion-row');
            rows.forEach(function(row) {
                row.draggable = true;
                
                row.ondragstart = function(e) {
                    draggedRow = this;
                    this.style.opacity = '0.5';
                    e.dataTransfer.effectAllowed = 'move';
                };
                
                row.ondragend = function(e) {
                    this.style.opacity = '1';
                    rows.forEach(function(r) {
                        r.classList.remove('drag-over');
                    });
                };
                
                row.ondragover = function(e) {
                    if (e.preventDefault) e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    return false;
                };
                
                row.ondragenter = function(e) {
                    this.classList.add('drag-over');
                };
                
                row.ondragleave = function(e) {
                    this.classList.remove('drag-over');
                };
                
                row.ondrop = function(e) {
                    if (e.stopPropagation) e.stopPropagation();
                    if (draggedRow !== this) {
                        var allRows = Array.from(tbody.querySelectorAll('tr.criterion-row'));
                        var draggedIndex = allRows.indexOf(draggedRow);
                        var targetIndex = allRows.indexOf(this);
                        
                        if (draggedIndex < targetIndex) {
                            this.parentNode.insertBefore(draggedRow, this.nextSibling);
                        } else {
                            this.parentNode.insertBefore(draggedRow, this);
                        }
                    }
                    return false;
                };
            });
            
            console.log('Sortable initialized for ' + rows.length + ' rows');
        } catch (error) {
            console.error('Error initializing sortable:', error);
        }
    }

    // Add a new criterion row to the rubric preview based on type
    function addCriterionRow() {
        var rubricType = $('#rubric_type').val();
        var individualEnabled = $('#is_individual_enabled').is(':checked');
        var row = $('<tr class="criterion-row"></tr>');
        var rowCount = $('#rubricPreviewBody tr').length + 1; // For unique radio names

        if (rubricType === 'numerical' && individualEnabled) {
            row.append(`
                <td>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;" title="Drag to reorder"></i>
                            <input type="text" class="form-control" name="criterion_description[]" placeholder="Enter criterion" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input criterion-is-blank"
                                   name="criterion_is_blank[]" value="1"
                                   title="Make this a blank row (section header)">
                            <label class="form-check-label small text-muted">Section Header</label>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex gap-2 score-inputs-container">
                        <div class="flex-fill">
                            <label class="form-label small mb-1">Min</label>
                            <input type="number"
                                   class="form-control form-control-sm"
                                   name="criterion_min_score[]"
                                   value="0"
                                   min="0"
                                   step="1"
                                   required>
                        </div>
                        <div class="flex-fill">
                            <label class="form-label small mb-1">Max</label>
                            <input type="number"
                                   class="form-control form-control-sm"
                                   name="criterion_score[]"
                                   value="0"
                                   min="0"
                                   step="1"
                                   required>
                        </div>
                    </div>
                </td>`);
        } else if (rubricType === 'numerical') {
            var qualityCriteriaCount = parseInt($('#qualityCriteriaCount').val()) || 1;

            // Criterion Description Column (Common for both group and individual)
            row.append(`
            <td>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;" title="Drag to reorder"></i>
                            <input type="text" class="form-control criterion-description"
                                   name="criterion_description[]" placeholder="Enter criterion description" required>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger ms-2 delete-criterion">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input criterion-is-blank"
                                   name="criterion_is_blank[]" value="1"
                                   title="Make this a blank row (section header)">
                            <label class="form-check-label small text-muted">Section Header</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center score-inputs-container">
                        <small class="text-muted" style="white-space: nowrap;">Optional Limits:</small>
                        <input type="number" class="form-control form-control-sm criterion-min-limit"
                               name="criterion_min_limit[]" placeholder="Min" min="0" step="1"
                               title="Optional minimum score for this criterion">
                        <span class="text-muted">to</span>
                        <input type="number" class="form-control form-control-sm criterion-max-limit"
                               name="criterion_max_limit[]" placeholder="Max" min="0" step="1"
                               title="Optional maximum score for this criterion">
                    </div>
                </div>
            </td>
        `);

            // Quality Level Columns
            for (var i = 0; i < qualityCriteriaCount; i++) {
                if (individualEnabled) {
                    // For individual scoring, add empty cells under quality levels
                    row.append(`<td class="text-center"></td>`); // Empty cell
                } else {
                    // For group scoring, add the text input for level description
                    row.append(`
                    <td class="text-center">
                        <input type="text" class="form-control criterion-level-input"
                               name="criterion_${rowCount}_level_value[]" placeholder="Enter Description">
                    </td>
                `);
                }
            }

            // Score Column (ONLY for group scoring)
            if (!individualEnabled) {
                var maxPoints = getMaxPoints(); // Get max possible points for a single criterion
                row.append(`
                <td>
                    <input type="number" class="form-control score-input" name="criterion_score[]" value="0" min="0" max="${maxPoints}" data-row="${rowCount}" readonly>
                </td>
            `);
            }

        } else if (rubricType === 'yesno') {
            // Yes/No layout
            row.append(`
            <td>
                 <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;" title="Drag to reorder"></i>
                        <input type="text" class="form-control criterion-input"
                               name="criterion_description[]" placeholder="Enter criterion" required>
                    </div>
                     <button type="button" class="btn btn-sm btn-danger ms-2 delete-criterion"> <!-- Added delete button -->
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `);
            row.append(`
            <td>
                <input type="text" class="form-control description-input" name="criterion_detail[]" placeholder="Enter description">
            </td>
        `);
            row.append(`
            <td class="text-center">
                <select class="form-select yesno-option" name="criterion_yesno[]">
                    <option value="Yes">Yes</option>
                    <option value="No" selected>No</option>
                </select>
            </td>
        `);
        } else if (rubricType === 'passfail') {
            // Pass/Fail layout - structure depends on row number
            var recommendationText = (rowCount === 1) ?
                ($('#passRecommendationText').val() || 'Pass Recommendation:') :
                ($('#failRecommendationText').val() || 'Fail Recommendation:');

            // Column 1: Recommendation Text (Input)
            row.append(`
            <td>
                 <input type="text" class="form-control recommendation-input"
                        name="criterion_recommendation[]" value="${recommendationText}" required>
                 <!-- No delete button for pass/fail rows -->
            </td>
        `);
            // Column 2: Container for radio buttons - populated by updatePassFailPreviewOptions
            row.append(`<td class="text-center passfail-options-cell" data-row="${rowCount}"></td>`);
        }

        $('#rubricPreviewBody').append(row);

        // If pass/fail, immediately populate the options for the new row(s)
        if (rubricType === 'passfail') {
            updatePassFailPreviewOptions();
        }

        updateTotalScoreDisplay(); // Update footer visibility and score for numerical
    }

    // Update existing rows (Only relevant for Numerical type when quality levels change)
    function updateExistingRows() {
        var rubricType = $('#rubric_type').val();
        if (rubricType !== 'numerical') return; // Only needed for numerical

        var individualEnabled = $('#is_individual_enabled').is(':checked');
        var rows = $('#rubricPreviewBody tr.criterion-row');
        var qualityCriteriaCount = parseInt($('#qualityCriteriaCount').val()) || 1;
        var maxPoints = getMaxPoints(); // Recalculate max points

        rows.each(function(rowIndex) {
            var $row = $(this);
            var rowNum = rowIndex + 1;

            // Find the criteria cell (first cell) and score cell (last cell, only if group scoring)
            var criteriaCell = $row.find('td:first-child');
            var scoreCell = individualEnabled ? null : $row.find('td:last-child');

            // Remove existing quality level cells (all cells between first and potential last)
            $row.find('td').slice(1, scoreCell ? -1 : undefined).remove();

            // Add new cells for each quality level
            var insertBeforeCell = scoreCell || null; // Insert before score cell or append if no score cell

            for (var i = 0; i < qualityCriteriaCount; i++) {
                var newCell;
                if (individualEnabled) {
                    newCell = $('<td class="text-center"></td>'); // Empty cell for individual
                } else {
                    // Cell with input for group scoring
                    newCell = $(`<td class="text-center">
                                <input type="text" class="form-control criterion-level-input"
                                       name="criterion_${rowNum}_level_value[]" placeholder="Enter description" required>
                           </td>`);
                }

                if (insertBeforeCell) {
                    newCell.insertBefore(insertBeforeCell);
                } else {
                    $row.append(newCell); // Append if scoreCell doesn't exist (individual scoring)
                }
            }

            // Update max value for score input if it exists (group scoring)
            if (scoreCell) {
                scoreCell.find('input.score-input').attr('max', maxPoints).val(0); // Reset score to 0
            }
        });

        updateTotalScoreDisplay();
    }

    // Update the total score display and visibility
    function updateTotalScoreDisplay() {
        var rubricType = $('#rubric_type').val();
        var individualEnabled = $('#is_individual_enabled').is(':checked'); // Check if individual scoring is enabled
        var footer = $('#rubricFooter');
        var totalScoreDisplay = $('#totalScoreDisplay');
        var maxTotalScoreInput = $('#maxTotalScore');

        // Show footer ONLY for numerical AND group scoring
        if (rubricType === 'numerical' && !individualEnabled) {
            var totalScore = 0;
            // Sum scores for group criteria (score inputs only exist in this case)
            $('#rubricPreviewBody tr.criterion-row').each(function() {
                totalScore += parseInt($(this).find('input.score-input').val()) || 0;
            });

            totalScoreDisplay.text(totalScore);
            maxTotalScoreInput.val(totalScore);
            footer.show();
        } else {
            // Hide footer for non-numerical types OR individual numerical scoring
            totalScoreDisplay.text(''); // Clear display
            maxTotalScoreInput.val(''); // Clear hidden input
            footer.hide();
        }
    }

    // --- MOVED Function: Generate inputs for Pass/Fail modifiers ---
    function generatePassFailModifierInputs() {
        var count = parseInt($('#passModifierCount').val());
        var container = $('#modifierInputsContainer');
        container.empty(); // Clear previous inputs

        // Regenerate Pass Modifier Inputs (now textareas)
        for (var i = 1; i <= count; i++) {
            var thresholdInput = $(`#passThresholds .pass-threshold[data-modifier="${i}"]`);
            var thresholdLabel = $(`#passThresholds .modifier-threshold[data-modifier="${i}"] label`);

            container.append(`
            <div class="row mb-2 modifier-row">
                <div class="col-md-12">
                    <label class="form-label">Pass Option ${i} Description</label> <!-- Changed Label -->
                    <textarea class="form-control modifier-text" data-modifier="${i}" rows="2" placeholder="Enter description for pass option ${i}"></textarea> <!-- Changed Placeholder, removed default value -->
                </div>
            </div>
        `);

            // Show/Hide and update label for corresponding threshold input
            if (thresholdInput.length) {
                $(`#passThresholds .modifier-threshold[data-modifier="${i}"]`).show();
                // Label update logic might need adjustment if modifier text is long
                thresholdLabel.text(`Pass Option ${i} Threshold (%)`); // Changed Label
            }
        }
        // Hide unused threshold inputs
        for (var j = count + 1; j <= 3; j++) {
            $(`#passThresholds .modifier-threshold[data-modifier="${j}"]`).hide();
        } 

        updatePassFailPreviewOptions(); // Update preview after generating inputs
    }

    // --- MOVED Function: Update radio button options in Pass/Fail preview rows ---
    function updatePassFailPreviewOptions() {
        var modifierCount = parseInt($('#passModifierCount').val());
        var failText = $('#failOptionText').val() || 'Fail Description'; // Now a description
        var modifierTexts = {};

        // Get modifier descriptions
        for (var i = 1; i <= modifierCount; i++) {
            // Use placeholder if textarea is empty
            modifierTexts[i] = $(`.modifier-text[data-modifier="${i}"]`).val() || `(Description for Pass Option ${i})`; // Changed fallback
        }

        $('#rubricPreviewBody tr.criterion-row').each(function() {
            var $row = $(this);
            var optionsCell = $row.find('.passfail-options-cell');
            if (!optionsCell.length) return; // Skip if not a pass/fail row

            var rowNum = optionsCell.data('row');
            var radioGroupName = `criterion_passfail_selection`; // Use one name for the whole rubric
            optionsCell.empty(); // Clear existing options

            if (rowNum === 1) { // Row 1: Pass Modifiers
                // Add radio buttons for pass modifiers
                for (var i = 1; i <= modifierCount; i++) {
                    optionsCell.append(`
                    <div class="form-check">
                        <input class="form-check-input passfail-radio" type="radio" name="${radioGroupName}" id="row${rowNum}_mod${i}" value="pass_${i}">
                        <label class="form-check-label text-start d-block" for="row${rowNum}_mod${i}">${modifierTexts[i]}</label> <!-- Use description directly -->
                    </div>
                `);
                }
            } else if (rowNum === 2) { // Row 2: Fail Option
                // Add radio button for fail
                optionsCell.append(`
                <div class="form-check">
                    <input class="form-check-input passfail-radio" type="radio" name="${radioGroupName}" id="row${rowNum}_fail" value="fail" checked> <!-- Default to fail -->
                    <label class="form-check-label text-start d-block" for="row${rowNum}_fail">${failText}</label> <!-- Use fail description directly -->
                </div>
            `);
            }
        });
    }

    // Function to load rubric data for editing
    function loadRubricForEdit(rubricId) {
        $.ajax({
            url: 'includes/get_rubric_details.php',
            method: 'GET',
            data: {
                id: rubricId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    var rubric = response.data.rubric;
                    var levels = response.data.levels;
                    var criteria = response.data.criteria;
                    var programs = response.data.programs || []; // Get associated programs

                    // --- Populate Basic Info ---
                    $('#rubricModalTitle').text('Edit Rubric');
                    $('#rubricId').val(rubric.id);
                    $('#name').val(rubric.name);
                    $('#description').val(rubric.description);
                    $('#rubric_type').val(rubric.rubric_type);
                    $('#defense_type').val(rubric.defense_type || ''); // Populate defense type
                    $('#rubric_description').val(rubric.rubric_description || '');

                    // --- Populate Applicable Programs ---
                    // Uncheck all first
                    $('.program-checkbox').prop('checked', false);
                    // Check programs associated with this rubric
                    programs.forEach(function(programName) {
                        $(`.program-checkbox[value="${programName}"]`).prop('checked', true);
                    });
                    // Ensure collapse state is reset if needed (optional)
                    $('#programsCollapse').removeClass('show');

                    // --- Populate Type-Specific Config ---
                    // Reset all config sections
                    $('#rubricPreviewBody').empty(); // Clear preview

                    // Show/Hide config sections based on loaded type
                    $('#numericalConfig').hide();
                    $('#passFailModifierConfig').hide();
                    $('#maxMembersConfig').hide();
                    $('#addCriterion').prop('disabled', false);

                    // Set the individual enabled checkbox state FIRST
                    var individualEnabled = rubric.rubric_type === 'numerical' && rubric.is_individual_enabled;
                    $('#is_individual_enabled').prop('checked', individualEnabled);

                    if (rubric.rubric_type === 'numerical') {
                        $('#numericalConfig').show();
                        if (individualEnabled) {
                            $('#maxMembersConfig').show();
                            $('#max_members').val(rubric.max_members || 5);
                            // --- NEW: Populate Max Score per Criterion ---
                            $('#individualConfig').show(); // Ensure the container is visible
                            $('#max_score_per_criterion').val(rubric.max_score_per_criterion || 100); // Set the value
                            // --- END NEW ---
                        }

                        // Populate Quality Levels Config
                        var levelCount = levels ? levels.length : 0;
                        $('#qualityCriteriaCount').val(levelCount || 1); // Set count input
                        generateQualityCriteriaInputs(); // Generate structure

                        // Fill generated structure with loaded data
                        if (levels) {
                            levels.forEach(function(level, index) {
                                var levelRow = $(`#qualityCriteriaContainer .quality-level-row:nth-child(${index + 2})`); // +2 to skip header
                                levelRow.find('.quality-level-input').val(level.name);
                                levelRow.find('.points-min').val(level.points_min);
                                levelRow.find('.points-max').val(level.points_max);
                                levelRow.find('.quality-description-input').val(level.description || '');
                                if (level.is_range == 1) {
                                    levelRow.find('.is-range-checkbox').prop('checked', true).trigger('change'); // Trigger change to show max input
                                } else {
                                    levelRow.find('.is-range-checkbox').prop('checked', false).trigger('change');
                                }
                            });
                        }
                        updateRubricTableHeader(); // Update header based on loaded levels AND individual flag

                        // Populate Criteria Rows
                        if (criteria && criteria.length > 0) {
                            $('#rubricPreviewBody').empty();
                            criteria.forEach(function(crit) {
                                addCriterionRow(); // Adds row structure based on individualEnabled state
                                var lastRow = $('#rubricPreviewBody tr:last-child');

                                // --- MODIFIED: Use correct selector for individual description ---
                                if (individualEnabled) {
                                    lastRow.find('input[name="criterion_description[]"]').val(crit.criterion_text);
                                    // Set the min and max scores for this individual criterion
                                    lastRow.find('input[name="criterion_min_score[]"]').val(crit.criterion_min_score || 0);
                                    lastRow.find('input[name="criterion_score[]"]').val(crit.criterion_score || 0);
                                    // Populate is_blank checkbox if it exists for individual rubrics too
                                    if (crit.is_blank && crit.is_blank == 1) {
                                        lastRow.find('.criterion-is-blank').prop('checked', true).trigger('change');
                                    }
                                } else {
                                    lastRow.find('.criterion-description').val(crit.criterion_text);
                                    // For group scoring, set the readonly score (calculated from levels)
                                    lastRow.find('input[name="criterion_score[]"]').val(crit.criterion_score || 0);
                                    
                                    // Populate optional criterion-level min/max limits if they exist
                                    if (crit.criterion_min_score !== null && crit.criterion_min_score !== undefined) {
                                        lastRow.find('input[name="criterion_min_limit[]"]').val(crit.criterion_min_score);
                                    }
                                    if (crit.max_score !== null && crit.max_score !== undefined) {
                                        lastRow.find('input[name="criterion_max_limit[]"]').val(crit.max_score);
                                    }
                                    
                                    // Populate is_blank checkbox if it exists
                                    if (crit.is_blank && crit.is_blank == 1) {
                                        lastRow.find('.criterion-is-blank').prop('checked', true).trigger('change');
                                    }
                                }
                                // --- END MODIFIED ---

                                // --- MODIFIED: Uncomment and refine level input population ---
                                if (!individualEnabled) {
                                    // Populate level inputs only if group scoring
                                    var levelInputs = lastRow.find('.criterion-level-input');
                                    try {
                                        // Assuming crit.criterion_detail stores a JSON array of level descriptions
                                        var details = JSON.parse(crit.criterion_detail || '[]');
                                        levelInputs.each(function(idx) {
                                            if (details[idx] !== undefined) { // Check if index exists
                                                $(this).val(details[idx]);
                                            }
                                        });
                                    } catch (e) {
                                        console.error("Could not parse criterion detail JSON:", crit.criterion_detail, e);
                                        // Optionally clear inputs or show an error
                                        levelInputs.val('');
                                    }
                                }
                                // --- END MODIFIED ---
                            });
                        }
                        validateLevelPoints();

                    } else if (rubric.rubric_type === 'yesno') {
                        $('#numericalConfig').hide();
                        $('#passFailModifierConfig').hide();
                        $('#addCriterion').prop('disabled', false);
                        updateRubricTableHeader(); // Rebuilds table with one row

                        // Populate Criteria Rows
                        if (criteria && criteria.length > 0) {
                            $('#rubricPreviewBody').empty(); // Clear the default row
                            criteria.forEach(function(crit) {
                                addCriterionRow(); // Add a blank row structure
                                var lastRow = $('#rubricPreviewBody tr:last-child');
                                lastRow.find('.criterion-input').val(crit.criterion_text); // Correct selector for yes/no
                                lastRow.find('.description-input').val(crit.criterion_detail || ''); // Correct selector for yes/no
                            });
                        }


                    } else if (rubric.rubric_type === 'passfail') {
                        $('#numericalConfig').hide();
                        $('#passFailModifierConfig').show();
                        $('#addCriterion').prop('disabled', true);

                        // Populate Pass/Fail Config
                        $('#passRecommendationText').val(rubric.pass_recommendation_text || 'System is accepted:');
                        $('#failRecommendationText').val(rubric.fail_recommendation_text || 'System is rejected:');
                        $('#failOptionText').val(rubric.fail_option_text || 'Rejected Description');

                        var modifierCount = levels ? levels.length : 1;
                        $('#passModifierCount').val(modifierCount);
                        generatePassFailModifierInputs(); // Generate structure (NOW CALLABLE)

                        // Fill modifier descriptions
                        if (levels) {
                            levels.forEach(function(level, index) {
                                $(`.modifier-text[data-modifier="${index + 1}"]`).val(level.description || `Modifier ${index + 1} Description`);
                            });
                        }

                        // Populate thresholds
                        $('#total_pass').val(rubric.pass_threshold_1 || 100);
                        $('#minor_revision_pass').val(rubric.pass_threshold_2 || 75);
                        $('#major_revision_pass').val(rubric.pass_threshold_3 || 65);

                        updatePassFailPreviewOptions(); // Update preview with loaded descriptions
                        // updateRubricTableHeader(); // Rebuilds the 2 fixed rows - called by generatePassFailModifierInputs -> updatePassFailPreviewOptions
                    }

                    updateTotalScoreDisplay(); // Recalculate score if numerical
                    
                    // Initialize sortable after loading criteria
                    setTimeout(function() {
                        if (typeof initializeSortable === 'function') {
                            initializeSortable();
                        }
                    }, 200);
                    
                    $('#rubricModal').modal('show');

                } else {
                    showToast('Error', response.message || 'Could not load rubric details.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error loading rubric:", xhr.responseText);
                showToast('Error', 'Failed to fetch rubric details: ' + error, 'error');
            }
        });
    }

    // Function to save rubric data (Add or Edit)
    function saveRubric() {

    // Basic required fields validation
    var form = $('#rubricForm')[0];
    var valid = true;

    // Clear any existing validation errors first
    $('#rubricForm .is-invalid').removeClass('is-invalid');
    $('#rubricForm .invalid-feedback').remove();

    // Validate text inputs using ValidationUtils for consistent error display
    var textInputs = [
        { selector: '#name', fieldName: 'Rubric Name' },
        { selector: '#description', fieldName: 'Description' },
        { selector: '#rubric_description', fieldName: 'Rubric Description' }
    ];
    
    textInputs.forEach(function(config) {
        var $input = $(config.selector);
        var value = $input.val();
        
        // Only validate required fields for emptiness
        if (!value && $input.prop('required')) {
            valid = false;
            $input.addClass('is-invalid');
            $input.after('<div class="invalid-feedback">This field is required.</div>');
            return;
        }
        
        // Validate content if present using ValidationUtils
        if (value) {
            var hasHtml = ValidationUtils.containsHTML(value);
            var hasEmoji = ValidationUtils.containsEmoji(value);
            
            if (hasHtml) {
                valid = false;
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback">HTML tags are not allowed in ${config.fieldName}.</div>`);
            } else if (hasEmoji) {
                valid = false;
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback">Emojis are not allowed in ${config.fieldName}.</div>`);
            }
        }
    });
    
    // Validate criterion inputs - ValidationUtils handles real-time validation
    // Server-side sanitization will handle final cleanup
    $('#rubricPreviewBody tr').each(function() {
        // Validation is now handled by real-time ValidationUtils
        // No need for client-side sanitization calls
    });
    
    // Quality level inputs - ValidationUtils handles real-time validation
    // No need for client-side sanitization calls

    // Pass/fail modifier text inputs - ValidationUtils handles real-time validation
    // No need for client-side sanitization calls

    // Pass/fail recommendation text inputs - ValidationUtils handles real-time validation
    // No need for client-side sanitization calls

    $(form).find('input[required], select[required], textarea[required]').each(function() {
        if (!$(this).val()) {
            valid = false;
            // Optionally add some visual cue
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    if (!valid) {
        showToast('Error', 'Please complete all required fields correctly before submitting.', 'error');
        return; // Stop the save if validation fails
    }

        // Check at least one program checkbox is checked
    if ($('#programCheckboxesContainer input[type="checkbox"]:checked').length === 0) {
        showToast('Error', 'Please select at least one applicable program.', 'error');
        return;
    }

        // --- Add validation check before saving ---
        var rubricType = $('#rubric_type').val();
        if (rubricType === 'numerical' && !validateLevelPoints()) {
            showToast('Error', 'Cannot save. Point ranges overlap between quality levels.', 'error');
            return; // Stop saving if points overlap
        }
        // --- End validation check ---

        var formData = new FormData($('#rubricForm')[0]); // Get basic form data
        var rubricType = $('#rubric_type').val();
        var rubricId = $('#rubricId').val();
        var individualEnabled = $('#is_individual_enabled').is(':checked');

        // Ensure is_individual_enabled is sent even if unchecked (value 0)
        if (rubricType === 'numerical' && !individualEnabled) {
            formData.append('is_individual_enabled', '0');
        }
        // If individual not enabled, remove max_members if it exists
        if (rubricType !== 'numerical' || !individualEnabled) {
            formData.delete('max_members');
        }

        // --- Collect Applicable Programs ---
        var selectedPrograms = [];
        $('.program-checkbox:checked').each(function() {
            selectedPrograms.push($(this).val());
        });
        formData.append('programs', JSON.stringify(selectedPrograms)); // Send as JSON string

        // --- Collect Defense Type ---
        // Already included in formData by name="defense_type"

        // --- Collect Numerical Levels ---
        var levelsData = [];
        if (rubricType === 'numerical') {
            $('#qualityCriteriaContainer .quality-level-row').each(function(index) {
                var level = index + 1;
                levelsData.push({
                    level_index: level,
                    name: $(this).find('.quality-level-input').val(),
                    description: $(this).find('.quality-description-input').val(),
                    points_min: $(this).find('.points-min').val(),
                    points_max: $(this).find('.points-max').val(),
                    is_range: $(this).find('.is-range-checkbox').is(':checked') ? 1 : 0
                });
            });
            formData.append('levels', JSON.stringify(levelsData));
        }

        // --- Collect Pass/Fail Levels (Modifiers) ---
        if (rubricType === 'passfail') {
            $('#modifierInputsContainer .modifier-row').each(function(index) {
                var level = index + 1;
                levelsData.push({
                    level_index: level,
                    name: `Pass Option ${level}`, // Internal name can still be descriptive
                    description: $(this).find('.modifier-text').val(), // Get description from textarea
                });
            });
            formData.append('levels', JSON.stringify(levelsData));
            formData.append('pass_recommendation_text', $('#passRecommendationText').val());
            formData.append('fail_recommendation_text', $('#failRecommendationText').val());
            formData.append('fail_option_text', $('#failOptionText').val());
        }


        // --- Collect Criteria Rows (Numerical & Yes/No) ---
        var criteriaData = [];
        if (rubricType === 'numerical' || rubricType === 'yesno') {
            $('#rubricPreviewBody tr.criterion-row').each(function(index) {
                var criterionText = '';
                var criterionDetail = null;
                var isIndividual = 0; // Default to Group
                var criterionScore = null; // Initialize score

                if (rubricType === 'numerical') {
                    // --- MODIFIED: Get text using name selector for consistency ---
                    criterionText = $(this).find('input[name="criterion_description[]"]').val();
                    // --- END MODIFIED ---

                    if (individualEnabled) {
                        isIndividual = 1; // Individual
                        criterionDetail = null; // No level detail saved per criterion
                        criterionScore = $(this).find('input[name="criterion_score[]"]').val(); // Get max score for individual
                        var criterionMinScore = $(this).find('input[name="criterion_min_score[]"]').val(); // Get min score for individual
                    } else {
                        isIndividual = 0; // Group scoring
                        // Collect level descriptions for group scoring
                        var levelValues = [];
                        $(this).find('.criterion-level-input').each(function() {
                            levelValues.push($(this).val());
                        });
                        criterionDetail = JSON.stringify(levelValues); // Store as JSON string
                        
                        // Get the calculated score from quality levels (readonly field)
                        var calculatedScore = $(this).find('input.score-input').val();
                        
                        // Collect optional criterion-level min/max limits for group scoring
                        var criterionMinLimit = $(this).find('input[name="criterion_min_limit[]"]').val();
                        var criterionMaxLimit = $(this).find('input[name="criterion_max_limit[]"]').val();
                        
                        // Only set custom limits if both values are provided and valid
                        if (criterionMinLimit !== '' && criterionMaxLimit !== '') {
                            var criterionMinScore = parseInt(criterionMinLimit) || null;
                            criterionScore = parseInt(criterionMaxLimit) || null; // Use custom max as criterion_score
                        } else {
                            var criterionMinScore = null; // Not set for group scoring without limits
                            criterionScore = calculatedScore; // Use calculated score from quality levels
                        }
                    }
                } else { // yesno
                    criterionText = $(this).find('.criterion-input').val();
                    criterionDetail = $(this).find('.description-input').val();
                    // criterionScore remains null for yes/no
                    var criterionMinScore = null; // Not used for yes/no
                }

                // console.log("Criterion Text: ", criterionText);
                // console.log("Criterion Detail: ", criterionDetail);
                // console.log("Criterion Score: ", criterionScore);
                // console.log("Criterion Min Score: ", criterionMinScore);
                // console.log("Is Individual: ", isIndividual);
                
                // Check if this is a blank/section header criterion
                var isBlank = $(this).find('.criterion-is-blank').is(':checked') ? 1 : 0;

                criteriaData.push({
                    order_index: index,
                    criterion_text: criterionText,
                    criterion_detail: criterionDetail,
                    criterion_score: criterionScore, // Send max score (null for yes/no)
                    criterion_min_score: criterionMinScore, // Send min score (null for yes/no and group)
                    is_individual: isIndividual,
                    is_blank: isBlank
                });
            });
            formData.append('criteria', JSON.stringify(criteriaData));
        }

        // --- Calculate max_total_score for individual rubrics ---
        if (rubricType === 'numerical' && individualEnabled) {
            var totalMaxScore = 0;
            criteriaData.forEach(function(criterion) {
                var maxScore = parseFloat(criterion.criterion_score) || 0;
                totalMaxScore += maxScore;
            });
            // Override the max_total_score in formData with calculated value
            formData.set('max_total_score', totalMaxScore);
        } else if (rubricType === 'numerical' && !individualEnabled) {
            // For group scoring, ensure max_total_score has a valid value
            var groupMaxTotal = formData.get('max_total_score');
            if (!groupMaxTotal || groupMaxTotal === '') {
                formData.set('max_total_score', 0);
            }
        }
        // --- End max_total_score calculation ---

        // Determine endpoint based on whether it's an add or edit
        var url = rubricId ? 'includes/edit_items.php' : 'includes/add_items.php';
        if (rubricId) {
            formData.set('id', rubricId);
        }
        formData.set('table', 'rubrics');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#rubricModal').modal('hide');
                    // Replace loadRubrics() with AJAX live update
                    if (typeof window.reloadCurrentRubricsView === 'function') {
                        setTimeout(function() {
                            window.reloadCurrentRubricsView(1);
                        }, 500);
                    } else {
                        loadRubrics(); // Fallback
                    }
                    showToast('Success', 'Rubric saved successfully!', 'success');
                } else {
                    showToast('Error', response.message || 'Failed to save rubric.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error saving rubric:", xhr.responseText);
                showToast('Error', 'Failed to save rubric: ' + error, 'error');
            }
        });
    }

    // Function to initiate rubric deletion
    function deleteRubric(rubricId, rubricName = 'this rubric') {
        $('#rubricToDeleteId').val(rubricId);
        $('#rubricDeleteTarget').text(rubricName);
        $('#rubricDeleteConfirmModal').modal('show');
    }

    // --- UPDATED Function: Load programs into checkboxes (Handles nested structure) ---
    function loadProgramsForCheckboxes() {
        const container = $('#programCheckboxesContainer');
        container.html('<p class="text-muted">Choose a program...</p>'); // Show loading message

        $.ajax({
            url: 'includes/get_programs.php', // Corrected path relative to the dashboard page
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                container.empty(); // Clear loading message/previous content
                if (response.success && response.data) {
                    let collegeIndex = 0;
                    for (const collegeName in response.data) {
                        if (response.data.hasOwnProperty(collegeName)) {
                            // Add HR if not the first college
                            if (collegeIndex > 0) {
                                container.append('<hr>');
                            }
                            container.append(`<h6>${collegeName}</h6>`); // Add college heading

                            const collegeData = response.data[collegeName];

                            // Check if collegeData is an array (direct programs) or object (departments)
                            if (Array.isArray(collegeData)) {
                                // --- College has programs directly ---
                                collegeData.forEach(function(program) {
                                    const checkboxId = `prog_${program.id}`;
                                    container.append(`
                                    <div class="form-check ms-3"> <!-- Indent programs slightly -->
                                        <input class="form-check-input program-checkbox" type="checkbox"
                                               value="${program.value}" id="${checkboxId}">
                                        <label class="form-check-label" for="${checkboxId}">${program.label}</label>
                                    </div>
                                `);
                                });
                            } else if (typeof collegeData === 'object' && collegeData !== null) {
                                // --- College has departments ---
                                for (const departmentName in collegeData) {
                                    if (collegeData.hasOwnProperty(departmentName)) {
                                        // Add department subheading (optional, adjust styling as needed)
                                        container.append(`<h7 class="ms-3 text-muted">${departmentName}</h7>`); // Use h7 or other styling

                                        const programs = collegeData[departmentName];
                                        programs.forEach(function(program) {
                                            const checkboxId = `prog_${program.id}`;
                                            container.append(`
                                            <div class="form-check ms-4"> <!-- Indent programs under department -->
                                                <input class="form-check-input program-checkbox" type="checkbox"
                                                       value="${program.value}" id="${checkboxId}">
                                                <label class="form-check-label" for="${checkboxId}">${program.label}</label>
                                            </div>
                                        `);
                                        });
                                    }
                                }
                            }
                            collegeIndex++;
                        }
                    }
                } else {
                    container.html(`<p class="text-danger">Error loading programs: ${response.message || 'Unknown error'}</p>`);
                }
            },
            error: function(xhr, status, error) {
                container.html('<p class="text-danger">Failed to fetch programs. Please check backend script.</p>');
                console.error("Error loading programs:", error, xhr.responseText);
            }
        });
    }
    // --- END UPDATED Function ---

    // Event Listeners
    $(document).ready(function() {
        // Unbind any existing click handlers to prevent duplication
        $('.add-btn[data-table="rubrics"]').off('click');
        $('.edit-rubric-btn').off('click');
        $('.delete-rubric-btn').off('click');
        $('#updateQualityCriteria').off('click');
        $('#addCriterion').off('click');
        $('.quality-level-input, .points-input').off('input');
        $('#saveRubric').off('click');
        $('.rubric-status').off('change');
        $('.delete-criterion').off('click');

        // Load rubrics on page load
        loadRubrics();
        // --- NEW: Load programs for checkboxes ---
        loadProgramsForCheckboxes();
        // --- END NEW ---

        // Handle search input
        $('#rubricsSearchInput').off('input').on('input', function() {
            window.reloadCurrentRubricsView(1);
        });

        // Handle rubric type filter
        $('#rubricsTypeFilterSelect').off('change').on('change', function() {
            window.reloadCurrentRubricsView(1);
        });

        // Handle pagination clicks
        $('#rubricsPagination').off('click').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            window.reloadCurrentRubricsView(page);
        });

        // Handle add rubric button click - use a namespaced event to avoid conflicts
        $(document).off('click.rubrics').on('click.rubrics', '.add-btn[data-table="rubrics"]', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Reset form
            $('#rubricForm')[0].reset();
            $('#rubricId').val('');
            $('#rubricModalTitle').text('Add New Rubric');
            $('#rubricPreviewBody').empty();
            $('.program-checkbox').prop('checked', false); // Uncheck all programs
            $('#programsCollapse').removeClass('show'); // Ensure programs are collapsed
            //$('#defense_type').val(''); // Reset defense type

            // Set default type and trigger change to set initial UI state
            $('#rubric_type').val('numerical'); // Set default
            $('#addCriterion').prop('disabled', false); // Ensure add button is enabled
            $('#numericalConfig').show();
            $('#passFailModifierConfig').hide();
            generateQualityCriteriaInputs();
            updateRubricTableHeader(); // This calls rebuildPreviewTable which adds 1 row

            // Show the modal
            $('#rubricModal').modal('show');
            return false; // Important: prevent other handlers from running
        });

        // Handle edit rubric button click - use namespaced events
        $(document).off('click.rubricEdit').on('click.rubricEdit', '.edit-rubric-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var rubricId = $(this).data('id');
            loadRubricForEdit(rubricId);
            return false; // Important: prevent other handlers from running
        });

        // Handle delete rubric button click - use namespaced events
        $(document).off('click.rubricDelete').on('click.rubricDelete', '.delete-rubric-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var rubricId = $(this).data('id');
            var rubricName = $(this).data('rubricName') || 'this rubric';
            deleteRubric(rubricId, rubricName);
            return false; // Important: prevent other handlers from running
        });

        // Handle update quality criteria button click (Numerical Only)
        $('#updateQualityCriteria').off('click').on('click', function() {
            generateQualityCriteriaInputs(); // This now calls validateLevelPoints internally
            updateRubricTableHeader();
        });

        // Handle add criterion button click (Now disabled for pass/fail)
        $('#addCriterion').off('click').on('click', function() {
            // Check type just in case it was re-enabled incorrectly
            if ($('#rubric_type').val() !== 'passfail') {
                addCriterionRow();
                // Reinitialize sortable after adding row
                setTimeout(initializeSortable, 100);
            }
        });

        // Handle quality level input changes (Numerical Only)
        $(document).off('input.rubricsQuality').on('input.rubricsQuality', '#numericalConfig .quality-level-input, #numericalConfig .points-input', function() {
            updateRubricTableHeader();
            validateLevelPoints(); // Validate points on any input change in the config section
        });

        // Handle save rubric button click
        $('#saveRubric').off('click').on('click', function() {
            saveRubric();
        });

        // Handle rubric status toggle
        $(document).off('change.rubricStatus').on('change.rubricStatus', '.rubric-status', function() {
            var $checkbox = $(this); // Reference the checkbox
            var rubricId = $checkbox.data('id');
            var isActive = $checkbox.prop('checked');
            var previousState = !$checkbox.prop('checked'); // Store the state before the change

            $.ajax({
                url: 'includes/update_rubric_status.php',
                method: 'POST',
                data: {
                    rubric_id: rubricId,
                    is_active: isActive ? 1 : 0
                },
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response.success) {
                        // Show success message instead of reloading the whole table
                        showToast('Success', 'Rubric status updated successfully.', 'success');
                        // The checkbox state remains as toggled by the user
                    } else {
                        // Revert the checkbox state if the update failed
                        $checkbox.prop('checked', previousState);
                        showToast('Error', response.message || 'Failed to update status.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // Revert the checkbox state on AJAX error
                    $checkbox.prop('checked', previousState);
                    console.error("AJAX Error updating status:", xhr.responseText);
                    showToast('Error', 'Failed to update status: ' + error, 'error');
                }
            });
        });

        // Handle section header checkbox toggle
        $(document).off('change.sectionHeader').on('change.sectionHeader', '.criterion-is-blank', function() {
            var $checkbox = $(this);
            var $row = $checkbox.closest('tr');
            var isChecked = $checkbox.is(':checked');
            var $scoreInputsContainer = $row.find('.score-inputs-container');
            var $levelInputs = $row.find('.criterion-level-input');
            
            if (isChecked) {
                // Hide score inputs and level inputs
                $scoreInputsContainer.hide();
                $levelInputs.closest('td').hide();
                // Remove required attribute from inputs when hidden
                $scoreInputsContainer.find('input').prop('required', false);
                $levelInputs.prop('required', false);
            } else {
                // Show score inputs and level inputs
                $scoreInputsContainer.show();
                $levelInputs.closest('td').show();
                // Restore required attribute
                $scoreInputsContainer.find('input').prop('required', true);
                $levelInputs.prop('required', true);
            }
        });

        // Handle delete criterion button click (Now disabled for pass/fail)
        $(document).off('click.deleteCriterion').on('click.deleteCriterion', '.delete-criterion', function() {
            // Check type just in case
            if ($('#rubric_type').val() !== 'passfail') {
                if (confirm('Are you sure you want to remove this criterion?')) {
                    $(this).closest('tr').remove();
                    updateTotalScoreDisplay();
                    // Reinitialize sortable after deletion
                    setTimeout(function() {
                        if (typeof initializeSortable === 'function') {
                            initializeSortable();
                        }
                    }, 100);
                }
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
            dataType: 'json',
            success: function(response) {
                console.log('Delete response:', response);
                if (response && response.success) {
                $('#rubricDeleteConfirmModal').modal('hide');
                // Replace location reload with AJAX live update
                if (typeof window.reloadCurrentRubricsView === 'function') {
                    setTimeout(function() {
                        window.reloadCurrentRubricsView(1);
                    }, 500);
                } else {
                    loadRubrics(); // Fallback
                }
                showToast('Success', 'Rubric deleted successfully!', 'success');
                } else {
                showToast('Error', response.message || 'Unknown error occurred', 'error');
                // Still use AJAX reload instead of location.reload()
                if (typeof window.reloadCurrentRubricsView === 'function') {
                    setTimeout(function() {
                        window.reloadCurrentRubricsView(1);
                    }, 500);
                } else {
                    loadRubrics();
                }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    showToast('Error', errorResponse.message || 'Unable to delete rubric', 'error');
                } catch(e) {
                    showToast('Error', 'Unable to delete rubric: ' + error, 'error');
                }
                // Replace location.reload() with AJAX reload
                if (typeof window.reloadCurrentRubricsView === 'function') {
                    setTimeout(function() {
                        window.reloadCurrentRubricsView(1);
                    }, 500);
                } else {
                    loadRubrics();
                }
            }
            });
        });

        // Handle Rubric Type Change - MAIN UI LOGIC
        $('#rubric_type').off('change.rubricType').on('change.rubricType', function() {
            var type = $(this).val();
            var addBtn = $('#addCriterion');

            // Hide all config sections initially
            $('#numericalConfig').hide();
            $('#passFailModifierConfig').hide();
            // Keep individual checkbox/max members hidden until numerical is confirmed
            $('#is_individual_enabled').prop('checked', false); // Reset checkbox
            $('#maxMembersConfig').hide();

            addBtn.prop('disabled', false); // Enable add button initially

            if (type === 'numerical') {
                $('#numericalConfig').show();
                // Don't show maxMembersConfig yet, depends on the checkbox
                generateQualityCriteriaInputs();
            } else if (type === 'yesno') {
                // No specific config section
            } else if (type === 'passfail') {
                $('#passFailModifierConfig').show();
                addBtn.prop('disabled', true);
                generatePassFailModifierInputs();
            }

            // Update header and rebuild table body for the new type
            updateRubricTableHeader(); // Header depends on type AND individual flag state
            updateTotalScoreDisplay();
        }).trigger('change'); // Trigger initially to set default state

        // Handle Individual Scoring Checkbox Change (within Numerical)
        $('#is_individual_enabled').off('change.individualCheck').on('change.individualCheck', function() {
            var individualEnabled = $(this).is(':checked');
            $('#qualityCriteriaControls').toggle(!individualEnabled);
            $('#individualConfig').toggle(individualEnabled);
            if (individualEnabled) {
                $('#maxMembersConfig').show();
                $('#individualConfig').show(); // Show individual-specific config
            } else {
                $('#maxMembersConfig').hide();
                $('#individualConfig').hide(); // Hide individual-specific config
            }
            // Update header and rebuild table body when this changes
            updateRubricTableHeader(); // This will now create different rows based on the flag
            updateTotalScoreDisplay(); // This will hide/show footer based on the flag
        });

        // Handle max score per criterion input change
        $('#max_score_per_criterion').off('input').on('input', function() {
            var max = $(this).val() || 0;
            $('input[name="criterion_score[]"]').attr('max', max);
        });

        // Handle Pass/Fail Modifier Count Change
        $('#passModifierCount').off('change.modifierCount').on('change.modifierCount', function() {
            generatePassFailModifierInputs(); // Regenerate inputs and update preview
        });

        // Handle changes in dynamically generated modifier textareas and recommendation inputs
        $(document).off('input.passFailConfig').on('input.passFailConfig',
            '#modifierInputsContainer .modifier-text, #failOptionText, #passRecommendationText, #failRecommendationText',
            function() {
                // Sanitize input in real-time
                var $input = $(this);
                var value = $input.val();
                var cleaned = value;
                
                // Remove HTML tags
                if (/<[^>]*>/g.test(cleaned)) {
                    cleaned = cleaned.replace(/<[^>]*>/g, '');
                }
                
                // Remove emojis
                if (/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u.test(cleaned)) {
                    cleaned = cleaned.replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '');
                }
                
                if (cleaned !== value) {
                    $input.val(cleaned);
                }
                
                // Update preview options (radio labels)
                updatePassFailPreviewOptions();
                // Update recommendation text in preview rows directly
                var passRec = $('#passRecommendationText').val();
                var failRec = $('#failRecommendationText').val();
                $('#rubricPreviewBody tr:nth-child(1) .recommendation-input').val(passRec);
                $('#rubricPreviewBody tr:nth-child(2) .recommendation-input').val(failRec);
            });

        // Real-time validation for main rubric form fields using ValidationUtils
        $(document).off('input.rubricValidation').on('input.rubricValidation',
            '#name, #description, #rubric_description',
            function() {
                var $input = $(this);
                var value = $input.val();
                var fieldNames = {
                    'name': 'Rubric Name',
                    'description': 'Description', 
                    'rubric_description': 'Rubric Description'
                };
                var fieldName = fieldNames[$input.attr('id')];
                
                // Clear previous errors
                $input.removeClass('is-invalid');
                $input.siblings('.invalid-feedback').remove();
                
                if (value) {
                    // Check for HTML and show persistent error
                    if (ValidationUtils.containsHTML(value)) {
                        $input.addClass('is-invalid');
                        $input.after(`<div class="invalid-feedback">HTML tags are not allowed in ${fieldName}.</div>`);
                    } 
                    // Check for emojis and show persistent error
                    else if (ValidationUtils.containsEmoji(value)) {
                        $input.addClass('is-invalid');
                        $input.after(`<div class="invalid-feedback">Emojis are not allowed in ${fieldName}.</div>`);
                    }
                }
            });

        // Real-time validation for quality level inputs (numerical rubrics)
        $(document).off('input.qualityValidation').on('input.qualityValidation',
            '#qualityCriteriaContainer .quality-level-input, #qualityCriteriaContainer .quality-description-input',
            function() {
                var $input = $(this);
                var value = $input.val();
                var fieldName = $input.hasClass('quality-level-input') ? 'Quality Level Name' : 'Quality Level Description';
                
                // Clear previous errors
                $input.removeClass('is-invalid');
                $input.siblings('.invalid-feedback').remove();
                
                if (value) {
                    if (ValidationUtils.containsHTML(value)) {
                        $input.addClass('is-invalid');
                        $input.after(`<div class="invalid-feedback">HTML tags are not allowed in ${fieldName}.</div>`);
                    } else if (ValidationUtils.containsEmoji(value)) {
                        $input.addClass('is-invalid');
                        $input.after(`<div class="invalid-feedback">Emojis are not allowed in ${fieldName}.</div>`);
                    }
                }
            });

        // Real-time validation for criterion inputs (all rubric types)
        $(document).off('input.criteriaValidation').on('input.criteriaValidation',
            '#rubricPreviewBody .criterion-input, #rubricPreviewBody .description-input, #rubricPreviewBody .criterion-description, #rubricPreviewBody .criterion-level-input, #rubricPreviewBody input[name="criterion_description[]"]',
            function() {
                var $input = $(this);
                var value = $input.val();
                var fieldName = 'Criterion';
                
                // Clear previous errors
                $input.removeClass('is-invalid');
                $input.siblings('.invalid-feedback').remove();
                
                if (value) {
                    if (ValidationUtils.containsHTML(value)) {
                        $input.addClass('is-invalid');
                        $input.after(`<div class="invalid-feedback">HTML tags are not allowed in ${fieldName}.</div>`);
                    } else if (ValidationUtils.containsEmoji(value)) {
                        $input.addClass('is-invalid');
                        $input.after(`<div class="invalid-feedback">Emojis are not allowed in ${fieldName}.</div>`);
                    }
                }
            });

        // Initial setup on document ready - trigger type change
        // $('#rubric_type').trigger('change.rubricType'); // Already triggered above

        // Meatball menu functionality for rubrics
        document.addEventListener('click', function(e) {
            // Handle meatball button clicks for rubrics
            if (e.target.closest('.meatball-btn[data-rubric-id]')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.meatball-btn');
                const rubricId = btn.getAttribute('data-rubric-id');
                const rubricName = btn.closest('tr')?.querySelector('td')?.textContent?.trim() || 'Unnamed rubric';
                let dropdown = document.getElementById(`rubric-dropdown-${rubricId}`);
                
                // Close all other dropdowns first
                document.querySelectorAll('.meatball-dropdown-portal[id^="rubric-dropdown-"]').forEach(dd => {
                    dd.style.display = 'none';
                });
                
                // If dropdown doesn't exist, create it
                if (!dropdown) {
                    console.log('Creating dropdown for rubric:', rubricId);
                    dropdown = document.createElement('div');
                    dropdown.className = 'meatball-dropdown-portal';
                    dropdown.id = `rubric-dropdown-${rubricId}`;
                    dropdown.style.cssText = `
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
                    dropdown.innerHTML = `
                        <button class="meatball-dropdown-item edit-rubric-btn" data-id="${rubricId}">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <button class="meatball-dropdown-item delete-rubric-btn" data-id="${rubricId}">
                            <i class="fas fa-trash-alt"></i>
                            Delete
                        </button>
                    `;
                    const deleteBtn = dropdown.querySelector('.delete-rubric-btn');
                    if (deleteBtn) {
                        deleteBtn.dataset.rubricName = rubricName;
                    }
                    document.body.appendChild(dropdown);
                }
                
                // Position and show the dropdown
                const btnRect = btn.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const dropdownWidth = 120;
                
                // Calculate position
                let left = btnRect.right - dropdownWidth;
                let top = btnRect.bottom + 5;
                
                // Adjust for mobile screens
                if (viewportWidth < 768) {
                    // On mobile, center the dropdown below the button
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
            // Close dropdown when clicking outside - only for rubric dropdowns
            else if (!e.target.closest('.meatball-dropdown-portal[id^="rubric-dropdown-"]') && !e.target.closest('.meatball-btn[data-rubric-id]')) {
                document.querySelectorAll('.meatball-dropdown-portal[id^="rubric-dropdown-"]').forEach(dd => {
                    dd.style.display = 'none';
                });
            }
        });

        // Handle meatball dropdown item clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.meatball-dropdown-item')) {
                const item = e.target.closest('.meatball-dropdown-item');
                const rubricId = item.getAttribute('data-id');
                console.log('Clicked dropdown item for rubric:', rubricId);
                
                // Close the dropdown
                const dropdown = item.closest('.meatball-dropdown-portal');
                if (dropdown) {
                    dropdown.style.display = 'none';
                    console.log('Dropdown found and hidden for rubric:', rubricId);
                } else {
                    console.log('Dropdown not found for rubric:', rubricId);
                }
                
                // The existing edit-rubric-btn and delete-rubric-btn event handlers will handle the action
                // since we've preserved the same classes on the dropdown items
            }
        });
    });
</script>