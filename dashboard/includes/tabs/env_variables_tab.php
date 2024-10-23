                        <!-- Environment Variables Tab -->
                        <div class="tab-pane fade" id="env-variables" role="tabpanel"
                            aria-labelledby="env-variables-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="env_variables">Add
                                    Environment Variable</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Value</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($envVariables as $variable): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($variable['key']); ?></td>
                                            <td><?php echo htmlspecialchars($variable['value']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    data-table="env_variables"
                                                    data-id="<?php echo $variable['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn"
                                                    data-table="env_variables"
                                                    data-id="<?php echo $variable['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>