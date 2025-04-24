<script>
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

                            var fieldsToShow = ['username', 'email', 'first_name', 'last_name', 'program', 'area_of_expertise','gender', 'headline', 'bio'];

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
                                } else if (key === 'program'){ 
                                    formHtml += `
                                    <div class="mb-3">
            <label for="program" class="form-label">Program</label>
            <div class="input-group">
                <input type="text" class="form-control" id="program" name="program" value="${response.data.program || ''}">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                <ul class="dropdown-menu">
                    <li><h6 class="dropdown-header">Department of Architecture</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Architecture">Bachelor of Science in Architecture</a></li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><h6 class="dropdown-header">Department of Computer Studies</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Data Science">Bachelor of Science in Computer Science with specialization in Data Science</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Software Engineering">Bachelor of Science in Computer Science with specialization in Software Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Network and Information Security">Bachelor of Science in Information Technology with specialization in Network and Information Security</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Web and Mobile Technology">Bachelor of Science in Information Technology with specialization in Web and Mobile Technology</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Library and Information Science">Bachelor of Library and Information Science</a></li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><h6 class="dropdown-header">Department of Engineering</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Aeronautical Engineering">Bachelor of Science in Aeronautical Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management">Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Structural Engineering">Bachelor of Science in Civil Engineering with specialization in Structural Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Transportation Engineering">Bachelor of Science in Civil Engineering with specialization in Transportation Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Engineering Technology with a major in Construction Technology and Management">Bachelor of Engineering Technology with a major in Construction Technology and Management</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electrical Engineering">Bachelor of Science in Electrical Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Industrial Engineering">Bachelor of Science in Industrial Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</a></li>
                </ul>
            </div>
        </div>
                                    `;
                                }else if (key === 'area_of_expertise') {
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
                                } else {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <input type="${inputType}" class="form-control" id="${key}" name="${key}" value="${value}">
                                        </div>
                                    `;
                                }
                            });
                            
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

                            // Add event listener to show/hide area of expertise field when usertype changes
                            $('#usertype').on('change', function() {
                                if ($(this).val() == 2) {
                                    $('.area-expertise-field').show();
                                    $('.is-part-time-field').show();
                                } else {
                                    $('.area-expertise-field').hide();
                                    $('.is-part-time-field').hide();
                                }
                            });
                        } else if (table === 'programs') {
                            var d = response.data;
                            var html=`
                              <input type="hidden" name="table" value="programs">
                              <input type="hidden" name="id" value="${id}">
                              <div class="mb-3">
                                <label class="form-label">College</label>
                                <input class="form-control" name="college" id="college" value="${d.college}" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input class="form-control" name="department" id="department" value="${d.department||''}">
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Program Name</label>
                                <input class="form-control" name="name" id="name" value="${d.name}" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Parent Program ID</label>
                                <input type="number" class="form-control" name="parent_id" id="parent_id" value="${d.parent_id||''}">
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
            <label for="program" class="form-label">Program</label>
            <div class="input-group">
                <input type="text" class="form-control" id="program" name="program" value="${response.data.program || ''}">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                <ul class="dropdown-menu">
                    <li><h6 class="dropdown-header">Department of Architecture</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Architecture">Bachelor of Science in Architecture</a></li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><h6 class="dropdown-header">Department of Computer Studies</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Data Science">Bachelor of Science in Computer Science with specialization in Data Science</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Software Engineering">Bachelor of Science in Computer Science with specialization in Software Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Network and Information Security">Bachelor of Science in Information Technology with specialization in Network and Information Security</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Web and Mobile Technology">Bachelor of Science in Information Technology with specialization in Web and Mobile Technology</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Library and Information Science">Bachelor of Library and Information Science</a></li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><h6 class="dropdown-header">Department of Engineering</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Aeronautical Engineering">Bachelor of Science in Aeronautical Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management">Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Structural Engineering">Bachelor of Science in Civil Engineering with specialization in Structural Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Transportation Engineering">Bachelor of Science in Civil Engineering with specialization in Transportation Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Engineering Technology with a major in Construction Technology and Management">Bachelor of Engineering Technology with a major in Construction Technology and Management</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electrical Engineering">Bachelor of Science in Electrical Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Industrial Engineering">Bachelor of Science in Industrial Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</a></li>
                </ul>
            </div>
        </div>
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
    `;

                            response.data.members.forEach(function(member, index) {
                                formHtml += `
            <div class="mb-3 row team-member" data-user-id="${member.id}">
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
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

                            // Add team member functionality
                            $('#addTeamMember').on('click', function() {
                                console.log('Add Team Member button clicked');
                                addNewTeamMember();
                            });

                        } else if (table === 'research_titles') {
                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="team_id" class="form-label">Team ID</label>
                                    <input type="text" class="form-control" id="team_id" name="team_id" value="${response.data.team_id}">
                                </div>  ${response.teams ? response.teams.map(team => `<option value="${team.id}"${team.id == response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('') : ''}
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="approved" name="approved" value="1" ${response.data.approved_at ? 'checked' : ''}>
                                        <label class="form-check-label" for="approved">Approved</label>
                                    </div>
                                </div>
                            `;
                            form.html(formHtml);
    // Duplicate teams section removed.
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
                        <h5 class="mt-4">Panelists</h5>
                        <div id="panelists">
                    `;

                            if (response.data.panelists) {
                                response.data.panelists.forEach(function(panelist, index) {

                                    formHtml += `
                                <div class="mb-3 row panelist" data-user-id="${panelist.id}">
                                    <div class="col-sm-10">
                                        <select class="form-select" name="panelist_id[${index}]">
                                            ${response.staff.map(staff => `<option value="${staff.id}"${staff.id === panelist.id ? ' selected' : ''}>${staff.name}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                    </div>
                                </div>
                            `;
                                    index++;
                                });
                            } else {
                                console.error('Panelists data is missing in the response');
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

                            // Add panelist functionality
                            $('#addPanelist').on('click', function() {
                                console.log('Add Panelist button clicked');
                                addNewPanelist(window.staffData);
                            });

                            // Remove panelist functionality
                            $(document).on('click', '.remove-panelist', function() {
                                $(this).closest('.panelist').remove();
                            });
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
            });
        });

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
                    <label class="form-label">Parent Program ID</label>
                    <input type="number" class="form-control" name="parent_id">
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
                    // New Program field inserted before area of expertise
                    '<div class="mb-3">' +
                        '<label for="program" class="form-label">Program</label>' +
                        '<div class="input-group">' +
                            '<input type="text" class="form-control" id="program" name="program" placeholder="Enter program">' +
                            '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                            '<ul class="dropdown-menu">' +
                                '<li><h6 class="dropdown-header">Department of Architecture</h6></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Architecture">Bachelor of Science in Architecture</a></li>' +
                                '<li><div class="dropdown-divider"></div></li>' +
                                '<li><h6 class="dropdown-header">Department of Computer Studies</h6></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Data Science">Bachelor of Science in Computer Science with specialization in Data Science</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Software Engineering">Bachelor of Science in Computer Science with specialization in Software Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Network and Information Security">Bachelor of Science in Information Technology with specialization in Network and Information Security</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Web and Mobile Technology">Bachelor of Science in Information Technology with specialization in Web and Mobile Technology</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Library and Information Science">Bachelor of Library and Information Science</a></li>' +
                                '<li><div class="dropdown-divider"></div></li>' +
                                '<li><h6 class="dropdown-header">Department of Engineering</h6></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Aeronautical Engineering">Bachelor of Science in Aeronautical Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management">Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Structural Engineering">Bachelor of Science in Civil Engineering with specialization in Structural Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Transportation Engineering">Bachelor of Science in Civil Engineering with specialization in Transportation Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Engineering Technology with a major in Construction Technology and Management">Bachelor of Engineering Technology with a major in Construction Technology and Management</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electrical Engineering">Bachelor of Science in Electrical Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Industrial Engineering">Bachelor of Science in Industrial Engineering</a></li>' +
                                '<li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</a></li>' +
                            '</ul>' +
                        '</div>' +
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
            <label for="program" class="form-label">Program</label>
            <div class="input-group">
                <input type="text" class="form-control" id="program" name="program" placeholder="Enter program">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                <ul class="dropdown-menu">
                    <li><h6 class="dropdown-header">Department of Architecture</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Architecture">Bachelor of Science in Architecture</a></li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><h6 class="dropdown-header">Department of Computer Studies</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Data Science">Bachelor of Science in Computer Science with specialization in Data Science</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Science with specialization in Software Engineering">Bachelor of Science in Computer Science with specialization in Software Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Network and Information Security">Bachelor of Science in Information Technology with specialization in Network and Information Security</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Information Technology with specialization in Web and Mobile Technology">Bachelor of Science in Information Technology with specialization in Web and Mobile Technology</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Library and Information Science">Bachelor of Library and Information Science</a></li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><h6 class="dropdown-header">Department of Engineering</h6></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Aeronautical Engineering">Bachelor of Science in Aeronautical Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management">Bachelor of Science in Civil Engineering with specialization in Construction Engineering & Management</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Structural Engineering">Bachelor of Science in Civil Engineering with specialization in Structural Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Civil Engineering with specialization in Transportation Engineering">Bachelor of Science in Civil Engineering with specialization in Transportation Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Engineering Technology with a major in Construction Technology and Management">Bachelor of Engineering Technology with a major in Construction Technology and Management</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electrical Engineering">Bachelor of Science in Electrical Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Industrial Engineering">Bachelor of Science in Industrial Engineering</a></li>
                    <li><a class="dropdown-item program-option" href="#" data-value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</a></li>
                </ul>
            </div>
            </div>
            <h5 class="mt-4">Team Members</h5>
            <div id="teamMembers">
            <!-- Team members will be added here -->
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
        `;
                form.append(formHtml);

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
                <input type="hidden" name="table" value="defense_schedules">        
                <div class="mb-3">
                    <label for="schedule_date" class="form-label">Schedule Date</label>
                    <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                    <small class="form-text text-muted">Select date for the defense schedule.</small>
                </div>
                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required 
                        onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                    <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                </div>
                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required
                        onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
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
                    ${data.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                    </select>
                </div>
                <h5 class="mt-4">Panelists</h5>
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

        // Add item button functionality
        $(document).off('click.addItem').on('click.addItem', '#addItem', function(e) {
            e.preventDefault();
            console.log('Add item button clicked');
            
            var form = $('#addForm');
            var table = form.find('input[name="table"]').val();
            var formData = new FormData(form[0]); // moved initialization here
            
            // For users, validate the email format
            if (table === 'users') {
                var email = form.find('input[name="email"]').val().trim();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showToast('Error', 'Invalid email format', 'error');
                    return; // Prevent submission on invalid email
                }
            }
            
            // For teams, process team member fields
            if (table === 'teams') {
                var members = [];
                $('#teamMembers .team-member').each(function() {
                    var userId = $(this).find('select[name="new_user_id[]"]').val();
                    var username = $(this).find('input[name="new_username[]"]').val();
                    var role = $(this).find('select[name="new_role[]"]').val();
                    if ((userId || username) && role) {
                        members.push({
                            id: userId,
                            username: username,
                            role: role
                        });
                    }
                });
                
                if (members.length > 0) {
                    formData.set('members', JSON.stringify(members));
                }
                formData.delete('new_user_id[]');
                formData.delete('new_username[]');
                formData.delete('new_role[]');
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
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'Unable to add item: ' + error, 'error');
                }
            });
        });

        // Delete button functionality
        $(document).off('click.deleteBtn').on('click.deleteBtn', '.delete-btn', function(e) {
            e.preventDefault();
            
            var table = $(this).data('table');
            var id = $(this).data('id');
            
            console.log('Main app: Delete button clicked. Table:', table, 'ID:', id);
            
            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric delete to rubrics_tab.php handler');
                // Let the delete-rubric-btn handler in rubrics_tab.php handle this
                $('.delete-rubric-btn[data-id="' + id + '"]').trigger('click');
                return true; // Allow event to bubble to other handlers
            }

            // For all other tables, show the confirmation modal
            // Update modal content
            $('#deleteTableName').text(table);
            $('#deleteItemId').text(id);
            
            // Show modal
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
            
            // Store data for the confirmation button
            $('#confirmDelete').data('table', table);
            $('#confirmDelete').data('id', id);
        });

        // Confirm delete button handler
        $(document).off('click.confirmDelete').on('click.confirmDelete', '#confirmDelete', function(e) {
            e.preventDefault();
            
            var table = $('#deleteTableName').text();
            var id = $('#deleteItemId').text();
            
            console.log('Main app: Confirm delete clicked for table:', table, 'ID:', id);
            
            $.ajax({
                url: 'includes/delete_item.php',
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Hide modal and refresh the table
                        $('#deleteConfirmModal').modal('hide');
                        
                        // Show success toast
                        showToast('Success', 'Item deleted successfully', 'success');
                        
                        // Reload the appropriate tab
                        if (table === 'users') {
                            if (typeof loadUsers === 'function') {
                                loadUsers();
                            } else {
                                // Fall back to page reload after a delay
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            }
                        } else if (table === 'teams') {
                            if (typeof loadTeams === 'function') {
                                loadTeams();
                            } else {
                                setTimeout(function() { location.reload(); }, 1500);
                            }
                        } else if (table === 'defense_schedules') {
                            if (typeof loadDefenseSchedules === 'function') {
                                loadDefenseSchedules();
                            } else {
                                setTimeout(function() { location.reload(); }, 1500);
                            }
                        } else if (table === 'thesis_topics') {
                            // Assuming there's a function to reload thesis topics
                            if (typeof loadThesisTopics === 'function') {
                                loadThesisTopics();
                            } else {
                                setTimeout(function() { location.reload(); }, 1500);
                            }
                        } else {
                            // For any other table, just reload the page
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        // Show error toast
                        showToast('Error', response.message || 'Unknown error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'Failed to delete item: ' + error, 'error');
                }
            });
        });

        if (typeof moment === 'undefined') {
            console.error("Moment.js is not loaded!");
        }

        // Datepicker initialization
        $("#days").datepicker({
            dateFormat: "yy-mm-dd",
            multidate: true,
            beforeShowDay: function(date) {
                var day = date.getDay();
                return [day != 0, '']; // Disable Sundays
            }
        });

        $('#saveSchedulerSettings').on('click', function() {
            // You can add validation here if needed (e.g., check if rooms, dates are provided)
            $('#schedulerSettingsModal').modal('hide'); // Close the modal
            // Enable "Generate Schedule" button after settings are saved
            $('#generateSchedule').prop('disabled', false);
        });

        // NEW: Bulk Add Users functionality
        $(document).off('click.bulkAddBtn').on('click.bulkAddBtn', '.bulk-add-btn', function(e) {
            e.preventDefault();
            console.log('Bulk Add Users button clicked');
            $('#bulkAddModal').modal('show');
        });

        $(document).off('click.bulkAddSubmit').on('click.bulkAddSubmit', '#bulkAddSubmit', function(e) {
            e.preventDefault();
            console.log('Bulk Add Users submit clicked');
            
            var bulkForm = $('#bulkAddForm');
            var formData = new FormData(bulkForm[0]);
            
            // NEW: If pasted bulk text is provided, append it as "bulk_users"
            var bulkText = $('#bulkTextInput').val().trim();
            if(bulkText !== "") {
                formData.append('bulk_users', bulkText);
            }
            
            // Optionally, add additional processing for the table rows if needed.
            
            $.ajax({
                url: 'includes/bulk_add_users.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('Bulk add response:', response);
                    if (response.success) {
                        showToast('Success', 'Users added successfully', 'success');
                        $('#bulkAddModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast('Error', response.message || 'Bulk add failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'Unable to add users: ' + error, 'error');
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

        // // When the Bulk Add Modal is shown, insert the CSV download link if not already present
        // $('#bulkAddModal').on('shown.bs.modal', function() {
        //     if (!$(this).find('#downloadCsvTemplate').length) {
        //         $(this).find('.modal-body').prepend(`
        //             <div class="mb-3">
        //                 <a href="#" id="downloadCsvTemplate" class="btn btn-sm btn-secondary">Download CSV Template</a>
        //             </div>
        //         `);
        //     }
        // });

        // Update Bulk Add Users CSV download handler
        $(document).off('click.downloadCsvTemplate').on('click.downloadCsvTemplate', '#downloadCsvTemplate', function(e) { 
            e.preventDefault();        
            const csvContent = 'ID,Name,Program,No Username\n';
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
                    console.log('Bulk add teams response:', response);
                    if (response.success) {
                        showToast('Success', 'Teams added successfully', 'success');
                        $('#bulkAddTeamsModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast('Error', response.message || 'Bulk add failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'Unable to add teams: ' + error, 'error');
                }
            });
        });

        // When the Bulk Add Teams Modal is shown, insert the CSV download link if not already present
        $('#bulkAddTeamsModal').on('shown.bs.modal', function() {
            if (!$(this).find('#downloadCsvTemplateTeams').length) {
                $(this).find('.modal-body').prepend(`
                    <div class="mb-3">
                        <a href="#" id="downloadCsvTemplateTeams" class="btn btn-sm btn-secondary">Download CSV Template</a>
                    </div>
                `);
            }
        });

        // Update Bulk Add Teams CSV download handler
        $(document).off('click.downloadCsvTemplateTeams').on('click.downloadCsvTemplateTeams', '#downloadCsvTemplateTeams', function(e) {
            e.preventDefault();
            // NEW: CSV template now includes the "Members" header
            const csvContent = 'Team Name,Research Title,Area of Expertise,Program,Members\n';
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'teams_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
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

    function addNewPanelist(staff) {
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
                        <textarea class="form-control" id="bulkTextInput" name="bulkTextInput" rows="5" placeholder="Paste CSV data here"></textarea>
                    </div>
                    <hr>
                    <!-- Table for manual data input -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bulkAddTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name <small>(format: Lastname, Firstname [no middle])</small></th>
                                    <th>Program</th>
                                    <th>No Username <br><small>(if checked, a username will be auto-generated)</small></th>
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