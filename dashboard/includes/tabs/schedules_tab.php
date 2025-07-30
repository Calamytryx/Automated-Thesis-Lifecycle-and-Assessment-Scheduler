<!-- Schedules Tab -->
<?php
require_once '../assets/setup/db.inc.php'; // Adjust path as needed
?>
<div class="tab-pane fade" id="schedules" role="tabpanel" aria-labelledby="schedules-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">User Schedules Management</h3>
                <p class="text-muted">Manage class schedules and time availability for users</p>
            </div>
        </div>

        <!-- Controls -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="btn-group" role="group" aria-label="View type toggle">
                            <button type="button" class="btn btn-outline-primary view-type-btn" data-view="program">By
                                Program/Section</button>
                            <button type="button" class="btn btn-outline-primary view-type-btn"
                                data-view="instructor">By Instructor</button>
                        </div>
                        <input type="hidden" id="viewTypeSelect" value="">
                    </div>
                    <!-- Program and Section Filters -->
                    <div class="d-flex gap-2 align-items-center" id="programSectionFilters">
                        <select class="form-select" id="programFilterSelect" style="width: 200px; display: none;">
                            <option value="">Select Program</option>
                            <?php
                            if ($_SESSION['usertype'] == '0' && $_SESSION['id'] == '0') {
                                // Super admin: show all programs
                                $stmt = $pdo->query("SELECT id, name, specialization FROM programs WHERE name IS NOT NULL ORDER BY name");
                                if ($stmt) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value=\"{$row['id']}\">" . htmlspecialchars($row['name']) .
                                            ($row['specialization'] ? " - " . htmlspecialchars($row['specialization']) : "") .
                                            "</option>";
                                    }
                                }
                            } else if ($_SESSION['usertype'] == '0' && $_SESSION['id'] != '0') {
                                // Admin: show only programs from their college
                                $user_id = $_SESSION['id'];
                                $stmt = $pdo->prepare("SELECT program FROM users WHERE id = ?");
                                $stmt->execute([$user_id]);
                                $user_program = trim($stmt->fetchColumn());

                                if ($user_program) {
                                    $stmt2 = $pdo->prepare("
                                        SELECT college FROM programs 
                                        WHERE TRIM(CONCAT(name, CASE WHEN specialization IS NOT NULL AND specialization != '' THEN CONCAT(' - ', specialization) ELSE '' END)) = ?
                                    ");
                                    $stmt2->execute([$user_program]);
                                    $user_college = $stmt2->fetchColumn();

                                    if ($user_college) {
                                        $stmt3 = $pdo->prepare("SELECT id, name, specialization FROM programs WHERE college = ? ORDER BY name");
                                        $stmt3->execute([$user_college]);
                                        while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value=\"{$row['id']}\">" . htmlspecialchars($row['name']) .
                                                ($row['specialization'] ? " - " . htmlspecialchars($row['specialization']) : "") .
                                                "</option>";
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                        <select class="form-select" id="sectionFilterSelect" style="width: 180px; display: none;"
                            disabled>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    <!-- Instructor Filter -->
                    <div class="d-flex gap-2 align-items-center" id="instructorFilters">
                        <select class="form-select" id="instructorFilterSelect" style="width: 220px; display: none;">
                            <option value="">All Instructors</option>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT DISTINCT u.id, u.first_name, u.last_name 
                                    FROM users u 
                                    INNER JOIN user_schedules us ON u.id = us.user_id 
                                    WHERE u.first_name IS NOT NULL AND u.last_name IS NOT NULL 
                                    ORDER BY u.last_name, u.first_name
                                ");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $fullName = htmlspecialchars($row['last_name'] . ', ' . $row['first_name']);
                                    echo "<option value=\"" . htmlspecialchars($row['id']) . "\">{$fullName}</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=\"\">Error loading instructors</option>";
                                error_log("Database error: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const viewTypeButtons = document.querySelectorAll('.view-type-btn');
                        const viewTypeInput = document.getElementById('viewTypeSelect');
                        const programSelect = document.getElementById("programFilterSelect");
                        const sectionSelect = document.getElementById("sectionFilterSelect");
                        const instructorSelect = document.getElementById("instructorFilterSelect");

                        function updateFilterVisibility() {
                            const selectedView = viewTypeInput.value;

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
        background: #007bff;
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
        border: 1px solid #0056b3;
    }

    .schedule-item:hover {
        background: #0056b3;
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

        // Modal for schedule actions (edit/delete)
        let scheduleActionModal = null;
        function showScheduleActionModal(scheduleId) {
            if (!scheduleActionModal) {
                scheduleActionModal = document.createElement('div');
                scheduleActionModal.className = 'modal fade';
                scheduleActionModal.id = 'scheduleActionModal';
                scheduleActionModal.tabIndex = -1;
                scheduleActionModal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Schedule Options</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <button type="button" class="btn btn-primary mb-2 w-100" id="editScheduleBtn">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </button>
                                <button type="button" class="btn btn-danger w-100" id="deleteScheduleBtn">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(scheduleActionModal);
            }

            // Attach event listeners for edit and delete
            scheduleActionModal.querySelector('#editScheduleBtn').onclick = function() {
                // Trigger edit like other tables
                const editBtn = document.createElement('button');
                editBtn.className = 'btn btn-sm edit-btn';
                editBtn.setAttribute('data-table', 'user_schedules');
                editBtn.setAttribute('data-id', scheduleId);
                document.body.appendChild(editBtn);
                editBtn.click();
                editBtn.remove();
                bootstrap.Modal.getOrCreateInstance(scheduleActionModal).hide();
            };
            scheduleActionModal.querySelector('#deleteScheduleBtn').onclick = function() {
                // Trigger delete like other tables
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'btn btn-sm delete-btn';
                deleteBtn.setAttribute('data-table', 'user_schedules');
                deleteBtn.setAttribute('data-id', scheduleId);
                document.body.appendChild(deleteBtn);
                deleteBtn.click();
                deleteBtn.remove();
                bootstrap.Modal.getOrCreateInstance(scheduleActionModal).hide();
            };

            // Show modal
            bootstrap.Modal.getOrCreateInstance(scheduleActionModal).show();
        }

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

                                if (viewType === 'program') {
                                    displayContent = `
                                            <span class="course-code">${schedule.class_name}</span> <br>
                                            <span class="program-info">${schedule.program_name || 'N/A'}</span> <br>
                                            <span class="section-info">Section: ${schedule.section || 'N/A'}</span> <br>
                                            <span class="instructor">
                                                ${schedule.first_name ? `${schedule.first_name} ${schedule.last_name}` : 'N/A'}
                                            </span> <br>
                                            <span class="time-info">${startTime} - ${endTime}</span> <br>
                                        `;
                                } else if (viewType === 'instructor') {
                                    displayContent = `
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
                                scheduleItem.setAttribute('data-id', schedule.id);
                                scheduleItem.setAttribute('title',
                                    `${schedule.class_name} (${startTime} - ${endTime})`);

                                // Show modal with Edit/Delete options
                                scheduleItem.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    showScheduleActionModal(schedule.id);
                                });

                                startCell.appendChild(scheduleItem);
                            }
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