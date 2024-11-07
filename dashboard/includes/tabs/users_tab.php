                        <!-- Users Tab -->
                        <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="users">Add User</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>User Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                            <td><?php echo getUserType($user['usertype']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="users"
                                                    data-id="<?php echo $user['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn" data-table="users"
                                                    data-id="<?php echo $user['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>