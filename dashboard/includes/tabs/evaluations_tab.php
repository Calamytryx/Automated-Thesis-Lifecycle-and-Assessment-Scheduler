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
                                        <?php
                                        // Pagination logic
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;
                                        $totalEvaluations = count($evaluations);
                                        $totalPages = ceil($totalEvaluations / $limit);
                                        $currentEvaluations = array_slice($evaluations, $offset, $limit);

                                        foreach ($currentEvaluations as $evaluation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(getDefenseScheduleInfo($pdo, $evaluation['defense_schedule_id'])); ?></td>
                                            <td><?php echo htmlspecialchars(getUserName($pdo, $evaluation['panelist_id'])); ?></td>
                                            <td><?php echo htmlspecialchars(getRubricName($pdo, $evaluation['rubric_id'])); ?></td>
                                            <td><?php echo htmlspecialchars($evaluation['score']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="evaluations"
                                                    data-id="<?php echo $evaluation['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn" data-table="evaluations"
                                                    data-id="<?php echo $evaluation['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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