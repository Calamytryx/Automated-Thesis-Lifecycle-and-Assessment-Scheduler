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
                            optgroup = $('<optgroup>', { label: currentCollege });
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
            $('body').append('<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
        }

        // Edit button functionality
        $(document).off('click.editBtn').on('click.editBtn', '.edit-btn', function(e) {
            e.preventDefault();
            
            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Main app: Edit button clicked. Table:', table, 'ID:', id);
            
            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric edit to rubrics_tab.php handler');
                // Let the edit-rubric-btn handler in rubrics_tab.php handle this
                $('.edit-rubric-btn[data-id="' + id + '"]').trigger('click');
                return true; // Allow event to bubble to other handlers
            }

            // Continue with normal edit handling for other tables
            $.ajax({
                url: 'includes/get_item_details.php',
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response data:', response)
                    if (response.success) {
                        var form = $('#editForm');
                        form.empty();

                        // Add hidden inputs for table and id
                        form.append('<input type="hidden" name="table" value="' + table + '">');
                        form.append('<input type="hidden" name="id" value="' + id + '">');

                        if (table === 'users') {
                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="usertype" class="form-label">User Type</label>
                                    <select class="form-select" id="usertype" name="usertype">
                                        <option value="0"${response.data.usertype == 0 ? ' selected' : ''}>Admin</option>
                                        <option value="1"${response.data.usertype == 1 ? ' selected' : ''}>Student</option>
                                        <option value="2"${response.data.usertype == 2 ? ' selected' : ''}>Faculty</option>
                                    </select>
                                </div>
                            `;

                            var fieldsToShow = ['username', 'email', 'first_name', 'last_name', 'gender', 'headline', 'bio'];

                            fieldsToShow.forEach(function(key) {
                                var value = response.data[key] || '';
                                var inputType = (key === 'email') ? 'email' : 'text';
                                var label = key.replace('_', ' ').charAt(0).toUpperCase() + key.slice(1);

                                if (key === 'bio') {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <textarea class="form-control" id="${key}" name="${key}" rows="3">${value}</textarea>
                                        </div>
                                    `;
                                } else {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <input type="${inputType}" class="form-control" id="${key}" name="${key}" value="${value}">
                                        </div>
                                    `;
                                }
                            });
                            
                            // Replace hardcoded program dropdown with select element
                            formHtml += `
                                <div class="mb-3">
                                    <label for="program" class="form-label">Program</label>
                                    <input type="text" class="form-control" id="program" name="program" value="${response.data.program || ''}">
                                </div>
                            `;
                            
                            // Area of expertise field
                            formHtml += `
                                <div class="mb-3 area-expertise-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                    <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                                        </ul>
                                    </div>
                                </div>
                            `;
                            
                            // Add Is Part Time radio buttons for faculty (usertype 2)
                            formHtml += `
                            <div class="mb-3 is-part-time-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                <label class="form-label">Is Part Time</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_parttime" id="fullTime" value="0" ${response.data.is_parttime == 0 || response.data.is_parttime == null ? 'checked' : ''}>
                                    <label class="form-check-label" for="fullTime">Full Time</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_parttime" id="partTime" value="1" ${response.data.is_parttime == 1 ? 'checked' : ''}>
                                    <label class="form-check-label" for="partTime">Part Time</label>
                                </div>
                            </div>
                            `;

                            form.html(formHtml);
                            
                            // Populate the programs dropdown
                            populateProgramDropdown($('#program_id'), response.data.program_id);

                            $('#program').val(response.data.program || '');
                            $('#usertype').on('change', function() {
                                if ($(this).val() == 2) {
                                    $('.area-expertise-field').show();
                                    $('.is-part-time-field').show();
                                } else {
                                    $('.area-expertise-field').hide();
                                    $('.is-part-time-field').hide();
                                }
                            });
                        } else if (table === 'teams') {
                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="name" class="form-label">Team Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name}">
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Research Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                            </div>
                            <div class="mb-3">
                                <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program">
                                    <option value="">Select Program</option>
                                    ${response.data.program_teams 
                                        ? `<option value="${response.data.program_teams}" selected>${response.data.program_teams}</option>` 
                                        : '<option value="">Loading programs...</option>'}
                                </select>
                            </div>
                            <h5 class="mt-4">Team Members</h5>
                            <div id="teamMembers">
`;

                            response.data.members.forEach(function(member, index) {
                                formHtml += `
                                <div class="mb-3 row team-member" data-user-id="${member.id}">
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
                                        <input type="hidden" name="member_ids[]" value="${member.id}">
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-select" name="member_role[]">
                                            <option value="adviser"${member.role === 'adviser' ? ' selected' : ''}>Adviser</option>
                                            <option value="leader"${member.role === 'leader' ? ' selected' : ''}>Leader</option>
                                            <option value="member"${member.role === 'member' ? ' selected' : ''}>Member</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                                    </div>
                                </div>
                            `;
                            });

                            formHtml += `
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
                        `;
                        
                        form.html(formHtml);
                        
                        // Populate the programs dropdown for the edit form, selecting the current value
                        populateProgramDropdown($('#editForm #program_id'), response.data.program_id);

                        // Add team member functionality
                        $('#addTeamMember').on('click', function() {
                            console.log('Add Team Member button clicked');
                            addNewTeamMember();
                        });

                    } else if (table === 'programs') {
                        var d = response.data;
                        var html=`
                          <input type="hidden" name="table" value="programs">
                          <input type="hidden" name="id" value="${id}">
                          <div class="mb-3">
                            <label class="form-label">College</label>
                            <input class="form-control" name="college" id="college" value="${d.college || ''}" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input class="form-control" name="department" id="department" value="${d.department || ''}">
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Program Name</label>
                            <input class="form-control" name="name" id="name" value="${d.name || ''}" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input class="form-control" name="specialization" id="specialization" value="${d.specialization || ''}">
                          </div>`;
                        form.html(html);
                        $('#editModal').modal('show');
                        return;
                    } else if (table === 'thesis_topics') {
                        var formHtml = `
                        <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="topic" class="form-label">Topic</label>
                                <input type="text" class="form-control" id="topic" name="topic" value="${response.data.topic}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" value="${response.data.category}">
                            </div>
                        `;
                        form.html(formHtml);
                    } else if (table === 'research_titles') {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${response.teams ? response.teams.map(team => `<option value="${team.id}"${team.id == response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('') : ''}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                            </div>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    <option value="">Loading programs...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="approved" name="approved_at" value="1" ${response.data.approved_at ? 'checked' : ''}>
                                    <label class="form-check-label" for="approved_at">Approved</label>
                                </div>
                            </div>
                        `;
                        form.html(formHtml);

                        // Populate the programs dropdown
                        populateProgramDropdown($('#program_id'), response.data.program_id);
                    } else if (table === 'env_variables') {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="key" class="form-label">Key</label>
                                <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                            </div>
                            <div class="mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="text" class="form-control" id="value" name="value" value="${response.data.value}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>
                        `;

                        // Handle special cases for DB_PASSWORD and MAIL_ENCRYPTION
                        if (response.data.key === 'DB_PASSWORD' || response.data.key === 'MAIL_ENCRYPTION') {
                            $('#value').val(''); // Clear the value field
                            form.html(`
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="key" class="form-label">Key</label>
                                <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                            </div>
                            <div class="mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="text" class="form-control" id="value" name="value" value="${response.data.value}">
                            </div>
                            <div class="mb-3">
                                <label for="old_value" class="form-label">Old Value</label>
                                <input type="password" class="form-control" id="old_value" name="old_value" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>
                            
                        `);
                        } else {
                            form.html(formHtml);
                        }
                    } else if (table === 'defense_schedules') {
                        var formHtml = `
                    <input type="hidden" name="table" value="${table}">
                    <input type="hidden" name="id" value="${id}">
                    <div class="mb-3">
                        <label for="schedule_date" class="form-label">Schedule Date</label>
                        <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required value="${response.data.schedule_date || ''}">
                        <small class="form-text text-muted">Select date for the defense schedule.</small>
                    </div>
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required value="${response.data.start_time || ''}"
                            onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                        <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                    </div>
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required value="${response.data.end_time || ''}"
                            onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                        <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                    </div>
                    <div class="mb-3">
                        <label for="room" class="form-label">Room</label>
                        <input type="text" class="form-control" id="room" name="room" required value="${response.data.room || ''}">
                    </div>
                    <div class="mb-3">
                        <label for="team_id" class="form-label">Team</label>
                        <select class="form-select" id="team_id" name="team_id" required>
                        <option value="">Select Team</option>
                            ${response.teams.map(team => `<option value="${team.id}"${team.id === response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('')}
                        </select>
                    </div>
                    <h5 class="mt-4">Panelists (Max 3)</h5>
                    <div id="panelists">
                `;

                        let panelistCount = 0; // Initialize panelist count
                        if (response.data.panelists && Array.isArray(response.data.panelists)) {
                            panelistCount = response.data.panelists.length; // Get initial count
                            response.data.panelists.forEach(function(panelist, index) {
                                // Ensure unique indices for names
                                formHtml += `
                            <div class="mb-3 row panelist align-items-center" data-user-id="${panelist.id}">
                                <label class="col-sm-2 col-form-label">Panelist ${index + 1}</label>
                                <div class="col-sm-8">
                                    <select class="form-select" name="panelist_id[${index}]">
                                        <option value="">Select Panelist</option>
                                        ${response.staff.map(staff => `<option value="${staff.id}"${staff.id === panelist.id ? ' selected' : ''}>${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                </div>
                            </div>
                        `;
                            });
                        } else {
                            console.warn('Panelists data is missing or not an array in the response for edit form.');
                        }

                        formHtml += `
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();
                            
                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = response.staff;

                        // Disable "Add Panelist" button initially if limit is reached
                        if (panelistCount >= 3) {
                            $('#addPanelist').prop('disabled', true);
                        }

                        // Add panelist functionality (uses global addNewPanelist function)
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked in edit modal');
                            addNewPanelist(window.staffData); // Call the global function
                        });

                        // Remove panelist functionality (Enable add button when removing)
                        // Use event delegation on the form for dynamically added elements
                        form.off('click.removePanelist').on('click.removePanelist', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                            // Check count and enable button if below limit
                            if ($('#panelists .panelist').length < 3) {
                                $('#addPanelist').prop('disabled', false);
                            }
                            // Re-index remaining panelists to ensure sequential names
                            $('#panelists .panelist').each(function(index) {
                                $(this).find('select').attr('name', `panelist_id[${index}]`);
                                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
                            });
                            updatePanelistDropdowns(); // Update dropdowns after removal
                        });

                        // Update dropdowns initially to disable selected options in other dropdowns
                        updatePanelistDropdowns();
                    } else if (table === 'requirements') {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required>${response.data.description || ''}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="${response.data.due_date || ''}" required>
                            </div>
                        `;
                        form.html(formHtml);
                    } else if (table === 'rubrics') {
                        // Don't generate form fields - they're handled in rubrics_tab.php
                        return;
                    }
                    // Add more conditions for other tables as needed
                    $('#editModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error: Unable to fetch item details - ' + error);
            }
        });
        });

        // UPDATED: Edit form submission handler with debug logs
        $(document).off('submit.editForm').on('submit.editForm', '#editForm', function(e) {
            e.preventDefault();
            console.log('DEBUG: Edit form submit event triggered');  // <-- New debug log
            var formData = new FormData(this);
            var table = formData.get('table'); // Get table name from form data

            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                    showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/edit_items.php 
                // to prevent rescheduling a team that already has a schedule on the selected date.
            }


            $.ajax({
                url: 'includes/edit_items.php', // Updated path for correct endpoint
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('DEBUG: Edit response received:', response);
                    if (response.success) {
                        showToast('Success', 'Item updated successfully', 'success');
                        $('#editModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast('Error', response.message || 'Update failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('DEBUG: AJAX error in edit form:', xhr.responseText);
                    showToast('Error', 'Unable to update item: ' + error, 'error');
                }
            }); // Close $.ajax call
        }); // Close $(document).on('submit', '#editForm', ...) handler

        // Connect the saveEdit button to trigger the edit form submission
        $(document).off('click.saveEdit').on('click.saveEdit', '#saveEdit', function(){
            console.log('DEBUG: Save changes button clicked, triggering edit form submission');
            $('#editForm').submit();
        });

        // Add button functionality
        $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function(e) {
            e.preventDefault();
            
            var table = $(this).data('table');
            console.log('Main app: Add button clicked for table:', table);
            
            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
                // Let the dedicated handler in rubrics_tab.php handle this
                return true; // Allow event to bubble to other handlers
            }
            
            // Special handling for thesis_topics
            if (table === 'thesis_topics') {
                console.log('Main app: Processing thesis topic add');
                // For thesis topics, we'll let the main handler show the add modal,
                // and the thesis_topics_tab.php handler will populate it
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="' + table + '">');
                
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );
                
                $('#addModal').modal('show');
                return true;
            }

            // programs
            if (table === 'programs') {
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="programs">');
                form.append(`
                  <div class="mb-3">
                    <label class="form-label">College</label>
                    <input class="form-control" name="college" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Department</label>
                    <input class="form-control" name="department">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Program Name</label>
                    <input class="form-control" name="name" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Specialization</label>
                    <input type="text" class="form-control" name="specialization">
                  </div>
                `);
                $('#addModal').modal('show');
                return true;
            }
            
            // Standard handling for all other tables
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');

            // Generate form fields based on table
            if (table === 'users') {
                form.append('<div class="mb-3">' +
                    '<label for="username" class="form-label">Username</label>' +
                    '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="email" class="form-label">Email</label>' +
                    '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="password" class="form-label">Password</label>' +
                    '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="first_name" class="form-label">First Name</label>' +
                    '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="last_name" class="form-label">Last Name</label>' +
                    '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label for="program_id" class="form-label">Program</label>' +
                        '<select class="form-select" id="program_id" name="program_id">' +
                            '<option value="">Loading programs...</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="mb-3 area-expertise-field" style="display:none;">' +
                    '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                    '<div class="input-group">' +
                        '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                        '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                        '<ul class="dropdown-menu">' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                        '</ul>' +
                    '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="usertype" class="form-label">User Type</label>' +
                    '<select class="form-select" id="usertype" name="usertype" required>' +
                    '<option value="" disabled selected>Select User Type</option>' +
                    '<option value="0">Admin</option>' +
                    '<option value="1">Student</option>' +
                    '<option value="2">Faculty</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="mb-3 is-part-time-field" style="display:none;">' +
                    '<label class="form-label">Is Part Time</label>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                    '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                    '</div>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                    '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                    '</div>' +
                    '</div>');
                
                // Populate the programs dropdown
                populateProgramDropdown($('#program_id'));
                
                // Add event listener for usertype change in add form
                $('#addForm').on('change', '#usertype', function() {
                    if ($(this).val() == 2) {
                        $('.area-expertise-field').show();
                        $('.is-part-time-field').show();
                    } else {
                        $('.area-expertise-field').hide();
                        $('.is-part-time-field').hide();
                    }
                });
            } else if (table === 'thesis_topics') {
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );
            } else if (table === 'research_titles') {
                // Fetch teams data to populate the dropdown
                $.ajax({
                    url: 'includes/get_teams_and_staff.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var formHtml = `
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team Name</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="approved" name="approved">
                                <label class="form-check-label" for="approved">Approved</label>
                            </div>
                        `;
                        $('#addForm').append(formHtml);
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams data', 'error');
                        // Fallback to simple input field if AJAX fails
                        form.append('<div class="mb-3">' +
                            '<label for="team_id" class="form-label">Team ID</label>' +
                            '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '<label for="title" class="form-label">Title</label>' +
                            '<input type="text" class="form-control" id="title" name="title" required>' +
                            '</div>' +
                            '<div class="mb-3 form-check">' +
                            '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                            '<label class="form-check-label" for="approved">Approved</label>' +
                            '</div>');
                    }
                });
            } else if (table === 'teams') {
                var formHtml = `
            <div class="mb-3">
            <label for="name" class="form-label">Team Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
            <label for="title" class="form-label">Research Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
            <label for="area_of_expertise" class="form-label">Area of Expertise</label>
            <div class="input-group">
                <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                </ul>
            </div>
            </div>
            <div class="mb-3">
            <label for="program_id" class="form-label">Program</label>
            <select class="form-select" id="program_id" name="program_id">
                <option value="">Loading programs...</option>
            </select>
            </div>
            <h5 class="mt-4">Team Members</h5>
            <div id="teamMembers">
            <!-- Team members will be added here -->
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
        `;
                form.append(formHtml);

                // Populate the programs dropdown for the add form
                populateProgramDropdown($('#addForm #program_id'));

                // Add team member functionality
                $('#addTeamMember').on('click', function() {
                    console.log('Add Team Member button clicked');
                    addNewTeamMember();
                });
            } else if (table === 'env_variables') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Key</label>' +
                    '<input type="text" class="form-control" id="key" name="key" required>' +
                    '</div>' +
                    '<label for="name" class="form-label">Value</label>' +
                    '<input type="text" class="form-control" id="value" name="value" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="created_by" class="form-label">Description</label>' +
                    '<input type="text" class="form-control" id="Description" name="description" required>' +
                    '</div>');
            } else if (table === 'requirements') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="due_date" class="form-label">Due Date</label>' +
                    '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                    '</div>');
            } else if (table === 'evaluations') {
                form.append('<div class="mb-3">' +
                    '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                    '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                    '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="total_score" class="form-label">Total Score</label>' +
                    '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="comments" class="form-label">Comments</label>' +
                    '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="recommendation" class="form-label">Recommendation</label>' +
                    '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                    '</div>');
            } else if (table === 'defense_schedules') {
                $.ajax({
                    url: 'includes/get_teams_and_staff.php', // Create this endpoint to fetch teams and staff
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <div class="mb-3">
                                <label for="schedule_date" class="form-label">Schedule Date</label>
                                <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                                <small class="form-text text-muted">Select date for the defense schedule.</small>
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="room" class="form-label">Room</label>
                                <input type="text" class="form-control" id="room" name="room" required>
                            </div>
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                    ${data.teams.map(team => `<option value="${team.id}"${team.id === response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <h5 class="mt-4">Panelists (Max 3)</h5>
                            <div id="panelists">
                `;

                        let panelistCount = 0; // Initialize panelist count
                        if (response.data.panelists && Array.isArray(response.data.panelists)) {
                            panelistCount = response.data.panelists.length; // Get initial count
                            response.data.panelists.forEach(function(panelist, index) {
                                // Ensure unique indices for names
                                formHtml += `
                            <div class="mb-3 row panelist align-items-center" data-user-id="${panelist.id}">
                                <label class="col-sm-2 col-form-label">Panelist ${index + 1}</label>
                                <div class="col-sm-8">
                                    <select class="form-select" name="panelist_id[${index}]">
                                        <option value="">Select Panelist</option>
                                        ${response.staff.map(staff => `<option value="${staff.id}"${staff.id === panelist.id ? ' selected' : ''}>${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                </div>
                            </div>
                        `;
                            });
                        } else {
                            console.warn('Panelists data is missing or not an array in the response for edit form.');
                        }

                        formHtml += `
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();
                            
                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = data.staff;

                        // Add panelist functionality
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked');
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                        });
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        // 
        // Add button functionality
        $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function(e) {
            e.preventDefault();
            
            var table = $(this).data('table');
            console.log('Main app: Add button clicked for table:', table);
            
            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
                // Let the dedicated handler in rubrics_tab.php handle this
                return true; // Allow event to bubble to other handlers
            }
            
            // Special handling for thesis_topics
            if (table === 'thesis_topics') {
                console.log('Main app: Processing thesis topic add');
                // For thesis topics, we'll let the main handler show the add modal,
                // and the thesis_topics_tab.php handler will populate it
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="' + table + '">');
                
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );
                
                $('#addModal').modal('show');
                return true;
            }

            // programs
            if (table === 'programs') {
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="programs">');
                form.append(`
                  <div class="mb-3">
                    <label class="form-label">College</label>
                    <input class="form-control" name="college" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Department</label>
                    <input class="form-control" name="department">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Program Name</label>
                    <input class="form-control" name="name" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Specialization</label>
                    <input type="text" class="form-control" name="specialization">
                  </div>
                `);
                $('#addModal').modal('show');
                return true;
            }
            
            // Standard handling for all other tables
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');

            // Generate form fields based on table
            if (table === 'users') {
                form.append('<div class="mb-3">' +
                    '<label for="username" class="form-label">Username</label>' +
                    '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="email" class="form-label">Email</label>' +
                    '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="password" class="form-label">Password</label>' +
                    '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="first_name" class="form-label">First Name</label>' +
                    '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="last_name" class="form-label">Last Name</label>' +
                    '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label for="program_id" class="form-label">Program</label>' +
                        '<select class="form-select" id="program_id" name="program_id">' +
                            '<option value="">Loading programs...</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="mb-3 area-expertise-field" style="display:none;">' +
                    '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                    '<div class="input-group">' +
                        '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                        '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                        '<ul class="dropdown-menu">' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                        '</ul>' +
                    '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="usertype" class="form-label">User Type</label>' +
                    '<select class="form-select" id="usertype" name="usertype" required>' +
                    '<option value="" disabled selected>Select User Type</option>' +
                    '<option value="0">Admin</option>' +
                    '<option value="1">Student</option>' +
                    '<option value="2">Faculty</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="mb-3 is-part-time-field" style="display:none;">' +
                    '<label class="form-label">Is Part Time</label>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                    '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                    '</div>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                    '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                    '</div>' +
                    '</div>');
                
                // Populate the programs dropdown
                populateProgramDropdown($('#program_id'));
                
                // Add event listener for usertype change in add form
                $('#addForm').on('change', '#usertype', function() {
                    if ($(this).val() == 2) {
                        $('.area-expertise-field').show();
                        $('.is-part-time-field').show();
                    } else {
                        $('.area-expertise-field').hide();
                        $('.is-part-time-field').hide();
                    }
                });
            } else if (table === 'thesis_topics') {
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );
            } else if (table === 'research_titles') {
                // Fetch teams data to populate the dropdown
                $.ajax({
                    url: 'includes/get_teams_and_staff.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var formHtml = `
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team Name</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="approved" name="approved">
                                <label class="form-check-label" for="approved">Approved</label>
                            </div>
                        `;
                        $('#addForm').append(formHtml);
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams data', 'error');
                        // Fallback to simple input field if AJAX fails
                        form.append('<div class="mb-3">' +
                            '<label for="team_id" class="form-label">Team ID</label>' +
                            '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '<label for="title" class="form-label">Title</label>' +
                            '<input type="text" class="form-control" id="title" name="title" required>' +
                            '</div>' +
                            '<div class="mb-3 form-check">' +
                            '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                            '<label class="form-check-label" for="approved">Approved</label>' +
                            '</div>');
                    }
                });
            } else if (table === 'teams') {
                var formHtml = `
            <div class="mb-3">
            <label for="name" class="form-label">Team Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
            <label for="title" class="form-label">Research Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
            <label for="area_of_expertise" class="form-label">Area of Expertise</label>
            <div class="input-group">
                <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                </ul>
            </div>
            </div>
            <div class="mb-3">
            <label for="program_id" class="form-label">Program</label>
            <select class="form-select" id="program_id" name="program_id">
                <option value="">Loading programs...</option>
            </select>
            </div>
            <h5 class="mt-4">Team Members</h5>
            <div id="teamMembers">
            <!-- Team members will be added here -->
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
        `;
                form.append(formHtml);

                // Populate the programs dropdown for the add form
                populateProgramDropdown($('#addForm #program_id'));

                // Add team member functionality
                $('#addTeamMember').on('click', function() {
                    console.log('Add Team Member button clicked');
                    addNewTeamMember();
                });
            } else if (table === 'env_variables') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Key</label>' +
                    '<input type="text" class="form-control" id="key" name="key" required>' +
                    '</div>' +
                    '<label for="name" class="form-label">Value</label>' +
                    '<input type="text" class="form-control" id="value" name="value" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="created_by" class="form-label">Description</label>' +
                    '<input type="text" class="form-control" id="Description" name="description" required>' +
                    '</div>');
            } else if (table === 'requirements') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="due_date" class="form-label">Due Date</label>' +
                    '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                    '</div>');
            } else if (table === 'evaluations') {
                form.append('<div class="mb-3">' +
                    '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                    '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                    '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="total_score" class="form-label">Total Score</label>' +
                    '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="comments" class="form-label">Comments</label>' +
                    '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="recommendation" class="form-label">Recommendation</label>' +
                    '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                    '</div>');
            } else if (table === 'defense_schedules') {
                $.ajax({
                    url: 'includes/get_teams_and_staff.php', // Create this endpoint to fetch teams and staff
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <div class="mb-3">
                                <label for="schedule_date" class="form-label">Schedule Date</label>
                                <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                                <small class="form-text text-muted">Select date for the defense schedule.</small>
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="room" class="form-label">Room</label>
                                <input type="text" class="form-control" id="room" name="room" required>
                            </div>
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                    ${data.teams.map(team => `<option value="${team.id}"${team.id === response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <h5 class="mt-4">Panelists (Max 3)</h5>
                            <div id="panelists">
                `;

                        let panelistCount = 0; // Initialize panelist count
                        if (response.data.panelists && Array.isArray(response.data.panelists)) {
                            panelistCount = response.data.panelists.length; // Get initial count
                            response.data.panelists.forEach(function(panelist, index) {
                                // Ensure unique indices for names
                                formHtml += `
                            <div class="mb-3 row panelist align-items-center" data-user-id="${panelist.id}">
                                <label class="col-sm-2 col-form-label">Panelist ${index + 1}</label>
                                <div class="col-sm-8">
                                    <select class="form-select" name="panelist_id[${index}]">
                                        <option value="">Select Panelist</option>
                                        ${response.staff.map(staff => `<option value="${staff.id}"${staff.id === panelist.id ? ' selected' : ''}>${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                </div>
                            </div>
                        `;
                            });
                        } else {
                            console.warn('Panelists data is missing or not an array in the response for edit form.');
                        }

                        formHtml += `
</div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();
                            
                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = data.staff;

                        // Add panelist functionality
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked');
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                        });
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        $(document).off('click.addItem').on('click.addItem', '#addItem', function(e) {
            e.preventDefault();
            console.log('Add item button clicked');
            
            var form = $('#addForm');
            var table = form.find('input[name="table"]').val();
            var formData = new FormData(form[0]); // moved initialization here
            
            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                                       showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/add_items.php 
                // to prevent scheduling a team that already has a schedule on the selected date.
            }
            
            $.ajax({
                url: 'includes/add_items.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Added successfully', 'success');
                        $('#addModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // Improved error message display
                        showToast('Error', response.message || 'An unknown error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // More user-friendly error message
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If parsing fails, stick with generic message but log the real error
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            }); // Close $.ajax call
    }); // Close $(document).on('click', '#addItem', ...) handler

    // NEW: Connect the saveChanges button to trigger the edit form submission
    $(document).on('click', '#saveChanges', function(){
        console.log('DEBUG: Save changes button clicked, triggering edit form submission');
        $('#editForm').submit();
    });

    // Add button functionality
    $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function(e) {
        e.preventDefault();
        
        var table = $(this).data('table');
        console.log('Main app: Add button clicked for table:', table);
        
        // Special handling for rubrics
        if (table === 'rubrics') {
            console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
            // Let the dedicated handler in rubrics_tab.php handle this
            return true; // Allow event to bubble to other handlers
        }
        
        // Special handling for thesis_topics
        if (table === 'thesis_topics') {
            console.log('Main app: Processing thesis topic add');
            // For thesis topics, we'll let the main handler show the add modal,
            // and the thesis_topics_tab.php handler will populate it
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');
            
            form.append('<div class="mb-3">' +
                '<label for="topic" class="form-label">Topic</label>' +
                '<input type="text" class="form-control" id="topic" name="topic" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="category" class="form-label">Category</label>' +
                '<input type="text" class="form-control" id="category" name="category" required>' +
                '</div>'
            );
            
            $('#addModal').modal('show');
            return true;
        }

        // programs
        if (table === 'programs') {
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="programs">');
            form.append(`
              <div class="mb-3">
            <label class="form-label">College</label>
            <input class="form-control" name="college" required>
              </div>
              <div class="mb-3">
            <label class="form-label">Department</label>
            <input class="form-control" name="department">
              </div>
              <div class="mb-3">
            <label class="form-label">Program Name</label>
            <input class="form-control" name="name" required>
              </div>
              <div class="mb-3">
            <label class="form-label">Specialization</label>
            <input type="text" class="form-control" name="specialization">
              </div>
            `);
            $('#addModal').modal('show');
            return true;
        }
        
        // Standard handling for all other tables
        var form = $('#addForm');
        form.empty();
        form.append('<input type="hidden" name="table" value="' + table + '">');

        // Generate form fields based on table
        if (table === 'users') {
            form.append('<div class="mb-3">' +
                '<label for="username" class="form-label">Username</label>' +
                '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="email" class="form-label">Email</label>' +
                '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="password" class="form-label">Password</label>' +
                '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="first_name" class="form-label">First Name</label>' +
                '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="last_name" class="form-label">Last Name</label>' +
                '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                    '<label for="program_id" class="form-label">Program</label>' +
                    '<select class="form-select" id="program_id" name="program_id">' +
                        '<option value="">Loading programs...</option>' +
                    '</select>' +
                '</div>' +
                '<div class="mb-3 area-expertise-field" style="display:none;">' +
                '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                '<div class="input-group">' +
                    '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                    '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                    '<ul class="dropdown-menu">' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                    '</ul>' +
                '</div>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="usertype" class="form-label">User Type</label>' +
                '<select class="form-select" id="usertype" name="usertype" required>' +
                '<option value="" disabled selected>Select User Type</option>' +
                '<option value="0">Admin</option>' +
                '<option value="1">Student</option>' +
                '<option value="2">Faculty</option>' +
                '</select>' +
                '</div>' +
                '<div class="mb-3 is-part-time-field" style="display:none;">' +
                '<label class="form-label">Is Part Time</label>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                '</div>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                '</div>' +
                '</div>');
            
            // Populate the programs dropdown
            populateProgramDropdown($('#program_id'));
            
            // Add event listener for usertype change in add form
            $('#addForm').on('change', '#usertype', function() {
                if ($(this).val() == 2) {
                    $('.area-expertise-field').show();
                    $('.is-part-time-field').show();
                } else {
                    $('.area-expertise-field').hide();
                    $('.is-part-time-field').hide();
                }
            });
        } else if (table === 'thesis_topics') {
            form.append('<div class="mb-3">' +
                '<label for="topic" class="form-label">Topic</label>' +
                '<input type="text" class="form-control" id="topic" name="topic" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="category" class="form-label">Category</label>' +
                '<input type="text" class="form-control" id="category" name="category" required>' +
                '</div>'
            );
        } else if (table === 'research_titles') {
            // Fetch teams data to populate the dropdown
            $.ajax({
                url: 'includes/get_teams_and_staff.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var formHtml = `
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team Name</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="approved" name="approved">
                            <label class="form-check-label" for="approved">Approved</label>
                        </div>
                    `;
                    $('#addForm').append(formHtml);
                },
                error: function() {
                    showToast('Error', 'Unable to fetch teams data', 'error');
                    // Fallback to simple input field if AJAX fails
                    form.append('<div class="mb-3">' +
                        '<label for="team_id" class="form-label">Team ID</label>' +
                        '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                        '</div>' +
                        '<div class="mb-3">' +
                        '<label for="title" class="form-label">Title</label>' +
                        '<input type="text" class="form-control" id="title" name="title" required>' +
                        '</div>' +
                        '<div class="mb-3 form-check">' +
                        '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                        '<label class="form-check-label" for="approved">Approved</label>' +
                        '</div>');
                }
            });
        } else if (table === 'teams') {
            var formHtml = `
        <div class="mb-3">
        <label for="name" class="form-label">Team Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
        <label for="title" class="form-label">Research Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
        <label for="area_of_expertise" class="form-label">Area of Expertise</label>
        <div class="input-group">
            <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
            </ul>
        </div>
        </div>
        <div class="mb-3">
        <label for="program_id" class="form-label">Program</label>
        <select class="form-select" id="program_id" name="program_id">
            <option value="">Loading programs...</option>
        </select>
        </div>
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
        <!-- Team members will be added here -->
        </div>
        <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
    `;
            form.append(formHtml);

            // Populate the programs dropdown for the add form
            populateProgramDropdown($('#addForm #program_id'));

            // Add team member functionality
            $('#addTeamMember').on('click', function() {
                console.log('Add Team Member button clicked');
                addNewTeamMember();
            });
        } else if (table === 'env_variables') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Key</label>' +
                '<input type="text" class="form-control" id="key" name="key" required>' +
                '</div>' +
                '<label for="name" class="form-label">Value</label>' +
                '<input type="text" class="form-control" id="value" name="value" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="created_by" class="form-label">Description</label>' +
                '<input type="text" class="form-control" id="Description" name="description" required>' +
                '</div>');
        } else if (table === 'requirements') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Name</label>' +
                '<input type="text" class="form-control" id="name" name="name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="due_date" class="form-label">Due Date</label>' +
                '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                '</div>');
        } else if (table === 'evaluations') {
            form.append('<div class="mb-3">' +
                '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="total_score" class="form-label">Total Score</label>' +
                '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="comments" class="form-label">Comments</label>' +
                '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="recommendation" class="form-label">Recommendation</label>' +
                '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                '</div>');
        } else if (table === 'defense_schedules') {
            $.ajax({
                url: 'includes/get_teams_and_staff.php', // Create this endpoint to fetch teams and staff
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var formHtml = `
                        <input type="hidden" name="table" value="${table}">
                        <div class="mb-3">
                            <label for="schedule_date" class="form-label">Schedule Date</label>
                            <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                            <small class="form-text text-muted">Select date for the defense schedule.</small>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                            <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                            <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Room</label>
                            <input type="text" class="form-control" id="room" name="room" required>
                        </div>
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                ${data.teams.map(team => `
                                    <option value="${team.id}" ${team.has_schedule ? 'disabled' : ''}>
                                        ${team.name} ${team.has_schedule ? '(Already Scheduled)' : ''}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <h5 class="mt-4">Panelists (Max 3)</h5>
                        <div id="panelists">
                            <div class="mb-3 row panelist">
                                <div class="col-sm-10">
                                    <select class="form-select" name="panelist_id[0]">
                                        <option value="">Select Panelist</option>
                                        ${data.staff.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                </div>
</div>
         </div>
</div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();
                            
                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = data.staff;

                        // Add panelist functionality
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked');
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                        });
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        $(document).off('click.addItem').on('click.addItem', '#addItem', function(e) {
            e.preventDefault();
            console.log('Add item button clicked');
            
            var form = $('#addForm');
            var table = form.find('input[name="table"]').val();
            var formData = new FormData(form[0]); // moved initialization here
            
            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                                       showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/add_items.php 
                // to prevent scheduling a team that already has a schedule on the selected date.
            }
            
            $.ajax({
                url: 'includes/add_items.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Added successfully', 'success');
                        $('#addModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // Improved error message display
                        showToast('Error', response.message || 'An unknown error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // More user-friendly error message
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If parsing fails, stick with generic message but log the real error
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            }); // Close $.ajax call
    }); // Close $(document).on('click', '#addItem', ...) handler

    // NEW: Connect the saveChanges button to trigger the edit form submission
    $(document).on('click', '#saveChanges', function(){
        console.log('DEBUG: Save changes button clicked, triggering edit form submission');
        $('#editForm').submit();
    });

    // Add button functionality
    $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function(e) {
        e.preventDefault();
        
        var table = $(this).data('table');
        console.log('Main app: Add button clicked for table:', table);
        
        // Special handling for rubrics
        if (table === 'rubrics') {
            console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
            // Let the dedicated handler in rubrics_tab.php handle this
            return true; // Allow event to bubble to other handlers
        }
        
        // Special handling for thesis_topics
        if (table === 'thesis_topics') {
            console.log('Main app: Processing thesis topic add');
            // For thesis topics, we'll let the main handler show the add modal,
            // and the thesis_topics_tab.php handler will populate it
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');
            
            form.append('<div class="mb-3">' +
                '<label for="topic" class="form-label">Topic</label>' +
                '<input type="text" class="form-control" id="topic" name="topic" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="category" class="form-label">Category</label>' +
                '<input type="text" class="form-control" id="category" name="category" required>' +
                '</div>'
            );
            
            $('#addModal').modal('show');
            return true;
        }

        // programs
        if (table === 'programs') {
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="programs">');
            form.append(`
              <div class="mb-3">
            <label class="form-label">College</label>
            <input class="form-control" name="college" required>
              </div>
              <div class="mb-3">
            <label class="form-label">Department</label>
            <input class="form-control" name="department">
              </div>
              <div class="mb-3">
            <label class="form-label">Program Name</label>
            <input class="form-control" name="name" required>
              </div>
              <div class="mb-3">
            <label class="form-label">Specialization</label>
            <input type="text" class="form-control" name="specialization">
              </div>
            `);
            $('#addModal').modal('show');
            return true;
        }
        
        // Standard handling for all other tables
        var form = $('#addForm');
        form.empty();
        form.append('<input type="hidden" name="table" value="' + table + '">');

        // Generate form fields based on table
        if (table === 'users') {
            form.append('<div class="mb-3">' +
                '<label for="username" class="form-label">Username</label>' +
                '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="email" class="form-label">Email</label>' +
                '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="password" class="form-label">Password</label>' +
                '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="first_name" class="form-label">First Name</label>' +
                '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="last_name" class="form-label">Last Name</label>' +
                '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                    '<label for="program_id" class="form-label">Program</label>' +
                    '<select class="form-select" id="program_id" name="program_id">' +
                        '<option value="">Loading programs...</option>' +
                    '</select>' +
                '</div>' +
                '<div class="mb-3 area-expertise-field" style="display:none;">' +
                '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                '<div class="input-group">' +
                    '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                    '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                    '<ul class="dropdown-menu">' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                    '</ul>' +
                '</div>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="usertype" class="form-label">User Type</label>' +
                '<select class="form-select" id="usertype" name="usertype" required>' +
                '<option value="" disabled selected>Select User Type</option>' +
                '<option value="0">Admin</option>' +
                '<option value="1">Student</option>' +
                '<option value="2">Faculty</option>' +
                '</select>' +
                '</div>' +
                '<div class="mb-3 is-part-time-field" style="display:none;">' +
                '<label class="form-label">Is Part Time</label>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                '</div>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                '</div>' +
                '</div>');
            
            // Populate the programs dropdown
            populateProgramDropdown($('#program_id'));
            
            // Add event listener for usertype change in add form
            $('#addForm').on('change', '#usertype', function() {
                if ($(this).val() == 2) {
                    $('.area-expertise-field').show();
                    $('.is-part-time-field').show();
                } else {
                    $('.area-expertise-field').hide();
                    $('.is-part-time-field').hide();
                }
            });
        } else if (table === 'thesis_topics') {
            form.append('<div class="mb-3">' +
                '<label for="topic" class="form-label">Topic</label>' +
                '<input type="text" class="form-control" id="topic" name="topic" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="category" class="form-label">Category</label>' +
                '<input type="text" class="form-control" id="category" name="category" required>' +
                '</div>'
            );
        } else if (table === 'research_titles') {
            // Fetch teams data to populate the dropdown
            $.ajax({
                url: 'includes/get_teams_and_staff.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var formHtml = `
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team Name</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="approved" name="approved">
                            <label class="form-check-label" for="approved">Approved</label>
                        </div>
                    `;
                    $('#addForm').append(formHtml);
                },
                error: function() {
                    showToast('Error', 'Unable to fetch teams data', 'error');
                    // Fallback to simple input field if AJAX fails
                    form.append('<div class="mb-3">' +
                        '<label for="team_id" class="form-label">Team ID</label>' +
                        '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                        '</div>' +
                        '<div class="mb-3">' +
                        '<label for="title" class="form-label">Title</label>' +
                        '<input type="text" class="form-control" id="title" name="title" required>' +
                        '</div>' +
                        '<div class="mb-3 form-check">' +
                        '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                        '<label class="form-check-label" for="approved">Approved</label>' +
                        '</div>');
                }
            });
        } else if (table === 'teams') {
            var formHtml = `
        <div class="mb-3">
        <label for="name" class="form-label">Team Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
        <label for="title" class="form-label">Research Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
        <label for="area_of_expertise" class="form-label">Area of Expertise</label>
        <div class="input-group">
            <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
            </ul>
        </div>
        </div>
        <div class="mb-3">
        <label for="program_id" class="form-label">Program</label>
        <select class="form-select" id="program_id" name="program_id">
            <option value="">Loading programs...</option>
        </select>
        </div>
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
        <!-- Team members will be added here -->
        </div>
        <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
    `;
            form.append(formHtml);

            // Populate the programs dropdown for the add form
            populateProgramDropdown($('#addForm #program_id'));

            // Add team member functionality
            $('#addTeamMember').on('click', function() {
                console.log('Add Team Member button clicked');
                addNewTeamMember();
            });
        } else if (table === 'env_variables') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Key</label>' +
                '<input type="text" class="form-control" id="key" name="key" required>' +
                '</div>' +
                '<label for="name" class="form-label">Value</label>' +
                '<input type="text" class="form-control" id="value" name="value" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="created_by" class="form-label">Description</label>' +
                '<input type="text" class="form-control" id="Description" name="description" required>' +
                '</div>');
        } else if (table === 'requirements') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Name</label>' +
                '<input type="text" class="form-control" id="name" name="name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="due_date" class="form-label">Due Date</label>' +
                '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                '</div>');
        } else if (table === 'evaluations') {
            form.append('<div class="mb-3">' +
                '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="total_score" class="form-label">Total Score</label>' +
                '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="comments" class="form-label">Comments</label>' +
                '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="recommendation" class="form-label">Recommendation</label>' +
                '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                '</div>');
        } else if (table === 'defense_schedules') {
            $.ajax({
                url: 'includes/get_teams_and_staff.php', // Create this endpoint to fetch teams and staff
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var formHtml = `
                        <input type="hidden" name="table" value="${table}">
                        <div class="mb-3">
                            <label for="schedule_date" class="form-label">Schedule Date</label>
                            <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                            <small class="form-text text-muted">Select date for the defense schedule.</small>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                            <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                            <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Room</label>
                            <input type="text" class="form-control" id="room" name="room" required>
                        </div>
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                ${data.teams.map(team => `
                                    <option value="${team.id}" ${team.has_schedule ? 'disabled' : ''}>
                                        ${team.name} ${team.has_schedule ? '(Already Scheduled)' : ''}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <h5 class="mt-4">Panelists (Max 3)</h5>
                        <div id="panelists">
                            <div class="mb-3 row panelist">
                                <div class="col-sm-10">
                                    <select class="form-select" name="panelist_id[0]">
                                        <option value="">Select Panelist</option>
                                        ${data.staff.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
            </div>
         </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();
                            
                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = data.staff;

                        // Add panelist functionality
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked');
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                        });
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        $(document).off('click.addItem').on('click.addItem', '#addItem', function(e) {
            e.preventDefault();
            console.log('Add item button clicked');
            
            var form = $('#addForm');
            var table = form.find('input[name="table"]').val();
            var formData = new FormData(form[0]); // moved initialization here
            
            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                                       showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/add_items.php 
                // to prevent scheduling a team that already has a schedule on the selected date.
            }
            
            $.ajax({
                url: 'includes/add_items.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Added successfully', 'success');
                        $('#addModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // Improved error message display
                        showToast('Error', response.message || 'An unknown error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // More user-friendly error message
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If parsing fails, stick with generic message but log the real error
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            }); // Close $.ajax call
    }); // Close $(document).on('click', '#addItem', ...) handler

    // NEW: Connect the saveChanges button to trigger the edit form submission
    $(document).on('click', '#saveChanges', function(){
        console.log('DEBUG: Save changes button clicked, triggering edit form submission');
        $('#editForm').submit();
    });

    // Add button functionality
    $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function(e) {
        e.preventDefault();
        
        var table = $(this).data('table');
        console.log('Main app: Add button clicked for table:', table);
        
        // Special handling for rubrics
        if (table === 'rubrics') {
            console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
            // Let the dedicated handler in rubrics_tab.php handle this
            return true; // Allow event to bubble to other handlers
        }
        
        // Special handling for thesis_topics
        if (table === 'thesis_topics') {
            console.log('Main app: Processing thesis topic add');
            // For thesis topics, we'll let the main handler show the add modal,
            // and the thesis_topics_tab.php handler will populate it
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');
            
            form.append('<div class="mb-3">' +
                '<label for="topic" class="form-label">Topic</label>' +
                '<input type="text" class="form-control" id="topic" name="topic" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="category" class="form-label">Category</label>' +
                '<input type="text" class="form-control" id="category" name="category" required>' +
                '</div>'
            );
            
            $('#addModal').modal('show');
            return true;
        }

        // programs
        if (table === 'programs') {
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="programs">');
            form.append(`
              <div class="mb-3">
            <label class="form-label">College</label>
            <input class="form-control" name="college" required>
              </div>
              <div class="mb-3">
            <label class="form-label">Department</label>
            <input class="form-control" name="department">
              </div>
              <div class="mb-3">
            <label class="form-label">Program Name</label>
            <input class="form-control" name="name" required>
              </div>
              <div class="mb-3">
            <label class="form-label">Specialization</label>
            <input type="text" class="form-control" name="specialization">
              </div>
            `);
            $('#addModal').modal('show');
            return true;
        }
        
        // Standard handling for all other tables
        var form = $('#addForm');
        form.empty();
        form.append('<input type="hidden" name="table" value="' + table + '">');

        // Generate form fields based on table
        if (table === 'users') {
            form.append('<div class="mb-3">' +
                '<label for="username" class="form-label">Username</label>' +
                '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="email" class="form-label">Email</label>' +
                '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="password" class="form-label">Password</label>' +
                '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="first_name" class="form-label">First Name</label>' +
                '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="last_name" class="form-label">Last Name</label>' +
                '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                    '<label for="program_id" class="form-label">Program</label>' +
                    '<select class="form-select" id="program_id" name="program_id">' +
                        '<option value="">Loading programs...</option>' +
                    '</select>' +
                '</div>' +
                '<div class="mb-3 area-expertise-field" style="display:none;">' +
                '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                '<div class="input-group">' +
                    '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                    '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                    '<ul class="dropdown-menu">' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                        '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                    '</ul>' +
                '</div>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="usertype" class="form-label">User Type</label>' +
                '<select class="form-select" id="usertype" name="usertype" required>' +
                '<option value="" disabled selected>Select User Type</option>' +
                '<option value="0">Admin</option>' +
                '<option value="1">Student</option>' +
                '<option value="2">Faculty</option>' +
                '</select>' +
                '</div>' +
                '<div class="mb-3 is-part-time-field" style="display:none;">' +
                '<label class="form-label">Is Part Time</label>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                '</div>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                '</div>' +
                '</div>');
            
            // Populate the programs dropdown
            populateProgramDropdown($('#program_id'));
            
            // Add event listener for usertype change in add form
            $('#addForm').on('change', '#usertype', function() {
                if ($(this).val() == 2) {
                    $('.area-expertise-field').show();
                    $('.is-part-time-field').show();
                } else {
                    $('.area-expertise-field').hide();
                    $('.is-part-time-field').hide();
                }
            });
        } else if (table === 'thesis_topics') {
            form.append('<div class="mb-3">' +
                '<label for="topic" class="form-label">Topic</label>' +
                '<input type="text" class="form-control" id="topic" name="topic" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="category" class="form-label">Category</label>' +
                '<input type="text" class="form-control" id="category" name="category" required>' +
                '</div>'
            );
        } else if (table === 'research_titles') {
            // Fetch teams data to populate the dropdown
            $.ajax({
                url: 'includes/get_teams_and_staff.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var formHtml = `
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team Name</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="approved" name="approved">
                            <label class="form-check-label" for="approved">Approved</label>
                        </div>
                    `;
                    $('#addForm').append(formHtml);
                },
                error: function() {
                    showToast('Error', 'Unable to fetch teams data', 'error');
                    // Fallback to simple input field if AJAX fails
                    form.append('<div class="mb-3">' +
                        '<label for="team_id" class="form-label">Team ID</label>' +
                        '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                        '</div>' +
                        '<div class="mb-3">' +
                        '<label for="title" class="form-label">Title</label>' +
                        '<input type="text" class="form-control" id="title" name="title" required>' +
                        '</div>' +
                        '<div class="mb-3 form-check">' +
                        '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                        '<label class="form-check-label" for="approved">Approved</label>' +
                        '</div>');
                }
            });
        } else if (table === 'teams') {
            var formHtml = `
        <div class="mb-3">
        <label for="name" class="form-label">Team Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
        <label for="title" class="form-label">Research Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
        <label for="area_of_expertise" class="form-label">Area of Expertise</label>
        <div class="input-group">
            <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
            </ul>
        </div>
        </div>
        <div class="mb-3">
        <label for="program_id" class="form-label">Program</label>
        <select class="form-select" id="program_id" name="program_id">
            <option value="">Loading programs...</option>
        </select>
        </div>
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
        <!-- Team members will be added here -->
        </div>
        <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
    `;
            form.append(formHtml);

            // Populate the programs dropdown for the add form
            populateProgramDropdown($('#addForm #program_id'));

            // Add team member functionality
            $('#addTeamMember').on('click', function() {
                console.log('Add Team Member button clicked');
                addNewTeamMember();
            });
        } else if (table === 'env_variables') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Key</label>' +
                '<input type="text" class="form-control" id="key" name="key" required>' +
                '</div>' +
                '<label for="name" class="form-label">Value</label>' +
                '<input type="text" class="form-control" id="value" name="value" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="created_by" class="form-label">Description</label>' +
                '<input type="text" class="form-control" id="Description" name="description" required>' +
                '</div>');
        } else if (table === 'requirements') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Name</label>' +
                '<input type="text" class="form-control" id="name" name="name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="due_date" class="form-label">Due Date</label>' +
                '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                '</div>');
        } else if (table === 'evaluations') {
            form.append('<div class="mb-3">' +
                '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="total_score" class="form-label">Total Score</label>' +
                '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="comments" class="form-label">Comments</label>' +
                '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="recommendation" class="form-label">Recommendation</label>' +
                '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                '</div>');
        } else if (table === 'defense_schedules') {
            $.ajax({
                url: 'includes/get_teams_and_staff.php', // Create this endpoint to fetch teams and staff
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var formHtml = `
                        <input type="hidden" name="table" value="${table}">
                        <div class="mb-3">
                            <label for="schedule_date" class="form-label">Schedule Date</label>
                            <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                            <small class="form-text text-muted">Select date for the defense schedule.</small>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                            <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                            <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Room</label>
                            <input type="text" class="form-control" id="room" name="room" required>
                        </div>
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                ${data.teams.map(team => `
                                    <option value="${team.id}" ${team.has_schedule ? 'disabled' : ''}>
                                        ${team.name} ${team.has_schedule ? '(Already Scheduled)' : ''}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <h5 class="mt-4">Panelists (Max 3)</h5>
                        <div id="panelists">
                            <div class="mb-3 row panelist">
                                <div class="col-sm-10">
                                    <select class="form-select" name="panelist_id[0]">
                                        <option value="">Select Panelist</option>
                                        ${data.staff.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                            </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();
                            
                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = data.staff;

                        // Add panelist functionality
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked');
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                        });
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        // NEW: Add bulk row functionality
        $(document).off('click.addBulkRow').on('click.addBulkRow', '#addBulkRow', function(){
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
        $(document).off('click.downloadCsvTemplate').on('click.downloadCsvTemplate', '#downloadCsvTemplate', function(e) { 
            e.preventDefault();        
            const csvContent = 'ID,Name,Program,No Username\\n';
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'users_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // NEW: Bulk Add Teams functionality
        $(document).off('click.bulkAddTeamsBtn').on('click.bulkAddTeamsBtn', '.bulk-add-teams-btn', function(e) {
            e.preventDefault();
            console.log('Bulk Add Teams button clicked');
            $('#bulkAddTeamsModal').modal('show');
        });

        $(document).off('click.addBulkTeamsRow').on('click.addBulkTeamsRow', '#addBulkTeamsRow', function(){
            let count = parseInt($('#teamsRowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTeamsTable tbody tr').length;
                var newRow = `
                  <tr>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][name]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][title]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][area_of_expertise]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][program]"></td>
                  </tr>
                `;
                $('#bulkAddTeamsTable tbody').append(newRow);
            }
        });

        $(document).off('submit.bulkAddTeamsForm').on('submit.bulkAddTeamsForm', '#bulkAddTeamsForm', function(e){
            e.preventDefault();
            console.log('Bulk Add Teams form submitted');
            
            var form = $('#bulkAddTeamsForm');
            var formData = new FormData(form[0]);
            
            // If pasted bulk text is provided, append it
            var bulkText = $('#bulkTeamsTextInput').val().trim();
            if(bulkText !== ""){
                formData.append('bulk_teams', bulkText);
            }
            
            $.ajax({
                url: 'includes/bulk_add_teams.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Teams added successfully', 'success');
                        $('#bulkAddTeamsModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // More descriptive error message
                        showToast('Error', response.message || 'Failed to add teams. Please check your data and try again.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // User-friendly error message
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            });
        });

        // Event handler for preset buttons in area of expertise fields
        $(document).on('click', '.area-option', function(e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="area_of_expertise"]').val(presetValue);
        });

        // Event handler for preset buttons in program fields
        $(document).on('click', '.program-option', function(e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="program"]').val(presetValue);
        });

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
        // Collect all selected panelist IDs
        var selectedIds = [];
        $('#panelists .panelist select').each(function() {
            var val = $(this).val();
            if (val) selectedIds.push(val);
        });

        $('#panelists .panelist select').each(function() {
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
        // Check current number of panelists
        var currentPanelistCount = $('#panelists .panelist').length;
        if (currentPanelistCount >= 3) {
            showToast('Warning', 'Maximum of 3 panelists allowed.', 'warning');
            return; // Stop if limit is reached
        }

        // Determine the next index based on existing panelists
        var currentIndices = $('#panelists .panelist select').map(function() {
            var name = $(this).attr('name');
            var match = name.match(/\[(\d+)\]/);
            return match ? parseInt(match[1], 10) : -1;
        }).get();
        var nextIndex = currentIndices.length > 0 ? Math.max(...currentIndices) + 1 : 0;

        var newPanelistHtml = `
        <div class="mb-3 row panelist">
            <div class="col-sm-10">
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

        $('#panelists').append(newPanelistHtml);
        console.log('New panelist added to DOM with name:', `panelist_id[${nextIndex}]`);

        // Disable button if limit is now reached
        if ($('#panelists .panelist').length >= 3) {
            $('#addPanelist').prop('disabled', true);
        }
        // Update dropdowns to enforce unique selection
        updatePanelistDropdowns();
    }

    // Optionally, update existing panelist entries to have unique indices
    $(document).ready(function() {
        $('#panelists .panelist').each(function(index) {
            $(this).find('select').attr('name', `panelist_id[${index}]`);
        });
    });

    // Define addNewTeamMember function globally
    function addNewTeamMember() {
        $.ajax({
            url: 'includes/get_users.php',
            method: 'GET',
            dataType: 'json',
            success: function(users) {
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
                            <option value="member">Member</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                    </div>
                </div>
                `;
                $('#teamMembers').append(newMemberHtml);
                $('#teamMembers .toggle-input').last().on('click', function(e) {
                    e.preventDefault();
                    var $select = $(this).siblings('.user-select');
                    var $input = $(this).siblings('.new-username-input');
                    if ($select.is(':visible')) {
                        $select.hide();
                        $input.show();
                        $(this).text('Switch to select');
                    } else {
                        $input.hide();
                        $select.show();
                        $(this).text('Switch to manual');
                    }
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error loading users');
            }
        });
    }

    // Remove team member functionality
    $(document).on('click', '.remove-member', function() {
        var teamMember = $(this).closest('.team-member');
        var userId = teamMember.data('user-id');
        var teamId = $('input[name="id"]').val();
        if (userId && teamId) {
            if (confirm('Are you sure you want to remove this team member? This action cannot be undone.')) {
                $.ajax({
                    url: 'includes/remove_team_member.php',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        team_id: teamId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            teamMember.remove();
                            alert('Team member removed successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error: Unable to remove team member');
                    }
                });
            }
        } else {
            // If it's a new member (not yet saved to database), just remove from form
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
    style="min-width: 300px; opacity: 1; background-color: ${type === 'success' ? 'var(--main-accent)' : 'var(--main-btn-del)'};">
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
                            optgroup = $('<optgroup>', { label: currentCollege });
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
            $('body').append('<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
        }

        // Edit button functionality
        $(document).off('click.editBtn').on('click.editBtn', '.edit-btn', function(e) {
            e.preventDefault();

            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Main app: Edit button clicked. Table:', table, 'ID:', id);

            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric edit to rubrics_tab.php handler');
                // Let the edit-rubric-btn handler in rubrics_tab.php handle this
                $('.edit-rubric-btn[data-id="' + id + '"]').trigger('click');
                return true; // Allow event to bubble to other handlers
            }

            // Continue with normal edit handling for other tables
            $.ajax({
                url: 'includes/get_item_details.php',
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response data:', response)
                    if (response.success) {
                        var form = $('#editForm');
                        form.empty();

                        // Add hidden inputs for table and id
                        form.append('<input type="hidden" name="table" value="' + table + '">');
                        form.append('<input type="hidden" name="id" value="' + id + '">');

                        if (table === 'users') {
                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="usertype" class="form-label">User Type</label>
                                    <select class="form-select" id="usertype" name="usertype">
                                        <option value="0"${response.data.usertype == 0 ? ' selected' : ''}>Admin</option>
                                        <option value="1"${response.data.usertype == 1 ? ' selected' : ''}>Student</option>
                                        <option value="2"${response.data.usertype == 2 ? ' selected' : ''}>Faculty</option>
                                    </select>
                                </div>
                            `;

                            var fieldsToShow = ['username', 'email', 'first_name', 'last_name', 'gender', 'headline', 'bio'];

                            fieldsToShow.forEach(function(key) {
                                var value = response.data[key] || '';
                                var inputType = (key === 'email') ? 'email' : 'text';
                                var label = key.replace('_', ' ').charAt(0).toUpperCase() + key.slice(1);

                                if (key === 'bio') {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <textarea class="form-control" id="${key}" name="${key}" rows="3">${value}</textarea>
                                        </div>
                                    `;
                                } else {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <input type="${inputType}" class="form-control" id="${key}" name="${key}" value="${value}">
                                        </div>
                                    `;
                                }
                            });

                            // Replace hardcoded program dropdown with select element
                            formHtml += `
                                <div class="mb-3">
                                    <label for="program_id" class="form-label">Program</label>
                                    <select class="form-select" id="program_id" name="program_id">
                                        <option value="">Loading programs...</option>
                                    </select>
                                </div>
                            `;

                            // Area of expertise field
                            formHtml += `
                                <div class="mb-3 area-expertise-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                    <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                                        </ul>
                                    </div>
                                </div>
                            `;

                            // Add Is Part Time radio buttons for faculty (usertype 2)
                            formHtml += `
                            <div class="mb-3 is-part-time-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                <label class="form-label">Is Part Time</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_parttime" id="fullTime" value="0" ${response.data.is_parttime == 0 || response.data.is_parttime == null ? 'checked' : ''}>
                                    <label class="form-check-label" for="fullTime">Full Time</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_parttime" id="partTime" value="1" ${response.data.is_parttime == 1 ? 'checked' : ''}>
                                    <label class="form-check-label" for="partTime">Part Time</label>
                                </div>
                            </div>
                            `;

                            form.html(formHtml);

                            // Populate the programs dropdown
                            populateProgramDropdown($('#program_id'), response.data.program_id);

                            $('#usertype').on('change', function() {
                                if ($(this).val() == 2) {
                                    $('.area-expertise-field').show();
                                    $('.is-part-time-field').show();
                                } else {
                                    $('.area-expertise-field').hide();
                                    $('.is-part-time-field').hide();
                                }
                            });
                        } else if (table === 'teams') {
                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="name" class="form-label">Team Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name}">
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Research Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                            </div>
                            <div class="mb-3">
                                <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    <option value="">Loading programs...</option>
                                </select>
                            </div>
                            <h5 class="mt-4">Team Members</h5>
                            <div id="teamMembers">
`;

                            response.data.members.forEach(function(member, index) {
                                formHtml += `
                                <div class="mb-3 row team-member" data-user-id="${member.id}">
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
                                        <input type="hidden" name="member_ids[]" value="${member.id}">
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-select" name="member_role[]">
                                            <option value="adviser"${member.role === 'adviser' ? ' selected' : ''}>Adviser</option>
                                            <option value="leader"${member.role === 'leader' ? ' selected' : ''}>Leader</option>
                                            <option value="member"${member.role === 'member' ? ' selected' : ''}>Member</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                                    </div>
                                </div>
                            `;
                            });

                            formHtml += `
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
                        `;

                        form.html(formHtml);

                        // Populate the programs dropdown for the edit form, selecting the current value
                        populateProgramDropdown($('#editForm #program_id'), response.data.program_id);

                        // Add team member functionality
                        $('#addTeamMember').on('click', function() {
                            console.log('Add Team Member button clicked');
                            addNewTeamMember();
                        });

                    } else if (table === 'programs') {
                        var d = response.data;
                        var html=`
                          <input type="hidden" name="table" value="programs">
                          <input type="hidden" name="id" value="${id}">
                          <div class="mb-3">
                            <label class="form-label">College</label>
                            <input class="form-control" name="college" id="college" value="${d.college || ''}" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input class="form-control" name="department" id="department" value="${d.department || ''}">
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Program Name</label>
                            <input class="form-control" name="name" id="name" value="${d.name || ''}" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input class="form-control" name="specialization" id="specialization" value="${d.specialization || ''}">
                          </div>`;
                        form.html(html);
                        $('#editModal').modal('show');
                        return;
                    } else if (table === 'thesis_topics') {
                        var formHtml = `
                        <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="topic" class="form-label">Topic</label>
                                <input type="text" class="form-control" id="topic" name="topic" value="${response.data.topic}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" value="${response.data.category}">
                            </div>
                        `;
                        form.html(formHtml);
                    } else if (table === 'research_titles') {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${response.teams ? response.teams.map(team => `<option value="${team.id}"${team.id == response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('') : ''}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                            </div>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    <option value="">Loading programs...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="approved" name="approved_at" value="1" ${response.data.approved_at ? 'checked' : ''}>
                                    <label class="form-check-label" for="approved_at">Approved</label>
                                </div>
                            </div>
                        `;
                        form.html(formHtml);

                        // Populate the programs dropdown
                        populateProgramDropdown($('#program_id'), response.data.program_id);
                    } else if (table === 'env_variables') {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="key" class="form-label">Key</label>
                                <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                            </div>
                            <div class="mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="text" class="form-control" id="value" name="value" value="${response.data.value}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>
                        `;

                        // Handle special cases for DB_PASSWORD and MAIL_ENCRYPTION
                        if (response.data.key === 'DB_PASSWORD' || response.data.key === 'MAIL_ENCRYPTION') {
                            $('#value').val(''); // Clear the value field
                            form.html(`
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="key" class="form-label">Key</label>
                                <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                            </div>
                            <div class="mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="text" class="form-control" id="value" name="value" value="${response.data.value}">
                            </div>
                            <div class="mb-3">
                                <label for="old_value" class="form-label">Old Value</label>
                                <input type="password" class="form-control" id="old_value" name="old_value" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>

                        `);
                        } else {
                            form.html(formHtml);
                        }
                    } else if (table === 'defense_schedules') {
                        var formHtml = `
                    <input type="hidden" name="table" value="${table}">
                    <input type="hidden" name="id" value="${id}">
                    <div class="mb-3">
                        <label for="schedule_date" class="form-label">Schedule Date</label>
                        <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required value="${response.data.schedule_date || ''}">
                        <small class="form-text text-muted">Select date for the defense schedule.</small>
                    </div>
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required value="${response.data.start_time || ''}"
                            onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                        <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                    </div>
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required value="${response.data.end_time || ''}"
                            onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                        <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                    </div>
                    <div class="mb-3">
                        <label for="room" class="form-label">Room</label>
                        <input type="text" class="form-control" id="room" name="room" required value="${response.data.room || ''}">
                    </div>
                    <div class="mb-3">
                        <label for="team_id" class="form-label">Team</label>
                        <select class="form-select" id="team_id" name="team_id" required>
                        <option value="">Select Team</option>
                            ${response.teams.map(team => `<option value="${team.id}"${team.id === response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('')}
                        </select>
                    </div>
                    <h5 class="mt-4">Panelists (Max 3)</h5>
                    <div id="panelists">
                `;

                        let panelistCount = 0; // Initialize panelist count
                        if (response.data.panelists && Array.isArray(response.data.panelists)) {
                            panelistCount = response.data.panelists.length; // Get initial count
                            response.data.panelists.forEach(function(panelist, index) {
                                // Ensure unique indices for names
                                formHtml += `
                            <div class="mb-3 row panelist align-items-center" data-user-id="${panelist.id}">
                                <label class="col-sm-2 col-form-label">Panelist ${index + 1}</label>
                                <div class="col-sm-8">
                                    <select class="form-select" name="panelist_id[${index}]">
                                        <option value="">Select Panelist</option>
                                        ${response.staff.map(staff => `<option value="${staff.id}"${staff.id === panelist.id ? ' selected' : ''}>${staff.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                </div>
                            </div>
                        `;
                            });
                        } else {
                            console.warn('Panelists data is missing or not an array in the response for edit form.');
                        }

                        formHtml += `
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();

                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = response.staff;

                        // Disable "Add Panelist" button initially if limit is reached
                        if (panelistCount >= 3) {
                            $('#addPanelist').prop('disabled', true);
                        }

                        // Add panelist functionality (uses global addNewPanelist function)
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked in edit modal');
                            addNewPanelist(window.staffData); // Call the global function
                        });

                        // Remove panelist functionality (Enable add button when removing)
                        // Use event delegation on the form for dynamically added elements
                        form.off('click.removePanelist').on('click.removePanelist', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                            // Check count and enable button if below limit
                            if ($('#panelists .panelist').length < 3) {
                                $('#addPanelist').prop('disabled', false);
                            }
                            // Re-index remaining panelists to ensure sequential names
                            $('#panelists .panelist').each(function(index) {
                                $(this).find('select').attr('name', `panelist_id[${index}]`);
                                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
                            });
                            updatePanelistDropdowns(); // Update dropdowns after removal
                        });

                        // Update dropdowns initially to disable selected options in other dropdowns
                        updatePanelistDropdowns();
                    } else if (table === 'requirements') {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required>${response.data.description || ''}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="${response.data.due_date || ''}" required>
                            </div>
                        `;
                        form.html(formHtml);
                    } else if (table === 'rubrics') {
                        // Don't generate form fields - they're handled in rubrics_tab.php
                        return;
                    }
                    // Add more conditions for other tables as needed
                    $('#editModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error: Unable to fetch item details - ' + error);
            }
        });
        });

        // UPDATED: Edit form submission handler with debug logs
        $(document).on('submit', '#editForm', function(e) {
            e.preventDefault();
            console.log('DEBUG: Edit form submit event triggered');  // <-- New debug log
            var formData = new FormData(this);
            var table = formData.get('table'); // Get table name from form data

            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                    showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/edit_items.php
                // to prevent rescheduling a team that already has a schedule on the selected date.
            }


            $.ajax({
                url: 'includes/edit_items.php', // Updated path for correct endpoint
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('DEBUG: Edit response received:', response);
                    if (response.success) {
                        showToast('Success', 'Item updated successfully', 'success');
                        $('#editModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast('Error', response.message || 'Update failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('DEBUG: AJAX error in edit form:', xhr.responseText);
                    showToast('Error', 'Unable to update item: ' + error, 'error');
                }
            }); // Close $.ajax call
        }); // Close $(document).on('submit', '#editForm', ...) handler

        // NEW: Connect the saveChanges button to trigger the edit form submission
        $(document).on('click', '#saveEdit', function(){ // Changed ID to match button in modal
            console.log('DEBUG: Save changes button clicked, triggering edit form submission');
            $('#editForm').submit();
        });

        // Add button functionality
        $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function(e) {
            e.preventDefault();

            var table = $(this).data('table');
            console.log('Main app: Add button clicked for table:', table);

            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
                // Let the dedicated handler in rubrics_tab.php handle this
                return true; // Allow event to bubble to other handlers
            }

            // Special handling for thesis_topics
            if (table === 'thesis_topics') {
                console.log('Main app: Processing thesis topic add');
                // For thesis topics, we'll let the main handler show the add modal,
                // and the thesis_topics_tab.php handler will populate it
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="' + table + '">');

                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );

                $('#addModal').modal('show');
                return true;
            }

            // programs
            if (table === 'programs') {
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="programs">');
                form.append(`
                  <div class="mb-3">
                    <label class="form-label">College</label>
                    <input class="form-control" name="college" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Department</label>
                    <input class="form-control" name="department">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Program Name</label>
                    <input class="form-control" name="name" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Specialization</label>
                    <input type="text" class="form-control" name="specialization">
                  </div>
                `);
                $('#addModal').modal('show');
                return true;
            }

            // Standard handling for all other tables
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');

            // Generate form fields based on table
            if (table === 'users') {
                form.append('<div class="mb-3">' +
                    '<label for="username" class="form-label">Username</label>' +
                    '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="email" class="form-label">Email</label>' +
                    '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="password" class="form-label">Password</label>' +
                    '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="first_name" class="form-label">First Name</label>' +
                    '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="last_name" class="form-label">Last Name</label>' +
                    '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label for="program_id" class="form-label">Program</label>' +
                        '<select class="form-select" id="program_id" name="program_id">' +
                            '<option value="">Loading programs...</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="mb-3 area-expertise-field" style="display:none;">' +
                    '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                    '<div class="input-group">' +
                        '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                        '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                        '<ul class="dropdown-menu">' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                            '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                        '</ul>' +
                    '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="usertype" class="form-label">User Type</label>' +
                    '<select class="form-select" id="usertype" name="usertype" required>' +
                    '<option value="" disabled selected>Select User Type</option>' +
                    '<option value="0">Admin</option>' +
                    '<option value="1">Student</option>' +
                    '<option value="2">Faculty</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="mb-3 is-part-time-field" style="display:none;">' +
                    '<label class="form-label">Is Part Time</label>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                    '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                    '</div>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                    '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                    '</div>' +
                    '</div>');

                // Populate the programs dropdown
                populateProgramDropdown($('#program_id'));

                // Add event listener for usertype change in add form
                $('#addForm').on('change', '#usertype', function() {
                    if ($(this).val() == 2) {
                        $('.area-expertise-field').show();
                        $('.is-part-time-field').show();
                    } else {
                        $('.area-expertise-field').hide();
                        $('.is-part-time-field').hide();
                    }
                });
            } else if (table === 'thesis_topics') {
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );
            } else if (table === 'research_titles') {
                // Fetch teams data to populate the dropdown
                $.ajax({
                    url: 'includes/get_teams_and_staff.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var formHtml = `
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team Name</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="approved" name="approved">
                                <label class="form-check-label" for="approved">Approved</label>
                            </div>
                        `;
                        $('#addForm').append(formHtml);
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams data', 'error');
                        // Fallback to simple input field if AJAX fails
                        form.append('<div class="mb-3">' +
                            '<label for="team_id" class="form-label">Team ID</label>' +
                            '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '<label for="title" class="form-label">Title</label>' +
                            '<input type="text" class="form-control" id="title" name="title" required>' +
                            '</div>' +
                            '<div class="mb-3 form-check">' +
                            '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                            '<label class="form-check-label" for="approved">Approved</label>' +
                            '</div>');
                    }
                });
            } else if (table === 'teams') {
                var formHtml = `
            <div class="mb-3">
            <label for="name" class="form-label">Team Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
            <label for="title" class="form-label">Research Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
            <label for="area_of_expertise" class="form-label">Area of Expertise</label>
            <div class="input-group">
                <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                    <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                </ul>
            </div>
            </div>
            <div class="mb-3">
            <label for="program_id" class="form-label">Program</label>
            <select class="form-select" id="program_id" name="program_id">
                <option value="">Loading programs...</option>
            </select>
            </div>
            <h5 class="mt-4">Team Members</h5>
            <div id="teamMembers">
            <!-- Team members will be added here -->
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
        `;
                form.append(formHtml);

                // Populate the programs dropdown for the add form
                populateProgramDropdown($('#addForm #program_id'));

                // Add team member functionality
                $('#addTeamMember').on('click', function() {
                    console.log('Add Team Member button clicked');
                    addNewTeamMember();
                });
            } else if (table === 'env_variables') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Key</label>' +
                    '<input type="text" class="form-control" id="key" name="key" required>' +
                    '</div>' +
                    '<label for="name" class="form-label">Value</label>' +
                    '<input type="text" class="form-control" id="value" name="value" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="created_by" class="form-label">Description</label>' +
                    '<input type="text" class="form-control" id="Description" name="description" required>' +
                    '</div>');
            } else if (table === 'requirements') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="due_date" class="form-label">Due Date</label>' +
                    '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                    '</div>');
            } else if (table === 'evaluations') {
                form.append('<div class="mb-3">' +
                    '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                    '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                    '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="total_score" class="form-label">Total Score</label>' +
                    '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="comments" class="form-label">Comments</label>' +
                    '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="recommendation" class="form-label">Recommendation</label>' +
                    '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                    '</div>');
            } else if (table === 'defense_schedules') {
                $.ajax({
                    url: 'includes/get_teams_and_staff.php', // Create this endpoint to fetch teams and staff
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <div class="mb-3">
                                <label for="schedule_date" class="form-label">Schedule Date</label>
                                <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                                <small class="form-text text-muted">Select date for the defense schedule.</small>
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="room" class="form-label">Room</label>
                                <input type="text" class="form-control" id="room" name="room" required>
                            </div>
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                    ${data.teams.map(team => `
                                        <option value="${team.id}" ${team.has_schedule ? 'disabled' : ''}>
                                            ${team.name} ${team.has_schedule ? '(Already Scheduled)' : ''}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            <h5 class="mt-4">Panelists (Max 3)</h5>
                            <div id="panelists">
                                <div class="mb-3 row panelist">
                                    <div class="col-sm-10">
                                        <select class="form-select" name="panelist_id[0]">
                                            <option value="">Select Panelist</option>
                                            ${data.staff.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                        `;

                        form.html(formHtml);

                        // Initialize the date picker with same options as in defense_schedules_tab.php
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd', // Match MySQL date format
                            multidate: false,      // Single date selection for defense schedule
                            startDate: new Date(), // Prevent selecting previous dates
                            todayHighlight: true,  // Highlight today's date
                            autoclose: true        // Close calendar after selection
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function() {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();

                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val(''); // Clear the current field
                            }
                        });

                        // Store staff data for addNewPanelist function
                        window.staffData = data.staff;

                        // Add panelist functionality
                        $('#addPanelist').on('click', function() {
                            console.log('Add Panelist button clicked');
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function() {
                            $(this).closest('.panelist').remove();
                            // Re-enable add button if below limit
                            if ($('#panelists .panelist').length < 3) {
                                $('#addPanelist').prop('disabled', false);
                            }
                            // Re-index remaining panelists
                            $('#panelists .panelist').each(function(index) {
                                $(this).find('select').attr('name', `panelist_id[${index}]`);
                                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
                            });
                            updatePanelistDropdowns();
                        });
                        updatePanelistDropdowns(); // Initial update
                    },
                    error: function() {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        // Add form submission handler
        $(document).off('click.addItem').on('click.addItem', '#addItem', function(e) {
            e.preventDefault();
            console.log('Add item button clicked');

            var form = $('#addForm');
            var table = form.find('input[name="table"]').val();
            var formData = new FormData(form[0]); // moved initialization here

            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                                       showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/add_items.php
                // to prevent scheduling a team that already has a schedule on the selected date.
            }

            $.ajax({
                url: 'includes/add_items.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Added successfully', 'success');
                        $('#addModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // Improved error message display
                        showToast('Error', response.message || 'An unknown error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // More user-friendly error message
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If parsing fails, stick with generic message but log the real error
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            }); // Close $.ajax call
        }); // Close $(document).on('click', '#addItem', ...) handler

        // Delete button functionality
        $(document).off('click.deleteBtn').on('click.deleteBtn', '.delete-btn', function(e) {
            e.preventDefault();
            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Delete button clicked. Table:', table, 'ID:', id);

            // Populate the modal
            $('#deleteTableName').text(table);
            $('#deleteItemId').text(id);

            // Store data on the confirm button for later use
            $('#confirmDelete').data('table', table);
            $('#confirmDelete').data('id', id);

            // Show the modal
            $('#deleteConfirmModal').modal('show');
        });

        // Confirm Delete button functionality
        $(document).off('click.confirmDelete').on('click.confirmDelete', '#confirmDelete', function() {
            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Confirm delete clicked. Table:', table, 'ID:', id);

            $.ajax({
                url: 'includes/delete_item.php', // Ensure you have this endpoint
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Item deleted successfully', 'success');
                        $('#deleteConfirmModal').modal('hide');
                        // Optionally remove the row from the table without reloading
                        // $('tr[data-id="' + id + '"]').remove(); // Requires rows to have data-id attribute
                        setTimeout(function() {
                            location.reload(); // Reload to reflect changes
                        }, 2000);
                    } else {
                        showToast('Error', response.message || 'Deletion failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'Unable to delete item: ' + error, 'error');
                }
            });
        });


        // NEW: Add bulk row functionality
        $(document).off('click.addBulkRow').on('click.addBulkRow', '#addBulkRow', function(){
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
        $(document).off('click.downloadCsvTemplate').on('click.downloadCsvTemplate', '#downloadCsvTemplate', function(e) {
            e.preventDefault();
            const csvContent = 'ID,Name,Program,No Username\\n';
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'users_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // NEW: Bulk Add Teams functionality
        $(document).off('click.bulkAddTeamsBtn').on('click.bulkAddTeamsBtn', '.bulk-add-teams-btn', function(e) {
            e.preventDefault();
            console.log('Bulk Add Teams button clicked');
            $('#bulkAddTeamsModal').modal('show');
        });

        $(document).off('click.addBulkTeamsRow').on('click.addBulkTeamsRow', '#addBulkTeamsRow', function(){
            let count = parseInt($('#teamsRowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTeamsTable tbody tr').length;
                var newRow = `
                  <tr>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][name]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][title]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][area_of_expertise]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][program]"></td>
                  </tr>
                `;
                $('#bulkAddTeamsTable tbody').append(newRow);
            }
        });

        $(document).off('submit.bulkAddTeamsForm').on('submit.bulkAddTeamsForm', '#bulkAddTeamsForm', function(e){
            e.preventDefault();
            console.log('Bulk Add Teams form submitted');

            var form = $('#bulkAddTeamsForm');
            var formData = new FormData(form[0]);

            // If pasted bulk text is provided, append it
            var bulkText = $('#bulkTeamsTextInput').val().trim();
            if(bulkText !== ""){
                formData.append('bulk_teams', bulkText);
            }

            $.ajax({
                url: 'includes/bulk_add_teams.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Teams added successfully', 'success');
                        $('#bulkAddTeamsModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // More descriptive error message
                        showToast('Error', response.message || 'Failed to add teams. Please check your data and try again.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // User-friendly error message
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            });
        });

        // Event handler for preset buttons in area of expertise fields
        $(document).on('click', '.area-option', function(e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="area_of_expertise"]').val(presetValue);
        });

        // Event handler for preset buttons in program fields
        $(document).on('click', '.program-option', function(e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="program"]').val(presetValue);
        });

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
    $('#editModal, #addModal').on('shown.bs.modal', function () {
        var $panelistContainer = $(this).find('#panelists');
        if ($panelistContainer.length) {
            $panelistContainer.find('.panelist').each(function(index) {
                $(this).find('select').attr('name', `panelist_id[${index}]`);
                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
            });
            // Add change listener to all panelist dropdowns within this modal
            $panelistContainer.find('select').off('change.updateDropdowns').on('change.updateDropdowns', updatePanelistDropdowns);
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
            if (confirm('Are you sure you want to remove this team member from the team? This action cannot be undone.')) {
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
          <button type="button" class="btn btn-primary" id="saveEdit">Save Changes</button> <!-- Changed type to button, ID to saveEdit -->
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
                    <button type="button" class="btn btn-primary" id="addItem">Add Item</button> <!-- Changed type to button -->
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Option to upload excel file -->
                    <div class="mb-3">
                        <label for="bulkFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkFileInput" name="bulkFile" accept=".csv, .xls, .xlsx">
                    </div>
                    <!-- NEW: Option to paste bulk data -->
                    <div class="mb-3">
                        <label for="bulkTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTextInput" name="bulkTextInput" rows="5" placeholder="Paste CSV data here (ID,Name,Program,No Username)"></textarea>
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
                        <input type="number" id="rowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
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
<div class="modal fade" id="bulkAddTeamsModal" tabindex="-1" aria-labelledby="bulkAddTeamsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="bulkAddTeamsForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkAddTeamsModalLabel">Bulk Add Teams</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Option to upload excel file -->
                    <div class="mb-3">
                        <label for="bulkTeamsFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkTeamsFileInput" name="bulkTeamsFile" accept=".csv, .xls, .xlsx">
                    </div>
                    <!-- NEW: Option to paste bulk data -->
                    <div class="mb-3">
                        <label for="bulkTeamsTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTeamsTextInput" name="bulkTeamsTextInput" rows="5" placeholder="Paste CSV data here (Team Name,Research Title,Area of Expertise,Program)"></textarea>
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
                        <input type="number" id="teamsRowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
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
<!-- Bulk Add Users Modal -->
<div class="modal fade" id="bulkAddModal" tabindex="-1" aria-labelledby="bulkAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="bulkAddForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkAddModalLabel">Bulk Add Users</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Option to upload excel file -->
                    <div class="mb-3">
                        <label for="bulkFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkFileInput" name="bulkFile" accept=".csv, .xls, .xlsx">
                    </div>
                    <!-- NEW: Option to paste bulk data -->
                    <div class="mb-3">
                        <label for="bulkTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTextInput" name="bulkTextInput" rows="5" placeholder="Paste CSV data here"></textarea>
                    </div>
                    <hr>
                    <!-- Table for manual data input -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bulkAddTable<tbody>
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
                        <input type="number" id="rowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
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
<div class="modal fade" id="bulkAddTeamsModal" tabindex="-1" aria-labelledby="bulkAddTeamsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="bulkAddTeamsForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkAddTeamsModalLabel">Bulk Add Teams</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Option to upload excel file -->
                    <div class="mb-3">
                        <label for="bulkTeamsFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkTeamsFileInput" name="bulkTeamsFile" accept=".csv, .xls, .xlsx">
                    </div>
                    <!-- NEW: Option to paste bulk data -->
                    <div class="mb-3">
                        <label for="bulkTeamsTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTeamsTextInput" name="bulkTeamsTextInput" rows="5" placeholder="Paste CSV data here"></textarea>
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
                        <input type="number" id="teamsRowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
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