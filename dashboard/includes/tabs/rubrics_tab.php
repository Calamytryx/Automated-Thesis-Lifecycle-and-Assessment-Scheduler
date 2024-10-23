                        <!-- Rubrics Tab -->
                        <div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="rubrics">Add Rubric</button>
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
                                        <?php foreach ($rubrics as $rubric): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($rubric['name']); ?></td>
                                            <td><?php echo htmlspecialchars($rubric['description']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="rubrics"
                                                    data-id="<?php echo $rubric['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn" data-table="rubrics"
                                                    data-id="<?php echo $rubric['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>