<!-- Teams Tab -->
<div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="teams">Add Team</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table" id="teams-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Research Title</th>
                    <th>Members</th>
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
       document.addEventListener('DOMContentLoaded', function () {
            const loadRubrics = (page = 1) => {
                fetch(`includes/tabs/get_table.php?table=teams&page=${page}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        const tbody = document.querySelector('#teams .db-table tbody');
                        tbody.innerHTML = '';
                        data.data.forEach(team => {
                            tbody.innerHTML += `
                                <tr>
                            <td>${team.name}</td>
                            <td>${team.research_title}</td>
                            <td>${team.team_members}</td>
                            <td class="text-center align-middle">
                                <button class="btn btn-primary btn-sm edit-btn" data-table="teams" data-id="${team.id}">Edit</button>
                                <button class="btn btn-danger btn-sm delete-btn" data-table="teams" data-id="${team.id}">Delete</button>
                            </td>
                        </tr>
                            `;
                        });

                        // Update Pagination
                        const pagination = document.querySelector('#teams .pagination');
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
            document.querySelector('#teams .pagination').addEventListener('click', function (e) {
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
