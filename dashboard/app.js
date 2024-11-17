$(document).ready(function () {
    
    // Edit button functionality
    $(document).on('click', '.edit-btn', function () {
        var table = $(this).data('table');
        var id = $(this).data('id');

        console.log('Edit button clicked. Table:', table, 'ID:', id);

        $.ajax({
            url: 'includes/get_item_details.php',
            method: 'POST',
            data: {
                table: table,
                id: id
            },
            dataType: 'json',
            success: function (response) {
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

                        fieldsToShow.forEach(function (key) {
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

                        form.html(formHtml);
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
        <h5 class="mt-4">Team Members</h5>
        <div id="teamMembers">
    `;

                        response.data.members.forEach(function (member, index) {
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
                        $('#addTeamMember').on('click', function () {
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
                                </div>
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

                        response.data.members.forEach(function (member, index) {
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
                        $('#addTeamMember').on('click', function () {
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
                        form.html(formHtml);
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
                                        <select class="form-select" name="panelist_id[]">
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
                    }
                    // Add more conditions for other tables as needed
                    $('#editModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error: Unable to fetch item details');
            }
        });
    });

    // Add button functionality
    $(document).on('click', '.add-btn', function () {
        var table = $(this).data('table');
        var form = $('#addForm');
        form.empty();
        form.append('<input type="hidden" name="table" value="' + table + '">');

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
                '<div class="mb-3">' +
                '<label for="usertype" class="form-label">User Type</label>' +
                '<select class="form-select" id="usertype" name="usertype" required>' +
                '<option value="0">Admin</option>' +
                '<option value="1">Student</option>' +
                '<option value="2">Faculty</option>' +
                '</select>' +
                '</div>');
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
            <h5 class="mt-4">Team Members</h5>
            <div id="teamMembers">
                <!-- Team members will be added here -->
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
        `;
            form.append(formHtml);

            // Add team member functionality
            $('#addTeamMember').on('click', function () {
                console.log('Add Team Member button clicked');
                addNewTeamMember();
            });



        } else if (table === 'rubrics') {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Name</label>' +
                '<input type="text" class="form-control" id="name" name="name" required>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="description" class="form-label">Description</label>' +
                '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                '</div>' +
                '<div class="mb-3">' +
                '<label for="created_by" class="form-label">Created By</label>' +
                '<input type="text" class="form-control" id="created_by" name="created_by" required>' +
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
        } else {
            form.append('<div class="mb-3">' +
                '<label for="name" class="form-label">Name</label>' +
                '<input type="text" class="form-control" id="name" name="name" required>' +
                '</div>');
        }

        $('#addModal').modal('show');
    });

    $('#saveChanges').on('click', function () {
        var form = $('#editForm');
        var formData = new FormData(form[0]);

        if (formData.get('table') === 'teams') {
            var members = [];
            $('.team-member').each(function () {
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

        console.log('Form data before send:', Object.fromEntries(formData));

        $.ajax({
            url: 'includes/update_item.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                console.log('Server response:', response);
                if (response.success) {
                    alert('Item updated successfully');
                    $('#editModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                    console.error('Update failed:', response);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                console.log('Status:', jqXHR.status);
                console.log('Status Text:', jqXHR.statusText);
                alert('Error: Unable to update item. Check console for details.');
            }
        });
    });

    // JavaScript code to handle form submission
    $('#addItem').on('click', function () {
        var form = $('#addForm');
        var formData = new FormData(form[0]);

        $.ajax({
            url: 'includes/add_items.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('Team added successfully');
                    $('#addModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function () {
                alert('Error: Unable to add team');
            }
        });
    });

    // JavaScript code to handle delete button click
    $(document).on('click', '.delete-btn', function () {
        var id = $(this).data('id');
        var table = $(this).data('table');

        if (confirm('Are you sure you want to delete this item from table ' + table + ' with ID ' + id + '?')) {
            $.ajax({
                url: 'includes/delete_item.php',
                method: 'POST',
                data: {
                    id: id,
                    table: table
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('Item deleted successfully from table ' + table + ' with ID ' + id);
                        // Optionally, refresh the table or page
                    } else {
                        alert('Error: ' + response.message + ' (Table: ' + table + ', ID: ' + id + ')');
                    }
                },
                error: function () {
                    alert('Error: Unable to delete item from table ' + table + ' with ID ' + id);
                }
            });
        }
    });

    $(document).ready(function () {
        $('#generateSchedule').on('click', function () {
            var $button = $(this);
            var $status = $('#scheduleGenerationStatus');

            $button.prop('disabled', true).text('Generating...');
            $status.text('Generating schedule...').removeClass('text-success text-danger').addClass('text-warning');

            $.ajax({
                url: 'includes/run_scheduler.php',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $status.text('Schedule generated successfully!').removeClass('text-warning').addClass('text-success');

                        // Update metrics
                        $('#initialPopulationSize').text(response.initialPopulationSize);
                        $('#crossoverCount').text(response.crossoverCount);
                        $('#mutationCount').text(response.mutationCount);
                        $('#conflictCounts').text(response.conflictCounts.join(', '));

                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        $status.text('Error: ' + response.message).removeClass('text-warning').addClass('text-danger');
                        $button.prop('disabled', false).text('Generate Defense Schedule');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    if (jqXHR.responseText) {
                        console.error('Server Response:', jqXHR.responseText);
                    }
                    $status.text('An error occurred while generating the schedule.').removeClass('text-warning').addClass('text-danger');
                    $button.prop('disabled', false).text('Generate Defense Schedule');
                }
            });
        });
    });
});
function addNewPanelist(staff) {
    console.log('addNewPanelist function called');
    var newPanelistHtml = `
        <div class="mb-3 row panelist">
            <div class="col-sm-10">
                <select class="form-select" name="new_panelist_id[]">
                    <option value="">Select a panelist</option>
                    ${staff.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
            </div>
        </div>
    `;
    console.log('New panelist HTML:', newPanelistHtml);
    $('#panelists').append(newPanelistHtml);
    console.log('New panelist added to DOM');
}
// Define addNewTeamMember function globally
function addNewTeamMember() {
    console.log('addNewTeamMember function called');
    $.ajax({
        url: 'includes/get_users.php',
        method: 'GET',
        dataType: 'json',
        success: function (users) {
            console.log('Users fetched:', users);
            var newMemberHtml = `
                <div class="mb-3 row team-member">
                    <div class="col-sm-5">
                        <select class="form-select" name="new_user_id[]">
                            <option value="">Select a user</option>
                            ${users.map(user => `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <select class="form-select" name="new_role[]">
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
            console.log('New member HTML:', newMemberHtml);
            $('#teamMembers').append(newMemberHtml);
            console.log('New member added to DOM');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching users:', textStatus, errorThrown);
        }
    });
}
// Remove team member functionality
$(document).on('click', '.remove-member', function () {
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
                success: function (response) {
                    if (response.success) {
                        teamMember.remove();
                        alert('Team member removed successfully');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function () {
                    alert('Error: Unable to remove team member');
                }
            });
        }
    } else {
        // If it's a new member (not yet saved to database), just remove from form
        teamMember.remove();
    }
});