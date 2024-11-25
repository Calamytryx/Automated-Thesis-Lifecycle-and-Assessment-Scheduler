<!-- Rubrics Tab -->
<div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="rubrics">Add Rubric</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items_per_page = 10;
                $total_items = count($rubrics);
                $total_pages = ceil($total_items / $items_per_page);
                $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $start_index = ($current_page - 1) * $items_per_page;
                $rubrics_to_display = array_slice($rubrics, $start_index, $items_per_page);

                foreach ($rubrics_to_display as $rubric): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rubric['name']); ?></td>
                    <td><?php echo htmlspecialchars($rubric['description']); ?></td>
                    <td class="text-center align-middle">
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