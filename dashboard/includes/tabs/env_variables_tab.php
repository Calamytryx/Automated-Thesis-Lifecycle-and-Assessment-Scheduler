                        <!-- Environment Variables Tab -->
                        <div class="tab-pane fade" id="env-variables" role="tabpanel"
                            aria-labelledby="env-variables-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="env_variables">Add
                                    Environment Variable</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm db-table">
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
                                            <td class="text-center align-middle">
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    data-table="env_variables"
                                                    data-id="<?php echo $variable['id']; ?>">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>