                        <!-- Defense Schedules Tab -->
                        <div class="tab-pane fade" id="defense-schedules" role="tabpanel"
                            aria-labelledby="defense-schedules-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm" id="generateSchedule">Generate Defense
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
                                        foreach ($defenseSchedules as $schedule):
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
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="scheduleGenerationResult" class="mb-3"></div>
                            </div>
                        </div>