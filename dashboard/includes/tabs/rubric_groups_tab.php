<?php
// This file requires backend scripts to be created for full functionality:
// - get_rubric_groups.php
// - get_available_rubrics.php
// - get_rubric_group_details.php
// - save_rubric_group.php
// - delete_rubric_group.php
?>
<!-- Rubric Groups Tab -->
<div class="tab-pane fade" id="rubric-groups" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center my-3">
        <h4>Rubric Groups Management</h4>
        <button class="btn btn-primary" id="addRubricGroupBtn">
            <i class="fas fa-plus"></i> Add New Group
        </button>
    </div>

    <!-- Rubric Groups Table -->
    <div class="table-responsive">
        <table class="table table-hover db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Rubric Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rubricGroupsTableBody">
                <!-- Data will be loaded dynamically via JS -->
                <tr><td colspan="4">Loading groups...</td></tr>
            </tbody>
        </table>
    </div>
    <!-- No pagination for now, assuming fewer groups -->
</div>

<!-- Add/Edit Rubric Group Modal -->
<div class="modal fade" id="rubricGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rubricGroupModalTitle">Add New Rubric Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rubricGroupForm">
                    <input type="hidden" name="group_id" id="groupId">
                    <div class="mb-3">
                        <label for="groupName" class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="groupName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="groupDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="groupDescription" name="description" rows="2"></textarea>
                    </div>

                    <hr>
                    <h6>Rubrics in this Group</h6>
                    <div class="mb-3">
                        <label for="availableRubrics" class="form-label">Add Rubric</label>
                        <div class="input-group">
                            <select class="form-select" id="availableRubrics">
                                <option value="">Select a rubric to add...</option>
                                <!-- Available rubrics loaded via JS -->
                            </select>
                            <button class="btn btn-outline-secondary" type="button" id="addSelectedRubricToGroupBtn">Add</button>
                        </div>
                    </div>

                    <ul id="selectedRubricsList" class="list-group mb-3">
                        <!-- Selected rubrics will be added here via JS -->
                        <li class="list-group-item text-muted">No rubrics added yet.</li>
                    </ul>
                     <div class="alert alert-info small">
                        Drag and drop rubrics to reorder. Enter weight (%) for numerical rubrics. Total weight should ideally be 100%.
                    </div>
                    <div id="totalWeightWarning" class="alert alert-warning small" style="display: none;">
                        Total weight does not equal 100%.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRubricGroupBtn">Save Group</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Reuse or create specific one) -->
<div class="modal fade" id="rubricGroupDeleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this rubric group? This cannot be undone.</p>
                <input type="hidden" id="groupToDeleteId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRubricGroupDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<script>
