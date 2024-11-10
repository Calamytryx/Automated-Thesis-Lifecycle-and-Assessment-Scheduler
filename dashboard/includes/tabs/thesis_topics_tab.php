                        <!-- Thesis Topics Tab -->
                        <!-- <div class="tab-pane fade" id="thesis-topics" role="tabpanel"
                            aria-labelledby="thesis-topics-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="thesis_topics">Add Thesis
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
                        </div> -->
                        <div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 my-3">
                                <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="thesis_topics">Add Thesis Topic</button>
                            </div>
                            <div class="my-3 p-3 home-sidebar-box">
                                <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Thesis Topic Decision Tool</h6>
                                <div class="media text-muted pt-3">
                                    <div class="form-group">
                                        <label for="thesisField">Select a field:</label>
                                        <select id="thesisField" class="form-select">
                                            <option value="">Select a field</option>
                                            <option value="Architecture">Architecture</option>
                                            <option value="Computer Science">Computer Science</option>
                                            <option value="Information Technology">Information Technology</option>
                                            <option value="Aeronautical Engineering">Aeronautical Engineering</option>
                                            <option value="Civil Engineering">Civil Engineering</option>
                                            <option value="Computer Engineering">Computer Engineering</option>
                                            <option value="Engineering Technology with a major in Construction Technology and Management">Engineering Technology (Construction Technology and Management)</option>
                                            <option value="Electrical Engineering">Electrical Engineering</option>
                                            <option value="Electronics Engineering">Electronics Engineering</option>
                                            <option value="Industrial Engineering">Industrial Engineering</option>
                                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                                        </select>
                                    </div>
                                    <button id="getTopicsBtn" class="btn btn-primary mt-3 feature-btn">Get Latest Topics</button>
                                </div>
                                <div id="topicAnalysisResult" class="mt-3">
                                    <!-- Loading spinner (initially hidden) -->
                                    <div id="loadingSpinner" class="text-center d-none">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Searching for the latest topics...</p>
                                    </div>
                                </div>
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
                                                    <button class="btn btn-primary btn-sm edit-btn" data-table="thesis_topics" data-id="<?php echo $topic['id']; ?>">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-btn" data-table="thesis_topics" data-id="<?php echo $topic['id']; ?>">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>