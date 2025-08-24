<!-- Defense Schedules Tab -->
<div class="tab-pane fade" id="defense-schedules" role="tabpanel" aria-labelledby="defense-schedules-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Defense Schedules Management</h3>
                <p class="text-muted">Manage thesis defense schedules, generate automated schedules, and assign panelists to teams</p>
            </div>
        </div>

        <!-- Defense Schedule Management Controls -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
                    <button class="btn feature-btn add-btn" data-table="defense_schedules">
                        <i class="fas fa-plus me-2"></i>Add Defense Schedule
                    </button>
                    <button type="button" class="btn feature-btn scheduler-btn" data-bs-toggle="modal" data-bs-target="#schedulerSettingsModal">
                        <i class="fas fa-cog me-2"></i>Scheduler Settings
                    </button>
                    <button id="generateSchedule" type="button" class="btn feature-btn generate-btn" disabled>
                        <i class="fas fa-calendar-plus me-2"></i>Generate Defense Schedule
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings display area -->
        <div class="row mb-3">
            <div class="col-12">
                <div id="generationSetting" class="bg-light p-3 rounded" style="display: none;"></div>
                <span id="scheduleGenerationStatusSpan" class="text-muted"></span>
            </div>
        </div>

        <!-- Modal for Scheduler Settings -->
        <div class="modal fade" id="schedulerSettingsModal" tabindex="-1" aria-labelledby="schedulerSettingsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="schedulerSettingsModalLabel">Scheduler Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="schedulerSettingsForm" novalidate onsubmit="event.preventDefault(); event.stopImmediatePropagation(); return false;">
                            <div class="mb-3">
                                <label for="rooms" class="form-label">Rooms (comma-separated)</label>
                                <input type="text" class="form-control" id="rooms" name="rooms" required>
                            </div>
                            <div class="mb-3">
                                <label for="timeDuration" class="form-label">Time Duration (hours)</label>
                                <input type="number" class="form-control" id="timeDuration" name="timeDuration" min="0.25" max="5" step="0.25" required>
                            </div>
                            <script>
                                // Define validateInputs and other functions at the global scope
                                // Declare global variables
                                let timeDurationInput, startTimeInput, endTimeInput, daysInput, roomsInput, programSelect, 
                                    saveButton, statusElement, includeLunchBreakCheckbox;

                                // Define the correctTimeDuration function in global scope
                                function correctTimeDuration() {
                                    if (!timeDurationInput) return; // Guard against undefined 
                                    
                                    let value = parseFloat(timeDurationInput.value);
                                    const minVal = parseFloat(timeDurationInput.min) || 0.25;
                                    const maxVal = parseFloat(timeDurationInput.max) || 5;
                                    const stepVal = parseFloat(timeDurationInput.step) || 0.25;

                                    if (isNaN(value)) {
                                        value = minVal;
                                    }

                                    // Enforce min/max
                                    if (value < minVal) {
                                        value = minVal;
                                    } else if (value > maxVal) {
                                        value = maxVal;
                                    }

                                    // Enforce step interval
                                    const remainder = value % stepVal;
                                    if (remainder !== 0) {
                                        // Round to the nearest step
                                        value = Math.round(value / stepVal) * stepVal;
                                        // Re-check min/max after rounding
                                        if (value < minVal) value = minVal;
                                        if (value > maxVal) value = maxVal;
                                    }

                                    // Update the input value if it changed
                                    const correctedValueStr = value.toFixed(2);
                                    if (timeDurationInput.value !== correctedValueStr) {
                                        timeDurationInput.value = correctedValueStr;
                                    }
                                }

                                // Define validateInputs in global scope
                                function validateInputs() {
                                    // Guard against undefined elements
                                    if (!startTimeInput || !endTimeInput || !timeDurationInput || !daysInput || !roomsInput || 
                                        !statusElement || !includeLunchBreakCheckbox || !saveButton) {
                                        console.error('Required DOM elements not initialized yet');
                                        return;
                                    }

                                    const startTime = startTimeInput.value;
                                    const endTime = endTimeInput.value;
                                    const timeDuration = parseFloat(timeDurationInput.value);
                                    const daysValue = daysInput.value;
                                    const daysArray = daysValue ? daysValue.split(',') : [];
                                    const days = daysArray.filter(date => date.trim() !== '').length;
                                    const roomsValue = roomsInput.value;
                                    const roomsArray = roomsValue ? roomsValue.split(',') : [];
                                    const rooms = roomsArray.filter(room => room.trim() !== '').length;
                                    const includeLunchBreak = includeLunchBreakCheckbox.checked;
                                    const numberOfTeams = parseInt(document.getElementById('selectedTeamCount').value) || 0;

                                    let isValid = true;
                                    let warningMessage = '';

                                    console.log('Validation - Start:', startTime, 'End:', endTime, 'Duration:', timeDuration, 'Days:', days, 'Rooms:', rooms, 'Teams:', numberOfTeams);

                                    if (!startTime || !endTime || !timeDuration || days === 0 || rooms === 0 || !programSelect) {
                                        isValid = false;
                                        warningMessage = 'All fields (Rooms, Duration, Start/End Time, Days) are required.';
                                    } else {
                                        const startTimeParts = startTime.split(':');
                                        const endTimeParts = endTime.split(':');
                                        const startHour = parseInt(startTimeParts[0]) + parseInt(startTimeParts[1]) / 60;
                                        const endHour = parseInt(endTimeParts[0]) + parseInt(endTimeParts[1]) / 60;

                                        if (startHour >= endHour) {
                                            isValid = false;
                                            warningMessage = 'End time must be after start time.';
                                        } else {
                                            let availableHours = endHour - startHour;

                                            if (includeLunchBreak && startHour <= 12 && endHour >= 13) {
                                                availableHours -= 1; // Subtract 1 hour for lunch break
                                            }

                                            const slotsPerRoomPerDay = Math.floor(availableHours / timeDuration);
                                            const totalSlots = slotsPerRoomPerDay * rooms * days;
                                            console.log('Calculated Slots - Per Room/Day:', slotsPerRoomPerDay, 'Total:', totalSlots);

                                            if (availableHours < timeDuration) {
                                                isValid = false;
                                                warningMessage = 'Duration exceeds available hours per day.';
                                            } else if (startHour < 7 || endHour > 20.5) {
                                                isValid = false;
                                                warningMessage = 'Time must be within working hours (7:00 AM to 8:30 PM).';
                                            } else if (totalSlots < numberOfTeams) {
                                                isValid = false;
                                                warningMessage = `Not enough time slots (${totalSlots}) for the selected teams (${numberOfTeams}). Adjust settings or filter.`;
                                            }
                                        }
                                    }

                                    // Update UI based on validation result
                                    if (!isValid) {
                                        saveButton.disabled = true;
                                        statusElement.innerText = warningMessage;
                                        statusElement.classList.add('text-danger');
                                        statusElement.classList.remove('text-success');
                                    } else {
                                        saveButton.disabled = false;
                                        statusElement.innerText = 'Settings are valid. You can generate the schedule.';
                                        statusElement.classList.remove('text-danger');
                                        statusElement.classList.add('text-success');
                                    }
                                }

                                // Define updateTeamCount in global scope
                                function updateTeamCount(callback) {
                                    if (!programSelect) {
                                        console.error('programSelect is not initialized');
                                        return;
                                    }
                                    const selectedProgram = programSelect.value;
                                    console.log('updateTeamCount - Selected program:', selectedProgram);
                                    $('#selectedProgram').val(selectedProgram);

                                    $.ajax({
                                        url: '../dashboard/includes/get_team_count.php',
                                        method: 'POST',
                                        data: { program: selectedProgram },
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.success) {
                                                $('#teamCountDisplay').text(response.count);
                                                $('#selectedTeamCount').val(response.count);
                                                console.log('Team count updated to:', response.count);
                                            } else {
                                                console.error("Error fetching team count:", response.message);
                                                $('#teamCountDisplay').text('Error');
                                                $('#selectedTeamCount').val(0);
                                            }
                                            
                                            if (typeof callback === 'function') {
                                                callback();
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error("AJAX Error fetching team count:", error);
                                            $('#teamCountDisplay').text('Error');
                                            $('#selectedTeamCount').val(0);
                                            if (typeof callback === 'function') {
                                                callback();
                                            }
                                        }
                                    });
                                }

                                // Initialize in DOMContentLoaded
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Initialize global variables with DOM elements
                                    timeDurationInput = document.getElementById('timeDuration');
                                    startTimeInput = document.getElementById('startTime');
                                    endTimeInput = document.getElementById('endTime');
                                    daysInput = document.getElementById('days');
                                    roomsInput = document.getElementById('rooms');
                                    programSelect = document.getElementById('programSelect');
                                    saveButton = document.getElementById('saveSchedulerSettings');
                                    statusElement = document.getElementById('scheduleGenerationStatus');
                                    includeLunchBreakCheckbox = document.getElementById('includeLunchBreak');

                                    // Limit time inputs to working hours
                                    if (startTimeInput) {
                                        startTimeInput.min = "07:00";
                                        startTimeInput.max = "20:30";
                                    }
                                    
                                    if (endTimeInput) {
                                        endTimeInput.min = "07:00";
                                        endTimeInput.max = "20:30";
                                    }

                                    // Add event listeners
                                    if (timeDurationInput) {
                                        timeDurationInput.addEventListener('input', () => {
                                            correctTimeDuration();
                                            updateTeamCount(validateInputs);
                                        });
                                        timeDurationInput.addEventListener('change', () => {
                                            correctTimeDuration();
                                            updateTeamCount(validateInputs);
                                        });
                                    }

                                    if (startTimeInput) {
                                        startTimeInput.addEventListener('input', () => updateTeamCount(validateInputs));
                                    }
                                    
                                    if (endTimeInput) {
                                        endTimeInput.addEventListener('input', () => updateTeamCount(validateInputs));
                                    }
                                    
                                    if (daysInput) {
                                        daysInput.addEventListener('change', () => updateTeamCount(validateInputs));
                                    }
                                    
                                    if (roomsInput) {
                                        roomsInput.addEventListener('input', () => updateTeamCount(validateInputs));
                                    }
                                    
                                    if (includeLunchBreakCheckbox) {
                                        includeLunchBreakCheckbox.addEventListener('change', () => updateTeamCount(validateInputs));
                                    }
                                    
                                    if (programSelect) {
                                        programSelect.addEventListener('change', () => updateTeamCount(validateInputs));
                                    }

                                    console.log('DOM Content Loaded: All event listeners attached');
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
                            <input type="hidden" id="selectedProgram" name="selectedProgram" value="">
                            <script>
                                $(document).ready(function() {
                                    // Explicitly cancel any submit event on the form
                                    $("#schedulerSettingsForm").on("submit", function(e) {
                                        e.preventDefault();
                                        e.stopImmediatePropagation();
                                        return false;
                                    });

                                    $('.datepicker').datepicker({
                                        format: 'mm-dd-yyyy',
                                        multidate: true,
                                        startDate: new Date(),
                                        todayHighlight: true
                                    });

                                    // Initial count update and validation on load
                                    console.log('jQuery ready: About to call updateTeamCount');
                                    updateTeamCount(validateInputs);
                                    
                                    // Global flag to prevent duplicate scheduler runs
                                    let schedulerRunning = false;

                                    // Update the Generate Schedule click handler
                                    document.getElementById('generateSchedule').addEventListener('click', function(e) {
                                        e.preventDefault(); // prevent default submission
                                        e.stopPropagation();
                                        if (schedulerRunning) {
                                            console.log("Scheduler already running, ignoring duplicate call.");
                                            return;
                                        }
                                        schedulerRunning = true;
                                        // Disable button to prevent duplicate calls
                                        $("#generateSchedule").prop("disabled", true);
                                        const statusElement = document.getElementById('scheduleGenerationStatus');
                                        statusElement.innerText = "Generating schedule, please wait...";

                                        const rooms = document.getElementById("rooms").value.split(',');
                                        const duration = parseFloat(document.getElementById("timeDuration").value);
                                        const startTime = document.getElementById("startTime").value;
                                        const endTime = document.getElementById("endTime").value;
                                        const days = document.getElementById("days").value.split(',');
                                        const program = document.getElementById("selectedProgram").value;
                                        const confirmOverwrite = document.getElementById("confirmOverwrite") ? 
                                            document.getElementById("confirmOverwrite").value : 'false';
                                            
                                        console.log('Generate Schedule - Program selected:', program);

                                        const increment = (duration % 1 === 0) ? 60 : 30;
                                        let currentTime = new Date(`1970-01-01T${startTime}`);
                                        if (duration % 1 === 0) {
                                            currentTime.setMinutes(0);
                                        }
                                        const endDateTime = new Date(`1970-01-01T${endTime}`);

                                        const timeSlots = [];
                                        while (currentTime < endDateTime) {
                                            timeSlots.push(currentTime.toTimeString().substring(0, 5));
                                            currentTime.setMinutes(currentTime.getMinutes() + increment);
                                        }

                                        const requestData = {
                                            rooms: rooms,
                                            timeDuration: duration,
                                            timeSlots: timeSlots,
                                            days: days,
                                            program: program,
                                            confirm_overwrite: confirmOverwrite
                                        };
                                        console.log('Request data for generateSchedule:', requestData);

                                        $.ajax({
                                            url: '../dashboard/includes/run_scheduler.php',
                                            method: 'POST',
                                            data: requestData,
                                            dataType: 'json',
                                            success: function(response) {
                                                if (response.success) {
                                                    statusElement.innerText = "Schedule generated successfully!";
                                                    if (response.overwrittenTeams > 0) {
                                                        statusElement.innerText += ` (Overwrote ${response.overwrittenTeams} existing team schedules)`;
                                                    }
                                                    loadDefenseSchedules(1);
                                                    // Optionally, you can refresh the table here
                                                    location.reload();
                                                } else if (response.requireConfirmation) {
                                                    // Show confirmation dialog with list of teams to be overwritten
                                                    let teamList = '<ul>';
                                                    for (const [id, name] of Object.entries(response.scheduledTeams)) {
                                                        teamList += `<li>${name}</li>`;
                                                    }
                                                    teamList += '</ul>';
                                                    
                                                    // Create or update confirmation UI
                                                    let confirmationDiv = document.getElementById('scheduleConfirmationDialog');
                                                    if (!confirmationDiv) {
                                                        confirmationDiv = document.createElement('div');
                                                        confirmationDiv.id = 'scheduleConfirmationDialog';
                                                        confirmationDiv.className = 'alert alert-warning mt-3';
                                                        document.getElementById('generationSetting').after(confirmationDiv);
                                                    }
                                                    
                                                    confirmationDiv.innerHTML = `
                                                        <p><strong>Warning:</strong> ${response.message}</p>
                                                        ${teamList}
                                                        <div class="mt-2">
                                                            <button id="confirmSchedule" class="btn btn-danger btn-sm">Overwrite Schedules</button>
                                                            <button id="cancelSchedule" class="btn btn-secondary btn-sm ml-2">Cancel</button>
                                                            <input type="hidden" id="confirmOverwrite" value="false">
                                                        </div>
                                                    `;
                                                    
                                                    // Add event listeners for confirm/cancel buttons
                                                    document.getElementById('confirmSchedule').addEventListener('click', function() {
                                                        document.getElementById('confirmOverwrite').value = 'true';
                                                        document.getElementById('generateSchedule').click();
                                                        confirmationDiv.style.display = 'none';
                                                    });
                                                    
                                                    document.getElementById('cancelSchedule').addEventListener('click', function() {
                                                        confirmationDiv.style.display = 'none';
                                                    });
                                                    
                                                    statusElement.innerText = "Please confirm overwriting existing schedules.";
                                                } else {
                                                    statusElement.innerText = "Error: " + response.message;
                                                }
                                                schedulerRunning = false;
                                                // Re-enable the button after request completes
                                                $("#generateSchedule").prop("disabled", false);
                                            },
                                            error: function(xhr, status, error) {
                                                statusElement.innerText = "Server error: " + error;
                                                schedulerRunning = false;
                                                $("#generateSchedule").prop("disabled", false);
                                            }
                                        });
                                    });
                                });
                            </script>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="includeLunchBreak" name="includeLunchBreak">
                                <label class="form-check-label" for="includeLunchBreak">Include Lunch Break (12 PM - 1 PM)</label>
                            </div>

                            <div class="mb-3">
                                <label for="programSelect" class="form-label">Program Filter</label>
                                <select class="form-select" id="programSelect" name="program">
                                    <option value="">All Programs</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT DISTINCT program FROM teams WHERE program IS NOT NULL ORDER BY program");
                                    $stmt->execute();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value=\"" . htmlspecialchars($row['program']) . "\">" . htmlspecialchars($row['program']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">Select a program to filter teams for scheduling.</small>
                                <input type="hidden" id="selectedTeamCount" name="selectedTeamCount" value="0">
                            </div>
                        </form>
                    </div>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teams");
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalTeams = $row['total'] ?? 0;
                    ?>
                    <div>Selected Teams for Scheduling: <span id="teamCountDisplay"><?php echo $totalTeams; ?></span></div>
                    <div id="scheduleGenerationStatus" class="mt-2"></div> <!-- Moved status element here -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveSchedulerSettings">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>

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
                        // USE THIS SCRIPT
                        let totalScheds = <?php echo $totalScheds; ?>;
                        // Use the global validateInputs function in saveSchedulerSettings click handler
                        document.getElementById('saveSchedulerSettings').addEventListener('click', function() {
                            // Call the global updateTeamCount function with validateInputs as callback
                            updateTeamCount(function() {
                                validateInputs(); // This will enable/disable saveButton
                                const saveButtonInstance = document.getElementById('saveSchedulerSettings'); // Re-fetch to get current state
                                const generateScheduleButton = document.getElementById('generateSchedule');
                                
                                if (!saveButtonInstance.disabled) { // Check if save button is NOT disabled (i.e., settings are valid)
                                     // Enable generate schedule button
                                     console.log("Settings are valid");
                                    if(generateScheduleButton) {
                                        generateScheduleButton.disabled = false;
                                        console.log("enabling generate schedule button")
                                    }
                                    $('#schedulerSettingsModal').modal('hide'); // Close the modal

                                    // Update the generationSetting display
                                    const settingsOutput = "Rooms: " + (document.getElementById("rooms").value || "N/A") + "<br>" +
                                        "Time Duration: " + (document.getElementById("timeDuration").value || "N/A") + " hours<br>" +
                                        "Start Time: " + (document.getElementById("startTime").value || "N/A") + "<br>" +
                                        "End Time: " + (document.getElementById("endTime").value || "N/A") + "<br>" +
                                        "Days: " + (document.getElementById("days").value || "N/A") + "<br>" +
                                        "Include Lunch Break: " + (document.getElementById("includeLunchBreak").checked ? "Yes" : "No");
                                    const generationSettingEl = document.getElementById("generationSetting");
                                    generationSettingEl.innerHTML = settingsOutput;
                                    generationSettingEl.style.display = 'block';

                                } else { // Settings are invalid
                                    if(generateScheduleButton) {
                                        generateScheduleButton.disabled = true;
                                    }
                                    // Do not close the modal if settings are invalid
                                }
                            });
                        });
                    </script>

        <!-- Defense Schedules Table -->
        <div class="row">
            <div class="col-12">
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
            </tbody>
        </table>

        <nav id="def-nav" aria-label="Page navigation">
            <ul class="pagination justify-content-center">
            </ul>
        </nav>
                </div>
            </div> 
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const options = {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    };
                    return date.toLocaleDateString('en-US', options);
                };

                const formatTime = (timeStr) => {
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    const period = hours >= 12 ? 'PM' : 'AM';
                    const hour12 = hours % 12 || 12;
                    return `${hour12}:${minutes.toString().padStart(2, '0')} ${period}`;
                };

                const splitPanelists = (panelistsString) => {
                    if (!panelistsString) return ['N/A', 'N/A', 'N/A']; // Return placeholders if null/empty
                    // Split the comma-separated string
                    const panelists = panelistsString.split(',').map(p => p.trim()).filter(p => p);
                    // Pad with 'N/A' if less than 3 panelists
                    while (panelists.length < 3) {
                        panelists.push('N/A');
                    }
                    // Return the first 3 panelists (or placeholders)
                    return panelists.slice(0, 3);
                };

                const loadDefenseSchedules = (page = 1) => {
                    console.log(`Loading Defense Schedules Page: ${page}`);
                    fetch(`../dashboard/includes/tabs/get_table.php?table=defense_schedules&page=${page}`)
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

                            const tbody = document.querySelector('#def-table tbody');
                            tbody.innerHTML = '';
                            if (data.data.length === 0) { // Added check for empty data array
                                tbody.innerHTML = `<tr><td colspan="9" class="text-center">No defense schedules found.</td></tr>`;
                            } else {
                                // Sort schedules by date and start time (earliest first)
                                data.data.sort((a, b) => {
                                    const dateA = new Date(a.schedule_date + 'T' + a.start_time);
                                    const dateB = new Date(b.schedule_date + 'T' + b.start_time);
                                    return dateA - dateB;
                                });
                                data.data.forEach(schedule => {
                                    const formattedDate = formatDate(schedule.schedule_date);
                                    const formattedStartTime = formatTime(schedule.start_time);
                                    const formattedEndTime = formatTime(schedule.end_time);
                                    const dateTime = `${formattedDate} ${formattedStartTime} - ${formattedEndTime}`;

                                    // Use the splitPanelists function
                                    const panelists = splitPanelists(schedule.panelists);

                                    tbody.innerHTML += `
                                        <tr>
                                            <td>${dateTime}</td>
                                            <td>${schedule.team_name || 'N/A'}</td>
                                            <td>${schedule.adviser || 'N/A'}</td> <!-- Use || 'N/A' -->
                                            <td>${schedule.thesis_title || 'N/A'}</td>
                                            <td>${panelists[0] || 'N/A'}</td> <!-- Use || 'N/A' -->
                                            <td>${panelists[1] || 'N/A'}</td> <!-- Use || 'N/A' -->
                                            <td>${panelists[2] || 'N/A'}</td> <!-- Use || 'N/A' -->
                                            <td>${schedule.room || 'N/A'}</td>
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
                            }

                            const pagination = document.querySelector('#def-nav .pagination');
                            pagination.innerHTML = '';

                            pagination.innerHTML += `
                                <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">
                                        &#8249;
                                    </a>
                                </li>
                            `;

                            for (let i = 1; i <= data.total_pages; i++) {
                                pagination.innerHTML += `
                                    <li class="page-item ${page === i ? 'active' : ''}">
                                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                                    </li>
                                `;
                            }

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
                            const tbody = document.querySelector('#def-table tbody'); // Ensure tbody is selected here too
                            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error loading schedule data: ${error.message}</td></tr>`;
                            const pagination = document.querySelector('#def-nav .pagination');
                            pagination.innerHTML = ''; // Clear pagination on error
                        });
                };

                // Global reload function for defense schedules (similar to other tabs)
                window.reloadCurrentDefenseSchedulesView = function(page = 1) {
                    loadDefenseSchedules(page);
                };

                loadDefenseSchedules();

                document.querySelector('#def-nav .pagination').addEventListener('click', function(e) {
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