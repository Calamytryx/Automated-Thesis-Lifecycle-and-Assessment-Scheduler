                        <!-- Evaluations Tab -->
                        <div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="evaluations">Add
                                    Evaluation</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Defense Schedule</th>
                                            <th>Panelist</th>
                                            <th>Rubric</th>
                                            <th>Score</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($evaluations as $evaluation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(getDefenseScheduleInfo($pdo, $evaluation['defense_schedule_id'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(getUserName($pdo, $evaluation['panelist_id'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(getRubricName($pdo, $evaluation['rubric_id'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($evaluation['score']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="evaluations"
                                                    data-id="<?php echo $evaluation['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn"
                                                    data-table="evaluations"
                                                    data-id="<?php echo $evaluation['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>