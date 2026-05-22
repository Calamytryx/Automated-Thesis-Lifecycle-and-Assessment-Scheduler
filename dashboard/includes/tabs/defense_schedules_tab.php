<!-- Defense Schedules Tab -->
<div class="tab-pane fade" id="defense-schedules" role="tabpanel" aria-labelledby="defense-schedules-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Defense Schedules Management</h3>
                <p class="text-muted">Manage thesis defense schedules, generate automated schedules, and assign panelists to groups</p>
                        <!-- Scheduler Status -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="bg-light border rounded-3 p-3">
                            <div id="generationSetting" class="mb-2" style="display: none;"></div>
                            <div id="scheduleGenerationStatus" class="mt-2"></div>
                        </div>
                    </div>
                </div>
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
                            <div class="defense-controls-group">
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
                <div class="defense-export-container mb-3">
                    <div class="defense-export-row">
                        <div class="defense-export-controls">
                            <input type="date" class="form-control user-control-height defense-export-date" id="reportStartDate" aria-label="Start date">
                            <input type="date" class="form-control user-control-height defense-export-date" id="reportEndDate" aria-label="End date">
                            <div class="defense-export-menu" id="defenseExportMenu">
                                <button class="btn defense-export-btn user-control-height" id="exportDefensePdf" type="button" aria-haspopup="true" aria-expanded="false">
                                    <svg class="defense-export-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <path d="M8 1.5v8.086l2.243-2.243 1.06 1.061L8 12.707 4.697 8.404l1.06-1.06L8 9.585V1.5h0Z" fill="currentColor"/>
                                        <path d="M2.5 12.5h11v1.5h-11v-1.5Z" fill="currentColor"/>
                                    </svg>
                                    <span class="defense-export-trigger-label">Export Table View</span>
                                    <i class="fas fa-angle-down ms-1"></i>
                                </button>
                                <div class="defense-export-options" role="menu" aria-label="Defense export options">
                                    <button type="button" class="defense-export-option" data-export-type="table" role="menuitem">Table View</button>
                                    <button type="button" class="defense-export-option" data-export-type="calendar" role="menuitem">Calendar View</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <div class="mb-3">
                                <label for="validationMode" class="form-label">Validation Mode</label>
                                <select class="form-select" id="validationMode" name="validationMode">
                                    <option value="hybrid" selected>Hybrid - stop on hard blockers, continue on warnings</option>
                                    <option value="strict">Strict - stop generation if any conflict remains</option>
                                    <option value="soft">Soft - continue and report unresolved groups</option>
                                </select>
                                <small class="form-text text-muted">Strict mode blocks generation when no conflict-free slot exists. Soft mode keeps going and reports unresolved teams.</small>
                            </div>
                            <script>
                                // Define validateInputs and other functions at the global scope
                                // Declare global variables
                                let timeDurationInput, startTimeInput, endTimeInput, daysInput, roomsInput, sectionSelect, 
                                    saveButton, statusElement, includeLunchBreakCheckbox, validationModeSelect,
                                    schedulerEstimateTimer = null, schedulerRunning = false;

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
                                                warningMessage = `Not enough time slots (${totalSlots}) for the selected groups (${numberOfTeams}). Adjust settings or filter.`;
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
                                    validationModeSelect = document.getElementById('validationMode');

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
                                    function touchSchedulerForm() {
                                        updateTeamCount(validateInputs);
                                        debouncedSchedulerSlotEstimate();
                                    }

                                    if (timeDurationInput) {
                                        timeDurationInput.addEventListener('input', () => {
                                            correctTimeDuration();
                                            touchSchedulerForm();
                                        });
                                        timeDurationInput.addEventListener('change', () => {
                                            correctTimeDuration();
                                            touchSchedulerForm();
                                        });
                                    }

                                    if (startTimeInput) {
                                        startTimeInput.addEventListener('input', touchSchedulerForm);
                                    }
                                    
                                    if (endTimeInput) {
                                        endTimeInput.addEventListener('input', touchSchedulerForm);
                                    }
                                    
                                    if (daysInput) {
                                        daysInput.addEventListener('change', touchSchedulerForm);
                                    }
                                    
                                    if (roomsInput) {
                                        roomsInput.addEventListener('input', touchSchedulerForm);
                                    }
                                    
                                    if (includeLunchBreakCheckbox) {
                                        includeLunchBreakCheckbox.addEventListener('change', touchSchedulerForm);
                                    }
                                    
                                    if (sectionSelect) {
                                        sectionSelect.addEventListener('change', touchSchedulerForm);
                                    }

                                    if (validationModeSelect) {
                                        validationModeSelect.addEventListener('change', touchSchedulerForm);
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
                                            touchSchedulerForm();
                                        });
                                    });

                                    // Build discrete start times (same rules as Generate) for server-side feasibility count.
                                    function buildSchedulerTimeSlotsArray() {
                                        const duration = parseFloat(document.getElementById('timeDuration')?.value);
                                        const startTime = document.getElementById('startTime')?.value;
                                        const endTime = document.getElementById('endTime')?.value;
                                        if (!startTime || !endTime || !Number.isFinite(duration) || duration <= 0) {
                                            return [];
                                        }
                                        const increment = (duration % 1 === 0) ? 60 : 30;
                                        let currentTime = new Date(`1970-01-01T${startTime}`);
                                        if (duration % 1 === 0) {
                                            currentTime.setMinutes(0);
                                        }
                                        const endDateTime = new Date(`1970-01-01T${endTime}`);
                                        function slotPasses2030Ceiling(slotHHMM, durHrs) {
                                            const pt = /^(\d{1,2}):(\d{2})$/.exec(String(slotHHMM).trim()) || /^(\d{1,2}):(\d{2}):\d{2}$/.exec(String(slotHHMM).trim());
                                            if (!pt) return false;
                                            const hh = parseInt(pt[1], 10);
                                            const mm = parseInt(pt[2], 10);
                                            if (Number.isNaN(hh) || Number.isNaN(mm)) return false;
                                            const startMin = hh * 60 + mm;
                                            const capMin = 20 * 60 + 30;
                                            const endMin = startMin + Math.round(Number(durHrs) * 60);
                                            return endMin <= capMin;
                                        }
                                        const timeSlots = [];
                                        while (currentTime < endDateTime) {
                                            const hhmm = currentTime.toTimeString().substring(0, 5);
                                            if (slotPasses2030Ceiling(hhmm, duration)) {
                                                timeSlots.push(hhmm);
                                            }
                                            currentTime.setMinutes(currentTime.getMinutes() + increment);
                                        }
                                        return timeSlots;
                                    }

                                    function requestSchedulerSlotEstimate() {
                                        const el = document.getElementById('schedulerSlotEstimate');
                                        if (!el) return;

                                        const generateBtn = document.getElementById('generateSchedule');
                                        const escapeHtml = function(value) {
                                            return String(value)
                                                .replace(/&/g, '&amp;')
                                                .replace(/</g, '&lt;')
                                                .replace(/>/g, '&gt;')
                                                .replace(/"/g, '&quot;')
                                                .replace(/'/g, '&#39;');
                                        };

                                        const rooms = (document.getElementById('rooms')?.value || '').split(',').map(r => r.trim()).filter(Boolean);
                                        const days = (document.getElementById('days')?.value || '').split(',').map(d => d.trim()).filter(Boolean);
                                        const duration = parseFloat(document.getElementById('timeDuration')?.value);
                                        const timeSlots = buildSchedulerTimeSlotsArray();
                                        const sectionHidden = document.getElementById('selectedSection');
                                        const sectionSel = document.getElementById('sectionSelect');
                                        const section = (sectionHidden && sectionHidden.value) ? sectionHidden.value : (sectionSel ? sectionSel.value : '');

                                        if (!rooms.length || !days.length || !Number.isFinite(duration) || duration <= 0 || !timeSlots.length) {
                                            el.innerHTML = '<span class="text-muted">Save valid settings to see how many conflict-free placements exist after class schedules are loaded.</span>';
                                            return;
                                        }

                                        el.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Loading class schedules and counting feasible slots…</span>';

                                        $.ajax({
                                            url: '../dashboard/includes/run_scheduler.php',
                                            method: 'POST',
                                            data: {
                                                estimate_slots: 'true',
                                                rooms: rooms,
                                                days: days,
                                                timeSlots: timeSlots,
                                                timeDuration: duration,
                                                selectedSection: section,
                                                validationMode: document.getElementById('validationMode') ? document.getElementById('validationMode').value : 'hybrid'
                                            },
                                            dataType: 'json',
                                            success: function(res) {
                                                if (!res || !res.success) {
                                                    el.innerHTML = '<span class="text-danger">' + (res && res.message ? res.message : 'Could not estimate slots.') + '</span>';
                                                    if (generateBtn) {
                                                        generateBtn.disabled = true;
                                                    }
                                                    return;
                                                }
                                                const n = res.feasible_placement_count;
                                                const teams = res.teams_considered != null ? res.teams_considered : '—';
                                                const maxChk = res.theoretical_max_checked != null ? res.theoretical_max_checked : '—';
                                                const blockedByDay = res.blocked_time_slots_by_day && typeof res.blocked_time_slots_by_day === 'object'
                                                    ? res.blocked_time_slots_by_day
                                                    : {};
                                                const blockedDays = Object.keys(blockedByDay);
                                                let warnHtml = '';
                                                if (Array.isArray(res.estimate_warnings) && res.estimate_warnings.length) {
                                                    warnHtml = '<ul class="mb-0 mt-2 text-warning-emphasis small ps-3">' +
                                                        res.estimate_warnings.map(function(w) {
                                                            return '<li>' + String(w).replace(/</g, '&lt;') + '</li>';
                                                        }).join('') + '</ul>';
                                                }
                                                let blockedHtml = '';
                                                if (blockedDays.length) {
                                                    blockedHtml = '<div class="alert alert-warning mt-2 mb-0 small">' +
                                                        '<div class="fw-semibold mb-1">Blocked start times to remove</div>' +
                                                        '<ul class="mb-0 ps-3">' +
                                                        blockedDays.slice(0, 6).map(function(day) {
                                                            const slots = Array.isArray(blockedByDay[day]) ? blockedByDay[day] : [];
                                                            const slotHtml = slots.slice(0, 6).map(function(slot) {
                                                                const reasons = Array.isArray(slot.reasons) && slot.reasons.length
                                                                    ? '<div class="text-muted">' + slot.reasons.map(function(reason) {
                                                                        return escapeHtml(reason);
                                                                    }).join('<br>') + '</div>'
                                                                    : '';
                                                                return '<li><strong>' + escapeHtml(slot.time_slot) + '</strong>' + reasons + '</li>';
                                                            }).join('');
                                                            return '<li><strong>' + escapeHtml(day) + '</strong><ul class="mb-0 ps-3">' + slotHtml + '</ul></li>';
                                                        }).join('') +
                                                        '</ul>' +
                                                    '</div>';
                                                }
                                                if (generateBtn) {
                                                    generateBtn.disabled = n <= 0;
                                                }
                                                if (n > 0) {
                                                    el.innerHTML = '<span class="text-success fw-semibold">' + n + '</span> conflict-free placement(s) for <span class="text-muted">' + teams + ' team(s)</span> (student + faculty class loads applied)'
                                                        + (maxChk !== '—' ? ' <span class="text-muted">(upper bound naive combinations: ' + maxChk + ')</span>.' : '.') + warnHtml + blockedHtml;
                                                } else {
                                                    el.innerHTML = '<span class="text-danger fw-semibold">0</span> conflict-free placements — class conflicts remove all available start times for the current rooms/dates/time window.' + warnHtml + blockedHtml;
                                                }
                                            },
                                            error: function() {
                                                el.innerHTML = '<span class="text-danger">Failed to reach server for slot estimate.</span>';
                                            }
                                        });
                                    }

                                    function debouncedSchedulerSlotEstimate() {
                                        if (schedulerEstimateTimer) {
                                            clearTimeout(schedulerEstimateTimer);
                                        }
                                        schedulerEstimateTimer = setTimeout(requestSchedulerSlotEstimate, 400);
                                    }

                                    console.log('DOM Content Loaded: All event listeners attached');
                                });
                            </script>
                            <div class="mb-3">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" min="07:00 AM" max="20:30" step="1800" required
                                    onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                            </div>
                            <div class="mb-3">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime" min="07:00" max="20:30" step="1800" required
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

                                    // --- Schedule generation console tracing -------------------
                                    // Lightweight, timestamped logging so the whole generation
                                    // lifecycle is visible in the browser console.
                                    let schedGenStart = 0;
                                    function schedElapsed() {
                                        if (!schedGenStart) return '0.0s';
                                        return ((performance.now() - schedGenStart) / 1000).toFixed(1) + 's';
                                    }
                                    function schedLog(stage, ...details) {
                                        console.log(`%c[Scheduler +${schedElapsed()}]%c ${stage}`,
                                            'color:#0d6efd;font-weight:bold', 'color:inherit', ...details);
                                    }
                                    window.schedLog = schedLog;

                                    function updateProgress(message, percentage = null) {
                                        const progressText = document.getElementById('progressText');
                                        const progressBar = document.getElementById('progressBar');

                                        if (progressText) progressText.textContent = message;
                                        if (progressBar && percentage !== null) {
                                            progressBar.style.width = percentage + '%';
                                        }
                                        schedLog('progress', `${percentage !== null ? percentage + '% ' : ''}${message}`);
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
                                        if (typeof window.stopLiveProgress === 'function') window.stopLiveProgress();
                                    }

                                    // Expose to global scope for cross-script access
                                    window.hideLoadingState = hideLoadingState;
                                    window.showLoadingState = showLoadingState;
                                    window.updateScheduleProgress = updateProgress;

                                    // Progress polling system
                                    function pollScheduleProgress(progressId) {
                                        schedLog('polling started', 'progressId=' + progressId);
                                        let lastMessage = null;
                                        const pollInterval = setInterval(() => {
                                            fetch(`../dashboard/includes/get_schedule_progress.php?id=${progressId}`)
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.status === 'running') {
                                                        // Only log when the message actually changes to avoid spam.
                                                        if (data.message !== lastMessage) {
                                                            lastMessage = data.message;
                                                            schedLog('stage', `${data.percentage ?? '?'}% — ${data.message}`);
                                                        }
                                                        updateProgress(data.message, data.percentage);
                                                    } else if (data.status === 'completed') {
                                                        clearInterval(pollInterval);
                                                        schedLog('COMPLETED', `total time ${schedElapsed()}`);
                                                        hideLoadingState(true, 'Schedule generated successfully!');
                                                        // Reload the defense schedules table
                                                        setTimeout(() => {
                                                            if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                                                window.reloadCurrentDefenseSchedulesView(1);
                                                            }
                                                        }, 1000);
                                                    } else if (data.status === 'error') {
                                                        clearInterval(pollInterval);
                                                        schedLog('ERROR', data.message || 'unknown error', `after ${schedElapsed()}`);
                                                        hideLoadingState(false, data.message || 'An error occurred during generation');
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('[Scheduler] Progress polling error:', error);
                                                    clearInterval(pollInterval);
                                                    hideLoadingState(false, 'Failed to monitor progress');
                                                });
                                        }, 1000); // Poll every second
                                    }

                                    // Live progress poller (display only). Runs WHILE the long
                                    // run_scheduler.php request is still in flight so the user sees
                                    // real backend stages instead of a frozen "Initializing…".
                                    // It never finalizes the UI — the AJAX success/error callback is
                                    // the source of truth (preview mode returns the schedule inline).
                                    let liveProgressTimer = null;
                                    let liveProgressLastMsg = null;
                                    function startLiveProgress(progressId) {
                                        stopLiveProgress();
                                        liveProgressLastMsg = null;
                                        schedLog('live polling started', 'progressId=' + progressId);
                                        liveProgressTimer = setInterval(() => {
                                            fetch(`../dashboard/includes/get_schedule_progress.php?id=${progressId}`)
                                                .then(r => r.json())
                                                .then(data => {
                                                    if (!data || !data.message) return;
                                                    if (data.message !== liveProgressLastMsg) {
                                                        liveProgressLastMsg = data.message;
                                                        schedLog('stage', `${data.percentage ?? '?'}% — ${data.message}`);
                                                    }
                                                    if (data.status === 'running' || data.status === 'info' || data.status === 'completed') {
                                                        updateProgress(data.message, data.percentage);
                                                    }
                                                })
                                                .catch(() => { /* transient; keep polling */ });
                                        }, 800);
                                    }
                                    function stopLiveProgress() {
                                        if (liveProgressTimer) {
                                            clearInterval(liveProgressTimer);
                                            liveProgressTimer = null;
                                        }
                                    }
                                    window.startLiveProgress = startLiveProgress;
                                    window.stopLiveProgress = stopLiveProgress;

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
                                            schedGenStart = performance.now();
                                            schedLog('generation started');

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
                                        function slotPasses2030Ceiling(slotHHMM, durHrs) {
                                            const pt = /^(\d{1,2}):(\d{2})$/.exec(String(slotHHMM).trim()) || /^(\d{1,2}):(\d{2}):\d{2}$/.exec(String(slotHHMM).trim());
                                            if (!pt) return false;
                                            const hh = parseInt(pt[1], 10);
                                            const mm = parseInt(pt[2], 10);
                                            if (Number.isNaN(hh) || Number.isNaN(mm)) return false;
                                            const startMin = hh * 60 + mm;
                                            const capMin = 20 * 60 + 30;
                                            const endMin = startMin + Math.round(Number(durHrs) * 60);
                                            return endMin <= capMin;
                                        }

                                        const timeSlots = [];
                                        while (currentTime < endDateTime) {
                                            const hhmm = currentTime.toTimeString().substring(0, 5);
                                            if (slotPasses2030Ceiling(hhmm, duration)) {
                                                timeSlots.push(hhmm);
                                            }
                                            currentTime.setMinutes(currentTime.getMinutes() + increment);
                                        }

                                        const requestData = {
                                            rooms: rooms,
                                            timeDuration: duration,
                                            timeSlots: timeSlots,
                                            days: days,
                                            validationMode: document.getElementById("validationMode") ? document.getElementById("validationMode").value : 'hybrid',
                                            section: section,
                                            confirm_overwrite: confirmOverwrite,
                                            preview: 'true'
                                        };
                                        // Client-generated progress ID so we can poll the backend
                                        // for live stage updates while this request is still running.
                                        const progressId = 'sched_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
                                        requestData.progressId = progressId;
                                        if (Array.isArray(window.__defensePendingUnresolvedTeamIds) && window.__defensePendingUnresolvedTeamIds.length > 0) {
                                            requestData.unresolved_team_ids = window.__defensePendingUnresolvedTeamIds;
                                            window.__defensePendingUnresolvedTeamIds = [];
                                        }
                                        schedLog('request payload', requestData);
                                        schedLog('POST run_scheduler.php (request sent)');
                                        startLiveProgress(progressId);

                                        $.ajax({
                                            url: '../dashboard/includes/run_scheduler.php',
                                            method: 'POST',
                                            data: requestData,
                                            dataType: 'json',
                                            success: function(response) {
                                                stopLiveProgress();
                                                schedLog('response received', `success=${response.success}` + (response.preview ? ' (preview)' : ''), response);
                                                if (response.success && response.preview && response.schedules) {
                                                    // Preview mode: show editable calendar
                                                    schedLog('preview ready', `${(response.schedules || []).length} scheduled rows`);
                                                    hideLoadingState(true, 'Preview ready! Review the schedule below.');
                                                    if (typeof window.showDefensePreviewCalendar === 'function') {
                                                        window.showDefensePreviewCalendar(response.schedules, response);
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
                                                        upgradeList += `<p class="text-muted"><small>Note: ${response.missingGrades.length} group(s) cannot be upgraded because grades are missing.</small></p>`;
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
                                                schedLog('REQUEST FAILED', `status=${status}`, error, xhr.responseText);
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
                                <small class="form-text text-muted">Select a section to filter groups for scheduling.</small>
                                <input type="hidden" id="selectedTeamCount" name="selectedTeamCount" value="0">
                            </div>
                        </form>
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teams");
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalTeams = $row['total'] ?? 0;
                        ?>
                        <div class="mt-3">Selected Groups for Scheduling: <span id="teamCountDisplay"><?php echo $totalTeams; ?></span></div>
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
                                        "Dates: " + (document.getElementById("days").value || "N/A") + "<br>" +
                                        "Include Lunch Break: " + (document.getElementById("includeLunchBreak").checked ? "Yes" : "No") + "<br>" +
                                        "Validation Mode: " + (document.getElementById("validationMode") ? document.getElementById("validationMode").value : "hybrid") +
                                        '<div class="mt-3 pt-2 border-top"><div id="schedulerSlotEstimate" class="small text-muted">Class schedule estimate will appear below after you save settings.</div></div>';
                                    const generationSettingEl = document.getElementById("generationSetting");
                                    generationSettingEl.innerHTML = settingsOutput;
                                    generationSettingEl.style.display = 'block';
                                    if (typeof requestSchedulerSlotEstimate === 'function') {
                                        requestSchedulerSlotEstimate();
                                    }

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
                    <th>Group</th>
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
                    <div class="modal-header flex-wrap gap-2 align-items-center">
                        <div class="me-auto">
                            <h5 class="modal-title mb-0" id="schedulePreviewModalLabel">Schedule Preview — Review & Edit Before Saving</h5>
                        </div>
                        <div id="previewVariantWrap" class="d-none d-flex align-items-center gap-2">
                            <label for="previewVariantSelect" class="small mb-0 text-muted text-nowrap">Layout options</label>
                            <select id="previewVariantSelect" class="form-select form-select-sm" style="min-width:13rem;"></select>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="container-fluid p-3">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Drag events</strong> to move them to different times/days. <strong>Click an event</strong> to edit details (room, panelists). When satisfied, click <strong>Confirm & Save</strong>.
                            </div>
                            <div id="previewDiagnostics" class="mb-3"></div>
                            <div class="def-calendar-legend mb-3" aria-label="Defense schedule preview legend">
                                <span class="def-legend-item"><span class="def-legend-dot def-legend-valid"></span>Valid schedule</span>
                                <span class="def-legend-item"><span class="def-legend-dot def-legend-conflict"></span>Conflict detected</span>
                                <span class="def-legend-item"><span class="def-legend-dot def-legend-overlay"></span>Section class schedule (preview teams)</span>
                            </div>
                            <div id="previewCalendar" style="min-height:70vh;"></div>
                        </div>
                    </div>
                    <div class="modal-footer flex-column align-items-stretch gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                            <span id="previewScheduleCount" class="me-auto text-muted"></span>
                            <button type="button" class="btn btn-outline-primary" id="regeneratePreviewSchedule">Generate Another Schedule</button>
                            <button type="button" class="btn btn-outline-warning d-none" id="generateUnresolvedPreview">Generate Unresolved Only</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Discard</button>
                            <button type="button" class="btn btn-primary" id="confirmSavePreview">Confirm & Save</button>
                        </div>
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
                            <label class="form-label fw-bold">Group</label>
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
                                <input type="time" class="form-control" id="editStartTime" step="1800" min="07:00" max="20:30">
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-bold">End</label>
                                <input type="time" class="form-control" id="editEndTime" step="1800" min="07:00" max="20:30">
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
            .fc-event.status-preview.preview-slot-clear:not(.preview-conflict) {
                border-left: 5px solid #22c55e !important;
            }
            .fc-event.status-preview.preview-warning {
                border-left: 5px solid #eab308 !important;
                box-shadow: inset 0 0 0 1px rgba(234, 179, 8, 0.45);
            }
            .fc-event.status-preview.preview-conflict { background-color: #dc2626 !important; border-color: #b91c1c !important; color: #fff !important; }
            .defense-export-controls {
                display: inline-flex;
                align-items: stretch;
                border: 1px solid #d1d5db;
                border-radius: 0.5rem;
                overflow: hidden;
                background: #fff;
            }
            .defense-export-controls .defense-export-date,
            .defense-export-controls .defense-export-btn {
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }
            .defense-export-controls .defense-export-date {
                min-width: 10.5rem;
            }
            .defense-export-controls .defense-export-date + .defense-export-date {
                border-left: 1px solid #d1d5db;
            }
            .defense-export-controls .defense-export-btn {
                border-left: 1px solid #d1d5db;
                white-space: nowrap;
            }
            .preview-diagnostics-card {
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 14px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            }
            .preview-diagnostics-list {
                max-height: 180px;
                overflow: auto;
                margin: 0;
                padding-left: 1rem;
            }
            .preview-overlay-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                border-radius: 999px;
                padding: 0.35rem 0.75rem;
                font-size: 0.82rem;
                background: #eef2ff;
                color: #3730a3;
                margin: 0.2rem 0.2rem 0 0;
            }
            .def-legend-valid { background-color: #10b981; }
            .def-legend-conflict { background-color: #dc2626; }
            .def-legend-overlay { background-color: #f59e0b; }
            #previewCalendar .fc-bg-event.preview-overlay-class {
                opacity: 1 !important;
                background-color: rgba(245, 158, 11, 0.42) !important;
            }
            #previewCalendar .fc-event.preview-overlay-class:not(.fc-bg-event) {
                background-color: rgba(253, 230, 138, 0.95) !important;
                border: 1px dashed #d97706 !important;
                color: #451a03 !important;
                font-size: 0.72rem;
                z-index: 1 !important;
            }
            #previewCalendar .fc-event.status-preview {
                z-index: 4 !important;
            }
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

                // ========== EXPORT PDF (Date range) ==========
                const defenseExportMenu = document.getElementById('defenseExportMenu');
                const defenseExportButton = document.getElementById('exportDefensePdf');
                const defenseExportLabel = document.querySelector('.defense-export-trigger-label');
                const defenseExportOptions = document.querySelectorAll('.defense-export-option');

                const setDefenseExportMenuOpen = (isOpen) => {
                    if (!defenseExportMenu || !defenseExportButton) return;
                    defenseExportMenu.classList.toggle('is-open', isOpen);
                    defenseExportButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                };

                const setDefenseExportBusy = (isBusy) => {
                    if (!defenseExportButton) return;
                    defenseExportButton.disabled = isBusy;
                    if (defenseExportLabel) {
                        defenseExportLabel.textContent = isBusy ? 'Preparing...' : 'Export Table View';
                    }
                };

                const updateDefenseExportLabel = () => {
                    if (defenseExportLabel) {
                        defenseExportLabel.textContent = 'Export Table View';
                    }
                };

                const getDefenseExportRange = () => {
                    const start = document.getElementById('reportStartDate').value;
                    const end = document.getElementById('reportEndDate').value;

                    if (!start || !end) {
                        showDefAlert('Please select both start and end dates for the report.', 'warning');
                        return null;
                    }

                    if (start > end) {
                        showDefAlert('Start date must be before or equal to end date.', 'warning');
                        return null;
                    }

                    return { start, end };
                };

                const ensureJsPdf = (cb) => {
                    if (window.jspdf) return cb();
                    const s = document.createElement('script');
                    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                    s.onload = cb;
                    s.onerror = function() { showDefAlert('Failed to load PDF library.', 'error'); };
                    document.head.appendChild(s);
                };

                const formatDefenseDateLabel = (ymd) => {
                    try {
                        const d = new Date(ymd + 'T00:00:00');
                        return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                    } catch (e) {
                        return ymd;
                    }
                };

                const formatDefenseTime = (hm) => {
                    if (!hm) return '';
                    const parts = String(hm).split(':');
                    if (parts.length < 2) return String(hm);
                    let hh = parseInt(parts[0], 10);
                    const mm = parts[1];
                    if (Number.isNaN(hh)) return String(hm);
                    const ampm = hh >= 12 ? 'PM' : 'AM';
                    hh = hh % 12 || 12;
                    return `${hh}:${mm} ${ampm}`;
                };

                const groupDefenseRowsByWeek = (rows, startDate, endDate) => {
                    const start = new Date(`${startDate}T00:00:00`);
                    const end = new Date(`${endDate}T00:00:00`);
                    const weekMap = new Map();

                    const getWeekStart = (date) => {
                        const d = new Date(date.getTime());
                        const day = d.getDay();
                        const delta = (day === 0 ? -6 : 1) - day;
                        d.setDate(d.getDate() + delta);
                        d.setHours(0, 0, 0, 0);
                        return d;
                    };

                    const getWeekInfo = (date) => {
                        const weekStart = getWeekStart(date);
                        const weekEnd = new Date(weekStart.getTime());
                        weekEnd.setDate(weekEnd.getDate() + 5);
                        return {
                            key: `${weekStart.toISOString().slice(0, 10)}__${weekEnd.toISOString().slice(0, 10)}`,
                            weekStart,
                            weekEnd
                        };
                    };

                    let cursor = getWeekInfo(start).weekStart;
                    const finalWeekStart = getWeekInfo(end).weekStart;
                    while (cursor <= finalWeekStart) {
                        const info = getWeekInfo(cursor);
                        weekMap.set(info.key, { key: info.key, weekStart: info.weekStart, weekEnd: info.weekEnd, rows: [] });
                        cursor.setDate(cursor.getDate() + 7);
                    }

                    rows.forEach((row) => {
                        if (!row.schedule_date) return;
                        const rowDate = new Date(`${row.schedule_date}T00:00:00`);
                        if (rowDate < start || rowDate > end) return;
                        const info = getWeekInfo(rowDate);
                        if (!weekMap.has(info.key)) {
                            weekMap.set(info.key, { key: info.key, weekStart: info.weekStart, weekEnd: info.weekEnd, rows: [] });
                        }
                        weekMap.get(info.key).rows.push(row);
                    });

                    return [...weekMap.values()]
                        .filter(week => Array.isArray(week.rows) && week.rows.length > 0)
                        .sort((a, b) => a.weekStart - b.weekStart)
                        .map((week) => {
                            week.rows.sort((left, right) => {
                                const dateDiff = String(left.schedule_date || '').localeCompare(String(right.schedule_date || ''));
                                if (dateDiff !== 0) return dateDiff;
                                return String(left.start_time || '').localeCompare(String(right.start_time || ''));
                            });
                            return week;
                        });
                };

                const generateDefenseTablePdf = (pdf, rows, currentUserName, currentUserCollege) => {
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    const margin = 10;
                    const footerGap = 14;
                    const bottomThreshold = pageHeight - margin - footerGap;
                    const grouped = {};

                    rows.forEach((row) => {
                        const dateKey = row.schedule_date || 'unknown';
                        const roomKey = row.room || 'Unspecified';
                        grouped[dateKey] = grouped[dateKey] || {};
                        grouped[dateKey][roomKey] = grouped[dateKey][roomKey] || [];
                        grouped[dateKey][roomKey].push(row);
                    });

                    const drawHeader = (collegeName) => {
                        const centerX = pageWidth / 2;
                        pdf.setTextColor(0, 0, 0);
                        pdf.setFont('times', 'bold');
                        pdf.setFontSize(14);
                        pdf.text('LYCEUM OF THE PHILIPPINES UNIVERSITY - CAVITE', centerX, 12, { align: 'center' });
                        pdf.setFont('times', 'normal');
                        pdf.setFontSize(11);
                        pdf.text(collegeName || '', centerX, 18, { align: 'center' });
                        return 28;
                    };

                    const drawFooter = () => {
                        const footerY = pageHeight - 10;
                        pdf.setTextColor(0, 0, 0);
                        pdf.setFont('times', 'italic');
                        pdf.setFontSize(8.5);
                        pdf.text(`Date printed/exported: ${new Date().toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })}`, margin, footerY);
                        pdf.text(`Printed by: ${currentUserName} through ATLAS`, pageWidth - margin, footerY, { align: 'right' });
                    };

                    const drawTableHeader = (y) => {
                        const colW = { time: 22, title: 48, adviser: 35, members: 42, p1: 25, p2: 25, p3: 25 };
                        const colX = {
                            time: margin,
                            title: margin + colW.time,
                            adviser: margin + colW.time + colW.title,
                            members: margin + colW.time + colW.title + colW.adviser,
                            p1: margin + colW.time + colW.title + colW.adviser + colW.members,
                            p2: margin + colW.time + colW.title + colW.adviser + colW.members + colW.p1,
                            p3: margin + colW.time + colW.title + colW.adviser + colW.members + colW.p1 + colW.p2
                        };
                        const headerHeight = 6;

                        pdf.setFont('times', 'bold');
                        pdf.setFontSize(9);
                        pdf.rect(colX.time, y, colW.time, headerHeight);
                        pdf.rect(colX.title, y, colW.title, headerHeight);
                        pdf.rect(colX.adviser, y, colW.adviser, headerHeight);
                        pdf.rect(colX.members, y, colW.members, headerHeight);
                        pdf.rect(colX.p1, y, colW.p1, headerHeight);
                        pdf.rect(colX.p2, y, colW.p2, headerHeight);
                        pdf.rect(colX.p3, y, colW.p3, headerHeight);

                        const textY = y + 4.2;
                        pdf.text('Time', colX.time + 0.8, textY);
                        pdf.text('Title', colX.title + 0.8, textY);
                        pdf.text('Adviser', colX.adviser + 0.8, textY);
                        pdf.text('Members', colX.members + 0.8, textY);
                        pdf.text('P1', colX.p1 + 0.8, textY);
                        pdf.text('P2', colX.p2 + 0.8, textY);
                        pdf.text('P3', colX.p3 + 0.8, textY);

                        return { colW, colX, nextY: y + headerHeight };
                    };

                    const drawDateHeader = (dateLabel) => {
                        let y = drawHeader(currentUserCollege);
                        pdf.setFont('times', 'bold');
                        pdf.setFontSize(14);
                        pdf.text(dateLabel, margin, y);
                        return y + 7;
                    };

                    const drawRoomHeader = (roomLabel, y, isContinued = false) => {
                        pdf.setFont('times', 'bold');
                        pdf.setFontSize(11);
                        pdf.text(`Room: ${roomLabel}${isContinued ? ' (continued)' : ''}`, margin, y);
                        return drawTableHeader(y + 6);
                    };

                    Object.keys(grouped).sort().forEach((date, dateIndex) => {
                        if (dateIndex > 0) {
                            pdf.addPage();
                        }

                        const dateLabel = formatDefenseDateLabel(date);
                        let y = drawDateHeader(dateLabel);
                        const rooms = Object.keys(grouped[date]).sort();

                        rooms.forEach((room) => {
                            const roomRows = grouped[date][room];
                            // 12 = room label (6) + table header (6); 6 = minimum first data row
                            if ((y + 18) > bottomThreshold) {
                                pdf.addPage();
                                y = drawDateHeader(dateLabel);
                            }

                            let tableState = drawRoomHeader(room, y, false);
                            const colW = tableState.colW;
                            const colX = tableState.colX;
                            let currentY = tableState.nextY;

                            roomRows.forEach((item) => {
                                const time = `${formatDefenseTime(item.start_time)} - ${formatDefenseTime(item.end_time)}`;
                                const title = item.thesis_title || item.team_name || 'N/A';
                                const adviser = item.adviser || 'N/A';
                                const members = item.members || 'N/A';
                                const panel = (item.panelists || '').split(',').map(p => p.trim()).filter(Boolean);
                                while (panel.length < 3) panel.push('');

                                const splitTime = pdf.splitTextToSize(time, colW.time - 1.6);
                                const splitTitle = pdf.splitTextToSize(title, colW.title - 1.6);
                                const splitAdviser = pdf.splitTextToSize(adviser, colW.adviser - 1.6);
                                const memberNames = members.split(',').map(name => name.trim()).filter(Boolean);
                                const splitMembers = memberNames.length ? memberNames.flatMap(name => pdf.splitTextToSize(name, colW.members - 1.6)) : ['N/A'];
                                const splitP1 = pdf.splitTextToSize(panel[0] || '', colW.p1 - 1.6);
                                const splitP2 = pdf.splitTextToSize(panel[1] || '', colW.p2 - 1.6);
                                const splitP3 = pdf.splitTextToSize(panel[2] || '', colW.p3 - 1.6);
                                const lineCount = Math.max(
                                    Array.isArray(splitTime) ? splitTime.length : 1,
                                    Array.isArray(splitTitle) ? splitTitle.length : 1,
                                    Array.isArray(splitAdviser) ? splitAdviser.length : 1,
                                    Array.isArray(splitMembers) ? splitMembers.length : 1,
                                    Array.isArray(splitP1) ? splitP1.length : 1,
                                    Array.isArray(splitP2) ? splitP2.length : 1,
                                    Array.isArray(splitP3) ? splitP3.length : 1
                                );
                                const rowHeight = Math.max(6, lineCount * 4.5 + 1);

                                if ((currentY + rowHeight) > bottomThreshold) {
                                    pdf.addPage();
                                    y = drawDateHeader(dateLabel);
                                    tableState = drawRoomHeader(room, y, true);
                                    currentY = tableState.nextY;
                                }

                                pdf.setFont('times', 'normal');
                                pdf.setFontSize(8);
                                const textY = currentY + 3.5;

                                pdf.rect(colX.time, currentY, colW.time, rowHeight);
                                pdf.rect(colX.title, currentY, colW.title, rowHeight);
                                pdf.rect(colX.adviser, currentY, colW.adviser, rowHeight);
                                pdf.rect(colX.members, currentY, colW.members, rowHeight);
                                pdf.rect(colX.p1, currentY, colW.p1, rowHeight);
                                pdf.rect(colX.p2, currentY, colW.p2, rowHeight);
                                pdf.rect(colX.p3, currentY, colW.p3, rowHeight);

                                pdf.text(splitTime, colX.time + 0.8, textY);
                                pdf.text(splitTitle, colX.title + 0.8, textY);
                                pdf.text(splitAdviser, colX.adviser + 0.8, textY);
                                pdf.text(splitMembers, colX.members + 0.8, textY);
                                pdf.text(splitP1, colX.p1 + 0.8, textY);
                                pdf.text(splitP2, colX.p2 + 0.8, textY);
                                pdf.text(splitP3, colX.p3 + 0.8, textY);

                                currentY += rowHeight;
                            });

                            y = currentY + 4;
                        });
                    });

                    for (let pageIndex = 1; pageIndex <= pdf.getNumberOfPages(); pageIndex++) {
                        pdf.setPage(pageIndex);
                        drawFooter();
                    }

                    pdf.save(`defense_schedules_${document.getElementById('reportStartDate').value}_to_${document.getElementById('reportEndDate').value}.pdf`);
                };

                const generateDefenseCalendarPdf = (rows, currentUserName, currentUserCollege, start, end) => {
                    const weekBuckets = groupDefenseRowsByWeek(rows, start, end);

                    const runCalendarPdf = () => {
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const margin = 10;
                        const timeColumnWidth = 24;
                        const rowHeight = 5.5;
                        const slotMinutes = 30;
                        const startMinutes = 7 * 60;
                        const endMinutes = 21 * 60;
                        const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const dateFormatter = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                        const toMinutes = (timeStr) => {
                            if (!timeStr) return null;
                            const parts = String(timeStr).split(':').map(Number);
                            if (parts.length < 2 || parts.some(Number.isNaN)) return null;
                            return (parts[0] * 60) + parts[1];
                        };

                        const getDayIndex = (scheduleDate) => {
                            const rowDate = new Date(`${scheduleDate}T00:00:00`);
                            const jsDay = rowDate.getDay();
                            return jsDay === 0 ? -1 : jsDay - 1;
                        };

                        const ellipsizeText = (text, maxWidth, fontFamily = 'times', fontStyle = 'normal', fontSize = 7.0) => {
                            const raw = String(text || '').trim();
                            if (!raw) return '';

                            pdf.setFont(fontFamily, fontStyle);
                            pdf.setFontSize(fontSize);
                            if (pdf.getTextWidth(raw) <= maxWidth) {
                                return raw;
                            }

                            const suffix = '...';
                            let left = 0;
                            let right = raw.length;
                            let best = suffix;

                            while (left <= right) {
                                const mid = Math.floor((left + right) / 2);
                                const candidate = raw.slice(0, mid).trimEnd() + suffix;
                                if (pdf.getTextWidth(candidate) <= maxWidth) {
                                    best = candidate;
                                    left = mid + 1;
                                } else {
                                    right = mid - 1;
                                }
                            }

                            return best;
                        };

                        const wrapTitleToTwoLines = (text, maxWidth) => {
                            const lines = pdf.splitTextToSize(String(text || '').trim(), maxWidth);
                            if (!Array.isArray(lines) || lines.length <= 2) {
                                return Array.isArray(lines) ? lines : [String(text || '').trim()];
                            }

                            const firstLine = String(lines[0] || '').trim();
                            const secondSource = lines.slice(1).join(' ').replace(/\s+/g, ' ').trim();
                            const secondLine = ellipsizeText(secondSource, maxWidth, 'times', 'normal', 7.0);
                            return [firstLine, secondLine];
                        };

                        const getOverlapLayout = (count) => {
                            if (count <= 1) return { rows: 1, cols: 1 };
                            if (count === 2) return { rows: 1, cols: 2 };
                            if (count === 3) return { rows: 1, cols: 3 };
                            if (count === 4) return { rows: 2, cols: 2 };

                            const cols = Math.ceil(Math.sqrt(count));
                            const rows = Math.ceil(count / cols);
                            return { rows, cols };
                        };

                        const buildOverlapGroups = (items) => {
                            const sorted = [...items].sort((left, right) => {
                                const startDiff = left.startMinutes - right.startMinutes;
                                if (startDiff !== 0) return startDiff;
                                const endDiff = left.endMinutes - right.endMinutes;
                                if (endDiff !== 0) return endDiff;
                                return String(left.row.room || '').localeCompare(String(right.row.room || ''));
                            });

                            const groups = [];
                            let currentGroup = null;

                            sorted.forEach((item) => {
                                if (!currentGroup || item.startMinutes >= currentGroup.endMinutes) {
                                    currentGroup = {
                                        startMinutes: item.startMinutes,
                                        endMinutes: item.endMinutes,
                                        items: [item]
                                    };
                                    groups.push(currentGroup);
                                    return;
                                }

                                currentGroup.items.push(item);
                                currentGroup.startMinutes = Math.min(currentGroup.startMinutes, item.startMinutes);
                                currentGroup.endMinutes = Math.max(currentGroup.endMinutes, item.endMinutes);
                            });

                            return groups;
                        };

                        const drawScheduleTile = (box, row) => {
                            const title = row.team_name || row.thesis_title || 'Defense Schedule';
                            const room = row.room || 'Unspecified';
                            const titleWidth = Math.max(0, box.width - 2.2);
                            const roomWidth = Math.max(0, box.width - 2.2);
                            const titleLines = wrapTitleToTwoLines(title, titleWidth);
                            const roomLine = ellipsizeText(room, roomWidth, 'times', 'normal', 6.0);

                            pdf.setFillColor(255, 255, 255);
                            pdf.setDrawColor(116, 163, 118);
                            pdf.rect(box.x, box.y, box.width, box.height, 'FD');

                            const compact = box.height < 10;
                            const titleFontSize = compact ? 6.0 : 6.7;
                            const roomFontSize = compact ? 5.6 : 6.0;
                            const titleLineHeight = compact ? 2.3 : 2.6;

                            pdf.setTextColor(41, 82, 42);
                            pdf.setFont('times', 'bold');
                            pdf.setFontSize(titleFontSize);

                            const titleY = box.y + 2.5;
                            pdf.text(titleLines, box.x + 1.0, titleY);

                            pdf.setFont('times', 'normal');
                            pdf.setFontSize(roomFontSize);
                            const roomY = titleY + (titleLines.length * titleLineHeight) + 1.0;
                            if (roomY < (box.y + box.height - 0.9)) {
                                pdf.text(roomLine, box.x + 1.0, roomY);
                            }
                        };

                        const renderOverlapGroup = (dayIndex, group, gridTop, dayColumnWidth, timeSlots, slotMinutesValue, rowHeightValue) => {
                            const groupX = margin + timeColumnWidth + (dayColumnWidth * dayIndex);
                            const groupY = gridTop + ((group.startMinutes - startMinutes) / slotMinutesValue) * rowHeightValue;
                            const groupHeight = Math.max(rowHeightValue, ((group.endMinutes - group.startMinutes) / slotMinutesValue) * rowHeightValue);
                            const inset = 0.8;
                            const innerX = groupX + inset;
                            const innerY = groupY + inset;
                            const innerWidth = Math.max(0, dayColumnWidth - (inset * 2));
                            const innerHeight = Math.max(0, groupHeight - (inset * 2));

                            pdf.setFillColor(219, 242, 221);
                            pdf.setDrawColor(116, 163, 118);
                            if (innerWidth > 0 && innerHeight > 0) {
                                pdf.rect(innerX, innerY, innerWidth, innerHeight, 'FD');
                            }

                            const layout = getOverlapLayout(group.items.length);
                            const cellWidth = innerWidth / layout.cols;
                            const cellHeight = innerHeight / layout.rows;

                            group.items.forEach((item, index) => {
                                const cellRow = Math.floor(index / layout.cols);
                                const cellCol = index % layout.cols;
                                const cellX = innerX + (cellCol * cellWidth);
                                const cellY = innerY + (cellRow * cellHeight);
                                const cellBox = {
                                    x: cellX,
                                    y: cellY,
                                    width: cellWidth,
                                    height: cellHeight
                                };

                                drawScheduleTile(cellBox, item.row);
                            });
                        };

                        const drawHeader = (weekLabel) => {
                            const centerX = pageWidth / 2;
                            pdf.setTextColor(0, 0, 0);
                            pdf.setFont('times', 'bold');
                            pdf.setFontSize(14);
                            pdf.text('LYCEUM OF THE PHILIPPINES UNIVERSITY - CAVITE', centerX, 12, { align: 'center' });
                            pdf.setFont('times', 'normal');
                            pdf.setFontSize(11);
                            pdf.text(currentUserCollege || '', centerX, 18, { align: 'center' });
                            pdf.setFont('times', 'bold');
                            pdf.setFontSize(12);
                            pdf.text(weekLabel, margin, 28);
                            return 34;
                        };

                        const drawFooter = () => {
                            const footerY = pageHeight - 10;
                            pdf.setTextColor(0, 0, 0);
                            pdf.setFont('times', 'italic');
                            pdf.setFontSize(8.5);
                            pdf.text(`Date printed/exported: ${new Date().toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })}`, margin, footerY);
                            pdf.text(`Printed by: ${currentUserName} through ATLAS`, pageWidth - margin, footerY, { align: 'right' });
                        };

                        weekBuckets.forEach((weekData, weekIndex) => {
                            if (weekIndex > 0) {
                                pdf.addPage();
                            }

                            const weekStart = new Date(weekData.weekStart.getTime());
                            const weekEnd = new Date(weekData.weekEnd.getTime());
                            const weekRows = Array.isArray(weekData.rows) ? [...weekData.rows] : [];
                            const weekLabel = `${dateFormatter.format(weekStart)} - ${dateFormatter.format(weekEnd)}`;
                            const bottomLimit = pageHeight - 12;
                            const dayColumnWidth = (pageWidth - (margin * 2) - timeColumnWidth) / dayNames.length;
                            const timeSlots = [];

                            for (let minutes = startMinutes; minutes < endMinutes; minutes += slotMinutes) {
                                timeSlots.push(minutes);
                            }

                            let y = drawHeader(weekLabel);
                            pdf.setFont('times', 'normal');
                            pdf.setFontSize(8.5);

                            pdf.rect(margin, y, timeColumnWidth, 7);
                            pdf.text('Time', margin + 1, y + 4.7);

                            dayNames.forEach((dayName, index) => {
                                const x = margin + timeColumnWidth + (dayColumnWidth * index);
                                const dayDate = new Date(weekStart.getTime());
                                dayDate.setDate(dayDate.getDate() + index);
                                pdf.rect(x, y, dayColumnWidth, 7);
                                pdf.text(dayName, x + 1, y + 3.0);
                                pdf.setFont('times', 'normal');
                                pdf.setFontSize(7.2);
                                pdf.text(dateFormatter.format(dayDate), x + 1, y + 5.8);
                                pdf.setFont('times', 'normal');
                                pdf.setFontSize(8.5);
                            });

                            y += 7;

                            timeSlots.forEach((minutes, rowIndex) => {
                                const rowTop = y + (rowIndex * rowHeight);
                                pdf.rect(margin, rowTop, timeColumnWidth, rowHeight);
                                const hours24 = Math.floor(minutes / 60);
                                const mins = minutes % 60;
                                const period = hours24 >= 12 ? 'PM' : 'AM';
                                const hour12 = hours24 % 12 || 12;
                                pdf.text(`${hour12}:${String(mins).padStart(2, '0')} ${period}`, margin + 1, rowTop + 3.7);

                                dayNames.forEach((_, index) => {
                                    const x = margin + timeColumnWidth + (dayColumnWidth * index);
                                    pdf.rect(x, rowTop, dayColumnWidth, rowHeight);
                                });
                            });

                            const dayBuckets = dayNames.map(() => []);
                            weekRows.forEach((row) => {
                                const dayIndex = getDayIndex(row.schedule_date);
                                const startMinutesValue = toMinutes(row.start_time);
                                const endMinutesValue = toMinutes(row.end_time);

                                if (dayIndex < 0 || startMinutesValue === null || endMinutesValue === null || endMinutesValue <= startMinutesValue) {
                                    return;
                                }

                                dayBuckets[dayIndex].push({
                                    row,
                                    startMinutes: startMinutesValue,
                                    endMinutes: endMinutesValue
                                });
                            });

                            dayBuckets.forEach((itemsForDay, dayIndex) => {
                                const groups = buildOverlapGroups(itemsForDay);
                                groups.forEach((group) => {
                                    renderOverlapGroup(dayIndex, group, y, dayColumnWidth, timeSlots, slotMinutes, rowHeight);
                                });
                            });

                            pdf.setTextColor(0, 0, 0);
                            for (let pageIndex = 1; pageIndex <= pdf.getNumberOfPages(); pageIndex++) {
                                pdf.setPage(pageIndex);
                                drawFooter();
                            }
                        });

                        pdf.save(`defense_schedules_calendar_${document.getElementById('reportStartDate').value}_to_${document.getElementById('reportEndDate').value}.pdf`);
                    };

                    if (window.jspdf) {
                        runCalendarPdf();
                        return;
                    }

                    const s = document.createElement('script');
                    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                    s.onload = runCalendarPdf;
                    s.onerror = function() { showDefAlert('Failed to load PDF library.', 'error'); };
                    document.head.appendChild(s);
                };

                const exportDefenseSchedules = (exportType) => {
                    const range = getDefenseExportRange();
                    if (!range) return;

                    if (!['table', 'calendar'].includes(exportType)) {
                        showDefAlert('Unsupported export type.', 'warning');
                        return;
                    }

                    const currentUserName = <?php echo json_encode(trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: 'Unknown', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
                    const currentUserCollege = <?php echo json_encode($_SESSION['college'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

                    setDefenseExportBusy(true);
                    setDefenseExportMenuOpen(false);

                    fetch('includes/get_defense_report.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ start_date: range.start, end_date: range.end })
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (!res || !res.success) {
                                showDefAlert(res && res.message ? res.message : 'No schedules found for the selected range.', 'warning');
                                return;
                            }

                            const rows = Array.isArray(res.data) ? res.data : [];
                            if (!rows.length) {
                                showDefAlert('No schedules found for the selected range.', 'warning');
                                return;
                            }

                            ensureJsPdf(() => {
                                if (exportType === 'calendar') {
                                    generateDefenseCalendarPdf(rows, currentUserName, currentUserCollege, range.start, range.end);
                                    return;
                                }

                                const { jsPDF } = window.jspdf;
                                const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                                generateDefenseTablePdf(pdf, rows, currentUserName, currentUserCollege);
                            });
                        })
                        .catch(err => {
                            console.error('Export error', err);
                            showDefAlert('Failed to generate report: ' + (err.message || err), 'error');
                        })
                        .finally(() => {
                            setDefenseExportBusy(false);
                        });
                };

                if (defenseExportButton) {
                    defenseExportButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        setDefenseExportMenuOpen(!defenseExportMenu?.classList.contains('is-open'));
                    });
                }

                defenseExportOptions.forEach((button) => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        exportDefenseSchedules(this.dataset.exportType || 'table');
                    });
                });

                document.addEventListener('click', function(e) {
                    if (defenseExportMenu && !defenseExportMenu.contains(e.target)) {
                        setDefenseExportMenuOpen(false);
                    }
                });

                if (defenseExportMenu) {
                    defenseExportMenu.addEventListener('mouseleave', function() {
                        setDefenseExportMenuOpen(false);
                    });
                }

                updateDefenseExportLabel();

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
                        slotMaxTime: '20:30:00',
                        allDaySlot: false,
                        height: 'auto',
                        editable: true,
                        eventDurationEditable: true,
                        events: events,
eventContent: function(arg) {
                            const props = arg.event.extendedProps;
                            // Build enhanced display with team, room, defense type, and time
                            const teamName = arg.event.title || 'Unknown Team';
                            const room = props.room || '';
                            const defenseType = props.defense_type || '';
                            const status = props.status || 'pending_chair';
                            const startTime = props.start_time || '';
                            const endTime = props.end_time || '';
                            
                            // Format defense type for display
                            const typeLabel = defenseType ? defenseType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Defense';
                            
                            // Get status color class
                            const statusClass = `status-${status}`;
                            
                            // Format time range
                            let timeDisplay = '';
                            if (startTime) {
                                const start = new Date('1970-01-01T' + startTime);
                                const end = new Date('1970-01-01T' + (endTime || startTime));
                                const formatTime = (d) => {
                                    const h = d.getHours();
                                    const m = d.getMinutes();
                                    const ampm = h >= 12 ? 'PM' : 'AM';
                                    const h12 = h % 12 || 12;
                                    return `${h12}:${m.toString().padStart(2, '0')} ${ampm}`;
                                };
                                timeDisplay = `${formatTime(start)}-${formatTime(end)}`;
                            }
                            
                            return {
                                html: `<div class="event-content-wrapper ${statusClass}">
                                    <div class="event-header">
                                        <span class="event-team">${teamName}</span>
                                        <span class="event-type">${typeLabel}</span>
                                    </div>
                                    <div class="event-body">
                                        <span class="event-room">📍 ${room}</span>
                                        <span class="event-time">🕐 ${timeDisplay}</span>
                                    </div>
                                </div>`
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
                let previewGenerationMeta = null;
                let previewClassScheduleData = [];
                let previewUnresolvedTeamIds = [];

                const toMinutes = (timeStr) => {
                    if (!timeStr) return null;
                    const parts = String(timeStr).split(':').map(Number);
                    if (parts.length < 2 || parts.some(Number.isNaN)) return null;
                    return (parts[0] * 60) + parts[1];
                };

                const timeRangesOverlap = (startA, endA, startB, endB) => {
                    return startA < endB && endA > startB;
                };

                /** user_schedules.day_of_week is stored as weekday names (Monday…Saturday); FullCalendar expects 0–6 (Sun–Sat). */
                function userScheduleDayToJsDay(dow) {
                    if (dow === null || dow === undefined || dow === '') return null;
                    if (typeof dow === 'number' && Number.isInteger(dow) && dow >= 0 && dow <= 6) return dow;
                    const n = Number(dow);
                    if (!Number.isNaN(n) && n >= 0 && n <= 6) return n;
                    const map = {
                        sunday: 0, monday: 1, tuesday: 2, wednesday: 3,
                        thursday: 4, friday: 5, saturday: 6
                    };
                    const key = String(dow).trim().toLowerCase();
                    return map[key] !== undefined ? map[key] : null;
                }

                /** Class blocks for students' program+section on the preview teams only (not all user_schedules). */
                function loadPreviewTeamSectionSchedules(schedules) {
                    const teamIds = [...new Set((schedules || [])
                        .map(s => s.team_id)
                        .filter(id => id != null && String(id).trim() !== ''))];
                    const numericIds = teamIds.map(id => parseInt(id, 10)).filter(n => !Number.isNaN(n) && n > 0);
                    if (numericIds.length === 0) {
                        return Promise.resolve([]);
                    }
                    return fetch('../dashboard/includes/get_preview_overlay_schedules.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ team_ids: numericIds })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error && !Array.isArray(data.data)) {
                                return [];
                            }
                            return Array.isArray(data.data) ? data.data : [];
                        })
                        .catch((err) => {
                            console.warn('Class overlay fetch failed:', err);
                            return [];
                        });
                }

                /**
                 * Section class overlay: always show the full weekly pattern for every class row.
                 * Times come only from user_schedules (API) — never from defense generator timeSlots or selected defense days.
                 */
                function formatClassTimeFromDb(raw) {
                    if (raw === null || raw === undefined || raw === '') return '';
                    const s = String(raw).trim();
                    const m = s.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?/);
                    if (!m) return '';
                    const h = String(Math.min(23, parseInt(m[1], 10))).padStart(2, '0');
                    const min = String(Math.min(59, parseInt(m[2], 10))).padStart(2, '0');
                    const sec = m[3] !== undefined ? String(Math.min(59, parseInt(m[3], 10))).padStart(2, '0') : '00';
                    return `${h}:${min}:${sec}`;
                }

                /** Matches FullCalendar's firstDay — weekStart is the calendar column start (e.g. Monday when firstDay=1). */
                function startOfDisplayedWeek(referenceDate, firstDayFc) {
                    const rd = referenceDate instanceof Date ? new Date(referenceDate.getTime()) : new Date(referenceDate);
                    rd.setHours(12, 0, 0, 0);
                    const fd = firstDayFc !== undefined ? firstDayFc : 1;
                    const dow = rd.getDay();
                    const delta = (dow - fd + 7) % 7;
                    rd.setDate(rd.getDate() - delta);
                    rd.setHours(0, 0, 0, 0);
                    return rd;
                }

                function isoDateLocal(d) {
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${dd}`;
                }

                function formatPreviewLocalYmd(d) {
                    return isoDateLocal(d);
                }

                function formatPreviewLocalHms(d) {
                    const h = String(d.getHours()).padStart(2, '0');
                    const m = String(d.getMinutes()).padStart(2, '0');
                    const s = String(d.getSeconds()).padStart(2, '0');
                    return `${h}:${m}:${s}`;
                }

                /** Defense preview: start times may only sit on :00 or :30; keep end within 8:30 PM cap. */
                function snapPreviewMomentToHalfHour(date) {
                    const d = new Date(date.getTime());
                    const durH = parseFloat(document.getElementById('timeDuration')?.value) || 1;
                    const durMin = Math.round(durH * 60);
                    const capEndMin = 20 * 60 + 30;
                    let totalMinutes = d.getHours() * 60 + d.getMinutes() + d.getSeconds() / 60;
                    let snapped = Math.round(totalMinutes / 30) * 30;
                    let endMin = snapped + durMin;
                    if (endMin > capEndMin) {
                        snapped = Math.max(7 * 60, capEndMin - durMin);
                        snapped = Math.round(snapped / 30) * 30;
                    }
                    const hh = Math.floor(snapped / 60);
                    const mm = snapped % 60;
                    d.setHours(hh, mm, 0, 0);
                    return d;
                }

                function snapHmStringToHalfHour(hm) {
                    const raw = String(hm || '').trim();
                    const m = /^(\d{1,2}):(\d{2})/.exec(raw);
                    if (!m) return raw;
                    let h = parseInt(m[1], 10);
                    let mi = parseInt(m[2], 10);
                    if (Number.isNaN(h)) h = 0;
                    if (Number.isNaN(mi)) mi = 0;
                    mi = mi < 30 ? 0 : 30;
                    h = Math.min(23, Math.max(0, h));

                    const durH = parseFloat(document.getElementById('timeDuration')?.value) || 1;
                    const durMin = Math.round(durH * 60);
                    const capEndMin = 20 * 60 + 30;
                    let startMin = h * 60 + mi;
                    if (startMin + durMin > capEndMin) {
                        startMin = Math.max(7 * 60, capEndMin - durMin);
                        startMin = Math.round(startMin / 30) * 30;
                        h = Math.floor(startMin / 60);
                        mi = startMin % 60;
                    }
                    return `${String(h).padStart(2, '0')}:${String(mi).padStart(2, '0')}`;
                }

                /** Map JS weekday (0–6 Sun–Sat) to the concrete date inside the visible FC week. */
                function dateForFcWeekdayInWeek(fcDow, weekStartDate, fcFirstDay) {
                    const start = new Date(weekStartDate.getTime());
                    start.setHours(12, 0, 0, 0);
                    const fd = fcFirstDay !== undefined ? fcFirstDay : 1;
                    const delta = (fcDow - fd + 7) % 7;
                    start.setDate(start.getDate() + delta);
                    return isoDateLocal(start);
                }

                /** Explicit start/end per date so timeGridWeek always paints Mon–Sat overlays (avoid FC daysOfWeek recurrence quirks). */
                function buildClassOverlayEvents(classSchedules, weekStartDate, fcFirstDay) {
                    const fd = fcFirstDay !== undefined ? fcFirstDay : 1;
                    const events = [];
                    (classSchedules || []).forEach((schedule, index) => {
                        const fcDay = userScheduleDayToJsDay(schedule.day_of_week);
                        if (fcDay === null) return;

                        const startT = formatClassTimeFromDb(schedule.start_time);
                        const endT = formatClassTimeFromDb(schedule.end_time);
                        if (!startT || !endT) return;

                        const dateStr = dateForFcWeekdayInWeek(fcDay, weekStartDate, fd);

                        events.push({
                            id: `class_overlay_${schedule.id != null ? schedule.id : 'row'}_${index}`,
                            title: schedule.class_name || 'Class block',
                            start: `${dateStr}T${startT}`,
                            end: `${dateStr}T${endT}`,
                            display: 'block',
                            backgroundColor: 'rgba(253, 230, 138, 0.92)',
                            borderColor: '#d97706',
                            textColor: '#451a03',
                            classNames: ['preview-overlay-class'],
                            editable: false,
                            durationEditable: false,
                            overlap: true,
                            extendedProps: {
                                overlayType: 'class',
                                section: schedule.section || '',
                                room: schedule.room || '',
                                facultyName: [schedule.first_name, schedule.last_name].filter(Boolean).join(' ').trim(),
                                class_name: schedule.class_name || ''
                            }
                        });
                    });
                    return events;
                }

                function findPreviewConflicts(generatedSchedules, classSchedules) {
                    const conflicts = [];

                    generatedSchedules.forEach((schedule, index) => {
                        const scheduleDate = new Date(`${schedule.schedule_date}T00:00:00`);
                        const generatedDow = scheduleDate.getDay();
                        const generatedStart = toMinutes(schedule.start_time);
                        const generatedEnd = toMinutes(schedule.end_time);

                        classSchedules.forEach(classSchedule => {
                            const classAllowsOverlap = Number(classSchedule.allow_overlap || 0) === 1 || Number(classSchedule.is_research_class || 0) === 1;
                            if (classAllowsOverlap) {
                                return;
                            }

                            const appliesList = Array.isArray(classSchedule.applies_to_team_ids)
                                ? classSchedule.applies_to_team_ids.map(id => Number(id)).filter(Number.isFinite)
                                : [];
                            const applies = classSchedule.applies_to_team_id;
                            if (appliesList.length > 0) {
                                if (!appliesList.includes(Number(schedule.team_id))) {
                                    return;
                                }
                            } else if (applies !== null && applies !== undefined && String(applies).trim() !== '') {
                                if (Number(applies) !== Number(schedule.team_id)) {
                                    return;
                                }
                            }
                            const classDow = userScheduleDayToJsDay(classSchedule.day_of_week);
                            if (classDow === null || classDow !== Number(generatedDow)) {
                                return;
                            }

                            const classStart = toMinutes(classSchedule.start_time);
                            const classEnd = toMinutes(classSchedule.end_time);
                            if (classStart === null || classEnd === null || generatedStart === null || generatedEnd === null) {
                                return;
                            }

                            if (!timeRangesOverlap(generatedStart, generatedEnd, classStart, classEnd)) {
                                return;
                            }

                            conflicts.push({
                                previewIndex: index,
                                team_name: schedule.team_name || 'Unknown',
                                class_name: classSchedule.class_name || 'Class block',
                                section: classSchedule.section || '',
                                room: schedule.room || '',
                                class_room: classSchedule.room || '',
                                day_of_week: generatedDow,
                                start_time: schedule.start_time,
                                end_time: schedule.end_time,
                                class_start: classSchedule.start_time,
                                class_end: classSchedule.end_time,
                                faculty_name: [classSchedule.first_name, classSchedule.last_name].filter(Boolean).join(' ').trim()
                            });
                        });
                    });

                    return conflicts;
                }

                function getPreviewPanelistIds(schedule) {
                    const ids = [
                        Number(schedule?.panelist_id || 0),
                        Number(schedule?.panelist_id2 || 0),
                        Number(schedule?.panelist_id3 || 0)
                    ].filter(n => Number.isFinite(n) && n > 0);
                    return [...new Set(ids)];
                }

                function normalizeRoomForConflict(room) {
                    return String(room || '').trim().toLowerCase();
                }

                function findPreviewInternalConflicts(generatedSchedules) {
                    const conflicts = [];
                    const total = Array.isArray(generatedSchedules) ? generatedSchedules.length : 0;

                    for (let i = 0; i < total; i++) {
                        const a = generatedSchedules[i] || {};
                        const aDate = String(a.schedule_date || '').trim();
                        const aStart = toMinutes(a.start_time);
                        const aEnd = toMinutes(a.end_time);
                        const aRoomNorm = normalizeRoomForConflict(a.room);
                        const aPanelists = getPreviewPanelistIds(a);

                        if (!aDate || aStart === null || aEnd === null || aStart >= aEnd) continue;

                        for (let j = i + 1; j < total; j++) {
                            const b = generatedSchedules[j] || {};
                            const bDate = String(b.schedule_date || '').trim();
                            if (aDate !== bDate) continue;

                            const bStart = toMinutes(b.start_time);
                            const bEnd = toMinutes(b.end_time);
                            if (bStart === null || bEnd === null || bStart >= bEnd) continue;

                            if (!timeRangesOverlap(aStart, aEnd, bStart, bEnd)) continue;

                            const bRoomNorm = normalizeRoomForConflict(b.room);
                            if (aRoomNorm && bRoomNorm && aRoomNorm === bRoomNorm) {
                                conflicts.push({
                                    type: 'room',
                                    previewIndex: i,
                                    otherIndex: j,
                                    team_name: a.team_name || 'Unknown',
                                    other_team_name: b.team_name || 'Unknown',
                                    room: a.room || '',
                                    day_of_week: new Date(`${aDate}T00:00:00`).getDay(),
                                    start_time: a.start_time,
                                    end_time: a.end_time
                                });
                                conflicts.push({
                                    type: 'room',
                                    previewIndex: j,
                                    otherIndex: i,
                                    team_name: b.team_name || 'Unknown',
                                    other_team_name: a.team_name || 'Unknown',
                                    room: b.room || '',
                                    day_of_week: new Date(`${bDate}T00:00:00`).getDay(),
                                    start_time: b.start_time,
                                    end_time: b.end_time
                                });
                            }

                            const bPanelists = getPreviewPanelistIds(b);
                            const sharedPanelists = aPanelists.filter(id => bPanelists.includes(id));
                            if (sharedPanelists.length > 0) {
                                conflicts.push({
                                    type: 'panelist',
                                    previewIndex: i,
                                    otherIndex: j,
                                    team_name: a.team_name || 'Unknown',
                                    other_team_name: b.team_name || 'Unknown',
                                    panelist_ids: sharedPanelists,
                                    day_of_week: new Date(`${aDate}T00:00:00`).getDay(),
                                    start_time: a.start_time,
                                    end_time: a.end_time
                                });
                                conflicts.push({
                                    type: 'panelist',
                                    previewIndex: j,
                                    otherIndex: i,
                                    team_name: b.team_name || 'Unknown',
                                    other_team_name: a.team_name || 'Unknown',
                                    panelist_ids: sharedPanelists,
                                    day_of_week: new Date(`${bDate}T00:00:00`).getDay(),
                                    start_time: b.start_time,
                                    end_time: b.end_time
                                });
                            }
                        }
                    }

                    return conflicts;
                }

                function setupPreviewVariantsUI(variants) {
                    const wrap = document.getElementById('previewVariantWrap');
                    const sel = document.getElementById('previewVariantSelect');
                    if (!wrap || !sel) return;
                    if (!variants || variants.length <= 1) {
                        wrap.classList.add('d-none');
                        sel.innerHTML = '';
                        sel.onchange = null;
                        return;
                    }
                    wrap.classList.remove('d-none');
                    sel.innerHTML = variants.map((v, i) => {
                        const owCount = Array.isArray(v.overlap_warnings) ? v.overlap_warnings.length : 0;
                        const lbl = String(v.label || ('Option ' + (v.variant_id || (i + 1)))).replace(/</g, '&lt;');
                        return `<option value="${i}">${lbl} — ${owCount} post-GA repair move(s)</option>`;
                    }).join('');
                    sel.onchange = function() {
                        const idx = parseInt(sel.value, 10);
                        const chosen = variants[idx];
                        if (!chosen || !Array.isArray(chosen.schedules)) return;
                        showPreviewCalendar(chosen.schedules, {
                            validationMode: window.__defensePreviewSharedMeta.validationMode,
                            validationSummary: window.__defensePreviewSharedMeta.validationSummary,
                            validationCounts: window.__defensePreviewSharedMeta.validationCounts,
                            validationIssues: window.__defensePreviewSharedMeta.validationIssues,
                            overlapWarnings: chosen.overlap_warnings || [],
                            overlapFixes: chosen.overlap_fixes,
                            remaining_conflicts: chosen.remaining_conflicts
                        }, { openModal: false, reuseClassOverlay: true });
                        sel.value = String(idx);
                    };
                }

                function renderPreviewDiagnostics(meta, conflicts) {
                    const target = document.getElementById('previewDiagnostics');
                    if (!target) return;

                    const validationCounts = meta?.validationCounts || { teams: 0, room: 0, panelist: 0, member: 0, invalid: 0 };
                    const unresolvedIssues = Array.isArray(meta?.validationIssues) ? meta.validationIssues : [];
                    const allConflicts = Array.isArray(conflicts) ? conflicts : [];
                    const classConflicts = allConflicts.filter(c => !c.type || c.type === 'class');
                    const internalConflicts = allConflicts.filter(c => c.type === 'room' || c.type === 'panelist');
                    const conflictCount = allConflicts.length;
                    const overlapPostGa = Array.isArray(meta?.overlapWarnings) ? meta.overlapWarnings : [];

                    target.innerHTML = `
                        <div class="preview-diagnostics-card p-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">Preview diagnostics</div>
                                    <div class="text-muted small">Overlay = unique class blocks for the selected teams. Post‑GA list = server repair log, not the overlay.</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="preview-overlay-chip">Teams with issues: ${validationCounts.teams || 0}</span>
                                    <span class="preview-overlay-chip">Room: ${validationCounts.room || 0}</span>
                                    <span class="preview-overlay-chip">Panelist: ${validationCounts.panelist || 0}</span>
                                    <span class="preview-overlay-chip">Member: ${validationCounts.member || 0}</span>
                                    <span class="preview-overlay-chip">Total conflicts: ${conflictCount}</span>
                                </div>
                            </div>
                            ${unresolvedIssues.length ? `
                                <div class="small fw-semibold text-danger mb-1">Unresolved generation issues</div>
                                <ul class="preview-diagnostics-list text-danger small">
                                    ${unresolvedIssues.slice(0, 5).map(issue => `<li>${issue}</li>`).join('')}
                                </ul>
                            ` : '<div class="small text-success">No unresolved generation issues reported by the validator.</div>'}
                            ${classConflicts.length ? `
                                <div class="small fw-semibold text-danger mt-3 mb-1">Overlay: section class vs defense (overlap)</div>
                                <ul class="preview-diagnostics-list small">
                                    ${classConflicts.slice(0, 5).map(conflict => `<li><strong>${conflict.team_name}</strong> vs <strong>${conflict.class_name}</strong> on ${formatTime(conflict.start_time)}-${formatTime(conflict.end_time)} (team room ${conflict.room || 'N/A'}, class venue ${conflict.class_room || 'N/A'})</li>`).join('')}
                                </ul>
                            ` : '<div class="small text-muted mt-3">No section-class overlap in this layout.</div>'}
                            ${internalConflicts.length ? `
                                <div class="small fw-semibold text-danger mt-3 mb-1">Internal schedule overlaps (must be fixed)</div>
                                <ul class="preview-diagnostics-list small">
                                    ${internalConflicts.slice(0, 8).map(conflict => {
                                        if (conflict.type === 'room') {
                                            return `<li><strong>${conflict.team_name}</strong> overlaps with <strong>${conflict.other_team_name}</strong> in room <strong>${conflict.room || 'N/A'}</strong> at ${formatTime(conflict.start_time)}-${formatTime(conflict.end_time)}</li>`;
                                        }
                                        return `<li><strong>${conflict.team_name}</strong> shares panelist(s) <strong>${(conflict.panelist_ids || []).join(', ') || 'N/A'}</strong> with <strong>${conflict.other_team_name}</strong> at ${formatTime(conflict.start_time)}-${formatTime(conflict.end_time)}</li>`;
                                    }).join('')}
                                </ul>
                            ` : '<div class="small text-muted mt-3">No room/panelist overlaps between generated defenses.</div>'}
                            ${overlapPostGa.length ? `
                                <div class="small fw-semibold mt-3 mb-1" style="color:#b45309;">Post‑GA overlap / repair notes (check before saving)</div>
                                <ul class="preview-diagnostics-list small">
                                    ${overlapPostGa.slice(0, 8).map(msg => `<li>${String(msg).replace(/</g, '&lt;')}</li>`).join('')}
                                </ul>
                            ` : ''}
                            ${typeof meta.remaining_conflicts === 'number' && meta.remaining_conflicts > 0 ? `
                                <div class="small text-danger mt-2"><strong>Unfixed internal overlaps:</strong> ${meta.remaining_conflicts} — widen rooms/times or re-run generator.</div>
                            ` : ''}
                        </div>
                    `;
                }

                function previewEventTooltip(scheduleLike, clash) {
                    const lines = [
                        scheduleLike.team_name || 'Team',
                        `Room ${scheduleLike.room || '—'}`,
                        `Panel ${[scheduleLike.panelist1_name, scheduleLike.panelist2_name, scheduleLike.panelist3_name].filter(Boolean).join(', ') || '—'}`
                    ];
                    if (clash && clash.class_name) {
                        lines.push(`Overlaps section class: «${clash.class_name}»`);
                        lines.push(`${formatTime(clash.class_start)}–${formatTime(clash.class_end)}${clash.faculty_name ? '; faculty: ' + clash.faculty_name : ''}`);
                    }
                    return lines.join('\n');
                }

                function showPreviewCalendar(schedules, meta = {}, opts = {}) {
                    const openModal = opts.openModal !== false;
                    const reuseClassOverlay = opts.reuseClassOverlay === true;

                    previewScheduleData = Array.isArray(schedules) ? schedules : [];

                    if (!reuseClassOverlay) {
                        window.__defensePreviewSharedMeta = {
                            validationMode: meta.validationMode,
                            validationSummary: meta.validationSummary,
                            validationCounts: meta.validationCounts,
                            validationIssues: meta.validationIssues
                        };
                        window.__defensePreviewVariants = meta.schedule_variants || [];
                    }

                    previewGenerationMeta = Object.assign({}, window.__defensePreviewSharedMeta || {}, {
                        overlapWarnings: meta.overlapWarnings || [],
                        overlapFixes: meta.overlapFixes,
                        remaining_conflicts: meta.remaining_conflicts
                    });
                    previewUnresolvedTeamIds = Array.isArray(meta.unresolved_team_ids) ? meta.unresolved_team_ids.map(Number).filter(Number.isFinite) : [];

                    const previewModalEl = document.getElementById('schedulePreviewModal');
                    const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
                    window.__defensePreviewModal = previewModal;
                    if (openModal) {
                        previewModal.show();
                        setupPreviewVariantsUI(window.__defensePreviewVariants || []);
                        const selVar = document.getElementById('previewVariantSelect');
                        if (selVar && selVar.options.length) {
                            selVar.value = '0';
                        }
                    }

                    document.getElementById('previewScheduleCount').textContent = `${previewScheduleData.length} schedule(s) generated`;
                    const unresolvedBtn = document.getElementById('generateUnresolvedPreview');
                    if (unresolvedBtn) {
                        if (previewUnresolvedTeamIds.length > 0) {
                            unresolvedBtn.classList.remove('d-none');
                            unresolvedBtn.textContent = `Generate Unresolved Only (${previewUnresolvedTeamIds.length})`;
                        } else {
                            unresolvedBtn.classList.add('d-none');
                        }
                    }

                    const overlayPromise = reuseClassOverlay
                        ? Promise.resolve(previewClassScheduleData)
                        : loadPreviewTeamSectionSchedules(previewScheduleData);

                    overlayPromise.then(classSchedules => {
                        previewClassScheduleData = classSchedules;
                        const classConflictDetails = findPreviewConflicts(previewScheduleData, classSchedules);
                        const internalConflictDetails = findPreviewInternalConflicts(previewScheduleData);
                        const conflictDetails = [...classConflictDetails, ...internalConflictDetails];

                        let postGaWarnIdx = {};
                        try {
                            (previewGenerationMeta.overlapWarnings || []).forEach(raw => {
                                const m = String(raw).match(/Team\s+(\d+)/);
                                if (m) postGaWarnIdx[m[1]] = true;
                            });
                        } catch (e) { /* ignore parse */ }

                        const generatedEvents = previewScheduleData.map((s, idx) => {
                            const matchingConflict = conflictDetails.find(conflict => conflict.previewIndex === idx);
                            const pgWarn = s.team_id && postGaWarnIdx[String(s.team_id)];

                            return {
                                id: 'preview_' + idx,
                                title: s.team_name || 'Unknown',
                                start: s.schedule_date + 'T' + s.start_time,
                                end: s.schedule_date + 'T' + s.end_time,
                                editable: true,
                                extendedProps: {
                                    ...s,
                                    previewIndex: idx,
                                    panelist1: s.panelist1_name || '',
                                    panelist2: s.panelist2_name || '',
                                    panelist3: s.panelist3_name || '',
                                    status: 'preview',
                                    previewConflict: matchingConflict || null,
                                    previewPostGaFlag: Boolean(pgWarn),
                                    tooltip: previewEventTooltip(s, matchingConflict)
                                }
                            };
                        });

                        let initialDate = new Date();
                        if (generatedEvents.length > 0) {
                            const dates = generatedEvents.map(e => new Date(e.start)).sort((a, b) => a - b);
                            initialDate = dates[0];
                        }

                        const PREVIEW_FIRST_DAY = 1; // Match Mon-first week strip (hidden Sundays)
                        const CLASS_OVERLAY_SOURCE_ID = 'preview-class-overlay';
                        const weekStartForOverlay = startOfDisplayedWeek(initialDate, PREVIEW_FIRST_DAY);
                        const classOverlayEvents = buildClassOverlayEvents(classSchedules, weekStartForOverlay, PREVIEW_FIRST_DAY);

                        renderPreviewDiagnostics(previewGenerationMeta, conflictDetails);

                        const initCal = function() {
                            if (previewCalendarInstance) previewCalendarInstance.destroy();
                            const calEl = document.getElementById('previewCalendar');
                            previewCalendarInstance = new FullCalendar.Calendar(calEl, {
                                initialView: 'timeGridWeek',
                                initialDate: initialDate,
                                firstDay: 1,
                                hiddenDays: [0],
                                headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'timeGridWeek,timeGridDay'
                                },
                                /* Preview window 7:00 AM – includes finishes through 8:30 PM (slotMax exclusive). */
                                slotMinTime: '07:00:00',
                                slotMaxTime: '21:00:00',
                                slotDuration: '00:30:00',
                                snapDuration: '00:30:00',
                                allDaySlot: false,
                                height: 'auto',
                                editable: true,
                                eventDurationEditable: false,
                                eventOverlap: true,
                                eventDidMount: function(arg) {
                                    const tp = arg.event.extendedProps && arg.event.extendedProps.tooltip;
                                    if (tp) {
                                        arg.el.setAttribute('title', tp);
                                    }
                                },
                                eventSources: [
                                    {
                                        id: CLASS_OVERLAY_SOURCE_ID,
                                        events: classOverlayEvents
                                    },
                                    {
                                        events: generatedEvents
                                    }
                                ],
                                datesSet: function(info) {
                                    // Rebuild class overlay for any navigated week/day.
                                    const overlayWeekStart = startOfDisplayedWeek(info.start, PREVIEW_FIRST_DAY);
                                    const overlayEvents = buildClassOverlayEvents(classSchedules, overlayWeekStart, PREVIEW_FIRST_DAY);
                                    const existingOverlaySource = previewCalendarInstance.getEventSourceById(CLASS_OVERLAY_SOURCE_ID);
                                    if (existingOverlaySource) {
                                        existingOverlaySource.remove();
                                    }
                                    previewCalendarInstance.addEventSource({
                                        id: CLASS_OVERLAY_SOURCE_ID,
                                        events: overlayEvents
                                    });
                                },
                                eventContent: function(arg) {
                                    const props = arg.event.extendedProps;
                                    if (props.overlayType === 'class') {
                                        return {
                                            html: `<div class="event-team">${arg.event.title}</div><div class="event-room">${props.facultyName || props.section || ''}</div>`
                                        };
                                    }
                                    return {
                                        html: `<div class="event-team">${arg.event.title}</div>
                                               <div class="event-room">${props.room || ''}${props.previewConflict ? ' · Conflict' : ''}</div>`
                                    };
                                },
                                eventClassNames: function(arg) {
                                    const props = arg.event.extendedProps;
                                    if (props.overlayType === 'class') {
                                        return ['preview-overlay-class'];
                                    }
                                    const out = ['status-preview'];
                                    if (props.previewConflict) {
                                        out.push('preview-conflict');
                                    } else {
                                        out.push('preview-slot-clear');
                                    }
                                    if (props.previewPostGaFlag && !props.previewConflict) {
                                        out.push('preview-warning');
                                    }
                                    return out;
                                },
                                eventClick: function(info) {
                                    if (info.event.extendedProps.overlayType === 'class') {
                                        info.jsEvent.preventDefault();
                                        return;
                                    }
                                    openEventEditModal(info.event, 'preview');
                                },
                                eventDrop: function(info) {
                                    if (info.event.extendedProps && info.event.extendedProps.overlayType === 'class') {
                                        info.revert();
                                        return;
                                    }
                                    updatePreviewDataFromEvent(info.event);
                                },
                                eventResize: function(info) {
                                    if (info.event.extendedProps && info.event.extendedProps.overlayType === 'class') {
                                        info.revert();
                                        return;
                                    }
                                    updatePreviewDataFromEvent(info.event);
                                }
                            });
                            previewCalendarInstance.render();
                            previewModalEl.removeEventListener('shown.bs.modal', initCal);
                        };

                        if (!openModal && previewModalEl.classList.contains('show')) {
                            initCal();
                        } else {
                            previewModalEl.addEventListener('shown.bs.modal', initCal, { once: true });
                        }
                    });
                }

                window.showDefensePreviewCalendar = showPreviewCalendar;

                function updatePreviewDataFromEvent(event) {
                    const idx = event.extendedProps.previewIndex;
                    if (idx !== undefined && previewScheduleData[idx]) {
                        const durH = parseFloat(document.getElementById('timeDuration')?.value) || 1;
                        const snappedStart = snapPreviewMomentToHalfHour(event.start);
                        const snappedEnd = new Date(snappedStart.getTime() + durH * 3600000);
                        event.setStart(snappedStart);
                        event.setEnd(snappedEnd);
                        previewScheduleData[idx].schedule_date = formatPreviewLocalYmd(snappedStart);
                        previewScheduleData[idx].start_time = formatPreviewLocalHms(snappedStart);
                        previewScheduleData[idx].end_time = formatPreviewLocalHms(snappedEnd);
                    }

                    refreshPreviewConflictDiagnostics();
                }

                function refreshPreviewConflictDiagnostics() {
                    if (!previewScheduleData.length) {
                        return;
                    }

                    const conflicts = [
                        ...findPreviewConflicts(previewScheduleData, previewClassScheduleData),
                        ...findPreviewInternalConflicts(previewScheduleData)
                    ];
                    renderPreviewDiagnostics(previewGenerationMeta, conflicts);

                    if (previewCalendarInstance) {
                        previewCalendarInstance.getEvents().forEach(event => {
                            const props = event.extendedProps || {};
                            if (props.overlayType === 'class') {
                                return;
                            }

                            const conflict = conflicts.find(item => String(item.previewIndex) === String(props.previewIndex));
                            event.setExtendedProp('previewConflict', conflict || null);
                        });
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
                    document.getElementById('editDate').value = formatPreviewLocalYmd(event.start);
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
                    let newStart = document.getElementById('editStartTime').value;
                    let newEnd = document.getElementById('editEndTime').value;
                    if (currentEditContext === 'preview') {
                        newStart = snapHmStringToHalfHour(newStart);
                        const durH = parseFloat(document.getElementById('timeDuration')?.value) || 1;
                        const p = String(newStart).split(':');
                        const sh = parseInt(p[0], 10) || 0;
                        const sm = parseInt(p[1], 10) || 0;
                        const s = new Date(2000, 0, 1, sh, sm, 0);
                        const e = new Date(s.getTime() + durH * 3600000);
                        newEnd = String(e.getHours()).padStart(2, '0') + ':' + String(e.getMinutes()).padStart(2, '0');
                    }
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
                            refreshPreviewConflictDiagnostics();
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
                    const allConflicts = [
                        ...findPreviewConflicts(previewScheduleData, previewClassScheduleData),
                        ...findPreviewInternalConflicts(previewScheduleData)
                    ];
                    if (Array.isArray(allConflicts) && allConflicts.length > 0) {
                        showDefAlert('Resolve class/room/panelist overlaps before saving preview schedules.', 'error');
                        return;
                    }
                    if (Array.isArray(previewUnresolvedTeamIds) && previewUnresolvedTeamIds.length > 0) {
                        showDefAlert('Some teams are unresolved. Use "Generate Unresolved Only" before saving.', 'warning');
                        return;
                    }
                    const btn = this;
                    btn.disabled = true;
                    btn.textContent = 'Saving...';

                    fetch('../dashboard/includes/save_preview_schedule.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            schedules: previewScheduleData
                        })
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

                document.getElementById('regeneratePreviewSchedule').addEventListener('click', function() {
                    const previewModalEl = document.getElementById('schedulePreviewModal');
                    const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
                    if (previewModal) {
                        previewModal.hide();
                    }

                    setTimeout(() => {
                        const generateButton = document.getElementById('generateSchedule');
                        if (generateButton) {
                            generateButton.click();
                        }
                    }, 200);
                });

                const unresolvedBtn = document.getElementById('generateUnresolvedPreview');
                if (unresolvedBtn) {
                    unresolvedBtn.addEventListener('click', function() {
                        if (!Array.isArray(previewUnresolvedTeamIds) || previewUnresolvedTeamIds.length === 0) {
                            showDefAlert('No unresolved teams to regenerate.', 'warning');
                            return;
                        }
                        window.__defensePendingUnresolvedTeamIds = previewUnresolvedTeamIds.slice();
                        const previewModalEl = document.getElementById('schedulePreviewModal');
                        const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
                        if (previewModal) {
                            previewModal.hide();
                        }
                        setTimeout(() => {
                            const generateButton = document.getElementById('generateSchedule');
                            if (generateButton) {
                                generateButton.click();
                            }
                        }, 200);
                    });
                }

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
                                const teamName = matchedSchedule.team_name || 'Unknown group';
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

            });
        </script>
    </div>
</div>