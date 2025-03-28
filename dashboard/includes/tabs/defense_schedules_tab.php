<!-- Defense Schedules Tab -->
<div class="tab-pane fade my-3" id="defense-schedules" role="tabpanel" aria-labelledby="defense-schedules-tab">
<button class="btn feature-btn add-btn" data-table="defense_schedules">
            <i class="fas fa-plus"></i>Add Defense Schedule
        </button>
    <div class="d-flex align-items-center mb-3 my-3">
        <div class="modal fade" id="schedulerSettingsModal" tabindex="-1" aria-labelledby="schedulerSettingsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="schedulerSettingsModalLabel">Scheduler Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="schedulerSettingsForm">
                            <div class="mb-3">
                                <label for="rooms" class="form-label">Rooms (comma-separated)</label>
                                <input type="text" class="form-control" id="rooms" name="rooms" required>
                            </div>
                            <div class="mb-3">
                                <label for="timeDuration" class="form-label">Time Duration (hours)</label>
                                <input type="number" class="form-control" id="timeDuration" name="timeDuration" min="1" max="5" required 
                                    oninput="this.value = Math.min(5, Math.max(1, Math.round(this.value)))">
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const timeDurationInput = document.getElementById('timeDuration');
                                    const startTimeInput = document.getElementById('startTime');
                                    const endTimeInput = document.getElementById('endTime');
                                    const daysInput = document.getElementById('days');
                                    const roomsInput = document.getElementById('rooms');
                                    const saveButton = document.getElementById('saveSchedulerSettings');
                                    const statusElement = document.getElementById('scheduleGenerationStatus');
                                    const warningElement = document.createElement('div');
                                    warningElement.className = 'text-danger';

                                    // Limit time inputs to working hours (7 AM to 8 PM)
                                    startTimeInput.min = "07:00";
                                    startTimeInput.max = "20:30";
                                    endTimeInput.min = "07:00";
                                    endTimeInput.max = "20:30";

                                    // Prevent selecting previous dates
                                    const now = new Date();
                                    const todayMonth = String(now.getMonth() + 1).padStart(2, '0');
                                    const todayDay = String(now.getDate()).padStart(2, '0');
                                    const todayYear = now.getFullYear();
                                    const today = `${todayMonth}-${todayDay}-${todayYear}`;