$(document).ready(function() {
    const availableRubricsSelect = $('#availableRubrics');
    const selectedRubricsList = $('#selectedRubricsList');
    const rubricGroupModal = new bootstrap.Modal(document.getElementById('rubricGroupModal'));
    const rubricGroupDeleteModal = new bootstrap.Modal(document.getElementById('rubricGroupDeleteConfirmModal'));
    let allRubricsData = []; // Store all available rubrics {id, name, type}

    // --- Initialize SortableJS ---
    let sortable = Sortable.create(selectedRubricsList[0], {
        animation: 150,
        handle: '.drag-handle', // Use a specific handle for dragging
        onEnd: function() {
            updateTotalWeight(); // Recalculate weight on reorder
        }
    });

    // --- Load Initial Data ---
    function loadRubricGroups() {
        // TODO: Replace with actual AJAX call to get_rubric_groups.php
        console.log("Loading rubric groups...");
        $.ajax({
            url: 'includes/tabs/get_rubric_groups.php', // Replace with actual endpoint
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const tbody = $('#rubricGroupsTableBody');
                tbody.empty();
                if (response && response.success && response.data.length > 0) {
                    response.data.forEach(group => {
                        tbody.append(`
                            <tr>
                                <td>${group.name || 'N/A'}</td>
                                <td>${group.description || 'N/A'}</td>
                                <td>${group.rubric_count || 0}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-group-btn" data-id="${group.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-group-btn" data-id="${group.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.html('<tr><td colspan="4">No rubric groups found.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#rubricGroupsTableBody').html('<tr><td colspan="4">Error loading groups. Please create backend script.</td></tr>');
                console.error("Error loading rubric groups:", error);
            }
        });
    }

    function loadAvailableRubrics() {
         // TODO: Replace with actual AJAX call to get_available_rubrics.php
        console.log("Loading available rubrics...");
         $.ajax({
            url: 'includes/get_available_rubrics.php', // Replace with actual endpoint
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                availableRubricsSelect.empty().append('<option value="">Select a rubric to add...</option>');
                allRubricsData = []; // Reset
                if (response && response.success && response.data.length > 0) {
                     allRubricsData = response.data; // Store {id, name, type}
                     // Populate dropdown initially (will be filtered when editing)
                     populateAvailableRubricsDropdown();
                } else {
                     console.warn("No available rubrics found or error loading them.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading available rubrics:", error);
                availableRubricsSelect.empty().append('<option value="">Error loading rubrics</option>');
            }
        });
    }

    // Populates the dropdown, excluding already selected rubrics
    function populateAvailableRubricsDropdown() {
        const selectedIds = getSelectedRubricIds();
        availableRubricsSelect.empty().append('<option value="">Select a rubric to add...</option>');
        allRubricsData.forEach(rubric => {
            if (!selectedIds.includes(rubric.id.toString())) { // Compare as strings
                 availableRubricsSelect.append(`<option value="${rubric.id}" data-type="${rubric.rubric_type}">${rubric.name} (${rubric.rubric_type})</option>`);
            }
        });
    }

    // --- Modal Handling ---
    function resetGroupModal() {
        $('#rubricGroupForm')[0].reset();
        $('#groupId').val('');
        $('#rubricGroupModalTitle').text('Add New Rubric Group');
        selectedRubricsList.empty().html('<li class="list-group-item text-muted">No rubrics added yet.</li>');
        $('#totalWeightWarning').hide();
        populateAvailableRubricsDropdown(); // Repopulate with all available
    }

    $('#addRubricGroupBtn').on('click', function() {
        resetGroupModal();
        rubricGroupModal.show();
    });

    // --- Add/Remove Rubrics within Modal ---
     $('#addSelectedRubricToGroupBtn').on('click', function() {
        const selectedOption = availableRubricsSelect.find('option:selected');
        const rubricId = selectedOption.val();
        const rubricName = selectedOption.text().split(' (')[0]; // Get name part
        const rubricType = selectedOption.data('type');

        if (rubricId) {
            // Remove placeholder if it exists
            if (selectedRubricsList.find('.text-muted').length > 0) {
                selectedRubricsList.empty();
            }

            // Check if already added
            if (selectedRubricsList.find(`[data-rubric-id="${rubricId}"]`).length > 0) {
                showToast('Info', 'Rubric already added to this group.', 'warning');
                return;
            }

            // Add to list
            const isNumerical = rubricType === 'numerical';
            selectedRubricsList.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center" data-rubric-id="${rubricId}" data-rubric-type="${rubricType}">
                    <span>
                        <i class="fas fa-grip-vertical drag-handle me-2" style="cursor: grab;"></i>
                        ${rubricName} <small class="text-muted">(${rubricType})</small>
                    </span>
                    <div class="d-flex align-items-center">
                        ${isNumerical ? `
                        <div class="input-group input-group-sm me-2" style="width: 100px;">
                            <input type="number" class="form-control rubric-weight" placeholder="Wt." min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        ` : '<span class="me-2" style="width: 100px;"></span>' // Placeholder for alignment
                        }
                        <button type="button" class="btn btn-sm btn-danger remove-rubric-from-group-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </li>
            `);

            // Remove from available dropdown and refresh it
            populateAvailableRubricsDropdown();
            updateTotalWeight();
        }
    });

    selectedRubricsList.on('click', '.remove-rubric-from-group-btn', function() {
        $(this).closest('li').remove();
        // Add back to available dropdown and refresh
        populateAvailableRubricsDropdown();
        // Add placeholder if list becomes empty
        if (selectedRubricsList.children().length === 0) {
            selectedRubricsList.html('<li class="list-group-item text-muted">No rubrics added yet.</li>');
        }
        updateTotalWeight();
    });

    // --- Weight Calculation ---
    selectedRubricsList.on('input', '.rubric-weight', function() {
        updateTotalWeight();
    });

    function updateTotalWeight() {
        let totalWeight = 0;
        let hasNumerical = false;
        selectedRubricsList.find('.rubric-weight').each(function() {
            hasNumerical = true;
            totalWeight += parseFloat($(this).val()) || 0;
        });

        const warningDiv = $('#totalWeightWarning');
        // Show warning only if there are numerical rubrics and weight is not 100
        if (hasNumerical && Math.abs(totalWeight - 100) > 0.001) {
             warningDiv.text(`Total weight is ${totalWeight.toFixed(2)}%. It should ideally be 100%.`).show();
        } else {
            warningDiv.hide();
        }
        return totalWeight;
    }

    function getSelectedRubricIds() {
        return selectedRubricsList.find('li[data-rubric-id]').map(function() {
            return $(this).data('rubric-id').toString();
        }).get();
    }


    // --- Save Group ---
    $('#saveRubricGroupBtn').on('click', function() {
        const groupId = $('#groupId').val();
        const groupName = $('#groupName').val().trim();
        const groupDescription = $('#groupDescription').val().trim();
        let rubrics = [];

        if (!groupName) {
            showToast('Error', 'Group Name is required.', 'error');
            return;
        }

        selectedRubricsList.find('li[data-rubric-id]').each(function(index) {
            const rubricId = $(this).data('rubric-id');
            const rubricType = $(this).data('rubric-type');
            let weight = null;
            if (rubricType === 'numerical') {
                weight = parseFloat($(this).find('.rubric-weight').val()) || null;
                 // Ensure weight is null if not a valid number or zero
                if (weight !== null && (isNaN(weight) || weight < 0)) {
                    weight = null;
                }
            }
            rubrics.push({
                rubric_id: rubricId,
                order_index: index,
                weight: weight
            });
        });

        if (rubrics.length === 0) {
             showToast('Warning', 'Add at least one rubric to the group.', 'warning');
             return;
        }

        // Check total weight if numerical rubrics exist
        let hasNumerical = rubrics.some(r => r.weight !== null);
        if (hasNumerical) {
            let totalWeight = rubrics.reduce((sum, r) => sum + (r.weight || 0), 0);
             if (Math.abs(totalWeight - 100) > 0.001) {
                 if (!confirm(`Total weight is ${totalWeight.toFixed(2)}%. Are you sure you want to save?`)) {
                     return;
                 }
             }
        }


        const payload = {
            id: groupId || null,
            name: groupName,
            description: groupDescription,
            rubrics: JSON.stringify(rubrics) // Send rubrics as JSON string
        };

        console.log("Saving rubric group:", payload);

        // TODO: Replace with actual AJAX call to save_rubric_group.php
        $.ajax({
            url: 'includes/save_rubric_group.php', // Replace with actual endpoint
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    showToast('Success', 'Rubric group saved successfully!', 'success');
                    rubricGroupModal.hide();
                    loadRubricGroups(); // Refresh the list
                } else {
                    showToast('Error', response.message || 'Failed to save rubric group.', 'error');
                }
            },
            error: function(xhr, status, error) {
                 showToast('Error', 'Error saving group. Please create backend script.', 'error');
                 console.error("Error saving rubric group:", error);
            }
        });
    });

    // --- Edit Group ---
     $(document).on('click', '.edit-group-btn', function() {
        const groupId = $(this).data('id');
        console.log("Editing group ID:", groupId);
        resetGroupModal(); // Reset form first

        // TODO: Replace with actual AJAX call to get_rubric_group_details.php?id=groupId
         $.ajax({
            url: 'includes/get_rubric_group_details.php', // Replace with actual endpoint
            method: 'GET',
            data: { id: groupId },
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.data) {
                    const group = response.data;
                    $('#rubricGroupModalTitle').text('Edit Rubric Group');
                    $('#groupId').val(group.id);
                    $('#groupName').val(group.name);
                    $('#groupDescription').val(group.description || '');

                    selectedRubricsList.empty(); // Clear placeholder/existing
                    if (group.rubrics && group.rubrics.length > 0) {
                        group.rubrics.forEach(item => {
                             const isNumerical = item.rubric_type === 'numerical';
                             selectedRubricsList.append(`
                                <li class="list-group-item d-flex justify-content-between align-items-center" data-rubric-id="${item.rubric_id}" data-rubric-type="${item.rubric_type}">
                                    <span>
                                        <i class="fas fa-grip-vertical drag-handle me-2" style="cursor: grab;"></i>
                                        ${item.rubric_name} <small class="text-muted">(${item.rubric_type})</small>
                                    </span>
                                    <div class="d-flex align-items-center">
                                        ${isNumerical ? `
                                        <div class="input-group input-group-sm me-2" style="width: 100px;">
                                            <input type="number" class="form-control rubric-weight" placeholder="Wt." min="0" max="100" step="0.01" value="${item.weight || ''}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        ` : '<span class="me-2" style="width: 100px;"></span>'}
                                        <button type="button" class="btn btn-sm btn-danger remove-rubric-from-group-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </li>
                            `);
                        });
                    } else {
                         selectedRubricsList.html('<li class="list-group-item text-muted">No rubrics added yet.</li>');
                    }

                    populateAvailableRubricsDropdown(); // Filter dropdown based on newly loaded selection
                    updateTotalWeight();
                    rubricGroupModal.show();
                } else {
                     showToast('Error', response.message || 'Could not load group details.', 'error');
                }
            },
            error: function(xhr, status, error) {
                 showToast('Error', 'Error loading group details. Please create backend script.', 'error');
                 console.error("Error loading group details:", error);
            }
        });
    });

    // --- Delete Group ---
    $(document).on('click', '.delete-group-btn', function() {
        const groupId = $(this).data('id');
        $('#groupToDeleteId').val(groupId);
        rubricGroupDeleteModal.show();
    });

    $('#confirmRubricGroupDeleteBtn').on('click', function() {
        const groupId = $('#groupToDeleteId').val();
        console.log("Deleting group ID:", groupId);

        // TODO: Replace with actual AJAX call to delete_rubric_group.php
         $.ajax({
            url: 'includes/delete_rubric_group.php', // Replace with actual endpoint
            method: 'POST',
            data: { id: groupId },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    showToast('Success', 'Rubric group deleted successfully.', 'success');
                    rubricGroupDeleteModal.hide();
                    loadRubricGroups(); // Refresh list
                } else {
                    showToast('Error', response.message || 'Failed to delete rubric group.', 'error');
                }
            },
            error: function(xhr, status, error) {
                 showToast('Error', 'Error deleting group. Please create backend script.', 'error');
                 console.error("Error deleting rubric group:", error);
            }
        });
    });


    // --- Initial Load ---
    loadRubricGroups();
    loadAvailableRubrics();
});
</script>

<style>
    .drag-handle {
        cursor: grab;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    /* Style for SortableJS dragging */
    .sortable-ghost {
        opacity: 0.4;
        background-color: #eee;
    }
</style>
