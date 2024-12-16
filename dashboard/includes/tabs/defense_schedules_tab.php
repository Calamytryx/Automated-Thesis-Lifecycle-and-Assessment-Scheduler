<!-- Defense Schedules Tab -->
<div class="tab-pane fade" id="defense-schedules" role="tabpanel"
    aria-labelledby="defense-schedules-tab">
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
                                <input type="number" class="form-control" id="timeDuration" name="timeDuration" min="1" max="24" value="1" required>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const timeDurationInput = document.getElementById('timeDuration');
                                    timeDurationInput.addEventListener('input', function() {
                                        if (timeDurationInput.value < 1) {
                                            timeDurationInput.value = 1;
                                        } else if (timeDurationInput.value > 24) {
                                            timeDurationInput.value = 24;
                                        }
                                    });
                                });
                            </script>
                            <div class="mb-3">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" required>
                            </div>
                            <div class="mb-3">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime" required>
                            </div>
                            <div class="mb-3">
                                <label for="days" class="form-label">Days (Select multiple dates if necessary)</label>
                                <input type="text" class="form-control" id="days" name="days" required>
                                <small id="daysHelp" class="form-text text-muted">Enter dates in YYYY-MM-DD format separated by commas.</small>

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
                            const days = document.getElementById('days').value.split(',').length;

                            // Assume numberOfTeams is available globally or fetched from the server
                            const numberOfTeams = <?php echo $totalTeams; ?>;

                            const availableHours = endTime - startTime;
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
                    <th>Panelists</th>
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
                                const dateTime = `${schedule.schedule_date} ${schedule.start_time} - ${schedule.end_time}`;

                                tbody.innerHTML += `
                                    <tr>
                                        <td>${dateTime}</td>
                                        <td>${schedule.team_name}</td>
                                        <td>${schedule.adviser}</td>
                                        <td>${schedule.thesis_title}</td>
                                        <td>${schedule.panelists}</td>
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

                            // Remove Test Row
                            // tbody.insertAdjacentHTML('beforeend', `<tr><td colspan="8">Test Row</td></tr>`); // Test row insertion removed
                            // console.log('Test row added'); // Test row log removed

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