                        <!-- Research Titles Tab -->
                        <div class="tab-pane fade" id="research-titles" role="tabpanel"
                            aria-labelledby="research-titles-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="research_titles">Add Research
                                    Title</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm db-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Team</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Pagination logic
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;

                                        $stmt = $pdo->prepare("SELECT * FROM research_titles LIMIT :limit OFFSET :offset");
                                        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                                        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                                        $stmt->execute();
                                        $researchTitles = $stmt->fetchAll();

                                        foreach ($researchTitles as $title):
                                            $team = getTeamName($pdo, $title['team_id']);
                                            $status = $title['approved_at']
                                                ? "Approved (" . date('Y-m-d H:i:s', strtotime($title['updated_at'])) . ")"
                                                : "Pending (" . date('Y-m-d H:i:s', strtotime($title['updated_at'])) . ")";
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($title['title']); ?></td>
                                                <td><?php echo htmlspecialchars($team); ?></td>
                                                <td><?php echo htmlspecialchars($status); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-btn"
                                                        data-table="research_titles"
                                                        data-id="<?php echo $title['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn"
                                                        data-table="research_titles"
                                                        data-id="<?php echo $title['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php
                            // Pagination controls
                            $stmt = $pdo->query("SELECT COUNT(*) FROM research_titles");
                            $totalTitles = $stmt->fetchColumn();
                            $totalPages = ceil($totalTitles / $limit);
                            ?>

                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <!-- <span aria-hidden="true">&laquo;</span> -->
                                             Previous
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <!-- <span aria-hidden="true">&raquo;</span> -->
                                             Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>