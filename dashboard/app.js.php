<script>
    // Add helper function to dynamically populate program dropdowns
    function populateProgramDropdown(selectElement, selectedValue = null) {
        selectElement.html('<option value="">Loading programs...</option>');

        $.ajax({
            url: 'includes/get_programs_grouped.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                selectElement.empty();
                selectElement.append('<option value="">Select Program</option>');

                if (response.success && response.programs.length > 0) {
                    let currentCollege = null;
                    let optgroup = null;

                    $.each(response.programs, function(i, program) {
                        // Create new optgroup when college changes
                        if (program.college !== currentCollege) {
                            currentCollege = program.college;
                            optgroup = $('<optgroup>', {
                                label: currentCollege
                            });
                            selectElement.append(optgroup);
                        }

                        // Add program option to current optgroup
                        const option = $('<option>', {
                            value: program.display_name,
                            text: program.display_name
                        });

                        // Set selected if matches
                        if (selectedValue !== null && selectedValue == program.id) {
                            option.prop('selected', true);
                        }

                        optgroup.append(option);
                    });
                } else {
                    selectElement.html('<option value="">No programs available</option>');
                }
            },
            error: function() {
                selectElement.html('<option value="">Error loading programs</option>');
                console.error("Failed to load programs");
            }
        });
    }

    $(document).ready(function() {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(
                '<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
        }

        // NEW: Connect the saveChanges button to trigger the edit form submission
        $(document).on('click', '#saveChanges', function() {
            console.log('DEBUG: Save changes button clicked, triggering edit form submission');
            $('#editForm').submit();
        });

        // NEW: Add bulk row functionality
        $(document).off('click.addBulkRow').on('click.addBulkRow', '#addBulkRow', function() {
            let count = parseInt($('#rowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTable tbody tr').length;
                var newRow = `
                    <tr>
                        <td><input type="text" class="form-control" name="users[${rowCount}][id]"></td>
                        <td><input type="text" class="form-control" name="users[${rowCount}][name]"></td>
                        <td><input type="text" class="form-control" name="users[${rowCount}][program]"></td>
                        <td class="text-center">
                        <input type="checkbox" name="users[${rowCount}][no_username]">
                        </td>
                    </tr>
                    `;
                $('#bulkAddTable tbody').append(newRow);
            }
        });

        // When the Bulk Add Modal is shown, insert the CSV download link if not already present
        $('#bulkAddModal').on('shown.bs.modal', function() {
            if (!$(this).find('#downloadCsvTemplate').length) {
                $(this).find('.modal-body').prepend(`
                        <div class="mb-3">
                            <a href="#" id="downloadCsvTemplate" class="btn btn-sm btn-secondary">Download CSV Template</a>
                        </div>
                    `);
            }
        });

        // Update Bulk Add Users CSV download handler
        $(document).off('click.downloadCsvTemplate').on('click.downloadCsvTemplate', '#downloadCsvTemplate',
            function(e) {
                e.preventDefault();
                const csvContent = 'ID,Name,Program,No Username\\n';
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'users_template.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });

        // NEW: Bulk Add Students functionality
        $(document).off('click.bulkAddBtn').on('click.bulkAddBtn', '.bulk-add-btn', function(e) {
            e.preventDefault();
            console.log('Bulk Add User button clicked');
            $('#bulkAddModal').modal('show');
        });

        // NEW: Bulk Add Teams functionality
        $(document).off('click.bulkAddTeamsBtn').on('click.bulkAddTeamsBtn', '.bulk-add-teams-btn', function(e) {
            e.preventDefault();
            console.log('Bulk Add Teams button clicked');
            $('#bulkAddTeamsModal').modal('show');
        });

        // Schedule-specific functionality
        let currentScheduleType = 'program';
        
        // Handle schedule add/edit modal functionality
        $(document).off('click.scheduleAddBtn').on('click.scheduleAddBtn', '.add-btn[data-schedule-type]', function(e) {
            e.preventDefault();
            
            const scheduleType = $(this).data('schedule-type');
            const table = $(this).data('table');
            
            resetScheduleForm();
            $('#scheduleModalLabel').text('Add Schedule');
            $('#scheduleTable').val(table);
            $('#scheduleType').val(scheduleType);
            
            if (scheduleType === 'program') {
                $('#programScheduleFields').show();
                $('#facultyScheduleFields').hide();
                loadProgramsForSchedule();
            } else {
                $('#programScheduleFields').hide();
                $('#facultyScheduleFields').show();
                loadFacultyForSchedule();
            }
            
            $('#scheduleModal').modal('show');
        });

        // Handle schedule edit functionality
        $(document).off('click.scheduleEditBtn').on('click.scheduleEditBtn', '.edit-schedule-btn', function(e) {
            e.preventDefault();
            
            const scheduleId = $(this).data('id');
            const table = $(this).data('table');
            
            $.ajax({
                url: 'includes/get_schedule_details.php',
                method: 'POST',
                data: { id: scheduleId, table: table },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        populateScheduleForm(response.data, table);
                        $('#scheduleModal').modal('show');
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function() {
                    showToast('Error', 'Failed to load schedule details', 'error');
                }
            });
        });

        // Save schedule functionality
        $(document).off('click.saveSchedule').on('click.saveSchedule', '#saveSchedule', function() {
            const form = $('#scheduleForm')[0];
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const formData = new FormData(form);
            
            // Validate time inputs
            const startTime = formData.get('start_time');
            const endTime = formData.get('end_time');
            
            if (startTime && endTime && startTime >= endTime) {
                showToast('Error', 'End time must be after start time', 'error');
                return;
            }
            
            $.ajax({
                url: 'includes/save_schedule.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Schedule saved successfully', 'success');
                        $('#scheduleModal').modal('hide');
                        
                        // Reload the appropriate table/tab
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function() {
                    showToast('Error', 'Failed to save schedule', 'error');
                }
            });
        });

        // Delete schedule functionality
        $(document).off('click.scheduleDeleteBtn').on('click.scheduleDeleteBtn', '.delete-schedule-btn', function(e) {
            e.preventDefault();
            
            const scheduleId = $(this).data('id');
            const table = $(this).data('table');
            
            if (confirm('Are you sure you want to delete this schedule?')) {
                $.ajax({
                    url: 'includes/delete_schedule.php',
                    method: 'POST',
                    data: { id: scheduleId, table: table },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('Success', 'Schedule deleted successfully', 'success');
                            
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('Error', 'Failed to delete schedule', 'error');
                    }
                });
            }
        });

        // Helper functions for schedule modal
        function resetScheduleForm() {
            $('#scheduleForm')[0].reset();
            $('#scheduleId').val('');
            $('#scheduleModalLabel').text('Add Schedule');
        }

        function loadProgramsForSchedule() {
            $.ajax({
                url: 'includes/get_programs_grouped.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const programSelect = $('#scheduleModal #program');
                    programSelect.empty().append('<option value="">Select Program</option>');
                    
                    if (response.success && response.programs.length > 0) {
                        response.programs.forEach(function(program) {
                            programSelect.append(`<option value="${program.display_name}">${program.display_name}</option>`);
                        });
                    }
                },
                error: function() {
                    showToast('Error', 'Failed to load programs', 'error');
                }
            });
        }

        function loadFacultyForSchedule() {
            $.ajax({
                url: 'includes/get_faculty.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const facultySelect = $('#scheduleModal #facultyUser');
                    facultySelect.empty().append('<option value="">Select Faculty</option>');
                    
                    if (response.success && response.faculty.length > 0) {
                        response.faculty.forEach(function(faculty) {
                            facultySelect.append(`<option value="${faculty.id}">${faculty.name}</option>`);
                        });
                    }
                },
                error: function() {
                    showToast('Error', 'Failed to load faculty', 'error');
                }
            });
        }

        function populateScheduleForm(data, table) {
            $('#scheduleModalLabel').text('Edit Schedule');
            $('#scheduleId').val(data.id);
            $('#scheduleTable').val(table);
            
            if (table === 'default_schedules') {
                $('#scheduleType').val('program');
                $('#programScheduleFields').show();
                $('#facultyScheduleFields').hide();
                
                loadProgramsForSchedule();
                
                // Populate fields after programs are loaded
                setTimeout(function() {
                    $('#program').val(data.program);
                    $('#year').val(data.year);
                    $('#section').val(data.section);
                    $('#className').val(data.class_name);
                    $('#building').val(data.building);
                    $('#room').val(data.room);
                }, 500);
                
            } else {
                $('#scheduleType').val('faculty');
                $('#programScheduleFields').hide();
                $('#facultyScheduleFields').show();
                
                loadFacultyForSchedule();
                
                // Populate fields after faculty are loaded
                setTimeout(function() {
                    $('#facultyUser').val(data.user_id);
                    $('#scheduleTypeField').val(data.schedule_type);
                    $('#facultyBuilding').val(data.building);
                    $('#facultyRoom').val(data.room);
                    $('#description').val(data.description);
                }, 500);
            }
            
            $('#dayOfWeek').val(data.day_of_week);
            $('#startTime').val(data.start_time);
            $('#endTime').val(data.end_time);
        }

        const sidebarContainer = $('#sidebarContainer');
        const mainContent = $('#mainContent');
        const toggleButton = $('#toggleSidebar');

        toggleButton.on('click', function() {
            sidebarContainer.toggleClass('collapsed');
            mainContent.toggleClass('expanded');
            toggleButton.toggleClass('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebarContainer.hasClass('collapsed'));
        });

        // Check localStorage for saved sidebar state on page load
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebarContainer.addClass('collapsed');
            mainContent.addClass('expanded');
            toggleButton.addClass('collapsed');
        }

        // Handle window resize
        $(window).on('resize', function() {
            if (window.innerWidth <= 768) {
                mainContent.addClass('expanded');
            } else {
                if (!sidebarContainer.hasClass('collapsed')) {
                    mainContent.removeClass('expanded');
                }
            }
        });
    });

    function updatePanelistDropdowns() {
        // Collect all selected panelist IDs from the current modal context
        var $modal = $('.modal.show'); // Target only the currently visible modal
        var $panelistContainer = $modal.find('#panelists');
        if (!$panelistContainer.length) return; // Exit if no panelist container found

        var selectedIds = [];
        $panelistContainer.find('.panelist select').each(function() {
            var val = $(this).val();
            if (val) selectedIds.push(val);
        });

        $panelistContainer.find('.panelist select').each(function() {
            var $select = $(this);
            var currentVal = $select.val();
            $select.find('option').each(function() {
                var $opt = $(this);
                if ($opt.val() && $opt.val() !== currentVal && selectedIds.includes($opt.val())) {
                    $opt.prop('disabled', true);
                } else {
                    $opt.prop('disabled', false);
                }
            });
        });
    }


    function addNewPanelist(staff) {
        var $modal = $('.modal.show'); // Target the currently visible modal
        var $panelistContainer = $modal.find('#panelists');
        var $addPanelistButton = $modal.find('#addPanelist');

        // Check current number of panelists within the specific modal
        var currentPanelistCount = $panelistContainer.find('.panelist').length;
        if (currentPanelistCount >= 3) {
            showToast('Warning', 'Maximum of 3 panelists allowed.', 'warning');
            return; // Stop if limit is reached
        }

        // Determine the next index based on existing panelists in this modal
        var currentIndices = $panelistContainer.find('.panelist select').map(function() {
            var name = $(this).attr('name');
            var match = name.match(/\[(\d+)\]/);
            return match ? parseInt(match[1], 10) : -1;
        }).get();
        var nextIndex = currentIndices.length > 0 ? Math.max(...currentIndices) + 1 : 0;

        var newPanelistHtml = `
        <div class="mb-3 row panelist">
            <label class="col-sm-2 col-form-label">Panelist ${nextIndex + 1}</label>
            <div class="col-sm-8">
                <select class="form-select" name="panelist_id[${nextIndex}]">
                    <option value="">Select a panelist</option>
                    ${staff.map(member => `<option value="${member.id}">${member.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
            </div>
        </div>
`;

        $panelistContainer.append(newPanelistHtml);
        console.log('New panelist added to DOM with name:', `panelist_id[${nextIndex}]`);

        // Disable button if limit is now reached
        if ($panelistContainer.find('.panelist').length >= 3) {
            $addPanelistButton.prop('disabled', true);
        }
        // Update dropdowns to enforce unique selection
        updatePanelistDropdowns();

        // Add change listener to the new dropdown
        $panelistContainer.find(`select[name="panelist_id[${nextIndex}]"]`).on('change', updatePanelistDropdowns);
    }


    // Optionally, update existing panelist entries to have unique indices on modal show
    $('#editModal, #addModal').on('shown.bs.modal', function() {
        var $panelistContainer = $(this).find('#panelists');
        if ($panelistContainer.length) {
            $panelistContainer.find('.panelist').each(function(index) {
                $(this).find('select').attr('name', `panelist_id[${index}]`);
                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
            });
            // Add change listener to all panelist dropdowns within this modal
            $panelistContainer.find('select').off('change.updateDropdowns').on('change.updateDropdowns',
                updatePanelistDropdowns);
            updatePanelistDropdowns(); // Initial update on show
        }
    });


    // Define addNewTeamMember function globally
    function addNewTeamMember() {
        $.ajax({
            url: 'includes/get_users.php',
            method: 'GET',
            dataType: 'json',
            success: function(users) {
                var $modal = $('.modal.show'); // Target the current modal
                var $teamMembersContainer = $modal.find('#teamMembers');

                var newMemberHtml = `
                <div class="mb-3 row team-member">
                    <div class="col-sm-5">
                        <select class="form-select user-select" name="new_user_id[]" style="display:block;">
                            <option value="">Select a user</option>
                            ${users.map(user => `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`).join('')}
                        </select>
                        <input type="text" class="form-control new-username-input" name="new_username[]" placeholder="Enter username" style="display:none;">
                        <a href="#" class="toggle-input">Switch to manual</a>
                    </div>
                    <div class="col-sm-5">
                        <select class="form-select role-select" name="new_role[]">
                            <option value="adviser">Adviser</option>
                            <option value="leader">Leader</option>
                            <option value="member" selected>Member</option> <!-- Default to member -->
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                    </div>
                </div>
                `;
                $teamMembersContainer.append(newMemberHtml);
                $teamMembersContainer.find('.toggle-input').last().on('click', function(e) {
                    e.preventDefault();
                    var $select = $(this).siblings('.user-select');
                    var $input = $(this).siblings('.new-username-input');
                    if ($select.is(':visible')) {
                        $select.hide().prop('disabled', true); // Disable select when hidden
                        $input.show().prop('disabled', false); // Enable input when shown
                        $(this).text('Switch to select');
                    } else {
                        $input.hide().prop('disabled', true); // Disable input when hidden
                        $select.show().prop('disabled', false); // Enable select when shown
                        $(this).text('Switch to manual');
                    }
                });
                // Initially disable the input field
                $teamMembersContainer.find('.new-username-input').last().prop('disabled', true);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showToast('Error', 'Error loading users', 'error');
            }
        });
    }


    // Remove team member functionality (works for both edit and add modals)
    $(document).on('click', '.remove-member', function() {
        var teamMember = $(this).closest('.team-member');
        var userId = teamMember.data('user-id'); // Only set for existing members in edit form
        var teamId = $('.modal.show').find('input[name="id"]').val(); // Get team ID from the current modal

        if (userId && teamId) { // Existing member in edit form
            if (confirm(
                    'Are you sure you want to remove this team member from the team? This action cannot be undone.'
                    )) {
                $.ajax({
                    url: 'includes/remove_team_member.php',
                    method: 'POST',
                    data: {
                        team_id: teamId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            teamMember.remove();
                            showToast('Success', 'Team member removed successfully', 'success');
                        } else {
                            showToast('Error', response.message || 'Failed to remove member', 'error');
                        }
                    },
                    error: function() {
                        showToast('Error', 'Unable to remove team member via AJAX', 'error');
                    }
                });
            }
        } else {
            // If it's a new member (not yet saved to database, or in add form), just remove from form
            teamMember.remove();
        }
    });

    // Helper function for showing toasts
    function showToast(title, message, type = 'success') {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(`
    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    </div>
`);

        }

        // Generate unique ID for the toast
        const toastId = 'toast-' + Date.now();

        // Create toast HTML with more prominent styling
        const toast = `
<div id="${toastId}" class="toast align-items-center border-0"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    style="min-width: 300px; opacity: 1; background-color: ${type === 'success' ? 'var(--main-accent)' : (type === 'error' ? 'var(--main-btn-del)' : 'var(--bs-warning)')};">
    <div class="d-flex">
        <div class="toast-body" style="font-size: 1rem; padding: 1rem; color:var(--main-bg-dark);">
            <strong>${title}:</strong> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>
`;

        // Add toast to container
        $('#toastContainer').append(toast);

        // Initialize and show the toast with modified options
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000,
            animation: true
        });
        toastElement.show();

        // Remove toast element after it's hidden
        $(`#${toastId}`).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
</script>
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form contents filled via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveEdit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="addForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form contents filled via JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addItem">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item?</p>
                <p class="mb-0"><strong>Table:</strong> <span id="deleteTableName"></span></p>
                <p class="mb-0"><strong>ID:</strong> <span id="deleteItemId"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Users Modal -->
<div class="modal fade" id="bulkAddModal" tabindex="-1" aria-labelledby="bulkAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="bulkAddForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkAddModalLabel">Bulk Add Users</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Option to upload excel file -->
                    <div class="mb-3">
                        <label for="bulkFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkFileInput" name="bulkFile"
                            accept=".csv, .xls, .xlsx">
                    </div>
                    <!-- NEW: Option to paste bulk data -->
                    <div class="mb-3">
                        <label for="bulkTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTextInput" name="bulkTextInput" rows="5"
                            placeholder="Paste CSV data here"></textarea>
                    </div>
                    <hr>
                    <!-- Table for manual data input -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bulkAddTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Program</th>
                                    <th>No Username?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- one sample row -->
                                <tr>
                                    <td><input type="text" class="form-control" name="users[0][id]"></td>
                                    <td><input type="text" class="form-control" name="users[0][name]"></td>
                                    <td><input type="text" class="form-control" name="users[0][program]"></td>
                                    <td class="text-center">
                                        <input type="checkbox" name="users[0][no_username]">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Optionally, add number input to add multiple rows -->
                    <div class="mb-3">
                        <label for="rowCountInput" class="form-label">Add Rows: </label>
                        <input type="number" id="rowCountInput" class="form-control"
                            style="width:100px; display:inline-block" min="1" value="1">
                        <button type="button" class="btn btn-secondary" id="addBulkRow">Add Rows</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="bulkAddSubmit" class="btn btn-info">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Bulk Add Teams Modal -->
<div class="modal fade" id="bulkAddTeamsModal" tabindex="-1" aria-labelledby="bulkAddTeamsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="bulkAddTeamsForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkAddTeamsModalLabel">Bulk Add Teams</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Option to upload excel file -->
                    <div class="mb-3">
                        <label for="bulkTeamsFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkTeamsFileInput" name="bulkTeamsFile"
                            accept=".csv, .xls, .xlsx">
                    </div>
                    <!-- NEW: Option to paste bulk data -->
                    <div class="mb-3">
                        <label for="bulkTeamsTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTeamsTextInput" name="bulkTeamsTextInput" rows="5"
                            placeholder="Paste CSV data here"></textarea>
                    </div>
                    <hr>
                    <!-- Table for manual data input -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bulkAddTeamsTable">
                            <thead>
                                <tr>
                                    <th>Team Name</th>
                                    <th>Research Title</th>
                                    <th>Area of Expertise</th>
                                    <th>Program</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- one sample row -->
                                <tr>
                                    <td><input type="text" class="form-control" name="teams[0][name]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][title]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][area_of_expertise]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][program]"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Optionally, add number input to add multiple rows -->
                    <div class="mb-3">
                        <label for="teamsRowCountInput" class="form-label">Add Rows: </label>
                        <input type="number" id="teamsRowCountInput" class="form-control"
                            style="width:100px; display:inline-block" min="1" value="1">
                        <button type="button" class="btn btn-secondary" id="addBulkTeamsRow">Add Rows</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="bulkAddTeamsSubmit" class="btn btn-info">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" id="scheduleId" name="id">
                    <input type="hidden" id="scheduleTable" name="table">
                    <input type="hidden" id="scheduleType" name="schedule_type">
                    
                    <!-- Program Schedule Fields -->
                    <div id="programScheduleFields">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="program" class="form-label">Program *</label>
                                <select class="form-select" id="program" name="program" required>
                                    <option value="">Select Program</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="year" class="form-label">Year *</label>
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="section" class="form-label">Section *</label>
                                <select class="form-select" id="section" name="section" required>
                                    <option value="">Select Section</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="className" class="form-label">Class Name *</label>
                                <input type="text" class="form-control" id="className" name="class_name" placeholder="e.g., Software Engineering" required>
                            </div>
                            <div class="col-md-3">
                                <label for="building" class="form-label">Building *</label>
                                <input type="text" class="form-control" id="building" name="building" placeholder="e.g., IT Building" required>
                            </div>
                            <div class="col-md-3">
                                <label for="room" class="form-label">Room *</label>
                                <input type="text" class="form-control" id="room" name="room" placeholder="e.g., IT-301" required>
                            </div>
                        </div>
                    </div>

                    <!-- Faculty Schedule Fields -->
                    <div id="facultyScheduleFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="facultyUser" class="form-label">Faculty *</label>
                                <select class="form-select" id="facultyUser" name="user_id" required>
                                    <option value="">Select Faculty</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="scheduleTypeField" class="form-label">Schedule Type *</label>
                                <select class="form-select" id="scheduleTypeField" name="schedule_type_field" required>
                                    <option value="">Select Type</option>
                                    <option value="office_hours">Office Hours</option>
                                    <option value="consultation">Consultation</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label for="facultyBuilding" class="form-label">Building</label>
                                <input type="text" class="form-control" id="facultyBuilding" name="building" placeholder="e.g., Faculty Building">
                            </div>
                            <div class="col-md-4">
                                <label for="facultyRoom" class="form-label">Room</label>
                                <input type="text" class="form-control" id="facultyRoom" name="room" placeholder="e.g., FB-201">
                            </div>
                            <div class="col-md-4">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" placeholder="Additional details">
                            </div>
                        </div>
                    </div>

                    <!-- Common Time Fields -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="dayOfWeek" class="form-label">Day of Week *</label>
                            <select class="form-select" id="dayOfWeek" name="day_of_week" required>
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="startTime" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="startTime" name="start_time" required>
                        </div>
                        <div class="col-md-4">
                            <label for="endTime" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="endTime" name="end_time" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSchedule">Save Schedule</button>
            </div>
        </div>
    </div>
</div>