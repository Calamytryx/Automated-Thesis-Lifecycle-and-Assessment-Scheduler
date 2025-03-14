<!-- Teams Tab -->
<div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn feature-btn add-btn" data-table="teams">
            <i class="fas fa-plus"></i>Add Team
        </button>
    </div>
    <div class="alert alert-warning mb-4 warning-table" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Teams Without Research Titles</h5>
        <p>The following teams do not have assigned research titles. Please add titles for these teams in <span class="text-danger">Research Titles Tab</span>.</p>
        <div class="table-responsive">
            <table class="table table-sm table-warning table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Program</th>
                    </tr>
                </thead>
                <tbody id="teams-no-title-body">
                    <!-- Teams with no title will be loaded via JavaScript -->
                </tbody>
            </table>
        </div>
        <div id="no-title-teams-message" class="text-center py-2">Loading...</div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to load teams without research titles
            const loadTeamsWithoutTitles = () => {
                fetch('includes/tabs/get_teams_without_titles.php')
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('teams-no-title-body');
                        const messageDiv = document.getElementById('no-title-teams-message');
                        const mainDiv = document.getElementById('warning-table');
                        
                        tbody.innerHTML = '';
                        
                        if (data.length === 0) {
                            messageDiv.textContent = 'No teams without research titles found.';
                            messageDiv.style.display = 'block';
                        } else {
                            data.forEach(team => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${team.name}</td>
                                        <td>${team.program}</td>
                                    </tr>
                                `;
                            });
                            messageDiv.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching teams without titles:', error);
                        document.getElementById('no-title-teams-message').textContent = 
                            'Error loading data. Please try again later.';
                    });
            };

            // Load teams without titles when page loads
            loadTeamsWithoutTitles();
        });
    </script>
    <div class="table-responsive db-table-container">
        <table class="table table-bordered table-hover table-sm db-table" id="teams-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Research Title</th>
                    <th>Adviser</th>
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
                            <td>${team.adviser}</td>
                            <td>${team.team_members}</td>
                            <td class="action-buttons">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-sm edit-btn" data-table="teams" data-id="${team.id}">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <button class="btn btn-sm delete-btn" data-table="teams" data-id="${team.id}">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </button>
                                </div>
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
