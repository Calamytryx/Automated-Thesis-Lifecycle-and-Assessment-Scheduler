<div class="tab-pane fade" id="schedules" role="tabpanel" aria-labelledby="schedules-tab">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Schedule Management</h2>
        </div>

        <!-- Schedule Type Tabs -->
        <ul class="nav nav-tabs mb-4" id="scheduleTypeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="program-schedules-tab" data-bs-toggle="tab" data-bs-target="#program-schedules" type="button" role="tab">
                    <i class="bi bi-calendar-week me-2"></i>Program Schedules
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="faculty-schedules-tab" data-bs-toggle="tab" data-bs-target="#faculty-schedules" type="button" role="tab">
                    <i class="bi bi-person-badge me-2"></i>Faculty Schedules
                </button>
            </li>
        </ul>

        <div class="tab-content" id="scheduleTypeContent">
            <!-- Program Schedules Tab -->
            <div class="tab-pane fade show active" id="program-schedules" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Program Class Schedules</h5>
                        <button type="button" class="btn btn-primary add-btn" data-table="default_schedules" data-schedule-type="program">
                            <i class="bi bi-plus-circle me-2"></i>Add Schedule
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Program Selection -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="programFilter" class="form-label">Filter by Program:</label>
                                <select class="form-select" id="programFilter">
                                    <option value="">All Programs</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="yearFilter" class="form-label">Filter by Year:</label>
                                <select class="form-select" id="yearFilter">
                                    <option value="">All Years</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="sectionFilter" class="form-label">Filter by Section:</label>
                                <select class="form-select" id="sectionFilter">
                                    <option value="">All Sections</option>
                                    <option value="A">Section A</option>
                                    <option value="B">Section B</option>
                                    <option value="C">Section C</option>
                                </select>
                            </div>
                        </div>

                        <!-- Program Schedules Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="programSchedulesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Program</th>
                                        <th>Year & Section</th>
                                        <th>Class Name</th>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Room</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faculty Schedules Tab -->
            <div class="tab-pane fade" id="faculty-schedules" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Faculty Individual Schedules</h5>
                        <button type="button" class="btn btn-primary add-btn" data-table="user_schedules" data-schedule-type="faculty">
                            <i class="bi bi-plus-circle me-2"></i>Add Faculty Schedule
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Faculty Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="facultyFilter" class="form-label">Select Faculty:</label>
                                <select class="form-select" id="facultyFilter">
                                    <option value="">All Faculty</option>
                                </select>
                            </div>
                        </div>

                        <!-- Faculty Schedules Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="facultySchedulesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Faculty Name</th>
                                        <th>Schedule Type</th>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Room</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Schedule Modal -->
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

