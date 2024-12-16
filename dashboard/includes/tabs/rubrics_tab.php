<!-- Rubrics Tab -->
<div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn feature-btn add-btn" data-table="rubrics">
            <i class="fas fa-plus"></i>Add Rubric
        </button>
    </div>
    <div class="table-responsive db-table-container">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <!-- Pagination loaded via AJAX -->
        </ul>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loadRubrics = (page = 1) => {
                fetch(`includes/tabs/get_table.php?table=rubrics&page=${page}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        const tbody = document.querySelector('#rubrics .db-table tbody');
                        tbody.innerHTML = '';
                        data.data.forEach(rubric => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${rubric.name}</td>
                                    <td>${rubric.description}</td>
                                    <td class="action-buttons">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm edit-btn" data-table="rubrics" data-id="${rubric.id}">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm delete-btn" data-table="rubrics" data-id="${rubric.id}">
                                                <i class="fas fa-trash-alt me-1"></i>Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        // Update Pagination
                        const pagination = document.querySelector('#rubrics .pagination');
                        pagination.innerHTML = '';

                        // Previous Button
                        pagination.innerHTML += `
                            <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">&#8249;</a>
                            </li>
                        `;

                        // Page Numbers
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `
                                <li class="page-item ${page === i ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }

                        // Next Button
                        pagination.innerHTML += `
                            <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">&#8250;</a>
                            </li>
                        `;
                    });
            };

            // Initial Load
            loadRubrics();

            // Handle Pagination Clicks
            document.querySelector('#rubrics .pagination').addEventListener('click', function (e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        loadRubrics(page);
                    }
                }
            });
        });
    </script>
</div>
