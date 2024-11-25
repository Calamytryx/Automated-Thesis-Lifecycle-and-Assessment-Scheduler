<!-- Teams Tab -->
<div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="teams">Add Team</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Research Title</th>
                    <th>Members</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;
                $total_teams = count($teams);
                $total_pages = ceil($total_teams / $limit);
                $teams_paginated = array_slice($teams, $offset, $limit);

                foreach ($teams_paginated as $team):
                    $teamMembers = getTeamMembersForEdit($pdo, $team['id'], 'html');
                    $researchTitle = getResearchTitle($pdo, $team['id']);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($team['name']); ?></td>
                    <td><?php echo htmlspecialchars($researchTitle); ?></td>
                    <td><?php echo $teamMembers ?></td>
                    <td class="text-center align-middle">
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
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                    <!-- <span aria-hidden="true">&laquo;</span> -->
                        Previous
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                    <!-- <span aria-hidden="true">&raquo;</span> -->
                        Next
                </a>
            </li>
        </ul>
    </nav>
</div>