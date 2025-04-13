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
                    <th>Type</th>
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
                            <select class="form-select" id="defense_type" name="defense_type">
                                <option value="" selected>Select Defense Type (Optional)</option>
                                <option value="Title Defense">Title Defense</option>
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
                                    <!-- Architecture -->
                                    <h6>Department of Architecture</h6>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Architecture" id="progArch">
                                        <label class="form-check-label" for="progArch">Bachelor of Science in Architecture</label>
                                    </div>
                                    <hr>
                                    <!-- Computer Studies -->
                                    <h6>Department of Computer Studies</h6>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Computer Science with specialization in Data Science" id="progCSDS">
                                        <label class="form-check-label" for="progCSDS">Bachelor of Science in Computer Science with specialization in Data Science</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Computer Science with specialization in Software Engineering" id="progCSSE">
                                        <label class="form-check-label" for="progCSSE">Bachelor of Science in Computer Science with specialization in Software Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Information Technology with specialization in Network and Information Security" id="progITNIS">
                                        <label class="form-check-label" for="progITNIS">Bachelor of Science in Information Technology with specialization in Network and Information Security</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Information Technology with specialization in Web and Mobile Technology" id="progITWMT">
                                        <label class="form-check-label" for="progITWMT">Bachelor of Science in Information Technology with specialization in Web and Mobile Technology</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Library and Information Science" id="progBLIS">
                                        <label class="form-check-label" for="progBLIS">Bachelor of Library and Information Science</label>
                                    </div>
                                    <hr>
                                    <!-- Engineering -->
                                    <h6>Department of Engineering</h6>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Aeronautical Engineering" id="progAeroE">
                                        <label class="form-check-label" for="progAeroE">Bachelor of Science in Aeronautical Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management" id="progCECM">
                                        <label class="form-check-label" for="progCECM">Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Civil Engineering with specialization in Structural Engineering" id="progCESE">
                                        <label class="form-check-label" for="progCESE">Bachelor of Science in Civil Engineering with specialization in Structural Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Civil Engineering with specialization in Transportation Engineering" id="progCETE">
                                        <label class="form-check-label" for="progCETE">Bachelor of Science in Civil Engineering with specialization in Transportation Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Computer Engineering" id="progCpE">
                                        <label class="form-check-label" for="progCpE">Bachelor of Science in Computer Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Engineering Technology with a major in Construction Technology and Management" id="progBETCTM">
                                        <label class="form-check-label" for="progBETCTM">Bachelor of Engineering Technology with a major in Construction Technology and Management</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Electrical Engineering" id="progEE">
                                        <label class="form-check-label" for="progEE">Bachelor of Science in Electrical Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Electronics Engineering" id="progECE">
                                        <label class="form-check-label" for="progECE">Bachelor of Science in Electronics Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Industrial Engineering" id="progIE">
                                        <label class="form-check-label" for="progIE">Bachelor of Science in Industrial Engineering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input program-checkbox" type="checkbox" value="Bachelor of Science in Mechanical Engineering" id="progME">
                                        <label class="form-check-label" for="progME">Bachelor of Science in Mechanical Engineering</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Applicable Programs -->

                    <!-- Numerical Rubric Configuration (Hidden for other types) -->
                    <div id="numericalConfig">
                        <!-- Quality Criteria Controls -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="qualityCriteriaCount" class="form-label">Number of Quality Levels</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="qualityCriteriaCount" min="1" max="5" value="1">
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
                
                if (response.data.length === 0) {
                    tbody.html('<tr><td colspan="5">No rubrics found.</td></tr>');
                } else {
                    response.data.forEach(function(rubric) {
                        let typeName = 'Unknown';
                        switch(rubric.rubric_type) {
                            case 'numerical': typeName = 'Numerical'; break;
                            case 'yesno': typeName = 'Yes/No'; break;
                            case 'passfail': typeName = 'Pass/Fail'; break;
                        }

                        var row = `
                            <tr>
                                <td>${rubric.name || 'N/A'}</td>
                                <td>${rubric.description || 'N/A'}</td>
                                <td>${typeName}</td>
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
                }

                // Update pagination
                updatePagination(response.total_pages, page);
            } else {
                $('#rubricsTableBody').html('<tr><td colspan="5">Error loading data.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            $('#rubricsTableBody').html('<tr><td colspan="5">Error loading data. Please try again.</td></tr>');
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
        var defaultPoints = Math.max(0, 5 - i); // Example default points

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
                       data-level="${i + 1}" style="display: none;"> <!-- Initially hidden -->
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

// Rebuilds the preview table body based on the current rubric type
function rebuildPreviewTable() {
    var body = $('#rubricPreviewBody');
    var rubricType = $('#rubric_type').val();
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

// Update the rubric table header based on rubric type
function updateRubricTableHeader() {
    var headerRow = $('#rubricHeaderRow');
    headerRow.empty(); // Clear existing headers
    var rubricType = $('#rubric_type').val();

    if (rubricType === 'numerical') {
        headerRow.append('<th style="width: 30%;">Criteria</th>');
        var count = parseInt($('#qualityCriteriaCount').val()) || 1;
        var totalWidthAvailable = 60; // % width for level columns
        var widthPerLevel = count > 0 ? totalWidthAvailable / count : totalWidthAvailable;

        for (var i = 0; i < count; i++) {
            var level = i + 1;
            var levelRow = $(`#qualityCriteriaContainer .quality-level-row:nth-child(${level + 1})`); // +1 to skip header row
            var levelName = levelRow.find(`input[name="quality_level_name[]"][data-level="${level}"]`).val() || `Level ${level}`;
            var isRange = levelRow.find(`input[name="quality_level_is_range[]"][data-level="${level}"]`).is(':checked');
            var pointsMin = levelRow.find(`input[name="quality_level_points_min[]"][data-level="${level}"]`).val() || 0;
            var pointsMax = levelRow.find(`input[name="quality_level_points_max[]"][data-level="${level}"]`).val() || pointsMin; // Use min if max hidden/invalid

            var pointsText = "";
            if (isRange && pointsMin !== pointsMax) {
                // SWAPPED: Display Max first, then Min for ranges
                pointsText = `(${pointsMax}-${pointsMin} points)`;
            } else {
                pointsText = `(${pointsMin} points)`;
            }

            headerRow.append(`<th style="width: ${widthPerLevel}%;">${levelName}<br>${pointsText}</th>`);
        }
        headerRow.append('<th style="width: 10%;">Score</th>');
    } else if (rubricType === 'yesno') {
        headerRow.append('<th style="width: 40%;">Criteria</th>');
        headerRow.append('<th style="width: 40%;">Description</th>');
        headerRow.append('<th style="width: 20%;">Option</th>');
    } else if (rubricType === 'passfail') {
        headerRow.append('<th style="width: 40%;">Recommendation</th>');
        headerRow.append('<th style="width: 60%;">Options</th>');
    }
    rebuildPreviewTable(); // Rebuild rows to match new header
}

