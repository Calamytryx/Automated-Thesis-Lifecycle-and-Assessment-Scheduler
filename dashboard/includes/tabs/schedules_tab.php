<!-- Schedules Tab -->
<?php
require_once '../assets/setup/db.inc.php'; // Adjust path as needed
?>
<div class="tab-pane fade" id="schedules" role="tabpanel" aria-labelledby="schedules-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Class/Teacher Schedules</h3>
                <p class="text-muted">Manage class schedules and time availability for teachers</p>
                <?php if ($_SESSION['usertype'] == 0): ?>
                <div class="mt-2">
                    <a href="#requirements" class="tab-redirect-link" onclick="document.getElementById('requirements-tab').click(); return false;">
                        <i class="bi bi-check-square-fill"></i>
                        <span>Manage Requirements and Research templates</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Conflicting Schedules -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> If you see overlapping schedules, please resolve them to avoid conflicts.

                    <br>Click on a schedule item to edit or delete it.
                    <br>Use the filters below to view schedules by program/section or instructor.

                    <?php
                    // Check for conflicting schedules
                    $conflictingSchedules = [];

                    try {
                        // Get all schedules with user information
                        $stmt = $pdo->query("
    SELECT 
        us1.id as schedule1_id,
        us1.user_id as user1_id,
        us1.class_name as class1_name,
        us1.day_of_week,
        us1.start_time as start1_time,
        us1.end_time as end1_time,
        us1.program as program1,
        us1.section as section1,
        us1.room as room1,
        u1.first_name as user1_first,
        u1.last_name as user1_last,
        us2.id as schedule2_id,
        us2.user_id as user2_id,
        us2.class_name as class2_name,
        us2.start_time as start2_time,
        us2.end_time as end2_time,
        us2.program as program2,
        us2.section as section2,
        us2.room as room2,
        u2.first_name as user2_first,
        u2.last_name as user2_last
    FROM user_schedules us1
    JOIN user_schedules us2 ON 
        us1.day_of_week = us2.day_of_week AND
        us1.id < us2.id AND
        (
            us1.start_time < us2.end_time AND us1.end_time > us2.start_time
        ) AND (
            us1.user_id = us2.user_id OR
            us1.room = us2.room OR
            (us1.program = us2.program AND us1.section = us2.section)
        )
    LEFT JOIN users u1 ON us1.user_id = u1.id
    LEFT JOIN users u2 ON us2.user_id = u2.id
    ORDER BY us1.day_of_week, us1.start_time
");

                        
                        $conflictingSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($conflictingSchedules)) {
                            echo '<br><div class="mt-3"><strong>Conflicting Schedules Found:</strong></div>';
                            echo '<div class="row mt-2">';
                            
                            foreach ($conflictingSchedules as $conflict) {
                                echo '<div class="col-md-6 mb-2">';
                                echo '<div class="card border-danger">';
                                echo '<div class="card-body p-2">';
                                echo '<small class="text-danger">';
                                echo '<strong>' . htmlspecialchars($conflict['day_of_week']) . '</strong><br>';
                                echo '1. ' . htmlspecialchars($conflict['class1_name']) . ' (' . date('g:i A', strtotime($conflict['start1_time'])) . '-' . date('g:i A', strtotime($conflict['end1_time'])) . ')<br>';
                                echo '&nbsp;&nbsp;&nbsp;' . htmlspecialchars($conflict['user1_first'] . ' ' . $conflict['user1_last']) . ' - ' . htmlspecialchars($conflict['program1']) . ' Sec: ' . htmlspecialchars($conflict['section1']);
                                if ($conflict['room1']) echo ' - Room: ' . htmlspecialchars($conflict['room1']);
                                echo '<br>';
                                echo '2. ' . htmlspecialchars($conflict['class2_name']) . ' (' . date('g:i A', strtotime($conflict['start2_time'])) . '-' . date('g:i A', strtotime($conflict['end2_time'])) . ')<br>';
                                echo '&nbsp;&nbsp;&nbsp;' . htmlspecialchars($conflict['user2_first'] . ' ' . $conflict['user2_last']) . ' - ' . htmlspecialchars($conflict['program2']) . ' Sec: ' . htmlspecialchars($conflict['section2']);
                                if ($conflict['room2']) echo ' - Room: ' . htmlspecialchars($conflict['room2']);
                                echo '</small>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                        } else {
                            echo '<br><div class="mt-2 text-success"><i class="fas fa-check-circle me-1"></i>No conflicting schedules found.</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<br><div class="mt-2 text-danger"><i class="fas fa-exclamation-circle me-1"></i>Error checking for conflicts: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>

                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="btn-group" role="group" aria-label="View type toggle">
                            <button type="button" class="btn btn-outline view-type-btn" data-view="program">By Program/Section</button>
                            <button type="button" class="btn btn-outline view-type-btn"
                                data-view="instructor">By Instructor</button>
                        </div>
                        <input type="hidden" id="viewTypeSelect" value="">
                    </div>
                    <!-- College Filter -->
                    <div class="d-flex gap-2 align-items-center">
                        <select class="form-select user-control-height" id="collegeFilterSelect" style="width: 220px; display: none;">
                            <option value="">Select College</option>
                        </select>
                    </div>

                    <!-- Program and Section Filters -->
                    <div class="d-flex gap-2 align-items-center" id="programSectionFilters">
                        <!-- Program select (disabled until college selected) -->
                        <select class="form-select user-control-height" id="programFilterSelect" style="width: 200px; display: none;" disabled>
                            <option value="">Select Program</option>
                        </select>
                        <select class="form-select user-control-height" id="sectionFilterSelect" style="width: 180px; display: none;"
                            disabled>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    <!-- Instructor Filter -->
                    <div class="d-flex gap-2 align-items-center" id="instructorFilters">
                        <select class="form-select user-control-height" id="instructorFilterSelect" style="width: 220px; display: none;">
                            <option value="">Select Instructors</option>
                        </select>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const viewTypeButtons = document.querySelectorAll('.view-type-btn');
                        const viewTypeInput = document.getElementById('viewTypeSelect');
                        const collegeSelect = document.getElementById("collegeFilterSelect");
                        const programSelect = document.getElementById("programFilterSelect");
                        const sectionSelect = document.getElementById("sectionFilterSelect");
                        const instructorSelect = document.getElementById("instructorFilterSelect");

                        const loadInstructorsForCollege = (college = '') => {
                            instructorSelect.innerHTML = '<option value="">Select Instructors</option>';

                            const url = college ?
                                `includes/tabs/load_instructors.php?college=${encodeURIComponent(college)}` :
                                'includes/tabs/load_instructors.php';

                            return fetch(url)
                                .then(res => res.text())
                                .then(html => {
                                    instructorSelect.innerHTML = '<option value="">Select Instructors</option>' + html;
                                })
                                .catch(err => {
                                    console.error('Error loading instructors:', err);
                                    alert('Failed to load instructors.');
                                });
                        };
                        window.loadInstructorsForCollege = loadInstructorsForCollege;

                        function updateFilterVisibility() {
                            const selectedView = viewTypeInput.value;
                            const showCollege = selectedView === 'program' || selectedView === 'instructor';

                            collegeSelect.style.display = showCollege ? 'inline-block' : 'none';

                            if (selectedView === 'program') {
                                programSelect.style.display = 'inline-block';
                                sectionSelect.style.display = 'inline-block';
                                instructorSelect.style.display = 'none';
                            } else if (selectedView === 'instructor') {
                                programSelect.style.display = 'none';
                                sectionSelect.style.display = 'none';
                                instructorSelect.style.display = 'inline-block';
                            } else {
                                programSelect.style.display = 'none';
                                sectionSelect.style.display = 'none';
                                instructorSelect.style.display = 'none';
                            }
                        }

                        // 🔘 View type button logic
                        viewTypeButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                // Update active class
                                viewTypeButtons.forEach(btn => btn.classList.remove('active'));
                                this.classList.add('active');

                                // Update hidden input + trigger visibility
                                const newView = this.getAttribute('data-view');
                                viewTypeInput.value = newView;
                                updateFilterVisibility();
                            });
                        });

                        // 🔁 Program select triggers section update
                        programSelect.addEventListener("change", function() {
                            const programId = this.value;
                            sectionSelect.innerHTML = '<option value="">Select Section</option>';
                            sectionSelect.disabled = true;

                            if (programId) {
                                fetch(
                                        `includes/tabs/get_section.php?program_id=${encodeURIComponent(programId)}`)
                                    .then(res => {
                                        if (!res.ok) throw new Error('Network response was not ok');
                                        return res.json();
                                    })
                                    .then(data => {
                                        if (Array.isArray(data) && data.length > 0) {
                                            data.forEach(section => {
                                                const option = document.createElement(
                                                    "option");
                                                option.value = section;
                                                option.textContent = section;
                                                sectionSelect.appendChild(option);
                                            });
                                            sectionSelect.disabled = false;
                                        }
                                    })
                                    .catch(err => {
                                        console.error("Error fetching sections:", err.message);
                                        alert("Failed to load sections.");
                                    });
                            }
                        });

                        // 🔁 College select triggers program update
                        collegeSelect.addEventListener('change', function() {
                            const college = this.value;
                            // Reset program and section
                            programSelect.innerHTML = '<option value="">Select Program</option>';
                            programSelect.disabled = true;
                            sectionSelect.innerHTML = '<option value="">Select Section</option>';
                            sectionSelect.disabled = true;
                            instructorSelect.value = '';

                            if (viewTypeInput.value === 'program' && college) {
                                fetch(`includes/tabs/load_programs.php?college=${encodeURIComponent(college)}`)
                                    .then(res => {
                                        if (!res.ok) throw new Error('Network response was not ok');
                                        return res.text();
                                    })
                                    .then(html => {
                                        // html contains <option> tags
                                        programSelect.innerHTML = '<option value="">Select Program</option>' + html;
                                        programSelect.disabled = false;
                                    })
                                    .catch(err => {
                                        console.error('Error loading programs for college:', err);
                                        alert('Failed to load programs for selected college.');
                                    });
                            }

                            if (viewTypeInput.value === 'instructor') {
                                loadInstructorsForCollege(college);
                            }

                            // Clear board until user picks program & section
                            loadSchedules();
                        });

                        // Populate colleges on load
                        fetch('includes/tabs/load_colleges.php')
                            .then(res => res.text())
                            .then(html => {
                                collegeSelect.innerHTML = '<option value="">Select College</option>' + html;
                            })
                            .catch(err => console.error('Failed to load colleges:', err));

                        loadInstructorsForCollege();

                        // ✅ Select "By Program" as default on load
                        document.querySelector('.view-type-btn[data-view="program"]').click();
                    });
                    </script>
                    <div class="d-flex gap-2">
                        <button class="btn feature-btn add-btn" data-table="user_schedules">
                            <i class="fas fa-plus me-2"></i>Add Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Calendar -->
        <div class="schedule-calendar-container" id="schedules-container">
            <div class="schedule-calendar" id="schedule-calendar">
                <!-- Calendar will be generated here -->
            </div>
        </div>
    </div>

    <style>
    .schedule-calendar-container {
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .schedule-calendar {
        display: grid;
        grid-template-columns: 80px repeat(6, 1fr);
        min-width: 800px;
        background: white;
    }

    .time-header,
    .day-header {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 8px;
        font-weight: 600;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .time-slot {
        border: 1px solid #dee2e6;
        padding: 4px;
        font-size: 12px;
        text-align: center;
        background: #f8f9fa;
        font-weight: 500;
    }

    .schedule-cell {
        border: 1px solid #dee2e6;
        min-height: 40px;
        position: relative;
        background: white;
    }

    .schedule-item {
        background: #9e2a2f;
        color: white;
        padding: 4px 6px;
        border-radius: 3px;
        font-size: 11px;
        margin: 1px;
        cursor: pointer;
        position: absolute;
        left: 2px;
        right: 2px;
        overflow: hidden;
        z-index: 5;
        border: 1px solid #731f22;
    }

    .schedule-item:hover {
        background: #731f22;
        z-index: 10;
    }

    .schedule-item .course-code {
        font-weight: bold;
        display: block;
        line-height: 1.2;
    }

    .schedule-item .program-info {
        font-size: 9px;
        opacity: 0.9;
        line-height: 1.1;
    }

    .schedule-item .time-info {
        font-size: 9px;
        opacity: 0.8;
        line-height: 1.1;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Time slots from 7:00 AM to 9:00 PM (30-min intervals, no 9:30 PM)
        const generateTimeSlots = () => {
            const slots = [];
            for (let hour = 7; hour <= 21; hour++) {
                const hour12 = hour > 12 ? hour - 12 : hour;
                const period = hour >= 12 ? 'PM' : 'AM';

                slots.push(`${hour12}:00 ${period}`);

                // Don't add 30-minute slot for 9 PM
                if (hour < 21) {
                    slots.push(`${hour12}:30 ${period}`);
                }
            }
            return slots;
        };

        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const timeSlots = generateTimeSlots();

        // Create calendar grid
        const createCalendarGrid = () => {
            const calendar = document.getElementById('schedule-calendar');
            calendar.innerHTML = '';

            // Header row
            calendar.innerHTML += '<div class="time-header">Time</div>';
            days.forEach(day => {
                calendar.innerHTML += `<div class="day-header">${day}</div>`;
            });

            // Time rows
            timeSlots.forEach(timeSlot => {
                calendar.innerHTML += `<div class="time-slot">${timeSlot}</div>`;
                days.forEach(day => {
                    calendar.innerHTML +=
                        `<div class="schedule-cell" data-day="${day}" data-time="${timeSlot}"></div>`;
                });
            });
        };

        // Convert time string to minutes for comparison
        const timeToMinutes = (timeStr) => {
            const [time, period] = timeStr.split(' ');
            const [hours, minutes] = time.split(':').map(Number);
            let totalHours = hours;
            if (period === 'PM' && hours !== 12) totalHours += 12;
            if (period === 'AM' && hours === 12) totalHours = 0;
            return totalHours * 60 + minutes;
        };

        // Format time for display
        const formatTime = (timeStr) => {
            const [hours, minutes] = timeStr.split(':').map(Number);
            const period = hours >= 12 ? 'PM' : 'AM';
            const hour12 = hours % 12 || 12;
            return `${hour12}:${minutes.toString().padStart(2, '0')} ${period}`;
        };

        // Only one view at a time, only relevant filters visible
        const getCurrentScheduleFilters = () => {
            const viewType = document.getElementById('viewTypeSelect').value;
            if (viewType === 'program') {
                return {
                    view_type: viewType,
                    program: document.getElementById('programFilterSelect').value,
                    section: document.getElementById('sectionFilterSelect').value
                };
            } else if (viewType === 'instructor') {
                return {
                    view_type: viewType,
                    instructor: document.getElementById('instructorFilterSelect').value
                };
            }
            return {
                view_type: viewType
            };
        };

        const toggleFilterVisibility = () => {
            const viewType = document.getElementById('viewTypeSelect').value;
            document.getElementById('programSectionFilters').style.display = (viewType === 'program') ?
                'flex' : 'none';
            document.getElementById('instructorFilters').style.display = (viewType === 'instructor') ?
                'flex' : 'none';
        };

        // Calculate schedule item position and height
        const calculateSchedulePosition = (startTime, endTime) => {
            const startMinutes = timeToMinutes(startTime);
            const endMinutes = timeToMinutes(endTime);

            // Find the starting slot index
            let startSlotIndex = -1;
            let endSlotIndex = -1;

            for (let i = 0; i < timeSlots.length; i++) {
                const slotMinutes = timeToMinutes(timeSlots[i]);
                if (startSlotIndex === -1 && slotMinutes >= startMinutes) {
                    startSlotIndex = i;
                }
                if (slotMinutes < endMinutes) {
                    endSlotIndex = i;
                }
            }

            const slotHeight = 40; // min-height of schedule-cell
            const slotsSpanned = Math.max(1, endSlotIndex - startSlotIndex + 1);

            return {
                startSlotIndex,
                top: 0,
                height: (slotsSpanned * slotHeight) - 2 // -2 for margins
            };
        };

        // Load schedules
        const loadSchedules = () => {
            const filters = getCurrentScheduleFilters();

            // Only fetch if section is selected for program view, or instructor for instructor view
            if (!filters.view_type ||
                (filters.view_type === 'program' && (!filters.program || !filters.section)) ||
                (filters.view_type === 'instructor' && !filters.instructor)) {

                document.querySelectorAll('.schedule-cell').forEach(cell => {
                    cell.innerHTML = '';
                });
                return;
            }

            let url = `includes/tabs/get_table.php?table=user_schedules&all=1`;
            if (filters.program) url += `&program=${encodeURIComponent(filters.program)}`;
            if (filters.section) url += `&section=${encodeURIComponent(filters.section)}`;
            if (filters.instructor) url += `&instructor=${encodeURIComponent(filters.instructor)}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    // Clear existing schedule items
                    document.querySelectorAll('.schedule-cell').forEach(cell => {
                        cell.innerHTML = '';
                    });

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(schedule => {
                            const startTime = formatTime(schedule.start_time);
                            const endTime = formatTime(schedule.end_time);
                            const position = calculateSchedulePosition(startTime, endTime);

                            // Find the starting cell for this schedule
                            const startCell = document.querySelector(
                                `[data-day="${schedule.day_of_week}"][data-time="${timeSlots[position.startSlotIndex]}"]`
                            );

                            if (startCell && position.startSlotIndex !== -1) {
                                const scheduleItem = document.createElement('div');
                                scheduleItem.className = 'schedule-item';
                                scheduleItem.style.height = `${position.height}px`;
                                scheduleItem.style.top = `${position.top}px`;

                                const viewType = filters.view_type;
                                let displayContent = '';

                                // --- Add edit/delete buttons ---
                                displayContent += `
                                    <div class="d-flex justify-content-end gap-1 mb-1">
                                        <button class="btn btn-sm btn-primary edit-btn" 
                                            data-table="user_schedules" data-id="${schedule.id}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                            data-table="user_schedules" data-id="${schedule.id}" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                `;

                                if (viewType === 'program') {
                                    displayContent += `
                                        <span class="course-code">${schedule.class_name}</span> <br>
                                        <span class="program-info">${schedule.program_name || 'N/A'}</span> <br>
                                        <span class="section-info">Section: ${schedule.section || 'N/A'}</span> <br>
                                        <span class="instructor">
                                            ${schedule.first_name ? `${schedule.first_name} ${schedule.last_name}` : 'N/A'}
                                        </span> <br>
                                        <span class="time-info">${startTime} - ${endTime}</span> <br>
                                    `;
                                } else if (viewType === 'instructor') {
                                    displayContent += `
                                        <span class="course-code">${schedule.class_name}</span> <br>
                                        <span class="program-info">${schedule.program_name || 'N/A'}</span> <br>
                                        <span class="section-info">Section: ${schedule.section || 'N/A'}</span> <br>
                                        <span class="instructor">
                                            ${schedule.first_name ? `${schedule.first_name} ${schedule.last_name}` : 'N/A'}
                                        </span> <br>
                                        <span class="time-info">${startTime} - ${endTime}</span>
                                    `;
                                }

                                scheduleItem.innerHTML = displayContent;
                                const deleteBtn = scheduleItem.querySelector('.delete-btn');
                                if (deleteBtn) {
                                    const selectedScheduleLabel = `${schedule.class_name || 'Class'} (${schedule.day_of_week || 'Day'} ${startTime} - ${endTime})`;
                                    deleteBtn.dataset.deleteLabel = selectedScheduleLabel;
                                }
                                scheduleItem.setAttribute('data-id', schedule.id);
                                scheduleItem.setAttribute('title',
                                    `${schedule.class_name} (${startTime} - ${endTime})`);

                                // --- Remove old click-to-edit logic ---

                                startCell.appendChild(scheduleItem);
                            }
                        });

                        // --- Connect edit/delete buttons to main modal logic ---
                        // Use event delegation for dynamically added buttons
                        document.querySelectorAll('.schedule-item .edit-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Trigger main edit modal logic
                                $(this).trigger('click.editBtn');
                            });
                        });
                        document.querySelectorAll('.schedule-item .delete-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Trigger main delete modal logic
                                $(this).trigger('click.deleteBtn');
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading schedules:', error);
                });
        };

        // Initialize calendar
        const initializeSchedulesTab = () => {
            const schedulesTab = document.getElementById('schedules');
            if (schedulesTab && (schedulesTab.classList.contains('active') || schedulesTab.classList
                    .contains('show'))) {
                createCalendarGrid();
                // Do not load schedules on init. Wait for user filter selection.
            }
        };

        // Filter events (only relevant ones)
        document.getElementById('viewTypeSelect').addEventListener('change', function() {
            toggleFilterVisibility();
            // Reset other filters when view changes
            document.getElementById('programFilterSelect').value = "";
            document.getElementById('sectionFilterSelect').innerHTML =
                '<option value="">Select Section</option>';
            document.getElementById('sectionFilterSelect').disabled = true;
            document.getElementById('instructorFilterSelect').value = "";

            const college = document.getElementById('collegeFilterSelect').value;
            if (this.value === 'program' && college) {
                fetch(`includes/tabs/load_programs.php?college=${encodeURIComponent(college)}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.text();
                    })
                    .then(html => {
                        document.getElementById('programFilterSelect').innerHTML =
                            '<option value="">Select Program</option>' + html;
                        document.getElementById('programFilterSelect').disabled = false;
                    })
                    .catch(err => {
                        console.error('Error loading programs for college:', err);
                        alert('Failed to load programs for selected college.');
                    });
            }

            if (this.value === 'instructor') {
                document.getElementById('instructorFilterSelect').innerHTML =
                    '<option value="">Select Instructors</option>';
                window.loadInstructorsForCollege(college);
            }

            loadSchedules(); // This will clear the board
        });
        document.getElementById('programFilterSelect').addEventListener('change', function() {
            // Section dropdown is populated by its own event above
            loadSchedules();
        });
        document.getElementById('sectionFilterSelect').addEventListener('change', loadSchedules);
        document.getElementById('instructorFilterSelect').addEventListener('change', loadSchedules);

        // Tab visibility event listeners
        document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                if (e.target.id === 'schedules-tab') {
                    initializeSchedulesTab();
                }
            });
        });

        // Initialize
        setTimeout(initializeSchedulesTab, 100);
        setTimeout(initializeSchedulesTab, 500);
        setTimeout(initializeSchedulesTab, 1000);

        // Make functions available globally for compatibility
        window.getCurrentScheduleFilters = getCurrentScheduleFilters;
        window.reloadCurrentScheduleView = loadSchedules;

        // Initialize view toggle
        toggleFilterVisibility();
    });
    </script>
</div>