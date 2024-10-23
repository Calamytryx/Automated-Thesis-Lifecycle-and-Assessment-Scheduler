                        <!-- Teams Tab -->
                        <div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-primary btn-sm add-btn" data-table="teams">Add Team</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Research Title</th>
                                            <th>Members</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teams as $team):
                                            $teamMembers = getTeamMembersForEdit($pdo, $team['id'], 'html');
                                            $researchTitle = getResearchTitle($pdo, $team['id']);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($team['name']); ?></td>
                                            <td><?php echo htmlspecialchars($researchTitle); ?></td>
                                            <td><?php echo $teamMembers ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-table="teams"
                                                    data-id="<?php echo $team['id']; ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm delete-btn" data-table="teams"
                                                    data-id="<?php echo $team['id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>