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
        <span id="scheduleGenerationStatus" class="ml-2"></span>
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
                <?php
                $current_date = null;
                $items_per_page = 10;
                $total_items = count($defenseSchedules);
                $total_pages = ceil($total_items / $items_per_page);
                $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $start_index = ($current_page - 1) * $items_per_page;
                $end_index = min($start_index + $items_per_page, $total_items);

                for ($i = $start_index; $i < $end_index; $i++):
                    $schedule = $defenseSchedules[$i];
                    $schedule_date = date('M-d-y', strtotime($schedule['schedule_date']));
                    $start_time = date('H:i', strtotime($schedule['start_time']));
                    $end_time = date('H:i', strtotime($schedule['end_time']));

                    // Display the date only if it's different from the previous row
                    $date_display = ($current_date !== $schedule_date) ? $schedule_date . '<br>' : '';
                    $current_date = $schedule_date;

                    // Filter out staff members from team_members
                    $team_members = array_filter(explode(', ', $schedule['team_members']), function ($member) {
                        // Assuming staff names always start with "Staff"
                        return strpos($member, 'Staff') !== 0;
                    });
                    $team_members = implode(', ', $team_members);
                ?>
                    <tr>
                        <td><?php echo $date_display . $start_time . ' - ' . $end_time; ?></td>
                        <td><?php echo htmlspecialchars($schedule['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($team_members); ?></td>
                        <td><?php echo htmlspecialchars($schedule['adviser']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['thesis_title']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['panelists']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                        <td class="text-center align-middle">
                            <div class="d-flex">
                                <button class="btn btn-primary btn-sm edit-btn"
                                    data-table="defense_schedules"
                                    data-id="<?php echo $schedule['id']; ?>">Edit
                                </button>
                                <button class="btn btn-danger btn-sm delete-btn"
                                    data-table="defense_schedules"
                                    data-id="<?php echo $schedule['id']; ?>">Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <div id="scheduleGenerationResult" class="mb-3"></div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        &#8249;
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                    &#8250;
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>