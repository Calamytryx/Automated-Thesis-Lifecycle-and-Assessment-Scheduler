<!-- Defense Schedules Tab -->
<div class="tab-pane fade" id="defense-schedules" role="tabpanel"
    aria-labelledby="defense-schedules-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
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
                                <input type="number" class="form-control" id="timeDuration" name="timeDuration" min="1" value="1" required>
                            </div>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveSchedulerSettings">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schedulerSettingsModal">
            Scheduler Settings
        </button>

        <div id="scheduleGenerationStatus"></div>
        <button id="generateSchedule" class="btn btn-primary ml-auto" disabled>Generate Defense Schedule</button>
        <span id="scheduleGenerationStatusSpan" class="ml-2"></span> <!-- Changed ID to ensure uniqueness -->
    </div>
    <div class="table-responsive" id="def-sched">
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
                                        
                                        <td class="text-center align-middle">
                                            <div class="d-flex">
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="defense_schedules" data-id="${schedule.id}">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn" data-table="defense_schedules" data-id="${schedule.id}">Delete</button>
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