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

                            var fieldsToShow = ['username', 'email', 'first_name', 'last_name', 'area_of_expertise','gender', 'headline', 'bio'];

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
                                } else if (key === 'area_of_expertise') {
                                    // Add area_of_expertise field only for faculty usertype (2)
                                    formHtml += `
                            <div class="mb-3 area-expertise-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
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
            <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
        </div>
        <div class="mb-3">
            <label for="program" class="form-label">Program</label>
            <input type="text" class="form-control" id="program" name="program" value="${response.data.program || ''}">
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
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
    `;

                            response.data.members.forEach(function(member, index) {
                                formHtml += `
            <div class="mb-3 row team-member" data-user-id="${member.id}">
                <div class="col-sm-7">
                    <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
                </div>
                <div class="col-sm-5">
                    <select class="form-select" name="member_role[]">
                        <option value="adviser"${member.role === 'adviser' ? ' selected' : ''}>Adviser</option>
                        <option value="leader"${member.role === 'leader' ? ' selected' : ''}>Leader</option>
                        <option value="member"${member.role === 'member' ? ' selected' : ''}>Member</option>
                    </select>
                </div>
            </div>
        `;
                            });

                            formHtml += `
        </div>
    `;
                            form.html(formHtml);
                            // Add team member functionality
                            $('#addTeamMember').on('click', function() {
                                console.log('Add Team Member button clicked');
                                addNewTeamMember();
                            });
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
                            <input type="date" class="form-control" id="schedule_date" name="schedule_date" value="${response.data.schedule_date}" required>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" value="${response.data.start_time}" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" value="${response.data.end_time}" required>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Room</label>
                            <input type="text" class="form-control" id="room" name="room" value="${response.data.room}" required>
                        </div>
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Team</label>
                            <select class="form-select" id="team_id" name="team_id" required>
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

                            // Add panelist functionality
                            $('#addPanelist').on('click', function() {
                                console.log('Add Panelist button clicked');
                                addNewPanelist(response.staff);
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
            
            // Standard handling for all other tables
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');

            // Generate form fields based on table
            if (table === 'users') {
                form.append('<div class="mb-3">' +
                    '<label for="username" class="form-label">Username</label>' +
                    '<input type="text" class="form-control" id="username" name="username" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="email" class="form-label">Email</label>' +
                    '<input type="email" class="form-control" id="email" name="email" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="password" class="form-label">Password</label>' +
                    '<input type="password" class="form-control" id="password" name="password" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="first_name" class="form-label">First Name</label>' +
                    '<input type="text" class="form-control" id="first_name" name="first_name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="last_name" class="form-label">Last Name</label>' +
                    '<input type="text" class="form-control" id="last_name" name="last_name" required>' +
                    '</div>' +
                    '<div class="mb-3 area-expertise-field" style="display:none;">' +
                    '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                    '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise">' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="usertype" class="form-label">User Type</label>' +
                    '<select class="form-select" id="usertype" name="usertype" required>' +
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
            <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise">
            </div>
            <div class="mb-3">
            <label for="program" class="form-label">Program</label>
            <input type="text" class="form-control" id="program" name="program">
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
                <div class="mb-3">
                    <label for="schedule_date" class="form-label">Schedule Date</label>
                    <input type="date" class="form-control" id="schedule_date" name="schedule_date" required>
                </div>
                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                </div>
                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" required>
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

        // Save changes button functionality
        $(document).off('click.saveChanges').on('click.saveChanges', '#saveChanges', function(e) {
            e.preventDefault();
            console.log('Save changes button clicked');
            
            var form = $('#editForm');
            var formData = new FormData(form[0]);
            
            var table = formData.get('table');
            if (table === 'teams') {
                var members = [];
                $('.team-member').each(function() {
                    var userId = $(this).data('user-id') || $(this).find('select[name="new_user_id[]"]').val();
                    var role = $(this).find('select[name="member_role[]"], select[name="new_role[]"]').val();
                    if (userId && role) {
                        members.push({
                            id: userId,
                            role: role
                        });
                    }
                });
                formData.set('members', JSON.stringify(members));
                formData.delete('member_role[]');
                formData.delete('new_user_id[]');
                formData.delete('new_role[]');
            }
            
            if (formData.get('table') === 'env_variables' && (formData.get('key') === 'DB_PASSWORD' || formData.get('key') === 'MAIL_ENCRYPTION')) {
                var oldValue = formData.get('old_value');
                if (!oldValue) {
                    alert('Old value is required for DB_PASSWORD and MAIL_ENCRYPTION');
                    return;
                }
                formData.set('value', null); // Set value to null
                formData.set('old_value', oldValue); // Add old value to form data
            }

            console.log('Form data before send:', Object.fromEntries(formData));

            $.ajax({
                url: 'includes/update_item.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    if (response.success) {
                        showToast('Success', 'Item updated successfully', 'success');
                        $('#editModal').modal('hide');
                        // Add delay before reload
                        setTimeout(function() {
                            location.reload();
                        }, 2000); // 2 second delay
                    } else {
                        showToast('Error', response.message, 'error');
                        console.error('Update failed:', response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    console.log('Response Text:', jqXHR.responseText);
                    console.log('Status:', jqXHR.status);
                    console.log('Status Text:', jqXHR.statusText);
                    showToast('Error', 'Unable to update item. Check console for details.', 'error');
                }
            });
        });

        // Add item button functionality
        $(document).off('click.addItem').on('click.addItem', '#addItem', function(e) {
            e.preventDefault();
            console.log('Add item button clicked');
            
            var form = $('#addForm');
            var formData = new FormData(form[0]);
            
            var table = formData.get('table');
            if (table === 'teams') {
                var members = [];
                $('#teamMembers .team-member').each(function() {
                    var userId = $(this).find('select[name="new_user_id[]"]').val();
                    var role = $(this).find('select[name="new_role[]"]').val();
                    if (userId && role) {
                        members.push({
                            id: userId,
                            role: role
                        });
                    }
                });
                
                // Add members data to formData as JSON string
                if (members.length > 0) {
                    formData.set('members', JSON.stringify(members));
                }
                
                // Remove unnecessary form fields to avoid confusion
                formData.delete('new_user_id[]');
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
                        // Add delay before reload
                        setTimeout(function() {
                            location.reload();
                        }, 2000); // 2 second delay
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

        $('#generateSchedule').on('click', function() {
            console.log('Generate Schedule button clicked'); // Existing log

            <?php
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM defense_schedules");
            $stmt->execute();
            $totalScheds = $stmt->fetchColumn();
            ?>
            // Check if there are existing schedules
            var totalScheds = <?php echo $totalScheds; ?>; // Get the total schedules from PHP

            console.log('Total Schedules:', totalScheds); // New log

            if (totalScheds !== 0) {
                if (confirm('Existing schedules will be removed. Are you sure you want to proceed?')) {
                    initiateScheduleGeneration();
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', './includes/truncate_sched.php', true);
                    xhr.send();
                }
            } else {
                initiateScheduleGeneration();
            }
        });

        // New function to initiate schedule generation
        function initiateScheduleGeneration() {
            var $button = $('#generateSchedule');
            var $status = $('#scheduleGenerationStatus');

            // Get scheduler settings
            var rooms = $('#rooms').val().split(',').map(function(room) {
                return room.trim();
            });
            var timeDuration = parseInt($('#timeDuration').val());
            var startTime = $('#startTime').val();
            var endTime = $('#endTime').val();
            var days = $('#days').datepicker('getDates').map(function(date) {
                return moment(date).format('YYYY-MM-DD'); // Format dates correctly
            });

            var timeSlots = generateTimeSlots(startTime, endTime, timeDuration);

            // Retrieve program and status from the form
            var program = $('#programSelect').val(); // Ensure the ID matches your program select element
            var status = $('input[name="status[]"]:checked').map(function() {
                return this.value;
            }).get(); // Collect all checked status checkboxes

            // Debugging logs
            console.log('Rooms:', rooms);
            console.log('Time Duration:', timeDuration);
            console.log('Start Time:', startTime);
            console.log('End Time:', endTime);
            console.log('Time Slots:', timeSlots);
            console.log('Days:', days);

            $button.prop('disabled', true).text('Generating...');
            $status.text('Generating schedule...').removeClass('text-success text-danger').addClass('text-warning');

            $.ajax({
                url: 'includes/run_scheduler.php',
                method: 'POST',
                data: {
                    rooms: rooms,
                    timeDuration: timeDuration,
                    startTime: startTime,
                    endTime: endTime,
                    timeSlots: timeSlots,
                    days: days,
                },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX response:', response); // Existing log
                    if (response.success) {
                        $status.text('Schedule generated successfully!').removeClass('text-warning').addClass('text-success');

                        // Update metrics (include all relevant metrics from the PHP response)
                        $('#initialPopulationSize').text(response.initialPopulationSize);
                        $('#crossoverCount').text(response.crossoverCount);
                        $('#mutationCount').text(response.mutationCount);
                        $('#conflictCounts').text(response.conflictCounts.join(', '));
                        // Add other metrics as needed (e.g., fitnessScores, populationPerGeneration)

                        // Reload or update the schedule display after a short delay
                        setTimeout(function() {
                            location.reload(); // Or update the schedule table dynamically
                        }, 2000);

                    } else {
                        $status.text('Error: ' + response.message).removeClass('text-warning').addClass('text-danger');
                        $button.prop('disabled', false).text('Generate Defense Schedule');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    if (jqXHR.responseText) {
                        console.error('Server Response:', jqXHR.responseText);
                    }
                    $status.text('An error occurred while generating the schedule.').removeClass('text-warning').addClass('text-danger');
                    $button.prop('disabled', false).text('Generate Defense Schedule');
                },
                complete: function() {
                    // This will run regardless of success or failure.
                    // Optionally, you can remove the "Generating..." state here.
                    $button.text('Generate Defense Schedule');
                }
            });

            var xhr = new XMLHttpRequest();
            xhr.open('POST', './includes/truncate_sched.php', true);
            xhr.send();

        }

        function generateTimeSlots(start, end, duration) {
            var timeSlots = [];

            // Parse start and end times as Moment.js objects
            var current = moment(start, "HH:mm");
            var endTime = moment(end, "HH:mm");
            var maxTime = moment("18:00", "HH:mm"); // Set maximum time to 6 PM

            // Loop to generate slots
            while (current.isBefore(endTime)) {
                // Only add time slots that are at or before 6 PM
                if (!current.isAfter(maxTime)) {
                    timeSlots.push(current.format("HH:mm:ss"));
                }
                current.add(duration, 'hours');
            }
            console.log('Time slots:', timeSlots);
            return timeSlots;
        }

    });

    const sidebarContainer = $('#sidebarContainer');
    const mainContent = $('#mainContent');
    const toggleButton = $('#toggleSidebar');

    toggleButton.on('click', function() {
        sidebarContainer.toggleClass('collapsed');
        mainContent.toggleClass('expanded');
        toggleButton.toggleClass('collapsed');

        // Store the sidebar state in localStorage
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
        console.log('addNewTeamMember function called');
        $.ajax({
            url: 'includes/get_users.php',
            method: 'GET',
            dataType: 'json',
            success: function(users) {
                console.log('Users fetched:', users);
                
                // Create new team member row
                var newMemberHtml = `
                <div class="mb-3 row team-member">
                    <div class="col-sm-5">
                        <select class="form-select user-select" name="new_user_id[]">
                            <option value="">Select a user</option>
                        </select>
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
                
                // Add the new row to the DOM
                $('#teamMembers').append(newMemberHtml);
                
                // Get the newly added elements
                var $newRow = $('#teamMembers .team-member').last();
                var $roleSelect = $newRow.find('.role-select');
                var $userSelect = $newRow.find('.user-select');
                
                // Function to update user options based on selected role
                function updateUserOptions(role) {
                    $userSelect.empty().append('<option value="">Select a user</option>');
                    
                    // Filter users based on role
                    var filteredUsers = users.filter(function(user) {
                        if (role === 'adviser') {
                            return user.usertype == 2; // Faculty only for adviser
                        } else if (role === 'leader' || role === 'member') {
                            return user.usertype == 1; // Student only for leader/member
                        }
                        return false; // Never show admins (usertype 0)
                    });
                    
                    // Add filtered users to dropdown
                    filteredUsers.forEach(function(user) {
                        $userSelect.append(`<option value="${user.id}">${user.first_name} ${user.last_name}</option>`);
                    });
                }
                
                // Initial filter based on default role (adviser)
                updateUserOptions($roleSelect.val());
                
                // Add event listener for role change
                $roleSelect.on('change', function() {
                    updateUserOptions($(this).val());
                });
                
                console.log('New member added to DOM with role-based filtering');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching users:', textStatus, errorThrown);
                alert('Error loading users. Please try again.');
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