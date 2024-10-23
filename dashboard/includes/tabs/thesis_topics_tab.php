                        <!-- Thesis Topics Tab -->
                        <div class="tab-pane fade" id="thesis-topics" role="tabpanel"
                            aria-labelledby="thesis-topics-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="thesis_topics">Add Thesis
                                    Topic</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($thesisTopics as $topic): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($topic['name']); ?></td>
                                            <td><?php echo htmlspecialchars($topic['description']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    data-table="thesis_topics"
                                                    data-id="<?php echo $topic['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn"
                                                    data-table="thesis_topics"
                                                    data-id="<?php echo $topic['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>