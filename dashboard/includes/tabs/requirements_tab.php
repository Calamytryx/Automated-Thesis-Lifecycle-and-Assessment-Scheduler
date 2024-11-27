<!-- Requirements Tab -->
<div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="requirements">Add Requirement</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table" id="requirements-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically populated by AJAX -->
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation" id="pagination">
        <ul class="pagination justify-content-center">
            <!-- Pagination links will be dynamically populated by AJAX -->
        </ul>
    </nav>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadRequirements = (page = 1) => {
            fetch(`includes/tabs/get_table.php?table=requirements&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error, 'SQL Query:', data.sql_query, 'Parameters:', data.parameters, 'Stack Trace:', data.stack_trace);
                        return;
                    }

                    const tbody = document.querySelector('#requirements .db-table tbody');
                    tbody.innerHTML = '';
                    data.data.forEach(requirement => {
                        tbody.innerHTML += `
                                <tr>
                            <td>${requirement.name}</td>
                            <td>${requirement.description}</td>
                            <td>${requirement.due_date}</td>
                            <td class="text-center align-middle">
                                <button class="btn btn-primary btn-sm edit-btn" data-table="requirements" data-id="${requirement.id}">Edit</button>
                                <button class="btn btn-danger btn-sm delete-btn" data-table="requirements" data-id="${requirement.id}">Delete</button>
                            </td>
                        </tr>
                            `;
                    });

                    // Update Pagination
                    const pagination = document.querySelector('#requirements .pagination');
                    pagination.innerHTML = '';

                        // Previous Button
                        pagination.innerHTML += `
                            <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">Previous</a>
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
                                <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">Next</a>
                            </li>
                        `;
                    });
            };


        // Initial Load
        loadRequirements();

        // Handle Pagination Clicks
        document.querySelector('#defense-schedules .pagination').addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    loadRequirements(page);
                }
            }
        });
    });
</script>