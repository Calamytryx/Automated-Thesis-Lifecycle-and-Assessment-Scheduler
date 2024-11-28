<!-- Defense Schedules Tab -->
<div class="tab-pane fade" id="defense-schedules" role="tabpanel" aria-labelledby="defense-schedules-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schedulerSettingsModal">Scheduler Settings</button>
        <button id="generateSchedule" class="btn btn-primary" disabled>Generate Defense Schedule</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Team</th>
                    <th>Members</th>
                    <th>Adviser</th>
                    <th>Thesis Title</th>
                    <th>Panelists</th>
                    <th>Room</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <!-- Pagination loaded via AJAX -->
        </ul>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loadSchedules = (page = 1) => {
                fetch(`includes/tabs/get_table.php?table=defense_schedules&page=${page}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error, 'SQL Query:', data.sql_query, 'Parameters:', data.parameters, 'Stack Trace:', data.stack_trace);
                            return;
                        }

                        const tbody = document.querySelector('#defense-schedules .db-table tbody');
                        tbody.innerHTML = '';
                        data.data.forEach(schedule => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${schedule.schedule_date} ${schedule.start_time} - ${schedule.end_time}</td>
                                    <td>${schedule.team_name}</td>
                                    <td>${schedule.team_members}</td>
                                    <td>${schedule.adviser}</td>
                                    <td>${schedule.thesis_title}</td>
                                    <td>${schedule.panelists}</td>
                                    <td>${schedule.room}</td>
                                    <td>
                                        <div class="d-flex">
                                            <button class="btn btn-primary btn-sm edit-btn" data-table="defense_schedules" data-id="${schedule.id}">Edit</button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-table="defense_schedules" data-id="${schedule.id}">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        // Update Pagination
                        const pagination = document.querySelector('#defense-schedules .pagination');
                        pagination.innerHTML = '';
                        pagination.innerHTML += `
                            <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page - 1}">&#8249;</a>
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
                                <a class="page-link" href="#" data-page="${page + 1}">&#8250;</a>
                            </li>
                        `;
                    });
            };

            // Initial Load
            loadSchedules();

            // Handle Pagination Clicks
            document.querySelector('#defense-schedules .pagination').addEventListener('click', function (e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        loadSchedules(page);
                    }
                }
            });
        });
    </script>
</div>
