                        <!-- Requirements Tab -->
                        <div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="requirements">Add
                                    Requirement</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Due Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requirements as $requirement): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($requirement['name']); ?></td>
                                            <td><?php echo htmlspecialchars($requirement['description']); ?></td>
                                            <td><?php echo htmlspecialchars($requirement['due_date']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    data-table="requirements"
                                                    data-id="<?php echo $requirement['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn"
                                                    data-table="requirements"
                                                    data-id="<?php echo $requirement['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>