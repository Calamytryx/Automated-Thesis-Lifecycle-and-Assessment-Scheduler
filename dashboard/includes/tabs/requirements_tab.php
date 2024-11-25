<!-- Requirements Tab -->
<div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="requirements">Add
            Requirement</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;
                $total_requirements = count($requirements);
                $total_pages = ceil($total_requirements / $limit);
                $current_requirements = array_slice($requirements, $offset, $limit);

                foreach ($current_requirements as $requirement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($requirement['name']); ?></td>
                        <td><?php echo htmlspecialchars($requirement['description']); ?></td>
                        <td><?php echo htmlspecialchars($requirement['due_date']); ?></td>
                        <td class="text-center align-middle">
                            <button class="btn btn-primary btn-sm edit-btn" data-table="requirements" data-id="<?php echo $requirement['id']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-table="requirements" data-id="<?php echo $requirement['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <nav>
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