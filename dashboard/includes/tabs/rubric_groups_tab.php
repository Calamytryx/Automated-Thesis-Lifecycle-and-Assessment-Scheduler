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
    <div class="container-fluid py-4 content-container" id="rubric-groups-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2">Rubric Groups Management</h3>
                <p class="text-muted">Organize rubrics into groups for streamlined assessment management and evaluation workflows.</p>
            </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="row mb-3">
            <div class="col">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button class="btn feature-btn rubric-group-add-btn" id="addRubricGroupBtn">
                        <i class="fas fa-plus"></i> Add New Group
                    </button>
                </div>
            </div>
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
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
                </div>
                <h4 class="fw-bold mb-3">Confirm Deletion</h4>
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

    // Client-side sanitization helper function - mirrors server-side sanitize_html_input()
    function sanitizeInput($input) {
        var value = $input.val();
        var originalValue = value;
        
        if (!value) return;
        
        // Remove HTML tags
        value = value.replace(/<[^>]*>/g, '');
        
        // Remove emojis using comprehensive pattern matching server-side
        var emojiPattern = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|[\u{1F900}-\u{1F9FF}]|[\u{1F018}-\u{1F270}]|[\u{238C}-\u{2454}]|[\u{20D0}-\u{20FF}]|[\u{FE00}-\u{FE0F}]|[\u{1F170}-\u{1F251}]|[\u{1F004}]|[\u{1F0CF}]|[\u{1F18E}]|[\u{3030}]|[\u{2B50}]|[\u{2B55}]|[\u{2934}-\u{2935}]|[\u{2B05}-\u{2B07}]|[\u{2B1B}-\u{2B1C}]|[\u{3297}]|[\u{3299}]|[\u{303D}]|[\u{00A9}]|[\u{00AE}]|[\u{2122}]|[\u{23E9}-\u{23FA}]|[\u{25AA}-\u{25AB}]|[\u{25B6}]|[\u{25C0}]|[\u{25FB}-\u{25FE}]|[\u{2600}-\u{2604}]|[\u{260E}]|[\u{2611}]|[\u{2614}-\u{2615}]|[\u{2618}]|[\u{261D}]|[\u{2620}]|[\u{2622}-\u{2623}]|[\u{2626}]|[\u{262A}]|[\u{262E}-\u{262F}]|[\u{2638}-\u{263A}]|[\u{2640}]|[\u{2642}]|[\u{2648}-\u{2653}]|[\u{2660}]|[\u{2663}]|[\u{2665}-\u{2666}]|[\u{2668}]|[\u{267B}]|[\u{267E}-\u{267F}]|[\u{2692}-\u{2697}]|[\u{2699}]|[\u{269B}-\u{269C}]|[\u{26A0}-\u{26A1}]|[\u{26AA}-\u{26AB}]|[\u{26B0}-\u{26B1}]|[\u{26BD}-\u{26BE}]|[\u{26C4}-\u{26C5}]|[\u{26C8}]|[\u{26CE}-\u{26CF}]|[\u{26D1}]|[\u{26D3}-\u{26D4}]|[\u{26E9}-\u{26EA}]|[\u{26F0}-\u{26F5}]|[\u{26F7}-\u{26FA}]|[\u{26FD}]|[\u{2702}]|[\u{2705}]|[\u{2708}-\u{270D}]|[\u{270F}]|[\u{2712}]|[\u{2714}]|[\u{2716}]|[\u{271D}]|[\u{2721}]|[\u{2728}]|[\u{2733}-\u{2734}]|[\u{2744}]|[\u{2747}]|[\u{274C}]|[\u{274E}]|[\u{2753}-\u{2755}]|[\u{2757}]|[\u{2763}-\u{2764}]|[\u{2795}-\u{2797}]|[\u{27A1}]|[\u{27B0}]|[\u{27BF}]|[\u{2934}-\u{2935}]|[\u{200D}]|[\u{1F000}-\u{1F02F}]|[\u{1F0A0}-\u{1F0FF}]|[\u{1F100}-\u{1F64F}]|[\u{1F680}-\u{1F6FF}]|[\u{1F700}-\u{1F77F}]|[\u{1F780}-\u{1F7FF}]|[\u{1F800}-\u{1F8FF}]|[\u{1F900}-\u{1F9FF}]|[\u{1FA00}-\u{1FA6F}]|[\u{1FA70}-\u{1FAFF}]/gu;
        value = value.replace(emojiPattern, '');
        
        // Remove any remaining non-printable characters except newlines and tabs
        value = value.replace(/[^\P{C}\n\r\t]/gu, '');
        
        // Trim whitespace
        value = value.trim();
        
        // Update the input if value changed
        if (value !== originalValue) {
            $input.val(value);
            if (originalValue.length > 0 && value.length === 0) {
                showToast('Warning', 'Input contained only invalid characters and has been cleared.', 'warning');
            } else {
                showToast('Warning', 'Invalid characters (HTML tags, emojis) have been removed.', 'warning');
            }
        }
    }

    // Real-time sanitization for rubric group form fields
    $(document).off('input.rubricGroupSanitize').on('input.rubricGroupSanitize',
        '#groupName, #groupDescription',
        function() {
            sanitizeInput($(this));
        });

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
        // Clear any existing dropdowns to prevent duplicates
        document.querySelectorAll('.meatball-dropdown-portal[id^="rubric-group-dropdown-"]').forEach(portal => portal.remove());
        
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
                                <td class="action-buttons text-center">
                                    <button class="meatball-btn" data-group-id="${group.id}" aria-label="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
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
            url: 'includes/get_available_rubrics.php', // Corrected URL
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
                     // Optionally display the message from the response
                     if (response && response.message) {
                         console.warn(response.message);
                     }
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading available rubrics:", error, xhr.responseText); // Log responseText
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
            // Remove placeholder LI specifically if it exists
            selectedRubricsList.find('li.text-muted').remove(); // More specific removal

            // Check if already added (using the data attribute)
            if (selectedRubricsList.find(`li[data-rubric-id="${rubricId}"]`).length > 0) {
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
        let rubrics = [];
        let invalidRubricFound = false; // Flag for validation

        // Client-side sanitization before validation
        sanitizeInput($('#groupName'));
        sanitizeInput($('#groupDescription'));

        // Get values after sanitization
        const groupName = $('#groupName').val().trim();
        const groupDescription = $('#groupDescription').val().trim();

        if (!groupName) {
            showToast('Error', 'Group Name is required.', 'error');
            return;
        }

        // --- Client-side Validation: Check if selected rubrics still exist ---
        const validRubricIds = allRubricsData.map(r => r.id.toString()); // Get currently known valid IDs
        // --- End Validation ---

        selectedRubricsList.find('li[data-rubric-id]').each(function(index) {
            const rubricId = $(this).data('rubric-id').toString(); // Ensure string for comparison
            const rubricType = $(this).data('rubric-type');
            const rubricName = $(this).find('span:first').contents().filter(function() { return this.nodeType === 3; }).text().trim(); // Get rubric name from text node

            // --- Client-side Validation: Check ID existence ---
            if (!validRubricIds.includes(rubricId)) {
                showToast('Error', `Rubric "${rubricName}" (ID: ${rubricId}) no longer exists or is inactive. Please remove it before saving.`, 'error');
                invalidRubricFound = true;
                return false; // Stop .each loop
            }
            // --- End Validation ---

            let weight = null;
            if (rubricType === 'numerical') {
                weight = parseFloat($(this).find('.rubric-weight').val()) || null;
                if (weight !== null && (isNaN(weight) || weight < 0)) {
                    weight = null;
                }
            }
            rubrics.push({
                rubric_id: rubricId, // Keep as string or number, backend should handle
                order_index: index,
                weight: weight
            });
        });

        // --- Client-side Validation: Stop if invalid rubric found ---
        if (invalidRubricFound) {
            return;
        }
        // --- End Validation ---


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
                    loadAvailableRubrics(); // Refresh available rubrics list too
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


    // --- Meatball Menu Functionality ---
    // Handle meatball button clicks specifically for rubric groups
    document.addEventListener('click', function(e) {
        // Handle meatball button clicks for rubric groups
        if (e.target.closest('.meatball-btn[data-group-id]')) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = e.target.closest('.meatball-btn');
            const groupId = btn.getAttribute('data-group-id');
            let dropdown = document.getElementById(`rubric-group-dropdown-${groupId}`);
            
            // Close all other dropdowns first
            document.querySelectorAll('.meatball-dropdown-portal[id^="rubric-group-dropdown-"]').forEach(dd => {
                dd.style.display = 'none';
            });
            
            // If dropdown doesn't exist, create it
            if (!dropdown) {
                console.log('Creating dropdown for rubric group:', groupId);
                dropdown = document.createElement('div');
                dropdown.className = 'meatball-dropdown-portal';
                dropdown.id = `rubric-group-dropdown-${groupId}`;
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
                    <button class="meatball-dropdown-item edit-group-btn" data-id="${groupId}">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="meatball-dropdown-item delete-group-btn" data-id="${groupId}">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                `;
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
        // Close dropdown when clicking outside - only for rubric group dropdowns
        else if (!e.target.closest('.meatball-dropdown-portal[id^="rubric-group-dropdown-"]') && !e.target.closest('.meatball-btn[data-group-id]')) {
            document.querySelectorAll('.meatball-dropdown-portal[id^="rubric-group-dropdown-"]').forEach(dd => {
                dd.style.display = 'none';
            });
        }
    });

    // Handle meatball dropdown item clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.meatball-dropdown-item')) {
            const item = e.target.closest('.meatball-dropdown-item');
            
            // Only handle if this is a rubric group dropdown item
            if (!item.classList.contains('edit-group-btn') && !item.classList.contains('delete-group-btn')) {
                return;
            }
            
            const groupId = item.getAttribute('data-id');
            console.log('Clicked dropdown item for rubric group:', groupId);
            
            // Close the dropdown
            const dropdown = item.closest('.meatball-dropdown-portal');
            if (dropdown) {
                dropdown.style.display = 'none';
                console.log('Dropdown found and hidden for rubric group:', groupId);
            } else {
                console.log('Dropdown not found for rubric group:', groupId);
            }
            
            // The existing edit-group-btn and delete-group-btn event handlers will handle the action
            // since we've preserved the same classes on the dropdown items
        }
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