console.log('Today:', today);
                                    // Validate inputs and enable/disable save button
                                    function validateInputs() {
                                        const startTime = startTimeInput.value;
                                        const endTime = endTimeInput.value;
                                        const timeDuration = parseInt(timeDurationInput.value);
                                        const days = daysInput.value.split(',').filter(date => date >= today).length;
                                        const rooms = roomsInput.value.split(',').length;
                                        const includeLunchBreak = document.getElementById('includeLunchBreak').checked;

                                        let isValid = true;
                                        let warningMessage = '';

                                        if (!startTime || !endTime || !timeDuration || days === 0 || rooms === 0) {
                                            isValid = false;
                                            warningMessage = 'All fields are required.';
                                        } else {
                                            const startTimeParts = startTime.split(':');
                                            const endTimeParts = endTime.split(':');
                                            const startHour = parseInt(startTimeParts[0]) + parseInt(startTimeParts[1]) / 60;
                                            const endHour = parseInt(endTimeParts[0]) + parseInt(endTimeParts[1]) / 60;
                                            console.log('Start Hour:', startHour);
console.log('End Hour:', endHour);
                                            const startAMPM = startHour >= 12 ? 'PM' : 'AM';
                                            const endAMPM = endHour >= 12 ? 'PM' : 'AM';

                                            let availableHours = endHour - startHour;
                                            const numberOfTeams = <?php echo $totalTeams; ?>;

                                            if (includeLunchBreak && startHour <= 12 && endHour >= 13) {
                                                availableHours -= 1; // Subtract 1 hour for lunch break
                                            }

                                            const slotsPerRoomPerDay = Math.floor(availableHours / timeDuration);
                                            const totalSlots = slotsPerRoomPerDay * rooms * days;
                                            console.log('Total Slots:', totalSlots);

                                            if (availableHours < timeDuration) {
                                                isValid = false;
                                                warningMessage = 'Duration exceeds available hours.';
                                            } else if (startHour < 7 || endHour > 20.5) {
                                                isValid = false;
                                                warningMessage = 'Time must be within working hours (7:00 AM to 8:30 PM).';
                                            } else if (totalSlots < numberOfTeams) {
                                                isValid = false;
                                                warningMessage = 'Not enough time slots for all teams.';
                                            } else if (daysInput.value.split(',').some(date => new Date(date) < new Date(today))) {
                                                isValid = false;
                                                warningMessage = 'Please select dates that are today or later.';
                                            }
                                        }

                                        if (!isValid) {
                                            saveButton.disabled = true;
                                            statusElement.innerText = warningMessage;
                                            warningElement.innerText = warningMessage;
                                            saveButton.parentNode.insertBefore(warningElement, saveButton.nextSibling);
                                        } else {
                                            saveButton.disabled = false;
                                            statusElement.innerText = 'Settings are valid. You can generate the schedule.';
                                            if (warningElement.parentNode) {
                                                warningElement.parentNode.removeChild(warningElement);
                                            }
                                        }
                                    }

                                    timeDurationInput.addEventListener('input', validateInputs);
                                    startTimeInput.addEventListener('input', validateInputs);
                                    endTimeInput.addEventListener('input', validateInputs);
                                    daysInput.addEventListener('input', validateInputs);
                                    roomsInput.addEventListener('input', validateInputs);

                                    validateInputs(); // Initial validation
                                });
                            </script>
                            <div class="mb-3">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" min="07:00 AM" max="20:00 PM" step="1800" required 
                                    onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                            </div>
                            <div class="mb-3">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime" min="07:00" max="20:00" step="1800" required
                                    onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                            </div>
                            <div class="mb-3">
                                <label for="days" class="form-label">Days (Select multiple dates if necessary)</label>
                                <input type="text" class="form-control datepicker" id="days" name="days" required>
                                <small id="daysHelp" class="form-text text-muted">Click to select dates. Multiple dates can be selected.</small>
                            </div>
                            <script>
                                $(document).ready(function() {
                                    $('.datepicker').datepicker({
                                        format: 'mm-dd-yyyy',
                                        multidate: true,
                                        startDate: new Date(),
                                        todayHighlight: true
                                    });
                                });
                            </script>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="includeLunchBreak" name="includeLunchBreak">
                                <label class="form-check-label" for="includeLunchBreak">Include Lunch Break (12 PM - 1 PM)</label>
                            </div>
                            
                            <!-- Add Program Selection Dropdown -->
                            <div class="mb-3">
                                <label for="programSelect" class="form-label">Program Filter</label>
                                <select class="form-select" id="programSelect" name="program">
                                    <option value="">All Programs</option>
                                    <?php
                                    // Get unique programs from teams table
                                    $stmt = $pdo->prepare("SELECT DISTINCT program FROM teams WHERE program IS NOT NULL ORDER BY program");
                                    $stmt->execute();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value=\"" . htmlspecialchars($row['program']) . "\">" . htmlspecialchars($row['program']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">Leave blank to include all programs</small>
                            </div>
                        </form>
                    </div>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teams");
                    if ($stmt->execute()) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalTeams = $row['total'] ?? 0;
                    } else {
                        $totalTeams = 0;
                    }
                    ?>

                    <script>
                        document.getElementById('saveSchedulerSettings').addEventListener('click', function() {
                            const rooms = document.getElementById('rooms').value.split(',').length;
                            const timeDuration = parseInt(document.getElementById('timeDuration').value);
                            const startTimeParts = document.getElementById('startTime').value.split(':');
                            const endTimeParts = document.getElementById('endTime').value.split(':');
                            const startTime = parseInt(startTimeParts[0]) + parseInt(startTimeParts[1]) / 60;
                            const endTime = parseInt(endTimeParts[0]) + parseInt(endTimeParts[1]) / 60;
                            const days = document.getElementById('days').value.split(',').filter(date => date >= today).length;
                            

                            // Assume numberOfTeams is available globally or fetched from the server
                            const numberOfTeams = <?php echo $totalTeams; ?>;

                            let availableHours = endTime - startTime;
                            if (includeLunchBreak && startTime <= 12 && endTime >= 13) {
                                availableHours -= 1; // Subtract 1 hour for lunch break
                            }
                            const slotsPerRoomPerDay = Math.floor(availableHours / timeDuration);
                            const totalSlots = slotsPerRoomPerDay * rooms * days;

                            const statusElement = document.getElementById('scheduleGenerationStatus');

                            if (totalSlots < numberOfTeams) {
                                statusElement.innerText = 'Cannot generate schedule: Not enough time slots for all teams.';
                                document.getElementById('generateSchedule').disabled = true;
                            } else {
                                statusElement.innerText = 'Settings are valid. You can generate the schedule.';
                                document.getElementById('generateSchedule').disabled = false;
                            }
                        });
                    </script>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveSchedulerSettings">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduler Settings Button -->
        <button type="button" class="btn feature-btn scheduler-btn" data-bs-toggle="modal" data-bs-target="#schedulerSettingsModal">
            <i class="fas fa-cog me-1"></i>Scheduler Settings
        </button>

        <div id="generationSetting"></div>
        <!-- Schedule Generation Status -->
        <div id="scheduleGenerationStatus"></div>

        <!-- Generate Schedule Button -->
        <button id="generateSchedule" class="btn feature-btn generate-btn" disabled>
            <i class="fas fa-calendar-plus me-1"></i>Generate Defense Schedule
        </button>
        <?php
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM defense_schedules");
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalScheds = $row['total'] ?? 0;
        } else {
            $totalScheds = 0;
        }
        ?>
        <script>
            let totalScheds = <?php echo $totalScheds; ?>;

            document.getElementById("saveSchedulerSettings").addEventListener("click", function(){
        const settingsOutput = "Rooms: " + document.getElementById("rooms").value + "<br>" +
            "Time Duration: " + document.getElementById("timeDuration").value + " hours<br>" +
            "Start Time: " + document.getElementById("startTime").value + "<br>" +
            "End Time: " + document.getElementById("endTime").value + "<br>" +
            "Days: " + document.getElementById("days").value + "<br>" +
            "Include Lunch Break: " + (document.getElementById("includeLunchBreak").checked ? "Yes" : "No");    
        document.getElementById("generationSetting").innerHTML = settingsOutput;
    });
        </script>

        <span id="scheduleGenerationStatusSpan" class="ml-2"></span> <!-- Changed ID to ensure uniqueness -->
    </div>
    <div class="table-responsive db-table-container" id="def-sched">
        <table class="table table-bordered table-hover table-sm db-table" id="def-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Team</th>
                    <th>Adviser</th>
                    <th>Thesis Title</th>
                    <th>Panelist 1</th>
                    <th>Panelist 2</th>
                    <th>Panelist 3</th>
                    <th>Room</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Existing PHP-generated rows removed -->
            </tbody>
        </table>

        <nav id="def-nav" aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Pagination loaded via AJAX -->
            </ul>
        </nav>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Format date to "Mar 15, 2025" style
                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const options = { month: 'short', day: 'numeric', year: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                };

                // Format time from "07:00:00" to "7:00 AM" style
                const formatTime = (timeStr) => {
                    // Parse the time (assuming timeStr is in format "HH:MM:SS" or "HH:MM")
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    const period = hours >= 12 ? 'PM' : 'AM';
                    const hour12 = hours % 12 || 12; // Convert to 12-hour format
                    return `${hour12}:${minutes.toString().padStart(2, '0')} ${period}`;
                };
                
                // Function to split panelists into an array of up to 3 panelists
                const splitPanelists = (panelistsString) => {
                    // Split panelists by comma
                    const panelists = panelistsString.split(',').map(p => p.trim()).filter(p => p);
                    
                    // Create an array of exactly 3 elements (fill with empty strings if fewer than 3)
                    return [
                        panelists[0] || '',
                        panelists[1] || '',
                        panelists[2] || ''
                    ];
                };

                const loadDefenseSchedules = (page = 1) => {
                    console.log(`Loading Defense Schedules Page: ${page}`);
                    fetch(`../dashboard/includes/tabs/get_table.php?table=defense_schedules&page=${page}`) // Changed to absolute path
                        .then(response => {
                            console.log('Fetch Response Status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Data Received:', data);
                            if (data.error) {
                                console.error('Server Error:', data.error);
                                document.getElementById('scheduleGenerationStatus').innerText = `Error: ${data.error}`;
                                return;
                            }

                            const tbody = document.querySelector('#def-table tbody'); // Updated selector
                            tbody.innerHTML = '';
                            data.data.forEach(schedule => {
                                // Format date and time with the new helper functions
                                const formattedDate = formatDate(schedule.schedule_date);
                                const formattedStartTime = formatTime(schedule.start_time);
                                const formattedEndTime = formatTime(schedule.end_time);
                                const dateTime = `${formattedDate} ${formattedStartTime} - ${formattedEndTime}`;
                                
                                // Get panelists as an array of 3 elements
                                const panelists = splitPanelists(schedule.panelists);

                                tbody.innerHTML += `
                                    <tr>
                                        <td>${dateTime}</td>
                                        <td>${schedule.team_name}</td>
                                        <td>${schedule.adviser}</td>
                                        <td>${schedule.thesis_title}</td>
                                        <td>${panelists[0]}</td>
                                        <td>${panelists[1]}</td>
                                        <td>${panelists[2]}</td>
                                        <td>${schedule.room}</td>
                                        
                                        <td class="action-buttons">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button class="btn btn-sm edit-btn" data-table="defense_schedules" data-id="${schedule.id}">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </button>
                                                <button class="btn btn-sm delete-btn" data-table="defense_schedules" data-id="${schedule.id}">
                                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });

                            const pagination = document.querySelector('#def-nav .pagination'); // Updated selector
                            pagination.innerHTML = '';

                            // Previous Button
                            pagination.innerHTML += `
                                <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">
                                        &#8249;
                                    </a>
                                </li>
                            `;

                            // Page Numbers
                            for (let i = 1; i <= data.total_pages; i++) {
                                pagination.innerHTML += `
                                    <li class="page-item ${page === i ? 'active' : ''}">
                                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                                    </li>
                                `;
                            }

                            // Next Button
                            pagination.innerHTML += `
                                <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">
                                        &#8250;
                                    </a>
                                </li>
                            `;
                        })
                        .catch(error => {
                            console.error('Fetch Error:', error);
                            document.getElementById('scheduleGenerationStatus').innerText = `Fetch Error: ${error.message}`;
                        });
                };

                // Initial load
                loadDefenseSchedules();

                // Handle pagination clicks
                document.querySelector('#def-nav .pagination').addEventListener('click', function(e) { // Updated selector
                    e.preventDefault();
                    if (e.target.tagName === 'A') {
                        const page = parseInt(e.target.getAttribute('data-page'));
                        if (!isNaN(page)) {
                            console.log(`Pagination Clicked: Loading Page ${page}`);
                            loadDefenseSchedules(page);
                        }
                    }
                });
            });
        </script>
    </div>
</div>