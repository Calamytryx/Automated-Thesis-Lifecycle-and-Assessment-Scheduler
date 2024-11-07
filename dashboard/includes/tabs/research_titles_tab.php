                        <!-- Research Titles Tab -->
                        <div class="tab-pane fade" id="research-titles" role="tabpanel"
                            aria-labelledby="research-titles-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="research_titles">Add Research
                                    Title</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Team</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($researchTitles as $title):
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
                        </div>