// Add a new criterion row to the rubric preview based on type
function addCriterionRow() {
    var rubricType = $('#rubric_type').val();
    var row = $('<tr class="criterion-row"></tr>');
    var rowCount = $('#rubricPreviewBody tr').length + 1; // For unique radio names

    if (rubricType === 'numerical') {
        var qualityCriteriaCount = parseInt($('#qualityCriteriaCount').val()) || 1;
        row.append(`
            <td>
                <div class="d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control criterion-description"
                           name="criterion_description[]" placeholder="Enter criterion description" required>
                    <button type="button" class="btn btn-sm btn-danger ms-2 delete-criterion">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `);
        for (var i = 0; i < qualityCriteriaCount; i++) {
            row.append(`
                <td class="text-center">
                    <input type="radio" name="criterion_${rowCount}_level" value="${i + 1}" class="criterion-level-radio"
                           data-row="${rowCount}" data-level="${i + 1}">
                </td>
            `);
        }
        var maxPoints = getMaxPoints(); // Get max possible points for a single criterion
        row.append(`
            <td>
                <input type="number" class="form-control score-input" name="criterion_score[]" value="0" min="0" max="${maxPoints}" data-row="${rowCount}" readonly>
            </td>
        `);
    } else if (rubricType === 'yesno') {
        // Yes/No layout
        row.append(`
            <td>
                 <div class="d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control criterion-input"
                           name="criterion_description[]" placeholder="Enter criterion" required>
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
        var recommendationText = (rowCount === 1)
            ? ($('#passRecommendationText').val() || 'Pass Recommendation:')
            : ($('#failRecommendationText').val() || 'Fail Recommendation:');

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

    var rows = $('#rubricPreviewBody tr.criterion-row');
    var qualityCriteriaCount = parseInt($('#qualityCriteriaCount').val()) || 1;
    var maxPoints = getMaxPoints(); // Recalculate max points

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
        scoreCell.find('input.score-input').attr('max', maxPoints).val(0); // Reset score to 0
    });

    updateTotalScoreDisplay();
}

// Get the maximum points (Only for Numerical) - Updated for ranges
function getMaxPoints() {
    var maxPoints = 0;
    // Iterate through the max point inputs in the configuration
    $('#qualityCriteriaContainer .points-max').each(function() {
        var points = parseInt($(this).val()) || 0;
        maxPoints = Math.max(maxPoints, points);
    });
    return maxPoints;
}

// Update the total score display and visibility
function updateTotalScoreDisplay() {
    var rubricType = $('#rubric_type').val();
    var footer = $('#rubricFooter');
    var totalScoreDisplay = $('#totalScoreDisplay');
    var maxTotalScoreInput = $('#maxTotalScore');

    if (rubricType === 'numerical') {
        var totalScore = 0;
        $('input.score-input').each(function() {
            totalScore += parseInt($(this).val()) || 0;
        });
        totalScoreDisplay.text(totalScore);
        maxTotalScoreInput.val(totalScore);
        footer.show(); // Show footer for numerical
    } else {
        totalScoreDisplay.text(''); // Clear display
        maxTotalScoreInput.val(''); // Clear hidden input
        footer.hide(); // Hide footer for non-numerical
    }
}

// Generate inputs for Pass/Fail modifiers
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
                    <label class="form-label">Pass Modifier ${i} Description</label>
                    <textarea class="form-control modifier-text" data-modifier="${i}" rows="2">Modifier ${i} Description</textarea>
                </div>
            </div>
        `);

        // Show/Hide and update label for corresponding threshold input
        if (thresholdInput.length) {
             $(`#passThresholds .modifier-threshold[data-modifier="${i}"]`).show();
             // Label update logic might need adjustment if modifier text is long
             thresholdLabel.text(`Pass Modifier ${i} (%)`);
        }
    }
     // Hide unused threshold inputs
    for (var j = count + 1; j <= 3; j++) {
        $(`#passThresholds .modifier-threshold[data-modifier="${j}"]`).hide();
    }

    updatePassFailPreviewOptions(); // Update preview after generating inputs
}