<script>
$(document).ready(function() {
    let currentScheduleType = 'program';
    
    // Load initial data
    loadPrograms();
    loadFaculty();
    loadProgramSchedules();
    
    // Tab switching
    $('#scheduleTypeTabs button').on('click', function() {
        const target = $(this).data('bs-target');
        currentScheduleType = target === '#program-schedules' ? 'program' : 'faculty';
        
        if (currentScheduleType === 'faculty') {
            loadFacultySchedules();
        }
    });

    // Add schedule button
    $(document).on('click', '.add-btn[data-schedule-type]', function() {
        const scheduleType = $(this).data('schedule-type');
        const table = $(this).data('table');
        
        resetScheduleForm();
        $('#scheduleModalLabel').text('Add Schedule');
        $('#scheduleTable').val(table);
        $('#scheduleType').val(scheduleType);
        
        if (scheduleType === 'program') {
            $('#programScheduleFields').show();
            $('#facultyScheduleFields').hide();
        } else {
            $('#programScheduleFields').hide();
            $('#facultyScheduleFields').show();
        }
        
        $('#scheduleModal').modal('show');
    });

    // Edit schedule
    $(document).on('click', '.edit-schedule-btn', function() {
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

    // Save schedule
    $('#saveSchedule').on('click', function() {
        const formData = new FormData($('#scheduleForm')[0]);
        
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
                    
                    // Reload appropriate table
                    if (currentScheduleType === 'program') {
                        loadProgramSchedules();
                    } else {
                        loadFacultySchedules();
                    }
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function() {
                showToast('Error', 'Failed to save schedule', 'error');
            }
        });
    });

    // Delete schedule
    $(document).on('click', '.delete-schedule-btn', function() {
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
                        
                        // Reload appropriate table
                        if (currentScheduleType === 'program') {
                            loadProgramSchedules();
                        } else {
                            loadFacultySchedules();
                        }
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

    // Filter changes
    $('#programFilter, #yearFilter, #sectionFilter').on('change', function() {
        loadProgramSchedules();
    });
    
    $('#facultyFilter').on('change', function() {
        loadFacultySchedules();
    });

    // Functions
    function loadPrograms() {
        $.ajax({
            url: 'includes/get_programs_grouped.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const programSelect = $('#program');
                    const programFilter = $('#programFilter');
                    
                    programSelect.empty().append('<option value="">Select Program</option>');
                    programFilter.empty().append('<option value="">All Programs</option>');
                    
                    response.programs.forEach(function(program) {
                        const option = `<option value="${program.display_name}">${program.display_name}</option>`;
                        programSelect.append(option);
                        programFilter.append(option);
                    });
                }
            }
        });
    }

    function loadFaculty() {
        $.ajax({
            url: 'includes/get_faculty.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const facultySelect = $('#facultyUser');
                    const facultyFilter = $('#facultyFilter');
                    
                    facultySelect.empty().append('<option value="">Select Faculty</option>');
                    facultyFilter.empty().append('<option value="">All Faculty</option>');
                    
                    response.faculty.forEach(function(faculty) {
                        const option = `<option value="${faculty.id}">${faculty.name}</option>`;
                        facultySelect.append(option);
                        facultyFilter.append(option);
                    });
                }
            }
        });
    }

    function loadProgramSchedules() {
        const filters = {
            program: $('#programFilter').val(),
            year: $('#yearFilter').val(),
            section: $('#sectionFilter').val()
        };
        
        $.ajax({
            url: 'includes/get_program_schedules.php',
            method: 'GET',
            data: filters,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#programSchedulesTable tbody');
                    tbody.empty();
                    
                    response.schedules.forEach(function(schedule) {
                        const row = `
                            <tr>
                                <td>${schedule.id}</td>
                                <td>${schedule.program}</td>
                                <td>Year ${schedule.year} - Section ${schedule.section}</td>
                                <td>${schedule.class_name}</td>
                                <td>${schedule.day_of_week}</td>
                                <td>${schedule.start_time} - ${schedule.end_time}</td>
                                <td>${schedule.building} ${schedule.room}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-schedule-btn" data-id="${schedule.id}" data-table="default_schedules">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-schedule-btn" data-id="${schedule.id}" data-table="default_schedules">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
            }
        });
    }

    function loadFacultySchedules() {
        const facultyId = $('#facultyFilter').val();
        
        $.ajax({
            url: 'includes/get_faculty_schedules.php',
            method: 'GET',
            data: { faculty_id: facultyId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#facultySchedulesTable tbody');
                    tbody.empty();
                    
                    response.schedules.forEach(function(schedule) {
                        const row = `
                            <tr>
                                <td>${schedule.id}</td>
                                <td>${schedule.faculty_name}</td>
                                <td>${schedule.schedule_type}</td>
                                <td>${schedule.day_of_week}</td>
                                <td>${schedule.start_time} - ${schedule.end_time}</td>
                                <td>${schedule.room || 'N/A'}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-schedule-btn" data-id="${schedule.id}" data-table="user_schedules">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-schedule-btn" data-id="${schedule.id}" data-table="user_schedules">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
            }
        });
    }

    function resetScheduleForm() {
        $('#scheduleForm')[0].reset();
        $('#scheduleId').val('');
        $('#scheduleModalLabel').text('Add Schedule');
    }

    function populateScheduleForm(data, table) {
        $('#scheduleModalLabel').text('Edit Schedule');
        $('#scheduleId').val(data.id);
        $('#scheduleTable').val(table);
        
        if (table === 'default_schedules') {
            $('#scheduleType').val('program');
            $('#programScheduleFields').show();
            $('#facultyScheduleFields').hide();
            
            $('#program').val(data.program);
            $('#year').val(data.year);
            $('#section').val(data.section);
            $('#className').val(data.class_name);
            $('#building').val(data.building);
            $('#room').val(data.room);
        } else {
            $('#scheduleType').val('faculty');
            $('#programScheduleFields').hide();
            $('#facultyScheduleFields').show();
            
            $('#facultyUser').val(data.user_id);
            $('#scheduleTypeField').val(data.schedule_type);
            $('#facultyBuilding').val(data.building);
            $('#facultyRoom').val(data.room);
            $('#description').val(data.description);
        }
        
        $('#dayOfWeek').val(data.day_of_week);
        $('#startTime').val(data.start_time);
        $('#endTime').val(data.end_time);
    }
});
</script>
