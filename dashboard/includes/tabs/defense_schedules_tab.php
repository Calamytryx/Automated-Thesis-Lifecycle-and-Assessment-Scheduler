                        <!-- Defense Schedules Tab -->
                        <div class="tab-pane fade" id="defense-schedules" role="tabpanel"
                            aria-labelledby="defense-schedules-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm feature-btn" id="generateSchedule">Generate Defense
                                    Schedule</button>
                                <span id="scheduleGenerationStatus" class="ml-2"></span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
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
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    data-table="defense_schedules"
                                                    data-id="<?php echo $schedule['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn"
                                                    data-table="defense_schedules"
                                                    data-id="<?php echo $schedule['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                                <div id="scheduleGenerationResult" class="mb-3"></div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>