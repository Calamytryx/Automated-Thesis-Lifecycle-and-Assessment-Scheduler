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
                <div class="user-controls-container p-0 mt-3">
                    <div class="row g-2 mb-3 align-items-center">
                        <!-- View Toggle -->
                        <div class="col-12 col-md-auto">
                            <div class="btn-group" role="group" aria-label="View toggle">
                                <input type="radio" class="btn-check" name="defViewMode" id="defTableView" checked autocomplete="off">
                                <label class="btn btn-outline-primary user-control-height" for="defTableView">Table</label>
                                <input type="radio" class="btn-check" name="defViewMode" id="defCalendarView" autocomplete="off">
                                <label class="btn btn-outline-primary user-control-height" for="defCalendarView">Calendar</label>
                            </div>
                        </div>
                        <!-- Bulk Approval Buttons (visible in calendar view for pending_chair items) -->
                        <div id="bulkApprovalControls" class="col-12 col-md-auto d-none">
                            <div class="d-flex gap-2">
                                <button class="btn btn-success user-control-height" id="bulkApproveBtn">
                                    <i class="fas fa-check-double me-1"></i>
                                    <span class="d-none d-lg-inline">Approve All</span>
                                    <span class="d-lg-none">Approve</span>
                                </button>
                                <button class="btn btn-danger user-control-height" id="bulkRejectBtn">
                                    <i class="fas fa-times-circle me-1"></i>
                                    <span class="d-none d-lg-inline">Reject All</span>
                                    <span class="d-lg-none">Reject</span>
                                </button>
                            </div>
                        </div>
                        <!-- Status Filter & Date Sort (table view only) -->
                        <div class="col-12 col-md-auto" id="defFilterControls">
                            <div class="d-flex gap-2">
                                <select class="form-select user-control-height" id="defStatusFilter">
                                    <option value="all">All Statuses</option>
                                    <option value="pending_chair">Chair Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                <select class="form-select user-control-height" id="defDateSort">
                                    <option value="desc">Date (Newest First)</option>
                                    <option value="asc">Date (Oldest First)</option>
                                </select>
                            </div>
                        </div>
                        <!-- Action Buttons -->
                        <div class="col-12 col-md">
                            <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                                <button class="btn feature-btn add-btn user-control-height flex-fill flex-md-grow-0" data-table="defense_schedules">
                                    <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Add Schedule</span>
                                    <span class="d-lg-none">Add</span>
                                </button>
                                <button type="button" class="btn feature-btn scheduler-btn user-control-height flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#schedulerSettingsModal">
                                    <i class="fas fa-cog me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Scheduler Settings</span>
                                    <span class="d-lg-none">Settings</span>
                                </button>
                                <button id="generateSchedule" type="button" class="btn feature-btn generate-btn user-control-height flex-fill flex-md-grow-0" disabled>
                                    <i class="fas fa-calendar-plus me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Generate Schedule</span>
                                    <span class="d-lg-none">Generate</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings display area -->
        <div class="row">
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
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="rooms" name="rooms" required placeholder="e.g., Defense Room 1, J201, S205">
                                </div>
                                <small class="form-text text-muted d-block mb-2">
                                    Room labels are free-text and used for manual coordination.
                                </small>
                                <div class="d-flex flex-wrap gap-1" style="gap: 0.25rem;">
                                    <button type="button" class="btn btn-sm btn-outline-primary room-preset" data-room="Defense Room 1" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Defense Room 1</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary room-preset" data-room="Defense Room 2" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Defense Room 2</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary room-preset" data-room="Accreditation Room" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Accreditation Room</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary room-preset" data-room="C" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Coecsa Building</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary room-preset" data-room="L" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Laboratory Building</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary room-preset" data-room="J" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">JPL Building</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary room-preset" data-room="S" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">SHL Building</button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="timeDuration" class="form-label">Time Duration (hours)</label>
                                <input type="number" class="form-control" id="timeDuration" name="timeDuration" min="0.25" max="5" step="0.25" required>
                            </div>
                            <script>
                                // Define validateInputs and other functions at the global scope
                                // Declare global variables
                                let timeDurationInput, startTimeInput, endTimeInput, daysInput, roomsInput, sectionSelect, 
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
                                    const roomsArray = roomsValue ? roomsValue.split(',').map(r => r.trim()).filter(r => r !== '') : [];
                                    const rooms = roomsArray.length;
                                    const includeLunchBreak = includeLunchBreakCheckbox.checked;
                                    const numberOfTeams = parseInt(document.getElementById('selectedTeamCount').value) || 0;

                                    let isValid = true;
                                    let warningMessage = '';

                                    console.log('Validation - Start:', startTime, 'End:', endTime, 'Duration:', timeDuration, 'Days:', days, 'Rooms:', rooms, 'Teams:', numberOfTeams);

                                    // Validate rooms first
                                    if (roomsValue && rooms > 0) {
                                        const normalizeRoomName = (name) => {
                                            return name
                                                .toLowerCase()
                                                .replace(/\s+/g, ''); // Only remove spaces for comparison
                                        };

                                        const normalizedSet = new Set();
                                        const seenRooms = [];

                                        for (let room of roomsArray) {
                                            const normalized = normalizeRoomName(room);
                                            if (normalizedSet.has(normalized)) {
                                                const duplicateIndex = seenRooms.findIndex(r => normalizeRoomName(r) === normalized);
                                                const originalRoom = seenRooms[duplicateIndex];
                                                isValid = false;
                                                warningMessage = `Duplicate room detected: "${room}" is the same as "${originalRoom}"`;
                                                break;
                                            }
                                            normalizedSet.add(normalized);
                                            seenRooms.push(room);
                                        }
                                    }

                                    if (isValid && (!startTime || !endTime || !timeDuration || days === 0 || rooms === 0 || !sectionSelect)) {
                                        isValid = false;
                                        warningMessage = 'All fields (Rooms, Duration, Start/End Time, Days) are required.';
                                    } else if (isValid) {
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
                                    if (!sectionSelect) {
                                        console.error('sectionSelect is not initialized');
                                        return;
                                    }
                                    const selectedSection = sectionSelect.value;
                                    console.log('updateTeamCount - Selected section:', selectedSection);
                                    $('#selectedSection').val(selectedSection);

                                    $.ajax({
                                        url: '../dashboard/includes/get_team_count.php',
                                        method: 'POST',
                                        data: { section: selectedSection },
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
                                    sectionSelect = document.getElementById('sectionSelect');
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
                                    
                                    if (sectionSelect) {
                                        sectionSelect.addEventListener('change', () => updateTeamCount(validateInputs));
                                    }

                                    // Room preset button handlers
                                    document.querySelectorAll('.room-preset').forEach(button => {
                                        button.addEventListener('click', function() {
                                            const roomName = this.getAttribute('data-room');
                                            const currentValue = roomsInput.value.trim();
                                            
                                            if (currentValue === '') {
                                                roomsInput.value = roomName;
                                            } else {
                                                // Check if room already exists
                                                const rooms = currentValue.split(',').map(r => r.trim());
                                                if (!rooms.includes(roomName)) {
                                                    roomsInput.value = currentValue + ', ' + roomName;
                                                }
                                            }
                                            // Trigger validation
                                            updateTeamCount(validateInputs);
                                        });
                                    });

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
                            <input type="hidden" id="selectedSection" name="selectedSection" value="">
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
                                        todayHighlight: true,
                                        daysOfWeekDisabled: [0] // Disable Sundays (0 = Sunday)
                                    });

                                    // Initial count update and validation on load
                                    console.log('jQuery ready: About to call updateTeamCount');
                                    updateTeamCount(validateInputs);
                                    
                                    // Global flag to prevent duplicate scheduler runs
                                    let schedulerRunning = false;

                                    // Enhanced loading state management
                                    function showLoadingState() {
                                        const statusElement = document.getElementById('scheduleGenerationStatus');
                                        const generateBtn = document.getElementById('generateSchedule');
                                        
                                        if (!statusElement) {
                                            console.error('Status element not found!');
                                            return;
                                        }
                                        
                                        if (!generateBtn) {
                                            console.error('Generate button not found!');
                                            return;
                                        }
                                        
                                        // Update button state
                                        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
                                        generateBtn.disabled = true;
                                        
                                        // Show progress container
                                        statusElement.innerHTML = `
                                            <div class="d-flex align-items-center">
                                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span id="progressText">Initializing schedule generation...</span>
                                            </div>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                     id="progressBar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        `;
                                    }

                                    function updateProgress(message, percentage = null) {
                                        const progressText = document.getElementById('progressText');
                                        const progressBar = document.getElementById('progressBar');
                                        
                                        if (progressText) progressText.textContent = message;
                                        if (progressBar && percentage !== null) {
                                            progressBar.style.width = percentage + '%';
                                        }
                                    }

                                    function hideLoadingState(success = true, message = '') {
                                        const statusElement = document.getElementById('scheduleGenerationStatus');
                                        const generateBtn = document.getElementById('generateSchedule');
                                        
                                        if (!statusElement || !generateBtn) {
                                            console.error('Required elements not found for hideLoadingState');
                                            return;
                                        }
                                        
                                        // Reset button
                                        generateBtn.innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Generate Defense Schedule';
                                        generateBtn.disabled = false;
                                        
                                        // Show final message
                                        statusElement.innerHTML = `
                                            <div class="alert alert-${success ? 'success' : 'danger'} alert-dismissible fade show">
                                                <i class="fas fa-${success ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                                                ${message}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        `;
                                        
                                        schedulerRunning = false;
                                    }

                                    // Expose to global scope for cross-script access
                                    window.hideLoadingState = hideLoadingState;
                                    window.showLoadingState = showLoadingState;
                                    window.updateScheduleProgress = updateProgress;

                                    // Progress polling system
                                    function pollScheduleProgress(progressId) {
                                        const pollInterval = setInterval(() => {
                                            fetch(`../dashboard/includes/get_schedule_progress.php?id=${progressId}`)
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.status === 'running') {
                                                        updateProgress(data.message, data.percentage);
                                                    } else if (data.status === 'completed') {
                                                        clearInterval(pollInterval);
                                                        hideLoadingState(true, 'Schedule generated successfully!');
                                                        // Reload the defense schedules table
                                                        setTimeout(() => {
                                                            if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                                                window.reloadCurrentDefenseSchedulesView(1);
                                                            }
                                                        }, 1000);
                                                    } else if (data.status === 'error') {
                                                        clearInterval(pollInterval);
                                                        hideLoadingState(false, data.message || 'An error occurred during generation');
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Progress polling error:', error);
                                                    clearInterval(pollInterval);
                                                    hideLoadingState(false, 'Failed to monitor progress');
                                                });
                                        }, 1000); // Poll every second
                                    }

                                    // Update the Generate Schedule click handler
                                    const generateButton = document.getElementById('generateSchedule');
                                    console.log('Generate button found:', generateButton);
                                    
                                    if (generateButton) {
                                        generateButton.addEventListener('click', function(e) {
                                            console.log('Generate button clicked!');
                                            e.preventDefault(); // prevent default submission
                                            e.stopPropagation();
                                            if (schedulerRunning) {
                                                console.log("Scheduler already running, ignoring duplicate call.");
                                                return;
                                            }
                                            schedulerRunning = true;
                                            
                                            showLoadingState();

                                        const rooms = document.getElementById("rooms").value.split(',');
                                        const duration = parseFloat(document.getElementById("timeDuration").value);
                                        const startTime = document.getElementById("startTime").value;
                                        const endTime = document.getElementById("endTime").value;
                                        const days = document.getElementById("days").value.split(',');
                                        const section = document.getElementById("selectedSection").value;
                                        const confirmOverwrite = document.getElementById("confirmOverwrite") ? 
                                            document.getElementById("confirmOverwrite").value : 'false';
                                            
                                        console.log('Generate Schedule - Section selected:', section);

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
                                            section: section,
                                            confirm_overwrite: confirmOverwrite,
                                            preview: 'true'
                                        };
                                        console.log('Request data for generateSchedule:', requestData);

                                        $.ajax({
                                            url: '../dashboard/includes/run_scheduler.php',
                                            method: 'POST',
                                            data: requestData,
                                            dataType: 'json',
                                            success: function(response) {
                                                console.log('AJAX Success Response:', response);
                                                if (response.success && response.preview && response.schedules) {
                                                    // Preview mode: show editable calendar
                                                    hideLoadingState(true, 'Preview ready! Review the schedule below.');
                                                    if (typeof window.showDefensePreviewCalendar === 'function') {
                                                        window.showDefensePreviewCalendar(response.schedules);
                                                    }
                                                } else if (response.success) {
                                                    if (response.progressId) {
                                                        // Start polling for progress if backend supports it
                                                        pollScheduleProgress(response.progressId);
                                                    } else {
                                                        // Fallback to immediate completion
                                                        hideLoadingState(true, 'Schedule generated successfully!');
                                                        if (response.overwrittenTeams > 0) {
                                                            updateProgress(`Success! Overwrote ${response.overwrittenTeams} existing schedules`);
                                                        }
                                                        // Reload table automatically
                                                        setTimeout(() => {
                                                            if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                                                window.reloadCurrentDefenseSchedulesView(1);
                                                            }
                                                        }, 1000);
                                                    }
                                                } else if (response.requireUpgradeConfirmation) {
                                                    hideLoadingState(false, 'Defense type upgrade confirmation required');
                                                    // Show defense upgrade confirmation dialog
                                                    let upgradeList = '<ul>';
                                                    response.upgradeInfo.forEach(function(team) {
                                                        upgradeList += `<li><strong>${team.team_name}:</strong> ${team.current_type} → ${team.new_type} (${team.status})</li>`;
                                                    });
                                                    upgradeList += '</ul>';
                                                    
                                                    if (response.missingGrades && response.missingGrades.length > 0) {
                                                        upgradeList += `<p class="text-muted"><small>Note: ${response.missingGrades.length} team(s) cannot be upgraded because grades are missing.</small></p>`;
                                                    }
                                                    
                                                    // Create or update confirmation UI
                                                    let confirmationDiv = document.getElementById('scheduleConfirmationDialog');
                                                    if (!confirmationDiv) {
                                                        confirmationDiv = document.createElement('div');
                                                        confirmationDiv.id = 'scheduleConfirmationDialog';
                                                        confirmationDiv.className = 'alert alert-info mt-3';
                                                        document.getElementById('generationSetting').after(confirmationDiv);
                                                    }
                                                    
                                                    confirmationDiv.innerHTML = `
                                                        <p><strong>Defense Type Upgrade:</strong> ${response.message}</p>
                                                        ${upgradeList}
                                                        <div class="mt-2">
                                                            <button id="confirmUpgrade" class="btn btn-primary btn-sm">Proceed with Upgrade</button>
                                                            <button id="cancelUpgrade" class="btn btn-secondary btn-sm ml-2">Cancel</button>
                                                            <input type="hidden" id="confirmUpgradeFlag" value="false">
                                                        </div>
                                                    `;
                                                    confirmationDiv.style.display = '';
                                                    
                                                    // Add event listeners for confirm/cancel buttons
                                                    document.getElementById('confirmUpgrade').addEventListener('click', function() {
                                                        // Re-submit with confirm_upgrade flag
                                                        let formData = new FormData(document.getElementById('generationSetting'));
                                                        formData.append('confirm_upgrade', 'true');
                                                        
                                                        let requestData = {};
                                                        formData.forEach((value, key) => {
                                                            if (requestData[key]) {
                                                                if (!Array.isArray(requestData[key])) {
                                                                    requestData[key] = [requestData[key]];
                                                                }
                                                                requestData[key].push(value);
                                                            } else {
                                                                requestData[key] = value;
                                                            }
                                                        });
                                                        
                                                        $.ajax({
                                                            url: '../dashboard/includes/run_scheduler.php',
                                                            method: 'POST',
                                                            data: requestData,
                                                            dataType: 'json',
                                                            success: function(response) {
                                                                if (response.success && response.progressId) {
                                                                    pollScheduleProgress(response.progressId);
                                                                } else if (response.success) {
                                                                    hideLoadingState(true, 'Schedule generated successfully!');
                                                                    setTimeout(() => {
                                                                        if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                                                            window.reloadCurrentDefenseSchedulesView(1);
                                                                        }
                                                                    }, 1000);
                                                                } else {
                                                                    hideLoadingState(false, response.message || 'Unknown error occurred');
                                                                }
                                                            },
                                                            error: function(xhr, status, error) {
                                                                hideLoadingState(false, `Server error: ${error}`);
                                                            }
                                                        });
                                                        
                                                        confirmationDiv.style.display = 'none';
                                                    });
                                                    
                                                    document.getElementById('cancelUpgrade').addEventListener('click', function() {
                                                        confirmationDiv.style.display = 'none';
                                                    });
                                                } else if (response.requireConfirmation) {
                                                    hideLoadingState(false, 'Confirmation required');
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
                                                    confirmationDiv.style.display = '';
                                                    
                                                    // Add event listeners for confirm/cancel buttons
                                                    document.getElementById('confirmSchedule').addEventListener('click', function() {
                                                        document.getElementById('confirmOverwrite').value = 'true';
                                                        document.getElementById('generateSchedule').click();
                                                        confirmationDiv.style.display = 'none';
                                                    });
                                                    
                                                    document.getElementById('cancelSchedule').addEventListener('click', function() {
                                                        confirmationDiv.style.display = 'none';
                                                    });
                                                } else {
                                                    hideLoadingState(false, response.message || 'Unknown error occurred');
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                hideLoadingState(false, `Server error: ${error}`);
                                            }
                                        });
                                    });
                                    }
                                    else {
                                        console.error('Generate button not found!');
                                    }
                                });
                            </script>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="includeLunchBreak" name="includeLunchBreak">
                                <label class="form-check-label" for="includeLunchBreak" data-bs-toggle="tooltip" data-bs-placement="right" title="When enabled, no defense schedules will be generated between 12:00 PM and 1:00 PM">Include Lunch Break (12 PM - 1 PM)</label>
                            </div>

                            <div class="mb-3">
                                <label for="sectionSelect" class="form-label">Section Filter</label>
                                <select class="form-select" id="sectionSelect" name="section">
                                    <option value="">All Accessible Sections</option>
                                    <?php
                                    // Get accessible sections based on user role
                                    $currentUserId = $_SESSION['id'] ?? 0;
                                    $currentUsertype = $_SESSION['usertype'] ?? -1;

                                    // Admin (id=0): All sections
                                    if ($currentUserId === 0 && $currentUsertype === 0) {
                                        $stmt = $pdo->prepare("SELECT DISTINCT section FROM users WHERE section IS NOT NULL ORDER BY section");
                                        $stmt->execute();
                                    }
                                    // Program Chair (usertype=0, id!=0): All sections in their college
                                    elseif ($currentUsertype === 0 && $currentUserId !== 0) {
                                        require_once __DIR__ . '/../../../assets/includes/auth_functions.php';
                                        $userCollege = get_user_college($pdo, $currentUserId);
                                        if ($userCollege) {
                                            $stmt = $pdo->prepare("
                                                SELECT DISTINCT u.section FROM users u
                                                LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                                                WHERE u.section IS NOT NULL AND p.college = :college
                                                ORDER BY u.section
                                            ");
                                            $stmt->execute([':college' => $userCollege]);
                                        } else {
                                            $stmt = null;
                                        }
                                    }
                                    // Faculty (usertype=2): Only their assigned sections
                                    elseif ($currentUsertype === 2) {
                                        require_once __DIR__ . '/../section_access.php';
                                        $sections = getProfessorSections($pdo, $currentUserId);
                                        if (!empty($sections)) {
                                            $placeholders = implode(',', array_fill(0, count($sections), '?'));
                                            $stmt = $pdo->prepare("SELECT DISTINCT section FROM users WHERE section IN ($placeholders) ORDER BY section");
                                            $stmt->execute($sections);
                                        } else {
                                            $stmt = null;
                                        }
                                    } else {
                                        $stmt = null;
                                    }

                                    if ($stmt) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value=\"" . htmlspecialchars($row['section']) . "\">" . htmlspecialchars($row['section']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">Select a section to filter teams for scheduling.</small>
                                <input type="hidden" id="selectedTeamCount" name="selectedTeamCount" value="0">
                            </div>
                        </form>
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teams");
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalTeams = $row['total'] ?? 0;
                        ?>
                        <div class="mt-3">Selected Teams for Scheduling: <span id="teamCountDisplay"><?php echo $totalTeams; ?></span></div>
                        <div id="scheduleGenerationStatus" class="mt-2"></div>
                    </div>
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



        <!-- Table View -->
        <div id="defTableViewContainer" class="row">
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
                    <th>Status</th>
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

        <!-- Calendar View -->
        <div id="defCalendarViewContainer" class="row d-none">
            <div class="col-12">
                <div class="p-3 bg-white rounded border">
                    <div class="def-calendar-legend mb-3" aria-label="Defense schedule status legend">
                        <span class="def-legend-item"><span class="def-legend-dot def-legend-approved"></span>Approved</span>
                        <span class="def-legend-item"><span class="def-legend-dot def-legend-rejected"></span>Rejected</span>
                        <span class="def-legend-item"><span class="def-legend-dot def-legend-chair-review"></span>Chair Review</span>
                    </div>
                    <div id="defenseCalendar" style="min-height:600px;"></div>
                </div>
            </div>
        </div>

        <!-- Preview Modal (shown after generating schedule) -->
        <div class="modal fade" id="schedulePreviewModal" tabindex="-1" aria-labelledby="schedulePreviewModalLabel" data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="schedulePreviewModalLabel">Schedule Preview — Review & Edit Before Saving</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="container-fluid p-3">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Drag events</strong> to move them to different times/days. <strong>Click an event</strong> to edit details (room, panelists). When satisfied, click <strong>Confirm & Save</strong>.
                            </div>
                            <div id="previewCalendar" style="min-height:70vh;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="previewScheduleCount" class="me-auto text-muted"></span>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Discard</button>
                        <button type="button" class="btn btn-primary" id="confirmSavePreview">Confirm & Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Edit Modal (for single event editing in preview/calendar) -->
        <div class="modal fade" id="eventEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Defense Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editEventId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Team</label>
                            <input type="text" class="form-control" id="editTeamName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Room</label>
                            <input type="text" class="form-control" id="editRoom" placeholder="Optional">
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">Date</label>
                                <input type="date" class="form-control" id="editDate">
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-bold">Start</label>
                                <input type="time" class="form-control" id="editStartTime">
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-bold">End</label>
                                <input type="time" class="form-control" id="editEndTime">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Panelist 1</label>
                            <select class="form-select" id="editPanelist1"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Panelist 2</label>
                            <select class="form-select" id="editPanelist2"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Panelist 3</label>
                            <select class="form-select" id="editPanelist3"></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark me-auto" id="toggleFinalizeScheduleBtn" style="display:none;">Finalize</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveEventEdit"><i class="fas fa-check me-1"></i>Apply Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Defense Schedule Confirmation Modal -->
        <div class="modal fade" id="defConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0 justify-content-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="defConfirmIcon" class="mb-3" style="font-size: 3rem;"></div>
                        <h4 class="fw-bold mb-3" id="defConfirmTitle"></h4>
                        <p id="defConfirmMessage"></p>
                        <p class="text-muted mb-0 d-none" id="defConfirmUndoNote">This action cannot be undone.</p>
                        <div id="defConfirmPromptWrap" class="d-none">
                            <textarea class="form-control mt-2" id="defConfirmPromptInput" rows="2" placeholder=""></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn" id="defConfirmActionBtn"></button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            #defenseCalendar .fc-event, #previewCalendar .fc-event {
                cursor: pointer;
                border-radius: 4px;
                padding: 2px 4px;
                font-size: 0.78rem;
                line-height: 1.2;
            }
            .def-calendar-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 0.85rem;
                align-items: center;
                font-size: 0.85rem;
                color: #374151;
            }
            .def-legend-item {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-weight: 500;
            }
            .def-legend-dot {
                width: 0.7rem;
                height: 0.7rem;
                border-radius: 50%;
                display: inline-block;
            }
            .def-legend-approved { background-color: #10b981; }
            .def-legend-rejected { background-color: #ef4444; }
            .def-legend-chair-review { background-color: #f59e0b; }
            .fc-event .event-team { font-weight: 600; }
            .fc-event .event-room { font-size: 0.7rem; opacity: 0.85; }
            .fc-event.status-pending_chair { background-color: #f59e0b !important; border-color: #d97706 !important; color: #451a03 !important; }
            .fc-event.status-approved { background-color: #10b981 !important; border-color: #059669 !important; color: #fff !important; }
            .fc-event.status-rejected { background-color: #ef4444 !important; border-color: #dc2626 !important; color: #fff !important; }
            .fc-event.status-preview { background-color: #8b5cf6 !important; border-color: #7c3aed !important; color: #fff !important; }
            /* Stacked modal z-index: eventEditModal sits above schedulePreviewModal */
            #eventEditModal { z-index: 1060; }
            #eventEditModal + .modal-backdrop, #eventEditModal ~ .modal-backdrop:last-of-type { z-index: 1055; }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // ========== Defense Confirmation/Alert Helpers ==========
                const defConfirmModalEl = document.getElementById('defConfirmModal');
                const defConfirmModal = new bootstrap.Modal(defConfirmModalEl);
                let defConfirmCallback = null;

                const successIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#28a745"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                const dangerIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#dc3545"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>';
                const warningIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#ffc107"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-8.17 14.1A2 2 0 004 21h16a2 2 0 001.73-3.04l-8.17-14.1a2 2 0 00-3.46 0z"/></svg>';

                const iconMap = { success: successIcon, danger: dangerIcon, warning: warningIcon, error: dangerIcon };

                function showDefConfirm(title, message, actionText, actionType, callback) {
                    document.getElementById('defConfirmIcon').innerHTML = iconMap[actionType] || dangerIcon;
                    document.getElementById('defConfirmTitle').textContent = title;
                    document.getElementById('defConfirmMessage').textContent = message;
                    const showUndoNote = /delete/i.test(title) || /delete/i.test(message) || /delete/i.test(actionText);
                    document.getElementById('defConfirmUndoNote').classList.toggle('d-none', !showUndoNote);
                    document.getElementById('defConfirmPromptWrap').classList.add('d-none');
                    const actionBtn = document.getElementById('defConfirmActionBtn');
                    actionBtn.className = 'btn btn-' + actionType;
                    actionBtn.textContent = actionText;
                    defConfirmCallback = callback;
                    defConfirmModal.show();
                }

                function showDefPrompt(title, message, placeholder, actionText, actionType, callback) {
                    document.getElementById('defConfirmIcon').innerHTML = iconMap[actionType] || dangerIcon;
                    document.getElementById('defConfirmTitle').textContent = title;
                    document.getElementById('defConfirmMessage').textContent = message;
                    const showUndoNote = /delete/i.test(title) || /delete/i.test(message) || /delete/i.test(actionText);
                    document.getElementById('defConfirmUndoNote').classList.toggle('d-none', !showUndoNote);
                    const promptWrap = document.getElementById('defConfirmPromptWrap');
                    const promptInput = document.getElementById('defConfirmPromptInput');
                    promptWrap.classList.remove('d-none');
                    promptInput.placeholder = placeholder;
                    promptInput.value = '';
                    const actionBtn = document.getElementById('defConfirmActionBtn');
                    actionBtn.className = 'btn btn-' + actionType;
                    actionBtn.textContent = actionText;
                    defConfirmCallback = function() { callback(promptInput.value); };
                    defConfirmModal.show();
                }

                function showDefAlert(message, type) {
                    const msgText = String(message || '');
                    const isScheduleConflict =
                        (type === 'error' || type === 'danger') &&
                        msgText.toLowerCase().includes('schedule conflict with:');

                    if (isScheduleConflict) {
                        let body = msgText;
                        if (body.startsWith('Error: ')) {
                            body = body.substring(7);
                        }

                        const lines = body.split('\n').map(x => x.trim()).filter(Boolean);
                        const heading = lines[0] || 'Schedule conflict';
                        const sub1 = lines[1] || '';
                        const sub2 = lines[2] || '';

                        const conflictHtml = `
                            <div style="font-weight:700;font-size:1rem;margin-bottom:4px;">${heading}</div>
                            ${sub1 ? `<div style="margin-top:2px;">${sub1}</div>` : ''}
                            ${sub2 ? `<div style="margin-top:2px;color:#374151;">${sub2}</div>` : ''}
                        `;

                        if (!document.getElementById('defConflictToastContainer')) {
                            const container = document.createElement('div');
                            container.id = 'defConflictToastContainer';
                            container.className = 'position-fixed top-0 end-0 p-3';
                            container.style.zIndex = '9999';
                            document.body.appendChild(container);
                        }

                        const toastId = 'def-conflict-toast-' + Date.now();
                        const toastHtml = `
                            <div id="${toastId}" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true"
                                style="min-width:340px;max-width:460px;opacity:1;background:#fef2f2;border-left:5px solid #dc2626;border-radius:12px;margin-bottom:1rem;">
                                <div class="d-flex align-items-start" style="padding:1rem 1.1rem;">
                                    <div class="toast-body p-0" style="font-size:0.98rem;color:#1f2937;line-height:1.45;">${conflictHtml}</div>
                                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close" style="margin-left:1rem;"></button>
                                </div>
                            </div>
                        `;

                        document.getElementById('defConflictToastContainer').insertAdjacentHTML('beforeend', toastHtml);
                        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
                            autohide: false,
                            delay: 15000,
                            animation: true
                        });
                        toastElement.show();
                        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
                            this.remove();
                        });
                        return;
                    }

                    if (typeof showToast === 'function') {
                        const title = type === 'success' ? 'Success' : type === 'warning' ? 'Notice' : 'Error';
                        const toastType = type === 'warning' ? 'notice' : (type === 'danger' ? 'error' : type);
                        showToast(title, message, toastType);
                    } else {
                        // Fallback floating alert
                        const alertDiv = document.createElement('div');
                        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
                        alertDiv.style.zIndex = '9999';
                        alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                        document.body.appendChild(alertDiv);
                        setTimeout(() => alertDiv.remove(), 4000);
                    }
                }

                document.getElementById('defConfirmActionBtn').addEventListener('click', function() {
                    defConfirmModal.hide();
                    if (typeof defConfirmCallback === 'function') defConfirmCallback();
                    defConfirmCallback = null;
                });

                // ========== Utility Functions ==========
                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                };
                const formatTime = (timeStr) => {
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    const period = hours >= 12 ? 'PM' : 'AM';
                    return `${hours % 12 || 12}:${minutes.toString().padStart(2, '0')} ${period}`;
                };
                const splitPanelists = (panelistsString) => {
                    if (!panelistsString) return ['N/A', 'N/A', 'N/A'];
                    const panelists = panelistsString.split(',').map(p => p.trim()).filter(p => p);
                    while (panelists.length < 3) panelists.push('N/A');
                    return panelists.slice(0, 3);
                };

                // ========== Faculty cache for panelist dropdowns ==========
                let facultyList = [];
                function loadFacultyList() {
                    // Use get_teams_and_staff.php (works for all user types) as primary,
                    // fall back to get_faculty_list.php
                    return fetch('../dashboard/includes/get_teams_and_staff.php')
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.staff) {
                                // Map staff format {id, name} to faculty format {id, full_name, program}
                                facultyList = data.staff.map(s => ({ id: s.id, full_name: s.name, program: '' }));
                                // Also expose globally for the add modal
                                window._defFacultyList = facultyList;
                            }
                        })
                        .catch(() => {
                            // Fallback
                            return fetch('../dashboard/includes/get_faculty_list.php')
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        facultyList = data.faculty;
                                        window._defFacultyList = facultyList;
                                    }
                                });
                        });
                }
                loadFacultyList();

                function populatePanelistDropdown(selectEl, selectedId) {
                    selectEl.innerHTML = '<option value="">-- Select --</option>';
                    const list = facultyList.length > 0 ? facultyList : (window._defFacultyList || []);
                    list.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f.id;
                        opt.textContent = (f.full_name || f.name || '') + (f.program ? ` (${f.program})` : '');
                        if (String(f.id) === String(selectedId)) opt.selected = true;
                        selectEl.appendChild(opt);
                    });
                }

                // ========== Schedule data cache (raw from server) ==========
                let allScheduleData = [];
                let currentTablePage = 1;

                // ========== VIEW TOGGLE ==========
                const tableViewBtn = document.getElementById('defTableView');
                const calendarViewBtn = document.getElementById('defCalendarView');
                const tableContainer = document.getElementById('defTableViewContainer');
                const calendarContainer = document.getElementById('defCalendarViewContainer');
                const bulkControls = document.getElementById('bulkApprovalControls');
                const filterControls = document.getElementById('defFilterControls');
                let activeDefenseView = tableViewBtn.checked ? 'table' : 'calendar';
                let loadRequestSeq = 0;

                function setDefenseViewMode(mode) {
                    activeDefenseView = mode;
                    const isCalendar = mode === 'calendar';
                    tableContainer.classList.toggle('d-none', isCalendar);
                    calendarContainer.classList.toggle('d-none', !isCalendar);
                    filterControls.classList.toggle('d-none', isCalendar);
                    // Bulk controls are shown only after calendar data is loaded.
                    bulkControls.classList.add('d-none');
                }

                setDefenseViewMode(activeDefenseView);

                tableViewBtn.addEventListener('change', () => {
                    if (!tableViewBtn.checked) {
                        return;
                    }
                    setDefenseViewMode('table');
                    loadDefenseSchedules(currentTablePage, false, false);
                });
                calendarViewBtn.addEventListener('change', () => {
                    if (!calendarViewBtn.checked) {
                        return;
                    }
                    setDefenseViewMode('calendar');
                    loadDefenseSchedules(1, false, true);
                });

                // ========== TABLE VIEW (existing) ==========
                const getDefenseFilters = () => ({
                    status: document.getElementById('defStatusFilter').value,
                    sortDir: document.getElementById('defDateSort').value
                });

                const loadDefenseSchedules = (page = 1, showProgress = false, forceCalendarMode = false) => {
                    if (showProgress && typeof window.updateScheduleProgress === 'function') window.updateScheduleProgress('Refreshing defense schedules...', 95);
                    
                    const isCalendarRequest = forceCalendarMode === true || calendarViewBtn.checked;
                    const requestSeq = ++loadRequestSeq;
                    const perPageParam = isCalendarRequest ? '&per_page=500' : '';
                    const filters = getDefenseFilters();
                    const statusParam = filters.status !== 'all' ? `&approval_status=${encodeURIComponent(filters.status)}` : '';
                    const sortParam = `&sort_by=schedule_date&sort_dir=${encodeURIComponent(filters.sortDir)}`;
                    
                    fetch(`../dashboard/includes/tabs/get_table.php?table=defense_schedules&page=${page}${perPageParam}${statusParam}${sortParam}`)
                        .then(response => {
                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                            return response.json();
                        })
                        .then(data => {
                            if (requestSeq !== loadRequestSeq) {
                                return;
                            }

                            if (data.error) {
                                document.getElementById('scheduleGenerationStatus').innerText = `Error: ${data.error}`;
                                return;
                            }

                            allScheduleData = data.data || [];
                            if (!isCalendarRequest) {
                                const tbody = document.querySelector('#def-table tbody');
                                tbody.innerHTML = '';
                                // Clean up old meatball dropdown portals
                                document.querySelectorAll('[id^="dropdown-def-"]').forEach(el => el.remove());
                                if (allScheduleData.length === 0) {
                                    tbody.innerHTML = `<tr><td colspan="10" class="text-center">No defense schedules found.</td></tr>`;
                                } else {
                                    allScheduleData.forEach(schedule => {
                                        const dateTime = `${formatDate(schedule.schedule_date)} ${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}`;
                                        const panelists = splitPanelists(schedule.panelists);
                                        let statusBadge = '';
                                        const approvalStatus = schedule.approval_status || 'pending_chair';
                                        switch (approvalStatus) {
                                            case 'pending_chair':
                                                statusBadge = '<span class="status-badge def-status-pending_chair"><i class="fas fa-clock me-1"></i>Chair Review</span>';
                                                break;
                                            case 'pending': statusBadge = '<span class="status-badge def-status-pending_chair"><i class="fas fa-clock me-1"></i>Chair Review</span>'; break;
                                            case 'approved': statusBadge = '<span class="status-badge def-status-approved"><i class="fas fa-check-circle me-1"></i>Approved</span>'; break;
                                            case 'rejected': statusBadge = '<span class="status-badge def-status-rejected"><i class="fas fa-times-circle me-1"></i>Rejected</span>'; break;
                                        }

                                        tbody.innerHTML += `<tr>
                                            <td>${dateTime}</td>
                                            <td>${schedule.team_name || 'N/A'}</td>
                                            <td>${schedule.adviser || 'N/A'}</td>
                                            <td>${schedule.thesis_title || 'N/A'}</td>
                                            <td>${panelists[0]}</td><td>${panelists[1]}</td><td>${panelists[2]}</td>
                                            <td>${schedule.room || 'N/A'}</td>
                                            <td class="text-center">${statusBadge}</td>
                                            <td class="action-buttons text-center">
                                                <button class="meatball-btn" data-def-id="${schedule.id}" data-approval-status="${approvalStatus}" aria-label="Actions">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                            </td>
                                        </tr>`;
                                    });
                                }

                                // Pagination
                                const pagination = document.querySelector('#def-nav .pagination');
                                pagination.innerHTML = '';

                                if (data.total_pages > 1) {
                                    const totalPages = data.total_pages;

                                    pagination.innerHTML += `<li class="page-item ${page <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page - 1}">&#8249;</a></li>`;

                                    const startPage = Math.max(1, page - 2);
                                    const endPage = Math.min(totalPages, page + 2);

                                    if (startPage > 1) {
                                        pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                                        if (startPage > 2) {
                                            pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                        }
                                    }

                                    for (let i = startPage; i <= endPage; i++) {
                                        pagination.innerHTML += `<li class="page-item ${page === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                                    }

                                    if (endPage < totalPages) {
                                        if (endPage < totalPages - 1) {
                                            pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                        }
                                        pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
                                    }

                                    pagination.innerHTML += `<li class="page-item ${page >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page + 1}">&#8250;</a></li>`;
                                }
                            }

                            if (showProgress && typeof window.hideLoadingState === 'function') setTimeout(() => window.hideLoadingState(true, 'Defense schedules updated!'), 500);

                            // Update calendar with full independent dataset when requested
                            if (isCalendarRequest) {
                                allScheduleData.sort((a, b) => new Date(a.schedule_date + 'T' + a.start_time) - new Date(b.schedule_date + 'T' + b.start_time));
                                renderDefenseCalendar();
                                const hasPendingChair = allScheduleData.some(s => s.approval_status === 'pending_chair');
                                const showBulkControls = activeDefenseView === 'calendar' && hasPendingChair;
                                bulkControls.classList.toggle('d-none', !showBulkControls);
                            }
                        })
                        .catch(error => {
                            if (requestSeq !== loadRequestSeq) {
                                return;
                            }

                            if (showProgress && typeof window.hideLoadingState === 'function') window.hideLoadingState(false, 'Failed to refresh schedules');
                            if (isCalendarRequest) {
                                showDefAlert('Failed to load calendar schedules: ' + error.message, 'error');
                            } else {
                                const tbody = document.querySelector('#def-table tbody');
                                tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error: ${error.message}</td></tr>`;
                                document.querySelector('#def-nav .pagination').innerHTML = '';
                            }
                        });
                };

                window.reloadCurrentDefenseSchedulesView = function(page = currentTablePage) {
                    if (calendarViewBtn.checked) {
                        loadDefenseSchedules(1, false, true);
                    } else {
                        currentTablePage = page;
                        loadDefenseSchedules(currentTablePage, false, false);
                    }
                };
                loadDefenseSchedules(currentTablePage, false, false);

                // Filter/sort change listeners
                document.getElementById('defStatusFilter').addEventListener('change', () => {
                    currentTablePage = 1;
                    loadDefenseSchedules(currentTablePage, false, false);
                });
                document.getElementById('defDateSort').addEventListener('change', () => {
                    currentTablePage = 1;
                    loadDefenseSchedules(currentTablePage, false, false);
                });

                document.querySelector('#def-nav .pagination').addEventListener('click', function(e) {
                    e.preventDefault();
                    if (e.target.tagName === 'A') {
                        const page = parseInt(e.target.getAttribute('data-page'));
                        if (!isNaN(page)) {
                            currentTablePage = page;
                            loadDefenseSchedules(currentTablePage, false, false);
                        }
                    }
                });

                // ========== DEFENSE CALENDAR VIEW ==========
                let defenseCalendarInstance = null;

                function scheduleToEvent(s, isEditable = false) {
                    const panelists = splitPanelists(s.panelists);
                    const rawStatus = s.approval_status || 'pending_chair';
                    const status = rawStatus === 'pending' ? 'pending_chair' : rawStatus;
                    const isFinalized = Number(s.is_finalized || 0) === 1;
                    return {
                        id: s.id,
                        title: s.team_name || 'Unknown',
                        start: s.schedule_date + 'T' + s.start_time,
                        end: s.schedule_date + 'T' + s.end_time,
                        editable: isEditable && !isFinalized,
                        classNames: ['status-' + status],
                        extendedProps: {
                            ...s,
                            is_finalized: isFinalized ? 1 : 0,
                            panelist1: panelists[0],
                            panelist2: panelists[1],
                            panelist3: panelists[2],
                            status: status
                        }
                    };
                }

                function renderDefenseCalendar() {
                    if (defenseCalendarInstance) defenseCalendarInstance.destroy();
                    const calendarEl = document.getElementById('defenseCalendar');
                    const events = allScheduleData.map(s => scheduleToEvent(s, true));

                    // Always open calendar on today's date.
                    const initialDate = new Date();

                    defenseCalendarInstance = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'timeGridWeek',
                        initialDate: initialDate,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'timeGridWeek,timeGridDay,dayGridMonth'
                        },
                        slotMinTime: '07:00:00',
                        slotMaxTime: '20:00:00',
                        allDaySlot: false,
                        height: 'auto',
                        editable: true,
                        eventDurationEditable: true,
                        events: events,
                        eventContent: function(arg) {
                            const props = arg.event.extendedProps;
                            return {
                                html: `<div class="event-team">${arg.event.title}</div>
                                       <div class="event-room">${props.room || ''}</div>`
                            };
                        },
                        eventClick: function(info) {
                            openEventEditModal(info.event, 'calendar');
                        },
                        eventDrop: function(info) {
                            // Update the schedule data cache
                            updateScheduleDataFromEvent(info.event);
                        },
                        eventResize: function(info) {
                            updateScheduleDataFromEvent(info.event);
                        }
                    });
                    defenseCalendarInstance.render();
                    setTimeout(() => {
                        if (defenseCalendarInstance) {
                            defenseCalendarInstance.updateSize();
                        }
                    }, 0);
                }

                function updateScheduleDataFromEvent(event) {
                    const id = event.id;
                    const sched = allScheduleData.find(s => String(s.id) === String(id));
                    if (sched) {
                        const start = event.start;
                        const end = event.end;
                        sched.schedule_date = start.toISOString().split('T')[0];
                        sched.start_time = start.toTimeString().substring(0, 8);
                        sched.end_time = end.toTimeString().substring(0, 8);
                    }
                }

                // ========== PREVIEW CALENDAR (for new schedule generation) ==========
                let previewCalendarInstance = null;
                let previewScheduleData = []; // The array of schedules from preview mode

                function showPreviewCalendar(schedules) {
                    previewScheduleData = schedules;
                    const events = schedules.map((s, idx) => ({
                        id: 'preview_' + idx,
                        title: s.team_name || 'Unknown',
                        start: s.schedule_date + 'T' + s.start_time,
                        end: s.schedule_date + 'T' + s.end_time,
                        editable: true,
                        classNames: ['status-preview'],
                        extendedProps: {
                            ...s,
                            previewIndex: idx,
                            panelist1: s.panelist1_name || '',
                            panelist2: s.panelist2_name || '',
                            panelist3: s.panelist3_name || '',
                            status: 'preview'
                        }
                    }));

                    // Determine initial date
                    let initialDate = new Date();
                    if (events.length > 0) {
                        const dates = events.map(e => new Date(e.start)).sort((a, b) => a - b);
                        initialDate = dates[0];
                    }

                    document.getElementById('previewScheduleCount').textContent = `${schedules.length} schedule(s) generated`;

                    const previewModal = new bootstrap.Modal(document.getElementById('schedulePreviewModal'));
                    previewModal.show();

                    // Render after modal is shown
                    document.getElementById('schedulePreviewModal').addEventListener('shown.bs.modal', function initCal() {
                        if (previewCalendarInstance) previewCalendarInstance.destroy();
                        const calEl = document.getElementById('previewCalendar');
                        previewCalendarInstance = new FullCalendar.Calendar(calEl, {
                            initialView: 'timeGridWeek',
                            initialDate: initialDate,
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'timeGridWeek,timeGridDay'
                            },
                            slotMinTime: '07:00:00',
                            slotMaxTime: '20:00:00',
                            allDaySlot: false,
                            height: 'auto',
                            editable: true,
                            eventDurationEditable: true,
                            events: events,
                            eventContent: function(arg) {
                                const props = arg.event.extendedProps;
                                return {
                                    html: `<div class="event-team">${arg.event.title}</div>
                                           <div class="event-room">${props.room || ''}</div>`
                                };
                            },
                            eventClick: function(info) {
                                openEventEditModal(info.event, 'preview');
                            },
                            eventDrop: function(info) {
                                updatePreviewDataFromEvent(info.event);
                            },
                            eventResize: function(info) {
                                updatePreviewDataFromEvent(info.event);
                            }
                        });
                        previewCalendarInstance.render();
                        document.getElementById('schedulePreviewModal').removeEventListener('shown.bs.modal', initCal);
                    }, { once: true });
                }

                function updatePreviewDataFromEvent(event) {
                    const idx = event.extendedProps.previewIndex;
                    if (idx !== undefined && previewScheduleData[idx]) {
                        const start = event.start;
                        const end = event.end;
                        previewScheduleData[idx].schedule_date = start.toISOString().split('T')[0];
                        previewScheduleData[idx].start_time = start.toTimeString().substring(0, 8);
                        previewScheduleData[idx].end_time = end.toTimeString().substring(0, 8);
                    }
                }

                // ========== EVENT EDIT MODAL ==========
                let currentEditEvent = null;
                let currentEditContext = null; // 'preview' or 'calendar'

                function openEventEditModal(event, context) {
                    currentEditEvent = event;
                    currentEditContext = context;
                    const props = event.extendedProps;
                    const isFinalized = Number(props.is_finalized || 0) === 1;

                    document.getElementById('editEventId').value = event.id;
                    document.getElementById('editTeamName').value = event.title;
                    document.getElementById('editRoom').value = props.room || '';
                    document.getElementById('editDate').value = event.start.toISOString().split('T')[0];
                    document.getElementById('editStartTime').value = event.start.toTimeString().substring(0, 5);
                    document.getElementById('editEndTime').value = event.end.toTimeString().substring(0, 5);

                    // Populate panelist dropdowns
                    const p1 = props.panelist_id || '';
                    const p2 = props.panelist_id2 || '';
                    const p3 = props.panelist_id3 || '';
                    populatePanelistDropdown(document.getElementById('editPanelist1'), p1);
                    populatePanelistDropdown(document.getElementById('editPanelist2'), p2);
                    populatePanelistDropdown(document.getElementById('editPanelist3'), p3);

                    const saveBtn = document.getElementById('saveEventEdit');
                    saveBtn.disabled = isFinalized;

                    const finalizeBtn = document.getElementById('toggleFinalizeScheduleBtn');
                    if (context === 'calendar') {
                        finalizeBtn.style.display = 'inline-block';
                        finalizeBtn.textContent = isFinalized ? 'Unfinalize' : 'Finalize';
                    } else {
                        finalizeBtn.style.display = 'none';
                    }

                    const editModalEl = document.getElementById('eventEditModal');
                    // Remove tabindex=-1 temporarily to avoid aria-hidden focus trap with stacked modals
                    editModalEl.removeAttribute('tabindex');
                    const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
                    editModal.show();
                    // Restore tabindex after shown
                    editModalEl.addEventListener('shown.bs.modal', function restoreTabindex() {
                        editModalEl.setAttribute('tabindex', '-1');
                        editModalEl.removeEventListener('shown.bs.modal', restoreTabindex);
                    }, { once: true });
                }

                document.getElementById('saveEventEdit').addEventListener('click', async function() {
                    if (!currentEditEvent) return;

                    if (Number(currentEditEvent.extendedProps?.is_finalized || 0) === 1) {
                        showDefAlert('This schedule is finalized and cannot be edited.', 'warning');
                        return;
                    }

                    const saveBtn = this;
                    const newDate = document.getElementById('editDate').value;
                    const newStart = document.getElementById('editStartTime').value;
                    const newEnd = document.getElementById('editEndTime').value;
                    const newRoom = document.getElementById('editRoom').value;
                    const newP1 = document.getElementById('editPanelist1').value;
                    const newP2 = document.getElementById('editPanelist2').value;
                    const newP3 = document.getElementById('editPanelist3').value;

                    if (!newDate || !newStart || !newEnd) {
                        if (typeof showToast === 'function') {
                            showToast('Error', 'Date and time are required.', 'error');
                        } else {
                            alert('Date and time are required.');
                        }
                        return;
                    }

                    if (newStart >= newEnd) {
                        if (typeof showToast === 'function') {
                            showToast('Error', 'End time must be after start time.', 'error');
                        } else {
                            alert('End time must be after start time.');
                        }
                        return;
                    }

                    // Get panelist names for display
                    const p1Name = document.getElementById('editPanelist1').selectedOptions[0]?.textContent?.split(' (')[0] || '';
                    const p2Name = document.getElementById('editPanelist2').selectedOptions[0]?.textContent?.split(' (')[0] || '';
                    const p3Name = document.getElementById('editPanelist3').selectedOptions[0]?.textContent?.split(' (')[0] || '';

                    saveBtn.disabled = true;
                    const originalBtnHtml = saveBtn.innerHTML;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

                    try {
                        if (currentEditContext === 'calendar') {
                            const matchedSchedule = allScheduleData.find(s => String(s.id) === String(currentEditEvent.id));
                            const teamIdForSave = currentEditEvent.extendedProps.team_id || matchedSchedule?.team_id || '';
                            const params = new URLSearchParams();
                            params.append('table', 'defense_schedules');
                            params.append('id', String(currentEditEvent.id));
                            params.append('schedule_date', newDate);
                            params.append('start_time', newStart + ':00');
                            params.append('end_time', newEnd + ':00');
                            params.append('room', newRoom);
                            params.append('team_id', String(teamIdForSave));
                            params.append('panelist_id[]', newP1 || '');
                            params.append('panelist_id[]', newP2 || '');
                            params.append('panelist_id[]', newP3 || '');

                            const response = await fetch('../dashboard/includes/update_item.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                                },
                                body: params.toString()
                            });

                            const raw = await response.text();
                            let result;
                            try {
                                result = JSON.parse(raw);
                            } catch (e) {
                                throw new Error('Server returned an invalid response.');
                            }
                            if (!result.success) {
                                throw new Error(result.message || 'Failed to save schedule changes.');
                            }
                        }

                        // Update event view after successful save (or preview edit)
                        currentEditEvent.setStart(newDate + 'T' + newStart);
                        currentEditEvent.setEnd(newDate + 'T' + newEnd);
                        currentEditEvent.setExtendedProp('room', newRoom);
                        currentEditEvent.setExtendedProp('panelist_id', newP1);
                        currentEditEvent.setExtendedProp('panelist_id2', newP2);
                        currentEditEvent.setExtendedProp('panelist_id3', newP3);
                        currentEditEvent.setExtendedProp('panelist1', p1Name);
                        currentEditEvent.setExtendedProp('panelist2', p2Name);
                        currentEditEvent.setExtendedProp('panelist3', p3Name);

                        if (currentEditContext === 'preview') {
                            const idx = currentEditEvent.extendedProps.previewIndex;
                            if (idx !== undefined && previewScheduleData[idx]) {
                                previewScheduleData[idx].schedule_date = newDate;
                                previewScheduleData[idx].start_time = newStart + ':00';
                                previewScheduleData[idx].end_time = newEnd + ':00';
                                previewScheduleData[idx].room = newRoom;
                                previewScheduleData[idx].panelist_id = newP1;
                                previewScheduleData[idx].panelist_id2 = newP2;
                                previewScheduleData[idx].panelist_id3 = newP3;
                                previewScheduleData[idx].panelist1_name = p1Name;
                                previewScheduleData[idx].panelist2_name = p2Name;
                                previewScheduleData[idx].panelist3_name = p3Name;
                            }
                        } else if (currentEditContext === 'calendar') {
                            const sched = allScheduleData.find(s => String(s.id) === String(currentEditEvent.id));
                            if (sched) {
                                sched.schedule_date = newDate;
                                sched.start_time = newStart + ':00';
                                sched.end_time = newEnd + ':00';
                                sched.room = newRoom;
                                sched.panelist_id = newP1;
                                sched.panelist_id2 = newP2;
                                sched.panelist_id3 = newP3;
                            }
                        }

                        bootstrap.Modal.getInstance(document.getElementById('eventEditModal')).hide();
                        if (typeof showToast === 'function') {
                            showToast('Success', 'Schedule changes saved.', 'success');
                        }
                    } catch (error) {
                        console.error('Failed to save event edit:', error);
                        showDefAlert(error.message || 'Failed to save schedule changes.', 'error');
                    } finally {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalBtnHtml;
                    }
                });

                document.getElementById('toggleFinalizeScheduleBtn').addEventListener('click', async function() {
                    if (!currentEditEvent || currentEditContext !== 'calendar') return;

                    const btn = this;
                    const isFinalized = Number(currentEditEvent.extendedProps?.is_finalized || 0) === 1;
                    const params = new URLSearchParams();
                    params.append('schedule_id', String(currentEditEvent.id));
                    params.append('action', isFinalized ? 'unfinalize' : 'finalize');

                    const oldHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

                    try {
                        const response = await fetch('../dashboard/includes/finalize_schedule.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body: params.toString()
                        });
                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.message || 'Failed to update finalization state.');
                        }

                        const newFinalized = isFinalized ? 0 : 1;
                        currentEditEvent.setExtendedProp('is_finalized', newFinalized);
                        currentEditEvent.setExtendedProp('approval_status', 'approved');
                        currentEditEvent.setExtendedProp('status', 'approved');
                        currentEditEvent.setProp('classNames', ['status-approved']);
                        currentEditEvent.setProp('editable', newFinalized === 0);

                        const sched = allScheduleData.find(s => String(s.id) === String(currentEditEvent.id));
                        if (sched) {
                            sched.is_finalized = newFinalized;
                            sched.approval_status = 'approved';
                        }

                        btn.textContent = newFinalized ? 'Unfinalize' : 'Finalize';
                        document.getElementById('saveEventEdit').disabled = (newFinalized === 1);
                        showDefAlert(result.message, 'success');
                    } catch (error) {
                        showDefAlert(error.message || 'Failed to update finalization state.', 'error');
                    } finally {
                        btn.disabled = false;
                        if (btn.innerHTML.includes('Processing')) {
                            btn.innerHTML = oldHtml;
                        }
                    }
                });

                // ========== CONFIRM SAVE PREVIEW ==========
                document.getElementById('confirmSavePreview').addEventListener('click', function() {
                    const btn = this;
                    btn.disabled = true;
                    btn.textContent = 'Saving...';

                    fetch('../dashboard/includes/save_preview_schedule.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ schedules: previewScheduleData })
                    })
                    .then(r => r.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.textContent = 'Confirm & Save';
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('schedulePreviewModal')).hide();
                            if (typeof window.hideLoadingState === 'function') window.hideLoadingState(true, data.message);
                            loadDefenseSchedules();
                        } else {
                            showDefAlert('Error: ' + data.message, 'error');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.textContent = 'Confirm & Save';
                        showDefAlert('Network error: ' + err.message, 'error');
                    });
                });

                // ========== BULK APPROVAL ==========
                document.getElementById('bulkApproveBtn').addEventListener('click', function() {
                    const pendingChair = allScheduleData.filter(s => s.approval_status === 'pending_chair');
                    if (pendingChair.length === 0) {
                        showDefAlert('No schedules awaiting chair review.', 'warning');
                        return;
                    }
                    showDefConfirm(
                        'Approve All Schedules',
                        `Approve all ${pendingChair.length} schedule(s) awaiting chair review? This will send panelist notices.`,
                        'Approve All',
                        'success',
                        function() {
                            const btn = document.getElementById('bulkApproveBtn');
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Approving...';

                            fetch('../dashboard/includes/handle_bulk_approval.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ action: 'approve', schedules: pendingChair })
                            })
                            .then(r => r.json())
                            .then(data => {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fas fa-check-double me-1"></i>Approve All Visible';
                                if (data.success) {
                                    showDefAlert(data.message, 'success');
                                    loadDefenseSchedules();
                                } else {
                                    if (Array.isArray(data.conflict_items) && data.conflict_items.length > 0) {
                                        data.conflict_items.forEach((item) => {
                                            showDefAlert(item, 'error');
                                        });
                                    }
                                    showDefAlert('Error: ' + data.message, 'error');
                                }
                            })
                            .catch(err => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-double me-1"></i>Approve All Visible'; showDefAlert('Network error: ' + err.message, 'error'); });
                        }
                    );
                });

                document.getElementById('bulkRejectBtn').addEventListener('click', function() {
                    const pendingChair = allScheduleData.filter(s => s.approval_status === 'pending_chair');
                    if (pendingChair.length === 0) {
                        showDefAlert('No schedules awaiting chair review.', 'warning');
                        return;
                    }
                    showDefPrompt(
                        'Reject All Schedules',
                        `Reject all ${pendingChair.length} schedule(s) awaiting chair review?`,
                        'Reason for rejection (optional)',
                        'Reject All',
                        'danger',
                        function(reason) {
                            const btn = document.getElementById('bulkRejectBtn');
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Rejecting...';

                            fetch('../dashboard/includes/handle_bulk_approval.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ action: 'reject', schedules: pendingChair, rejection_reason: reason })
                            })
                            .then(r => r.json())
                            .then(data => {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fas fa-times-circle me-1"></i>Reject All Visible';
                                if (data.success) {
                                    showDefAlert(data.message, 'success');
                                    loadDefenseSchedules();
                                } else {
                                    showDefAlert('Error: ' + data.message, 'error');
                                }
                            })
                            .catch(err => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-times-circle me-1"></i>Reject All Visible'; showDefAlert('Network error: ' + err.message, 'error'); });
                        }
                    );
                });

                // ========== MEATBALL MENU HANDLER (defense schedules) ==========
                document.addEventListener('click', function(e) {
                    // Handle meatball button clicks for defense schedules
                    if (e.target.closest('.meatball-btn[data-def-id]') && e.target.closest('#defense-schedules')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const btn = e.target.closest('.meatball-btn');
                        const defId = btn.getAttribute('data-def-id');
                        const approvalStatus = btn.getAttribute('data-approval-status') || '';

                        // Close all other defense dropdown portals
                        document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-def-"]').forEach(dd => {
                            dd.style.display = 'none';
                        });

                        let dropdown = document.getElementById(`dropdown-def-${defId}`);

                        // Lazy-create portal if it doesn't exist
                        if (!dropdown) {
                            let chairDropdownItems = '';
                            if (approvalStatus === 'pending_chair') {
                                chairDropdownItems = `
                                    <button class="meatball-dropdown-item approve-item chair-approve-btn" data-id="${defId}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="meatball-dropdown-item reject-item chair-reject-btn" data-id="${defId}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <div class="meatball-dropdown-divider"></div>`;
                            }
                            dropdown = document.createElement('div');
                            dropdown.className = 'meatball-dropdown-portal';
                            dropdown.id = `dropdown-def-${defId}`;
                            dropdown.style.cssText = 'position:fixed;background:white;border:1px solid #dee2e6;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;min-width:120px;padding:4px 0;display:none;';
                            dropdown.innerHTML = `
                                ${chairDropdownItems}
                                <button class="meatball-dropdown-item edit-item edit-btn" data-table="defense_schedules" data-id="${defId}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="meatball-dropdown-item delete-item delete-btn" data-table="defense_schedules" data-id="${defId}">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>`;

                            const matchedSchedule = allScheduleData.find(s => String(s.id) === String(defId));
                            const deleteBtn = dropdown.querySelector('.delete-btn');
                            if (deleteBtn && matchedSchedule) {
                                const scheduleDate = matchedSchedule.schedule_date ? formatDate(matchedSchedule.schedule_date) : 'Unknown date';
                                const teamName = matchedSchedule.team_name || 'Unknown team';
                                deleteBtn.dataset.deleteLabel = `${scheduleDate} - ${teamName}`;
                            }
                            document.body.appendChild(dropdown);
                        }

                        const isOpen = dropdown.style.display === 'block';
                        if (!isOpen) {
                            const btnRect = btn.getBoundingClientRect();
                            const vpWidth = window.innerWidth;
                            const ddWidth = 140;
                            let left = btnRect.right - ddWidth;
                            let top = btnRect.bottom + 5;
                            if (vpWidth < 768) left = btnRect.left + (btnRect.width / 2) - (ddWidth / 2);
                            if (left < 10) left = 10;
                            if (left + ddWidth > vpWidth - 10) left = vpWidth - ddWidth - 10;
                            dropdown.style.top = `${top}px`;
                            dropdown.style.left = `${left}px`;
                            dropdown.style.display = 'block';
                        }
                    } else if (!e.target.closest('.meatball-dropdown-portal') && !e.target.closest('.meatball-btn')) {
                        document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-def-"]').forEach(dd => { dd.style.display = 'none'; });
                    }
                });

                // ========== SINGLE CHAIR ACTIONS (from meatball dropdown) ==========
                document.addEventListener('click', function(e) {
                    const approveBtn = e.target.closest('.chair-approve-btn');
                    const rejectBtn = e.target.closest('.chair-reject-btn');
                    
                    if (approveBtn) {
                        document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => { dd.style.display = 'none'; });
                        const scheduleId = approveBtn.getAttribute('data-id');
                        showDefConfirm(
                            'Approve Schedule',
                            'Approve this defense schedule? Panelists will be notified.',
                            'Approve',
                            'success',
                            function() { handleChairAction(scheduleId, 'approve'); }
                        );
                    }
                    if (rejectBtn) {
                        document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => { dd.style.display = 'none'; });
                        const scheduleId = rejectBtn.getAttribute('data-id');
                        showDefPrompt(
                            'Reject Schedule',
                            'Are you sure you want to reject this defense schedule?',
                            'Reason for rejection (optional)',
                            'Reject',
                            'danger',
                            function(reason) { handleChairAction(scheduleId, 'reject', reason); }
                        );
                    }
                });

                function handleChairAction(scheduleId, action, reason = '') {
                    const formData = new FormData();
                    formData.append('schedule_id', scheduleId);
                    formData.append('action', action);
                    if (reason) formData.append('rejection_reason', reason);

                    fetch('../assets/includes/handle_chair_approval.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showDefAlert(data.message, 'success');
                            loadDefenseSchedules();
                        } else {
                            showDefAlert('Error: ' + data.message, 'error');
                        }
                    })
                    .catch(err => showDefAlert('Network error. Please try again.', 'error'));
                }

                // ========== EXPOSE showPreviewCalendar for generate handler ==========
                window.showDefensePreviewCalendar = showPreviewCalendar;
            });
        </script>
    </div>
</div>