// Update radio button options in Pass/Fail preview rows
function updatePassFailPreviewOptions() {
    var modifierCount = parseInt($('#passModifierCount').val());
    var failText = $('#failOptionText').val() || 'Fail Description'; // Now a description
    var modifierTexts = {};

    // Get modifier descriptions
    for (var i = 1; i <= modifierCount; i++) {
        modifierTexts[i] = $(`.modifier-text[data-modifier="${i}"]`).val() || `Pass Option ${i} Description`;
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
                        <label class="form-check-label text-start d-block" for="row${rowNum}_mod${i}">${modifierTexts[i]}</label>
                    </div>
                `);
            }
        } else if (rowNum === 2) { // Row 2: Fail Option
            // Add radio button for fail
            optionsCell.append(`
                <div class="form-check">
                    <input class="form-check-input passfail-radio" type="radio" name="${radioGroupName}" id="row${rowNum}_fail" value="fail" checked> <!-- Default to fail -->
                    <label class="form-check-label text-start d-block" for="row${rowNum}_fail">${failText}</label>
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
        data: { id: rubricId },
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
                $('#rubricPreviewBody').empty(); // Clear preview

                if (rubric.rubric_type === 'numerical') {
                    $('#numericalConfig').show();
                    $('#passFailModifierConfig').hide();
                    $('#addCriterion').prop('disabled', false);

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
                    updateRubricTableHeader(); // Update header based on loaded levels

                    // Populate Criteria Rows (rebuildPreviewTable adds one, so remove it first if criteria exist)
                    if (criteria && criteria.length > 0) {
                         $('#rubricPreviewBody').empty(); // Clear the default row added by rebuild
                         criteria.forEach(function(crit) {
                            addCriterionRow(); // Add a blank row structure
                            var lastRow = $('#rubricPreviewBody tr:last-child');
                            lastRow.find('.criterion-description').val(crit.criterion_text);
                         });
                    }
                    validateLevelPoints(); // Validate points after loading

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
                            lastRow.find('.criterion-input').val(crit.criterion_text);
                            lastRow.find('.description-input').val(crit.criterion_detail || '');
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
                    generatePassFailModifierInputs(); // Generate structure

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

                    updateRubricTableHeader(); // Rebuilds the 2 fixed rows
                }

                updateTotalScoreDisplay(); // Recalculate score if numerical
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
                name: `Modifier ${level}`,
                description: $(this).find('.modifier-text').val(),
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
            if (rubricType === 'numerical') {
                criterionText = $(this).find('.criterion-description').val();
            } else { // yesno
                criterionText = $(this).find('.criterion-input').val();
                criterionDetail = $(this).find('.description-input').val();
            }
            criteriaData.push({
                order_index: index,
                criterion_text: criterionText,
                criterion_detail: criterionDetail // Will be null for numerical
            });
        });
         formData.append('criteria', JSON.stringify(criteriaData));
    }

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
                loadRubrics();
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
function deleteRubric(rubricId) {
    $('#rubricToDeleteId').val(rubricId);
    $('#rubricDeleteConfirmModal').modal('show');
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
        
        // Reset form
        $('#rubricForm')[0].reset();
        $('#rubricId').val('');
        $('#rubricModalTitle').text('Add New Rubric');
        $('#rubricPreviewBody').empty();
        $('.program-checkbox').prop('checked', false); // Uncheck all programs
        $('#programsCollapse').removeClass('show'); // Ensure programs are collapsed
        $('#defense_type').val(''); // Reset defense type
        
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
        deleteRubric(rubricId);
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
        }
    });

    // Handle quality level input changes (Numerical Only)
    $(document).off('input.rubricsQuality').on('input.rubricsQuality', '#numericalConfig .quality-level-input, #numericalConfig .points-input', function() {
        updateRubricTableHeader();
        validateLevelPoints(); // Validate points on any input change in the config section
    });

    // Handle criterion level radio button changes (Numerical Only) - REVISED LOGIC
    $(document).off('change.rubricsLevel').on('change.rubricsLevel', '.criterion-level-radio', function() {
        var rowNum = $(this).data('row'); // Changed from 'row' to 'rowNum' for clarity
        var level = $(this).data('level');
        var points = 0;

        // Find the corresponding quality level configuration row
        var levelConfigRow = $(`#qualityCriteriaContainer .quality-level-row:nth-child(${level + 1})`); // +1 to skip header row
        if (levelConfigRow.length) {
            var isRange = levelConfigRow.find(`.is-range-checkbox[data-level="${level}"]`).is(':checked');

            if (isRange) {
                // If it's a range, use the MAX points
                points = parseInt(levelConfigRow.find(`.points-max[data-level="${level}"]`).val()) || 0;
            } else {
                // If it's NOT a range (single point), use the MIN points
                points = parseInt(levelConfigRow.find(`.points-min[data-level="${level}"]`).val()) || 0;
            }
        }

        // Update the score input for the specific row
        $(`input.score-input[data-row="${rowNum}"]`).val(points);
        updateTotalScoreDisplay(); // Update the total score display
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

    // Handle delete criterion button click (Now disabled for pass/fail)
    $(document).off('click.deleteCriterion').on('click.deleteCriterion', '.delete-criterion', function() {
        // Check type just in case
        if ($('#rubric_type').val() !== 'passfail') {
            if (confirm('Are you sure you want to remove this criterion?')) {
                $(this).closest('tr').remove();
                updateTotalScoreDisplay();
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
            success: function(response) {
                response = JSON.parse(response);
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

    // Handle Rubric Type Change - MAIN UI LOGIC
    $('#rubric_type').off('change.rubricType').on('change.rubricType', function() {
        var type = $(this).val();
        var addBtn = $('#addCriterion');

        // Toggle sections visibility and Add button state
        if (type === 'numerical') {
            $('#numericalConfig').show();
            $('#passFailModifierConfig').hide();
            addBtn.prop('disabled', false); // Enable add button
            generateQualityCriteriaInputs(); // Calls validateLevelPoints internally
        } else if (type === 'yesno') {
            $('#numericalConfig').hide();
            $('#passFailModifierConfig').hide();
            addBtn.prop('disabled', false); // Enable add button
        } else if (type === 'passfail') {
            $('#numericalConfig').hide();
            $('#passFailModifierConfig').show();
            addBtn.prop('disabled', true); // Disable add button
            generatePassFailModifierInputs();
        }

        // Update header and rebuild table body for the new type
        updateRubricTableHeader(); // This now calls rebuildPreviewTable
        updateTotalScoreDisplay(); // Update footer visibility
    });

    // Handle Pass/Fail Modifier Count Change
    $('#passModifierCount').off('change.modifierCount').on('change.modifierCount', function() {
        generatePassFailModifierInputs(); // Regenerate inputs and update preview
    });

     // Handle changes in dynamically generated modifier textareas and recommendation inputs
    $(document).off('input.passFailConfig').on('input.passFailConfig',
        '#modifierInputsContainer .modifier-text, #failOptionText, #passRecommendationText, #failRecommendationText',
        function() {
        // Update preview options (radio labels)
        updatePassFailPreviewOptions();
        // Update recommendation text in preview rows directly
        var passRec = $('#passRecommendationText').val();
        var failRec = $('#failRecommendationText').val();
        $('#rubricPreviewBody tr:nth-child(1) .recommendation-input').val(passRec);
        $('#rubricPreviewBody tr:nth-child(2) .recommendation-input').val(failRec);
    });

    // Initial setup on document ready
    $('#rubric_type').trigger('change.rubricType'); // Trigger change to set initial state
});
</script>
